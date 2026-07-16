# AI visibility remediation, staging validation, and production promotion

This change set is designed for staging first. It must not be copied directly to the production document root without a staging smoke test and a current backup.

## What this phase fixes

- Removes the undefined analytical-service renderer that caused the service-page failure.
- Replaces the crawl-visible failed assistant panel with an accurately labelled WhatsApp link, removes the exposed agent credential from current source, and filters the confirmed legacy raw chatbot snippet from final front-end HTML as a defense in depth.
- Publishes a stable Organization, WebSite, Karachi branch, and Lahore branch schema graph with canonical IDs.
- Corrects the LinkedIn entity URL and adds verified Instagram and YouTube profiles.
- Adds 301 consolidation for duplicate credential and knowledge-hub URLs.
- Makes the reviewed Git redirect map authoritative before older Rank Math database redirect records can run.
- Removes redirected legacy URLs from Rank Math XML sitemaps and disables Rank Math's transient sitemap cache so redirect-map changes are reflected immediately at the application layer.
- Removes the duplicated full inline stylesheet and the theme's forced global jQuery enqueue.
- Adds direct issuer evidence and location/method limits for accreditation claims.
- Removes theme-level content negotiation and keeps any upstream Markdown representation isolated from the shared HTML cache.
- Provides reviewed `llms.txt` and `llms-full.txt` files for the webroot.
- Improves keyboard focus visibility and repeated-link accessible names.
- Aligns staging HTML robots meta with the staging-wide `X-Robots-Tag`, and gives virtual `robots.txt` a short edge TTL plus a LiteSpeed no-cache directive.
- Keeps the responsive homepage LCP hero eager and same-origin by excluding that one image from ShortPixel Adaptive Images rewriting; its preload and 520/900/1500 WebP candidates stay on the same URL contract.
- Canonicalizes reviewed legacy internal links at render time in classic menus, generated post/page/custom-post permalinks, post content, excerpts, widget text, and block output. External/lookalike hosts, non-default ports, relative URLs, and non-HTTP schemes are unchanged; query strings and fragments are preserved, and no database value is rewritten.
- Adds a contextual Karachi laboratory pathway on the homepage and related service, location, credential, and verification pathways on the national FAQ page.

## Required security action

The old DigitalOcean assistant credential and agent/chatbot identifiers were embedded in publicly delivered JavaScript. Removing or filtering the snippet is not sufficient: revoke or rotate them at the provider before production deployment. The upstream source of the raw snippet must also be removed from its plugin, code-snippet configuration, or WordPress database record, followed by a cache purge. Old values may remain in Git history and caches, so they must be considered compromised.

The theme's response filter is deliberately narrow: it removes only complete script tags carrying the confirmed agent/chatbot identifier attributes or a source ending in `/static/chatbot/widget.js`. It removes an immediately adjacent raw style block only when that block contains the distinctive `.chatbot-button` selector. It does not run for admin, AJAX, REST, feeds, the `llms` discovery endpoints, or non-HTML responses, and it does not replace upstream removal and credential revocation.

## Staging deployment

The repository includes a fail-closed staging script. It obtains both document roots from cPanel, rejects symlinked or overlapping staging/production paths, refuses a dirty repository, backs up and verifies the current staging theme, pulls `main`, verifies that the theme still matches the validated PR commit, builds directly from that pinned Git tree, normalizes the public theme tree to `0755` directories and `0644` files, lints the exact replacement, then performs an atomic directory swap. After verification it asks LiteSpeed to purge application caches through WP-CLI's `do_action("litespeed_purge_all")` hook; when the installed cache integration is connected to Cloudflare, that action may also notify Cloudflare. The previous tree is retained as a verified private backup archive outside the webroot.

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

The rollback verifies the saved archive, restores the full previous theme directory with public `0755` directory and `0644` file permissions, and retains the failed version in a private archive for diagnosis.

Do not copy the static `llms.txt` files to a publicly reachable staging host until the web server or CDN protects every static response with authentication or `X-Robots-Tag: noindex, nofollow, noarchive`. The theme provides virtual versions for staging tests. Static files bypass WordPress's staging-header hook.

## Staging checks

The deployment script already performs PHP lint before and after copying and requests an application-cache purge, which may also notify the configured Cloudflare integration. If it prints a WP-CLI or purge-hook warning, purge the staging application's caches manually before continuing, taking care not to alter production configuration. Then verify the canonical URLs without a cache-busting query string:

```bash
curl -fsS -o /dev/null -w '%{http_code}\n' "https://staging.envitechal.com/services/analytical-lab-services/"
curl -fsSI "https://staging.envitechal.com/llms.txt"
curl -fsSI "https://staging.envitechal.com/llms-full.txt"
curl -fsSI -H 'Accept: text/markdown' "https://staging.envitechal.com/services/water-testing-lab-services/"
curl -fsSI "https://staging.envitechal.com/certificates-approvals/"
curl -fsSI "https://staging.envitechal.com/newsupdates/"
```

Expected results:

- analytical service: HTTP 200 with the controlled scope block;
- `llms.txt` and `llms-full.txt`: HTTP 200 and `text/plain`;
- an ordinary WordPress page either remains HTML for `Accept: text/markdown` or returns a controlled Markdown representation with `Vary: Accept` and private/no-store cache controls;
- duplicate URLs: HTTP 301 to their canonical destinations;
- redirected legacy URLs are absent from `post-sitemap.xml` after the sitemap/application cache purge;
- no failed-assistant prose or assistant iframe in page source;
- only one external `eta-modern.css` delivery;
- JSON-LD contains `#organization`, `#website`, `#karachi-lab`, and `#lahore-lab`.
- every staging response includes an effective `noindex` directive; the edge challenge must not replace it with an indexable response;
- Rank Math may intentionally omit HTML canonicals on staging after the staging-only `noindex` filter applies; verify the theme/schema URL remains self-consistent there and confirm exact self-canonicals again on production, where the filter is inactive;
- `/accreditations-certifications/` exists before the old credentials URL is accepted as a successful redirect.

The theme deliberately does not negotiate Markdown and does not add `Vary: Accept`. Production currently has an upstream WordPress/hosting Markdown representation: it emits `Vary: Accept`, `private`, `no-store`, and `no-cache`, and an HTML → Markdown → HTML test returned byte-identical HTML before and after the Markdown response. Keep `/llms.txt` and `/llms-full.txt` as the stable AI discovery endpoints and monitor the upstream representation so shared caches never mix the two bodies.

Rank Math's transient sitemap cache is disabled through its documented `rank_math/sitemap/enable_caching` filter because the redirect map is version-controlled and a stale sitemap can otherwise continue advertising redirected URLs after deployment. LiteSpeed and edge caches are separate layers: keep sitemap XML/XSL paths excluded from full-page caching where possible, and purge configured application/edge caches after each release.

After staging approval, use the separate production transaction below. Do not reuse the staging command by changing its hostname.

If Cloudflare or another edge worker generates `llms.txt`, update or disable that rule as part of production deployment; origin theme code cannot override a response generated at the edge.

## Discovery cache-header transaction

The hosting-wide text-file expiry policy can otherwise give `robots.txt`, `llms.txt`, `llms-full.txt`, and `/.well-known/agent-skills/index.json` a one-year browser TTL. After the reviewed theme is deployed to staging, run the separate, narrowly scoped `.htaccess` transaction:

```bash
REPO="$HOME/repositories/envitechal-wordpres"
DISCOVERY_CACHE_TARGET=staging \
CONFIRM_STAGING_DISCOVERY_CACHE=staging.envitechal.com \
  bash "$REPO/scripts/remediate-discovery-cache-headers.sh"
```

The script independently resolves production and staging through cPanel, rejects equal, nested, symlinked, or inode-identical roots, requires a clean local `main` exactly equal to `origin/main`, and makes a verified private backup. It appends one marked, exact-`Request_URI` header block after hosting-wide directives. Only the four reviewed discovery paths receive one `Cache-Control: public, max-age=300, s-maxage=3600, must-revalidate` field and no `Expires` field. Homepage and theme-asset negative controls prove that this policy does not leak to ordinary URLs. Canonical and cache-busted GET and HEAD requests must all pass status, MIME, body, challenge, and cache-header checks before commit.

A staging success writes a 24-hour private attestation bound to the repository commit and SHA-256 digests of the remediation script and both validators. Production refuses any different or stale evidence and rechecks staging immediately before mutation. Then run:

```bash
DISCOVERY_CACHE_TARGET=production \
CONFIRM_PRODUCTION_DISCOVERY_CACHE=envitechal.com \
  bash "$REPO/scripts/remediate-discovery-cache-headers.sh"
```

On production only, the transaction removes either all nine reviewed duplicate `.htaccess` redirect lines or none; partial, altered, duplicate, or `RewriteCond`-governed rules stop the run. All 27 legacy sources must then return GET and HEAD 301 responses with `X-Redirect-By: Envi Tech AL`, and every unique canonical target must return a direct 200. Any failure automatically restores the exact previous `.htaccess`, purges again, and leaves the private recovery set for inspection. The firewall/WAF is never disabled or weakened.

For a deliberate rollback after a committed run, copy the exact `Recovery set:` path printed by that run:

```bash
DISCOVERY_CACHE_RECOVERY_SET="$HOME/backups/envitechal-ai-visibility/PASTE-EXACT-PRINTED-DIRECTORY" \
CONFIRM_DISCOVERY_CACHE_ROLLBACK=envitechal.com \
  bash "$REPO/scripts/rollback-discovery-cache-headers.sh"
```

Use `staging.envitechal.com` as the confirmation only when rolling back a staging recovery set. Rollback verifies the saved manifest and metadata, stops if active `.htaccess` has drifted from the recorded candidate digest, preserves the removed version, restores the exact prior present/absent state with a same-directory rename, purges through the official LiteSpeed hook, and rechecks public availability. Never substitute a different backup directory or bypass a drift failure.

## Production promotion

Production promotion is deliberately separate from staging. `scripts/deploy-production.sh` discovers both cPanel document roots through UAPI, rejects symlinked, identical, nested, or inode-identical roots and theme directories, refuses a dirty repository, and archives the exact theme plus the reviewed `deploy/public_html/llms.txt` and `llms-full.txt` from the hard-coded validated commit.

The production transaction will stop unless the deployed staging theme tree digest is exactly the same as the prepared pinned theme tree. This is a promotion gate, not merely a Git comparison: after any correction, deploy that corrected candidate to staging and complete the public staging checks before updating the production pin. The production pin must never be changed just to make the gate pass.

Before changing a public path, the script creates a timestamped private recovery set under `$HOME/backups/envitechal-ai-visibility`, verifies its manifest, and records whether each discovery file was previously present or absent. It normalizes theme directories to `0755`, theme files to `0644`, and discovery files to `0644`; lints and hashes the prepared tree; and atomically renames the theme and both discovery files into place. Any error or signal before all post-swap lint, path, permission, and digest checks pass restores every prior path automatically. The private recovery marker is outside the webroot.

Production prerequisites:

- the corrected pinned theme is deployed to staging and its canonical, non-cache-busted URLs have passed the staging checks;
- the embedded DigitalOcean credential has been revoked or rotated, and the upstream raw snippet has been removed where possible;
- the commit in `VALIDATED_PRODUCTION_COMMIT` is the reviewed candidate that passed those checks;
- the production `/accreditations-certifications/` page is published. This target has already been confirmed on production; its absence from the current staging database is a staging-data parity issue, not evidence that the production target is missing;
- no unrelated WordPress, plugin, content, or infrastructure change is bundled into this promotion.

From cPanel Terminal:

```bash
REPO="$HOME/repositories/envitechal-wordpres"
git -C "$REPO" fetch origin --prune
git -C "$REPO" switch main
git -C "$REPO" pull --ff-only origin main
CONFIRM_PRODUCTION_DEPLOY=envitechal.com bash "$REPO/scripts/deploy-production.sh"
```

Do not change the confirmation value, hostname variables, or document-root logic. A successful run ends with `PRODUCTION DEPLOYMENT COMMITTED` and prints all three replacement digests plus the private recovery-set path. The script requests a LiteSpeed purge through WordPress's official `do_action("litespeed_purge_all")` hook when WP-CLI is available. If WP-CLI or the hook fails, the verified files remain deployed and the output clearly requires a manual application-cache purge.

If post-deployment checks fail, restore the exact prior theme and the prior presence/content of both discovery files:

```bash
CONFIRM_PRODUCTION_ROLLBACK=envitechal.com \
  bash "$HOME/repositories/envitechal-wordpres/scripts/rollback-production.sh"
```

Rollback verifies the saved manifest, prepares and lints the prior theme, preserves the version being removed in a second private recovery set, restores or removes each discovery file according to the recorded pre-deployment state, then verifies the restored digests and permissions before committing.

## Production checks and edge work

Purge the external CDN/edge cache separately after the script finishes; a WordPress LiteSpeed action cannot purge or reconfigure an upstream WAF. Then check the canonical URLs without cache-busting query strings:

```bash
curl -fsS https://envitechal.com/ | grep -F 'Environmental testing and compliance support'
curl -fsSI https://envitechal.com/llms.txt
curl -fsSI https://envitechal.com/llms-full.txt
curl -fsSI https://envitechal.com/certificates-approvals/
curl -fsS https://envitechal.com/accreditations-certifications/ | grep -F '<h1'
curl -fsS https://envitechal.com/services/analytical-lab-services/ | grep -F 'PNAC LAB-347'
```

Confirm that the discovery files return origin `text/plain` content, the legacy credentials URL redirects to the existing canonical production page, any `Accept: text/markdown` response is private/no-store and cannot alter the following HTML response, no legacy chatbot script or identifier attributes are present, the staging schema checks remain true, and production emits exact self-canonicals.

Also confirm that `img.eta-home-bg-img` retains `data-spai-excluded="true"`, `loading="eager"`, `fetchpriority="high"`, dimensions, `sizes="100vw"`, and all three same-origin hero candidates. Its browser `currentSrc` must remain on `envitechal.com`; no ShortPixel placeholder, lazy marker, or `cdn.shortpixel.ai` hero request should appear after the cache purge.

The static discovery files may be safely installed at the origin by this transaction, but they do not create AI visibility while the edge replaces them with challenge HTML. Keep the firewall enabled and ask A2 Hosting or the edge provider to exempt verified search crawlers and the public discovery resources from JavaScript-only verification. Retest Googlebot-like, Bingbot-like, GPTBot-like, ordinary browser, `HEAD`, and `Accept: text/markdown` requests after the rule and edge-cache purge. Do not report the AI visibility remediation as complete until those requests reach the intended origin responses.

## Edge and staging prerequisites

Fresh crawler-like requests were initially served a JavaScript verification page before reaching WordPress. After the production release and external cache purge, cookie-free ordinary, Googlebot, Bingbot, GPTBot, and OAI-SearchBot GET/HEAD checks returned the intended origin responses on 16 July 2026. Treat that result as monitored state, not a permanent assumption: the public edge can regress independently of the theme. User-Agent simulation can detect differential treatment and challenges, but it is not proof of a provider-verified crawler identity; WAF allow rules must use the provider's documented IP/DNS verification mechanism rather than trusting the User-Agent string.

The scheduled `Live AI visibility` workflow runs `scripts/check-ai-visibility-live.sh` on the first day of every month and can also be dispatched manually. It checks the homepage, robots, sitemap, both LLMS files, crawler-like GET/HEAD behavior, Markdown cache isolation, the legacy credentials redirect, and challenge/chatbot markers. Any recurrence of 403, 415, 429, 5xx, challenge HTML, unsafe cache controls, cross-representation contamination, or an incorrect content type requires immediate edge-provider review. Keep the firewall enabled; any exception must use provider-verified crawler identity rather than trusting User-Agent alone.

The current staging database must also contain the canonical `/accreditations-certifications/` page. A redirect to a staging 404 is not a successful test. Do not update WordPress core or plugins during the theme test because unrelated changes would make the result harder to isolate.

If the staging database is missing the theme-rendered credentials, Karachi laboratory, or consolidated FAQ records, run `bash "$REPO/scripts/prepare-staging-ai-page-parity.sh"` before redeploying staging. The helper fails closed unless cPanel confirms separate staging/production roots and WordPress confirms separate database/prefix identities. It compares a runtime SHA-256 fingerprint of each WordPress database name plus table prefix, without printing either value; ordinary plugins and the active theme are skipped while calculating the fingerprint. A private lock prevents concurrent runs; page, trash, auto-draft, and root-attachment slug collisions stop the run; and any page records created by a failed run are force-deleted before exit. The exact three-slug allowlist is enforced by CI. Their reviewed visible content comes from the theme, so the helper creates only missing empty staging page records and verifies that each public staging URL resolves to the expected page ID.

## Evidence limits that remain intentional

- PNAC LAB-347 is for the Lahore laboratory and only the water/wastewater methods listed in the official scope.
- No current Karachi PNAC claim should be published until a current issuer document is supplied.
- The published Sindh EPA document remains labelled for current-status confirmation.
- No named expert or Person schema should be invented. Add one only after the business supplies the person's name, role, credentials, biography, profile photo, and approval to publish.
- WordPress database articles with contradictory regulatory timelines need a reviewed content migration or admin edit; theme code alone cannot safely decide the authoritative replacement text.
