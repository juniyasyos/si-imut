<?php

return [
    'name' => 'validator_pic',
    'label' => 'Unit Kerja - Validator PIC Indikator Mutu',
    'guard_name' => 'web',
    'description' => 'Role untuk pengguna Unit Kerja sebagai Validator atau PIC Indikator Mutu, akses terbatas pada validasi dan penilaian IMUT.',
    'permissions' => [
        // Pages
        'page_MyProfilePage',

        // Widget
        'widget_UnitKerjaInfo',
        'widget_StatsForUnitKerja',
        'widget_LaporanLatestWidget',
        'widget_LaporanUnitWidget',

        // Folder
        'view_any_folder::custom',
        'view_folder::custom',
        'view_by_unit_kerja_folder::custom',
        'create_folder::custom',
        'update_folder::custom',
        'delete_folder::custom',

        // Media
        'view_media::custom',
        'view_by_unit_kerja_media::custom',
        'create_media::custom',
        'update_media::custom',

        // IMUT Data
        'view_imut::data',
        'view_any_imut::data',
        'create_imut::data',
        'update_imut::data',
        'view_by_unit_kerja_imut::data',
        'delete_imut::data',
        'force_delete_imut::data',
        'force_delete_any_imut::data',
        'restore_imut::data',
        'restore_any_imut::data',


        // IMUT Profile
        'view_imut::profile',
        'view_any_imut::profile',
        'create_imut::profile',
        'update_imut::profile',
        'restore_imut::profile',
        'restore_any_imut::profile',
        'delete_imut::profile',
        'delete_any_imut::profile',
        'replicate_imut::profile',
        'reorder_imut::profile',
        'force_delete_imut::profile',
        'force_delete_any_imut::profile',

        // IMUT Category
        'view_any_imut::category',
        'view_imut::category',

        // Laporan
        'view_any_laporan::imut',

        // Penilaian
        'view_any_imut::penilaian',
        'view_imut::penilaian',
        'update_imut::penilaian',
        'view_imut_penilaian_imut::penilaian',
        'update_numerator_denominator_imut::penilaian',

        // Report
        'view_unit_kerja_report_detail_laporan::imut',
        'view_imut_data_report_detail_laporan::imut',
        'update_numerator_denominator_imut::penilaian',
        'create_recommendation_penilaian_imut::penilaian',

        // Daily Report Validation
        'validate_reports',
    ],
];
