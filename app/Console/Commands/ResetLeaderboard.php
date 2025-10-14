<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeaderboardService;
use App\Models\Leaderboard;
use App\Models\User;
use App\Models\ExpTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetLeaderboard extends Command
{
    protected $signature = 'leaderboard:reset {--confirm : Skip confirmation prompt}';
    protected $description = 'Reset leaderboard untuk kompetisi bulanan (DANGEROUS!)';

    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        parent::__construct();
        $this->leaderboardService = $leaderboardService;
    }

    public function handle(): int
    {
        try {
            // Safety confirmation
            if (!$this->option('confirm')) {
                $this->warn('⚠️  PERINGATAN: Command ini akan mereset semua EXP user ke 0!');
                $this->warn('⚠️  Ini adalah operasi BERBAHAYA dan TIDAK DAPAT DIBATALKAN!');
                
                if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?')) {
                    $this->info('❌ Reset leaderboard dibatalkan.');
                    return self::SUCCESS;
                }
            }

            $this->info('🔄 Memulai reset leaderboard...');

            $activeLeaderboard = Leaderboard::where('status', true)->first();

            if ($activeLeaderboard) {
                $this->info('🔄 Memproses reset user EXP...');
                
                // Process users in batches for memory efficiency
                $totalUsers = 0;
                User::where('total_exp', '>', 0)
                    ->chunkById(50, function ($users) use ($activeLeaderboard, &$totalUsers) {
                        $transactions = [];
                        $userIds = [];

                        // Prepare batch data
                        foreach ($users as $user) {
                            $userIds[] = $user->id;
                            $totalUsers++;

                            // Prepare transaction data for batch insert
                            $transactions[] = [
                                'user_id' => $user->id,
                                'exp' => -$user->total_exp, // Record as negative value
                                'coin' => 0,
                                'description' => 'Monthly leaderboard reset',
                                'related_to_id' => $activeLeaderboard->id,
                                'related_to_type' => get_class($activeLeaderboard),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        // Perform batch operations in transaction
                        DB::transaction(function () use ($userIds, $transactions) {
                            // Batch insert transactions
                            if (!empty($transactions)) {
                                ExpTransaction::insert($transactions);
                            }

                            // Batch update users
                            if (!empty($userIds)) {
                                User::whereIn('id', $userIds)->update([
                                    'total_exp' => 0,
                                    'updated_at' => now()
                                ]);
                            }
                        });

                        $this->line("✅ Processed batch of " . count($users) . " users");
                    });

                // Deactivate current leaderboard
                $activeLeaderboard->update([
                    'status' => false,
                    'ended_at' => now(),
                ]);

                // Create new leaderboard period
                Leaderboard::create([
                    'started_at' => now()->startOfMonth(),
                    'ended_at' => now()->endOfMonth(),
                    'status' => true,
                ]);

                // Create snapshot before reset
                $this->info('📸 Membuat backup data sebelum reset...');
                $this->leaderboardService->refreshLeaderboard(); // Ensure latest data is saved
                $this->line('✅ Backup data berhasil dibuat');

                // Clear all caches
                $this->leaderboardService->clearLeaderboardCache();

                $this->info("🎉 Leaderboard berhasil direset! Total {$totalUsers} users diproses.");
                
                Log::warning('Leaderboard reset completed', [
                    'total_users_processed' => $totalUsers,
                    'previous_leaderboard_id' => $activeLeaderboard->id,
                    'admin_action' => true
                ]);

            } else {
                $this->warn('⚠️  Tidak ada leaderboard aktif untuk direset.');
                return self::SUCCESS;
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error resetting leaderboard: ' . $e->getMessage());
            Log::error('Leaderboard reset failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }
}
