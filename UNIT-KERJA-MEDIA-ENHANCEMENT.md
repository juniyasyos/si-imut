# 📁 Unit Kerja Media Structure Enhancement

## 🎯 Overview
Peningkatan sistem manajemen media untuk Unit Kerja dengan struktur folder terorganisir dan sistem migrasi untuk data existing (8 bulan).

---

## 🔧 Changes Summary

### 1. **Unit Kerja Folder Sync System**
- **Command**: `php artisan unit-kerja:sync-folders`
- **Purpose**: Sinkronisasi folder structure untuk unit kerja setelah database migration
- **Features**: Duplicate detection, force recreation, specific unit targeting

### 2. **Enhanced Media Structure**
- **Old**: `uploads/imut-documents/igd/file.pdf`
- **New**: `uploads/imut-documents/igd-laporan-imut/Juni 2025/file.pdf`
- **Purpose**: Periode-based organization untuk IMUT documents

### 3. **Media Migration System**
- **Command**: `php artisan media:migrate-unit-kerja-structure`
- **Purpose**: Migrate 1,911+ existing media files ke struktur baru
- **Features**: Analysis, backup, selective migration

---

## 📂 New Folder Structure

### **Unit Kerja Main & Subfolders**
```
Main Folder:
📁 igd (Collection: "igd")

Subfolders:
📁 igd-laporan-imut      → Laporan IMUT (dengan periode)
📁 igd-dokumen-mutu      → Dokumen Mutu  
📁 igd-sop-panduan       → SOP & Panduan
📁 igd-data-pendukung    → Data Pendukung
📁 igd-evaluasi-audit    → Evaluasi & Audit
```

### **Periode-based Storage (Laporan IMUT Only)**
```
📁 igd-laporan-imut/
   📁 Januari 2025/
      📄 file1.pdf
      📄 dokumen1.xlsx
   📁 Februari 2025/
      📄 file2.pdf
   📁 Juni 2025/
      📄 file3.pdf

📁 igd-dokumen-mutu/
   📄 sop-direct.pdf      (tanpa periode)
   📄 panduan.doc
```

---

## 🚀 Commands Reference

### **Unit Kerja Folder Sync**

#### Check Status
```bash
php artisan unit-kerja:sync-folders --check
```
**Output**: Analysis table dengan status Complete/Missing/Broken

#### Dry Run (Simulation)
```bash
php artisan unit-kerja:sync-folders --dry-run
```
**Output**: Preview apa yang akan dilakukan tanpa execute

#### Sync Missing Folders
```bash
php artisan unit-kerja:sync-folders
```
**Features**: 
- Auto-create missing main & subfolders
- Duplicate prevention
- Progress tracking

#### Force Recreate (Cleanup Duplicates)
```bash
php artisan unit-kerja:sync-folders --force
```
**Use Case**: Clean up duplicate folders dari migration errors

#### Specific Unit
```bash
php artisan unit-kerja:sync-folders --unit-id=1
php artisan unit-kerja:sync-folders --unit-slug=igd
```

#### Automation Mode
```bash
php artisan unit-kerja:sync-folders --no-interaction
```

---

### **Media Structure Migration**

#### Analysis Mode
```bash
php artisan media:migrate-unit-kerja-structure --check
```
**Output**:
- ✅ Already Migrated: Files sudah di struktur baru
- ⏯️ Needs Migration: Files perlu migrate
- ❌ Cannot Map: Files yang ga bisa resolve periode

#### Dry Run Migration
```bash
php artisan media:migrate-unit-kerja-structure --dry-run
```
**Output**: Preview file movements tanpa execute

#### Full Migration with Backup
```bash
php artisan media:migrate-unit-kerja-structure --backup
```
**Features**:
- Create backup sebelum migrate
- Move physical files
- Update database records

#### Selective Migration
```bash
php artisan media:migrate-unit-kerja-structure --unit-slug=igd --unit-slug=laboratorium
```

---

## 🏗️ Code Changes

### **1. Model Enhancements**

#### **LaporanImut.php**
```php
/**
 * Get periode folder name for media storage
 */
public function getPeriodeFolderName(): string
{
    return $this->period_name; // "Juni 2025"
}

/**
 * Static method untuk generate periode folder name
 */
public static function generatePeriodeFolderName(int $month, int $year): string
{
    $monthNames = [1 => 'Januari', 2 => 'Februari', ...];
    return ($monthNames[$month] ?? $month) . ' ' . $year;
}
```

#### **ImutPenilaian.php** (Existing)
```php
use Spatie\MediaLibrary\InteractsWithMedia;

public function registerMediaCollections(): void
{
    $this->addMediaCollection('documents')->useDisk('public');
}
```

### **2. Repository Enhancements**

#### **UnitKerjaFolderRepository.php** 
```php
public function createFolder(UnitKerja $unitKerja): void
{
    // Duplicate prevention
    $existingMainFolder = Folder::where('collection', $collection)
        ->whereNull('parent_id')->first();
        
    if ($existingMainFolder) {
        Log::warning("⚠️ Main folder already exists...");
        $this->ensureSubfoldersExist($existingMainFolder, $unitKerja);
        return;
    }
    
    // Create main + 5 subfolders
}

private function ensureSubfoldersExist(Folder $mainFolder, UnitKerja $unitKerja): void
{
    // Auto-create missing subfolders only
}
```

### **3. Observer Enhancements**

#### **UnitKerjaObserver.php**
```php
public function created(UnitKerja $unitKerja): void
{
    // Additional duplicate check
    $existingFolder = Folder::where('collection', $collection)
        ->whereNull('parent_id')->exists();
        
    if ($existingFolder) {
        Log::warning("⚠️ Folder sudah ada, skip pembuatan...");
        return;
    }
    
    $this->repository->createFolder($unitKerja);
}
```

### **4. Upload Logic Enhancement**

#### **UnitKerjaImutDataDetailReport.php**
```php
protected function getUploadDirectory(string $collection): string
{
    $baseDirectory = 'uploads/imut-documents/' . $collection;
    
    // Periode folder hanya untuk laporan-imut
    if (str_ends_with($collection, '-laporan-imut')) {
        if ($this->laporan) {
            $periodFolder = $this->laporan->getPeriodeFolderName();
            return $baseDirectory . '/' . $periodFolder;
        }
    }
    
    return $baseDirectory; // Direct untuk subfolder lain
}

protected function getMediaUploadFieldForAction($livewireComponent): array
{
    return [
        SpatieMediaLibraryFileUpload::make('document_upload')
            ->collection(fn(callable $get) => $get('selected_collection'))
            ->directory(fn(callable $get) => $this->getUploadDirectory($get('selected_collection')))
            // ... other configs
    ];
}
```

---

## 🔄 Migration Logic Flow

### **Phase 1: Analysis**
```
1. Scan semua Media records (model_type = ImutPenilaian)
2. Group by collection_name (unit slug)
3. Resolve ImutPenilaian → LaporanUnitKerja → LaporanImut
4. Generate new path: unit-slug-laporan-imut/Periode/file.ext
5. Categorize: Needs Migration / Already Migrated / Cannot Map
```

### **Phase 2: Migration**  
```
1. Create backup (optional)
2. For each file needing migration:
   a. Create new directory structure
   b. Move physical file
   c. Update Media record (collection_name, file paths)
3. Progress tracking & error handling
4. Summary report
```

---

## 📊 Expected Migration Results

### **Sample Data (1,911 files)**
- ✅ **Already Migrated**: 0 files (0%)
- ⏯️ **Needs Migration**: 1,694 files (88.6%)  
- ❌ **Cannot Map**: 217 files (11.4%)

### **Before Migration**
```
Collection: "igd"          → Path: igd/file.pdf
Collection: "hemodialisa"  → Path: hemodialisa/doc.pdf
Collection: "gizi"         → Path: gizi/laporan.xlsx
```

### **After Migration**
```
Collection: "igd-laporan-imut"          → Path: igd-laporan-imut/Juni 2025/file.pdf
Collection: "hemodialisa-laporan-imut"  → Path: hemodialisa-laporan-imut/Februari 2025/doc.pdf
Collection: "gizi-laporan-imut"         → Path: gizi-laporan-imut/September 2025/laporan.xlsx
```

---

## ⚠️ Important Notes

### **Duplicate Prevention System**
- **Repository level**: Check existing folders before create
- **Observer level**: Additional safety check on Unit creation
- **Command level**: Detection & cleanup via `--force` mode

### **Backup Strategy**
- Command option: `--backup` 
- Creates timestamped backup folder
- Includes media table export & file structure backup

### **Error Handling**
- **Cannot Map files**: Manual intervention needed
- **Missing LaporanImut**: Cannot resolve periode info
- **File move failures**: Logged dengan rollback capability  

### **Rollback Strategy**
- Backup media table records sebelum migration
- Physical file backup 
- Database rollback via backup restoration

---

## 🧪 Testing Scenarios

### **Unit Kerja Folder Sync**
```bash
# Test 1: Check semua unit kerja
php artisan unit-kerja:sync-folders --check

# Test 2: Dry run sebelum execute  
php artisan unit-kerja:sync-folders --dry-run

# Test 3: Test duplicate cleanup
php artisan unit-kerja:sync-folders --unit-id=1 --force

# Test 4: Automation mode
php artisan unit-kerja:sync-folders --no-interaction
```

### **Media Migration**
```bash  
# Test 1: Analysis only
php artisan media:migrate-unit-kerja-structure --check

# Test 2: Selective dry run
php artisan media:migrate-unit-kerja-structure --unit-slug=igd --dry-run

# Test 3: Single unit migration
php artisan media:migrate-unit-kerja-structure --unit-slug=igd --backup
```

### **Upload Functionality**
```bash
# Test via tinker
$laporan = LaporanImut::find(2); // Juni 2025
$dir = testUploadDirectory('igd-laporan-imut', $laporan);
// Result: uploads/imut-documents/igd-laporan-imut/Juni 2025
```

---

## 🎯 Production Deployment Steps

### **Step 1: Folder Structure Setup**
```bash
php artisan unit-kerja:sync-folders --check
php artisan unit-kerja:sync-folders
```

### **Step 2: Media Migration (Gradual)**
```bash
# Analysis first
php artisan media:migrate-unit-kerja-structure --check

# Test dengan 1 unit
php artisan media:migrate-unit-kerja-structure --unit-slug=igd --backup --dry-run
php artisan media:migrate-unit-kerja-structure --unit-slug=igd --backup

# Full migration setelah test berhasil
php artisan media:migrate-unit-kerja-structure --backup
```

### **Step 3: Verification**
- Check folder structure di storage/app/public/
- Verify media records di database  
- Test upload functionality di application
- Check existing file access

---

## 📈 Benefits Achieved

1. **🗂️ Organized Structure**: Periode-based organization untuk IMUT documents
2. **🔧 Maintainable**: Clear separation antara laporan vs dokumen lain
3. **🚫 Duplicate Prevention**: Robust system prevent folder duplication  
4. **⚡ Scalable**: Easy expansion untuk unit kerja baru
5. **🛡️ Safe Migration**: Backup & rollback capabilities
6. **📊 Trackable**: Progress monitoring & error logging
7. **🎯 Selective**: Granular control untuk migration process

---

*✨ Enhanced Sistem Unit Kerja Media Structure - Ready untuk Production! 🚀*