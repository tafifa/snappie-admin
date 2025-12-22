<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AppUpdate;
use Illuminate\Http\Request;

class AppUpdaterController
{
    public function checkAndUpdate(Request $request)
    {
        $devicePlatform = $request->query('device_platform');
        $versionCode = (int) $request->query('version_code');

        if (!$devicePlatform || !$versionCode) {
            return response()->json([
                'success' => false,
                'message' => 'device_platform dan version_code wajib diisi',
            ], 422);
        }

        $result = AppUpdate::where("device_platform", $devicePlatform)
                            ->latest()
                            ->first();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Update record tidak ditemukan untuk platform ini',
            ], 404);
        }

        if ($versionCode >= (int) $result->version_code) {
            return response()->json([
                'success' => false,
                'message' => 'No new update available',
            ], 200);
        }

        return response()->json([
            'success'=> true,
            'message'=> 'APK files retrieved successfully',
            'data' => $result,
        ], 200);
    }
}
