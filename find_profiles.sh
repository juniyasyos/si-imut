#!/bin/bash
cd /home/juni/projects/SIIMUT
php artisan tinker --execute="
\$profiles = App\Models\ImutProfile::whereHas('formTemplates.dailyReportResponses')->with('formTemplates')->limit(5)->get();
echo 'Found profiles with responses: ' . \$profiles->count() . PHP_EOL;
foreach(\$profiles as \$profile) {
    echo 'Slug: ' . \$profile->slug . ' | Version: ' . \$profile->version . PHP_EOL;
    echo 'URL: /siimut/imut-profiles/' . \$profile->slug . '/daily-reports' . PHP_EOL;
    echo '---' . PHP_EOL;
}
"