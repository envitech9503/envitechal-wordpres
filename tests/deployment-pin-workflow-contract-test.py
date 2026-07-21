from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
WORKFLOW = (ROOT / ".github/workflows/theme-quality.yml").read_text(encoding="utf-8")
PRODUCTION = (ROOT / "scripts/deploy-production.sh").read_text(encoding="utf-8")


def require(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)


require(
    'if [[ "$staging_pin" != "$production_pin" ]]' not in WORKFLOW,
    "CI must allow a reviewed staging candidate to lead the last production-approved pin",
)
require(
    'git -C "$REPO" diff --quiet "$VALIDATED_PRODUCTION_COMMIT" HEAD --' in PRODUCTION,
    "production must refuse a payload that differs from its approved pin",
)
require(
    '[[ "$STAGING_THEME_DIGEST" == "$PREPARED_THEME_DIGEST" ]]' in PRODUCTION,
    "production must require the exact theme tree already deployed to staging",
)

print("Deployment pin workflow contract tests passed.")
