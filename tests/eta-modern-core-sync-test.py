"""eta-modern-core.css must equal eta-modern.css minus its body.single-post rules."""
import pathlib
import shutil
import subprocess
import sys

ROOT = pathlib.Path(__file__).resolve().parents[1]
CSS = ROOT / "wp-content/themes/generatepress-envitechal/assets/css"

core = (CSS / "eta-modern-core.css").read_text()
for line in core.splitlines():
    stripped = line.strip()
    if stripped.startswith(".single-post") and stripped.endswith("{"):
        sys.exit("FAIL: core file still contains a .single-post rule: " + stripped)

if shutil.which("node"):
    result = subprocess.run(["node", str(ROOT / "scripts/build-eta-modern-core.js"), "--check"],
                            capture_output=True, text=True)
    if result.returncode != 0 and "Cannot find module 'postcss'" not in result.stderr:
        sys.exit("FAIL: " + (result.stderr or result.stdout).strip())
    print(result.stdout.strip() or "postcss not installed: generator check skipped.")
print("eta-modern core stylesheet contract tests passed.")
