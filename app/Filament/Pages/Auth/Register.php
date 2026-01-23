<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Auth\Events\Registered;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Register extends BaseRegister
{
    public function register(): ?RegistrationResponse
    {
        // ... (kode rate limit tetap sama)

        $data = $this->form->getState();

        // 1. AKTIFKAN KEMBALI baris ini agar user punya role dan bisa liat data
        $data['role'] = 'user'; 

        // 2. Pastikan model User diisi dengan data dari form (termasuk bagian_id)
        $user = $this->getUserModel()::create($data);

        // ... (kode event dan login tetap sama)

        return app(RegistrationResponse::class);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getRoleFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getRoleFormComponent(): \Filament\Forms\Components\Component
    {
    return \Filament\Forms\Components\Select::make('bagian_id')
        ->label('Bidang / Bagian')
        ->options(\App\Models\Bagian::pluck('nama_bagian', 'id')) 
        ->searchable()
        ->required();
    }   
}
