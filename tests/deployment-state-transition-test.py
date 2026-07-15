#!/usr/bin/env python3

"""Source guard for signal-safe production rename state transitions."""

from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def assert_preceded(path: str, move: str, state: str) -> None:
    lines = (ROOT / path).read_text(encoding="utf-8").splitlines()
    matches = [index for index, line in enumerate(lines) if line.strip() == move]
    if len(matches) != 1:
        raise AssertionError(f"{path}: expected one critical rename {move!r}, found {len(matches)}")

    index = matches[0]
    previous = index - 1
    while previous >= 0 and not lines[previous].strip():
        previous -= 1
    if previous < 0 or lines[previous].strip() != state:
        actual = lines[previous].strip() if previous >= 0 else "<start of file>"
        raise AssertionError(
            f"{path}: {move!r} must be immediately guarded by {state!r}; found {actual!r}"
        )


def assert_recovery_indicators(path: str, required: tuple[str, ...]) -> None:
    source = (ROOT / path).read_text(encoding="utf-8")
    for token in required:
        if token not in source:
            raise AssertionError(f"{path}: missing signal-window recovery indicator {token!r}")


def main() -> None:
    deploy = "scripts/deploy-production.sh"
    rollback = "scripts/rollback-production.sh"

    for move, state in (
        ('mv -- "$TARGET" "$OLD_THEME"', 'THEME_STATE="moving-old"'),
        ('mv -- "$NEW_THEME" "$TARGET"', 'THEME_STATE="moving-new"'),
        ('mv -- "$LLMS_TARGET" "$OLD_LLMS"', 'LLMS_STATE="moving-old"'),
        ('mv -- "$NEW_LLMS" "$LLMS_TARGET"', 'LLMS_STATE="moving-new"'),
        ('mv -- "$LLMS_FULL_TARGET" "$OLD_LLMS_FULL"', 'LLMS_FULL_STATE="moving-old"'),
        ('mv -- "$NEW_LLMS_FULL" "$LLMS_FULL_TARGET"', 'LLMS_FULL_STATE="moving-new"'),
    ):
        assert_preceded(deploy, move, state)

    for move, state in (
        ('mv -- "$TARGET" "$CURRENT_THEME"', 'THEME_STATE="moving-current"'),
        ('mv -- "$RESTORE_THEME" "$TARGET"', 'THEME_STATE="moving-desired"'),
        ('mv -- "$LLMS_TARGET" "$CURRENT_LLMS"', 'LLMS_STATE="moving-current"'),
        ('mv -- "$RESTORE_LLMS" "$LLMS_TARGET"', 'LLMS_STATE="moving-desired"'),
        ('mv -- "$LLMS_FULL_TARGET" "$CURRENT_LLMS_FULL"', 'LLMS_FULL_STATE="moving-current"'),
        ('mv -- "$RESTORE_LLMS_FULL" "$LLMS_FULL_TARGET"', 'LLMS_FULL_STATE="moving-desired"'),
    ):
        assert_preceded(rollback, move, state)

    assert_recovery_indicators(
        deploy,
        (
            'if test -f "$old_swap"; then',
            'if test -d "$old_swap"; then',
            'recover_public_file "$LLMS_FULL_STATE"',
            'recover_theme "$THEME_STATE"',
            "trap '' INT TERM",
        ),
    )
    assert_recovery_indicators(
        rollback,
        (
            'if test -f "$current_swap"; then',
            'if test -d "$current_swap"; then',
            'recover_current_public_file "$LLMS_FULL_STATE"',
            'recover_current_theme "$THEME_STATE"',
            "trap '' INT TERM",
        ),
    )


if __name__ == "__main__":
    main()
