# Envi Tech AL AI Visibility Execution Architecture

Status: planning only. This document does not authorize or perform a production change.

## 1. Mandate and non-negotiable boundary

The programme may improve factual accuracy, AI discoverability, entity evidence,
indexation quality, public technical evidence, off-site consistency, and measurement.
It must not redesign or restructure the live website.

Without a separate written approval, the programme must not change:

- CSS, JavaScript, images, fonts, layout, spacing, responsive behaviour, templates,
  menus, navigation, forms, calls to action, or visual components;
- the WordPress theme structure, parent theme, plugins, WordPress core, database
  schema, hosting topology, DigitalOcean resources, CDN, firewall, or WAF policy;
- a public URL, title, body, author, image, schema claim, redirect, canonical, robots
  directive, or indexation state merely because it appears beneficial.

Public expert profiles, case studies, datasets, and technical visuals must first be
prepared as unpublished drafts using existing content structures. Publication is a
separate owner-approval gate. If a required improvement needs a new template,
navigation item, visible component, or layout change, work stops and a narrow visual
change proposal is submitted for approval.

## 2. Authority resolution and remaining scope gate

The supplied action list says Karachi is PNAC **LAB-284**. The current PNAC active
register and the owner-supplied screenshot dated 8 August 2026 establish that this
identifier is incorrect:

- PNAC's active-laboratory listing identifies Envi Tech AL's Bahadurabad, Karachi
  laboratory as **LAB-285**, issued 05-05-2026 and valid until 04-05-2029.
- The same active register identifies Envi Tech AL's Lahore laboratory as
  **LAB-347**, issued 22-09-2025 and valid until 21-09-2028.
- PNAC's LAB-285 accreditation document names Envi Tech AL at 345, First Floor,
  Street 15, Bahadurabad, Karachi.
- PNAC's LAB-284 document names Century Paper & Board Testing Laboratory, not
  Envi Tech AL.

Controlling references:

- <https://pnac.gov.pk/Accredited-Cabs-serve/Testing-and-Calibration-Laboratories/Active>
- <https://pnac.gov.pk/pdfFiles/LAB-285>
- <https://pnac.gov.pk/pdfFiles/LAB-284>

LAB-284 must not be attributed to Envi Tech AL. The identifiers, locations, issue
dates, and validity dates above may form the approved register facts. However, no
specific LAB-285 scope, method, matrix, parameter, or range statement may be changed
until the exact current PNAC scope PDF is captured and approved. That PDF must be
hashed and its following fields recorded in the credential ledger:

- accredited legal entity;
- laboratory address and location;
- accreditation identifier;
- first-grant and current-validity dates;
- standard and edition;
- matrices/materials;
- parameters/properties;
- methods and ranges;
- surveillance/current-status qualification;
- official source URL and evidence-capture date.

If an owner-supplied document conflicts with PNAC's active register, implementation
stops for written PNAC clarification. Marketing text, a certificate image, a search
result, or an old site page cannot overrule the current issuer record.

## 3. Current baseline and implications

The repository currently contains extensive Lahore LAB-347 controls and deliberately
prohibits a Karachi PNAC claim. On the reviewed `origin/main` baseline there are 66
tracked LAB-347 references and no LAB-284 or LAB-285 references. The live assistant
therefore currently answers a Karachi PNAC question with the older safe rule that
accreditation applies only to Lahore.

A read-only production validation run on 8 August 2026 passed:

- ordinary, Googlebot-like, Bingbot-like, and OAI-SearchBot-like access;
- production robots policy and discovery cache headers;
- sitemap index and child sitemaps;
- exclusion of all reviewed legacy redirect sources from sitemaps;
- `llms.txt`, `llms-full.txt`, and the agent-skills JSON endpoint;
- one-hop 301 behaviour for the existing reviewed redirect map;
- HTML/Markdown cache isolation and challenge-page checks.

The assistant health endpoint returned `ready` in verified mode. Consequently:

- P2 crawler directives and the assistant are verification/monitoring tasks unless a
  reproducible regression is found;
- the first implementation priority is credential truth and propagation, not new
  discovery infrastructure or assistant replacement;
- existing passing controls remain frozen while P0 work is performed.

## 4. Governance model

### 4.1 Sources of truth

Evidence is ranked in this order:

1. Current issuer/regulator register and signed scope document.
2. Current company-controlled certificate or approved internal record.
3. Published Envi Tech AL page that cites the controlling evidence.
4. Third-party directory or search-engine description.
5. Historical article, marketing copy, or unsupported statement.

Lower-ranked evidence cannot override higher-ranked evidence.

### 4.2 Claim ledger

Every material public claim receives a ledger record containing:

- claim ID, exact wording, claim class, entity, location, service, and audience;
- controlling evidence, evidence hash, URL, capture date, and owner;
- allowed wording and prohibited extrapolations;
- scope dimensions: location, matrix, parameter, method, range, and validity;
- every publication surface where the claim appears;
- review date, expiry/renewal trigger, and status.

Claim classes include accreditation, certification, regulatory approval, laboratory
capability, geographic availability, price, turnaround, named expertise, client
outcome, methodology, and safety/compliance conclusions.

### 4.3 Change isolation

Each change set must use a new `codex/` branch created from current `origin/main`.
Unrelated work is prohibited. Recommended pull-request sequence:

1. evidence ledger and failing claim-contract tests;
2. P0 credential corrections;
3. legacy-content decisions and redirect/noindex controls;
4. canonical intent map and structured-data corrections;
5. each expert profile, case study, or dataset as its own reviewed content unit;
6. measurement and monitoring changes.

### 4.4 Approval roles

- Business owner: credential truth, expert identity, case-study facts, client consent,
  public datasets, third-party profile text, and publication approval.
- Technical implementer: inventory, code/content implementation, tests, staging,
  backups, and rollback preparation.
- QA reviewer: independent claim-to-source comparison, crawl/indexation validation,
  assistant scenarios, schema validation, and release evidence.

No person should both author and finally approve a high-risk credential claim.

## 5. Step-by-step execution plan

## Phase 0 — Authority lock and immutable baseline

Priority: blocking prerequisite.

1. Record LAB-285 as the current Karachi register identifier and add an explicit
   negative control prohibiting LAB-284 attribution to Envi Tech AL.
2. Obtain current source documents for Lahore LAB-347, Karachi PNAC, Sindh EPA,
   Punjab EPA, ISO 9001, ISO 14001, and every other credential in public use.
3. Capture hashes, dates, exact scope, location, identifier, and approved wording in
   the claim ledger.
4. Record a read-only production baseline: URL inventory, response status, title,
   canonical, robots, schema types/IDs, sitemap membership, internal links, and claim
   text.
5. Capture current CSS, JavaScript, image, template-structure, and navigation hashes
   as the no-frontend-change baseline.
6. Create private, verified file and database recovery sets before any staging write.

Exit criteria:

- the Karachi LAB-285 register record and exact scope PDF are owner-approved;
- every credential has a current controlling source or is marked `unverified`;
- baseline and rollback evidence are complete;
- no production mutation has occurred.

## Phase 1 — P0 site-wide claim audit and emergency correction package

Priority: P0.

### 1A. Full publication-surface inventory

Search, without initially editing, all of the following:

- published, draft, private, scheduled, trashed, and revision WordPress content;
- post titles, bodies, excerpts, slugs, authors, dates, featured-image metadata, and
  custom fields;
- Rank Math titles, descriptions, canonicals, robots, schema, redirects, and sitemap
  settings;
- theme-rendered text, PHP data structures, JSON-LD, AI assistant responses and
  prompts, tests, discovery generators, `llms.txt`, `llms-full.txt`, agent-skills,
  Markdown output, menus, widgets, blocks, and reusable content;
- media/PDF filenames, attachment pages, captions, alt text, OCR-visible certificate
  text, and publicly indexed uploads;
- cached copies and search snippets as evidence of propagation, not as truth;
- official social profiles and third-party listings.

Output: one row per claim occurrence with URL/surface, exact text, evidence status,
risk, required action, and proposed canonical claim.

### 1B. Claim adjudication

Classify each occurrence:

- `KEEP`: exact, current, and correctly scoped;
- `CORRECT`: useful page with repairable wording;
- `SCOPE`: true only after location/matrix/parameter/method qualification;
- `301`: duplicate/obsolete URL with a strong equivalent canonical destination;
- `NOINDEX`: page required for users or records but not fit for search/AI discovery;
- `HOLD`: insufficient evidence; do not publish or redirect until resolved.

Never replace a specific false claim with a broad unqualified claim such as
"PNAC-accredited laboratory." Every accreditation statement must identify the
applicable location and instruct the reader to verify the listed scope.

### 1C. P0 correction implementation

After Gate 0 approval, update the credential truth atomically across all affected
surfaces. At minimum, the correction package must cover:

- Organization and branch credential data;
- credential and location pages;
- service pages and high-risk legacy articles;
- AI assistant answers, guardrails, Urdu/Roman Urdu equivalents, and citations;
- discovery files and their generator;
- Markdown representation;
- page titles/descriptions where they contain a credential;
- schema and all automated positive/negative tests;
- deployment/live-monitor assertions that currently assume Lahore-only PNAC.

Required negative tests must prove that:

- LAB-284 is never attributed to Envi Tech AL unless authoritative evidence changes;
- Karachi scope is never attributed to Lahore and Lahore scope is never attributed
  to Karachi;
- Organization-level schema does not make branch-specific scope appear universal;
- no unlisted parameter, matrix, method, range, or calibration capability is called
  accredited;
- expired or superseded evidence cannot pass the readiness contract;
- the assistant refuses ambiguity rather than guessing.

Exit criteria:

- 100% of ledgered credential occurrences match approved evidence;
- no conflicting claim remains in HTML, metadata, schema, discovery, assistant, or
  indexed media surfaces;
- complete local validation passes;
- no CSS/JS/image/navigation hash changes;
- staging passes claim, schema, assistant, canonical, sitemap, robots, and visual
  structure checks.

## Phase 2 — P0 legacy article repair and indexation control

Priority: P0, after the credential truth set is approved.

1. Crawl every WordPress URL and compare it with the claim ledger and canonical
   intent map.
2. Score each page for factual risk, uniqueness, backlinks/internal links, organic
   visibility, conversion purpose, and replacement quality.
3. Repair strong pages that have genuine unique value.
4. Use a single one-hop 301 only when a substantially equivalent canonical page
   satisfies the same intent.
5. Use noindex for a page that must remain accessible but should not be presented as
   search/AI evidence.
6. Do not bulk-delete content, redirect unrelated intent, create chains, or redirect
   every weak page to the homepage.
7. Remove retired URLs from sitemaps and canonicalize every internal link, menu,
   citation, discovery entry, and assistant source.
8. Preserve a decision record for each URL and monitor 404/301 traffic after release.

Exit criteria:

- every legacy URL has an approved disposition;
- all redirects are one hop and targets return direct 200 responses;
- no redirected/noindexed URL appears in sitemaps or AI discovery artifacts;
- no canonical target is itself retired, noindexed, or redirected;
- high-value backlinks and intent are preserved.

## Phase 3 — P1 canonical authority and entity architecture

Priority: P1.

### 3A. One authority URL per intent

Build an intent matrix for, at minimum:

- organization and credentials;
- Karachi environmental laboratory;
- Lahore environmental laboratory;
- analytical laboratory services;
- drinking/process/wastewater testing;
- air, stack, noise, soil, hazardous waste, industrial hygiene, calibration,
  consultancy, certification advisory, ballast water, and thermal imaging;
- report verification and each major regulatory guidance intent.

For each intent select one indexable canonical authority URL, define supporting
pages, prevent cannibalization, and map internal-link anchors and evidence sources.
Consolidation is content/SEO work only; no navigation or layout change is allowed
without separate approval.

### 3B. Expert/author/reviewer evidence model

No expert or Person schema may be invented. For each proposed person obtain:

- legal/public name and approved display name;
- current role and relationship to Envi Tech AL;
- qualifications, issuer, identifier if public, and evidence;
- bounded areas of expertise;
- original biography and approved professional photograph;
- LinkedIn or other authoritative same-person profile;
- approval to publish and a review date.

Prepare an unpublished profile and an author/reviewer assignment matrix. An article
may name a reviewer only when the person actually reviewed that version and the
review date is recorded.

### 3C. Structured-data validation

Create a page-type schema contract and validate the final rendered graph, not only
PHP arrays:

- one Organization identity with stable `@id`;
- branch-specific LocalBusiness nodes with correct addresses and credentials;
- Service nodes tied to the correct provider and area served;
- Article nodes with real author/reviewer identity and dates;
- Person nodes only for approved public profiles;
- BreadcrumbList matching visible hierarchy;
- canonical URL and schema `url`/`mainEntityOfPage` agreement.

Prohibit unsupported ratings, reviews, prices, opening hours, awards, clients,
credentials, service areas, or aggregate claims. Test with Schema.org validation,
Google Rich Results where applicable, JSON parsing, duplicate-ID detection, and
page-to-graph consistency checks.

Exit criteria:

- one approved authority URL per intent;
- no competing indexable page targets the same primary intent without a documented
  supporting role;
- every schema property is visible or evidenced and entity IDs are stable;
- profiles remain unpublished until owner approval.

## Phase 4 — P1 original evidence programme

Priority: P1. This phase cannot be manufactured from marketing copy.

### 4A. Technical case studies

Select real completed work with client consent or a defensible anonymization basis.
Each case-study evidence pack must contain:

- problem and decision context;
- sample/site description without identifying confidential information;
- applicable standard or requirement;
- date range, method, equipment, QA/QC, limitations, and uncertainty where relevant;
- actual findings with units and no invented improvement percentage;
- action/outcome that can be documented;
- technical author, independent reviewer, review date, and source records.

Legal/confidentiality review occurs before an unpublished draft is created. Client
logos, names, report screenshots, certificates, or quotations require explicit
permission.

### 4B. Original datasets/research insights

Define a data publication protocol before selecting a topic:

- purpose, population, time window, matrix, method, units, detection limits, and
  exclusions;
- de-identification and aggregation thresholds;
- statistical method, missing-data treatment, version, licence, and DOI/repository
  option where appropriate;
- machine-readable CSV plus data dictionary and human-readable methodology;
- named technical author/reviewer and reproducibility checks.

Never publish customer-identifiable data, raw reports, regulated personal data, or
an aggregate small enough to re-identify a client/site. Findings must not be
generalized beyond the sample population.

### 4C. Photographs, charts, and technical visuals

Use only original, consented, correctly captioned evidence. Preserve the existing
site design and media positions. Before publication verify:

- provenance, consent, date, location sensitivity, and absence of confidential
  labels/screens/screenshots;
- correct axis, units, sample size, methodology note, colour accessibility, alt text,
  and non-misleading scale;
- responsive dimensions, compression, EXIF/privacy handling, and no LCP regression.

Any new visual component or layout requires separate frontend approval.

Exit criteria:

- all facts trace to private source records;
- anonymization and client consent are documented;
- drafts pass technical, legal/confidentiality, and brand review;
- publication approval is explicit and item-specific.

## Phase 5 — P1/P2 external entity consistency and Lahore reputation

Priority: P1 for stale descriptions; P2 for reputation growth.

1. Inventory owned/claimable profiles: Google Business Profiles, Bing Places,
   LinkedIn, Facebook, industry directories, regulator directories, maps, and major
   citation sources.
2. Compare name, legal entity, address, phone, website, services, credentials, and
   descriptions with the claim ledger.
3. Draft one approved short, medium, and long company description with branch-
   specific credential wording.
4. Change only owner-controlled profiles with recorded account authorization;
   document submissions to third parties and recheck after moderation.
5. Strengthen Lahore reputation through complete accurate profile data, real local
   photographs, service evidence, response to genuine reviews, and a compliant
   customer review-request process.

Never purchase reviews, gate negative reviewers, create staff reviews, duplicate
locations, keyword-stuff the business name, or claim unverified accreditation.

Exit criteria:

- priority profiles match the approved entity/credential record;
- every external mutation has an account owner, before/after evidence, and status;
- Lahore reputation activity is authentic and policy compliant.

## Phase 6 — P2 technical discovery verification

Priority: P2. Existing controls currently pass; default action is monitor, not change.

### 6A. Robots and crawlers

Verify production and edge responses for ordinary clients, Googlebot, Bingbot, and
OAI-SearchBot using GET and HEAD. Confirm content type, status, body, cache policy,
and absence of challenge HTML. User-Agent simulation is diagnostic only; any WAF
exception must use provider-verified crawler identity. Never weaken the firewall.

### 6B. Sitemap, canonicals, and indexation

Build a complete URL matrix with status, canonical, robots, sitemap membership,
indexation intent, schema URL, internal-link count, and redirect target. Require:

- one self-canonical for each indexable 200 page;
- no staging URLs, redirect sources, noindex pages, parameter traps, attachment
  duplicates, or noncanonical pages in sitemaps;
- consistent protocol/host/trailing slash;
- no canonical chains, cross-intent canonicals, orphan authority pages, or production
  noindex leaks;
- Search Console inspection/sitemap evidence where access is supplied.

### 6C. AI assistant

Keep the assistant because the verified same-origin endpoint is currently healthy.
After credential correction, update its verified catalogue, citations, Urdu/Roman
Urdu answers, and negative scope rules. Run at least:

- direct, paraphrased, misspelled, Urdu, Roman Urdu, and contextual questions;
- location/matrix/parameter/method accreditation traps;
- prompt injection, fabricated price/timeline, unsafe health/compliance conclusion,
  and unsupported availability scenarios;
- latency, empty/error response, citation availability, and mobile interaction tests.

If the endpoint becomes reproducibly unavailable, prepare a restore-versus-remove
decision. Any visible removal, replacement, button, message, or widget change is a
frontend change and requires explicit approval.

Exit criteria:

- read-only crawler/indexation suite passes with zero unexplained 4xx/5xx/429;
- assistant answers approved credential questions correctly and refuses unsupported
  claims;
- no frontend change was made.

## Phase 7 — P2 GA4 and server-log measurement

Priority: P2; no frontend instrumentation change without approval.

1. Inventory existing GA4 tags/events and server/CDN logs without adding a browser
   tag.
2. Define AI referral channels for known AI/search referral hosts separately from
   verified crawler traffic.
3. Build a privacy-minimized data pipeline with retention, access control, IP
   handling, bot verification, timezone, and sampling documented.
4. Report landing page, source, engaged session, conversion where already measured,
   crawler hits, discovery-resource hits, status codes, bytes, cache outcome, and
   response time.
5. Separate human referrals, declared bots, verified bots, monitoring probes, and
   suspected spoofing. Do not infer "AI citations" from crawler hits alone.
6. Establish weekly anomaly alerts and monthly trend reporting with a baseline period.

If meaningful conversion measurement requires a new GA4/GTM browser event or tag,
submit the exact event, payload, consent impact, performance impact, and rollback
plan for approval before implementation.

Exit criteria:

- dashboard definitions are reproducible and privacy reviewed;
- no PII, secrets, raw production logs, or client data enter Git;
- each metric states what it proves and what it does not prove.

## 6. Release gates and zero-known-error standard

"Zero error" is implemented as a fail-closed release standard: no known critical or
high-severity defect, no unsupported claim, and no bypassed test may be accepted.
It is not a promise that future issuers, crawlers, or search indexes cannot change.

Every production candidate must pass all gates:

1. **Evidence gate:** every changed claim has current primary evidence and owner
   approval.
2. **Scope gate:** location, matrix, parameter, method, range, and validity are not
   broadened.
3. **Frontend-freeze gate:** CSS/JS/image/navigation hashes are unchanged; DOM
   structure/classes and responsive layout are unchanged except approved text nodes.
4. **Local gate:** `bash scripts/test-ai-visibility.sh` passes from a clean branch.
5. **Review gate:** CI passes and an independent reviewer checks the claim diff.
6. **Staging gate:** exact pinned candidate is deployed with verified backup; staging
   remains noindex and separate from production.
7. **Functional gate:** claims, schema, redirects, canonicals, sitemaps, discovery,
   assistant scenarios, accessibility, mobile behaviour, and performance pass.
8. **Promotion gate:** production deploy pin equals the exact staging-tested commit;
   owner approves production promotion.
9. **Post-release gate:** canonical production URLs pass without cache-busting query
   strings; edge cache is purged; 429/challenge/5xx outcomes are reported, never
   hidden.
10. **Rollback gate:** recovery set and restoration command are verified before the
    release is declared complete.

Immediate stop conditions include:

- conflicting or expired issuer evidence;
- any attribution of one laboratory's scope to another;
- a visual, template, menu, plugin, WAF, or infrastructure change outside approval;
- production becoming noindex or staging becoming indexable;
- redirect chains, sitemap contamination, schema contradictions, or canonical drift;
- assistant fabrication or loss of source citations;
- unexplained 403, 415, 429, 5xx, challenge HTML, or cache cross-contamination;
- dirty repository, staging/production parity failure, backup failure, or rollback
  drift.

## 7. Deliverables

The programme produces:

- approved credential evidence pack and claim ledger;
- site-wide claim occurrence and risk report;
- legacy URL disposition register;
- canonical intent/authority map;
- structured-data page-type contract and validation report;
- unpublished expert-profile, case-study, dataset, and visual drafts;
- third-party entity consistency register;
- crawler, sitemap, canonical, indexation, and assistant QA evidence;
- GA4/server-log measurement specification and dashboard;
- branch/PR/staging/production evidence and verified rollback records.

## 8. Required owner inputs before implementation

1. The exact current PNAC LAB-285 scope PDF and approval to use only its listed
   matrices, parameters, methods, and ranges. The active-register identifier and
   validity dates are now resolved by the supplied PNAC screenshot.
2. Permission for a narrow P0 exception to edit credential text/data inside existing
   theme-rendered content, JSON-LD, AI answers, and discovery artifacts without any
   CSS, layout, template-structure, navigation, or UI change.
3. Current source documents for every other credential to be retained.
4. Expert identity/qualification/photo/publication approvals.
5. Anonymized case-study source records, client consent status, and dataset owners.
6. Authorized owners for third-party profiles, GA4, Search Console, and server/CDN
   log access.

Without inputs 1 and 2, planning and read-only audits may continue, but the P0 public
correction cannot be implemented safely.
