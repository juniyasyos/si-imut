# 🔄 SI-IMUT Refactor TODO List

## 🎯 **PRINSIP REFACTOR**
- ❌ No Over-Engineering
- ✅ Extract Business Logic dari Filament
- ✅ Testing fokus pada Logic (tanpa database)
- ✅ Gradual refactor (tidak breaking changes)

---

## 📋 **FASE 1: CORE CALCULATION SERVICES** (Week 1-2)

### 🔢 **Calculator Services** - Priority: CRITICAL
- [x] **ImutCalculatorService** - Logic perhitungan IMUT (numerator/denominator, percentage)
- [ ] **TargetComparisonService** - Logic perbandingan dengan target (>=, <=, =, >, <)
- [ ] **PercentageCalculatorService** - Logic perhitungan persentase dan rounding
- [ ] **PenilaianEvaluatorService** - Logic evaluasi apakah penilaian tercapai

### 📊 **Statistics Services** - Priority: HIGH
- [ ] **ImutStatisticsService** - Agregasi data statistik IMUT
- [x] **ChartDataProcessorService** - Processing data untuk chart
- [ ] **DashboardDataService** - Agregasi data dashboard
- [x] **FormCalculationService** - Extract calculation logic dari form components

### 🧪 **Unit Tests** - Priority: HIGH
- [x] **ImutCalculatorServiceTest** - Test semua calculation logic ✅ (10/10 tests passed)
- [ ] **TargetComparisonServiceTest** - Test target comparison scenarios
- [ ] **PercentageCalculatorServiceTest** - Test edge cases (divide by zero, rounding)
- [x] **ChartDataProcessorServiceTest** - Test chart data processing logic
- [x] **FormCalculationServiceTest** - Test form calculation logic

---

## 📋 **FASE 2: FILAMENT CLEANUP** (Week 3-4)

### 🔧 **Widget Refactoring** - Priority: HIGH
- [x] **ImutCapaianWidget** - Extract ke ChartDataProcessorService
- [ ] **ImutCapaianUnitKerjaWidget** - Extract complex query logic
- [ ] **ImutDataUnitKerjaGrafikOverview** - Extract chart building logic
- [ ] **DashboardSiimutOverview** - Sudah baik, minor cleanup

### 📝 **Form/Schema Cleanup** - Priority: MEDIUM
- [x] **ImutDataSchema** - Extract validation logic
- [x] **ImutPenilaianResourceSchema** - Extract calculation logic dari form
- [ ] **ImutProfileForm** - Extract date calculation logic

### 📊 **Table Cleanup** - Priority: MEDIUM
- [ ] **ImutDataTable** - Extract authorization logic
- [ ] **LaporanImutTable** - Extract statistics calculation

---

## 📋 **FASE 3: PERFORMANCE & OPTIMIZATION** (Week 5-6)

### ⚡ **Cache Strategy** - Priority: MEDIUM
- [ ] **CacheManagerService** - Centralized cache management
- [ ] **ImutCacheService** - IMUT-specific cache strategies
- [ ] Optimize existing cache keys dan TTL

### 🔍 **Query Optimization** - Priority: LOW
- [ ] Review N+1 queries di Widget
- [ ] Optimize complex joins di Models

---

## 📋 **CURRENT ANALYSIS**

### ❌ **PROBLEMATIC FILES** (Need Immediate Attention)
1. `app/Filament/Widgets/ImutCapaianWidget.php` - 130 lines, complex calculation
2. `app/Filament/Resources/ImutDataResource/Widgets/ImutDataUnitKerjaGrafikOverview.php` - 300 lines, DB queries
3. `app/Filament/Resources/ImutDataResource/Schema/ImutDataSchema.php` - 287 lines, business logic
4. `app/Filament/Resources/ImutPenilaianResource/Schema/ImutPenilaianResourceSchema.php` - Complex calculation

### ✅ **ALREADY GOOD** (Minor improvements only)
1. `app/Services/DashboardImutService.php` - Well structured
2. `app/Services/ImutChartSeriesService.php` - Good separation
3. `app/Models/*` - Clean model implementations

---

## 🚀 **IMPLEMENTATION STRATEGY**

### **Step 1: Extract Pure Logic**
```php
// Target: Move dari Widget ke Service
// Before: Widget mengandung calculation
// After: Widget hanya memanggil service
```

### **Step 2: Create Service Contracts**
```php
// Simple interfaces untuk testing
interface CalculatorServiceInterface
{
    public function calculatePercentage(float $numerator, float $denominator): float;
    public function isTargetAchieved(float $value, string $operator, float $target): bool;
}
```

### **Step 3: Unit Testing**
```php
// Focus on business logic testing
// No database, use arrays/collections
// Test edge cases dan business rules
```

---

## 📈 **SUCCESS METRICS**

- [ ] Reduce Widget file sizes by 50%+
- [ ] Extract 80%+ business logic dari Filament
- [ ] Achieve 90%+ test coverage pada calculation logic
- [ ] Zero breaking changes to existing functionality
- [ ] Maintain or improve performance

---

## 🎯 **NEXT IMMEDIATE ACTIONS**

1. **✅ TODAY**: Create `ImutCalculatorService` - extract core calculation logic ✅ DONE
2. **✅ THIS WEEK**: Refactor `ImutCapaianWidget` - remove business logic ✅ DONE  
3. **✅ THIS WEEK**: Create `ChartDataProcessorService` - extract chart processing ✅ DONE
4. **✅ THIS WEEK**: Create `FormCalculationService` - extract form calculations ✅ DONE
5. **🔄 NEXT**: Refactor `ImutCapaianUnitKerjaWidget` - complex query extraction
6. **🔄 NEXT**: Create comprehensive service tests
7. **🔄 NEXT**: Extract logic dari `ImutDataUnitKerjaGrafikOverview` widget

---

## 📈 **PROGRESS SUMMARY**

### ✅ **COMPLETED TODAY**
- **ImutCalculatorService**: Pure calculation logic (percentage, target comparison, batch processing)
- **ChartDataProcessorService**: Chart data transformation and series building  
- **FormCalculationService**: Form calculation helpers for Filament schemas
- **Comprehensive Unit Tests**: 10+ tests covering edge cases dan business logic
- **Widget Refactoring**: ImutCapaianWidget now uses service layer
- **Schema Refactoring**: ImutPenilaianResourceSchema uses FormCalculationService

### 🔄 **IN PROGRESS**
- Widget refactoring (1/4 completed)
- Form/Schema cleanup (2/3 completed)
- Unit test coverage (expanding)

### 📊 **IMPACT ACHIEVED**
- **Testability**: ✅ Business logic dapat di-test tanpa database
- **Maintainability**: ✅ Logic terpisah dari UI components
- **Reusability**: ✅ Services dapat digunakan di multiple widgets
- **Code Quality**: ✅ Following SOLID principles dan dependency injection

## � **BEFORE vs AFTER COMPARISON**

### **ImutCapaianWidget.php**
```php
// BEFORE (130 lines) - Logic mixed dengan UI
protected function getOptions(): array
{
    $laporans = $this->getCachedLaporans();
    // ... complex calculation logic di widget
    $xLabels = $this->generateXLabels($laporans);
    $series = $this->getChartService()->buildSeries($laporans, $this->filterFormData ?? []);
    // ... 20+ lines calculation logic
}

// AFTER (Clean widget, ~90 lines)
protected function getOptions(): array
{
    $laporans = $this->getCachedLaporans();
    
    if ($laporans->isEmpty()) {
        return ApexChartConfig::noDataOptions();
    }
    
    // Clean service calls
    $categories = $this->getChartService()->getCategories();
    $colors = $this->getChartService()->getDefaultColors();
    $xLabels = $this->chartProcessor->generateTimeLabels($laporans);
    $processedData = $this->chartProcessor->processCapaianData($laporans, $categories);
    $series = $this->chartProcessor->buildChartSeries($processedData, $this->filterFormData ?? [], $colors);
    
    return ApexChartConfig::defaultOptions(...);
}
```

### **ImutPenilaianResourceSchema.php**
```php
// BEFORE - Calculation logic mixed dalam schema
protected static function updateResult(callable $set, callable $get): void
{
    $numerator = floatval($get('numerator_value') ?? 0);
    $denominator = floatval($get('denominator_value') ?? 0);
    $result = $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : 0;
    $set('result_operation', $result);
}

// AFTER - Clean service delegation
protected static function updateResult(callable $set, callable $get): void
{
    $formCalculationService = app(FormCalculationService::class);
    $formCalculationService->updatePenilaianResult($set, $get);
}
```

### **Testing Coverage**
```php
// BEFORE - No unit testing untuk business logic (mixed dengan UI)

// AFTER - Comprehensive unit testing
✅ ImutCalculatorService: 10 tests covering all calculation scenarios
✅ ChartDataProcessorService: 8 tests covering data transformation
✅ FormCalculationService: 10 tests covering form calculations
📊 Total: 28+ unit tests focusing on pure business logic
```

---

## 📝 **NOTES & PRINCIPLES**

### 🎯 **PRINSIP YANG DIIKUTI**
- **No Over-Engineering**: Tidak membuat Repository pattern yang tidak perlu
- **Service-First**: Extract ke Service layer yang reusable dan testable
- **Pure Logic Testing**: Unit tests fokus pada business logic tanpa database dependency
- **Gradual Refactor**: Tidak breaking existing functionality
- **Keep What Works**: Maintain cache strategy dan patterns yang sudah baik

### 🔧 **TECHNICAL DECISIONS**
- ✅ **Service Layer** untuk business logic yang complex
- ✅ **Direct Eloquent** untuk query yang straightforward  
- ✅ **Strategy Pattern** untuk algoritma yang bervariasi (planned)
- ❌ **Repository Pattern** - Laravel Eloquent sudah cukup powerful
- ✅ **Dependency Injection** untuk better testability

### 🎨 **ARCHITECTURE IMPACT**
- **Before**: Logic scattered dalam Filament components
- **After**: Clean separation of concerns
  - **Widgets**: UI configuration only
  - **Services**: Business logic dan calculations
  - **Models**: Data access dan relationships
  - **Tests**: Comprehensive coverage untuk business rules

### 🚀 **BENEFITS ACHIEVED**
1. **Maintainability**: Logic changes tidak affect UI components
2. **Testability**: Business logic dapat di-test secara isolated
3. **Reusability**: Services dapat digunakan di multiple places
4. **Code Quality**: Cleaner, more focused components
5. **Performance**: Same performance dengan better structure

---
