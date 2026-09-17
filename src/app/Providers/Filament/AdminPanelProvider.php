<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\ExecutiveOverviewWidget;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\PurchaseVsSalesChartWidget;
use App\Filament\Widgets\SalesTrendChartWidget;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('MarsERP')
            ->brandLogo(asset('images/marserp-logo.png'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/marserp-favicon.png'))
            ->login()
            ->colors([
                'primary' => Color::hex('#B47854'),
                'gray' => Color::hex('#3C3C48'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(false)
            ->navigationGroups([
                'Approvals',
                'Master Data',
                'Purchasing',
                'Inventory',
                'Sales',
                'Finance',
                'Asset',
                'Productivity',
                'Helpdesk',
                'AI & Automation',
                'Reports',
                'Administration',
            ])
            ->navigationItems([
                NavigationItem::make('ERP AI')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->group('AI & Automation')
                    ->url('/ai', shouldOpenInNewTab: true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                ExecutiveOverviewWidget::class,
                SalesTrendChartWidget::class,
                PurchaseVsSalesChartWidget::class,
                PendingApprovalsWidget::class,
                FilamentInfoWidget::class,
            ])
            ->databaseNotifications()
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
