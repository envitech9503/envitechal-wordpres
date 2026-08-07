#!/usr/bin/env python3
"""Build a private, aggregate-only AI visibility operations dashboard.

The input is the output of analyze-ai-visibility-logs.py. This script refuses
query strings, fragments and unknown columns so raw access-log data cannot be
silently turned into a report.
"""

from __future__ import annotations

import argparse
import csv
import html
import json
import os
import re
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path


EXPECTED_FIELDS = {
    "date", "traffic_class", "source_family", "path", "status",
    "status_family", "discovery_resource", "requests", "bytes",
}
ANOMALY_STATUSES = {403, 415, 429}
SENSITIVE_PATH = re.compile(r"(?:[0-9a-f]{24,}|[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12})", re.I)


def load_rows(path: Path) -> list[dict[str, object]]:
    with path.open(encoding="utf-8", newline="") as handle:
        reader = csv.DictReader(handle)
        if set(reader.fieldnames or ()) != EXPECTED_FIELDS:
            raise ValueError("aggregate CSV has an unexpected schema")
        rows: list[dict[str, object]] = []
        for line_number, raw in enumerate(reader, start=2):
            route = raw["path"]
            if not route or (route != "OTHER_LOW_VOLUME" and not route.startswith("/")):
                raise ValueError(f"invalid aggregate path on line {line_number}")
            if "?" in route or "#" in route or "@" in route or len(route) > 300 or SENSITIVE_PATH.search(route):
                raise ValueError(f"non-minimized path on line {line_number}")
            try:
                status = int(raw["status"])
                requests = int(raw["requests"])
                byte_count = int(raw["bytes"])
            except ValueError as exc:
                raise ValueError(f"invalid numeric field on line {line_number}") from exc
            if not 100 <= status <= 599 or requests < 0 or byte_count < 0:
                raise ValueError(f"out-of-range aggregate on line {line_number}")
            rows.append({**raw, "status": status, "requests": requests, "bytes": byte_count})
    return rows


def summarize(rows: list[dict[str, object]], run_summary: dict[str, object]) -> dict[str, object]:
    by_class: Counter[str] = Counter()
    by_source: Counter[str] = Counter()
    by_status: Counter[str] = Counter()
    by_path: Counter[str] = Counter()
    discovery: Counter[str] = Counter()
    anomalies: Counter[str] = Counter()
    dates: list[str] = []

    for row in rows:
        count = int(row["requests"])
        route = str(row["path"])
        status = int(row["status"])
        dates.append(str(row["date"]))
        by_class[str(row["traffic_class"])] += count
        by_source[str(row["source_family"])] += count
        by_status[str(row["status_family"])] += count
        by_path[route] += count
        if str(row["discovery_resource"]).lower() == "true":
            discovery[f"{route} | {status}"] += count
        if status in ANOMALY_STATUSES or status >= 500:
            anomalies[f"{route} | {status}"] += count

    return {
        "schema_version": 1,
        "generated_utc": datetime.now(timezone.utc).isoformat(timespec="seconds"),
        "period_start": min(dates) if dates else None,
        "period_end": max(dates) if dates else None,
        "included_requests": sum(by_class.values()),
        "privacy": "Aggregate only; no IP, query, referrer path or full User-Agent.",
        "identity_note": "Declared crawler classes are not verified identities without provider verification.",
        "minimum_path_count": run_summary.get("minimum_path_count"),
        "traffic_classes": dict(by_class.most_common()),
        "source_families": dict(by_source.most_common()),
        "status_families": dict(by_status.most_common()),
        "top_paths": dict(by_path.most_common(20)),
        "discovery_resources": dict(discovery.most_common()),
        "anomalies": dict(anomalies.most_common()),
    }


def table(title: str, values: dict[str, int], empty: str = "No data") -> str:
    body = "".join(
        f"<tr><th scope='row'>{html.escape(str(label))}</th><td>{count:,}</td></tr>"
        for label, count in values.items()
    ) or f"<tr><td colspan='2'>{html.escape(empty)}</td></tr>"
    return f"<section><h2>{html.escape(title)}</h2><table>{body}</table></section>"


def render(summary: dict[str, object]) -> str:
    anomalies = summary["anomalies"]
    anomaly_state = "Attention required" if anomalies else "No 403, 415, 429 or 5xx aggregate"
    return """<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Private AI Visibility Operations</title>
<style>body{{font:16px/1.45 system-ui,sans-serif;max-width:1100px;margin:auto;padding:2rem;color:#14251f;background:#f6f8f7}}header,section{{background:#fff;border:1px solid #d7dfdb;border-radius:12px;padding:1.1rem;margin:0 0 1rem}}h1,h2{{margin:.1rem 0 .8rem}}small{{color:#52645d}}.cards{{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem}}.metric{{font-size:1.7rem;font-weight:700}}table{{border-collapse:collapse;width:100%}}th,td{{text-align:left;border-bottom:1px solid #e3e8e5;padding:.45rem}}td{{text-align:right}}.ok{{color:#08783e}}.warn{{color:#9b3d00}}</style></head><body>
<header><h1>AI visibility operations</h1><small>Private aggregate report. Not a public webpage and not installed on the WordPress frontend.</small></header>
<div class="cards"><section><h2>Period</h2><div class="metric">{period}</div></section><section><h2>Included requests</h2><div class="metric">{requests:,}</div></section><section><h2>Anomaly state</h2><div class="metric {state_class}">{state}</div></section></div>
{tables}
<section><h2>Interpretation limits</h2><p>{identity}</p><p>{privacy}</p><small>Generated {generated}</small></section>
</body></html>
""".format(
        period=html.escape(f"{summary['period_start'] or 'n/a'} to {summary['period_end'] or 'n/a'}"),
        requests=int(summary["included_requests"]),
        state_class="warn" if anomalies else "ok",
        state=html.escape(anomaly_state),
        tables="".join([
            table("Traffic classes", summary["traffic_classes"]),
            table("Source families", summary["source_families"]),
            table("Status families", summary["status_families"]),
            table("Discovery resources", summary["discovery_resources"], "No discovery-resource traffic"),
            table("Anomalies", anomalies, "No configured anomaly status"),
            table("Top normalized paths", summary["top_paths"]),
        ]),
        identity=html.escape(str(summary["identity_note"])),
        privacy=html.escape(str(summary["privacy"])),
        generated=html.escape(str(summary["generated_utc"])),
    )


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--aggregate-csv", required=True)
    parser.add_argument("--run-summary", required=True)
    parser.add_argument("--output-dir", required=True)
    args = parser.parse_args()
    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    rows = load_rows(Path(args.aggregate_csv))
    run_summary = json.loads(Path(args.run_summary).read_text(encoding="utf-8"))
    result = summarize(rows, run_summary)
    html_path = output / "ai-visibility-dashboard.html"
    json_path = output / "dashboard-summary.json"
    html_path.write_text(render(result), encoding="utf-8")
    json_path.write_text(json.dumps(result, indent=2) + "\n", encoding="utf-8")
    if os.name != "nt":
        html_path.chmod(0o600)
        json_path.chmod(0o600)
    print(json.dumps({"rows": len(rows), "requests": result["included_requests"], "anomalies": len(result["anomalies"])}, sort_keys=True))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
