<?php

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;

// Find ImutProfile with DailyReportResponse data
$profilesWithResponses = ImutProfile::whereHas('formTemplates.dailyReportResponses')
    ->with(['formTemplates' => function ($query) {
        $query->withCount('dailyReportResponses');
    }])
    ->get()
    ->map(function ($profile) {
        $totalResponses = $profile->formTemplates->sum('daily_report_responses_count');
        return [
            'slug' => $profile->slug,
            'version' => $profile->version,
            'responses_count' => $totalResponses,
            'url' => "/siimut/imut-profiles/{$profile->slug}/daily-reports"
        ];
    })
    ->sortByDesc('responses_count');

echo "ImutProfiles with Daily Report Responses:\n";
echo "==========================================\n";

foreach ($profilesWithResponses as $profile) {
    echo "Slug: {$profile['slug']}\n";
    echo "Version: {$profile['version']}\n";
    echo "Responses: {$profile['responses_count']}\n";
    echo "URL: {$profile['url']}\n";
    echo "---\n";
}

if ($profilesWithResponses->isEmpty()) {
    echo "No ImutProfiles found with Daily Report Responses!\n";
    echo "Please check if seeder was run correctly.\n";
}
