<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Widgets\StockMovementChart;
use App\Filament\Widgets\TopRequestedItemsChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('app')
            ->login()
            ->registration(Register::class)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->brandName('SIATK')
            ->renderHook(
                'panels::user-menu.before',
                fn() => view('filament.components.realtime-clock'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StockMovementChart::class,
                TopRequestedItemsChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarFullyCollapsibleOnDesktop();
    }

    public function boot()
    {
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => Blade::render('<style>
    /* 1. Background Biru Muda */
    .fi-sidebar {
    background: linear-gradient(180deg, #e0f2fe 0%, #ffffff 100%) !important;
    }

    /* 2. Teks & Icon harus Gelap agar terbaca */
    .fi-sidebar-item-label {
        color: #0369a1 !important; /* Biru Tua */
        font-weight: 500;
    }

    .fi-sidebar-item-icon {
        color: #0ea5e9 !important; /* Biru Cerah */
    }

    /* 3. Efek Hover (Jangan pakai warna gelap) */
    .fi-sidebar-item-button:hover {
        background-color: #bae6fd !important; /* Biru yang sedikit lebih tua dari bg */
    }

    /* 4. Saat Menu Aktif */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #0284c7 !important; /* Biru Solid */
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .fi-sidebar-item-active .fi-sidebar-item-label,
    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: white !important;
    }

    /* Judul Group Menu */
    .fi-sidebar-group-label {
        color: #64748b !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.7rem;
    }
    </style>'),
        );
    }
}
