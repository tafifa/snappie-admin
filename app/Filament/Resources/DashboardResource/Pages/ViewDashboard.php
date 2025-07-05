<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use App\Filament\Resources\DashboardResource;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\RecentReviews;
use Filament\Resources\Pages\Page;

class ViewDashboard extends Page
{
    protected static string $resource = DashboardResource::class;
    protected static string $view = 'filament.resources.dashboard-resource.pages.view-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentActivity::class,
            RecentReviews::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
