<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateManagedUserRequest;
use App\Http\Resources\Api\ManagedUserResource;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));

        return ManagedUserResource::collection(
            User::query()
                ->when($term !== '', fn ($query) => $query->where(fn ($users) => $users
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')))
                ->latest()
                ->paginate($validated['limit'] ?? 20)
                ->withQueryString()
        );
    }

    public function update(UpdateManagedUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('manageRole', $user);

        $attributes = $request->validated();
        $actor = $request->user('sanctum');

        DB::transaction(function () use ($user, $attributes): void {
            if (array_key_exists('role', $attributes)) {
                $this->updateRole($user, $attributes['role']);
            }

            if (array_key_exists('banned', $attributes)) {
                $this->updateBan($user, $attributes['banned'], $attributes['ban_reason'] ?? null);
            }

            if (array_key_exists('premium_duration', $attributes)) {
                $this->updatePremium($user, $attributes['premium_duration']);
            }
        });

        $user->refresh();
        $this->activityLogger->record(
            $actor,
            'user.management_updated',
            'Kullanıcı yönetim ayarları güncellendi.',
            $user,
            collect($attributes)->only(['role', 'banned', 'premium_duration'])->all(),
        );

        return (new ManagedUserResource($user))
            ->additional(['message' => 'Kullanıcı ayarları güncellendi.'])
            ->response();
    }

    private function updateRole(User $user, string $role): void
    {
        abort_if($user->is_admin && $role !== 'admin' && User::query()->where('is_admin', true)->count() <= 1, 422, 'Platformda en az bir yönetici kalmalıdır.');

        $user->update([
            'is_admin' => $role === 'admin',
            'is_moderator' => $role === 'moderator',
        ]);
    }

    private function updateBan(User $user, bool $banned, ?string $reason): void
    {
        abort_if($user->is_admin, 422, 'Yöneticiler yasaklanamaz.');

        $user->update($banned
            ? ['banned_at' => now(), 'ban_reason' => $reason]
            : ['banned_at' => null, 'ban_reason' => null]);
    }

    private function updatePremium(User $user, string $duration): void
    {
        if ($duration === 'revoke') {
            $user->update(['premium_until' => null]);

            return;
        }

        $startsAt = $user->premium_until?->isFuture() ? $user->premium_until->copy() : now();
        $user->update(['premium_until' => $startsAt->addMonths((int) $duration)]);
    }
}
