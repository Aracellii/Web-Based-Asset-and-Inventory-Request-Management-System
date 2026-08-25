<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    protected static ?string $navigationGroup = 'Account';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Account';
    protected static ?int $navigationSort = 1;
    public function getHeading(): string
    {
        return 'Account Settings';
    }

    

    public function getMaxContentWidth(): MaxWidth
    {
        // Memastikan container halaman menggunakan 100% lebar layar
        return MaxWidth::Full;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Profile Changes')
                ->icon('heroicon-m-check-circle')
                ->size('lg'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->description('Update your account name and email.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getNameFormComponent()
                                    ->label('Name')
                                    ->prefixIcon('heroicon-m-user')
                                    ->columnSpan(1),

                                $this->getEmailFormComponent()
                                    ->label('Email Address')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Security Credentials')
                    ->description('Use a strong, unique password to keep your account secure.')
                    ->icon('heroicon-o-lock-closed')
                    // ->aside() dihapus agar deskripsi pindah ke atas
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getPasswordFormComponent()
                                    ->label('New Password')
                                    ->prefixIcon('heroicon-m-key')
                                    ->columnSpan(1),

                                $this->getPasswordConfirmationFormComponent()
                                    ->label('Confirm Password')
                                    ->prefixIcon('heroicon-m-shield-check')
                                    ->columnSpan(1),
                            ]),
                    ]),
                // SECTION HAPUS AKUN (Manual)
                Section::make('Delete Account')
                    ->description('Deleting your account will permanently remove all related data.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Actions::make([
                            Action::make('deleteAccount')
                                ->label('Delete My Account')
                                ->color('danger')
                                ->icon('heroicon-m-trash')
                                ->requiresConfirmation() // Menampilkan modal konfirmasi
                                ->modalHeading('Delete Account?')
                                ->modalDescription('Are you sure you want to delete your account? This action cannot be undone.')
                                ->modalSubmitActionLabel('Yes, Delete Permanently')
                                ->action(function () {
                                    /** @var \App\Models\User $user  */
                                    $user = Auth::user();
                                    Auth::logout();
                                    $user->delete();

                                    Notification::make()
                                        ->title('Account deleted successfully')
                                        ->success()
                                        ->send();

                                    return redirect()->to(route('filament.admin.auth.login'));
                                }),
                        ]),
                    ]),
            ])
            ->inlineLabel(false);
    }
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
