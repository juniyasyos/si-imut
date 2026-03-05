#!/bin/bash

set -e  # Exit on error

DB_USER="siimut"
DB_PASS="password-siimut"
DB_NAME="siimut_prod"
OUTPUT_FILE="laporan_feb_2026_insert.sql"

echo "-- Insert statements untuk Laporan IMUT Februari 2026" > "$OUTPUT_FILE"
echo "-- Generated: $(date)" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

# Export laporan_imuts
echo "-- ====== LAPORAN_IMUTS ======" >> "$OUTPUT_FILE"
docker compose exec -T db mysql -u $DB_USER -p"$DB_PASS" $DB_NAME << 'QUERY1' >> "$OUTPUT_FILE"
SELECT CONCAT(
    'INSERT INTO laporan_imuts (id, name, slug, status, assessment_period_start, assessment_period_end, report_month, report_year, recommendation_analysis_duration, created_by, is_auto_generated, created_at, updated_at) VALUES (',
    id, ', ',
    QUOTE(name), ', ',
    QUOTE(slug), ', ',
    QUOTE(status), ', ',
    QUOTE(assessment_period_start), ', ',
    QUOTE(assessment_period_end), ', ',
    report_month, ', ',
    report_year, ', ',
    IFNULL(recommendation_analysis_duration, 0), ', ',
    created_by, ', ',
    is_auto_generated, ', ',
    QUOTE(created_at), ', ',
    QUOTE(updated_at),
    ');'
) as stmt
FROM laporan_imuts
WHERE report_month = 2 AND report_year = 2026 AND deleted_at IS NULL;
QUERY1

echo "" >> "$OUTPUT_FILE"
echo "-- ====== LAPORAN_UNIT_KERJAS ======" >> "$OUTPUT_FILE"

# Export laporan_unit_kerjas untuk laporan Februari 2026
docker-compose exec -T db mysql -u $DB_USER -p"$DB_PASS" $DB_NAME << 'QUERY2' >> "$OUTPUT_FILE"
SELECT CONCAT(
    'INSERT INTO laporan_unit_kerjas (id, laporan_imut_id, unit_kerja_id, created_at, updated_at) VALUES (',
    luk.id, ', ',
    luk.laporan_imut_id, ', ',
    luk.unit_kerja_id, ', ',
    QUOTE(luk.created_at), ', ',
    QUOTE(luk.updated_at),
    ');'
) as stmt
FROM laporan_unit_kerjas luk
INNER JOIN laporan_imuts li ON luk.laporan_imut_id = li.id
WHERE li.report_month = 2 AND li.report_year = 2026 AND li.deleted_at IS NULL;
QUERY2

echo "" >> "$OUTPUT_FILE"
echo "-- ====== IMUT_PENILAIANS ======" >> "$OUTPUT_FILE"

# Export imut_penilaians untuk laporan Februari 2026
docker-compose exec -T db mysql -u $DB_USER -p"$DB_PASS" $DB_NAME << 'QUERY3' >> "$OUTPUT_FILE"
SELECT CONCAT(
    'INSERT INTO imut_penilaians (id, imut_profil_id, laporan_unit_kerja_id, analysis, recommendations, numerator_value, denominator_value, is_auto_calculated, calculation_metadata, created_at, updated_at) VALUES (',
    ip.id, ', ',
    ip.imut_profil_id, ', ',
    ip.laporan_unit_kerja_id, ', ',
    QUOTE(ip.analysis), ', ',
    QUOTE(ip.recommendations), ', ',
    IFNULL(ip.numerator_value, 'NULL'), ', ',
    IFNULL(ip.denominator_value, 'NULL'), ', ',
    ip.is_auto_calculated, ', ',
    QUOTE(ip.calculation_metadata), ', ',
    QUOTE(ip.created_at), ', ',
    QUOTE(ip.updated_at),
    ');'
) as stmt
FROM imut_penilaians ip
INNER JOIN laporan_unit_kerjas luk ON ip.laporan_unit_kerja_id = luk.id
INNER JOIN laporan_imuts li ON luk.laporan_imut_id = li.id
WHERE li.report_month = 2 AND li.report_year = 2026 AND li.deleted_at IS NULL;
QUERY3

echo ""
echo "✓ File generated: $OUTPUT_FILE"
echo "✓ Untuk import ke database baru:"
echo "  mysql -u user -p database_target < $OUTPUT_FILE"
