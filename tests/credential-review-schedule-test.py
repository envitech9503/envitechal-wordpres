#!/usr/bin/env python3
from __future__ import annotations

import importlib.util
from datetime import date
from pathlib import Path

ROOT = Path(__file__).absolute().parents[1]
SPEC = importlib.util.spec_from_file_location("reviews", ROOT / "scripts" / "check-credential-reviews.py")
assert SPEC and SPEC.loader
MODULE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(MODULE)

rows = MODULE.evaluate(ROOT / "docs" / "CREDENTIAL_REVIEW_SCHEDULE.csv", date(2026, 8, 8))
assert len(rows) == 6
assert {row["credential_id"] for row in rows} == {"PNAC-KHI", "PNAC-LHE", "PEPA-LHE", "SEPA-KHI", "QMS", "EMS"}
assert all(row["status"] == "CURRENT_REVIEW_SCHEDULED" for row in rows)
assert all(row["days_to_expiry"] > 365 for row in rows)

future = MODULE.evaluate(ROOT / "docs" / "CREDENTIAL_REVIEW_SCHEDULE.csv", date(2030, 1, 1))
assert all(row["status"] == "EXPIRED_FAIL_CLOSED" for row in future)
print("Credential review schedule tests passed.")
