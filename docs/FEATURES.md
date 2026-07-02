# Daftar Feature

Dokumen ini mendeskripsikan fitur utama pada project SI-IMUT. Informasi ini diproses oleh sistem RAG.

## FEAT-001 - ImutCapaianWidget Triwulan

Type: Feature
Status: Active
Area: Reporting
Related Services:
- SVC-001
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Filament/Widgets/ImutCapaianWidget.php
- commit: pending

Summary:
Pembaruan pada widget ImutCapaianWidget untuk menampilkan data tren capaian indikator mutu rumah sakit berdasarkan Triwulan (Quarter). Menggunakan visualisasi grafik garis mulus (smooth line chart) melintasi bulan dalam kuartal terpilih, menggantikan bar chart statis per laporan tunggal.
