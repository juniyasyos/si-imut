import json
from collections import defaultdict

# Load data dari file JSON
with open('unit.json', 'r', encoding='utf-8') as file:
    data = json.load(file)

# Simpan entri berdasarkan title, beserta indeksnya
title_map = defaultdict(list)

for index, item in enumerate(data):
    title_map[item['title']].append({
        'index': index,
        'description': item['description']
    })

# Tampilkan hasil duplikat secara rinci
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
