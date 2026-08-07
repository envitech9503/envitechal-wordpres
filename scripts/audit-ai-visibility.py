#!/usr/bin/env python3
"""Create a reproducible, read-only AI visibility and frontend-freeze baseline."""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
import re
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path


USER_AGENT = "EnviTechAL-AI-Visibility-Audit/1.0"
SCHEMA_ID_RE = re.compile(r'"@id"\s*:\s*"([^"]+)"')
SCHEMA_TYPE_RE = re.compile(r'"@type"\s*:\s*(?:"([^"]+)"|\[([^\]]+)\])')
CLAIM_RE = re.compile(
    r"\b(?:LAB-(?:284|285|347)|PNAC|ISO/?IEC\s*17025|accredit(?:ed|ation)|"
    r"Sindh\s+EPA|Punjab\s+EPA|Green\s+Lab|ISO\s*9001|ISO\s*14001)\b",
    re.IGNORECASE,
)


class PageParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.title_parts: list[str] = []
        self.h1_parts: list[str] = []
        self.canonical = ""
        self.meta_robots: list[str] = []
        self.links: list[str] = []
        self.schema_documents: list[str] = []
        self._current_schema_parts: list[str] = []
        self.visible_parts: list[str] = []
        self._capture_title = False
        self._capture_h1 = False
        self._capture_schema = False
        self._ignored_depth = 0

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = {key.lower(): value or "" for key, value in attrs}
        lower_tag = tag.lower()
        if lower_tag in {"script", "style", "noscript", "svg"}:
            self._ignored_depth += 1
        if lower_tag == "title":
            self._capture_title = True
        if lower_tag == "h1":
            self._capture_h1 = True
        if lower_tag == "link" and "canonical" in values.get("rel", "").lower().split():
            self.canonical = values.get("href", "").strip()
        if lower_tag == "meta" and values.get("name", "").lower() in {"robots", "googlebot", "bingbot"}:
            self.meta_robots.append(values.get("content", "").strip())
        if lower_tag == "a" and values.get("href"):
            self.links.append(values["href"].strip())
        if lower_tag == "script" and values.get("type", "").lower() == "application/ld+json":
            self._capture_schema = True
            self._current_schema_parts = []

    def handle_endtag(self, tag: str) -> None:
        lower_tag = tag.lower()
        if lower_tag == "title":
            self._capture_title = False
        if lower_tag == "h1":
            self._capture_h1 = False
        if lower_tag == "script":
            if self._capture_schema:
                self.schema_documents.append("".join(self._current_schema_parts))
            self._capture_schema = False
        if lower_tag in {"script", "style", "noscript", "svg"} and self._ignored_depth:
            self._ignored_depth -= 1

    def handle_data(self, data: str) -> None:
        text = " ".join(data.split())
        if not text:
            return
        if self._capture_title:
            self.title_parts.append(text)
        if self._capture_h1:
            self.h1_parts.append(text)
        if self._capture_schema:
            self._current_schema_parts.append(data)
        if not self._ignored_depth:
            self.visible_parts.append(text)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def git(root: Path, *args: str) -> str:
    return subprocess.check_output(["git", "-C", str(root), *args], text=True).strip()


def fetch(url: str, method: str = "GET") -> tuple[int, dict[str, str], bytes, str]:
    request = urllib.request.Request(url, method=method, headers={"User-Agent": USER_AGENT, "Accept": "text/html,*/*"})
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            return response.status, {key.lower(): value for key, value in response.headers.items()}, response.read(), response.geturl()
    except urllib.error.HTTPError as error:
        return error.code, {key.lower(): value for key, value in error.headers.items()}, error.read(), error.geturl()


def sitemap_urls(base_url: str) -> list[str]:
    status, _, body, _ = fetch(base_url.rstrip("/") + "/sitemap_index.xml")
    if status != 200:
        raise RuntimeError(f"sitemap index returned {status}")
    root = ET.fromstring(body)
    child_urls = [node.text.strip() for node in root.findall("{*}sitemap/{*}loc") if node.text]
    urls: set[str] = set()
    for child in child_urls:
        child_status, _, child_body, _ = fetch(child)
        if child_status != 200:
            raise RuntimeError(f"child sitemap returned {child_status}: {child}")
        child_root = ET.fromstring(child_body)
        urls.update(node.text.strip() for node in child_root.findall("{*}url/{*}loc") if node.text)
    return sorted(urls)


def frontend_files(root: Path) -> list[tuple[str, str]]:
    tracked = git(root, "ls-files").splitlines()
    categories = {
        ".css": "css",
        ".js": "javascript",
        ".jpg": "image", ".jpeg": "image", ".png": "image", ".gif": "image", ".webp": "image", ".svg": "image", ".avif": "image",
    }
    output: list[tuple[str, str]] = []
    for relative in tracked:
        path = root / relative
        suffix = path.suffix.lower()
        category = categories.get(suffix)
        if category is None and relative.startswith("wp-content/themes/generatepress-envitechal/") and suffix == ".php":
            category = "template"
        if category and path.is_file():
            output.append((relative.replace("\\", "/"), category))
    return output


def normalized_host(url: str) -> str:
    return (urllib.parse.urlsplit(url).hostname or "").lower()


def audit_page(url: str, site_host: str) -> dict[str, object]:
    status, headers, body, effective = fetch(url)
    row: dict[str, object] = {
        "url": url,
        "status": status,
        "effective_url": effective,
        "content_type": headers.get("content-type", ""),
        "x_robots_tag": headers.get("x-robots-tag", ""),
        "cache_control": headers.get("cache-control", ""),
        "bytes": len(body),
        "title": "",
        "h1": "",
        "canonical": "",
        "meta_robots": "",
        "schema_types": "",
        "schema_ids": "",
        "schema_json_valid": "",
        "duplicate_entity_ids": "",
        "canonical_in_schema": "",
        "internal_link_count": 0,
        "credential_claim_terms": "",
        "credential_claim_snippets": "",
    }
    if status != 200 or "html" not in headers.get("content-type", "").lower():
        return row

    parser = PageParser()
    parser.feed(body.decode("utf-8", errors="replace"))
    schema = "\n".join(parser.schema_documents)
    types: set[str] = set()
    ids: set[str] = set()
    defined_ids: list[str] = []
    schema_valid = True

    def walk_schema(value: object) -> None:
        if isinstance(value, dict):
            node_type = value.get("@type")
            if isinstance(node_type, str):
                types.add(node_type)
            elif isinstance(node_type, list):
                types.update(item for item in node_type if isinstance(item, str))
            node_id = value.get("@id")
            if isinstance(node_id, str):
                ids.add(node_id)
                if len(value) > 1:
                    defined_ids.append(node_id)
            for child in value.values():
                walk_schema(child)
        elif isinstance(value, list):
            for child in value:
                walk_schema(child)

    for document in parser.schema_documents:
        try:
            walk_schema(json.loads(document))
        except json.JSONDecodeError:
            schema_valid = False
    duplicate_ids = sorted({item for item in defined_ids if defined_ids.count(item) > 1})
    visible = " ".join(parser.visible_parts)
    claim_terms = sorted({match.group(0) for match in CLAIM_RE.finditer(visible)}, key=str.lower)
    claim_snippets: list[str] = []
    for sentence in re.split(r"(?<=[.!?])\s+|\s{2,}", visible):
        sentence = " ".join(sentence.split())
        if CLAIM_RE.search(sentence) and sentence not in claim_snippets:
            claim_snippets.append(sentence[:500])
    internal_links = 0
    for href in parser.links:
        absolute = urllib.parse.urljoin(url, href)
        if normalized_host(absolute) == site_host:
            internal_links += 1
    row.update({
        "title": " ".join(parser.title_parts),
        "h1": " ".join(parser.h1_parts),
        "canonical": parser.canonical,
        "meta_robots": "; ".join(parser.meta_robots),
        "schema_types": ";".join(sorted(types)),
        "schema_ids": ";".join(sorted(ids)),
        "schema_json_valid": "yes" if schema_valid else "no",
        "duplicate_entity_ids": ";".join(duplicate_ids),
        "canonical_in_schema": "yes" if parser.canonical and parser.canonical in schema else "no",
        "internal_link_count": internal_links,
        "credential_claim_terms": ";".join(claim_terms),
        "credential_claim_snippets": " || ".join(claim_snippets),
    })
    return row


def write_csv(path: Path, rows: list[dict[str, object]], fieldnames: list[str]) -> None:
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames, extrasaction="ignore")
        writer.writeheader()
        writer.writerows(rows)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://envitechal.com")
    parser.add_argument("--output-dir", required=True)
    parser.add_argument("--repo-root", default=str(Path(__file__).resolve().parents[1]))
    parser.add_argument("--reuse-url-inventory", action="store_true")
    args = parser.parse_args()

    # Keep the caller-visible workspace path. Codex desktop workspaces may be
    # backed by a junction whose resolved target deliberately omits .git.
    root = Path(args.repo_root).absolute()
    output = Path(args.output_dir).resolve()
    output.mkdir(parents=True, exist_ok=True)
    base_url = args.base_url.rstrip("/")
    host = normalized_host(base_url)
    if host not in {"envitechal.com", "staging.envitechal.com"}:
        raise RuntimeError("base URL must be an approved Envi Tech AL host")

    url_fields = [
        "url", "status", "effective_url", "content_type", "x_robots_tag", "cache_control", "bytes",
        "title", "h1", "canonical", "meta_robots", "schema_types", "schema_ids",
        "schema_json_valid", "duplicate_entity_ids", "canonical_in_schema",
        "internal_link_count", "credential_claim_terms", "credential_claim_snippets",
    ]
    inventory_path = output / "url-inventory.csv"
    if args.reuse_url_inventory and inventory_path.is_file():
        with inventory_path.open("r", encoding="utf-8", newline="") as handle:
            rows = list(csv.DictReader(handle))
        for row in rows:
            row["status"] = int(row["status"])
            row["bytes"] = int(row["bytes"])
            row["internal_link_count"] = int(row["internal_link_count"])
    else:
        urls = sitemap_urls(base_url)
        with ThreadPoolExecutor(max_workers=6) as pool:
            rows = list(pool.map(lambda url: audit_page(url, host), urls))
        write_csv(inventory_path, rows, url_fields)

    occurrence_rows: list[dict[str, object]] = []
    for row in rows:
        snippets = [snippet.strip() for snippet in str(row.get("credential_claim_snippets", "")).split(" || ") if snippet.strip()]
        for snippet in snippets:
            lower = snippet.lower()
            if "lab-284" in lower:
                action, risk = "CORRECT", "critical"
            elif ("pnac" in lower or "accredit" in lower) and "lab-285" not in lower and "lab-347" not in lower:
                action, risk = "SCOPE", "high"
            else:
                action, risk = "KEEP_REVIEWED", "moderate"
            occurrence_rows.append({
                "url": row["url"],
                "surface": "rendered_html",
                "exact_text": snippet,
                "evidence_status": "primary-source bounded" if action == "KEEP_REVIEWED" else "requires adjudication",
                "risk": risk,
                "recommended_action": action,
                "canonical_claim": "Karachi PNAC LAB-285 and Lahore PNAC LAB-347 are location- and scope-specific.",
            })
    write_csv(
        output / "claim-occurrence-register.csv",
        occurrence_rows,
        ["url", "surface", "exact_text", "evidence_status", "risk", "recommended_action", "canonical_claim"],
    )

    hashes = []
    for relative, category in frontend_files(root):
        path = root / relative
        hashes.append({"category": category, "path": relative, "sha256": sha256(path), "bytes": path.stat().st_size})
    write_csv(output / "frontend-freeze-hashes.csv", hashes, ["category", "path", "sha256", "bytes"])

    failures = [row for row in rows if row["status"] != 200 or row["canonical"] != row["url"]]
    noindex = [row["url"] for row in rows if "noindex" in (str(row["x_robots_tag"]) + " " + str(row["meta_robots"])).lower()]
    schema_failures = [
        row["url"] for row in rows
        if row.get("schema_json_valid") != "yes" or row.get("duplicate_entity_ids") or row.get("canonical_in_schema") != "yes"
    ]
    summary = {
        "generated_utc": datetime.now(timezone.utc).replace(microsecond=0).isoformat(),
        "base_url": base_url,
        "repository_commit": git(root, "rev-parse", "HEAD"),
        "url_count": len(rows),
        "frontend_file_count": len(hashes),
        "non_200_or_non_self_canonical": failures,
        "sitemap_noindex_urls": noindex,
        "schema_contract_failures": schema_failures,
        "credential_term_url_count": sum(bool(row["credential_claim_terms"]) for row in rows),
        "rendered_claim_occurrence_count": len(occurrence_rows),
    }
    (output / "summary.json").write_text(json.dumps(summary, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

    print(f"Audited {len(rows)} sitemap URLs and hashed {len(hashes)} frontend files.")
    print(f"Output: {output}")
    return 1 if failures or noindex or schema_failures else 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (RuntimeError, urllib.error.URLError, ET.ParseError) as error:
        print(f"Audit failed: {error}", file=sys.stderr)
        raise SystemExit(1)
