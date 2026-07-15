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

## Staging deployment outline

From the cPanel repository clone, replace `STAGING_ROOT` with the staging site's real document root:

```bash
REPO="$HOME/repositories/envitechal-wordpres"
STAGING_ROOT="/home/envitechal/REPLACE_WITH_STAGING_DOCUMENT_ROOT"
THEME_REL="wp-content/themes/generatepress-envitechal"

test -d "$REPO/.git"
test -f "$STAGING_ROOT/wp-config.php"
test -d "$STAGING_ROOT/$THEME_REL"

mkdir -p "$HOME/backups/envitechal-ai-visibility"
tar -czf "$HOME/backups/envitechal-ai-visibility/theme-before-$(date +%Y%m%d-%H%M%S).tar.gz" \
  -C "$STAGING_ROOT" "$THEME_REL"

rsync -a "$REPO/$THEME_REL/" "$STAGING_ROOT/$THEME_REL/"
```

Do not use `rsync --delete` on the first staging deployment.

Do not copy the static `llms.txt` files to a publicly reachable staging host until the web server or CDN protects every static response with authentication or `X-Robots-Tag: noindex, nofollow, noarchive`. The theme provides virtual versions for staging tests. Static files bypass WordPress's staging-header hook.

## Staging checks

Run PHP lint first:

```bash
find "$STAGING_ROOT/wp-content/themes/generatepress-envitechal" -type f -name '*.php' -print0 \
  | xargs -0 -n1 php -l
```

Then clear only the staging application/CDN cache and verify:

```bash
curl -fsS -o /dev/null -w '%{http_code}\n' "https://STAGING_HOST/services/analytical-lab-services/"
curl -fsSI "https://STAGING_HOST/llms.txt"
curl -fsS -H 'Accept: text/markdown' "https://STAGING_HOST/services/water-testing-lab-services/" | head -80
curl -fsSI "https://STAGING_HOST/certificates-approvals/"
curl -fsSI "https://STAGING_HOST/newsupdates/"
```

Expected results:

- analytical service: HTTP 200 with the controlled scope block;
- `llms.txt`: HTTP 200 and `text/plain`;
- Markdown request: HTTP 200 and `text/markdown`;
- duplicate URLs: HTTP 301 to their canonical destinations;
- no failed-assistant prose or assistant iframe in page source;
- only one external `eta-modern.css` delivery;
- JSON-LD contains `#organization`, `#website`, `#karachi-lab`, and `#lahore-lab`.

Also request Markdown followed by ordinary HTML, then repeat in the opposite order. The HTML response must never contain Markdown and the Markdown response must never contain HTML. Negotiated Markdown is deliberately marked `private, no-store`; keep it that way unless the CDN cache key has been explicitly configured and tested to vary on `Accept`.

After staging approval, copy the reviewed static files to the production webroot so they take precedence over any older copies:

```bash
install -m 0644 "$REPO/deploy/public_html/llms.txt" "$PRODUCTION_ROOT/llms.txt"
install -m 0644 "$REPO/deploy/public_html/llms-full.txt" "$PRODUCTION_ROOT/llms-full.txt"
```

If Cloudflare or another edge worker generates `llms.txt` or Markdown, update or disable that rule as part of production deployment; origin theme code cannot override a response generated at the edge.

## Evidence limits that remain intentional

- PNAC LAB-347 is for the Lahore laboratory and only the water/wastewater methods listed in the official scope.
- No current Karachi PNAC claim should be published until a current issuer document is supplied.
- The published Sindh EPA document remains labelled for current-status confirmation.
- No named expert or Person schema should be invented. Add one only after the business supplies the person's name, role, credentials, biography, profile photo, and approval to publish.
- WordPress database articles with contradictory regulatory timelines need a reviewed content migration or admin edit; theme code alone cannot safely decide the authoritative replacement text.
