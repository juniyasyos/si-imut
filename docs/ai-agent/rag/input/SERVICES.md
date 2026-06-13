# Daftar Service

Dokumen ini berisi daftar service utama di SI-IMUT. Informasi ini diproses oleh sistem RAG.

## SVC-001 - ImutCalculationService

Type: Service
Status: Active
Area: Reporting
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Services/ImutCalculationService.php
- commit: 1be0c3f

Summary:
Service untuk melakukan kalkulasi pada skor kepatuhan laporan.

---

## SVC-002 - DailyReportCalculationService

Type: Service
Status: Active
Area: Daily Report
Related Services:
- SVC-001
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Services/DailyReportCalculationService.php
- commit: 601d0da

Summary:
Service untuk menghitung data dan indikator pada modul Daily Report.
