<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class CategoryReportPdfController extends Controller
{
    /**
     * Generate a PDF version of the category report using Browsershot.
     *
     * Accepts exactly the same query parameters as the normal
     * CategoryReportController@show route, then navigates to that URL
     * internally and captures the rendered HTML as PDF.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        // rebuild query string so we can call the public route
        $params = $request->query();
        $url = route('laporan.indikator-mutu.by-category', $params);

        // configure browsershot
        $pdfContents = Browsershot::url($url)
            ->windowSize(1280, 1024)
            ->margins(10, 10, 10, 10)
            ->showBackground()           // include background colors/images
            ->waitUntilNetworkIdle()     // ensure charts/data loaded
            ->format('A4')
            ->landscape();               // default; user can override via query

        // enforce orientation if provided
        if ($request->query('orientation') === 'portrait') {
            $pdfContents->portrait();
        }

        $pdf = $pdfContents->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="laporan-kategori.pdf"');
    }
}
