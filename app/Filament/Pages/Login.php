<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Don't auto-fill credentials to prevent accidental re-login after logout
        // Users still see demo credentials in placeholder text
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $identifier = $data['identifier'];

        // Cari user berdasarkan NIP atau No HP
        $user = User::where('nip', $identifier)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            Notification::make()
                ->title('Login Gagal')
                ->body('NIP atau password salah. Silakan coba lagi.')
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.identifier' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        if (
            ($user instanceof FilamentUser) &&
            (!$user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            $this->throwFailureValidationException();
        }

        Filament::auth()->login($user, $data['remember'] ?? false);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getIdentifierFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getIdentifierFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getIdentifierFormComponent(): Component
    {
        return TextInput::make('identifier')
            ->label('NIP')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
