#!/usr/bin/env python3
"""Synthetic contract test for the private aggregate dashboard."""

from __future__ import annotations

import csv
import importlib.util
from pathlib import Path
from tempfile import TemporaryDirectory


ROOT = Path(__file__).absolute().parents[1]
SPEC = importlib.util.spec_from_file_location("dashboard", ROOT / "scripts" / "build-ai-visibility-dashboard.py")
assert SPEC and SPEC.loader
MODULE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(MODULE)

with TemporaryDirectory() as directory:
    csv_path = Path(directory) / "aggregate.csv"
    fields = sorted(MODULE.EXPECTED_FIELDS)
    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields)
        writer.writeheader()
        writer.writerow({
            "date": "2026-08-08", "traffic_class": "HUMAN_AI_REFERRAL",
            "source_family": "CHATGPT", "path": "/services/", "status": "200",
            "status_family": "2xx", "discovery_resource": "False", "requests": "7", "bytes": "700",
        })
        writer.writerow({
            "date": "2026-08-08", "traffic_class": "DECLARED_AI_CRAWLER",
            "source_family": "OAI_SEARCHBOT", "path": "/llms.txt", "status": "429",
            "status_family": "4xx", "discovery_resource": "True", "requests": "2", "bytes": "20",
        })
    rows = MODULE.load_rows(csv_path)
    summary = MODULE.summarize(rows, {"minimum_path_count": 5, "identity_note": "Declared only"})
    rendered = MODULE.render(summary)
    assert summary["included_requests"] == 9
    assert summary["anomalies"] == {"/llms.txt | 429": 2}
    assert "Not a public webpage" in rendered
    assert "203.0.113.9" not in rendered
    assert "secret@example.com" not in rendered

    with csv_path.open("a", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields)
        writer.writerow({
            "date": "2026-08-08", "traffic_class": "DECLARED_AI_CRAWLER",
            "source_family": "OAI_SEARCHBOT", "path": "/private?token=x", "status": "200",
            "status_family": "2xx", "discovery_resource": "False", "requests": "1", "bytes": "1",
        })
    try:
        MODULE.load_rows(csv_path)
    except ValueError as error:
        assert "non-minimized path" in str(error)
    else:
        raise AssertionError("dashboard accepted a query string")

    csv_path.write_text(
        ",".join(fields) + "\n" +
        ",".join({
            "date": "2026-08-08", "traffic_class": "DECLARED_AI_CRAWLER",
            "source_family": "OAI_SEARCHBOT", "path": "/report/0123456789abcdef01234567/", "status": "200",
            "status_family": "2xx", "discovery_resource": "False", "requests": "1", "bytes": "1",
        }[field] for field in fields) + "\n",
        encoding="utf-8",
    )
    try:
        MODULE.load_rows(csv_path)
    except ValueError as error:
        assert "non-minimized path" in str(error)
    else:
        raise AssertionError("dashboard accepted a token-like path")

print("AI visibility dashboard tests passed.")
