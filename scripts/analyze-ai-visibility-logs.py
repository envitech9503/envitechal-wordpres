#!/usr/bin/env python3
"""Create privacy-minimized AI visibility aggregates from Apache access logs.

Raw log data is read locally and never copied into the output. IP addresses,
query strings, referrer paths and complete User-Agent strings are discarded.
"""

from __future__ import annotations

import argparse
import csv
import json
import re
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urlsplit


LOG_RE = re.compile(
    r'^(?P<remote>\S+)\s+\S+\s+\S+\s+\[(?P<time>[^]]+)]\s+'
    r'"(?P<method>\S+)\s+(?P<target>\S+)\s+(?P<protocol>[^"]+)"\s+'
    r'(?P<status>\d{3})\s+(?P<bytes>\S+)\s+'
    r'"(?P<referer>[^"]*)"\s+"(?P<agent>[^"]*)"'
)

AI_REFERRERS = {
    "chatgpt.com": "CHATGPT",
    "chat.openai.com": "CHATGPT",
    "perplexity.ai": "PERPLEXITY",
    "copilot.microsoft.com": "MICROSOFT_COPILOT",
    "gemini.google.com": "GOOGLE_GEMINI",
    "claude.ai": "ANTHROPIC_CLAUDE",
}

DECLARED_AI_BOTS = {
    "oai-searchbot": "OAI_SEARCHBOT",
    "gptbot": "GPTBOT",
    "chatgpt-user": "CHATGPT_USER",
    "claudebot": "CLAUDEBOT",
    "perplexitybot": "PERPLEXITYBOT",
}

SEARCH_BOTS = {
    "googlebot": "GOOGLEBOT",
    "bingbot": "BINGBOT",
}

DISCOVERY_PATHS = {
    "/robots.txt",
    "/sitemap_index.xml",
    "/llms.txt",
    "/llms-full.txt",
    "/wp-json/ai-visibility/v1/agent-skills",
}


def normalize_host(value: str) -> str:
    host = value.lower().rstrip(".")
    return host[4:] if host.startswith("www.") else host


def referrer_family(value: str) -> str | None:
    if not value or value == "-":
        return None
    try:
        host = normalize_host(urlsplit(value).hostname or "")
    except ValueError:
        return None
    for expected, family in AI_REFERRERS.items():
        if host == expected or host.endswith("." + expected):
            return family
    return None


def classify(agent: str, referer: str) -> tuple[str, str]:
    lowered = agent.lower()
    for token, family in DECLARED_AI_BOTS.items():
        if token in lowered:
            return "DECLARED_AI_CRAWLER", family
    for token, family in SEARCH_BOTS.items():
        if token in lowered:
            return "DECLARED_SEARCH_CRAWLER", family
    referral = referrer_family(referer)
    if referral:
        return "HUMAN_AI_REFERRAL", referral
    return "OTHER", "OTHER"


def normalize_path(target: str) -> str:
    try:
        path = urlsplit(target).path or "/"
    except ValueError:
        return "/INVALID_TARGET"
    if not path.startswith("/"):
        return "/INVALID_TARGET"
    return path


def parse_date(value: str) -> str:
    parsed = datetime.strptime(value, "%d/%b/%Y:%H:%M:%S %z")
    return parsed.strftime("%Y-%m-%d")


def is_discovery(path: str) -> bool:
    return path in DISCOVERY_PATHS or path.endswith("-sitemap.xml")


def aggregate(lines: list[str], minimum: int) -> tuple[list[dict[str, object]], dict[str, object]]:
    counts: Counter[tuple[str, str, str, str, int, bool]] = Counter()
    byte_counts: Counter[tuple[str, str, str, str, int, bool]] = Counter()
    rejected = 0
    excluded_other = 0

    for line in lines:
        match = LOG_RE.match(line.rstrip("\r\n"))
        if not match:
            rejected += 1
            continue
        try:
            date = parse_date(match.group("time"))
            status = int(match.group("status"))
        except (ValueError, OverflowError):
            rejected += 1
            continue
        traffic_class, family = classify(match.group("agent"), match.group("referer"))
        if traffic_class == "OTHER":
            excluded_other += 1
            continue
        path = normalize_path(match.group("target"))
        key = (date, traffic_class, family, path, status, is_discovery(path))
        counts[key] += 1
        size = match.group("bytes")
        if size.isdigit():
            byte_counts[key] += int(size)

    visible: list[dict[str, object]] = []
    low_volume: Counter[tuple[str, str, str, int, bool]] = Counter()
    low_bytes: Counter[tuple[str, str, str, int, bool]] = Counter()

    for key, count in counts.items():
        date, traffic_class, family, path, status, discovery = key
        if count < minimum:
            low_key = (date, traffic_class, family, status, discovery)
            low_volume[low_key] += count
            low_bytes[low_key] += byte_counts[key]
            continue
        visible.append(
            {
                "date": date,
                "traffic_class": traffic_class,
                "source_family": family,
                "path": path,
                "status": status,
                "status_family": f"{status // 100}xx",
                "discovery_resource": discovery,
                "requests": count,
                "bytes": byte_counts[key],
            }
        )

    for key, count in low_volume.items():
        date, traffic_class, family, status, discovery = key
        visible.append(
            {
                "date": date,
                "traffic_class": traffic_class,
                "source_family": family,
                "path": "OTHER_LOW_VOLUME",
                "status": status,
                "status_family": f"{status // 100}xx",
                "discovery_resource": discovery,
                "requests": count,
                "bytes": low_bytes[key],
            }
        )

    visible.sort(key=lambda row: (str(row["date"]), str(row["traffic_class"]), str(row["source_family"]), str(row["path"]), int(row["status"])))
    summary = {
        "schema_version": 1,
        "generated_utc": datetime.now(timezone.utc).isoformat(timespec="seconds"),
        "timezone": "Asia/Karachi (date preserved from log offset)",
        "minimum_path_count": minimum,
        "included_requests": sum(counts.values()),
        "excluded_other_requests": excluded_other,
        "rejected_lines": rejected,
        "privacy": "Aggregate only; IP, query, referrer path and full User-Agent are discarded.",
        "identity_note": "Declared crawler classes are not verified identities without provider verification.",
    }
    return visible, summary


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", required=True, help="Apache combined log file")
    parser.add_argument("--output-dir", required=True)
    parser.add_argument("--minimum-path-count", type=int, default=5)
    args = parser.parse_args()

    if args.minimum_path_count < 1:
        parser.error("--minimum-path-count must be at least 1")

    source = Path(args.input)
    output = Path(args.output_dir)
    output.mkdir(parents=True, exist_ok=True)
    rows, summary = aggregate(source.read_text(encoding="utf-8", errors="replace").splitlines(), args.minimum_path_count)

    fields = ["date", "traffic_class", "source_family", "path", "status", "status_family", "discovery_resource", "requests", "bytes"]
    with (output / "ai-visibility-traffic.csv").open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields)
        writer.writeheader()
        writer.writerows(rows)
    (output / "summary.json").write_text(json.dumps(summary, indent=2) + "\n", encoding="utf-8")
    print(json.dumps({"rows": len(rows), **summary}, sort_keys=True))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
