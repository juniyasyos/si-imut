#!/bin/bash

# File output
OUTPUT_FILE="public/serviceworker-files.js"

# Awal array
echo "const FILES_TO_CACHE = [" > "$OUTPUT_FILE"

# Tambahan manual file awal
echo '  "/offline",' >> "$OUTPUT_FILE"
echo '  "/build/manifest.json",' >> "$OUTPUT_FILE"

# Fungsi untuk menambahkan semua file js/css dari folder tertentu
add_assets_from() {
    local folder="$1"
    find "$folder" -type f \( -name "*.js" -o -name "*.css" -o -name "*.woff2" -o -name "*.svg" -o -name "*.json" -o -name "*.png" \) | while read -r file; do
        filepath="/${file#public/}"
        echo "  \"$filepath\"," >> "$OUTPUT_FILE"
    done
}

# Daftar folder asset
directories=(
  "public/build/assets"
  "public/images/assets"
  "public/images/icons"
  "public/css/filament/filament"
  "public/css/filament/forms"
  "public/css/filament/support"
  "public/css/archilex/filament-toggle-icon-column"
  "public/css/asmit/resized-column"
  "public/css/njxqlus/filament-progressbar"
  "public/css/rmsramos/activitylog"
  "public/js/filament/filament"
  "public/js/njxqlus/filament-progressbar"
  "public/js/filament/notifications"
  "public/js/asmit/resized-column"
  "public/js/filament/forms/components"
  "public/js/filament/support"
  "public/js/app/components"
  "public/js/filament/tables/components"
)

# Tambahkan semua asset dari folder
for dir in "${directories[@]}"; do
  add_assets_from "$dir"
done

# Tutup array
echo "];" >> "$OUTPUT_FILE"

# Info selesai
echo "✅ Berhasil generate $OUTPUT_FILE"
