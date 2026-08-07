# AI Visibility Execution Report

Completed: 8 August 2026

## Outcome

The approved AI visibility programme was implemented without changing CSS,
JavaScript, images, fonts, layout, navigation, forms, visual components or
responsive behaviour. The production release uses pinned payload commit
`efe6c14e3cb80c47872516ec27e837a3081d4fe0`.

## Delivered controls

- Credential truth is governed by issuer evidence, document hashes, Karachi
  LAB-285, Lahore LAB-347, and explicit LAB-284/branch/scope negative controls.
- The pre-release crawl recorded 80 indexable URLs, 280 claim occurrences and 23
  frontend file hashes.
- Every reviewed current or historical URL has a disposition: 34 authority URLs,
  12 supporting URLs and 66 one-hop redirects across 112 register entries.
- The post-release sitemap contains 46 direct 200/self-canonical URLs. It has zero
  noindex leaks and zero rendered schema-contract failures.
- Thirty-four canonical intent records define one authority URL and supporting
  roles for organization, credentials, locations, services and guidance.
- Schema contracts enforce stable Organization/branch IDs, branch-specific
  credentials, canonical agreement and evidence-backed properties only.
- The AI discovery generator can read the approved staging origin while emitting
  production canonical URLs; it fails if a staging hostname remains.
- Unpublished expert, case-study, dataset and technical-visual packs are available
  as explicit HOLD structures. No person, client result or dataset was invented.
- External profile descriptions and a platform consistency register are copy-ready
  for authorized account owners. No unauthorized third-party mutation was made.
- A privacy-minimized cPanel log pipeline produced aggregate-only measurement with
  IPs, query strings, referrer paths and full user agents discarded.
- A private static dashboard generator now turns only that aggregate output into
  status, source, path, discovery-resource and anomaly panels. It rejects unknown
  columns and non-minimized paths and never installs a browser tag or public page.
- A machine-readable credential review schedule now monitors all six controlled
  credentials, including accelerated issuer-confirmation checks for SEPA, QMS and
  EMS. A deterministic checker fails closed on expired records.
- A single operations register distinguishes `COMPLETE`, `READY_ACCOUNT_ACTION`
  and `BLOCKED_EXTERNAL_EVIDENCE`, with an exact authority, evidence set and next
  action for every original priority-list item.

## Release evidence

- Pull requests #48 through #53 passed GitHub `php-and-safety` checks and were
  merged. PR #52 records the first post-release audit; PR #53 contains the final
  assistant precision payload.
- The complete repository-local validation suite passed after each implementation
  tranche.
- The guarded staging transaction created verified private recovery archives,
  deployed the pinned theme and purged application/edge caches.
- Staging QA passed all 66 redirect sources, 18 unique direct targets, five critical
  claim/schema pages, seven discovery-source page records and staging noindex.
- The guarded production transaction verified the recovery manifest and atomically
  activated the pinned theme plus `robots.txt`, `llms.txt` and `llms-full.txt`.
- Production theme digest:
  `3dac02a28b7848d246f0fd2410eada4c193723b3683c4410e3a7d13c0b421275`.
- Production discovery digests:
  `cff0d0101534e50fbade9f0749c8bb7dc90081a399b3b0555db41bfe0206aea0`
  (`robots.txt`),
  `d3a09bb471d0d0e724c0b6b72d501ecd782713f5d6c1d570c6c7580122de90f6`
  (`llms.txt`), and
  `f1ba559f7e768b1a768034ff61bee861dafc8b6433781af0680ae5464cdb904b`
  (`llms-full.txt`).
- The first immediate post-purge monitor saw one HTML byte-difference while the
  edge repopulated. The unchanged second run passed byte-identical HTML/Markdown
  cache isolation and the complete live suite with no 403, 415, 429, 5xx or
  challenge response.
- The final assistant release passed 20 live customer scenarios with 19 unique
  answers in verified mode. It passed English, Urdu, Roman Urdu, LAB-284 and
  ballast-water scope traps, price/turnaround refusal, report verification,
  safety/off-topic handling and explicit prompt-injection refusal.
- The final immutable crawl is in `docs/audits/2026-08-08-final/`: 46 direct
  200/self-canonical sitemap URLs, zero sitemap noindex leaks, zero rendered
  schema failures and 190 claim occurrences.

## Measurement snapshot

The private cPanel aggregate run classified 2,290 AI/search referral or declared
crawler requests into 207 thresholded rows, deliberately excluded 17,853 ordinary
requests, and rejected zero malformed lines. Declared user agents remain labelled
unverified until provider identity verification is performed. Raw logs and the
aggregate output remain outside Git in the restricted hosting account.

## Intentional HOLD/account-owner work

The architecture is complete, but evidence-dependent publications remain HOLD by
design: named expert profiles, case studies, original datasets and new technical
visuals require real source records, consent and item-specific approval. Google
Business, Bing Places and social-profile edits require the relevant account owner;
approved text and a correction queue are prepared. These are evidence/authority
constraints, not unfinished code or permission to fabricate content.

The external-profile and Lahore work is now owner-ready under
`AI_VISIBILITY_ACCOUNT_OWNER_RUNBOOK.md`; execution still requires the genuine
platform owner. Evidence-dependent publishing remains blocked until real evidence
is supplied. The only item that may require a frontend decision is placement of a
future approved technical visual, and that remains explicitly permission-gated.
