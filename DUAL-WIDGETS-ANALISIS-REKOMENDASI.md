# 📊 Dual Widget Pengisian Analisis & Rekomendasi - Dokumentasi

## 🎯 Overview

Sistem widget **dual-purpose** yang menampilkan informasi pengisian analisis dan rekomendasi dengan perspektif berbeda sesuai dengan role dan unit kerja user:

1. **RecommendationAnalysisTimMutuWidget** - Untuk Tim Mutu & Admin
2. **RecommendationAnalysisUnitKerjaWidget** - Untuk User dengan Unit Kerja

### 🔀 Auto-Detection Logic

Widget dipilih secara otomatis berdasarkan role dan unit kerja user:

```
┌─ User Authenticated
│
├─ Has Role: Tim Mutu / Admin?
│  └─ YES → Tampilkan RecommendationAnalysisTimMutuWidget
│
├─ No Role Tim Mutu, Tapi Ada Unit Kerja?
│  └─ YES → Tampilkan RecommendationAnalysisUnitKerjaWidget
│
└─ No Role & No Unit Kerja?
   └─ NO WIDGET
```

---

## 📁 File Structure

```
app/Filament/Widgets/
├── RecommendationAnalysisTimMutuWidget.php      ← Widget untuk Tim Mutu
├── RecommendationAnalysisUnitKerjaWidget.php    ← Widget untuk Unit Kerja

resources/views/filament/widgets/
├── recommendation-analysis-tim-mutu-widget.blade.php       ← View Tim Mutu
├── recommendation-analysis-unit-kerja-widget.blade.php     ← View Unit Kerja

app/Filament/Resources/LaporanImutResource/Pages/
├── ListLaporanImuts.php    ← Conditional getHeaderWidgets()
```

---

## 🎯 Widget #1: RecommendationAnalysisTimMutuWidget

### Untuk Siapa?
- ✅ Super Admin
- ✅ Admin
- ✅ Tim Mutu

### Apa yang Ditampilkan?

1. **Overview Semua Laporan**
   - Daftar laporan dalam fase analisis
   - Sorted by deadline (terdekat duluan)

2. **Most Urgent Report (Highlighted)**
   - Status badge (URGENT/PERHATIAN/BERLANGSUNG)
   - Deadline countdown
   - **Detail per Unit Kerja** (scrollable list)
     - Nama unit kerja
     - Completed/Total items
     - Status indicator (✅ Selesai / ⚠️ Progress)

3. **Overall Progress Bar**
   ```
   Pengisian: 5/8 unit kerja (62%)
   [████░░░░] 62%
   ```

4. **Unit Kerja Details (Expandable)**
   - Bisa melihat detail pengisian tiap unit kerja
   - Max height 200px dengan scroll untuk banyak unit

### Key Data Points
- Total units yang terlibat
- Completed units
- Percentage completion
- Detailed breakdown per unit kerja

### Use Cases
- 🎯 **Monitoring Overall Progress**: Lihat progress semua unit kerja dalam 1 view
- 🔔 **Identify Bottlenecks**: Langsung lihat unit mana yang belum selesai
- ⏱️ **Deadline Management**: Priority management berdasarkan days remaining
- 📊 **Reporting**: Quick overview untuk report/meeting

---

## 🎯 Widget #2: RecommendationAnalysisUnitKerjaWidget

### Untuk Siapa?
- ✅ User dengan Unit Kerja (PIC, Pengumpul Data)
- ❌ Tidak punya role Tim Mutu/Admin
- ❌ Minimal harus memiliki 1 unit kerja

### Apa yang Ditampilkan?

1. **Laporan Relevan Untuk User**
   - Hanya laporan yang melibatkan unit kerja user
   - Sorted by deadline (terdekat duluan)

2. **Most Urgent Report (Highlighted)**
   - Status badge
   - Deadline countdown
   - **Detail Pengisian Unit Kerja User SAJA**
     ```
     Pengisian Analisis Unit Kerja Anda:
     
     ┌─ Unit Kerja A                      ✅ Selesai
     │  3/3 item selesai
     │  [████████████] 100%
     │
     └─ Unit Kerja B                      50%
        2/4 item selesai
        [██████░░░░░] 50%
     ```

3. **Other Reports (Collapsible)**
   - Minimal info (name, deadline, days left)
   - Per-unit progress untuk unit kerja mereka

4. **Focused Information**
   - Hanya info yang relevan untuk mereka
   - Simple & tidak overload

### Key Data Points
- Laporan yang melibatkan unit kerja mereka saja
- Pengisian untuk tiap unit mereka
- Progress per item dalam unit mereka
- Completion status

### Use Cases
- 📝 **Know My Tasks**: User lihat laporan apa saja yang harus mereka kerjakan
- ✅ **Track Progress**: Monitor progress pengisian mereka sendiri
- ⏰ **Deadline Awareness**: Tahu kapan deadline untuk setiap laporan
- 🎯 **Focus Effort**: Fokus pada laporan yang paling urgent

---

## 🔧 Implementation Details

### Conditional Display in ListLaporanImuts

```php
protected function getHeaderWidgets(): array
{
    $user = Auth::user();
    
    if (!$user) {
        return [];
    }
    
    // Tim Mutu/Admin gets comprehensive view
    if ($user->hasAnyRole(['super_admin', 'admin', 'tim_mutu'])) {
        return [
            RecommendationAnalysisTimMutuWidget::class,
        ];
    }
    
    // Unit Kerja users get focused view
    if ($user->unitKerjas()->exists()) {
        return [
            RecommendationAnalysisUnitKerjaWidget::class,
        ];
    }
    
    return [];
}
```

### Permission Checks

**Tim Mutu Widget:**
```php
public static function canView(): bool
{
    return Auth::user()?->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);
}
```

**Unit Kerja Widget:**
```php
public static function canView(): bool
{
    $user = Auth::user();
    if (!$user) return false;
    
    $hasUnitKerja = $user->unitKerjas()->exists();
    $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);
    
    return $hasUnitKerja && !$isAdminOrTimMutu;
}
```

---

## 📊 Data Calculation

### Tim Mutu Widget - getOverallCompletionStats()

**Menghitung** pengisian per unit kerja:
```
For each unit kerja di laporan:
  total_penilaians = count of all imut_penilaians
  completed = count where (analisis IS NOT NULL OR rekomendasi IS NOT NULL)
  percentage = (completed / total) * 100
  
total_units = count of units
completed_units = count where percentage == 100
overall_percentage = (completed_units / total_units) * 100
```

### Unit Kerja Widget - getUserUnitKerjaCompletionStats()

**Menghitung** pengisian hanya untuk unit kerja yang dihuni user:
```
For each user's unit kerja:
  total_penilaians = count for this unit kerja
  completed = count where (analisis IS NOT NULL OR rekomendasi IS NOT NULL)
  percentage = (completed / total) * 100
  is_completed = (percentage == 100)
```

---

## 🎨 Visual Differences

### Tim Mutu Widget
```
📊 Monitoring Pengisian Analisis & Rekomendasi
   Overview status pengisian SEMUA unit kerja
   
   [3 Laporan Aktif]

   ┌─ MOST URGENT REPORT ──────────────────┐
   │ 🔴 URGENT | 1 Hari Tersisa           │
   │ Laporan IMUT Januari 2026             │
   │ Deadline: 31 Jan 2026                 │
   │                                       │
   │ Pengisian: 5/8 unit (62%)            │
   │ [████░░░░] 62%                        │
   │                                       │
   │ Detail Per Unit Kerja:                │
   │ • Unit A      ✅ 3/3                  │
   │ • Unit B      ⚠️  2/4                 │
   │ • Unit C      ⚠️  0/3                 │
   │                                       │
   │              [Lihat Detail] →         │
   └───────────────────────────────────────┘
```

### Unit Kerja Widget
```
📋 Pengisian Analisis & Rekomendasi
   Kolaborasi Anda dalam laporan berkala
   
   [1 Laporan Aktif]

   ┌─ MOST URGENT REPORT ──────────────────┐
   │ 🔵 BERLANGSUNG | 5 Hari Tersisa      │
   │ Laporan IMUT Januari 2026             │
   │ Deadline: 31 Jan 2026                 │
   │                                       │
   │ Pengisian Analisis Unit Kerja Anda:  │
   │                                       │
   │ ┌─ Unit Kerja A          100% ✅    │
   │ │ 3/3 item selesai                   │
   │ │ [████████████]                     │
   │                                       │
   │ └─ Unit Kerja B          67% ⚠️     │
   │   2/3 item selesai                    │
   │   [████████░░░░]                      │
   │                                       │
   │              [Lihat] →                │
   └───────────────────────────────────────┘
```

---

## ✅ Features Comparison

| Feature | Tim Mutu | Unit Kerja |
|---------|----------|-----------|
| **Scope** | Semua laporan & unit | Laporan relevan saja |
| **Detail** | Lengkap (all units) | Fokus (their units) |
| **Primary Use** | Monitoring & oversight | Task management |
| **Complexity** | Comprehensive | Simple & focused |
| **Scrollable** | Unit detail list | None |
| **Progress** | Overall + per-unit | Per-unit mereka |
| **Target User** | Admin/QA | PIC/Data entry |

---

## 🔐 Permission & Security

```
User Authentication Flow:

1. User Login (e.g., PIC with unit_kerja_id = 5)
2. Page Load: ListLaporanImuts
3. getHeaderWidgets() checks:
   - Has role Tim Mutu? NO
   - Has unit kerja? YES (ID: 5)
   - Result: Show RecommendationAnalysisUnitKerjaWidget
4. Widget Load:
   - getRelevantAnalysisReports() filters:
     - Only laporan where unit_kerja_id IN (5)
     - Only current user can see their own data
5. Data Display: Only their relevant reports shown
```

---

## 🚀 Future Enhancements

### 1. **Live Refresh** (Livewire Polling)
```php
// Both widgets
#[On('refreshAnalysisWidget')]
public function refresh()
{
    // Auto refresh every 5 minutes
}
```

### 2. **Export to Excel**
```php
// Tim Mutu Widget specific
public function exportProgress(): void
{
    // Export 'unit name | completed | total | %'
}
```

### 3. **Bulk Actions** (Tim Mutu only)
```php
// Send reminders to units
// Mark as completed
// Add notes
```

### 4. **Email Notifications**
```php
// Unit Kerja: reminder 1-2 days before deadline
// Tim Mutu: summary report every morning
```

### 5. **Progress History Chart**
```php
// Show completion trend over time
// Predict if deadline can be met
```

---

## 🐛 Troubleshooting

### Widget Tidak Muncul
**Solusi**:
1. Verify user punya unit_kerja atau role Tim Mutu
   ```bash
   php artisan tinker
   > $user = \App\Models\User::find(1);
   > $user->unitKerjas()->count();  // Should > 0
   > $user->roles()->pluck('name'); // Should contain role
   ```

2. Clear cache:
   ```bash
   php artisan view:clear && php artisan view:cache
   php artisan cache:clear
   ```

### Data Tidak Muncul
1. Verify ada laporan dalam status 'process'
   ```bash
   php artisan tinker
   > \App\Models\LaporanImut::where('status', 'process')->count();
   ```

2. Check laporan ada unit kerja terkait
   ```bash
   > $laporan = \App\Models\LaporanImut::first();
   > $laporan->unitKerjas()->count();
   ```

### Progress Bar Tidak Akurat
1. Verify `imut_penilaians` punya `analisis` atau `rekomendasi` field
2. Check data sudah di-update di database

---

## 📞 Support

Untuk pertanyaan atau issue, lihat dokumentasi atau hubungi tim development.

---

**Last Updated:** April 7, 2026
**Status:** ✅ Production Ready
