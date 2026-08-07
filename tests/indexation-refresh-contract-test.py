#!/usr/bin/env python3
"""Fail-closed contracts for index refresh records and Person schema."""

from __future__ import annotations

import csv
from pathlib import Path


ROOT = Path(__file__).absolute().parents[1]
register = ROOT / "docs" / "AI_VISIBILITY_INDEXATION_REFRESH_REGISTER.csv"
with register.open(encoding="utf-8-sig", newline="") as handle:
    rows = list(csv.DictReader(handle))

assert len(rows) == 9
allowed_states = {
    "PRIORITY_CRAWL_QUEUED", "SUBMITTED_SUCCESSFULLY", "VALIDATION_STARTED",
    "INDEX_CONFIRMED_QUEUE_NOT_REPEATED", "READY_FOR_SINGLE_SUBMISSION",
    "ACCOUNT_ACCESS_REQUIRED",
}
assert all(row["action_state"] in allowed_states for row in rows)
assert any(row["target_or_issue"] == "https://envitechal.com/accreditations-certifications/" and row["action_state"] == "PRIORITY_CRAWL_QUEUED" for row in rows)
assert any(row["target_or_issue"] == "https://envitechal.com/sitemap_index.xml" and row["action_state"] == "SUBMITTED_SUCCESSFULLY" for row in rows)

targets = "\n".join(row["target_or_issue"] for row in rows)
for prohibited in (
    "https://envitechal.com/certificates-approvals/",
    "https://envitechal.com/environmental-testing-lab-in-lahore/",
    "https://envitechal.com/accredited-water-testing-lab-in-karachi/",
):
    assert prohibited not in targets, f"redirect source must not be submitted: {prohibited}"

theme = (ROOT / "wp-content" / "themes" / "generatepress-envitechal" / "functions.php").read_text(encoding="utf-8")
assert "'@type' => 'Person'" not in theme, "Person schema requires an approved evidence record"
assert "'author' => ['@id' => home_url('/#organization')]" in theme
assert "'@type' => 'Organization',\n                'name' => 'Envi Tech AL'" in theme

print("Indexation refresh and no-invented-Person contracts passed.")
