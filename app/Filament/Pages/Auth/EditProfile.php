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
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Akun';
    protected static ?int $navigationSort = 10;
    public function getHeading(): string
    {
        return 'Pengaturan Akun';
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
                ->label('Simpan Perubahan Profil')
                ->icon('heroicon-m-check-circle')
                ->size('lg'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pribadi')
                    ->description('Data ini digunakan untuk identifikasi akun Anda di seluruh sistem.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getNameFormComponent()
                                    ->label('Username')
                                    ->prefixIcon('heroicon-m-user')
                                    ->columnSpan(1),

                                $this->getEmailFormComponent()
                                    ->label('Alamat Email')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Kredensial Keamanan')
                    ->description('Ganti password secara berkala untuk mencegah akses yang tidak diinginkan.')
                    ->icon('heroicon-o-lock-closed')
                    // ->aside() dihapus agar deskripsi pindah ke atas
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                $this->getPasswordFormComponent()
                                    ->label('Password Baru')
                                    ->prefixIcon('heroicon-m-key')
                                    ->columnSpan(1),

                                $this->getPasswordConfirmationFormComponent()
                                    ->visible(true)
                                    ->label('Konfirmasi Password')
                                    ->prefixIcon('heroicon-m-shield-check')
                                    ->columnSpan(1),
                            ]),
                    ]),
                // SECTION HAPUS AKUN (Manual)
                Section::make('Hapus Akun')
                    ->description('Menghapus akun Anda akan menghapus semua data terkait secara permanen.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Actions::make([
                            Action::make('deleteAccount')
                                ->label('Hapus Akun Saya')
                                ->color('danger')
                                ->icon('heroicon-m-trash')
                                ->requiresConfirmation() // Menampilkan modal konfirmasi
                                ->modalHeading('Hapus Akun?')
                                ->modalDescription('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.')
                                ->modalSubmitActionLabel('Ya, Hapus Permanen')
                                ->action(function () {
                                    /** @var \App\Models\User $user  */
                                    $user = Auth::user();
                                    Auth::logout();
                                    $user->delete();

                                    Notification::make()
                                        ->title('Akun berhasil dihapus')
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
