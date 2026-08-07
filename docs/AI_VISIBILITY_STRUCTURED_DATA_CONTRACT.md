# AI Visibility Structured-Data Contract

Effective date: 8 August 2026
Applies to: rendered production HTML on `https://envitechal.com/`
baseline must never be overwritten.
This contract is fail closed. A schema property may be published only when the
same fact is visible on the page or traceable to the repository credential
ledger. Schema must describe the page; it must not manufacture search features.

## Stable entity graph

| Entity | Required stable `@id` | Required controls |
| --- | --- | --- |
| Organization | `https://envitechal.com/#organization` | One organization identity; official name, canonical website and evidenced contact data only. |
| Karachi laboratory | `https://envitechal.com/#karachi-lab` | Karachi address; PNAC LAB-285 only; credential scope must remain location-, matrix-, parameter- and method-specific. |
| Lahore laboratory | `https://envitechal.com/#lahore-lab` | Lahore address; PNAC LAB-347 only; credential scope must remain location-, matrix-, parameter- and method-specific. |
| WebSite | `https://envitechal.com/#website` | Publisher references the Organization stable ID. |
| WebPage | Canonical URL plus `#webpage` | `url`, `mainEntityOfPage` and canonical must agree. |

LAB-284 is a prohibited Envi Tech AL identifier. A branch credential must never
be attached to the other branch or represented as universal Organization scope.

## Page-type contracts

### Organization and location pages

- One `Organization` node and, where relevant, distinct branch
  `LocalBusiness` nodes.
- Branch credentials reference the applicable branch only.
- Address, telephone and URL must match visible content and the credential
  ledger.
- `sameAs` is limited to verified official profiles.

### Service pages

- One primary `Service` node whose `url` equals the page canonical.
- `provider` references `#organization`; `areaServed` states service
  availability, not accreditation scope.
- A service capability must not be described as accredited unless every scope
  dimension is evidenced by the applicable current PNAC document.

### Articles and guidance

- `Article` or a more specific supported subtype may be used.
- `mainEntityOfPage` and `url` equal the self-canonical URL.
- Until a named person has documented identity, qualifications, relationship,
  approval and a real review record, `author` and `publisher` reference the
  Organization. No `Person` or reviewer claim may be invented.
- Published/modified dates must match the visible record.

### Breadcrumbs

- `BreadcrumbList` order and labels match the visible hierarchy.
- The final item equals the canonical page.
- Redirect sources, noindex URLs and noncanonical URLs are prohibited.

## Prohibited properties and implications

Do not publish unsupported ratings, reviews, prices, offers, opening hours,
awards, client names/logos, outcome percentages, employee expertise,
certifications, accredited capabilities or geographic branches. Do not use an
Organization credential to imply that every service or report is accredited.

## Automated release assertions

Rendered-page validation must prove:

1. every JSON-LD block parses;
2. no duplicate defined `@id` occurs in a page graph;
3. the canonical URL appears in the graph;
4. every indexable sitemap URL is a direct 200 and self-canonical;
5. no sitemap URL is noindex;
6. no retired redirect source occurs in a sitemap or discovery artifact;
7. LAB-284 is absent and branch scope does not cross locations;
8. CSS, JavaScript, images, navigation and visual structure remain unchanged.

The immutable pre-release crawl is recorded in
`docs/audits/2026-08-08/summary.json`. It covered 80 sitemap URLs and reported
zero non-200/non-self-canonical URLs, zero sitemap noindex URLs and zero schema
contract failures. A post-release crawl must be captured separately; the
baseline must never be overwritten.
