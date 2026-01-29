<?php

namespace App\Filament\Pages\Auth;

use App\Models\Bagian;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Component;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

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

    protected function getRoleFormComponent(): Component
    {
    return Select::make('bagian_id')
        ->label('Bidang / Bagian')
        ->options(Bagian::pluck('nama_bagian', 'id')) 
        ->searchable()
        ->required();
    }   
}
