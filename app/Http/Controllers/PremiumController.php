<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PremiumController extends Controller
{
    public function index(): View
    {
        return view('premium.index', [
            'hasPremium' => auth()->user()?->hasPremiumAccess() ?? false,
        ]);
    }
}
