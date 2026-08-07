#!/usr/bin/env python3
"""Fail-closed contracts for the AI visibility execution deliverables."""

from __future__ import annotations

import csv
import hashlib
import json
import re
from pathlib import Path


ROOT = Path(__file__).absolute().parents[1]
AUDIT = ROOT / "docs" / "audits" / "2026-08-08"

required = [
    ROOT / "docs" / "CREDENTIAL_CLAIM_LEDGER.md",
    ROOT / "docs" / "PNAC_CREDENTIAL_CLAIM_LEDGER.md",
    ROOT / "docs" / "AI_VISIBILITY_STRUCTURED_DATA_CONTRACT.md",
    ROOT / "docs" / "AI_VISIBILITY_UNPUBLISHED_EVIDENCE_PACKS.md",
    ROOT / "docs" / "AI_VISIBILITY_EXTERNAL_ENTITY_REGISTER.csv",
    ROOT / "docs" / "AI_VISIBILITY_EXTERNAL_DESCRIPTIONS.md",
    ROOT / "docs" / "AI_VISIBILITY_MEASUREMENT_SPEC.md",
    AUDIT / "url-inventory.csv",
    AUDIT / "claim-occurrence-register.csv",
    AUDIT / "frontend-freeze-hashes.csv",
    AUDIT / "legacy-url-disposition-register.csv",
    AUDIT / "canonical-intent-map.csv",
    AUDIT / "summary.json",
]
missing = [str(path.relative_to(ROOT)) for path in required if not path.is_file()]
assert not missing, f"Missing architecture deliverables: {missing}"

summary = json.loads((AUDIT / "summary.json").read_text(encoding="utf-8"))
assert summary["url_count"] >= 1
assert summary["rendered_claim_occurrence_count"] >= 1
assert summary["non_200_or_non_self_canonical"] == []
assert summary["sitemap_noindex_urls"] == []
assert summary["schema_contract_failures"] == []

with (AUDIT / "legacy-url-disposition-register.csv").open(encoding="utf-8", newline="") as handle:
    dispositions = list(csv.DictReader(handle))
assert dispositions
assert all(row["disposition"] in {"KEEP_AUTHORITY", "KEEP_SUPPORTING", "301", "NOINDEX", "HOLD"} for row in dispositions)
assert all(row["canonical_or_redirect_target"].startswith("https://envitechal.com/") for row in dispositions)
assert len({row["url"] for row in dispositions}) == len(dispositions)

redirect_php = (ROOT / "wp-content" / "themes" / "generatepress-envitechal" / "inc" / "legacy-redirects.php").read_text(encoding="utf-8")
redirect_pairs = re.findall(r"^\s*'(/[^']*)'\s*=>\s*'(/[^']*)',\s*$", redirect_php, re.MULTILINE)
redirect_sources = {"https://envitechal.com" + source for source, _ in redirect_pairs}
registered_redirects = {row["url"] for row in dispositions if row["disposition"] == "301"}
assert redirect_sources == registered_redirects, "Redirect map and disposition register drifted"

with (AUDIT / "canonical-intent-map.csv").open(encoding="utf-8", newline="") as handle:
    intents = list(csv.DictReader(handle))
assert len(intents) >= 30
assert len({row["primary_intent"] for row in intents}) == len(intents)
assert len({row["authority_url"] for row in intents}) == len(intents)

# The mandate permits content/data/redirect changes, but not visual assets or
# behaviour. Prove CSS, JavaScript and image bytes still equal the baseline.
with (AUDIT / "frontend-freeze-hashes.csv").open(encoding="utf-8", newline="") as handle:
    frozen = list(csv.DictReader(handle))
for row in frozen:
    if row["category"] not in {"css", "js", "image"}:
        continue
    path = ROOT / row["path"]
    assert path.is_file(), f"Frozen frontend file missing: {row['path']}"
    actual = hashlib.sha256(path.read_bytes()).hexdigest()
    assert actual == row["sha256"], f"Frontend freeze violated: {row['path']}"

credential_ledger = (ROOT / "docs" / "CREDENTIAL_CLAIM_LEDGER.md").read_text(encoding="utf-8")
assert "LAB-284 belongs to another organization" in credential_ledger
assert "ISSUER-CURRENT CONFIRMATION REQUIRED" in credential_ledger
hold_pack = (ROOT / "docs" / "AI_VISIBILITY_UNPUBLISHED_EVIDENCE_PACKS.md").read_text(encoding="utf-8")
assert "HOLD — NOT PUBLIC CONTENT, NOT A CLAIM, NOT APPROVED FOR SCHEMA" in hold_pack
print(f"Architecture completion contracts passed: {len(dispositions)} URL decisions, {len(intents)} canonical intents.")
