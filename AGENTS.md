# AI Agents & Tooling

Daftar agen AI, tools, dan konfigurasi yang digunakan dalam pengembangan project SIIMUT.

---

## 🤖 Claude Code (AI Coding Assistant)

**File konfigurasi**: `.claude/settings.json`, `.claude/projects/`

### Mode yang Tersedia

| Mode | Deskripsi | Use Case |
|---|---|---|
| **Normal** | Default — menjawab pertanyaan dan menulis kode dengan approval manual | Eksplorasi, debugging ringan |
| **Plan** | Membuat rencana implementasi sebelum menulis kode | Fitur kompleks, refactor besar |
| **Review** | Review diff/PR untuk bug, performance, security | Sebelum commit atau PR |

### Agents Khusus

| Agent | Fungsi | Dipanggil Dengan |
|---|---|---|
| **Explore** | Membaca & mencari kode tanpa menulis | `/explore <query>` |
| **Plan** | Mendesain arsitektur implementasi | `/plan <task>` |
| **Code Review** | Review kode untuk bug/simplifikasi | `/code-review` |
| **Verify** | Menjalankan app dan memverifikasi perubahan | `/verify` |
| **Security Review** | Security audit terhadap perubahan | `/security-review` |
| **Simplify** | Refactor reuse/simplifikasi otomatis | `/simplify` |
| **Deep Research** | Multi-source research dengan verifikasi | `/deep-research <topic>` |
| **Claude API Guide** | Referensi API Claude/Anthropic | `/claude-api` |

### Workflows (Multi-Agent Orchestration)

Project mendukung **ultracode workflows** untuk task skala besar:

- **code-review**: Review dimensi (bugs, perf, security) + adversarial verify
- **security-review**: Security audit dengan multiple lenses
- **migrate**: Migrasi terstruktur dengan worktree isolation
- **research**: Multi-source deep dive dengan synthesis

> Lihat `/workflows` untuk daftar workflow yang tersedia.

---

## 🧠 contexta (Node/Bun Architectural RAG)

Sistem pemetaan arsitektur (Caveman Librarian) ringan berbasis Regex untuk memahami relasi file, class, dan entity di dalam proyek Laravel.

### Skrip / CLI

| Perintah | Fungsi |
|---|---|
| `bunx contexta scan` | Scan file proyek menggunakan `laravel.yml` dan ekstrak ke `graph.json` |
| `bunx contexta graph stats` | Menampilkan statistik keseluruhan node dan edge |
| `bunx contexta inspect <node_id>` | Menampilkan detail relasi dari suatu node (misal: `model-user`) |
| `bunx contexta impact <node_id>` | Melakukan analisis dampak (blast radius) dari sebuah node |
| `bunx contexta query --intent <intent> --entity <entitas>` | Melakukan pencarian arsitektural |

### Cara Pakai

```bash
# Scan & Ingest (Rebuild Graph)
bunx contexta scan

# Inspect relasi suatu model
bunx contexta inspect model-user

# Cek dampak perubahan
bunx contexta impact model-user --depth 2
```

> Panduan untuk AI Agent: [RAG_USAGE_FOR_AGENT.md](docs/RAG_USAGE_FOR_AGENT.md)

---

## 🔧 Tools Pendukung

| Tool | Versi | Fungsi |
|---|---|---|
| **Laravel IDE Helper** | ^1.x | Auto-completion Facades & Models |
| **Laravel Pint** | ^1.x | PHP code style fixer |
| **Pest** | ^3.x | PHP testing framework |
| **Laravel Debugbar** | ^3.x | Debugging toolbar dev |
| **Laravel Pail** | ^1.x | Log viewer real-time |
| **TablePlus / Sequel Ace** | - | Database GUI |

---

## ⚙️ Konfigurasi Claude Code

### Settings (`settings.json`)

```json
{
  "model": "claude-sonnet-4-6",
  "theme": "dark",
  "permissions": {
    "allow": ["bun", "composer", "php", "npm", "git", "cp", "grep", "find", "ls", "pip*", "python3"]
  }
}
```

### Hooks (`settings.json`)

Hook diaktifkan di `.claude/settings.local.json`:

```json
{
  "hooks": {
    "PreToolUse": {
      "Bash": {
        "allow": ["bun", "npm", "comoser", "php", "git", "cp", "mv", "rm", "cat", "grep", "find", "ls", "head", "tail", "sort", "uniq", "wc", "tee", "echo", "printf", "mkdir", "touch", "chmod", "python3", "pip*"]
      }
    }
  }
}
```

---

## 📝 Aturan untuk AI Agents

1. **Jika task tentang SIIMUT**, pakai `bunx contexta` sebagai peta awal.
2. **Jangan baca seluruh repo** kalau hasil `contexta` cukup jelas.
3. **Kalau `contexta` kurang jelas/gagal**, baru baca source file relevan. Source code tetap source of truth.
4. **Jangan ubah logic aplikasi utama** tanpa persetujuan eksplisit.
5. **Jangan scan** `app/` secara penuh, vendor, node_modules, storage, logs, build.
6. **Jangan hardcode** secret, API key, atau credential.
7. **Fokus dokumentasi** — semua perubahan docs wajib dicatat di CHANGELOG.
8. **RAG rebuild** — setiap kali ada perubahan struktur besar, jalankan `bunx contexta scan`.
9. **Bahasa** — dokumentasi dalam Bahasa Indonesia, kecuali file teknis tertentu (SBOM, LICENSE).
10. **Wajib baca panduan RAG** — lihat [AI_AGENT_USAGE.md](docs/AI_AGENT_USAGE.md) dan [RAG_WORKFLOW.md](docs/RAG_WORKFLOW.md).
