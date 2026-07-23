<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $dbOk = false;
        }

        $storageOk = is_writable(storage_path());

        $status = ($dbOk && $storageOk) ? 'ok' : 'degraded';

        return response()->json([
            'status' => $status,
            'app' => config('app.name', 'AppGov'),
            'environment' => config('app.env'),
            'timestamp_utc' => now()->toIso8601String(),
            'timestamp_wat' => now()->timezone('Africa/Douala')->toIso8601String(),
            'checks' => [
                'database' => $dbOk ? 'healthy' : 'unhealthy',
                'storage' => $storageOk ? 'healthy' : 'unhealthy',
            ],
        ], $status === 'ok' ? 200 : 503);
    }
}
