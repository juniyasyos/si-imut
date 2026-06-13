Third-party dependencies and licenses
=====================================

This file should list all third-party packages included with this project and
their licenses. You can generate or refresh this list using Composer.

Recommended commands (run on the project root):

```bash
# Show license list (human readable)
composer licenses

# Export license list as JSON (if your Composer version supports it)
composer licenses --format=json > docs/third_party_licenses.json
```

If you don't have `composer licenses` available, run:

```bash
composer show -l > docs/third_party_licenses.txt
```

Example (partial) - populate this table after running the commands above:

- laravel/framework — MIT
- spatie/laravel-backup — MIT
- spatie/db-dumper — MIT
- league/flysystem-aws-s3-v3 — MIT
- firebase/php-jwt — BSD-3-Clause

Notes:
- After generating a JSON/text export, you may copy relevant details into this
  markdown file for easier review.
- If you need a machine-readable SPDX or CycloneDX SBOM, see `SBOM.md`.
