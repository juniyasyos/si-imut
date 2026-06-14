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

## 🐍 GraphRAG (Python Knowledge Base)

**Lokasi**: `rag/`

Sistem RAG ringan berbasis Python untuk query knowledge base project dari dokumentasi.

### Skrip

| Skrip | Fungsi |
|---|---|
| `rag/scripts/sync_docs.py` | Sync markdown dari `docs/` ke `docs/ai-agent/rag/input/` |
| `rag/scripts/ingest.py` | Chunking + graph extraction |
| `rag/scripts/query.py` | Query CLI (keyword scoring + LLM opsional) |

### Cara Pakai

```bash
cd rag
python3 -m venv venv
source venv/bin/activate
pip install -e .

# Sync + Ingest (Rebuild)
rag-project rebuild

# Query
rag-project query "pertanyaan anda"
rag-project graph "entity"
```

> Dokumentasi lengkap: [RAG_GUIDE.md](docs/RAG_GUIDE.md)
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

1. **Jika task tentang SIIMUT**, pakai `rag-project` sebagai peta awal.
2. **Jika task tentang RAG/parser/chunks/graph/query/ingest**, mulai dari `rag-project/`.
3. **Jangan baca seluruh repo** kalau RAG/docs cukup.
4. **Kalau RAG kurang jelas/gagal**, baru baca source file relevan. Source code tetap source of truth.
5. **Jangan ubah logic aplikasi utama** tanpa persetujuan eksplisit.
6. **Jangan scan** `app/` secara penuh, vendor, node_modules, storage, logs, build.
7. **Jangan hardcode** secret, API key, atau credential.
8. **Fokus dokumentasi** — semua perubahan docs wajib dicatat di CHANGELOG.
9. **RAG rebuild** — setiap kali docs berubah, jalankan sync_docs + ingest.
10. **Bahasa** — dokumentasi dalam Bahasa Indonesia, kecuali file teknis tertentu (SBOM, LICENSE).
11. **Wajib baca panduan RAG** — lihat [AI_AGENT_USAGE.md](docs/AI_AGENT_USAGE.md) dan [RAG_WORKFLOW.md](docs/RAG_WORKFLOW.md).
