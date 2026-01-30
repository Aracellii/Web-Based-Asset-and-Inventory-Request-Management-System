<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Form;
// Tetap menginduk ke BaseEditProfile agar fiturnya tetap jalan otomatis
use Filament\Pages\Auth\EditProfile as BaseEditProfile; 

class EditProfile extends BaseEditProfile
{
    public function getHeading(): string
    {
        return 'Profil';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Profil')
                    ->aside()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),
                
                Section::make('Ubah Password')
                    ->description('Pastikan password Anda aman.')
                    ->aside()
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }
}