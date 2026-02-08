<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\LaporanImut;
use App\Models\ImutPenilaian;
use Illuminate\Support\Facades\DB;

echo "=== TEST CLEANUP QUERY EXECUTION ===\n\n";

// Test 1: Create test laporan
$laporan = LaporanImut::create([
    'tahun' => 2024,
    'bulan' => 2,
    'periode_awal' => '2024-02-01',
    'periode_akhir' => '2024-02-29',
    'status' => 'draft'
]);

echo "✅ Created Test Laporan ID: {$laporan->id}\n\n";

// Test 2: Get cleanup query results for existing laporan (ID 37)
$query = DB::table('laporan_unit_kerjas')
    ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
    ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
    ->where('laporan_unit_kerjas.laporan_imut_id', 37)
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
            ->from('imut_data_unit_kerja')
            ->whereColumn('imut_data_unit_kerja.unit_kerja_id', 'laporan_unit_kerjas.unit_kerja_id')
            ->whereColumn('imut_data_unit_kerja.imut_data_id', 'imut_profil.imut_data_id');
    })
    ->select('imut_penilaians.id', 'imut_profil.imut_data_id', 'laporan_unit_kerjas.unit_kerja_id')
    ->take(10);

$orphaned = $query->get();

echo "📊 Sample Orphaned Records (First 10 of 45):\n";
foreach ($orphaned as $record) {
    echo "   - ID: {$record->id}, IMUT: {$record->imut_data_id}, Unit: {$record->unit_kerja_id}\n";
}

echo "\n✅ Query executed successfully!\n";
echo "   Total orphaned records: " . DB::table('laporan_unit_kerjas')
    ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
    ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
    ->where('laporan_unit_kerjas.laporan_imut_id', 37)
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
            ->from('imut_data_unit_kerja')
            ->whereColumn('imut_data_unit_kerja.unit_kerja_id', 'laporan_unit_kerjas.unit_kerja_id')
            ->whereColumn('imut_data_unit_kerja.imut_data_id', 'imut_profil.imut_data_id');
    })
    ->count() . "\n\n";

echo "=== TEST COMPLETE ===\n";
