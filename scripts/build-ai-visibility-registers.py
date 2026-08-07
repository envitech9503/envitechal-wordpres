#!/usr/bin/env python3
"""Build reviewed URL disposition and canonical-intent registers."""

from __future__ import annotations

import argparse
import csv
import re
import urllib.parse
from collections import defaultdict
from pathlib import Path


AUTHORITY_INTENTS = {
    "/": "organization and national service overview",
    "/aboutus/": "organization identity and governance",
    "/about-envi-tech-al-for-ai-search-engines/": "machine-readable organization facts",
    "/accreditations-certifications/": "credentials and issuer verification",
    "/karachi-environmental-lab/": "Karachi environmental laboratory",
    "/lahore-environmental-lab/": "Lahore environmental laboratory",
    "/services/": "service portfolio",
    "/services/analytical-lab-services/": "analytical laboratory services",
    "/services/water-testing-lab-services/": "water testing laboratory services",
    "/drinking-water-testing-lab/": "drinking-water testing",
    "/wastewater-testing-services/": "wastewater testing",
    "/ambient-air-monitoring-services/": "ambient-air monitoring",
    "/gaseous-air-emission-testing-lab-near-me/": "stack and gaseous emissions testing",
    "/noise-monitoring-dosimetry/": "noise monitoring and dosimetry",
    "/soil-hazardous-waste-testing/": "soil and hazardous-waste testing",
    "/industrial-hygiene-monitoring/": "industrial-hygiene monitoring",
    "/services/equipment-calibration-services/": "equipment calibration capability",
    "/services/environmental-consultancy/": "environmental consultancy",
    "/services/certification-advisory/": "certification advisory",
    "/services/ballast-water-testing-services/": "ballast-water testing capability",
    "/services/thermal-imaging-inspection/": "thermal-imaging inspection",
    "/services/technical-advisory-2/": "technical advisory",
    "/services/environmental-advisory/": "environmental advisory",
    "/maritime-environmental-testing/": "maritime environmental testing",
    "/emp-emr-iee-eia-compliance/": "EMP, EMR, IEE and EIA compliance",
    "/report-verification-portal/": "report verification",
    "/environmental-testing-faqs-pakistan/": "environmental testing FAQs",
    "/sindh-environmental-quality-standards-seqs/": "SEQS reference",
    "/tdap-registered-lab-in-karachi-pakistan/": "TDAP laboratory support",
    "/contact-us-envi-tech-al/": "contact and quotation route",
    "/downloads/": "official downloads",
    "/blognewsupdates/": "knowledge hub index",
    "/careers-at-envi-tech-al/": "careers",
    "/ourclients/": "published client portfolio",
}


def path_of(url: str) -> str:
    path = urllib.parse.urlsplit(url).path
    return path if path.endswith("/") else path + "/"


def supporting_authority(path: str) -> str:
    rules = [
        (("water", "drinking"), "/drinking-water-testing-lab/"),
        (("wastewater", "effluent", "etp"), "/wastewater-testing-services/"),
        (("ambient-air",), "/ambient-air-monitoring-services/"),
        (("noise",), "/noise-monitoring-dosimetry/"),
        (("calibration",), "/services/equipment-calibration-services/"),
        (("consultancy", "eia", "iee", "emp", "emr", "epa", "noc"), "/services/environmental-consultancy/"),
        (("gots", "ce-marking", "certification"), "/services/certification-advisory/"),
        (("iso-17025", "environmental-lab"), "/services/analytical-lab-services/"),
        (("ballast", "maritime"), "/services/ballast-water-testing-services/"),
        (("ppwr", "heavy-metal", "protection-kit"), "/services/analytical-lab-services/"),
        (("tdap",), "/tdap-registered-lab-in-karachi-pakistan/"),
    ]
    for needles, authority in rules:
        if any(needle in path for needle in needles):
            return authority
    return "/services/analytical-lab-services/"


def redirect_map(path: Path) -> dict[str, str]:
    source = path.read_text(encoding="utf-8")
    match = re.search(r"function eta_modern_legacy_redirect_map\(\).*?return \[(.*?)^\s*\];", source, re.MULTILINE | re.DOTALL)
    if not match:
        raise RuntimeError("redirect map could not be parsed")
    pairs = dict(re.findall(r"^\s*'([^']+)'\s*=>\s*'([^']+)',\s*$", match.group(1), re.MULTILINE))
    if not pairs:
        raise RuntimeError("redirect map is empty")
    return pairs


def write_csv(path: Path, rows: list[dict[str, str]], fields: list[str]) -> None:
    with path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields)
        writer.writeheader()
        writer.writerows(rows)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--inventory", required=True)
    parser.add_argument("--redirect-file", required=True)
    parser.add_argument("--output-dir", required=True)
    args = parser.parse_args()

    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    with Path(args.inventory).open("r", encoding="utf-8", newline="") as handle:
        inventory = list(csv.DictReader(handle))
    redirects = redirect_map(Path(args.redirect_file))
    inventory_by_path = {path_of(row["url"]): row for row in inventory}

    for source, target in redirects.items():
        if target in redirects:
            raise RuntimeError(f"redirect chain configured: {source} -> {target}")
        target_row = inventory_by_path.get(target)
        if not target_row or target_row["status"] != "200" or target_row["canonical"] != target_row["url"]:
            raise RuntimeError(f"redirect target is not a direct self-canonical 200: {source} -> {target}")

    disposition_rows: list[dict[str, str]] = []
    authority_support: dict[str, list[str]] = defaultdict(list)
    for path, row in sorted(inventory_by_path.items()):
        if path in redirects:
            disposition, target, rationale = "301", redirects[path], "obsolete or competing URL with an equivalent canonical intent"
        elif path in AUTHORITY_INTENTS:
            disposition, target, rationale = "KEEP_AUTHORITY", path, AUTHORITY_INTENTS[path]
        else:
            target = supporting_authority(path)
            disposition, rationale = "KEEP_SUPPORTING", "unique supporting article; internal evidence and anchors should reinforce the authority URL"
            authority_support[target].append(path)
        disposition_rows.append({
            "url": row["url"], "current_status": row["status"], "disposition": disposition,
            "canonical_or_redirect_target": "https://envitechal.com" + target,
            "sitemap_after_release": "no" if disposition == "301" else "yes",
            "rationale": rationale,
        })

    for source, target in sorted(redirects.items()):
        if source in inventory_by_path:
            continue
        disposition_rows.append({
            "url": "https://envitechal.com" + source, "current_status": "legacy",
            "disposition": "301", "canonical_or_redirect_target": "https://envitechal.com" + target,
            "sitemap_after_release": "no", "rationale": "reviewed legacy consolidation already outside the sitemap",
        })

    intent_rows = []
    for authority, intent in AUTHORITY_INTENTS.items():
        if authority not in inventory_by_path:
            continue
        supporters = sorted(authority_support.get(authority, []))
        intent_rows.append({
            "primary_intent": intent,
            "authority_url": "https://envitechal.com" + authority,
            "supporting_urls": " | ".join("https://envitechal.com" + path for path in supporters),
            "authority_status": "indexable self-canonical 200",
            "schema_contract": "page-type contract; stable Organization/provider references",
        })

    write_csv(
        output / "legacy-url-disposition-register.csv", disposition_rows,
        ["url", "current_status", "disposition", "canonical_or_redirect_target", "sitemap_after_release", "rationale"],
    )
    write_csv(
        output / "canonical-intent-map.csv", intent_rows,
        ["primary_intent", "authority_url", "supporting_urls", "authority_status", "schema_contract"],
    )
    print(f"Recorded {len(disposition_rows)} URL dispositions and {len(intent_rows)} canonical intents.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
