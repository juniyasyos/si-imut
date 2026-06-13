# GraphRAG — Project Knowledge Base Ringan

**GraphRAG** (Graph Retrieval-Augmented Generation) adalah proof-of-concept sistem
pengetahuan berbasis dokumentasi project SIIMUT. Sistem ini mengambil dokumen markdown
dari `docs/`, memecahnya menjadi chunk terstruktur, mengekstrak grafik entitas-relasi
sederhana, dan menyediakan antarmuka query (keyword + LLM opsional).

## 📁 Struktur Folder

```txt
rag/
├── input/              # Hasil copy dari docs/ (dihasilkan sync_docs.py)
├── output/             # Output chunk & graph (dihasilkan ingest.py)
│   ├── chunks.json     # Chunk dokumen berbasis heading
│   └── graph.json      # Grafik pengetahuan (nodes + edges)
├── scripts/
│   ├── sync_docs.py    # Copy file markdown dari docs/ ke rag/input/
│   ├── ingest.py       # Chunking + graph extraction
│   └── query.py        # Query CLI dengan scoring + LLM opsional
├── requirements.txt    # Python dependencies
├── .env.example        # Template konfigurasi environment
└── README.md           # File ini
```

## 🚀 Cara Menjalankan

### 1. Setup Virtual Environment

```bash
cd rag
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### 2. (Optional) Copy .env

Jika ingin menggunakan Claude untuk menjawab pertanyaan:

```bash
cp .env.example .env
# Isi ANTHROPIC_API_KEY atau ANTHROPIC_AUTH_TOKEN
# dan ANTHROPIC_MODEL (default: claude-3-5-sonnet-latest)
# Jika ada ANTHROPIC_BASE_URL kustom, isi juga
```

Lewati langkah ini dan `query.py` tetap berjalan dalam mode **retrieval-only**
(tanpa LLM).

### 3. Sync Dokumentasi

```bash
python3 rag/scripts/sync_docs.py
```

Menyalin semua `.md` dari `docs/` dan `docs/releases/` ke `rag/input/`.

### 4. Ingest

```bash
python3 rag/scripts/ingest.py
```

Membaca markdown dari `rag/input/` dan menghasilkan:

- `rag/output/chunks.json` — chunk dokumen berbasis heading
- `rag/output/graph.json` — grafik entitas-relasi sederhana

### 5. Query

```bash
python3 rag/scripts/query.py "jelaskan service SIIMUT"
python3 rag/scripts/query.py "apa known issue terkait nginx?"
python3 rag/scripts/query.py "command apa untuk migrate database?"
```

## 🔄 Rebuild Jika Dokumentasi Berubah

```bash
python3 rag/scripts/sync_docs.py    # Sync ulang
python3 rag/scripts/ingest.py       # Re-chunk & rebuild graph
```

## 📤 Struktur Output

### chunks.json

Array of chunk object:

```json
{
  "id": "chunk-0001",
  "source_file": "PROJECT_STRUCTURE.md",
  "heading": "app/ — Source Code",
  "chunk_index": 1,
  "content": "Path | Fungsi | Catatan\n|---|---|---|\n| app/Filament/ | ..."
}
```

### graph.json

Object dengan `nodes` dan `edges`:

```json
{
  "nodes": [
    {
      "id": "siimut",
      "type": "Project",
      "label": "SIIMUT",
      "source": "PROJECT_STRUCTURE.md"
    }
  ],
  "edges": [
    {
      "from": "filament",
      "to": "nginx",
      "type": "exposed_by",
      "source": "DEPLOYMENT.md"
    }
  ]
}
```

## 🧠 Cara Kerja

### Chunking
1. Baca setiap file `.md`
2. Pecah berdasarkan heading markdown (`#`, `##`, dst.)
3. Jika satu heading terlalu panjang (>2000 karakter), pecah lagi per paragraf
4. Setiap chunk punya ID unik, source file, dan heading asal

### Graph Extraction
1. Pattern matching sederhana berbasis kata kunci
2. Ekstrak entitas (Project, App, Service, Module, Command, Port, dll.)
3. Buat relasi antar entitas berdasarkan konteks kemunculan bersama
4. Deduplikasi otomatis — node yang sama tidak dibuat dua kali

### Query
1. Tokenize pertanyaan
2. Score setiap chunk berdasarkan overlap kata kunci + heading boost
3. Cari node/edge graph yang relevan
4. Jika API key dan model dikonfigurasi, kirim konteks ke LLM
5. Jika tidak, tampilkan hasil retrieval saja

## ⚠️ Keterbatasan

1. **Pattern-based graph extraction** — hanya menangkap entitas dengan kata kunci
   eksplisit. Relasi kompleks atau implisit tidak tertangkap.
2. **Source code tidak diindeks** — hanya dokumentasi markdown di `docs/`.
3. **Keyword scoring sederhana** — tanpa embedding/semantic search. Bisa salah
   memahami konteks.
4. **LLM opsional** — jika tidak dikonfigurasi, hanya keyword retrieval.
5. **No persistence** — output file-based, bukan database. Rebuild penuh setiap
   kali dokumentasi berubah.
6. **Bahasa campuran** — dokumentasi dalam Bahasa Indonesia dan Inggris,
   query juga mendukung keduanya.

## 🔮 Next Improvement

- [ ] Gunakan embedding (sentence-transformers) untuk semantic chunk search
- [ ] Indeks juga README utama dan CHANGELOG.md
- [ ] Indeks AI agent docs (`docs/ai-agents/`)
- [ ] Graph extraction dengan LLM untuk akurasi lebih tinggi
- [ ] UI sederhana (Streamlit) untuk browsing knowledge base
- [ ] Auto-rebuild via git hook atau watchdog
- [ ] Export ke format yang bisa dibaca tools lain (JSON-LD, RDF)
- [ ] Caching query untuk mempercepat pertanyaan berulang
- [ ] Integrasi dengan Laravel Artisan command
