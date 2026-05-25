<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login;

class IAMLogin extends Login
{
    public function mount(): void
    {
        if (auth()->check()) {
            redirect()->intended(filament()->getUrl());
        }

        // Redirect langsung ke IAM SSO
        redirect()->route('sso.login', [
            'intended' => filament()->getUrl()
        ]);
    }
}
