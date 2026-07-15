# AI visibility remediation and staging deployment

This change set is designed for staging first. It must not be copied directly to the production document root without a staging smoke test and a current backup.

## What this phase fixes

- Removes the undefined analytical-service renderer that caused the service-page failure.
- Replaces the crawl-visible failed assistant panel with an accurately labelled WhatsApp link and removes the exposed agent credential from current source.
- Publishes a stable Organization, WebSite, Karachi branch, and Lahore branch schema graph with canonical IDs.
- Corrects the LinkedIn entity URL and adds verified Instagram and YouTube profiles.
- Adds 301 consolidation for duplicate credential and knowledge-hub URLs.
- Removes the duplicated full inline stylesheet and the theme's forced global jQuery enqueue.
- Adds direct issuer evidence and location/method limits for accreditation claims.
- Adds `text/markdown` content negotiation for public WordPress pages.
- Provides reviewed `llms.txt` and `llms-full.txt` files for the webroot.
- Improves keyboard focus visibility and repeated-link accessible names.

## Required security action

The old assistant credential was embedded in publicly delivered JavaScript. Removing it from current source is not sufficient: revoke or rotate that credential at its provider before production deployment. The old value may also remain in Git history and caches, so it must be considered compromised.

## Staging deployment

The repository includes a fail-closed staging script. It obtains both document roots from cPanel, rejects symlinked or overlapping staging/production paths, refuses a dirty repository, backs up and verifies the current staging theme, pulls `main`, verifies that the theme still matches the validated PR commit, builds directly from that pinned Git tree, lints the exact replacement, then performs an atomic directory swap. The previous tree is retained as a verified backup archive outside the webroot.

From the cPanel Terminal:

```bash
REPO="$HOME/repositories/envitechal-wordpres"
test -d "$REPO/.git"
git -C "$REPO" fetch origin --prune
git -C "$REPO" switch main
git -C "$REPO" pull --ff-only origin main
bash "$REPO/scripts/deploy-staging-theme.sh"
```

If the staging theme must be restored, run:

```bash
bash "$HOME/repositories/envitechal-wordpres/scripts/rollback-staging-theme.sh"
```

The rollback verifies the saved archive, restores the full previous theme directory, and retains the failed version for diagnosis.

Do not copy the static `llms.txt` files to a publicly reachable staging host until the web server or CDN protects every static response with authentication or `X-Robots-Tag: noindex, nofollow, noarchive`. The theme provides virtual versions for staging tests. Static files bypass WordPress's staging-header hook.

## Staging checks

The deployment script already performs PHP lint before and after copying. Clear only the staging application/CDN cache, then verify:

```bash
curl -fsS -o /dev/null -w '%{http_code}\n' "https://staging.envitechal.com/services/analytical-lab-services/"
curl -fsSI "https://staging.envitechal.com/llms.txt"
curl -fsS -H 'Accept: text/markdown' "https://staging.envitechal.com/services/water-testing-lab-services/" | head -80
curl -fsSI "https://staging.envitechal.com/certificates-approvals/"
curl -fsSI "https://staging.envitechal.com/newsupdates/"
```

Expected results:

- analytical service: HTTP 200 with the controlled scope block;
- `llms.txt`: HTTP 200 and `text/plain`;
- Markdown request: HTTP 200 and `text/markdown`;
- duplicate URLs: HTTP 301 to their canonical destinations;
- no failed-assistant prose or assistant iframe in page source;
- only one external `eta-modern.css` delivery;
- JSON-LD contains `#organization`, `#website`, `#karachi-lab`, and `#lahore-lab`.
- every staging response includes an effective `noindex` directive; the edge challenge must not replace it with an indexable response;
- the analytical-service HTML canonical points to its own URL, not the homepage;
- `/accreditations-certifications/` exists before the old credentials URL is accepted as a successful redirect.

Also request Markdown followed by ordinary HTML, then repeat in the opposite order. The HTML response must never contain Markdown and the Markdown response must never contain HTML. Negotiated Markdown is deliberately marked `private, no-store`; keep it that way unless the CDN cache key has been explicitly configured and tested to vary on `Accept`.

After staging approval, use a separate production deployment with a fresh production backup and a pinned merged commit. Do not reuse the staging command by changing its hostname, and do not copy static AI files until the edge/WAF behavior has been corrected and retested.

If Cloudflare or another edge worker generates `llms.txt` or Markdown, update or disable that rule as part of production deployment; origin theme code cannot override a response generated at the edge.

## Edge and staging prerequisites

At the time this remediation was prepared, fresh crawler-like requests were served a JavaScript verification page before reaching WordPress. Googlebot, GPTBot, and `Accept: text/markdown` requests must receive the intended origin response rather than challenge HTML. Ask A2 Hosting to preserve the site firewall while excluding verified search crawlers and the public `/llms.txt` and `/llms-full.txt` resources from JavaScript-only verification.

The current staging database must also contain the canonical `/accreditations-certifications/` page. A redirect to a staging 404 is not a successful test. Do not update WordPress core or plugins during the theme test because unrelated changes would make the result harder to isolate.

## Evidence limits that remain intentional

- PNAC LAB-347 is for the Lahore laboratory and only the water/wastewater methods listed in the official scope.
- No current Karachi PNAC claim should be published until a current issuer document is supplied.
- The published Sindh EPA document remains labelled for current-status confirmation.
- No named expert or Person schema should be invented. Add one only after the business supplies the person's name, role, credentials, biography, profile photo, and approval to publish.
- WordPress database articles with contradictory regulatory timelines need a reviewed content migration or admin edit; theme code alone cannot safely decide the authoritative replacement text.
