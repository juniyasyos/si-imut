# Legacy Namespace Autoload Failures After Domain Refactor

## Ringkasan
Refactor domain memindahkan model dan policy dari `App\Models\*` / `App\Policies\*` ke `App\Domains\{Domain}\Models\*` dan `App\Domains\{Domain}\Policies\*`. Namun beberapa bagian aplikasi (terutama plugin Filament dan cache perizinan) masih mereferensikan namespace lama, sehingga Composer mencoba meng-autoload file seperti `app/Models/ImutData.php`. Karena file tersebut tidak ada lagi, aplikasi memunculkan error:

```
include(.../app/Models/ImutData.php): Failed to open stream: No such file or directory
```

## Dampak
- Semua request yang memicu referensi legacy (`App\Models\…` atau `App\Policies\…`) gagal dengan 500 Internal Server Error.
- Pemicu utama berasal dari:
  - Gate dan Filament Shield yang mendaftarkan policy berdasarkan konvensi `App\Policies\{Model}Policy`.
  - Hook Filament / plugin lain yang masih menargetkan model lama.
  - Data persisten (permission cache, activity log, morph map) yang menyimpan nama class lama.
- Setelah satu model dipindah, error akan berulang untuk model/policy lain selama namespace lama masih digunakan.

## Opsi Solusi
1. **Lapisan kompatibilitas (disarankan jangka pendek)**
   - Pada bootstrap aplikasi, lakukan scanning folder `app/Domains/*/Models` dan `app/Domains/*/Policies`.
   - Jika class `App\Models\X`/`App\Policies\XPolicy` belum ada tetapi versi domainnya ada, daftarkan `class_alias()`.
   - Tambahkan `Relation::morphMap()` untuk memetakan alias lama → baru bila diperlukan.
   - Keuntungan: cepat mengembalikan panel ke kondisi normal; tidak perlu menyentuh plugin atau data historis.

2. **Migrasi penuh namespace (jangka panjang)**
   - Update semua referensi hard-coded (plugin, konfigurasi, policy mapping) ke namespace domain.
   - Migrasi data persisten yang menyimpan nama class (misal di tabel permission/activity).
   - Hapus alias setelah semua referensi dan data dibersihkan.

## Status Saat Ini
- Alias manual sudah dibuat untuk `ImutProfile` (model & policy) di `app/Models` dan `app/Policies`.
- Error selanjutnya muncul untuk `ImutData`, menandakan kebutuhan alias berskala besar atau pembaruan konfigurasi plugin.

## Saran Tindak Lanjut
- Implementasikan mekanisme alias otomatis agar namespace lama tetap dikenali hingga seluruh ekosistem (plugin + data) dimigrasikan.
- Setelah panel stabil, audit referensi/plugin untuk merencanakan migrasi namespace permanen.

