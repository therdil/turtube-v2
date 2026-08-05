<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function clear(): RedirectResponse
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'Tüm bildirimler temizlendi.');
    }

    public function readAndVisit(Request $request, string $notification): RedirectResponse
    {
        $item = auth()->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        $url = (string) ($item->data['url'] ?? route('notifications.index'));

        if (Str::startsWith($url, '//') || ! Str::startsWith($url, [url('/'), '/'])) {
            $url = route('notifications.index');
        }

        return redirect()->to($url);
    }
}
