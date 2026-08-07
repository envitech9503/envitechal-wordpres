#!/usr/bin/env python3
"""Report credential review and expiry state from the controlled schedule."""

from __future__ import annotations

import argparse
import csv
import json
from datetime import date, datetime
from pathlib import Path


REQUIRED = {"credential_id", "issuer", "entity", "valid_until", "next_review", "review_cadence_days", "evidence_state", "required_review", "owner"}


def evaluate(path: Path, as_of: date) -> list[dict[str, object]]:
    results = []
    with path.open(encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        if set(reader.fieldnames or ()) != REQUIRED:
            raise ValueError("credential schedule has an unexpected schema")
        for row in reader:
            expiry = datetime.strptime(row["valid_until"], "%Y-%m-%d").date()
            review = datetime.strptime(row["next_review"], "%Y-%m-%d").date()
            days_to_expiry = (expiry - as_of).days
            if days_to_expiry < 0:
                status = "EXPIRED_FAIL_CLOSED"
            elif review <= as_of:
                status = "REVIEW_DUE"
            elif days_to_expiry <= 90:
                status = "EXPIRY_WITHIN_90_DAYS"
            else:
                status = "CURRENT_REVIEW_SCHEDULED"
            results.append({
                "credential_id": row["credential_id"], "status": status,
                "days_to_expiry": days_to_expiry, "next_review": row["next_review"],
                "evidence_state": row["evidence_state"], "required_review": row["required_review"],
            })
    return results


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--schedule", default=str(Path(__file__).absolute().parents[1] / "docs" / "CREDENTIAL_REVIEW_SCHEDULE.csv"))
    parser.add_argument("--as-of", default=date.today().isoformat())
    parser.add_argument("--output")
    args = parser.parse_args()
    results = evaluate(Path(args.schedule), datetime.strptime(args.as_of, "%Y-%m-%d").date())
    payload = {"as_of": args.as_of, "credentials": results}
    serialized = json.dumps(payload, indent=2) + "\n"
    if args.output:
        Path(args.output).write_text(serialized, encoding="utf-8")
    print(serialized, end="")
    return 2 if any(row["status"].startswith("EXPIRED") for row in results) else 0


if __name__ == "__main__":
    raise SystemExit(main())
