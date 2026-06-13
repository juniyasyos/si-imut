# Release vX.X.X

**Tanggal**: YYYY-MM-DD

## Ringkasan

Deskripsi singkat tentang apa yang dicapai di rilis ini.

---

## Added

- Fitur baru A.
- Fitur baru B.

## Changed

- Perubahan pada fitur C.
- Perubahan pada konfigurasi D.

## Fixed

- Perbaikan bug E.

## Removed

- Fitur F dihapus.

## Security

- Perbaikan keamanan G.

---

## Breaking Changes

- Perubahan API endpoint X → Y.
- Database schema migration Z.

## Migration Notes

1. Jalankan `php artisan migrate` setelah update.
2. Perbarui `.env` dengan variabel baru.
3. Jalankan `php artisan shield:generate --all`.

## Known Issues

- Issue A — sedang dalam penyelidikan.
- Issue B — workaround: ...

## Checklist

- [ ] README diperbarui.
- [ ] CHANGELOG diperbarui.
- [ ] Dokumentasi konfigurasi diperbarui.
- [ ] Dokumentasi deployment diperbarui jika perlu.
- [ ] Dokumentasi struktur project diperbarui.
- [ ] Semua test passing.
- [ ] Sudah dites lokal.
- [ ] Migration bisa rollback dengan aman.
