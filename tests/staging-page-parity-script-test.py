#!/usr/bin/env python3

"""Static safety contract for the narrowly scoped staging page helper."""

from pathlib import Path
import re


script = Path("scripts/prepare-staging-ai-page-parity.sh").read_text(encoding="utf-8")

expected_specs = [
    "accreditations-certifications|Accreditations & Certifications",
    "karachi-environmental-lab|Karachi Environmental Laboratory",
    "environmental-testing-faqs-pakistan|Environmental Testing FAQs Pakistan",
]

matches = list(re.finditer(r"^PAGE_SPECS=\(\n(?P<body>.*?)^\)$", script, re.MULTILINE | re.DOTALL))
if len(matches) != 1:
    raise SystemExit("PAGE_SPECS could not be parsed")
match = matches[0]

body_lines = [line for line in match.group("body").splitlines() if line.strip()]
parsed_lines = [re.fullmatch(r'\s*"([^"]+)"\s*', line) for line in body_lines]
if any(parsed is None for parsed in parsed_lines):
    raise SystemExit("every PAGE_SPECS entry must be one quoted literal on its own line")

actual_specs = [parsed.group(1) for parsed in parsed_lines if parsed is not None]
if actual_specs != expected_specs:
    raise SystemExit(f"PAGE_SPECS must be the exact reviewed allowlist: {expected_specs!r}; got {actual_specs!r}")

assignment_pattern = re.compile(
    r"^\s*(?:declare\s+(?:-[aA]\s+)?|readonly\s+)?PAGE_SPECS\s*(?:\+?=|\[[^]]+\]\s*=)",
    re.MULTILINE,
)
assignments = assignment_pattern.findall(script)
if len(assignments) != 1:
    raise SystemExit("PAGE_SPECS must have exactly one assignment and no later mutation")

required_safety_markers = [
    "command -v flock",
    'ensure_private_directory "$BACKUP_ROOT"',
    'exec 9<"$LOCK_DIR"',
    "flock -n 9",
    "post_status=auto-draft",
    "post_type=attachment",
    "post_status=inherit",
    "wordpress_database_identity",
    "--skip-plugins --skip-themes eval",
    '$wpdb->get_var("SELECT DATABASE()")',
    'echo "\\nETA_DB_IDENTITY:" . hash("sha256", $database . "\\0" . $prefix)',
    "sed -nE 's/^ETA_DB_IDENTITY:([0-9a-f]{64})$/\\1/p'",
    '[[ "$identity" =~ ^[0-9a-f]{64}$ ]]',
    "CREATED_IDS",
    'post delete "$created_id" --force',
    "trap cleanup_created_pages EXIT",
    "post url-to-id",
    "COMMITTED=1",
]

missing = [marker for marker in required_safety_markers if marker not in script]
if missing:
    raise SystemExit(f"staging page helper is missing fail-closed safety markers: {missing!r}")

forbidden_portability_markers = ["< <(", "mapfile", "readarray"]
present_forbidden = [marker for marker in forbidden_portability_markers if marker in script]
if present_forbidden:
    raise SystemExit(
        "staging page helper must not require /dev/fd process substitution: "
        f"{present_forbidden!r}"
    )

print("Staging page-parity helper safety contract passed.")
