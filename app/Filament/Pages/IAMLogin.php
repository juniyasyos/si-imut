<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\Login as BaseLogin;

class IAMLogin extends BaseLogin
{
    public function mount(): void
    {
        if (auth()->check()) {
            redirect()->intended(filament()->getUrl());
        }

        // Redirect langsung ke IAM SSO
        redirect()->route('iam.sso.login', [
            'intended' => filament()->getUrl()
        ]);
    }
}
