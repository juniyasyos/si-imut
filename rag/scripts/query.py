#!/usr/bin/env python3
"""
query.py — Query the GraphRAG knowledge base using keyword scoring
and optional LLM enhancement (Anthropic Claude).
"""

import json
import os
import re
import sys
from pathlib import Path
from typing import Any

# Load .env manually (python-dotenv)
from dotenv import load_dotenv

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
load_dotenv(PROJECT_ROOT / "rag" / ".env")
load_dotenv(PROJECT_ROOT / "rag" / ".env.example")

OUTPUT_DIR = PROJECT_ROOT / "rag" / "output"
CHUNKS_PATH = OUTPUT_DIR / "chunks.json"
GRAPH_PATH = OUTPUT_DIR / "graph.json"

# Number of top chunks to retrieve
TOP_K = 5


# ── Data loading ────────────────────────────────────────────────────────


def load_json(path: Path) -> Any:
    if not path.exists():
        print(f"  ❌  File not found: {path}", file=sys.stderr)
        print(f"  💡  Run ingest.py first: python3 rag/scripts/ingest.py",
              file=sys.stderr)
        sys.exit(1)
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)


# ── Scoring ─────────────────────────────────────────────────────────────


def tokenize(text: str) -> set[str]:
    """Lowercase word tokens, filtering short noise."""
    words = re.findall(r"[a-z0-9-]+", text.lower())
    return {w for w in words if len(w) > 2}


def score_chunks(query: str, chunks: list[dict]) -> list[tuple[float, dict]]:
    """Rank chunks by keyword overlap with query."""
    q_tokens = tokenize(query)

    # Also extract bigrams from query for partial matching
    q_words = list(q_tokens)

    scored = []
    for chunk in chunks:
        c_tokens = tokenize(chunk["content"])
        # Exact token overlap
        overlap = len(q_tokens & c_tokens)

        # Partial / prefix matches
        partial = sum(
            1 for qt in q_words
            for ct in c_tokens
            if qt in ct or ct in qt
        )

        # Boost if query words appear in heading
        heading_lower = chunk["heading"].lower()
        heading_boost = sum(2 for qt in q_words if qt in heading_lower)

        # Boost if query words appear in source filename
        source_boost = sum(1 for qt in q_words if qt in chunk["source_file"].lower())

        score = overlap * 2 + partial + heading_boost + source_boost
        if score > 0:
            scored.append((score, chunk))

    scored.sort(key=lambda x: -x[0])
    return scored[:TOP_K]


def score_graph(query: str, graph: dict) -> tuple[list[dict], list[dict]]:
    """Return (relevant_nodes, relevant_edges) based on keyword match."""
    q_tokens = tokenize(query)

    relevant_nodes = []
    for node in graph["nodes"]:
        text = f"{node['id']} {node['label']} {node['type']}".lower()
        n_tokens = tokenize(text)
        if q_tokens & n_tokens:
            relevant_nodes.append(node)

    node_ids = {n["id"] for n in relevant_nodes}

    relevant_edges = []
    for edge in graph["edges"]:
        edge_text = f"{edge['from']} {edge['to']} {edge['type']}".lower()
        e_tokens = tokenize(edge_text)
        if q_tokens & e_tokens:
            relevant_edges.append(edge)
        elif edge["from"] in node_ids or edge["to"] in node_ids:
            relevant_edges.append(edge)

    return relevant_nodes, relevant_edges


# ── LLM ─────────────────────────────────────────────────────────────────


def build_context(chunks: list[tuple[float, dict]],
                  nodes: list[dict], edges: list[dict]) -> str:
    """Build a context string from retrieved chunks and graph data."""
    parts = ["Berikut adalah konteks dari dokumentasi project:\n"]

    # Chunks
    parts.append("=== DOKUMEN ===")
    seen = set()
    for score, chunk in chunks:
        header = f"[{chunk['source_file']}] {chunk['heading']}"
        if header not in seen:
            seen.add(header)
            parts.append(f"\n{header}")
            content = chunk["content"][:1500]
            parts.append(content)

    # Graph
    if nodes or edges:
        parts.append("\n=== GRAF PENGETAHUAN ===")
        for n in nodes:
            parts.append(f"  • [{n['type']}] {n['label']} ({n['id']})")
        for e in edges:
            parts.append(f"  • {e['from']} --[{e['type']}]--> {e['to']}")

    return "\n".join(parts)


def llm_answer(question: str, context: str) -> str | None:
    """Call Anthropic Claude. Returns None on failure."""
    # Check ANTHROPIC_API_KEY first, then ANTHROPIC_AUTH_TOKEN as fallback
    api_key = os.getenv("ANTHROPIC_API_KEY", "").strip() or \
              os.getenv("ANTHROPIC_AUTH_TOKEN", "").strip()
    model = os.getenv("ANTHROPIC_MODEL", "").strip()
    base_url = os.getenv("ANTHROPIC_BASE_URL", "").strip() or None

    if not api_key or not model:
        return None

    try:
        from anthropic import Anthropic
    except ImportError:
        print("  ⚠️  anthropic package not installed. Install: pip install anthropic",
              file=sys.stderr)
        return None

    try:
        client_kwargs = {"api_key": api_key}
        if base_url:
            client_kwargs["base_url"] = base_url
        client = Anthropic(**client_kwargs)
        response = client.messages.create(
            model=model,
            system=(
                "Anda adalah asisten yang menjawab pertanyaan tentang "
                "project SIIMUT (Sistem Indikator Mutu Rumah Sakit) "
                "berdasarkan konteks dokumentasi yang diberikan.\n\n"
                "Aturan:\n"
                "1. Jawab berdasarkan konteks yang diberikan.\n"
                "2. Jika konteks tidak cukup jawab, katakan "
                "'Tidak ditemukan informasi yang cukup dalam dokumentasi.'\n"
                "3. Jangan mengarang informasi yang tidak ada dalam konteks.\n"
                "4. Jawab dalam bahasa Indonesia.\n"
                "5. Sertakan referensi file sumber jika relevan."
            ),
            messages=[
                {
                    "role": "user",
                    "content": f"Konteks:\n{context}\n\nPertanyaan: {question}",
                },
            ],
            temperature=0.3,
            max_tokens=1000,
        )
        return response.content[0].text.strip()
    except Exception as e:
        print(f"  ⚠️  LLM call failed: {e}", file=sys.stderr)
        return None


# ── Output formatting ───────────────────────────────────────────────────


def format_sources(chunks: list[tuple[float, dict]]) -> list[str]:
    seen = set()
    sources = []
    for _, c in chunks:
        fn = c["source_file"]
        if fn not in seen:
            seen.add(fn)
            sources.append(f"docs/{fn}")
    return sources


def format_edges(edges: list[dict]) -> list[str]:
    return [f"  • {e['from']} --[{e['type']}]--> {e['to']}" for e in edges]


# ── Main ────────────────────────────────────────────────────────────────


def main():
    question = " ".join(sys.argv[1:]).strip()
    if not question:
        print("Usage: python3 rag/scripts/query.py <pertanyaan>")
        print()
        print("Examples:")
        print('  python3 rag/scripts/query.py "jelaskan service SIIMUT"')
        print('  python3 rag/scripts/query.py "apa itu modular monolith?"')
        sys.exit(1)

    # Load data
    chunks = load_json(CHUNKS_PATH)
    graph = load_json(GRAPH_PATH)

    # Score
    top_chunks = score_chunks(question, chunks)
    rel_nodes, rel_edges = score_graph(question, graph)

    # Build context for LLM
    context = build_context(top_chunks, rel_nodes, rel_edges)

    # Try LLM
    answer = llm_answer(question, context)

    # ── Output ──────────────────────────────────────────────────────
    print()
    print("=" * 60)
    print(f"  Pertanyaan: {question}")
    print("=" * 60)
    print()

    if answer:
        print("  Jawaban:")
        print(f"  {answer}")
    else:
        print("  [Retrieval Only — no LLM configured]")
        print()
        print("  Chunk teratas yang relevan:")
        for i, (score, chunk) in enumerate(top_chunks, 1):
            snippet = chunk["content"][:300].replace("\n", " ")
            print(f"\n  [{i}] {chunk['source_file']} → {chunk['heading']}")
            print(f"      Score: {score}")
            print(f"      {snippet}...")

    # Sources
    sources = format_sources(top_chunks)
    if sources:
        print()
        print("  Sumber:")
        for s in sources:
            print(f"    • {s}")

    # Graph
    if rel_nodes or rel_edges:
        print()
        print("  Relasi Graph Terkait:")
        for n in rel_nodes:
            print(f"    • [{n['type']}] {n['label']} ({n['id']})")
        for line in format_edges(rel_edges):
            print(line)

    print()

    if not top_chunks:
        print("  ⚠️  Tidak ditemukan chunk yang relevan dengan pertanyaan.")
        print("     Coba gunakan kata kunci yang berbeda.")
        print()


if __name__ == "__main__":
    main()
