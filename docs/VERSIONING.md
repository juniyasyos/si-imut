# Versioning

Aturan versioning untuk project SIIMUT.

---

## Semantic Versioning

Project ini menggunakan **Semantic Versioning** dengan format:

```
MAJOR.MINOR.PATCH
```

Contoh: `1.2.3` → Major 1, Minor 2, Patch 3.

### Arti Setiap Komponen

| Komponen | Contoh Perubahan | Kapan Dinaikkan |
|---|---|---|
| **MAJOR** | Perubahan arsitektur, database schema breaking | Perubahan yang tidak kompatibel dengan versi sebelumnya |
| **MINOR** | Fitur baru, halaman baru, modul baru | Penambahan fitur yang kompatibel ke belakang |
| **PATCH** | Bug fix, dokumentasi, refactor kecil | Perbaikan yang tidak mengubah fungsionalitas |

### Contoh

| Versi | Perubahan |
|---|---|
| `1.0.0` | Rilis stabil pertama |
| `1.0.1` | Bug fix di dashboard |
| `1.1.0` | Fitur export PDF baru |
| `2.0.0` | Migrasi database breaking change |

---

## Aturan

### 1. Setiap Versi Wajib Punya Catatan di CHANGELOG

Setiap kali versi berubah, tambahkan entry di `CHANGELOG.md` dengan format:

```md
## [1.2.3] - YYYY-MM-DD

### Added
- Fitur baru...

### Changed
- Perubahan fitur...

### Fixed
- Bug fix...

### Security
- Perbaikan keamanan...
```

### 2. Rilis Besar Boleh Punya File Detail

Untuk MAJOR dan MINOR release, buat file detail di `docs/releases/vX.X.X.md` menggunakan template dari `docs/releases/template.md`.

### 3. Dokumentasi Wajib Diperbarui

Setiap perubahan versi harus memeriksa apakah dokumentasi berikut perlu diperbarui:

| Perubahan | Dokumen yang Perlu Dicek |
|---|---|
| Command baru | `USAGE.md` |
| Konfigurasi baru | `CONFIGURATION.md` |
| Struktur folder berubah | `PROJECT_STRUCTURE.md` |
| Cara install berubah | `INSTALLATION.md` |
| Cara deploy berubah | `DEPLOYMENT.md` |

### 4. Branching

```txt
main (vX.X.X) — tag setiap rilis
└── develop (vX.X.X-dev) — development
```

- Setiap rilis di-*tag* di branch `main`.
- Versi development ditandai suffix `-dev`.

### 5. Stabilitas

| Fase | Range Versi | Keterangan |
|---|---|---|
| Initial Development | `0.x.x` | API belum stabil, boleh ada breaking change |
| Stable | `1.x.x` ke atas | API stabil, backward compatibility dijaga |

---

## Cara Melakukan Rilis

### 1. Persiapan

```bash
# Cek semua perubahan sudah di develop branch
git checkout modular-monolist
git log --oneline main..HEAD

# Update versi di file-file terkait
# - CHANGELOG.md
# - config/app.php (APP_VERSION)
```

### 2. Buat Release Notes

Buat file `docs/releases/vX.X.X.md` (gunakan template).

### 3. Merge ke Main

```bash
git checkout main
git merge modular-monolist
git tag vX.X.X
git push origin main --tags
```

### 4. Update Branch Development

```bash
git checkout modular-monolist
# Update versi ke X.X.X-dev
git commit --allow-empty -m "chore: bump version to X.X.X-dev"
git push
```

---

## Referensi

- [Semantic Versioning Specification](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
