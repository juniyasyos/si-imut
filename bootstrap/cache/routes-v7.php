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
      '/siimut/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.auth.login',
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
      '/siimut/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.auth.logout',
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
      '/siimut' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.pages.dashboard',
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
      '/siimut/pwa-settings-page' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.pages.pwa-settings-page',
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
      '/siimut/backups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.pages.backups',
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
      '/siimut/my-profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.pages.my-profile',
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
      '/siimut/activitylogs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.activitylogs.index',
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
      '/siimut/daily-report-entries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.daily-report-entries.index',
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
      '/siimut/daily-report-entries/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.daily-report-entries.create',
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
      '/siimut/folders' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.folders.index',
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
      '/siimut/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-categories.index',
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
      '/siimut/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-categories.create',
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
      '/siimut/imut-category-resource/schema/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.index',
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
      '/siimut/imut-category-resource/schema/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.create',
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
      '/siimut/imut-category-resource/tables/imut-categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.index',
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
      '/siimut/imut-category-resource/tables/imut-categories/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.create',
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
      '/siimut/imut-datas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.index',
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
      '/siimut/imut-datas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.create',
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
      '/siimut/imut-datas/bencmarkings/region-type' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.bencmarking-region-type',
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
      '/siimut/imut-datas/overview/unit-kerja' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.overview-unit-kerja',
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
      '/siimut/imut-datas/overview/summary-imut-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.overview-imut-data',
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
      '/siimut/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profiles.index',
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
      '/siimut/imut-profile-resource/schema/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.index',
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
      '/siimut/imut-profile-resource/tables/imut-profiles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.index',
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
      '/siimut/laporan-imuts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.index',
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
      '/siimut/laporan-imuts/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.create',
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
      '/siimut/laporan-imuts/unit-kerja-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.unit-kerja-report',
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
      '/siimut/laporan-imuts/unit-kerja-imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.unit-kerja-imut-data-report-detail',
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
      '/siimut/laporan-imuts/imut-data-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.imut-data-report',
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
      '/siimut/laporan-imuts/imut-data-unit-kerja-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.imut-data-unit-kerja-report-detail',
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
      '/siimut/media' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.media.index',
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
      '/siimut/region-type-bencmarkings/bencmarkings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.region-type-bencmarkings.index',
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
      '/siimut/shield/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.shield.roles.index',
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
      '/siimut/shield/roles/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.shield.roles.create',
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
      '/siimut/unit-kerjas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.unit-kerjas.index',
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
      '/siimut/unit-kerjas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.unit-kerjas.create',
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
      '/siimut/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.users.index',
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
      '/siimut/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.users.create',
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
      '/siimut/user-resource/schema/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.schema.users.index',
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
      '/siimut/user-resource/schema/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.schema.users.create',
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
      '/siimut/user-resource/tables/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.tables.users.index',
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
      '/siimut/user-resource/tables/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.tables.users.create',
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
      '/siimut/two-factor-authentication' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.auth.two-factor',
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
      '/livewire/livewire.js' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::TOzL6PTXFVXSc7jI',
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
            '_route' => 'generated::E5tbgGAHOqZ0NjG7',
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
            '_route' => 'generated::ZvyKtiNLQtaMboVX',
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
            '_route' => 'generated::Vw0YaTV0K2ff2Kr9',
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
            '_route' => 'generated::W41mBe7lzr50yY8E',
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
            '_route' => 'generated::d1qk7rcwEbjR7by9',
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
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
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
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/oauth/callback/([^/]++)(*:105)|/filament(?|/(?|exports/([^/]++)/download(*:154)|imports/([^/]++)/failed\\-rows/download(*:200))|\\-excel/(.*)(*:221))|/s(?|iimut/(?|activitylogs/([^/]++)(*:265)|daily\\-report\\-entries/([^/]++)(?|(*:307)|/edit(*:320))|folders/([^/]++)(?|(*:348)|/media(*:362))|imut\\-(?|categor(?|ies/([^/]++)/edit(*:407)|y\\-resource/(?|schema/imut\\-categories/([^/]++)/edit(*:467)|tables/imut\\-categories/([^/]++)/edit(*:512)))|datas/(?|edit\\=([^/]++)(*:545)|([^/]++)/(?|profile/(?|create(*:582)|edit\\=([^/]++)(*:604))|form\\-builder(?|(*:629)|/preview(*:645))))|profile(?|s/([^/]++)/(?|form\\-builder(?|(*:696)|/preview(*:712))|daily\\-reports(*:735))|\\-resource/(?|schema/imut\\-profiles/([^/]++)/(?|form\\-builder(?|(*:808)|/preview(*:824))|daily\\-reports(*:847))|tables/imut\\-profiles/([^/]++)/(?|form\\-builder(?|(*:906)|/preview(*:922))|daily\\-reports(*:945)))))|laporan\\-imuts/([^/]++)/edit(*:985)|shield/roles/([^/]++)(?|(*:1017)|/edit(*:1031))|u(?|nit\\-kerjas/([^/]++)/edit(*:1070)|ser(?|s/([^/]++)(?|(*:1098)|/edit(*:1112))|\\-resource/(?|schema/users/([^/]++)(?|(*:1160)|/edit(*:1174))|tables/users/([^/]++)(?|(*:1208)|/edit(*:1222))))))|torage/(.*)(*:1247))|/livewire/preview\\-file/([^/]++)(*:1289)|/api/greeting/quote(?:/([^/]++))?(*:1331))/?$}sDu',
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
      154 => 
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
      200 => 
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
      221 => 
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
      265 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.activitylogs.view',
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
      307 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.daily-report-entries.view',
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
      320 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.daily-report-entries.edit',
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
      348 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.folders.view',
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
      362 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.folders.media',
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
          5 => false,
          6 => NULL,
        ),
      ),
      407 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-categories.edit',
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
      467 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.edit',
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
      512 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.edit',
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
      545 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.edit',
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
      582 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.create-profile',
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
      604 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.edit-profile',
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
      629 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.manage-form-builder',
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
      645 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.preview-form',
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
      696 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profiles.manage-form-builder',
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
      712 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profiles.preview-form',
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
      735 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profiles.list-daily-reports',
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
      808 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.manage-form-builder',
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
      824 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.preview-form',
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
      847 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.list-daily-reports',
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
      906 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.manage-form-builder',
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
      922 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.preview-form',
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
      945 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.list-daily-reports',
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
      985 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.edit',
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
      1017 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.shield.roles.view',
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
      1031 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.shield.roles.edit',
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
      1070 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.unit-kerjas.edit',
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
      1098 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.users.view',
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
      1112 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.users.edit',
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
      1160 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.schema.users.view',
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
      1174 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.schema.users.edit',
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
      1208 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.tables.users.view',
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
      1222 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.user-resource.tables.users.edit',
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
      1247 => 
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
      1289 => 
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
      1331 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::C1G1L378iF4bfre9',
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
    'filament.siimut.auth.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/login',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Pages\\Login@__invoke',
        'controller' => 'App\\Filament\\Pages\\Login',
        'as' => 'filament.siimut.auth.login',
        'namespace' => NULL,
        'prefix' => '/siimut',
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
    'filament.siimut.auth.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'siimut/logout',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'Filament\\Http\\Controllers\\Auth\\LogoutController@__invoke',
        'controller' => 'Filament\\Http\\Controllers\\Auth\\LogoutController',
        'as' => 'filament.siimut.auth.logout',
        'namespace' => NULL,
        'prefix' => '/siimut',
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
    'filament.siimut.pages.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.pages.dashboard',
        'namespace' => NULL,
        'prefix' => 'siimut/',
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
    'filament.siimut.pages.pwa-settings-page' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/pwa-settings-page',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.pages.pwa-settings-page',
        'namespace' => NULL,
        'prefix' => 'siimut/',
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
    'filament.siimut.pages.backups' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/backups',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.pages.backups',
        'namespace' => NULL,
        'prefix' => 'siimut/',
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
    'filament.siimut.pages.my-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/my-profile',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.pages.my-profile',
        'namespace' => NULL,
        'prefix' => 'siimut/',
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
    'filament.siimut.resources.activitylogs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/activitylogs',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.activitylogs.index',
        'namespace' => NULL,
        'prefix' => 'siimut/activitylogs',
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
    'filament.siimut.resources.activitylogs.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/activitylogs/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.activitylogs.view',
        'namespace' => NULL,
        'prefix' => 'siimut/activitylogs',
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
    'filament.siimut.resources.daily-report-entries.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/daily-report-entries',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.daily-report-entries.index',
        'namespace' => NULL,
        'prefix' => 'siimut/daily-report-entries',
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
    'filament.siimut.resources.daily-report-entries.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/daily-report-entries/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.daily-report-entries.create',
        'namespace' => NULL,
        'prefix' => 'siimut/daily-report-entries',
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
    'filament.siimut.resources.daily-report-entries.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/daily-report-entries/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.daily-report-entries.view',
        'namespace' => NULL,
        'prefix' => 'siimut/daily-report-entries',
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
    'filament.siimut.resources.daily-report-entries.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/daily-report-entries/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.daily-report-entries.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/daily-report-entries',
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
    'filament.siimut.resources.folders.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/folders',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.folders.index',
        'namespace' => NULL,
        'prefix' => 'siimut/folders',
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
    'filament.siimut.resources.folders.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/folders/{folder}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.folders.view',
        'namespace' => NULL,
        'prefix' => 'siimut/folders',
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
    'filament.siimut.resources.folders.media' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/folders/{folder}/media',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.folders.media',
        'namespace' => NULL,
        'prefix' => 'siimut/folders',
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
    'filament.siimut.resources.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-categories.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-categories',
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
    'filament.siimut.resources.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-categories.create',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-categories',
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
    'filament.siimut.resources.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.schema.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/schema/imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/schema/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.schema.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/schema/imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.create',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/schema/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.schema.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/schema/imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.schema.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/schema/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.tables.imut-categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/tables/imut-categories',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/tables/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.tables.imut-categories.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/tables/imut-categories/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.create',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/tables/imut-categories',
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
    'filament.siimut.resources.imut-category-resource.tables.imut-categories.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-category-resource/tables/imut-categories/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-category-resource.tables.imut-categories.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-category-resource/tables/imut-categories',
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
    'filament.siimut.resources.imut-datas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.create',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/edit={record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.create-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/{imutDataSlug}/profile/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.create-profile',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.edit-profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/{imutDataSlug}/profile/edit={record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.edit-profile',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.bencmarking-region-type' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/bencmarkings/region-type',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.bencmarking-region-type',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.overview-unit-kerja' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/overview/unit-kerja',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.overview-unit-kerja',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.overview-imut-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/overview/summary-imut-data',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.overview-imut-data',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.manage-form-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/{record}/form-builder',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-datas.manage-form-builder',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-datas.preview-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/{record}/form-builder/preview',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\FormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutDataResource\\Pages\\FormBuilder',
        'as' => 'filament.siimut.resources.imut-datas.preview-form',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-datas',
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
    'filament.siimut.resources.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profiles',
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
    'filament.siimut.resources.imut-profiles.manage-form-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profiles/{record}/form-builder',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder',
        'as' => 'filament.siimut.resources.imut-profiles.manage-form-builder',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profiles',
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
    'filament.siimut.resources.imut-profiles.preview-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profiles/{record}/form-builder/preview',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder',
        'as' => 'filament.siimut.resources.imut-profiles.preview-form',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profiles',
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
    'filament.siimut.resources.imut-profiles.list-daily-reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profiles/{record}/daily-reports',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports',
        'as' => 'filament.siimut.resources.imut-profiles.list-daily-reports',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/schema/imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/schema/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.manage-form-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/schema/imut-profiles/{record}/form-builder',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder',
        'as' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.manage-form-builder',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/schema/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.preview-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/schema/imut-profiles/{record}/form-builder/preview',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder',
        'as' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.preview-form',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/schema/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.list-daily-reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/schema/imut-profiles/{record}/daily-reports',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports',
        'as' => 'filament.siimut.resources.imut-profile-resource.schema.imut-profiles.list-daily-reports',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/schema/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/tables/imut-profiles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.index',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/tables/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.manage-form-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/tables/imut-profiles/{record}/form-builder',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ManageFormBuilder',
        'as' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.manage-form-builder',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/tables/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.preview-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/tables/imut-profiles/{record}/form-builder/preview',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\FormBuilder',
        'as' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.preview-form',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/tables/imut-profiles',
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
    'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.list-daily-reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-profile-resource/tables/imut-profiles/{record}/daily-reports',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'uses' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports@__invoke',
        'controller' => 'App\\Filament\\Resources\\ImutProfileResource\\Pages\\ListDailyReports',
        'as' => 'filament.siimut.resources.imut-profile-resource.tables.imut-profiles.list-daily-reports',
        'namespace' => NULL,
        'prefix' => 'siimut/imut-profile-resource/tables/imut-profiles',
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
    'filament.siimut.resources.laporan-imuts.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.index',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.create',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.unit-kerja-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/unit-kerja-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.unit-kerja-report',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.unit-kerja-imut-data-report-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/unit-kerja-imut-data-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.unit-kerja-imut-data-report-detail',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.imut-data-report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/imut-data-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.imut-data-report',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.laporan-imuts.imut-data-unit-kerja-report-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/imut-data-unit-kerja-report',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.laporan-imuts.imut-data-unit-kerja-report-detail',
        'namespace' => NULL,
        'prefix' => 'siimut/laporan-imuts',
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
    'filament.siimut.resources.media.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/media',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.media.index',
        'namespace' => NULL,
        'prefix' => 'siimut/media',
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
    'filament.siimut.resources.region-type-bencmarkings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/region-type-bencmarkings/bencmarkings',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.region-type-bencmarkings.index',
        'namespace' => NULL,
        'prefix' => 'siimut/region-type-bencmarkings',
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
    'filament.siimut.resources.shield.roles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/shield/roles',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.shield.roles.index',
        'namespace' => NULL,
        'prefix' => 'siimut/shield/roles',
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
    'filament.siimut.resources.shield.roles.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/shield/roles/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.shield.roles.create',
        'namespace' => NULL,
        'prefix' => 'siimut/shield/roles',
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
    'filament.siimut.resources.shield.roles.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/shield/roles/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.shield.roles.view',
        'namespace' => NULL,
        'prefix' => 'siimut/shield/roles',
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
    'filament.siimut.resources.shield.roles.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/shield/roles/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.shield.roles.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/shield/roles',
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
    'filament.siimut.resources.unit-kerjas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/unit-kerjas',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.unit-kerjas.index',
        'namespace' => NULL,
        'prefix' => 'siimut/unit-kerjas',
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
    'filament.siimut.resources.unit-kerjas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/unit-kerjas/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.unit-kerjas.create',
        'namespace' => NULL,
        'prefix' => 'siimut/unit-kerjas',
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
    'filament.siimut.resources.unit-kerjas.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/unit-kerjas/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.unit-kerjas.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/unit-kerjas',
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
    'filament.siimut.resources.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.users.index',
        'namespace' => NULL,
        'prefix' => 'siimut/users',
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
    'filament.siimut.resources.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.users.create',
        'namespace' => NULL,
        'prefix' => 'siimut/users',
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
    'filament.siimut.resources.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.users.view',
        'namespace' => NULL,
        'prefix' => 'siimut/users',
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
    'filament.siimut.resources.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.users.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/users',
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
    'filament.siimut.resources.user-resource.schema.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/schema/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.schema.users.index',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/schema/users',
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
    'filament.siimut.resources.user-resource.schema.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/schema/users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.schema.users.create',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/schema/users',
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
    'filament.siimut.resources.user-resource.schema.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/schema/users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.schema.users.view',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/schema/users',
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
    'filament.siimut.resources.user-resource.schema.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/schema/users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.schema.users.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/schema/users',
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
    'filament.siimut.resources.user-resource.tables.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/tables/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.tables.users.index',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/tables/users',
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
    'filament.siimut.resources.user-resource.tables.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/tables/users/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.tables.users.create',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/tables/users',
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
    'filament.siimut.resources.user-resource.tables.users.view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/tables/users/{record}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.tables.users.view',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/tables/users',
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
    'filament.siimut.resources.user-resource.tables.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/user-resource/tables/users/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.resources.user-resource.tables.users.edit',
        'namespace' => NULL,
        'prefix' => 'siimut/user-resource/tables/users',
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
    'filament.siimut.auth.two-factor' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/two-factor-authentication',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:siimut',
          1 => 'panel:siimut',
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
        'as' => 'filament.siimut.auth.two-factor',
        'namespace' => NULL,
        'prefix' => '/siimut',
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
    'generated::TOzL6PTXFVXSc7jI' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/livewire.js',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'controller' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'as' => 'generated::TOzL6PTXFVXSc7jI',
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
    'generated::E5tbgGAHOqZ0NjG7' => 
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
        'as' => 'generated::E5tbgGAHOqZ0NjG7',
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
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000014260000000000000000";}}',
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
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000016140000000000000000";}}',
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
    'generated::ZvyKtiNLQtaMboVX' => 
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
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000016950000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ZvyKtiNLQtaMboVX',
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
    'generated::Vw0YaTV0K2ff2Kr9' => 
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
        'as' => 'generated::Vw0YaTV0K2ff2Kr9',
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
    'generated::C1G1L378iF4bfre9' => 
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
        'as' => 'generated::C1G1L378iF4bfre9',
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
    'generated::W41mBe7lzr50yY8E' => 
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
        'as' => 'generated::W41mBe7lzr50yY8E',
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
    'generated::d1qk7rcwEbjR7by9' => 
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
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000016940000000000000000";}}',
        'as' => 'generated::d1qk7rcwEbjR7by9',
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
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:550:"function () {
        // If authenticated, go to admin dashboard
        if (\\Illuminate\\Support\\Facades\\Auth::check()) {
            return \\redirect(\'/siimut\');
        }

        // If not authenticated, check SSO mode
        $ssoEnabled = \\config(\'iam.enabled\', false) || \\env(\'USE_SSO\', false);

        if ($ssoEnabled) {
            // Production: Redirect to SSO login
            return \\redirect(\'/login\');
        } else {
            // Development: Redirect to custom login
            return \\redirect(\'/siimut/login\');
        }
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000016920000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
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
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000168f0000000000000000";}}',
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
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:4:{s:6:"driver";s:5:"local";s:4:"root";s:46:"/home/juni/projects/SIIMUT/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000016390000000000000000";}}',
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
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000016860000000000000000";}}',
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
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000169c0000000000000000";}}',
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
