# AI Visibility Measurement Specification

Version: 1.0 — 8 August 2026

This specification uses existing GA4 and server/CDN records only. It does not add
or modify a browser tag, consent flow, frontend event, visual component or live
infrastructure.

## Measurement classes

| Class | Examples | What it can establish | What it cannot establish |
| --- | --- | --- | --- |
| Human AI referral | `chatgpt.com`, `perplexity.ai`, `copilot.microsoft.com`, `gemini.google.com`, `claude.ai` referrer | A session arrived with that referrer | Which answer/citation caused the visit; unattributed/private-app traffic |
| Declared AI crawler | OAI-SearchBot, GPTBot, ChatGPT-User, ClaudeBot, PerplexityBot | The request declared a known agent | Authenticity without verified IP/DNS; citation or model use |
| Verified crawler | Provider identity validation outside this repository | The request origin passed the provider's verification procedure | Human engagement or a model citation |
| Search crawler | Googlebot, Bingbot | Declared/verified discovery activity | Ranking, indexing or citation by itself |
| Monitoring probe | Repository/cPanel health monitor signature | Availability checks | Customer or crawler demand |
| Suspected spoofing | Known bot name without provider verification | An untrusted declaration | Provider identity |

## Privacy-minimized server-log output

Raw logs remain in the controlled hosting account and never enter Git. The
repository analyzer emits aggregates only and must not output IP addresses, full
user-agent strings, cookies, query strings, fragments, request bodies, emails or
client identifiers. Normalize paths by removing query and fragment components.

Aggregate dimensions:

- date in Asia/Karachi;
- traffic class and normalized source family;
- normalized landing/resource path;
- HTTP status family and exact status;
- discovery-resource flag (`/robots.txt`, `/sitemap_index.xml`, child sitemaps,
  `/llms.txt`, `/llms-full.txt`, agent-skills endpoint);
- request count and bytes, where present.

Minimum aggregation threshold for a path row is five requests in shared reports;
lower-volume rows remain in the restricted operational output or roll up to
`OTHER_LOW_VOLUME`. Retain aggregates for 13 months unless policy requires less.

## GA4 definitions

- `AI referral sessions`: sessions whose existing session source/medium host is in
  the controlled referral-host mapping.
- `AI referral landing pages`: normalized landing page with sessions, engaged
  sessions and existing conversions only.
- `AI referral conversion rate`: existing conversions divided by AI referral
  sessions; no new conversion event is inferred or installed.
- Direct/unknown remains direct/unknown. It must not be reclassified as AI traffic.

## Dashboard

Weekly operational panels:

1. verified versus declared/suspected crawler requests;
2. status distribution and 403/415/429/5xx anomalies;
3. discovery-resource availability and cache outcome where logs expose it;
4. top authority landing pages for human AI referrals;
5. sitemap/redirect-source errors and retired-path traffic;
6. month-over-month AI referrals and existing conversions with low-volume caveats.

Alerts: any production noindex, discovery-resource non-200, assistant readiness
failure, redirect chain, repeated 403/415/429/5xx, crawler-class surge above three
times the trailing four-week median, or credential URL approaching review/expiry.

## Access and reproducibility

Restricted operations staff may read raw logs. Analysts receive aggregates; public
or Git artifacts contain definitions and synthetic tests only. Every run records
script version, input period, timezone and aggregation threshold. Bot identity is
reported as verified only after the relevant provider verification mechanism; a
User-Agent string alone is never enough.
