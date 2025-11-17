<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController
{
    public function index(): JsonResponse
    {
        $data = $this->status();
        return response()->json([
            'success' => true,
            'message' => 'Snappie API Server is running',
            'version' => $data['version'],
            'timestamp' => $data['timestamp'],
            'uptime' => $data['uptime'],
            'environment' => $data['environment'],
            'database' => $data['database'],
        ]);
    }
    
    private function status(): array
    {
        $start = microtime(true);
        $dbStatus = 'disconnected';
        $connection = config('database.default');
        $ping = null;

        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
            DB::connection()->select('select 1');
            $ping = (int) (1000 * (microtime(true) - $start));
        } catch (\Throwable $e) {
            Log::error('Health check DB error', ['message' => $e->getMessage()]);
        }

        return [
            'version' => '2.0.0',
            'timestamp' => now()->toIso8601String(),
            'uptime' => (float) (microtime(true) - LARAVEL_START),
            'environment' => App::environment(),
            'database' => [
                'status' => $dbStatus,
                'connection' => (string) $connection,
                'ping_time_ms' => $ping ?? 0,
            ],
        ];
    }
}
