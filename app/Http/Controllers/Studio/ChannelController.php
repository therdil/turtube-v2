<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChannelController extends Controller
{
    public function index()
    {
        return view('studio.channel.index', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([

            'channel_name' => [
                'required',
                'string',
                'max:100',
            ],

            'channel_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'avatar' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'banner' => [
                'nullable',
                'image',
                'max:4096',
            ],

        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request
                ->file('avatar')
                ->store('avatars', 'public');
        }

        if ($request->hasFile('banner')) {

            if ($user->banner) {
                Storage::disk('public')->delete($user->banner);
            }

            $validated['banner'] = $request
                ->file('banner')
                ->store('banners', 'public');
        }

        $user->update($validated);

        return back()->with(
            'success',
            'Kanal bilgileri güncellendi.'
        );
    }
}