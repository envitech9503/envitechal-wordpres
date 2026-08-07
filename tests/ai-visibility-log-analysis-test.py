#!/usr/bin/env python3
"""Synthetic privacy and classification tests for log aggregation."""

from __future__ import annotations

import importlib.util
from pathlib import Path


ROOT = Path(__file__).absolute().parents[1]
SPEC = importlib.util.spec_from_file_location("ai_log", ROOT / "scripts" / "analyze-ai-visibility-logs.py")
assert SPEC and SPEC.loader
MODULE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(MODULE)


def line(path: str, referer: str, agent: str, status: int = 200) -> str:
    return f'203.0.113.9 - - [08/Aug/2026:09:10:11 +0500] "GET {path} HTTP/1.1" {status} 123 "{referer}" "{agent}"'


rows, summary = MODULE.aggregate(
    [
        line("/services/?email=secret@example.com", "https://chatgpt.com/citation/private", "Mozilla/5.0"),
        line("/llms.txt?cache=private", "-", "OAI-SearchBot/1.0"),
        line("/private?id=44", "-", "Googlebot/2.1"),
        "not a log line",
    ],
    minimum=1,
)

assert summary["included_requests"] == 3
assert summary["rejected_lines"] == 1
assert {row["traffic_class"] for row in rows} == {
    "HUMAN_AI_REFERRAL",
    "DECLARED_AI_CRAWLER",
    "DECLARED_SEARCH_CRAWLER",
}
assert any(row["path"] == "/services/" for row in rows)
assert any(row["path"] == "/llms.txt" and row["discovery_resource"] for row in rows)
serialized = repr(rows) + repr(summary)
for forbidden in ["203.0.113.9", "secret@example.com", "citation/private", "OAI-SearchBot/1.0", "Googlebot/2.1"]:
    assert forbidden not in serialized, forbidden

low_rows, _ = MODULE.aggregate(
    [line("/one/", "https://perplexity.ai/", "Mozilla/5.0")],
    minimum=5,
)
assert low_rows[0]["path"] == "OTHER_LOW_VOLUME"
print("AI visibility log aggregation tests passed.")
