# Update: Periode Catatan IMUT - Dari Tanggal ke Triwulan/Tahunan

## Perubahan

Sistem periode catatan IMUT telah diupdate dari sistem **tanggal start/end** menjadi sistem **Triwulan dan Tahunan** yang lebih sesuai dengan konteks pelaporan IMUT.

## Alasan Perubahan

1. **Lebih Masuk Akal**: Laporan IMUT biasanya dibuat per periode triwulan atau tahunan, bukan per tanggal spesifik
2. **Konsisten dengan Laporan IMUT**: Sistem periode yang sama dengan laporan IMUT yang sudah ada
3. **Lebih Mudah Digunakan**: User hanya perlu memilih tahun dan triwulan (opsional)
4. **Query Lebih Efisien**: Filter berdasarkan tahun dan triwulan lebih cepat daripada range tanggal

## Struktur Database Baru

### Field yang Dihapus
- `period_start` (date)
- `period_end` (date)

### Field yang Ditambahkan
- `period_year` (year, nullable) - Tahun periode
- `period_quarter` (enum: Q1, Q2, Q3, Q4, nullable) - Triwulan
- `period_type` (enum: 'tahunan', 'triwulan', default: 'tahunan') - Tipe periode

### Definisi Triwulan
- **Q1**: Januari - Maret (Jan-Mar)
- **Q2**: April - Juni (Apr-Jun)
- **Q3**: Juli - September (Jul-Sep)
- **Q4**: Oktober - Desember (Oct-Des)

## Cara Penggunaan

### Membuat Catatan Tahunan

```php
ImutDataNote::create([
    'imut_data_id' => 1,
    'note_name' => 'Evaluasi Tahunan 2025',
    'period_year' => 2025,
    'period_type' => 'tahunan',
    'period_quarter' => null, // Tidak perlu diisi untuk tahunan
    // ... field lainnya
]);
```

**Tampilan**: "Tahunan 2025"

### Membuat Catatan Triwulan

```php
ImutDataNote::create([
    'imut_data_id' => 1,
    'note_name' => 'Evaluasi Triwulan 2',
    'period_year' => 2025,
    'period_type' => 'triwulan',
    'period_quarter' => 'Q2',
    // ... field lainnya
]);
```

**Tampilan**: "Triwulan II (Apr-Jun) 2025"

## Form Input

### Tipe Periode (Required)
- Radio/Select: **Tahunan** atau **Triwulan**
- Default: Tahunan

### Tahun (Optional)
- Dropdown tahun: 2020 - 2027 (5 tahun ke belakang, 2 tahun ke depan)
- Default: Tahun saat ini
- Searchable

### Triwulan (Conditional)
- Hanya muncul jika tipe periode = **Triwulan**
- Options:
  - Q1 (Jan-Mar)
  - Q2 (Apr-Jun)
  - Q3 (Jul-Sep)
  - Q4 (Oct-Des)
- Required jika tipe periode = Triwulan

## Model Updates

### Accessor Baru: `period_display`

Menampilkan periode dalam format human-readable:

```php
$note = ImutDataNote::find(1);
echo $note->period_display;

// Output contoh:
// - "Tahunan 2025"
// - "Triwulan II (Apr-Jun) 2025"
// - "-" (jika tidak ada periode)
```

### Scopes Baru

```php
// Filter berdasarkan tahun
$notes = ImutDataNote::byYear(2025)->get();

// Filter berdasarkan triwulan
$notes = ImutDataNote::byQuarter('Q2')->get();

// Filter berdasarkan tipe periode
$notes = ImutDataNote::byPeriodType('triwulan')->get();

// Kombinasi
$notes = ImutDataNote::forImutData(1)
    ->byYear(2025)
    ->byQuarter('Q2')
    ->active()
    ->get();
```

## Filter di Widget

Widget table sekarang memiliki filter tambahan:

1. **Tahun** - Filter catatan berdasarkan tahun
2. **Tipe Periode** - Filter tahunan atau triwulan
3. **Triwulan** - Filter berdasarkan Q1, Q2, Q3, atau Q4
4. **Prioritas** - High, Medium, Low (existing)
5. **Status** - Aktif/Tidak Aktif (existing)

## Migration

### Migration File
- **File**: `database/migrations/2025_11_11_000912_update_imut_data_notes_period_fields.php`

### Running Migration

```bash
# Jalankan migration
php artisan migrate

# Rollback jika perlu (akan mengembalikan ke struktur lama)
php artisan migrate:rollback --step=1
```

### Data Migration

**PENTING**: Migration ini akan menghapus data di kolom `period_start` dan `period_end` yang sudah ada!

Jika Anda memiliki data penting, lakukan backup terlebih dahulu:

```bash
# Backup database
mysqldump -u username -p database_name imut_data_notes > backup_notes.sql

# Atau export via SQL
SELECT * FROM imut_data_notes INTO OUTFILE '/tmp/notes_backup.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Konversi Data Lama (Manual)

Jika perlu konversi data dari sistem lama:

```php
use App\Models\ImutDataNote;
use Carbon\Carbon;

// Contoh: Convert period_start/end ke quarter/year
$notes = ImutDataNote::withTrashed()->get();

foreach ($notes as $note) {
    if ($note->period_start) {
        $date = Carbon::parse($note->period_start);
        $note->period_year = $date->year;
        $note->period_quarter = 'Q' . $date->quarter;
        $note->period_type = 'triwulan';
        $note->save();
    }
}
```

## UI Changes

### Form
- ❌ Removed: Date picker untuk period_start
- ❌ Removed: Date picker untuk period_end
- ✅ Added: Select tipe periode (Tahunan/Triwulan)
- ✅ Added: Select tahun dengan range dinamis
- ✅ Added: Select triwulan (conditional)
- ✅ Added: Reactive form (triwulan muncul jika tipe = triwulan)

### Table Column
- Updated: Kolom "Periode" sekarang menampilkan format baru
- Sortable: Berdasarkan tahun dan triwulan
- Searchable: Dapat mencari berdasarkan periode

### Detail Modal
- Updated: Menampilkan periode dengan format "Tahunan 2025" atau "Triwulan II (Apr-Jun) 2025"

## Testing

### Test Manual Checklist

- [ ] Create catatan dengan periode tahunan
- [ ] Create catatan dengan periode triwulan Q1
- [ ] Create catatan dengan periode triwulan Q2
- [ ] Create catatan dengan periode triwulan Q3
- [ ] Create catatan dengan periode triwulan Q4
- [ ] Create catatan tanpa periode (all nullable)
- [ ] Edit catatan dari tahunan ke triwulan
- [ ] Edit catatan dari triwulan ke tahunan
- [ ] Filter berdasarkan tahun
- [ ] Filter berdasarkan triwulan
- [ ] Filter berdasarkan tipe periode
- [ ] Kombinasi multiple filters
- [ ] Sorting by periode
- [ ] View detail catatan dengan periode baru
- [ ] Form validation (triwulan required when type = triwulan)

### Unit Test Example

```php
use App\Models\ImutDataNote;
use Tests\TestCase;

class ImutDataNotePeriodTest extends TestCase
{
    public function test_can_create_annual_note()
    {
        $note = ImutDataNote::create([
            'imut_data_id' => 1,
            'note_name' => 'Test Annual',
            'period_year' => 2025,
            'period_type' => 'tahunan',
            'priority' => 'medium',
            'created_by' => 1,
        ]);
        
        $this->assertEquals('Tahunan 2025', $note->period_display);
    }
    
    public function test_can_create_quarterly_note()
    {
        $note = ImutDataNote::create([
            'imut_data_id' => 1,
            'note_name' => 'Test Quarterly',
            'period_year' => 2025,
            'period_quarter' => 'Q2',
            'period_type' => 'triwulan',
            'priority' => 'medium',
            'created_by' => 1,
        ]);
        
        $this->assertEquals('Triwulan II (Apr-Jun) 2025', $note->period_display);
    }
    
    public function test_scope_by_year()
    {
        ImutDataNote::factory()->count(3)->create(['period_year' => 2025]);
        ImutDataNote::factory()->count(2)->create(['period_year' => 2024]);
        
        $notes = ImutDataNote::byYear(2025)->get();
        
        $this->assertCount(3, $notes);
    }
}
```

## Query Examples

### Get Notes untuk Tahun Tertentu

```php
$notes2025 = ImutDataNote::byYear(2025)->get();
```

### Get Notes untuk Triwulan Tertentu

```php
$notesQ2 = ImutDataNote::byYear(2025)
    ->byQuarter('Q2')
    ->get();
```

### Get All Tahunan Notes

```php
$annualNotes = ImutDataNote::byPeriodType('tahunan')->get();
```

### Get All Triwulan Notes

```php
$quarterlyNotes = ImutDataNote::byPeriodType('triwulan')->get();
```

### Group by Year and Quarter

```php
$grouped = ImutDataNote::query()
    ->select('period_year', 'period_quarter', DB::raw('count(*) as total'))
    ->groupBy('period_year', 'period_quarter')
    ->orderBy('period_year', 'desc')
    ->orderBy('period_quarter')
    ->get();
```

## API Response Example

```json
{
  "id": 1,
  "imut_data_id": 5,
  "note_name": "Evaluasi Triwulan II",
  "period_year": 2025,
  "period_quarter": "Q2",
  "period_type": "triwulan",
  "period_display": "Triwulan II (Apr-Jun) 2025",
  "recommendation": "Tingkatkan kualitas data",
  "analysis": "Data menunjukkan peningkatan",
  "priority": "high",
  "is_active": true,
  "created_at": "2025-11-11T00:00:00.000000Z"
}
```

## Benefits

1. ✅ **Lebih Intuitif**: User lebih familiar dengan konsep triwulan/tahunan
2. ✅ **Konsisten**: Selaras dengan sistem laporan IMUT yang ada
3. ✅ **Flexible**: Bisa pilih tahunan atau triwulan sesuai kebutuhan
4. ✅ **Query Efficient**: Index pada year dan quarter meningkatkan performa
5. ✅ **Easy Filtering**: Filter lebih mudah dengan dropdown tahun dan triwulan
6. ✅ **Better UX**: Form lebih sederhana, tidak perlu pilih tanggal spesifik

## Rollback Guide

Jika perlu rollback ke sistem lama:

```bash
# Rollback migration
php artisan migrate:rollback --step=1

# Ini akan:
# - Menghapus kolom: period_year, period_quarter, period_type
# - Mengembalikan kolom: period_start, period_end
```

**Note**: Data di kolom baru akan hilang setelah rollback!

## Support & Documentation

- Full documentation: `docs/imut-data-notes-feature.md`
- Quick reference: `docs/imut-data-notes-quick-reference.md`
- Update notes: `docs/imut-data-notes-update-periode.md` (this file)
