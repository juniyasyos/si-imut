# Panduan contexta (Architectural RAG)

Panduan lengkap tentang sistem **contexta** untuk memetakan dan melakukan query terhadap knowledge base arsitektural project SIIMUT.

> **Khusus AI Agents:** Wajib membaca alur kerja dan pola penggunaan RAG di [RAG_USAGE_FOR_AGENT.md](RAG_USAGE_FOR_AGENT.md).

---

## 📋 Ringkasan

`contexta` adalah sistem Architectural RAG berbasis Node.js/Bun yang dirancang khusus untuk membedah source code Laravel dan mengekstrak relasi antar entitas (Controller, Model, Service, Route, dll) menggunakan teknik Regex ringan (Caveman Librarian).

1. **Scan** — Membaca source code dan ekstrak entitas & relasi ke `graph.json`.
2. **Inspect** — Melihat blast radius / ketergantungan dari satu file ke file lain.
3. **Query** — Melakukan pencarian arsitektural berdasarkan intent.

**Tujuan**: Memudahkan developer (manusia dan AI) memetakan *blast radius* dan keterkaitan komponen tanpa harus membaca ribuan file secara manual, sehingga menghemat konsumsi token LLM.

---

## 📁 Instalasi & Setup

`contexta` diinstal sebagai tool CLI menggunakan `bun`.

```bash
# Pastikan berada di root project contexta
cd /home/juni/projects/plugin/contexta
bun install
```

---

## 🚀 Quick Start

Semua perintah dijalankan di dalam root direktori aplikasi `siimut`.

### 1. Build / Scan Arsitektur

Perintah ini akan membaca `laravel.yml` scanner dan mengekstrak seluruh entitas di dalam `app/`, `database/`, dan `routes/` ke dalam file `docs/ai-agent/rag/output/graph.json`.

```bash
bunx contexta scan
```

### 2. Cek Statistik
Melihat ringkasan arsitektur (jumlah Controller, Model, relasi, dll).

```bash
bunx contexta graph stats
```

### 3. Inspect (Analisis Dampak / Relasi)

Jika Anda ingin mengubah suatu `Model` atau `Service`, gunakan command ini untuk melihat file apa saja yang bergantung padanya:

```bash
bunx contexta inspect model-user
```
*(Akan menghasilkan list Controller, Service, Resource, dll yang menggunakan `User`)*

Untuk melihat grafik dampak dengan kedalaman tertentu:
```bash
bunx contexta impact model-user --depth 2
```

### 4. Query Intent

Mencari service, dokumentasi, atau entitas tertentu berdasarkan intent:

```bash
bunx contexta query --intent service_lookup --entity LaporanImut
```

---

## 🔄 Kapan Harus Rebuild / Scan Ulang?

Jalankan `bunx contexta scan` setiap kali Anda melakukan:
1. Pembuatan Model / Controller / Service baru.
2. Perubahan relasi antar class (misalnya sebuah Controller baru mulai memanggil sebuah Service).
3. Perubahan besar pada struktur folder.

---

## 📤 Output Arsitektur

### graph.json

File utama (bisa mencapai 1MB+) yang berisi ribuan *nodes* (entitas) dan *edges* (relasi).

```json
{
  "nodes": [
    { "id": "model-user", "type": "model", "label": "User", "domain": "models" }
  ],
  "edges": [
    { "from": "controller-logincontroller", "to": "model-user", "type": "uses_model" }
  ]
}
```

---

## 🧠 Cara Kerja (Caveman Librarian)

`contexta` sengaja tidak menggunakan AST (Abstract Syntax Tree) Parser seperti PHPStan, melainkan menggunakan regex pattern matching.
Alasannya:
1. **Kecepatan**: Sangat cepat (scan ribuan file dalam hitungan detik).
2. **Toleransi Error**: Tidak peduli jika ada syntax error di dalam kode PHP, ia tetap bisa membaca struktur dasarnya.
3. **Efisiensi Token**: Outputnya berukuran kecil namun mencakup relasi level makro, sangat disukai oleh AI Agents.

---

## ⚠️ Keterbatasan

1. **Bukan Analisis Fungsi/Method**: Tidak bisa mendeteksi logika baris-per-baris atau parameter fungsi.
2. **False Positive Regex**: Terkadang regex bisa menangkap string/komentar yang menyerupai definisi class.
3. **Spesifik Laravel**: Aturan pencariannya di-hardcode ke struktur Laravel (`src/scanners/laravel.yml`).
