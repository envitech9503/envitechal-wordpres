# Handoff: standards pages, keyword-gap content and title fixes (22-10-2026)

Branch `content-22-10-2026`. Nothing is deployed. Built in the cloud session
from the Search Console gap analysis of 21-10-2026.

## What is in the branch

### Two reference pages (theme, `inc/standards-pages.php`)

| Page | Target searches (90 days) | Source |
|---|---|---|
| /punjab-environmental-quality-standards-peqs/ | peqs (330), peqs for drinking water, peqs for wastewater, peqs standards | Punjab Gazette (Extraordinary) 15-08-2016, Notification 120 of 2016, SO(G)/EPD/7-26/2013 of 05-08-2016: effluent (32 parameters), drinking water, ambient air, noise, industrial gaseous emissions |
| /national-environmental-quality-standards-neqs/ | neqs (71), neqs for wastewater, neqs for drinking water, NEQS pdf | S.R.O. 549(I)/2000 (effluent, gaseous emissions); S.R.O. 1062, 1063, 1064(I)/2010 (ambient air, drinking water, noise) |

Both use one renderer driven by a data array, the SEQS page's design, Article
and FAQPage schema, download links to the official notifications, links to
both laboratories and to each other. Limit tables have a sticky first column
on mobile. Pages are created on `init` if missing, as the SEQS page is. Source
PDFs are kept in the session record; every value was checked against the
gazette scans.

### Three articles (scripts/posts-22-10-2026/)

| Slug | Target searches | Words | Note |
|---|---|---|---|
| how-to-choose-the-suitable-environmental-lab | water testing company (2,142 impressions at position 81), how to choose a testing laboratory | 1,882 | Rewrite in place of the 2024 post; URL kept, publish date refreshed |
| epa-certified-laboratories-pakistan | epa certified labs (71), epa approved labs, list of ISO 17025 laboratories | 1,721 | Sindh 2014 regulations and Punjab EPD list, from the sources |
| water-testing-cost-lahore | water testing laboratory lahore / rawalpindi / islamabad price list, water test price in pakistan | 1,584 | Price-free, matching the Karachi guide and the site's AI guidance |

Each has FAQ data for schema, exact-anchor links to owner pages, and the
owner's artwork fitted to 1600 x 1000 as the featured image.

### Title and heading fixes

- Analytical lab service: title "Environmental Testing Lab in Pakistan |
  Envi Tech AL"; H1 "Environmental testing lab results you can defend."
- Noise page: "Industrial Noise Survey and Monitoring | Envi Tech AL", H1 and
  summary rewritten around survey and assessment.
- Soil page: "Soil Testing Laboratory Karachi & Lahore | Envi Tech AL", H1
  and summary rewritten.

### Links added

SEQS page to PEQS and NEQS; Lahore lab page to PEQS and the Lahore cost
guide; accreditations page to the EPA article; water page to the Lahore cost
guide; llms.txt quick answers for SEQS/PEQS/NEQS and EPA certification.

### Tests

`tests/standards-pages-test.php` (new, 40+ checks) and
`tests/post-faq-meta-test.php` (extended to both batches, with a guard
against compliance-guarantee wording). All tests pass; `php -l` clean; core
CSS regenerated and in sync.

## Deploy steps (next online session)

1. Merge `content-22-10-2026` into `main`, pin `VALIDATED_PR_COMMIT` in both
   deploy scripts, push.
2. cPanel: pull, create `~/PRODUCTION_APPROVED`, click Deploy HEAD Commit.
3. Publish the posts from cPanel Terminal, staging first:
   ```
   cd ~/repositories/envitechal-wordpres
   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-22-10-2026.php dry
   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-22-10-2026.php
   wp --path="$HOME/public_html" eval-file scripts/publish-posts-22-10-2026.php
   wp --path="$HOME/public_html" litespeed-purge all
   ```
4. Verify: /punjab-environmental-quality-standards-peqs/ and
   /national-environmental-quality-standards-neqs/ render with five tables
   each and FAQ schema; the three articles render with FAQ schema; the three
   retitled pages show the new titles; llms.txt carries the new quick answers.
5. Request indexing for the two pages, three articles and three retitled
   pages (eight URLs).
6. Lighthouse on the PEQS page and the water page from a quiet container.

## Still open

- Pakistan Accord article (item 7 of the plan), not started.
- Lighthouse parity check from 21-10 still unverified.
