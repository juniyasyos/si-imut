#!/usr/bin/env python3
"""
sync_docs.py — Sync documentation markdown files into rag/input/.

Copies all .md files from docs/ (including docs/releases/*.md)
into rag/input/ for further processing by ingest.py.
"""

import shutil
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
DOCS_DIR = PROJECT_ROOT / "docs"
INPUT_DIR = PROJECT_ROOT / "rag" / "input"


def clean_input_dir():
    """Remove all files inside rag/input/."""
    if INPUT_DIR.exists():
        count = 0
        for f in INPUT_DIR.iterdir():
            if f.is_file():
                f.unlink()
                count += 1
        print(f"  🧹  Cleaned {count} existing file(s) from {INPUT_DIR}")
    else:
        INPUT_DIR.mkdir(parents=True, exist_ok=True)
        print(f"  📁  Created {INPUT_DIR}")


TEMPLATE_NAMES = {"template.md"}


def is_template(path: Path) -> bool:
    """Return True if the file is a template (should not be synced)."""
    return path.name in TEMPLATE_NAMES


def collect_markdown_files() -> list[Path]:
    """Return all .md files from docs/ and docs/releases/, excluding templates."""
    files: list[Path] = []

    # Root docs/ .md files (exclude docs/releases/, docs/upgrade/, docs/ai-agents/)
    for f in sorted(DOCS_DIR.glob("*.md")):
        if not is_template(f):
            files.append(f)

    # releases/*.md (skip template)
    releases_dir = DOCS_DIR / "releases"
    if releases_dir.exists():
        for f in sorted(releases_dir.glob("*.md")):
            if not is_template(f):
                files.append(f)

    return files


def copy_files(files: list[Path]) -> list[Path]:
    """Copy files to INPUT_DIR, return list of successfully copied paths."""
    copied: list[Path] = []
    for src in files:
        dest = INPUT_DIR / src.name
        shutil.copy2(src, dest)
        copied.append(dest)
    return copied


def main():
    print("=" * 50)
    print("  Sync Docs → rag/input/")
    print("=" * 50)

    clean_input_dir()

    md_files = collect_markdown_files()
    if not md_files:
        print("  ⚠️  No markdown files found in docs/ or docs/releases/.")
        return

    print(f"\n  📄  Found {len(md_files)} markdown file(s):")
    for f in md_files:
        rel = f.relative_to(PROJECT_ROOT)
        print(f"       • {rel}")

    copied = copy_files(md_files)
    print(f"\n  ✅  Copied {len(copied)} file(s) to {INPUT_DIR}")

    print("\n  Files in rag/input/:")
    for f in sorted(INPUT_DIR.iterdir()):
        size = f.stat().st_size
        print(f"       • {f.name}  ({size:,} bytes)")


if __name__ == "__main__":
    main()
