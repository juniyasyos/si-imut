<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/manifest.json' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pwa.manifest',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/offline' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'pwa.offline',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/open' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.openhandler',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/stylesheets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.css',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/javascript' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.js',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/queries/explain' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.queries.explain',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daily-report-dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.daily-report-dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daily-report-periods' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.daily-report-periods',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/pwa-settings-page' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.pwa-settings-page',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/backups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.backups',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/my-profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.my-profile',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/activitylogs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.activitylogs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daily-report-entries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.daily-report-entries.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/daily-report-entries/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.daily-report-entries.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/folders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.folders.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-categories.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-categories.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-category-resource/schema/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-category-resource/schema/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-category-resource/tables/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-category-resource/tables/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-datas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-datas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-datas/bencmarkings/region-type' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.bencmarking-region-type',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-datas/overview/unit-kerja' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.overview-unit-kerja',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-datas/overview/summary-imut-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.overview-imut-data',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-penilaians' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-penilaians.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-penilaian-resource/schema/imut-penilaians' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-penilaian-resource.schema.imut-penilaians.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-profiles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-profile-resource/schema/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-profile-resource.schema.imut-profiles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/imut-profile-resource/tables/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-profile-resource.tables.imut-profiles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts/unit-kerja-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.unit-kerja-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts/unit-kerja-imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.unit-kerja-imut-data-report-detail',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts/imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.imut-data-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/laporan-imuts/imut-data-unit-kerja-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.imut-data-unit-kerja-report-detail',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/media' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.media.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/region-type-bencmarkings/bencmarkings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.region-type-bencmarkings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/shield/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.shield.roles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/shield/roles/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.shield.roles.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/unit-kerjas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.unit-kerjas.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/unit-kerjas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.unit-kerjas.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/user-resource/schema/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.schema.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/user-resource/schema/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.schema.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/user-resource/tables/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.tables.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/user-resource/tables/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.tables.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/two-factor-authentication' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.two-factor',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.sso.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/callback' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.sso.callback',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'livewire.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/livewire.min.js' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::8an0xXvCSLswRxkU',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/livewire.min.js.map' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Nptg1dPRlMWuexBw',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/upload-file' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'livewire.upload-file',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/resend/webhook' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'resend.webhook',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/filament-impersonate/leave' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament-impersonate.leave',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::l7cDVzW4kgtDhtcS',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/greeting' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::inPrh0be7ac2fuXw',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/greeting/quotes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yvW5hsCRIO8JR8rA',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::K0cGr5OAnrjhc51q',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/print/preview/imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'print.preview.imut-data-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/print/preview/imut-indicator-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'print.preview.imut-indicator-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/print/imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'print.imut-data-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/print/imut-indicator-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'print.imut-indicator-report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sso/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sso.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sso/callback' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sso.callback',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sso/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sso.status',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/debug-session' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debug.session',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/docs/api' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'scramble.docs.ui',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/docs/api.json' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'scramble.docs.document',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/oauth/callback/([^/]++)(*:105)|/f(?|ilament(?|/(?|exports/([^/]++)/download(*:157)|imports/([^/]++)/failed\\-rows/download(*:203))|\\-excel/(.*)(*:224))|olders/(?|([^/]++)(*:251)|media\\-name\\=([^/]++)(*:280)))|/a(?|ctivitylogs/([^/]++)(*:315)|pi/greeting/quote(?:/([^/]++))?(*:354))|/daily\\-report\\-entries/([^/]++)(?|(*:398)|/edit(*:411))|/imut\\-(?|categor(?|ies/([^/]++)/edit(*:457)|y\\-resource/(?|schema/imut\\-categories/([^/]++)/edit(*:517)|tables/imut\\-categories/([^/]++)/edit(*:562)))|datas/(?|edit\\=([^/]++)(*:595)|([^/]++)/(?|profile/(?|create(*:632)|edit\\=([^/]++)(*:654))|form\\-builder(?|(*:679)|/preview(*:695)))))|/l(?|aporan\\-imuts/([^/]++)/(?|edit(*:742)|penilaian\\-edit\\=([^/]++)(*:775))|ivewire/preview\\-file/([^/]++)(*:814))|/s(?|hield/roles/([^/]++)(?|(*:851)|/edit(*:864))|torage/(.*)(*:884))|/u(?|nit\\-kerjas/([^/]++)/edit(*:923)|ser(?|s/([^/]++)(?|(*:950)|/edit(*:963))|\\-resource/(?|schema/users/([^/]++)(?|(*:1010)|/edit(*:1024))|tables/users/([^/]++)(?|(*:1058)|/edit(*:1072))))))/?$}sDu',
    ),
    3 => 
    array (
      39 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.clockwork',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      73 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.cache.delete',
            'tags' => NULL,
          ),
          1 => 
          array (
            0 => 'key',
            1 => 'tags',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      105 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'oauth.callback',
          ),
          1 => 
          array (
            0 => 'provider',
          ),
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      157 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.exports.download',
          ),
          1 => 
          array (
            0 => 'export',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      203 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.imports.failed-rows.download',
          ),
          1 => 
          array (
            0 => 'import',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      224 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament-excel-download',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      251 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.folders.view',
          ),
          1 => 
          array (
            0 => 'folder',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      280 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.folders.media',
          ),
          1 => 
          array (
            0 => 'folderName',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      315 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.activitylogs.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      354 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xBRseemYLtusTeu3',
            'timeKey' => NULL,
          ),
          1 => 
          array (
            0 => 'timeKey',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      398 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.daily-report-entries.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      411 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.daily-report-entries.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      457 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-categories.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      517 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      562 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      595 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      632 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.create-profile',
          ),
          1 => 
          array (
            0 => 'imutDataSlug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      654 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.edit-profile',
          ),
          1 => 
          array (
            0 => 'imutDataSlug',
            1 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      679 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.manage-form-builder',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      695 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.imut-datas.preview-form',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      742 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      775 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.laporan-imuts.edit-penilaian',
          ),
          1 => 
          array (
            0 => 'laporanSlug',
            1 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      814 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'livewire.preview-file',
          ),
          1 => 
          array (
            0 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      851 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.shield.roles.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      864 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.shield.roles.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      884 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      923 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.unit-kerjas.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      950 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.users.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      963 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.users.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1010 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.schema.users.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1024 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.schema.users.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1058 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.tables.users.view',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1072 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.user-resource.tables.users.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'pwa.manifest' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'manifest.json',
      'action' => 
      array (
        'middleware' => 
        array (
        ),
        'uses' => 'Juniyasyos\\FilamentPWA\\Http\\Controllers\\PWAController@index',
        'controller' => 'Juniyasyos\\FilamentPWA\\Http\\Controllers\\PWAController@index',
        'as' => 'pwa.manifest',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'pwa.offline' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'offline',
      'action' => 
      array (
        'middleware' => 
        array (
        ),
        'uses' => 'Juniyasyos\\FilamentPWA\\Http\\Controllers\\PWAController@offline',
        'controller' => 'Juniyasyos\\FilamentPWA\\Http\\Controllers\\PWAController@offline',
        'as' => 'pwa.offline',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.openhandler' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/open',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'as' => 'debugbar.openhandler',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.clockwork' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/clockwork/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'as' => 'debugbar.clockwork',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.css' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/stylesheets',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'as' => 'debugbar.assets.css',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.js' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/javascript',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'as' => 'debugbar.assets.js',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.cache.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '_debugbar/cache/{key}/{tags?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'as' => 'debugbar.cache.delete',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.queries.explain' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_debugbar/queries/explain',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'as' => 'debugbar.queries.explain',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'oauth.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'oauth/callback/{provider}',
      'action' => 
      array (
        'uses' => 'DutchCodingCompany\\FilamentSocialite\\Http\\Controllers\\SocialiteLoginController@processCallback',
        'controller' => 'DutchCodingCompany\\FilamentSocialite\\Http\\Controllers\\SocialiteLoginController@processCallback',
        'middleware' => 
        array (
          0 => 'DutchCodingCompany\\FilamentSocialite\\Http\\Middleware\\PanelFromUrlQuery',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          5 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
        ),
        'as' => 'oauth.callback',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.exports.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament/exports/{export}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'filament.actions',
        ),
        'uses' => 'Filament\\Actions\\Exports\\Http\\Controllers\\DownloadExport@__invoke',
        'controller' => 'Filament\\Actions\\Exports\\Http\\Controllers\\DownloadExport',
        'as' => 'filament.exports.download',
        'namespace' => NULL,
        'prefix' => 'filament',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.imports.failed-rows.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament/imports/{import}/failed-rows/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'filament.actions',
        ),
        'uses' => 'Filament\\Actions\\Imports\\Http\\Controllers\\DownloadImportFailureCsv@__invoke',
        'controller' => 'Filament\\Actions\\Imports\\Http\\Controllers\\DownloadImportFailureCsv',
        'as' => 'filament.imports.failed-rows.download',
        'namespace' => NULL,
        'prefix' => 'filament',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\LogoutController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\LogoutController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'App\\Filament\\Pages\\Dashboard@__invoke',
        'controller' => 'App\\Filament\\Pages\\Dashboard',
        'as' => 'filament.admin.pages.dashboard',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.daily-report-dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-dashboard',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'App\\Filament\\Pages\\DailyReportDashboard@__invoke',
        'controller' => 'App\\Filament\\Pages\\DailyReportDashboard',
        'as' => 'filament.admin.pages.daily-report-dashboard',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.daily-report-periods' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-periods',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'App\\Filament\\Pages\\DailyReportPeriods@__invoke',
        'controller' => 'App\\Filament\\Pages\\DailyReportPeriods',
        'as' => 'filament.admin.pages.daily-report-periods',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.pwa-settings-page' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'pwa-settings-page',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'Juniyasyos\\FilamentPWA\\Filament\\Pages\\PWASettingsPage@__invoke',
        'controller' => 'Juniyasyos\\FilamentPWA\\Filament\\Pages\\PWASettingsPage',
        'as' => 'filament.admin.pages.pwa-settings-page',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.backups' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'backups',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Pages\\Backups@__invoke',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Pages\\Backups',
        'as' => 'filament.admin.pages.backups',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.my-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'my-profile',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'uses' => 'Jeffgreco13\\FilamentBreezy\\Pages\\MyProfilePage@__invoke',
        'controller' => 'Jeffgreco13\\FilamentBreezy\\Pages\\MyProfilePage',
        'as' => 'filament.admin.pages.my-profile',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.activitylogs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'activitylogs',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'Rmsramos\\Activitylog\\Resources\\ActivitylogResource\\Pages\\ListActivitylog@__invoke',
        'controller' => 'Rmsramos\\Activitylog\\Resources\\ActivitylogResource\\Pages\\ListActivitylog',
        'as' => 'filament.admin.resources.activitylogs.index',
        'namespace' => NULL,
        'prefix' => '/activitylogs',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.activitylogs.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'activitylogs/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'Rmsramos\\Activitylog\\Resources\\ActivitylogResource\\Pages\\ViewActivitylog@__invoke',
        'controller' => 'Rmsramos\\Activitylog\\Resources\\ActivitylogResource\\Pages\\ViewActivitylog',
        'as' => 'filament.admin.resources.activitylogs.view',
        'namespace' => NULL,
        'prefix' => '/activitylogs',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.daily-report-entries.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-entries',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\ListDailyReportEntries@__invoke',
        'controller' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\ListDailyReportEntries',
        'as' => 'filament.admin.resources.daily-report-entries.index',
        'namespace' => NULL,
        'prefix' => '/daily-report-entries',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.daily-report-entries.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-entries/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\CreateDailyReportEntry@__invoke',
        'controller' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\CreateDailyReportEntry',
        'as' => 'filament.admin.resources.daily-report-entries.create',
        'namespace' => NULL,
        'prefix' => '/daily-report-entries',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.daily-report-entries.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-entries/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\ViewDailyReportEntry@__invoke',
        'controller' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\ViewDailyReportEntry',
        'as' => 'filament.admin.resources.daily-report-entries.view',
        'namespace' => NULL,
        'prefix' => '/daily-report-entries',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.daily-report-entries.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'daily-report-entries/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\EditDailyReportEntry@__invoke',
        'controller' => 'App\\Filament\\Resources\\DailyReportEntryResource\\Pages\\EditDailyReportEntry',
        'as' => 'filament.admin.resources.daily-report-entries.edit',
        'namespace' => NULL,
        'prefix' => '/daily-report-entries',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.folders.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'folders',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\FolderCustomResource\\Pages\\ListFoldersCustom@__invoke',
        'controller' => 'App\\Filament\\Resources\\FolderCustomResource\\Pages\\ListFoldersCustom',
        'as' => 'filament.admin.resources.folders.index',
        'namespace' => NULL,
        'prefix' => '/folders',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.folders.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'folders/{folder}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'Juniyasyos\\FilamentMediaManager\\Resources\\FolderResource\\Pages\\ViewFolder@__invoke',
        'controller' => 'Juniyasyos\\FilamentMediaManager\\Resources\\FolderResource\\Pages\\ViewFolder',
        'as' => 'filament.admin.resources.folders.view',
        'namespace' => NULL,
        'prefix' => '/folders',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.folders.media' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'folders/media-name={folderName}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\MediaCustomResource\\Pages\\ListMediaCustom@__invoke',
        'controller' => 'App\\Filament\\Resources\\MediaCustomResource\\Pages\\ListMediaCustom',
        'as' => 'filament.admin.resources.folders.media',
        'namespace' => NULL,
        'prefix' => '/folders',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories',
        'as' => 'filament.admin.resources.imut-categories.index',
        'namespace' => NULL,
        'prefix' => '/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory',
        'as' => 'filament.admin.resources.imut-categories.create',
        'namespace' => NULL,
        'prefix' => '/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory',
        'as' => 'filament.admin.resources.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => '/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.schema.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/schema/imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories',
        'as' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.index',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/schema/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.schema.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/schema/imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory',
        'as' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.create',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/schema/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.schema.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/schema/imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory',
        'as' => 'filament.admin.resources.imut-category-resource.schema.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/schema/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.tables.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/tables/imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\ListImutCategories',
        'as' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.index',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/tables/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.tables.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/tables/imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\CreateImutCategory',
        'as' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.create',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/tables/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-category-resource.tables.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-category-resource/tables/imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutCategoryResource\\Pages\\EditImutCategory',
        'as' => 'filament.admin.resources.imut-category-resource.tables.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => '/imut-category-resource/tables/imut-categories',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\ListImutData@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\ListImutData',
        'as' => 'filament.admin.resources.imut-datas.index',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\CreateImutData@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\CreateImutData',
        'as' => 'filament.admin.resources.imut-datas.create',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/edit={record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\EditImutData@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\EditImutData',
        'as' => 'filament.admin.resources.imut-datas.edit',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'record' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.create-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/{imutDataSlug}/profile/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\CreateImutProfile@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\CreateImutProfile',
        'as' => 'filament.admin.resources.imut-datas.create-profile',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.edit-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/{imutDataSlug}/profile/edit={record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\EditImutProfile@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\EditImutProfile',
        'as' => 'filament.admin.resources.imut-datas.edit-profile',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.bencmarking-region-type' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/bencmarkings/region-type',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RegionTypeBencmarkingResource\\Pages\\ListRegionTypeBencmarkings@__invoke',
        'controller' => 'App\\Filament\\Resources\\RegionTypeBencmarkingResource\\Pages\\ListRegionTypeBencmarkings',
        'as' => 'filament.admin.resources.imut-datas.bencmarking-region-type',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.overview-unit-kerja' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/overview/unit-kerja',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\UnitKerjaOverview@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\UnitKerjaOverview',
        'as' => 'filament.admin.resources.imut-datas.overview-unit-kerja',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.overview-imut-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/overview/summary-imut-data',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\SummaryDiagram@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\SummaryDiagram',
        'as' => 'filament.admin.resources.imut-datas.overview-imut-data',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.manage-form-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/{record}/form-builder',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\ManageFormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\ManageFormBuilder',
        'as' => 'filament.admin.resources.imut-datas.manage-form-builder',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'record' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-datas.preview-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-datas/{record}/form-builder/preview',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\PreviewFormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\PreviewFormBuilder',
        'as' => 'filament.admin.resources.imut-datas.preview-form',
        'namespace' => NULL,
        'prefix' => '/imut-datas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'record' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-penilaians.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-penilaians',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian',
        'as' => 'filament.admin.resources.imut-penilaians.edit',
        'namespace' => NULL,
        'prefix' => '/imut-penilaians',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-penilaian-resource.schema.imut-penilaians.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-penilaian-resource/schema/imut-penilaians',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian',
        'as' => 'filament.admin.resources.imut-penilaian-resource.schema.imut-penilaians.edit',
        'namespace' => NULL,
        'prefix' => '/imut-penilaian-resource/schema/imut-penilaians',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles',
        'as' => 'filament.admin.resources.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => '/imut-profiles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-profile-resource.schema.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-profile-resource/schema/imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles',
        'as' => 'filament.admin.resources.imut-profile-resource.schema.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => '/imut-profile-resource/schema/imut-profiles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.imut-profile-resource.tables.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'imut-profile-resource/tables/imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListImutProfiles',
        'as' => 'filament.admin.resources.imut-profile-resource.tables.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => '/imut-profile-resource/tables/imut-profiles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ListLaporanImuts@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ListLaporanImuts',
        'as' => 'filament.admin.resources.laporan-imuts.index',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\CreateLaporanImut@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\CreateLaporanImut',
        'as' => 'filament.admin.resources.laporan-imuts.create',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\EditLaporanImut@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\EditLaporanImut',
        'as' => 'filament.admin.resources.laporan-imuts.edit',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'record' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.unit-kerja-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/unit-kerja-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\UnitKerjaReport@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\UnitKerjaReport',
        'as' => 'filament.admin.resources.laporan-imuts.unit-kerja-report',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.unit-kerja-imut-data-report-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/unit-kerja-imut-data-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\UnitKerjaImutDataReport@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\UnitKerjaImutDataReport',
        'as' => 'filament.admin.resources.laporan-imuts.unit-kerja-imut-data-report-detail',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.imut-data-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/imut-data-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ImutDataReport@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ImutDataReport',
        'as' => 'filament.admin.resources.laporan-imuts.imut-data-report',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.imut-data-unit-kerja-report-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/imut-data-unit-kerja-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ImutDataUnitKerjaReport@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\ImutDataUnitKerjaReport',
        'as' => 'filament.admin.resources.laporan-imuts.imut-data-unit-kerja-report-detail',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.laporan-imuts.edit-penilaian' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan-imuts/{laporanSlug}/penilaian-edit={record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutPenilaianResource\\Pages\\EditImutPenilaian',
        'as' => 'filament.admin.resources.laporan-imuts.edit-penilaian',
        'namespace' => NULL,
        'prefix' => '/laporan-imuts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.media.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'media',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\MediaCustomResource\\Pages\\ListMediaCustom@__invoke',
        'controller' => 'App\\Filament\\Resources\\MediaCustomResource\\Pages\\ListMediaCustom',
        'as' => 'filament.admin.resources.media.index',
        'namespace' => NULL,
        'prefix' => '/media',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.region-type-bencmarkings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'region-type-bencmarkings/bencmarkings',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RegionTypeBencmarkingResource\\Pages\\ListRegionTypeBencmarkings@__invoke',
        'controller' => 'App\\Filament\\Resources\\RegionTypeBencmarkingResource\\Pages\\ListRegionTypeBencmarkings',
        'as' => 'filament.admin.resources.region-type-bencmarkings.index',
        'namespace' => NULL,
        'prefix' => '/region-type-bencmarkings',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.shield.roles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shield/roles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RoleResource\\Pages\\ListRoles@__invoke',
        'controller' => 'App\\Filament\\Resources\\RoleResource\\Pages\\ListRoles',
        'as' => 'filament.admin.resources.shield.roles.index',
        'namespace' => NULL,
        'prefix' => '/shield/roles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.shield.roles.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shield/roles/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RoleResource\\Pages\\CreateRole@__invoke',
        'controller' => 'App\\Filament\\Resources\\RoleResource\\Pages\\CreateRole',
        'as' => 'filament.admin.resources.shield.roles.create',
        'namespace' => NULL,
        'prefix' => '/shield/roles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.shield.roles.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shield/roles/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RoleResource\\Pages\\ViewRole@__invoke',
        'controller' => 'App\\Filament\\Resources\\RoleResource\\Pages\\ViewRole',
        'as' => 'filament.admin.resources.shield.roles.view',
        'namespace' => NULL,
        'prefix' => '/shield/roles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.shield.roles.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'shield/roles/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\RoleResource\\Pages\\EditRole@__invoke',
        'controller' => 'App\\Filament\\Resources\\RoleResource\\Pages\\EditRole',
        'as' => 'filament.admin.resources.shield.roles.edit',
        'namespace' => NULL,
        'prefix' => '/shield/roles',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.unit-kerjas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'unit-kerjas',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\ListUnitKerja@__invoke',
        'controller' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\ListUnitKerja',
        'as' => 'filament.admin.resources.unit-kerjas.index',
        'namespace' => NULL,
        'prefix' => '/unit-kerjas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.unit-kerjas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'unit-kerjas/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\CreateUnitKerja@__invoke',
        'controller' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\CreateUnitKerja',
        'as' => 'filament.admin.resources.unit-kerjas.create',
        'namespace' => NULL,
        'prefix' => '/unit-kerjas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.unit-kerjas.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'unit-kerjas/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\EditUnitKerja@__invoke',
        'controller' => 'App\\Filament\\Resources\\UnitKerjaResource\\Pages\\EditUnitKerja',
        'as' => 'filament.admin.resources.unit-kerjas.edit',
        'namespace' => NULL,
        'prefix' => '/unit-kerjas',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'record' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers',
        'as' => 'filament.admin.resources.users.index',
        'namespace' => NULL,
        'prefix' => '/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser',
        'as' => 'filament.admin.resources.users.create',
        'namespace' => NULL,
        'prefix' => '/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser',
        'as' => 'filament.admin.resources.users.view',
        'namespace' => NULL,
        'prefix' => '/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser',
        'as' => 'filament.admin.resources.users.edit',
        'namespace' => NULL,
        'prefix' => '/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.schema.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/schema/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers',
        'as' => 'filament.admin.resources.user-resource.schema.users.index',
        'namespace' => NULL,
        'prefix' => '/user-resource/schema/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.schema.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/schema/users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser',
        'as' => 'filament.admin.resources.user-resource.schema.users.create',
        'namespace' => NULL,
        'prefix' => '/user-resource/schema/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.schema.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/schema/users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser',
        'as' => 'filament.admin.resources.user-resource.schema.users.view',
        'namespace' => NULL,
        'prefix' => '/user-resource/schema/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.schema.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/schema/users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser',
        'as' => 'filament.admin.resources.user-resource.schema.users.edit',
        'namespace' => NULL,
        'prefix' => '/user-resource/schema/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.tables.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/tables/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers',
        'as' => 'filament.admin.resources.user-resource.tables.users.index',
        'namespace' => NULL,
        'prefix' => '/user-resource/tables/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.tables.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/tables/users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\CreateUser',
        'as' => 'filament.admin.resources.user-resource.tables.users.create',
        'namespace' => NULL,
        'prefix' => '/user-resource/tables/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.tables.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/tables/users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ViewUser',
        'as' => 'filament.admin.resources.user-resource.tables.users.view',
        'namespace' => NULL,
        'prefix' => '/user-resource/tables/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.user-resource.tables.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'user-resource/tables/users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          12 => 'Filament\\Http\\Middleware\\Authenticate',
          13 => 'Jeffgreco13\\FilamentBreezy\\Middleware\\MustTwoFactor',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\EditUser',
        'as' => 'filament.admin.resources.user-resource.tables.users.edit',
        'namespace' => NULL,
        'prefix' => '/user-resource/tables/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.two-factor' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'two-factor-authentication',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'panel:admin',
          2 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          3 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          4 => 'Illuminate\\Session\\Middleware\\StartSession',
          5 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
          6 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          7 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          8 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          9 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          10 => 'BezhanSalleh\\FilamentLanguageSwitch\\Http\\Middleware\\SwitchLanguageLocale',
          11 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
        ),
        'uses' => 'Jeffgreco13\\FilamentBreezy\\Pages\\TwoFactorPage@__invoke',
        'controller' => 'Jeffgreco13\\FilamentBreezy\\Pages\\TwoFactorPage',
        'as' => 'filament.admin.auth.two-factor',
        'namespace' => NULL,
        'prefix' => '/',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'iam.sso.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.sso.login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'guard' => 'web',
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'iam.sso.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'callback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoCallbackController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoCallbackController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.sso.callback',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'guard' => 'web',
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'livewire.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'livewire/update',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\HandleRequests\\HandleRequests@handleUpdate',
        'controller' => 'Livewire\\Mechanisms\\HandleRequests\\HandleRequests@handleUpdate',
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'livewire.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::8an0xXvCSLswRxkU' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/livewire.min.js',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'controller' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'as' => 'generated::8an0xXvCSLswRxkU',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Nptg1dPRlMWuexBw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/livewire.min.js.map',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@maps',
        'controller' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@maps',
        'as' => 'generated::Nptg1dPRlMWuexBw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'livewire.upload-file' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'livewire/upload-file',
      'action' => 
      array (
        'uses' => 'Livewire\\Features\\SupportFileUploads\\FileUploadController@handle',
        'controller' => 'Livewire\\Features\\SupportFileUploads\\FileUploadController@handle',
        'as' => 'livewire.upload-file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'livewire.preview-file' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/preview-file/{filename}',
      'action' => 
      array (
        'uses' => 'Livewire\\Features\\SupportFileUploads\\FilePreviewController@handle',
        'controller' => 'Livewire\\Features\\SupportFileUploads\\FilePreviewController@handle',
        'as' => 'livewire.preview-file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament-excel-download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament-excel/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:259:"function (string $path) {
    $filename = \\substr($path, 37);
    $path = \\Illuminate\\Support\\Facades\\Storage::disk(\'filament-excel\')->path($path);

    return
        \\response()
            ->download($path, $filename)
            ->deleteFileAfterSend();
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000013de0000000000000000";}}',
        'middleware' => 
        array (
          0 => 'web',
          1 => 'signed',
        ),
        'as' => 'filament-excel-download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'resend.webhook' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'resend/webhook',
      'action' => 
      array (
        'domain' => NULL,
        'uses' => 'Resend\\Laravel\\Http\\Controllers\\WebhookController@handleWebhook',
        'controller' => 'Resend\\Laravel\\Http\\Controllers\\WebhookController@handleWebhook',
        'as' => 'resend.webhook',
        'namespace' => 'Resend\\Laravel\\Http\\Controllers',
        'prefix' => 'resend',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament-impersonate.leave' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament-impersonate/leave',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:296:"function() {
    if(!\\app(\\Lab404\\Impersonate\\Services\\ImpersonateManager::class)->isImpersonating()) {
        return \\redirect(\'/\');
    }

    \\app(\\Lab404\\Impersonate\\Services\\ImpersonateManager::class)->leave();

    return \\redirect(
        \\session()->pull(\'impersonate.back_to\')
    );
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000015cc0000000000000000";}}',
        'as' => 'filament-impersonate.leave',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::l7cDVzW4kgtDhtcS' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:77:"function (\\Illuminate\\Http\\Request $request) {
    return $request->user();
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000016390000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::l7cDVzW4kgtDhtcS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::inPrh0be7ac2fuXw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/greeting',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\GreetingController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\GreetingController@index',
        'namespace' => NULL,
        'prefix' => 'api/greeting',
        'where' => 
        array (
        ),
        'as' => 'generated::inPrh0be7ac2fuXw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xBRseemYLtusTeu3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/greeting/quote/{timeKey?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\GreetingController@quote',
        'controller' => 'App\\Http\\Controllers\\Api\\GreetingController@quote',
        'namespace' => NULL,
        'prefix' => 'api/greeting',
        'where' => 
        array (
        ),
        'as' => 'generated::xBRseemYLtusTeu3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yvW5hsCRIO8JR8rA' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/greeting/quotes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\GreetingController@quotes',
        'controller' => 'App\\Http\\Controllers\\Api\\GreetingController@quotes',
        'namespace' => NULL,
        'prefix' => 'api/greeting',
        'where' => 
        array (
        ),
        'as' => 'generated::yvW5hsCRIO8JR8rA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::K0cGr5OAnrjhc51q' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:820:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'/home/juni/projects/SIIMUT/vendor/laravel/framework/src/Illuminate/Foundation/Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000016380000000000000000";}}',
        'as' => 'generated::K0cGr5OAnrjhc51q',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'print.preview.imut-data-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'print/preview/imut-data-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PrintReportController@previewImutDataReport',
        'controller' => 'App\\Http\\Controllers\\PrintReportController@previewImutDataReport',
        'as' => 'print.preview.imut-data-report',
        'namespace' => NULL,
        'prefix' => '/print',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'print.preview.imut-indicator-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'print/preview/imut-indicator-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'can:view_all_data_imut::data',
        ),
        'uses' => 'App\\Http\\Controllers\\PrintReportController@previewImutIndicatorReport',
        'controller' => 'App\\Http\\Controllers\\PrintReportController@previewImutIndicatorReport',
        'as' => 'print.preview.imut-indicator-report',
        'namespace' => NULL,
        'prefix' => '/print',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'print.imut-data-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'print/imut-data-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PrintReportController@printImutDataReport',
        'controller' => 'App\\Http\\Controllers\\PrintReportController@printImutDataReport',
        'as' => 'print.imut-data-report',
        'namespace' => NULL,
        'prefix' => '/print',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'print.imut-indicator-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'print/imut-indicator-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\PrintReportController@printImutIndicatorReport',
        'controller' => 'App\\Http\\Controllers\\PrintReportController@printImutIndicatorReport',
        'as' => 'print.imut-indicator-report',
        'namespace' => NULL,
        'prefix' => '/print',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sso.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sso/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'App\\Http\\Middleware\\RedirectIfSsoDisabled',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sso.login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sso.callback' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sso/callback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'App\\Http\\Middleware\\RedirectIfSsoDisabled',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoCallbackController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoCallbackController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sso.callback',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sso.status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sso/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'App\\Http\\Middleware\\RedirectIfSsoDisabled',
        ),
        'uses' => '\\Illuminate\\Routing\\ViewController@__invoke',
        'controller' => '\\Illuminate\\Routing\\ViewController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sso.status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'view' => 'auth-status',
        'data' => 
        array (
        ),
        'status' => 200,
        'headers' => 
        array (
        ),
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debug.session' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'debug-session',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:690:"function () {
        return \\response()->json([
            \'sso_enabled\' => \\config(\'iam.enabled\', false) || \\env(\'USE_SSO\', false),
            \'app_env\' => \\config(\'app.env\'),
            \'session_id\' => \\session()->getId(),
            \'session_started\' => \\session()->isStarted(),
            \'auth_check\' => \\Illuminate\\Support\\Facades\\Auth::check(),
            \'auth_id\' => \\Illuminate\\Support\\Facades\\Auth::id(),
            \'auth_user\' => \\Illuminate\\Support\\Facades\\Auth::user(),
            \'session_data\' => \\session()->all(),
            \'cookies\' => \\request()->cookies->all(),
            \'laravel_session_cookie\' => \\request()->cookie(\'laravel_session\'),
        ]);
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000016350000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'debug.session',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:4:{s:6:"driver";s:5:"local";s:4:"root";s:46:"/home/juni/projects/SIIMUT/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;}s:12:"isProduction";b:1;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000162a0000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'scramble.docs.ui' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'docs/api',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:1:{s:3:"api";s:7:"default";}s:8:"function";s:337:"function (\\Dedoc\\Scramble\\Generator $generator) use ($api) {
                    $config = \\Dedoc\\Scramble\\Scramble::getGeneratorConfig($api);

                    return view(\'scramble::docs\', [
                        \'spec\' => $generator($config),
                        \'config\' => $config,
                    ]);
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000162f0000000000000000";}}',
        'as' => 'scramble.docs.ui',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'scramble.docs.document' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'docs/api.json',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:1:{s:3:"api";s:7:"default";}s:8:"function";s:255:"function (\\Dedoc\\Scramble\\Generator $generator) use ($api) {
                    $config = \\Dedoc\\Scramble\\Scramble::getGeneratorConfig($api);

                    return response()->json($generator($config), options: JSON_PRETTY_PRINT);
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000016400000000000000000";}}',
        'as' => 'scramble.docs.document',
        'middleware' => 
        array (
          0 => 'web',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
