<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;

class Akun extends Page
{
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.auth.akun';
}
