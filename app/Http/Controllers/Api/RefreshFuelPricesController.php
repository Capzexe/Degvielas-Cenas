<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RefreshFuelPricesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! Cache::add('fuel-prices:manual-refresh', true, now()->addMinutes(10))) {
            return response()->json([
                'message' => 'Cenas nesen jau tika atjauninātas. Lūdzu, mēģini vēlreiz pēc dažām minūtēm.',
            ], 429);
        }

        try {
            $exitCode = Artisan::call('fetch:fuel-prices');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Neizdevās atjaunināt cenas. Lūdzu, mēģini vēlreiz vēlāk.',
            ], 500);
        }

        if ($exitCode !== 0) {
            return response()->json([
                'message' => 'Neizdevās atjaunināt cenas. Lūdzu, mēģini vēlreiz vēlāk.',
            ], 500);
        }

        return response()->json([
            'message' => 'Degvielas cenas ir atjauninātas.',
        ]);
    }
}
