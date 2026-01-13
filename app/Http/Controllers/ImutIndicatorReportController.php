<?php

namespace App\Http\Controllers;

use App\Models\ImutData;
use App\Models\LaporanImut;
use Illuminate\Http\Request;

class ImutIndicatorReportController extends Controller
{
    public function index()
    {
        // Redirect to a default or list page, or show error
        abort(404, 'Please specify an indicator and period.');
    }

    public function show(Request $request, $indicator, $periode)
    {
        // Find the ImutData by slug or id
        $imutData = ImutData::where('slug', $indicator)->orWhere('id', $indicator)->firstOrFail();

        // Find the LaporanImut
        $laporan = LaporanImut::findOrFail($periode);

        // Return the HTML view with Alpine.js
        return view('reports.imut-indicator-report', compact('imutData', 'laporan'));
    }

    public function detail(Request $request, $indicator, $periode, $filter_periode = null, $catatan = null)
    {
        // Similar to show, but with additional parameters
        return $this->show($request, $indicator, $periode);
    }
}
