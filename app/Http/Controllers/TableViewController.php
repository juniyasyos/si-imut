<?php

namespace App\Http\Controllers;

use App\Services\DailyReport\TableViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableViewController extends Controller
{
    public function __construct(protected TableViewService $tableViewService)
    {
    }

    /**
     * Display the table view page.
     */
    public function index(Request $request)
    {
        return view('table-view');
    }

    /**
     * Return table data for the selected period, template, and unit.
     */
    public function getData(Request $request)
    {
        $payload = $this->tableViewService->buildTableViewData(
            Auth::user(),
            $request->integer('form_template_id') ?: null,
            $request->integer('unit_kerja_id') ?: null,
            $request->input('period', now()->format('Y-m'))
        );

        return response()->json($payload);
    }
}
