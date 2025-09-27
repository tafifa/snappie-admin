<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Place;
use App\Models\Checkin;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -2;
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalPlaces = Place::count();
        $totalCheckins = Checkin::count();
        $totalReviews = Review::count();
        
        $todayCheckins = Checkin::whereDate('created_at', today())->count();
        $todayReviews = Review::whereDate('created_at', today())->count();
        
        $pendingReviews = Review::where('status', true)->count();
        $completedCheckins = Checkin::where('status', true)->count();
        
        return [
            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            
            Stat::make('Total Places', $totalPlaces)
                ->description('Available locations')
                ->descriptionIcon('heroicon-o-map')
                ->color('success'),
            
            Stat::make('Check-ins Today', $todayCheckins)
                ->description("Total: {$totalCheckins}")
                ->descriptionIcon('heroicon-o-map-pin')
                ->color($todayCheckins > 0 ? 'success' : 'warning'),
            
            Stat::make('Reviews Today', $todayReviews)
                ->description("Total: {$totalReviews}")
                ->descriptionIcon('heroicon-o-star')
                ->color($todayReviews > 0 ? 'success' : 'warning'),
            
            Stat::make('Pending Reviews', $pendingReviews)
                ->description('Need moderation')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingReviews > 0 ? 'danger' : 'success'),
            
            Stat::make('Completed Check-ins', $completedCheckins)
                ->description("Out of {$totalCheckins} total")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),
        ];
    }
}
