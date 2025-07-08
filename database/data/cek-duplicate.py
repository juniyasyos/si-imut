import json
from collections import defaultdict

# Load data dari file JSON
with open('unit.json', 'r', encoding='utf-8') as file:
    data = json.load(file)

# Simpan entri berdasarkan title
title_map = defaultdict(list)

for index, item in enumerate(data):
    title_map[item['title']].append({
        'index': index,
        'description': item['description']
    })

# === Pengecekan Duplikat Title dan Deskripsi ===
print("=== Pengecekan Duplikat Title dan Deskripsi ===\n")
duplikat_ditemukan = False

for title, entries in title_map.items():
    if len(entries) > 1:
        duplikat_ditemukan = True
        print(f"🟠 Duplikat ditemukan pada title: '{title}'")
        print(f"Jumlah kemunculan: {len(entries)}")
        print("Posisi dalam list dan deskripsi:")
        descriptions = set()
        for entry in entries:
            descriptions.add(entry['description'])

        for item in entries:
            print(f"  - Index ke-{item['index']}:")
            print(f"    Deskripsi: {item['description'][:80]}{'...' if len(item['description']) > 80 else ''}\n")

        if len(descriptions) == 1:
            print("✅ Semua deskripsi **sama**.\n")
        else:
            print("⚠️  Deskripsi **berbeda** antara duplikat.\n")

if not duplikat_ditemukan:
    print("✅ Tidak ada duplikat title yang ditemukan.")

# === Konversi Semua target_value bertipe string angka ke integer ===
print("\n=== Konversi target_value string → integer ===\n")
converted_count = 0

for idx, item in enumerate(data):
    profile = item.get("profile", {})
    target = profile.get("target_value")

    # Jika target_value adalah string angka
    if isinstance(target, str) and target.strip().isdigit():
        new_value = int(target.strip())
        data[idx]["profile"]["target_value"] = new_value
        converted_count += 1
        print(f"🔄 Index {idx} | Konversi target_value: '{target}' → {new_value}")

if converted_count == 0:
    print("✅ Tidak ditemukan target_value bertipe string angka.")
else:
    with open('unit_cleaned.json', 'w', encoding='utf-8') as f_out:
        json.dump(data, f_out, ensure_ascii=False, indent=2)
    print(f"\n✅ Total {converted_count} nilai berhasil dikonversi dan disimpan ke 'unit_cleaned.json'")
