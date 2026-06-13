#!/usr/bin/env python3
"""
ingest.py — Read markdown files from rag/input/, chunk by heading,
extract a lightweight entity-relation graph, and write both outputs.
"""

import json
import re
from pathlib import Path
from collections import OrderedDict

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
INPUT_DIR = PROJECT_ROOT / "rag" / "input"
OUTPUT_DIR = PROJECT_ROOT / "rag" / "output"

# Max characters before we sub-chunk a heading section
MAX_CHUNK_SIZE = 2000


# ── Chunking ────────────────────────────────────────────────────────────


def read_markdown_files(input_dir: Path) -> list[tuple[str, str]]:
    """Return list of (filename, content) for every .md file."""
    results = []
    for f in sorted(input_dir.glob("*.md")):
        content = f.read_text(encoding="utf-8")
        results.append((f.name, content))
    return results


def heading_level(line: str) -> int:
    """Return heading level (1-6) or 0 if not a heading."""
    m = re.match(r"^(#{1,6})\s", line)
    return len(m.group(1)) if m else 0


def slugify(text: str) -> str:
    """Basic slug for chunk IDs."""
    s = text.lower().strip()
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return s.strip("-")[:60]


def chunk_markdown(filename: str, content: str) -> list[dict]:
    """Split markdown into chunks by heading boundaries."""
    lines = content.split("\n")
    chunks = []

    current_heading = "(root)"
    current_lines: list[str] = []
    chunk_counter = 0
    heading_encountered = False

    def flush():
        nonlocal chunk_counter
        text = "\n".join(current_lines).strip()
        if not text:
            return
        # If text is still too long, sub-chunk by paragraphs
        if len(text) > MAX_CHUNK_SIZE:
            sub_chunks = split_long_text(text, filename, current_heading, chunk_counter)
            chunks.extend(sub_chunks)
            chunk_counter += len(sub_chunks)
        else:
            chunk_counter += 1
            chunks.append({
                "id": f"chunk-{chunk_counter:04d}",
                "source_file": filename,
                "heading": current_heading,
                "chunk_index": chunk_counter,
                "content": text,
            })

    for line in lines:
        h = heading_level(line)
        if h > 0:
            # flush previous section
            if heading_encountered:
                flush()
            else:
                heading_encountered = True
            current_heading = line.lstrip("#").strip()
            current_lines = [line]
        else:
            current_lines.append(line)

    # flush last section
    if current_lines:
        flush()

    return chunks


def split_long_text(text: str, filename: str, heading: str,
                    start_index: int) -> list[dict]:
    """Split a long heading section into smaller chunks by paragraphs."""
    paragraphs = re.split(r"\n\s*\n", text)
    sub_chunks = []
    buffer: list[str] = []
    buf_len = 0
    idx = start_index

    for para in paragraphs:
        para = para.strip()
        if not para:
            continue
        if buf_len + len(para) > MAX_CHUNK_SIZE and buffer:
            idx += 1
            sub_chunks.append({
                "id": f"chunk-{idx:04d}",
                "source_file": filename,
                "heading": heading,
                "chunk_index": idx,
                "content": "\n\n".join(buffer),
            })
            buffer = [para]
            buf_len = len(para)
        else:
            buffer.append(para)
            buf_len += len(para)

    if buffer:
        idx += 1
        sub_chunks.append({
            "id": f"chunk-{idx:04d}",
            "source_file": filename,
            "heading": heading,
            "chunk_index": idx,
            "content": "\n\n".join(buffer),
        })

    return sub_chunks


# ── Graph Extraction ────────────────────────────────────────────────────


# Entity types & keyword patterns for lightweight extraction
ENTITY_PATTERNS: dict[str, list[str]] = {
    "Project": ["project", "siimut", "sistem indikator mutu"],
    "App": ["aplikasi", "app"],
    "Service": ["service", "services", "layer", "service provider"],
    "Container": ["container", "docker", "docker-compose"],
    "Command": ["artisan", "command", "php artisan", "composer"],
    "Port": ["port", ":8000", ":8088", ":3306", ":9000"],
    "Volume": ["volume", "storage"],
    "Env": [".env", "env", "environment", "environment variable"],
    "Module": ["modul", "module", "modular monolith", "bounded context"],
}

RELATION_PATTERNS: list[tuple[str, str, str, str]] = [
    # (entity_a_keyword, entity_b_keyword, relation_type, context_words)
    ("app", "service", "uses", r"service|layer|menggunakan"),
    ("app", "nginx", "exposed_by", r"nginx|web"),
    ("app", "port", "has_port", r"port"),
    ("app", "volume", "has_volume", r"volume|storage"),
    ("app", "env", "has_env", r"\.env|environment"),
    ("module", "service", "contains", r"module|modul"),
    ("command", "file", "defined_in", r"command|route|file"),
    ("issue", "service", "related_to", r"issue|masalah|bug"),
    ("decision", "project", "affects", r"keputusan|decision|refactor|migrasi"),
    ("release", "change", "includes", r"release|rilis|added|changed|fixed"),
]


def extract_entities(filename: str, content: str,
                     all_nodes: dict[str, dict]) -> None:
    """Scan content for known entity patterns and add nodes."""
    lower = content.lower()

    # Project
    if re.search(r"project.*siimut|siimut.*adalah|sistem indikator mutu", lower):
        _add_node(all_nodes, "siimut", "Project", "SIIMUT", filename)

    # Apps / services from docs
    if "filament" in lower:
        _add_node(all_nodes, "filament", "App", "Filament", filename)

    # Modules — 7 known modules
    if "authorization" in lower:
        _add_node(all_nodes, "authorization", "Module", "Authorization", filename)
    if "benchmarking" in lower:
        _add_node(all_nodes, "benchmarking", "Module", "Benchmarking", filename)
    if "daily-report" in lower or "dailyreport" in lower or "laporan harian" in lower:
        _add_node(all_nodes, "daily-report", "Module", "DailyReport", filename)
    if "formengine" in lower or "form engine" in lower:
        _add_node(all_nodes, "form-engine", "Module", "FormEngine", filename)
    if "imutmaster" in lower or "imut master" in lower or "master data" in lower:
        _add_node(all_nodes, "imut-master", "Module", "ImutMaster", filename)
    if "laporan" in lower and "periodik" in lower:
        _add_node(all_nodes, "laporan", "Module", "Laporan", filename)
    if "reporting" in lower:
        _add_node(all_nodes, "reporting", "Module", "Reporting", filename)

    # Key services
    if "iam" in lower or "sso" in lower or "nexaid" in lower:
        _add_node(all_nodes, "iam-service", "Service", "IAM/SSO Service", filename)
    if "nginx" in lower:
        _add_node(all_nodes, "nginx", "Service", "Nginx", filename)
    if "mysql" in lower or "mariadb" in lower:
        _add_node(all_nodes, "mysql", "Service", "MySQL", filename)
    if "redis" in lower:
        _add_node(all_nodes, "redis", "Service", "Redis", filename)
    if "queue" in lower:
        _add_node(all_nodes, "queue-worker", "Service", "Queue Worker", filename)
    if "backup" in lower:
        _add_node(all_nodes, "backup-service", "Service", "Backup Service", filename)

    # Ports
    if re.search(r"port\s+8000|:8000", lower):
        _add_node(all_nodes, "port-8000", "Port", "Port 8000", filename)
    if re.search(r"port\s+8088|:8088", lower):
        _add_node(all_nodes, "port-8088", "Port", "Port 8088", filename)
    if re.search(r"port\s+3306|:3306", lower):
        _add_node(all_nodes, "port-3306", "Port", "Port 3306", filename)

    # Volumes
    if "volume" in lower:
        _add_node(all_nodes, "storage-volume", "Volume", "Storage Volume", filename)

    # Env
    if re.search(r"session_driver|queue_connection|app_env|db_host|app_key", lower):
        _add_node(all_nodes, "env-config", "Env", "Environment Config", filename)

    # Commands
    if "php artisan serve" in lower:
        _add_node(all_nodes, "cmd-serve", "Command", "php artisan serve", filename)
    if "php artisan migrate" in lower:
        _add_node(all_nodes, "cmd-migrate", "Command", "php artisan migrate", filename)
    if "composer" in lower and "dev" not in lower.split()[:3]:
        _add_node(all_nodes, "cmd-composer", "Command", "Composer", filename)

    # Known issues — detect structured issues from KNOWN_ISSUES.md
    if re.search(r"needs review|needs verification|bug|issue|masalah|bottleneck", lower):
        _add_node(all_nodes, "known-issue-kernel", "KnownIssue",
                  "Kernel Duplication Issue", filename)

    # Detect KNOWN_ISSUES.md and extract each issue
    if filename == "KNOWN_ISSUES.md":
        for issue_id in re.findall(r"\*\*ID\*\*:\s*(KI-\d+)", content):
            issue_label_match = re.search(
                r"##\s+" + re.escape(issue_id) + r":\s*(.+?)(?:\n|$)",
                content
            )
            label = issue_label_match.group(1).strip() if issue_label_match else issue_id
            safe_id = issue_id.lower().replace("-", "-")
            _add_node(all_nodes, safe_id, "KnownIssue", label, filename)

    # Detect DECISIONS.md and extract each decision
    if filename == "DECISIONS.md":
        for dec_id in re.findall(r"\*\*ID\*\*:\s*(DEC-\d+)", content):
            dec_label_match = re.search(
                r"##\s+" + re.escape(dec_id) + r":\s*(.+?)(?:\n|$)",
                content
            )
            label = dec_label_match.group(1).strip() if dec_label_match else dec_id
            safe_id = dec_id.lower().replace("-", "-")
            _add_node(all_nodes, safe_id, "Decision", label, filename)

    # Decisions
    if "refactor" in lower or "migrasi" in lower:
        _add_node(all_nodes, "decision-refactor", "Decision",
                  "Refactor Decision", filename)

    # Docker
    if "docker" in lower:
        _add_node(all_nodes, "docker", "Container", "Docker", filename)

    # Releases (generic detection)
    if re.search(r"v\d+\.\d+\.\d+", content):
        for v in re.findall(r"v(\d+\.\d+\.\d+)", content):
            safe = f"release-v{v.replace('.', '-')}"
            _add_node(all_nodes, safe, "Release", f"Release v{v}", filename)

    # Dynamic RAG Metadata Parsing
    blocks = re.split(r'\n##\s+([A-Z]+-\d+)\s+-\s+([^\n]+)\n', '\n' + content)
    for i in range(1, len(blocks) - 2, 3):
        node_id = blocks[i].lower().strip()
        label = blocks[i+1].strip()
        body = blocks[i+2]
        
        metadata = {}
        for key in ["Type", "Status", "Area"]:
            m = re.search(fr"^{key}:\s*(.+)", body, re.MULTILINE)
            if m:
                metadata[key.lower()] = m.group(1).strip()
        
        for list_key in ["Related Services", "Related Commands", "Related Issues", "Related Decisions", "Related Modules", "Source"]:
            m = re.search(fr"^{list_key}:\n((?:-\s+.*\n?)+)", body, re.MULTILINE)
            if m:
                items = [x.strip("- ").strip() for x in m.group(1).strip().split('\n') if x.strip()]
                metadata[list_key.lower().replace(" ", "_")] = items
        
        m = re.search(r"-\s+commit:\s*(.+)", body, re.MULTILINE)
        if m:
            metadata["commit"] = m.group(1).strip()
            
        node_type = metadata.get("type", "Unknown")
        _add_node(all_nodes, node_id, node_type, label, filename, metadata)


def _add_node(nodes: dict, node_id: str, node_type: str, label: str,
              source: str, metadata: dict = None) -> None:
    if node_id not in nodes:
        nodes[node_id] = {
            "id": node_id,
            "type": node_type,
            "label": label,
            "source": source,
        }
    if metadata:
        nodes[node_id].update(metadata)


def extract_edges(filename: str, content: str,
                  nodes: dict[str, dict],
                  edges: list[dict]) -> None:
    """Add edges based on content patterns."""
    lower = content.lower()

    # app → service (Filament uses services)
    if "filament" in lower and ("service" in lower or "layer" in lower):
        _add_edge(edges, "filament", "iam-service", "uses", filename, nodes)
        _add_edge(edges, "filament", "backup-service", "uses", filename, nodes)

    # app → nginx
    if "nginx" in lower and "filament" in lower or "nginx" in lower and "aplikasi" in lower:
        _add_edge(edges, "filament", "nginx", "exposed_by", filename, nodes)

    # app → port
    if ":8000" in lower:
        _add_edge(edges, "filament", "port-8000", "has_port", filename, nodes)

    # Module → Service (each module contains services)
    for mod_id in ["authorization", "benchmarking", "daily-report", "form-engine",
                   "imut-master", "laporan", "reporting"]:
        mod_label = mod_id.replace("-", " ").title().replace(" ", "")
        if mod_label.lower() in lower:
            _add_edge(edges, mod_id, "iam-service", "uses", filename, nodes)

    # decision → project (refactor)
    if "refactor" in lower:
        _add_edge(edges, "decision-refactor", "siimut", "affects", filename, nodes)

    # Known issues → affected areas from KNOWN_ISSUES.md
    for issue_id in re.findall(r"\*\*ID\*\*:\s*(KI-\d+)", content):
        safe_issue = issue_id.lower().replace("-", "-")
        area_match = re.search(
            r"\*\*Area\*\*:\s*(.+?)(?:\n|$)", content
        )
        if area_match:
            area = area_match.group(1).strip().lower()
            area_node_map = {
                "performa": "daily-report",
                "performance": "daily-report",
                "arsitektur": "siimut",
                "architecture": "siimut",
                "security": "siimut",
                "konfigurasi": "env-config",
                "configuration": "env-config",
                "dependency": "cmd-composer",
                "ui": "filament",
            }
            target = area_node_map.get(area, "siimut")
            _add_edge(edges, safe_issue, target, "related_to", filename, nodes)

    # Decisions → affected areas from DECISIONS.md
    for dec_id in re.findall(r"\*\*ID\*\*:\s*(DEC-\d+)", content):
        safe_dec = dec_id.lower().replace("-", "-")
        context_lower = content.lower()
        if "modular" in context_lower or "arsitektur" in context_lower:
            _add_edge(edges, safe_dec, "siimut", "affects", filename, nodes)
        if "query" in context_lower or "performa" in context_lower:
            _add_edge(edges, safe_dec, "daily-report", "affects", filename, nodes)
        if "dokumentasi" in context_lower or "graphrag" in context_lower:
            _add_edge(edges, safe_dec, "siimut", "affects", filename, nodes)

    # release → change patterns
    if re.search(r"##\s+added|##\s+changed|##\s+fixed", content):
        for v in re.findall(r"v(\d+\.\d+\.\d+)", content):
            safe = f"release-v{v.replace('.', '-')}"
            safe_change = f"change-{v.replace('.', '-')}"
            _add_node(nodes, safe_change, "Change",
                      f"Changes in v{v}", filename)
            _add_edge(edges, safe, safe_change, "includes", filename, nodes)

    # issue → service
    if re.search(r"bottleneck|30.sec|n.1.query", lower):
        _add_edge(edges, "known-issue-kernel", "daily-report", "related_to",
                  filename, nodes)

    # Docker containers
    if "docker-compose" in lower:
        _add_node(nodes, "docker-compose", "Container", "Docker Compose", filename)
        _add_edge(edges, "docker", "docker-compose", "depends_on", filename, nodes)

    # Dynamic RAG Edges
    for node_id, node_data in nodes.items():
        if node_data.get("source") != filename:
            continue
        for key, rel_type in [
            ("related_services", "uses"),
            ("related_commands", "has_command"),
            ("related_issues", "has_issue"),
            ("related_decisions", "decided_by"),
            ("related_modules", "related_to")
        ]:
            if key in node_data:
                for item in node_data[key]:
                    if item.lower() != "none" and "needs verification" not in item.lower():
                        target_id = item.lower()
                        _add_edge(edges, node_id, target_id, rel_type, filename, nodes)


def _add_edge(edges: list, from_id: str, to_id: str, rel_type: str,
              source: str, nodes: dict) -> None:
    """Add edge only if both nodes exist."""
    if from_id in nodes and to_id in nodes:
        edges.append({
            "from": from_id,
            "to": to_id,
            "type": rel_type,
            "source": source,
        })


def build_graph(all_files: list[tuple[str, str]]) -> dict:
    """Build nodes and edges from all documents."""
    nodes: dict[str, dict] = OrderedDict()
    edges: list[dict] = []
    seen_edges: set = set()

    for filename, content in all_files:
        extract_entities(filename, content, nodes)

    for filename, content in all_files:
        e_before = len(edges)
        extract_edges(filename, content, nodes, edges)
        # dedup edges added in this file
        new_edges = edges[e_before:]
        deduped = []
        for e in new_edges:
            key = (e["from"], e["to"], e["type"])
            if key not in seen_edges:
                seen_edges.add(key)
                deduped.append(e)
        edges[e_before:] = deduped

    return {
        "nodes": list(nodes.values()),
        "edges": edges,
    }


# ── Main ────────────────────────────────────────────────────────────────


def main():
    from rich.console import Console
    from rich.table import Table

    console = Console()
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # 1. Read
    console.print("\n[bold cyan]📖  Reading markdown files…[/bold cyan]")
    files = read_markdown_files(INPUT_DIR)
    if not files:
        console.print("  [yellow]⚠️  No markdown files found in rag/input/[/yellow]")
        console.print("  [dim]Run sync_docs.py first: python3 rag/scripts/sync_docs.py[/dim]")
        return
    console.print(f"  Found [green]{len(files)}[/green] file(s)")

    # 2. Chunk
    console.print("\n[bold cyan]✂️  Chunking markdown by headings…[/bold cyan]")
    all_chunks = []
    for filename, content in files:
        chunks = chunk_markdown(filename, content)
        all_chunks.extend(chunks)

    chunks_path = OUTPUT_DIR / "chunks.json"
    with open(chunks_path, "w", encoding="utf-8") as f:
        json.dump(all_chunks, f, indent=2, ensure_ascii=False)
    console.print(f"  Created [green]{len(all_chunks)}[/green] chunks → "
                  f"[bold]{chunks_path}[/bold]")

    # 3. Graph
    console.print("\n[bold cyan]🔗  Building graph…[/bold cyan]")
    graph = build_graph(files)
    graph_path = OUTPUT_DIR / "graph.json"
    with open(graph_path, "w", encoding="utf-8") as f:
        json.dump(graph, f, indent=2, ensure_ascii=False)
    console.print(f"  [green]{len(graph['nodes'])}[/green] nodes, "
                  f"[green]{len(graph['edges'])}[/green] edges → "
                  f"[bold]{graph_path}[/bold]")

    # 4. Summary table
    table = Table("Type", "Count")
    table.add_row("Files", str(len(files)))
    table.add_row("Chunks", str(len(all_chunks)))
    table.add_row("Nodes", str(len(graph["nodes"])))
    table.add_row("Edges", str(len(graph["edges"])))
    console.print("\n[bold]Summary:[/bold]")
    console.print(table)

    # Node type breakdown
    type_counts: dict[str, int] = {}
    for n in graph["nodes"]:
        type_counts[n["type"]] = type_counts.get(n["type"], 0) + 1
    if type_counts:
        console.print("\n[bold]Node types:[/bold]")
        for t, c in sorted(type_counts.items(), key=lambda x: -x[1]):
            console.print(f"  • {t}: {c}")
    console.print()


if __name__ == "__main__":
    main()
