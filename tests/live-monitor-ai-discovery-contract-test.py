#!/usr/bin/env python3

"""Static contract for the public AI-discovery monitor."""

from pathlib import Path
import subprocess


ROOT = Path(__file__).resolve().parents[1]
MONITOR = ROOT / "scripts/check-ai-visibility-live.sh"


def require(source: str, token: str, label: str) -> None:
    if token not in source:
        raise AssertionError(f"missing {label}: {token!r}")


def main() -> None:
    subprocess.run(["bash", "-n", str(MONITOR)], check=True)
    source = MONITOR.read_text(encoding="utf-8")

    required = {
        "OAI search crawler client": "OAI-SearchBot/1.0",
        "explicit OAI allow check": "robots.txt explicitly permits OAI-SearchBot discovery",
        "explicit GPTBot disallow check": "robots.txt explicitly blocks GPTBot training crawl",
        "current Markdown marker": "Environmental testing and compliance support for teams that need clear, defensible reports.",
        "current LLMS corpus marker": "# Envi Tech AL full AI-readable corpus",
        "agent skills monitoring": "/.well-known/agent-skills/index.json",
        "shared cache validator": "validate-discovery-cache-headers.php",
        "Expires rejection reporting": "exact reviewed short cache policy and no Expires header",
        "bounded 429 retry": "returned 429 on attempt",
        "fresh retry cache key": "eta_live_retry=${attempt}",
    }
    for label, token in required.items():
        require(source, token, label)

    stale_tokens = (
        "# Envi Tech AL | Environmental Testing Lab & Consultancy",
        "## Organization facts",
        'fetch "GPTBot HEAD',
        'fetch \'llms.txt\' "$gptbot_ua"',
    )
    for token in stale_tokens:
        if token in source:
            raise AssertionError(f"stale live-monitor assertion remains: {token!r}")


if __name__ == "__main__":
    main()
