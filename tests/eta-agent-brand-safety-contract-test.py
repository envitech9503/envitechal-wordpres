from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
FUNCTIONS = (ROOT / "wp-content/themes/generatepress-envitechal/functions.php").read_text(encoding="utf-8")
BUNDLE = (ROOT / "wp-content/themes/generatepress-envitechal/assets/js/eta-chatbot.js").read_text(encoding="utf-8")
STYLES = (ROOT / "wp-content/themes/generatepress-envitechal/assets/css/eta-modern.css").read_text(encoding="utf-8")


def require(condition: bool, message: str) -> None:
    if not condition:
        raise AssertionError(message)


root_markup = FUNCTIONS.split('class="eta-chatbot-root"', 1)[1].split('>', 1)[0]
require("hidden" in root_markup, "assistant root must be server-rendered hidden")
require(".eta-chatbot-root[hidden]" in STYLES and "display: none !important" in STYLES, "hidden assistant must not be overridden by theme CSS")
require("function preflight()" in FUNCTIONS, "bootstrap must preflight the first-party health route")
require("!response.ok" in FUNCTIONS, "only a successful health response may reveal the assistant")
require("data.status !== 'ready'" in FUNCTIONS, "preflight must validate the explicit ready response body")
require("root.hidden = false" in FUNCTIONS, "successful preflight must explicitly reveal the launcher")

unavailable = FUNCTIONS.split("function showUnavailable()", 1)[1].split("function preflight()", 1)[0]
require("root.hidden = true" in unavailable, "any bootstrap failure must hide the complete assistant")
require("fallback.hidden = false" not in unavailable, "bootstrap failure must not expose an error panel")

require("root.dataset.etaPreflight !== 'ready'" in BUNDLE, "the interactive bundle must enforce successful preflight")
require("function checkHealth(" not in BUNDLE, "opening the panel must not launch another slow health check")
require("function delay(" not in BUNDLE, "the browser must not retry failed completion requests")
require("sendQuestion(question, 0)" not in BUNDLE, "completion requests must be single-attempt")

agent_php = (ROOT / "wp-content/themes/generatepress-envitechal/inc/eta-agent.php").read_text(encoding="utf-8")
health_handler = agent_php.split("function eta_agent_health_response()", 1)[1].split("function eta_agent_chat_response", 1)[0]
require("eta_agent_verified_catalogue_ready" in health_handler, "health must verify the deterministic source catalogue")
require("eta_agent_remote_completion" not in health_handler, "public readiness must never wait for DigitalOcean inference")
require("if (!eta_agent_remote_enabled())" in agent_php, "generative answers must be explicitly enabled server-side")
require("eta_agent_safe_fallback_response" in agent_php, "unsupported or failed remote answers must fail to a verified response")

print("Agent brand-safety contract tests passed.")
