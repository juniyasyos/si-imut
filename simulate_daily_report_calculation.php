<?php

/**
 * Simulasi Perhitungan Daily Report → IMUT Penilaian
 * 
 * File ini mensimulasikan bagaimana DailyReportAggregationService bekerja
 * tanpa perlu database atau framework Laravel.
 * 
 * Run: php simulate_daily_report_calculation.php
 */

// Simulasi data Daily Reports
$dailyReports = [
    ['date' => '2025-01-01', 'compliance_score' => 100.0],
    ['date' => '2025-01-02', 'compliance_score' => 95.0],
    ['date' => '2025-01-03', 'compliance_score' => 100.0],
    ['date' => '2025-01-05', 'compliance_score' => 88.0],
    ['date' => '2025-01-08', 'compliance_score' => 100.0],
    ['date' => '2025-01-10', 'compliance_score' => 92.5],
    ['date' => '2025-01-12', 'compliance_score' => 100.0],
    ['date' => '2025-01-15', 'compliance_score' => 100.0],
    ['date' => '2025-01-18', 'compliance_score' => 97.0],
    ['date' => '2025-01-20', 'compliance_score' => 100.0],
    ['date' => '2025-01-25', 'compliance_score' => 100.0],
    ['date' => '2025-01-28', 'compliance_score' => 100.0],
    ['date' => '2025-01-30', 'compliance_score' => 89.0],
];

$periodStart = '2025-01-01';
$periodEnd = '2025-01-31';

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║      SIMULASI PERHITUNGAN DAILY REPORT → IMUT PENILAIAN      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "📊 DATA SIMULASI:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Indikator    : Hand Hygiene Compliance\n";
echo "Unit Kerja   : ICU\n";
echo "Periode      : {$periodStart} s/d {$periodEnd}\n";
echo "Total Hari   : 31 hari\n";
echo "Total Laporan: " . count($dailyReports) . " laporan\n\n";

// Hitung Numerator dan Denominator
$denominator = count($dailyReports);
$numerator = 0;
$breakdown = [];

echo "📈 BREAKDOWN PERHITUNGAN:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "No. | Tanggal    | Compliance | Perfect? | Status\n";
echo "─────────────────────────────────────────────────────────────\n";

$no = 1;
foreach ($dailyReports as $report) {
    $score = $report['compliance_score'];
    $isPerfect = $score >= 100;

    if ($isPerfect) {
        $numerator++;
        $status = '✅ Masuk N';
    } else {
        $status = '❌ Tidak';
    }

    printf(
        "%2d. | %s | %6.2f%%   | %-8s | %s\n",
        $no++,
        $report['date'],
        $score,
        $isPerfect ? 'Ya' : 'Tidak',
        $status
    );

    $breakdown[] = [
        'date' => $report['date'],
        'compliance_score' => $score,
        'is_perfect' => $isPerfect,
    ];
}

// Hitung persentase
$percentage = $denominator > 0
    ? round(($numerator / $denominator) * 100, 2)
    : 0;

// Cari missing dates
$start = new DateTime($periodStart);
$end = new DateTime($periodEnd);
$reportedDates = array_column($dailyReports, 'date');
$missingDates = [];

$current = clone $start;
while ($current <= $end) {
    $dateStr = $current->format('Y-m-d');
    if (!in_array($dateStr, $reportedDates)) {
        $missingDates[] = $dateStr;
    }
    $current->modify('+1 day');
}

$totalDays = $start->diff($end)->days + 1;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                        HASIL AKHIR                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "📊 NILAI N/D:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Numerator (N)    : {$numerator} laporan\n";
echo "                   (Laporan dengan compliance 100%)\n\n";
echo "Denominator (D)  : {$denominator} laporan\n";
echo "                   (Total laporan yang diinput)\n\n";
echo "Persentase (%)   : {$percentage}%\n";
echo "                   Formula: (N/D) × 100 = ({$numerator}/{$denominator}) × 100\n\n";

echo "📅 INFORMASI PERIODE:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "Total Hari Periode: {$totalDays} hari\n";
echo "Laporan Diinput   : {$denominator} laporan\n";
echo "Laporan Perfect   : {$numerator} laporan\n";
echo "Tanggal Kosong    : " . count($missingDates) . " hari\n\n";

if (!empty($missingDates)) {
    echo "⚠️  TANGGAL TANPA LAPORAN:\n";
    echo "─────────────────────────────────────────────────────────────\n";

    // Tampilkan dalam baris 5 tanggal
    $chunks = array_chunk($missingDates, 5);
    foreach ($chunks as $chunk) {
        echo "   " . implode(', ', $chunk) . "\n";
    }
    echo "\n";
}

echo "💾 DATA YANG AKAN DISIMPAN:\n";
echo "─────────────────────────────────────────────────────────────\n";
$savedData = [
    'numerator_value' => $numerator,
    'denominator_value' => $denominator,
    'is_auto_calculated' => true,
    'calculation_metadata' => [
        'calculated_at' => date('Y-m-d H:i:s'),
        'total_days_in_period' => $totalDays,
        'reports_submitted' => $denominator,
        'reports_perfect' => $numerator,
        'missing_dates_count' => count($missingDates),
        'form_template_id' => 123,
        'form_template_title' => 'Hand Hygiene Compliance',
    ],
];
echo json_encode($savedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Interpretasi
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                         INTERPRETASI                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

if ($percentage >= 90) {
    echo "✅ SANGAT BAIK ({$percentage}%)\n";
    echo "   Compliance sangat tinggi, pertahankan!\n";
} elseif ($percentage >= 75) {
    echo "✅ BAIK ({$percentage}%)\n";
    echo "   Compliance cukup baik, tingkatkan konsistensi.\n";
} elseif ($percentage >= 50) {
    echo "⚠️  CUKUP ({$percentage}%)\n";
    echo "   Perlu peningkatan untuk mencapai target.\n";
} else {
    echo "❌ PERLU PERHATIAN SERIUS ({$percentage}%)\n";
    echo "   Compliance rendah, perlu action plan segera.\n";
}

if (count($missingDates) > ($totalDays / 2)) {
    echo "\n⚠️  PERINGATAN: Lebih dari 50% hari tidak ada laporan!\n";
    echo "   Tingkatkan disiplin pengisian daily report.\n";
}

echo "\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "📖 CARA MEMBACA HASIL:\n\n";
echo "1. Numerator (N):\n";
echo "   Menghitung berapa LAPORAN yang compliance-nya TEPAT 100%.\n";
echo "   Kalau 99.9% tidak dihitung, harus persis 100%.\n\n";
echo "2. Denominator (D):\n";
echo "   Total LAPORAN yang DIINPUT (bukan total hari dalam periode).\n";
echo "   Tanggal tanpa laporan = diabaikan, bukan dihitung 0.\n\n";
echo "3. Persentase:\n";
echo "   Menunjukkan konsistensi mencapai perfect compliance.\n";
echo "   Semakin tinggi = semakin konsisten.\n\n";
echo "4. Missing Dates:\n";
echo "   Tanggal-tanggal yang tidak ada daily report.\n";
echo "   Perlu monitoring untuk meningkatkan coverage.\n";
echo "─────────────────────────────────────────────────────────────\n\n";

// Simulasi tambahan: berbagai skenario
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    PERBANDINGAN SKENARIO                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$scenarios = [
    [
        'name' => 'Perfect Compliance',
        'numerator' => 31,
        'denominator' => 31,
        'description' => '31 laporan diinput, semua 100%',
    ],
    [
        'name' => 'Good Input, Mixed Compliance',
        'numerator' => 20,
        'denominator' => 30,
        'description' => '30 laporan diinput, 20 perfect',
    ],
    [
        'name' => 'Few Reports, All Perfect',
        'numerator' => 10,
        'denominator' => 10,
        'description' => 'Hanya 10 laporan, tapi semua perfect',
    ],
    [
        'name' => 'Many Reports, Few Perfect',
        'numerator' => 5,
        'denominator' => 31,
        'description' => '31 laporan diinput, hanya 5 perfect',
    ],
    [
        'name' => 'No Perfect Report',
        'numerator' => 0,
        'denominator' => 20,
        'description' => '20 laporan diinput, tidak ada yang 100%',
    ],
];

echo "Skenario                          | N  | D  | %      | Catatan\n";
echo "─────────────────────────────────────────────────────────────\n";

foreach ($scenarios as $scenario) {
    $pct = $scenario['denominator'] > 0
        ? round(($scenario['numerator'] / $scenario['denominator']) * 100, 2)
        : 0;

    $icon = $pct >= 80 ? '✅' : ($pct >= 50 ? '⚠️ ' : '❌');

    printf(
        "%-32s | %2d | %2d | %5.1f%% | %s %s\n",
        $scenario['name'],
        $scenario['numerator'],
        $scenario['denominator'],
        $pct,
        $icon,
        $scenario['description']
    );
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                    SIMULASI SELESAI\n";
echo "═══════════════════════════════════════════════════════════════\n";
