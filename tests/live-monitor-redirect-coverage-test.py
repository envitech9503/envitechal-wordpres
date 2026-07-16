#!/usr/bin/env python3

"""Keep the live redirect/sitemap monitor synchronized with the PHP map."""

from pathlib import Path
import re
from urllib.parse import unquote


php = Path("wp-content/themes/generatepress-envitechal/inc/legacy-redirects.php").read_text(encoding="utf-8")
monitor = Path("scripts/check-ai-visibility-live.sh").read_text(encoding="utf-8")

map_match = re.search(
    r"function eta_modern_legacy_redirect_map\(\)\s*\{\s*return \[(?P<body>.*?)^\s*\];",
    php,
    re.MULTILINE | re.DOTALL,
)
if map_match is None:
    raise SystemExit("legacy redirect map could not be parsed")

redirect_map = dict(
    re.findall(r"^\s*'([^']+)'\s*=>\s*'([^']+)',\s*$", map_match.group("body"), re.MULTILINE)
)
if not redirect_map:
    raise SystemExit("legacy redirect map is empty")

pairs_match = re.search(
    r"^legacy_redirect_pairs=\(\n(?P<body>.*?)^\)$",
    monitor,
    re.MULTILINE | re.DOTALL,
)
if pairs_match is None:
    raise SystemExit("legacy_redirect_pairs could not be parsed")

monitor_pairs = {}
for source, target in re.findall(r"^\s*'([^'|]+)\|([^']+)'\s*$", pairs_match.group("body"), re.MULTILINE):
    normalized_source = unquote(source)
    if normalized_source in monitor_pairs:
        raise SystemExit(f"duplicate monitored redirect source: {normalized_source}")
    monitor_pairs[normalized_source] = target

if monitor_pairs != redirect_map:
    raise SystemExit(
        "live redirect checks must exactly match the PHP redirect map; "
        f"missing_or_changed={redirect_map.items() - monitor_pairs.items()!r}, "
        f"unexpected={monitor_pairs.items() - redirect_map.items()!r}"
    )

sitemap_match = re.search(
    r"^for legacy_sitemap_path in \\\n(?P<body>.*?); do$",
    monitor,
    re.MULTILINE | re.DOTALL,
)
if sitemap_match is None:
    raise SystemExit("legacy sitemap exclusion loop could not be parsed")

sitemap_sources = {
    unquote(source)
    for source in re.findall(r"'([^']+)'", sitemap_match.group("body"))
}
missing_sitemap_sources = set(redirect_map) - sitemap_sources
if missing_sitemap_sources:
    raise SystemExit(f"live sitemap checks are missing redirect sources: {sorted(missing_sitemap_sources)!r}")

print(f"Live monitor covers all {len(redirect_map)} redirect-map sources.")
