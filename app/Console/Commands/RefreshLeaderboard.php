<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Log;

class RefreshLeaderboard extends Command
{
    protected $signature = 'leaderboard:refresh';
    protected $description = 'Refresh complete leaderboard data dengan recalculation';

    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        parent::__construct();
        $this->leaderboardService = $leaderboardService;
    }

    public function handle(): int
    {
        try {
            $this->info('🔄 Memulai refresh complete leaderboard...');
            
            // Refresh all leaderboard data
            $this->leaderboardService->refreshLeaderboard();
            $this->line('✅ Leaderboard data berhasil di-refresh');

            // Clear cache setelah refresh
            $this->leaderboardService->clearLeaderboardCache();
            $this->line('✅ Cache dibersihkan setelah refresh');

            $this->info('🎉 Complete leaderboard refresh selesai!');
            
            Log::info('Complete leaderboard refresh completed successfully');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error refreshing leaderboard: ' . $e->getMessage());
            Log::error('Leaderboard refresh failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }
}