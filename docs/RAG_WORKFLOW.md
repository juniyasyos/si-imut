# RAG Workflow & Troubleshooting

Alur kerja penggunaan RAG untuk AI Agent:

1. Baca `AGENTS.md`
2. Pahami perintah CLI `contexta` (e.g., `bunx contexta scan`)
3. Baca docs RAG
4. Query RAG jika perlu mengetahui arsitektur
5. Baru baca source file relevan

## Troubleshooting Command RAG

Jika command RAG dijalankan, contohnya:
```bash
bunx contexta scan
```

Dan menghasilkan error:
```
SyntaxError: Export named ...
```
*(Exit code: 1)*

**Catatan / Solusi:**
Ini biasanya terjadi karena Anda belum menjalankan `bun install` di dalam root project `contexta` atau environment belum di-setup dengan benar. Pastikan dependensi package manager sudah terinstal.
