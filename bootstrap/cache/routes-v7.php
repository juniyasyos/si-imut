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
      '/siimut/backup-settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.pages.backup-settings',
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
      '/api/iam/sync-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.sync-users',
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
      '/api/iam/sync-roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.sync-roles',
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
      '/api/iam/push-roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.push-roles',
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
      '/api/iam/push-users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.push-users',
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
      '/api/iam/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.health',
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
            '_route' => 'login',
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
      '/iam/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.iam.logout',
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
      '/iam/user-applications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.user-applications',
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
      '/iam/debug/user-applications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.user-applications.debug',
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
      '/iam/backchannel-logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'iam.backchannel.logout',
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
      '/api/manage-unit-kerja/center/provision' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0V6WgXuHz3HuTlmA',
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
      '/api/manage-unit-kerja/client/sync' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::vKvhnv0jQSsJNsuo',
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
            '_route' => 'default.livewire.update',
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
            '_route' => 'generated::fYlmCa2mZP7aEDiT',
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
            '_route' => 'generated::PqP6I07tNCf3gvij',
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
            '_route' => 'generated::RFh5yPmlQhAXniyh',
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
            '_route' => 'generated::Hdll4Dn4HB8Y2z8C',
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
            '_route' => 'generated::J90qw0z5ZGsRUrtx',
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
      '/api/benchmarks/coverage' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::RP2EfJIKqaIeRhMe',
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
      '/api/benchmarks/missing' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NtKitkiy0YJRrvp6',
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
      '/api/benchmarks/bulk-create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yTEY89EmOYD84W5b',
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
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ocJpFWc619MfpQl2',
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
      '/table-view' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'table-view',
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
      '/api/table-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.table-data',
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
      '/api/user-applications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.user-applications',
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
      '/laporan/indikator-mutu/kategori' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.by-category',
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
      '/laporan/indikator-mutu/kategori/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.by-category.pdf',
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
      0 => '{^(?|/oauth/callback/([^/]++)(*:31)|/filament(?|/(?|exports/([^/]++)/download(*:79)|imports/([^/]++)/failed\\-rows/download(*:124))|\\-excel/(.*)(*:145))|/siimut/(?|activitylogs/([^/]++)(*:186)|daily\\-report\\-entries/([^/]++)(?|(*:228)|/edit(*:241))|folders/([^/]++)(?|(*:269)|/media(*:283))|imut\\-(?|categories/([^/]++)/edit(*:325)|datas/(?|edit\\=([^/]++)(*:356)|([^/]++)/(?|profile/(?|create(*:393)|edit\\=([^/]++)(*:415))|([^/]++)/(?|form\\-builder(?|(*:452)|/preview(*:468))|daily\\-reports(*:491)))))|laporan\\-imuts/([^/]++)/(?|edit(*:534)|monitoring\\-(?|daily\\-reports(*:571)|unit\\-detail/([^/]++)(*:600)))|shield/roles/([^/]++)(?|(*:634)|/edit(*:647))|u(?|nit\\-kerjas/([^/]++)/edit(*:685)|sers/([^/]++)(?|(*:709)|/edit(*:722))))|/a(?|dmin/backup/([^/]++)(?|/(?|retry(*:770)|cancel(*:784)|download(*:800))|(*:809))|pi/(?|greeting/quote(?:/([^/]++))?(*:852)|chart/imut/([^/]++)/benchmarks(?|(*:893)|/debug(*:907))|imut\\-(?|data/(?|([^/]++)/(?|summary(*:952)|notes(*:965))|report/([^/]++)/([^/]++)(*:998))|indicator\\-report/([^/]++)/([^/]++)(*:1042))))|/l(?|ivewire/preview\\-file/([^/]++)(*:1089)|aporan/indikator\\-mutu/(?|([a-z0-9-]+)/([0-9]+)(*:1145)|([a-z0-9-]+)/([0-9]+)(?:/([a-z_]+)(?:/([0-9]+))?)?(*:1204)|unit\\-kerja/(?|([^/]++)(*:1236)|([a-z0-9-]+)/(yearly|quarterly|semester|custom)/([a-zA-Z0-9\\-,]+)(*:1310))))|/export/monitoring/([^/]++)(*:1349)|/(.*)(*:1363))/?$}sDu',
    ),
    3 => 
    array (
      31 => 
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
      79 => 
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
      124 => 
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
      145 => 
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
      186 => 
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
      228 => 
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
      241 => 
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
      269 => 
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
      283 => 
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
      325 => 
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
      356 => 
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
      393 => 
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
      415 => 
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
      452 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.manage-form-builder',
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
          5 => false,
          6 => NULL,
        ),
      ),
      468 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.preview-form',
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
          5 => false,
          6 => NULL,
        ),
      ),
      491 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.imut-datas.list-daily-reports',
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
          5 => false,
          6 => NULL,
        ),
      ),
      534 => 
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
      571 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.monitoring-daily-reports',
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
      600 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.siimut.resources.laporan-imuts.monitoring-unit-detail',
          ),
          1 => 
          array (
            0 => 'record',
            1 => 'unit',
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
      634 => 
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
      647 => 
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
      685 => 
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
      709 => 
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
      722 => 
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
      770 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.retry',
          ),
          1 => 
          array (
            0 => 'backupJob',
          ),
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
      784 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.cancel',
          ),
          1 => 
          array (
            0 => 'backupJob',
          ),
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
      800 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.download',
          ),
          1 => 
          array (
            0 => 'backupJob',
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
      809 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'backup.delete',
          ),
          1 => 
          array (
            0 => 'backupJob',
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
      852 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::RxTjDXhqv3RQAoau',
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
      893 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Rxhgm3FFOofVt2rT',
          ),
          1 => 
          array (
            0 => 'imutDataId',
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
      907 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::vP07RdnCZ2NABkNK',
          ),
          1 => 
          array (
            0 => 'imutDataId',
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
      952 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Hn1fwJsrW5wd958A',
          ),
          1 => 
          array (
            0 => 'imutDataId',
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
      965 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CKaXB2tg0MqgH6Xa',
          ),
          1 => 
          array (
            0 => 'imutDataId',
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
      998 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Akae5qD3sAKmZokJ',
          ),
          1 => 
          array (
            0 => 'indicator',
            1 => 'periode',
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
      1042 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.imut-indicator-report.show',
          ),
          1 => 
          array (
            0 => 'indicator',
            1 => 'periode',
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
      1089 => 
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
      1145 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.show',
          ),
          1 => 
          array (
            0 => 'indicator',
            1 => 'periode',
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
      1204 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.detail',
            'filter_periode' => NULL,
            'catatan' => NULL,
          ),
          1 => 
          array (
            0 => 'indicator',
            1 => 'periode',
            2 => 'filter_periode',
            3 => 'catatan',
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
      1236 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.unit-kerja.show',
          ),
          1 => 
          array (
            0 => 'unitKerja',
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
      1310 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'laporan.indikator-mutu.unit-kerja.show-with-period',
          ),
          1 => 
          array (
            0 => 'unitKerja',
            1 => 'tipe',
            2 => 'periode',
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
      1349 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'export.monitoring',
          ),
          1 => 
          array (
            0 => 'templateId',
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
      1363 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::86VIttMlaeN3JZRu',
          ),
          1 => 
          array (
            0 => 'fallbackPlaceholder',
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
    'filament.siimut.pages.backup-settings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/backup-settings',
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
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Pages\\BackupSettings@__invoke',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Pages\\BackupSettings',
        'as' => 'filament.siimut.pages.backup-settings',
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
      'uri' => 'siimut/imut-datas/{imutDataSlug}/{record}/form-builder',
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
      'uri' => 'siimut/imut-datas/{imutDataSlug}/{record}/form-builder/preview',
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
    'filament.siimut.resources.imut-datas.list-daily-reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/imut-datas/{imutDataSlug}/{record}/daily-reports',
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
        'as' => 'filament.siimut.resources.imut-datas.list-daily-reports',
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
    'filament.siimut.resources.laporan-imuts.monitoring-daily-reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/{record}/monitoring-daily-reports',
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
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\MonitoringDailyReports@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\MonitoringDailyReports',
        'as' => 'filament.siimut.resources.laporan-imuts.monitoring-daily-reports',
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
    'filament.siimut.resources.laporan-imuts.monitoring-unit-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'siimut/laporan-imuts/{record}/monitoring-unit-detail/{unit}',
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
        'uses' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\MonitoringUnitDetail@__invoke',
        'controller' => 'App\\Filament\\Resources\\LaporanImutResource\\Pages\\MonitoringUnitDetail',
        'as' => 'filament.siimut.resources.laporan-imuts.monitoring-unit-detail',
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
    'backup.retry' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/backup/{backupJob}/retry',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@retry',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@retry',
        'as' => 'backup.retry',
        'namespace' => NULL,
        'prefix' => 'admin/backup',
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
    'backup.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/backup/{backupJob}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@cancel',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@cancel',
        'as' => 'backup.cancel',
        'namespace' => NULL,
        'prefix' => 'admin/backup',
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
    'backup.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/backup/{backupJob}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@download',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@download',
        'as' => 'backup.download',
        'namespace' => NULL,
        'prefix' => 'admin/backup',
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
    'backup.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/backup/{backupJob}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@delete',
        'controller' => 'Juniyasyos\\FilamentLaravelBackup\\Http\\Controllers\\BackupController@delete',
        'as' => 'backup.delete',
        'namespace' => NULL,
        'prefix' => 'admin/backup',
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
    'iam.sync-users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/iam/sync-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'iam.backchannel.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SyncUsersController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SyncUsersController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.sync-users',
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
    'iam.sync-roles' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'api/iam/sync-roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'iam.backchannel.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SyncRolesController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SyncRolesController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.sync-roles',
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
    'iam.push-roles' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/iam/push-roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'iam.backchannel.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\PushRolesController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\PushRolesController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.push-roles',
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
    'iam.push-users' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/iam/push-users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'iam.backchannel.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\PushUsersController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\PushUsersController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.push-users',
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
    'iam.health' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/iam/health',
      'action' => 
      array (
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\HealthController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\HealthController',
        'as' => 'iam.health',
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
    'login' => 
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
          1 => 'web',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\SsoLoginRedirectController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
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
        'uses' => 'App\\Http\\Controllers\\Auth\\LogoutController@__invoke',
        'controller' => 'App\\Http\\Controllers\\Auth\\LogoutController',
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
    'iam.iam.logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'iam/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamInitiatedLogoutController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamInitiatedLogoutController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.iam.logout',
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
    'iam.user-applications' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'iam/user-applications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'iam.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamUserApplicationsController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamUserApplicationsController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.user-applications',
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
    'iam.user-applications.debug' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'iam/debug/user-applications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamUserApplicationsController@webUserApplications',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\IamUserApplicationsController@webUserApplications',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.user-applications.debug',
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
    'iam.backchannel.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'iam/backchannel-logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'iam.backchannel.verify',
        ),
        'uses' => 'Juniyasyos\\IamClient\\Http\\Controllers\\BackchannelLogoutController@__invoke',
        'controller' => 'Juniyasyos\\IamClient\\Http\\Controllers\\BackchannelLogoutController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'iam.backchannel.logout',
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
    'generated::0V6WgXuHz3HuTlmA' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/manage-unit-kerja/center/provision',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Juniyasyos\\ManageUnitKerja\\Http\\Controllers\\CenterSyncController@provision',
        'controller' => 'Juniyasyos\\ManageUnitKerja\\Http\\Controllers\\CenterSyncController@provision',
        'namespace' => NULL,
        'prefix' => 'api/manage-unit-kerja',
        'where' => 
        array (
        ),
        'as' => 'generated::0V6WgXuHz3HuTlmA',
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
    'generated::vKvhnv0jQSsJNsuo' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/manage-unit-kerja/client/sync',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'Juniyasyos\\ManageUnitKerja\\Http\\Controllers\\ClientSyncController@sync',
        'controller' => 'Juniyasyos\\ManageUnitKerja\\Http\\Controllers\\ClientSyncController@sync',
        'namespace' => NULL,
        'prefix' => 'api/manage-unit-kerja',
        'where' => 
        array (
        ),
        'as' => 'generated::vKvhnv0jQSsJNsuo',
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
    'default.livewire.update' => 
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
        'as' => 'default.livewire.update',
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
    'generated::fYlmCa2mZP7aEDiT' => 
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
        'as' => 'generated::fYlmCa2mZP7aEDiT',
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
    'generated::PqP6I07tNCf3gvij' => 
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
        'as' => 'generated::PqP6I07tNCf3gvij',
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
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000015230000000000000000";}}',
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
}";s:5:"scope";s:34:"Illuminate\\Support\\ServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000011510000000000000000";}}',
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
    'generated::RFh5yPmlQhAXniyh' => 
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
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000014ac0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::RFh5yPmlQhAXniyh',
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
    'generated::Hdll4Dn4HB8Y2z8C' => 
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
        'as' => 'generated::Hdll4Dn4HB8Y2z8C',
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
    'generated::RxTjDXhqv3RQAoau' => 
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
        'as' => 'generated::RxTjDXhqv3RQAoau',
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
    'generated::J90qw0z5ZGsRUrtx' => 
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
        'as' => 'generated::J90qw0z5ZGsRUrtx',
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
    'generated::Rxhgm3FFOofVt2rT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/chart/imut/{imutDataId}/benchmarks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getChartData',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getChartData',
        'namespace' => NULL,
        'prefix' => 'api/chart/imut/{imutDataId}',
        'where' => 
        array (
        ),
        'as' => 'generated::Rxhgm3FFOofVt2rT',
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
    'generated::vP07RdnCZ2NABkNK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/chart/imut/{imutDataId}/benchmarks/debug',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getDebugData',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getDebugData',
        'namespace' => NULL,
        'prefix' => 'api/chart/imut/{imutDataId}',
        'where' => 
        array (
        ),
        'as' => 'generated::vP07RdnCZ2NABkNK',
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
    'generated::RP2EfJIKqaIeRhMe' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/benchmarks/coverage',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getCoverage',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getCoverage',
        'namespace' => NULL,
        'prefix' => 'api/benchmarks',
        'where' => 
        array (
        ),
        'as' => 'generated::RP2EfJIKqaIeRhMe',
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
    'generated::NtKitkiy0YJRrvp6' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/benchmarks/missing',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getMissingBenchmarks',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@getMissingBenchmarks',
        'namespace' => NULL,
        'prefix' => 'api/benchmarks',
        'where' => 
        array (
        ),
        'as' => 'generated::NtKitkiy0YJRrvp6',
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
    'generated::yTEY89EmOYD84W5b' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/benchmarks/bulk-create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@bulkCreate',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutBenchmarkingController@bulkCreate',
        'namespace' => NULL,
        'prefix' => 'api/benchmarks',
        'where' => 
        array (
        ),
        'as' => 'generated::yTEY89EmOYD84W5b',
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
    'generated::Hn1fwJsrW5wd958A' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/imut-data/{imutDataId}/summary',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@summary',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@summary',
        'namespace' => NULL,
        'prefix' => 'api/imut-data',
        'where' => 
        array (
        ),
        'as' => 'generated::Hn1fwJsrW5wd958A',
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
    'generated::CKaXB2tg0MqgH6Xa' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/imut-data/{imutDataId}/notes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@notes',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@notes',
        'namespace' => NULL,
        'prefix' => 'api/imut-data',
        'where' => 
        array (
        ),
        'as' => 'generated::CKaXB2tg0MqgH6Xa',
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
    'generated::Akae5qD3sAKmZokJ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/imut-data/report/{indicator}/{periode}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@reportData',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutDataApiController@reportData',
        'namespace' => NULL,
        'prefix' => 'api/imut-data',
        'where' => 
        array (
        ),
        'as' => 'generated::Akae5qD3sAKmZokJ',
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
    'api.imut-indicator-report.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/imut-indicator-report/{indicator}/{periode}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ImutIndicatorReportController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\ImutIndicatorReportController@show',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'api.imut-indicator-report.show',
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
    'generated::ocJpFWc619MfpQl2' => 
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

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'/home/juni/projects/siimut/vendor/laravel/framework/src/Illuminate/Foundation/Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000015280000000000000000";}}',
        'as' => 'generated::ocJpFWc619MfpQl2',
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
    'table-view' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'table-view',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TableViewController@index',
        'controller' => 'App\\Http\\Controllers\\TableViewController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'table-view',
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
    'api.table-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/table-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\TableViewController@getData',
        'controller' => 'App\\Http\\Controllers\\TableViewController@getData',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.table-data',
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
    'export.monitoring' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'export/monitoring/{templateId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:8085:"function ($templateId) {
    try {
        $user = \\Illuminate\\Support\\Facades\\Auth::user();
        $month = \\request(\'month\', \\now()->format(\'Y-m\'));

        // Parse month
        $date = \\Carbon\\Carbon::createFromFormat(\'Y-m\', $month);

        // Get period settings
        $settings = \\App\\Models\\LaporanImutAutoGenerationSetting::getInstance();

        // Use full month approach
        $startDate = $date->copy()->startOfMonth()->startOfDay();
        $endDate = $date->copy()->endOfMonth()->endOfDay();

        // Get template with responses
        $template = \\App\\Models\\FormTemplate::with([
            \'imutProfile.imutData\',
            \'formFields.options\',
            \'dailyReportResponses\' => function ($query) use ($startDate, $endDate, $user) {
                $query->whereBetween(\'report_date\', [$startDate, $endDate])
                    ->forUserUnits($user)
                    ->with([\'submittedBy\', \'validator\', \'unitKerja\', \'fieldResponses.formField\']);
            }
        ])->findOrFail($templateId);

        // Generate filename
        $filename = \'monitoring_\' . $template->imutProfile->imutData->title . \'_\' . $month . \'.xlsx\';
        $filename = \\preg_replace(\'/[^A-Za-z0-9\\-_.]/\', \'_\', $filename);

        // Create Excel file
        return \\Maatwebsite\\Excel\\Facades\\Excel::download(
            new class($template, $startDate, $endDate) implements \\Maatwebsite\\Excel\\Concerns\\FromCollection, \\Maatwebsite\\Excel\\Concerns\\WithHeadings, \\Maatwebsite\\Excel\\Concerns\\WithTitle {
                private $template;
                private $startDate;
                private $endDate;

                public function __construct($template, $startDate, $endDate)
                {
                    $this->template = $template;
                    $this->startDate = $startDate;
                    $this->endDate = $endDate;
                }

                public function collection()
                {
                    $data = \\collect();

                    foreach ($this->template->dailyReportResponses as $response) {
                        $row = [
                            \'Tanggal\' => $response->report_date->format(\'d/m/Y\'),
                            \'Unit Kerja\' => $response->unitKerja->unit_name ?? \'\',
                            \'Pengumpul Data\' => $response->submittedBy->name ?? \'\',
                            \'Validator\' => $response->validator->name ?? \'\',
                            \'Status Validasi\' => $response->is_validated ? \'Tervalidasi\' : \'Belum Divalidasi\',
                        ];

                        // Add field responses
                        foreach ($this->template->formFields as $field) {
                            $fieldResponse = $response->fieldResponses->where(\'form_field_id\', $field->id)->first();
                            $value = \'\';

                            if ($fieldResponse) {
                                $fieldValue = $fieldResponse->field_value;

                                // Format value based on field type
                                switch ($field->field_type) {
                                    case \'boolean\':
                                        $value = ($fieldValue == 1 || $fieldValue === true || $fieldValue === \'1\') ? \'Ya\' : \'Tidak\';
                                        break;

                                    case \'single_select\':
                                    case \'multi_select\':
                                        if (\\is_array($fieldValue)) {
                                            $selectedOptions = [];
                                            foreach ($fieldValue as $optionValue) {
                                                $option = $field->options->firstWhere(\'option_value\', $optionValue);
                                                if ($option) {
                                                    $selectedOptions[] = $option->option_text;
                                                }
                                            }
                                            $value = \\implode(\', \', $selectedOptions);
                                        } else {
                                            $option = $field->options->firstWhere(\'option_value\', $fieldValue);
                                            $value = $option ? $option->option_text : $fieldValue;
                                        }
                                        break;

                                    case \'time_duration\':
                                    case \'time_range\':
                                        if (\\is_array($fieldValue)) {
                                            if (isset($fieldValue[\'start_time\']) && isset($fieldValue[\'end_time\'])) {
                                                $value = $fieldValue[\'start_time\'] . \' - \' . $fieldValue[\'end_time\'];
                                            } elseif (isset($fieldValue[\'duration\'])) {
                                                $value = $fieldValue[\'duration\'] . \' menit\';
                                            } else {
                                                $value = \\json_encode($fieldValue);
                                            }
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;

                                    case \'number\':
                                        $value = \\is_numeric($fieldValue) ? \\number_format($fieldValue, 0, \',\', \'.\') : $fieldValue;
                                        break;

                                    case \'date\':
                                        if ($fieldValue && \\strtotime($fieldValue)) {
                                            $value = \\date(\'d/m/Y\', \\strtotime($fieldValue));
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;

                                    default:
                                        if (\\is_array($fieldValue)) {
                                            $value = \\json_encode($fieldValue);
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;
                                }
                            }

                            $row[$field->field_label] = $value;
                        }

                        $data->push($row);
                    }

                    return $data;
                }

                public function headings(): array
                {
                    $headings = [
                        \'Tanggal\',
                        \'Unit Kerja\',
                        \'Pengumpul Data\',
                        \'Validator\',
                        \'Status Validasi\'
                    ];

                    // Add field headings
                    foreach ($this->template->formFields as $field) {
                        $headings[] = $field->field_label;
                    }

                    return $headings;
                }

                public function title(): string
                {
                    return \\substr($this->template->imutProfile->imutData->title, 0, 31);
                }
            },
            $filename
        );
    } catch (\\Exception $e) {
        \\Log::error(\'Export monitoring data failed\', [
            \'template_id\' => $templateId,
            \'user_id\' => $user->id ?? null,
            \'month\' => $month,
            \'error\' => $e->getMessage(),
            \'trace\' => $e->getTraceAsString()
        ]);

        // For file download endpoints, redirect back with error message
        return \\redirect()->back()->with(\'error\', \'Gagal export data: \' . $e->getMessage());
    }
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000010610000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'export.monitoring',
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
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:562:"function () {
        // If authenticated, go to admin dashboard
        if (\\Illuminate\\Support\\Facades\\Auth::check()) {
            return \\redirect(\'/siimut\');
        }

        // If not authenticated, check SSO mode
        $ssoEnabled = \\config(\'iam.enabled\', false) || \\env(\'USE_SSO\', false);

        if ($ssoEnabled) {
            // Production: Redirect to SSO login
            return \\redirect()->route(\'sso.login\');
        } else {
            // Development: Redirect to custom login
            return \\redirect(\'/siimut/login\');
        }
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000014890000000000000000";}}',
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
    'generated::86VIttMlaeN3JZRu' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{fallbackPlaceholder}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:470:"function (\\Illuminate\\Http\\Request $request) {
        $ssoEnabled = \\config(\'iam.enabled\', false) || \\env(\'USE_SSO\', false);
        $path = \\trim($request->path(), \'/\');

        if (\\in_array($path, [\'siimut/login\', \'admin/login\'], true) && $ssoEnabled) {
            return \\redirect(\'/login\');
        }

        if ($path === \'login\' && ! $ssoEnabled) {
            return \\redirect(\\Filament\\Facades\\Filament::getLoginUrl());
        }

        \\abort(404);
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000175b0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::86VIttMlaeN3JZRu',
      ),
      'fallback' => true,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'fallbackPlaceholder' => '.*',
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
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000016e60000000000000000";}}',
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
    'api.user-applications' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user-applications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'auth',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:2018:"function () {
        try {
            if (!\\auth()->check()) {
                return \\response()->json([
                    \'error\' => \'Unauthorized\',
                    \'message\' => \'User not authenticated\'
                ], 401);
            }

            if (!\\config(\'iam.enabled\')) {
                return \\response()->json([
                    \'error\' => \'IAM Not Enabled\',
                    \'applications\' => []
                ]);
            }

            $service = \\app(\\Juniyasyos\\IamClient\\Services\\UserApplicationsService::class);
            $data = $service->getApplications();

            if ($data === null || isset($data[\'error\'])) {
                return \\response()->json([
                    \'error\' => \'Failed to fetch applications\',
                    \'applications\' => []
                ]);
            }

            // Transform response untuk component
            $applications = \\collect($data[\'applications\'] ?? [])
                ->filter(fn($app) => $app[\'enabled\'] ?? true)
                ->map(fn($app) => [
                    \'id\' => $app[\'id\'],
                    \'name\' => $app[\'name\'],
                    \'app_key\' => $app[\'app_key\'],
                    \'app_url\' => $app[\'app_url\'],
                    \'role\' => \\collect($app[\'roles\'] ?? [])
                        ->first()[\'name\'] ?? \'User\',
                    \'enabled\' => true
                ])
                ->values()
                ->toArray();

            return \\response()->json([
                \'success\' => true,
                \'applications\' => $applications,
                \'total\' => \\count($applications)
            ]);
        } catch (\\Exception $e) {
            \\Log::error(\'Failed to fetch user applications\', [
                \'error\' => $e->getMessage(),
                \'user_id\' => \\auth()->id()
            ]);

            return \\response()->json([
                \'error\' => \'Server error\',
                \'applications\' => []
            ], 500);
        }
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000013b90000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'api.user-applications',
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
    'laporan.indikator-mutu.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/{indicator}/{periode}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ImutIndicatorReportController@show',
        'controller' => 'App\\Http\\Controllers\\ImutIndicatorReportController@show',
        'as' => 'laporan.indikator-mutu.show',
        'namespace' => NULL,
        'prefix' => '/laporan/indikator-mutu',
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
        'indicator' => '[a-z0-9-]+',
        'periode' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'laporan.indikator-mutu.detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/{indicator}/{periode}/{filter_periode?}/{catatan?}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ImutIndicatorReportController@detail',
        'controller' => 'App\\Http\\Controllers\\ImutIndicatorReportController@detail',
        'as' => 'laporan.indikator-mutu.detail',
        'namespace' => NULL,
        'prefix' => '/laporan/indikator-mutu',
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
        'indicator' => '[a-z0-9-]+',
        'periode' => '[0-9]+',
        'filter_periode' => '[a-z_]+',
        'catatan' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'laporan.indikator-mutu.by-category' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/kategori',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryReportController@show',
        'controller' => 'App\\Http\\Controllers\\CategoryReportController@show',
        'as' => 'laporan.indikator-mutu.by-category',
        'namespace' => NULL,
        'prefix' => '/laporan/indikator-mutu',
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
    'laporan.indikator-mutu.by-category.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/kategori/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\CategoryReportPdfController@download',
        'controller' => 'App\\Http\\Controllers\\CategoryReportPdfController@download',
        'as' => 'laporan.indikator-mutu.by-category.pdf',
        'namespace' => NULL,
        'prefix' => '/laporan/indikator-mutu',
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
    'laporan.indikator-mutu.unit-kerja.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/unit-kerja/{unitKerja}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\UnitKerjaLaporanController@show',
        'controller' => 'App\\Http\\Controllers\\UnitKerjaLaporanController@show',
        'as' => 'laporan.indikator-mutu.unit-kerja.show',
        'namespace' => NULL,
        'prefix' => 'laporan/indikator-mutu/unit-kerja',
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
    'laporan.indikator-mutu.unit-kerja.show-with-period' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'laporan/indikator-mutu/unit-kerja/{unitKerja}/{tipe}/{periode}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\UnitKerjaLaporanController@show',
        'controller' => 'App\\Http\\Controllers\\UnitKerjaLaporanController@show',
        'as' => 'laporan.indikator-mutu.unit-kerja.show-with-period',
        'namespace' => NULL,
        'prefix' => 'laporan/indikator-mutu/unit-kerja',
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
        'unitKerja' => '[a-z0-9-]+',
        'tipe' => 'yearly|quarterly|semester|custom',
        'periode' => '[a-zA-Z0-9\\-,]+',
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
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000001b990000000000000000";}}',
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
                }";s:5:"scope";s:38:"Dedoc\\Scramble\\ScrambleServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000190a0000000000000000";}}',
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
