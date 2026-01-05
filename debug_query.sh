#!/bin/bash
cd /home/juni/projects/SIIMUT
php artisan tinker --execute="
\$profileId = App\Models\ImutProfile::where('slug', '/version-2024-q1-f4d7ce60-f584-4d33-bbb4-14fae4428c56')->value('id');
echo 'Profile ID: ' . \$profileId . PHP_EOL;

\$responses = App\Models\DailyReportResponse::whereHas('formTemplate', function(\$query) use (\$profileId) {
    \$query->where('imut_profile_id', \$profileId);
})->with(['unitKerja', 'submittedBy', 'formTemplate'])->limit(5)->get();

echo 'Found responses: ' . \$responses->count() . PHP_EOL;
foreach(\$responses as \$response) {
    echo 'Response ID: ' . \$response->id . ' | Date: ' . \$response->report_date . ' | Score: ' . \$response->total_score . ' | Unit: ' . (\$response->unitKerja->unit_name ?? 'No Unit') . PHP_EOL;
}
"