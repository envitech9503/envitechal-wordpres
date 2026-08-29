#!/usr/bin/env python3

"""Guard the scrollytelling homepage hero (ets-twin) contract.

The homepage hero is a scroll-driven WebGL/GSAP stage. This test pins the
invariants that keep it fast, accessible, and resilient:

- the hero section, headline, and primary CTA are server-rendered HTML
  (no JS required for the H1 or the quote pathway);
- the stage scripts load with the LiteSpeed/optimizer opt-out attributes
  that keep the module script from being rewritten or deferred twice;
- both stage scripts respect prefers-reduced-motion;
- no lazy-loaded image sits inside the hero (its visuals are canvas-drawn).
"""

from pathlib import Path
import re


ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "wp-content/themes/generatepress-envitechal"

front_page = (THEME / "front-page.php").read_text(encoding="utf-8")

hero_start = front_page.index('id="ets-twin"')
hero_end = front_page.index("</section>", front_page.index("ets-cue", hero_start))
hero = front_page[hero_start:hero_end]

if '<h1 class="ets-h1"' not in hero:
    raise SystemExit("the homepage hero headline must be server-rendered inside ets-twin")
if 'href="/contact-us-envi-tech-al/"' not in hero:
    raise SystemExit("the hero primary CTA must link to the contact page")
if '<canvas class="ets-gl" aria-hidden="true">' not in hero:
    raise SystemExit("the hero canvas must stay aria-hidden for assistive tech")
if 'loading="lazy"' in hero:
    raise SystemExit("no lazy-loaded image may sit inside the above-the-fold hero")

for script_name in ("eta-hero-twin.js", "eta-home-body.js"):
    name_at = front_page.find(script_name)
    if name_at < 0:
        raise SystemExit(f"front-page.php must load {script_name}")
    tag_start = front_page.rindex("<script", 0, name_at)
    tag_end = front_page.index(">", name_at)
    tag = front_page[tag_start:tag_end]
    for attr in ('data-no-optimize="1"', 'data-litespeed-noopt="1"'):
        if attr not in tag:
            raise SystemExit(f"{script_name} must keep the optimizer opt-out attribute {attr}")

    script_source = (THEME / script_name).read_text(encoding="utf-8")
    if "prefers-reduced-motion" not in script_source:
        raise SystemExit(f"{script_name} must respect prefers-reduced-motion")

print("Homepage scrollytelling hero contract tests passed.")
