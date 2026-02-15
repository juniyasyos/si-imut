<?php

return [
    'name' => 'super_admin',
    'label' => 'Super Admin',
    'guard_name' => 'web',
    'description' => 'Role untuk Super Administrator dengan akses penuh ke semua fitur.',
    'permissions' => [
        // Semua permissions dari role lain, plus yang spesifik
        // Pages
        'page_MyProfilePage',

        // Widget
        'widget_LaporanLatestWidget',
        'widget_DashboardSiimutOverview',
        'widget_ImutCapaianWidget',
        'widget_ImutTercapai',
        'widget_imutDataCompletionChart',
        'widget_UnitKerjaCompletionChart',

        // User Management
        'view_user',
        'view_any_user',
        'create_user',
        'update_user',
        'delete_user',
        'delete_any_user',
        'export_user',

        // Role Management
        'view_role',
        'view_any_role',
        'create_role',
        'update_role',
        'delete_role',
        'delete_any_role',

        // Unit Kerja
        'view_any_unit::kerja',
        'view_unit::kerja',
        'create_unit::kerja',
        'update_unit::kerja',
        'delete_unit::kerja',
        'delete_any_unit::kerja',
        'attach_user_to_unit_kerja_unit::kerja',
        'attach_imut_data_to_unit_kerja_unit::kerja',

        // IMUT Category
        'view_any_imut::category',
        'view_imut::category',
        'create_imut::category',
        'update_imut::category',
        'delete_imut::category',
        'delete_any_imut::category',
        'force_delete_imut::category',
        'force_delete_any_imut::category',
        'restore_imut::category',
        'restore_any_imut::category',

        // IMUT Data
        'view_imut::data',
        'view_any_imut::data',
        'create_imut::data',
        'update_imut::data',
        'delete_imut::data',
        'delete_any_imut::data',
        'restore_imut::data',
        'restore_any_imut::data',
        'export_imut::data',

        // IMUT Profile
        'view_imut::profile',
        'view_any_imut::profile',
        'create_imut::profile',
        'update_imut::profile',
        'delete_imut::profile',
        'delete_any_imut::profile',
        'force_delete_imut::profile',
        'force_delete_any_imut::profile',
        'restore_imut::profile',
        'restore_any_imut::profile',
        'force_editable_imut::profile',

        // IMUT Penilaian
        'view_imut::penilaian',
        'view_any_imut::penilaian',
        'create_imut::penilaian',
        'update_imut::penilaian',
        'delete_imut::penilaian',
        'delete_any_imut::penilaian',

        // Laporan IMUT
        'view_laporan::imut',
        'view_any_laporan::imut',
        'create_laporan::imut',
        'update_laporan::imut',
        'delete_laporan::imut',
        'delete_any_laporan::imut',

        // Daily Report
        'view_daily::report::entry',
        'view_any_daily::report::entry',
        'create_daily::report::entry',
        'update_daily::report::entry',
        'delete_daily::report::entry',
        'delete_any_daily::report::entry',

        // Region Type
        'view_region::type',
        'view_any_region::type',
        'create_region::type',
        'update_region::type',
        'delete_region::type',
        'delete_any_region::type',

        // Activity Log
        'view_activity::log',
        'view_any_activity::log',

        // Backup
        'view_backup',
        'view_any_backup',
        'create_backup',
        'delete_backup',
        'delete_any_backup',
        'download_backup',

        // Settings
        'view_setting',
        'view_any_setting',
        'create_setting',
        'update_setting',
        'delete_setting',
        'delete_any_setting',

        // Media
        'view_media',
        'view_any_media',
        'create_media',
        'update_media',
        'delete_media',
        'delete_any_media',

        // Folder
        'view_folder',
        'view_any_folder',
        'create_folder',
        'update_folder',
        'delete_folder',
        'delete_any_folder',

        // Khusus Super Admin: Update form dengan existing responses
        'update_form_with_existing_responses',
    ],
];
