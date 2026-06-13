Software Bill of Materials (SBOM)
=================================

Purpose
-------
This file explains how to generate an SBOM for the PHP/Composer project.
An SBOM provides a machine-readable inventory of components and licenses.

Recommended formats
- SPDX (text or JSON)
- CycloneDX (XML or JSON)

How to generate
---------------
Option A — Composer (simple, package list):
```bash
# human-readable list
composer licenses > docs/third_party_licenses.txt
```

Option B — CycloneDX (recommended for full SBOM):
- Use a CycloneDX generator that supports Composer, for example `cyclonedx-php` or
  the CycloneDX Composer plugin (install as a dev dependency) and run it to
  produce `bom.xml` or `bom.json` in `docs/`.

Example commands (install/usage depend on the chosen tool):
```bash
# example (adjust per tool documentation):
composer require --dev cyclonedx/cyclonedx-php
# then run the tool to create a BOM
vendor/bin/cyclonedx convert --output docs/bom.json
```

Minimal SBOM header (fill after generation):
- Generated: 2026-06-01
- Tool: <tool-name>
- Format: CycloneDX/JSON
- Entry point: composer.json
- Notes: see `docs/THIRD_PARTY_LICENSES.md` for license summaries

If you want, I can generate a first SBOM here (needs Composer and/or the
CycloneDX tool available on the server). Run the recommended commands or tell
me to run them for you.
