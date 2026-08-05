<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private AdminActivityLogger $activityLogger)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $users = User::query()
            ->when(filled($validated['q'] ?? null), function ($query) use ($validated) {
                $term = $validated['q'];
                $query->where(fn ($users) => $users
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%'));
            })
            ->withCount(['videos', 'comments', 'videoReports'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Kendi yönetici yetkinizi değiştiremezsiniz.');
        abort_if($user->is_admin && User::where('is_admin', true)->count() <= 1, 422, 'Platformda en az bir yönetici kalmalıdır.');

        $user->update(['is_admin' => ! $user->is_admin]);
        $this->activityLogger->record(auth()->user(), 'user.role_updated', 'Kullanici yonetici yetkisi guncellendi.', $user, ['is_admin' => $user->is_admin]);

        return back()->with('success', 'Yönetici yetkisi güncellendi.');
    }

    public function updatePremium(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['duration' => ['required', 'in:1,3,12,revoke']]);

        if ($validated['duration'] === 'revoke') {
            $user->update(['premium_until' => null]);
            $this->activityLogger->record($request->user(), 'user.premium_revoked', 'Kullanicinin premium erisimi kaldirildi.', $user);

            return back()->with('success', 'Premium erişimi kaldırıldı.');
        }

        $startsAt = $user->premium_until?->isFuture() ? $user->premium_until->copy() : now();
        $user->update(['premium_until' => $startsAt->addMonths((int) $validated['duration'])]);
        $this->activityLogger->record($request->user(), 'user.premium_updated', 'Kullanicinin premium erisimi guncellendi.', $user, ['duration_months' => (int) $validated['duration']]);

        return back()->with('success', 'Premium erişimi güncellendi.');
    }

    public function toggleVerified(User $user): RedirectResponse
    {
        $user->update(['is_verified' => ! $user->is_verified]);
        $this->activityLogger->record(auth()->user(), 'user.verification_updated', 'Kanal dogrulama rozeti guncellendi.', $user, ['is_verified' => $user->is_verified]);

        return back()->with('success', 'Kanal doğrulama rozeti güncellendi.');
    }

    public function toggleBan(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()) || $user->is_admin, 422, 'Yöneticiler veya kendi hesabınız yasaklanamaz.');

        if ($user->banned_at) {
            $user->update(['banned_at' => null, 'ban_reason' => null]);
            $this->activityLogger->record($request->user(), 'user.unbanned', 'Kullanici yasagi kaldirildi.', $user);

            return back()->with('success', 'Kullanıcı yasağı kaldırıldı.');
        }

        $validated = $request->validate(['ban_reason' => ['nullable', 'string', 'max:500']]);
        $user->update(['banned_at' => now(), 'ban_reason' => $validated['ban_reason'] ?? null]);
        $this->activityLogger->record($request->user(), 'user.banned', 'Kullanici banlandi.', $user, ['reason' => $validated['ban_reason'] ?? null]);

        return back()->with('success', 'Kullanıcı banlandı; sonraki isteğinde oturumu kapatılacak.');
    }

    public function show(User $user): View
    {
        $user->loadCount(['videos', 'comments', 'videoReports']);

        $activities = collect()
            ->merge($user->videos()->latest()->take(10)->get()->map(fn ($video) => [
                'type' => 'Video yükledi', 'description' => $video->title, 'created_at' => $video->created_at, 'url' => route('videos.show', $video),
            ]))
            ->merge($user->comments()->with('video')->latest()->take(10)->get()->map(fn ($comment) => [
                'type' => 'Yorum yaptı', 'description' => $comment->video?->title ?: 'Silinmiş video', 'created_at' => $comment->created_at, 'url' => $comment->video ? route('videos.show', $comment->video) : null,
            ]))
            ->merge($user->videoReports()->with('video')->latest()->take(10)->get()->map(fn ($report) => [
                'type' => 'Video bildirdi', 'description' => $report->video?->title ?: 'Silinmiş video', 'created_at' => $report->created_at, 'url' => $report->video ? route('videos.show', $report->video) : null,
            ]))
            ->sortByDesc('created_at')
            ->take(20)
            ->values();

        return view('admin.user-activity', compact('user', 'activities'));
    }
}
