<?php

use App\Http\Controllers\Api\ChartController;
use Illuminate\Support\Facades\Route;

Route::get('test-chart', function () {
    $controller = new ChartController();
    $imutData = \App\Models\ImutData::first();

    if (!$imutData) {
        return response()->json(['error' => 'No IMUT data found']);
    }

    $request = new \Illuminate\Http\Request();
    $request->merge([
        'year' => 2025,
        'start_month' => 1,
        'end_month' => 12,
        'show_benchmark' => true
    ]);

    try {
        return $controller->getImutChartData($request, $imutData);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
