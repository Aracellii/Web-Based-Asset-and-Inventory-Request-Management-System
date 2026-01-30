<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\StockMovementChart;
use App\Filament\Widgets\TopRequestedItemsChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\EditProfile;

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
            ->brandName('SIATK')

            ->colors([
                'primary' => Color::Indigo,
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->profile(EditProfile::class) 
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Akun Saya')
                    ->icon('heroicon-m-user-circle'),
            ])
            // Hooks
            ->renderHook(
                'panels::user-menu.before',
                fn() => view('filament.components.realtime-clock'),
            )
            // Discoveries
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // Registered Components
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                StockMovementChart::class,
                TopRequestedItemsChart::class,
            ])
            // Middlewares
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
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => Blade::render('
                <style>
                    /* 1. TEMA TERANG */
                    html:not(.dark) .fi-sidebar {
                        background: linear-gradient(180deg, #11386b 0%, #5ca8ff 100%) !important;
                    }
                    html:not(.dark) .fi-sidebar-header {
                        background: #11386b !important;
                        
                    }
                    html:not(.dark) .fi-sidebar-header .fi-logo,
                    html:not(.dark) .fi-sidebar-header a {
                        color: white !important;
                    }
                    html:not(.dark) .fi-sidebar-item-label {
                        color: #eef4f7 !important;
                        font-weight: 500;
                    }
                    html:not(.dark) .fi-sidebar-item-icon {
                        color: #0ea5e9 !important;
                    }
                    html:not(.dark) .fi-sidebar-item-button:hover {
                        background-color: #bae6fd !important;
                    }
                    html:not(.dark) .fi-sidebar-item-active .fi-sidebar-item-button {
                        background-color: #0284c7 !important;
                        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                    }
                    html:not(.dark) .fi-sidebar-item-active .fi-sidebar-item-label,
                    html:not(.dark) .fi-sidebar-item-active .fi-sidebar-item-icon {
                        color: white !important;
                    }

                    /* 2. TEMA GELAP */
                    .dark .fi-sidebar {
                        background: #0f172a !important;
                        border-right: 1px solid #1e293b !important;
                    }
                 
                   
                    .dark .fi-sidebar-header {
                        background: #0f172a !important;
                    }
                    .dark .fi{
                        background: #0f172a !important;
                    }
                    .dark .fi-sidebar-item-label {
                        color: #94a3b8 !important;
                    }
                    .dark .fi-sidebar-item-icon {
                        color: #38bdf8 !important;
                    }
                    .dark .fi-sidebar-item-button:hover {
                        background-color: rgba(255, 255, 255, 0.05) !important;
                    }
                    .dark .fi-sidebar-item-active .fi-sidebar-item-button {
                        background-color: #2563eb !important;
                    }
                    .dark .fi-sidebar-item-active .fi-sidebar-item-label,
                    .dark .fi-sidebar-item-active .fi-sidebar-item-icon {
                        color: white !important;
                    }
                </style>
            '),
        );
    }
}
