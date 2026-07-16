#!/usr/bin/env python3

"""Keep the LCP hero and its responsive preload on one proven URL contract."""

from pathlib import Path
import re


front_page = Path("wp-content/themes/generatepress-envitechal/front-page.php").read_text(
    encoding="utf-8"
)
functions = Path("wp-content/themes/generatepress-envitechal/functions.php").read_text(
    encoding="utf-8"
)

hero_match = re.search(
    r'<img\s+class="eta-home-bg-img"(?P<body>.*?)decoding="async">',
    front_page,
    re.DOTALL,
)
if hero_match is None:
    raise SystemExit("the homepage hero image contract could not be located")

hero = hero_match.group(0)
required_hero_markers = [
    'data-spai-excluded="true"',
    'sizes="100vw"',
    'width="1500"',
    'height="844"',
    'loading="eager"',
    'fetchpriority="high"',
    'decoding="async"',
]
for marker in required_hero_markers:
    if marker not in hero:
        raise SystemExit(f"homepage hero is missing required marker: {marker}")

for forbidden in [
    'loading="lazy"',
    'cdn.shortpixel.ai',
    'data-spai="',
    'data-spai-loading',
]:
    if forbidden in hero:
        raise SystemExit(f"homepage hero contains forbidden runtime marker: {forbidden}")

preload_match = re.search(
    r"add_action\('wp_head', function \(\) \{.*?"
    r"if \(is_front_page\(\)\) \{(?P<body>.*?)\n\s*\}\n\n\s*\$description",
    functions,
    re.DOTALL,
)
if preload_match is None:
    raise SystemExit("the front-page hero preload contract could not be located")

preload = preload_match.group("body")
for marker in [
    'rel="preload" as="image"',
    'imagesrcset="%2$s 520w, %3$s 900w, %4$s 1500w"',
    'imagesizes="100vw"',
]:
    if marker not in preload:
        raise SystemExit(f"homepage hero preload is missing required marker: {marker}")

asset_pattern = r"eta-home-hero-(?:520|900|1500)\.webp"
hero_assets = set(re.findall(asset_pattern, hero))
preload_assets = set(re.findall(asset_pattern, preload))
expected_assets = {
    "eta-home-hero-520.webp",
    "eta-home-hero-900.webp",
    "eta-home-hero-1500.webp",
}
if hero_assets != expected_assets:
    raise SystemExit(f"homepage hero candidates differ from the reviewed set: {hero_assets!r}")
if preload_assets != expected_assets:
    raise SystemExit(f"homepage preload candidates differ from the hero set: {preload_assets!r}")

print("Homepage hero and responsive preload contract tests passed.")
