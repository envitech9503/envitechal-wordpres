# Handoff: keyword-support content and FAQ schema (20-10-2026)

Branch `content-20-10-2026`, merged to main (b73da9b, theme d275157).

**Deployed 21-10-2026:** theme to staging and production via cPanel (backups
`staging-envitechal-com-theme-before-20261021T122422Z` and
`envitechal-com-theme-before-20261021T122431Z`); posts published on staging
(23242, 23244, 23246) and production (23255, 23257, 23259); llms-full corpus
rebuilt (58 sections); indexing requested for the seven URLs. Lighthouse on
21-10 ran in a slow container (unchanged homepage TBT also tripled), so the
water page figure of 60 is not a verified regression; re-run before 12-11.

## What is in the branch

### Three Knowledge Hub articles (scripts/posts-20-10-2026/)

| Slug | Target searches | Words | FAQs | Author |
|---|---|---|---|---|
| water-testing-lab-pakistan-any-city | water testing lab Pakistan; water testing lab near me (other cities) | 1,841 | 8 | Hina Abidi |
| verify-pnac-accredited-lab-pakistan | PNAC accredited lab Pakistan; supports environmental testing lab Karachi / Lahore | 1,759 | 7 | Syed Imran Ali Kazmi |
| water-testing-karachi-borewell-tanker-ro | water testing lab Karachi; borewell, tanker, RO water Karachi | 1,953 | 7 | Hina Abidi |

Each article links to its owner page with the exact keyword as anchor text
(water testing lab in Karachi, water testing lab in Lahore, environmental
testing lab in Karachi, environmental testing lab in Lahore). All links were
checked live and return 200. Hero images are branded 1200 x 900 WebP, no PNAC
logo, no hyphens in the image text.

### Theme (commit on this branch)

- `functions.php`: any post with `eta_post_faqs` meta (JSON list of
  [question, answer]) now emits FAQPage schema. Previously only the EMR post
  had FAQ schema. New function `eta_modern_post_meta_faqs()`.
- `functions.php`: SEO title and description overrides for the three slugs.
- `functions.php`: Karachi lab page links to the borewell article and the PNAC
  verification article; Lahore lab page links to the any-city article and the
  PNAC verification article; accreditations page links to the PNAC article.
- `template-parts/service-water-testing.php`: three new related cards
  (any city, verifying accreditation) alongside the existing six.
- `tests/post-faq-meta-test.php`: guards the meta reader, the article house
  rules (no em dash, British spelling, owner links, FAQ headings match the
  manifest) and the theme links. All tests pass; `php -l` clean on all files.

### Publish script (scripts/publish-posts-20-10-2026.php)

Idempotent by slug. Sets content, excerpt, categories, tags, Rank Math title,
description and focus keyword, `eta_author`, `eta_post_faqs`, and the featured
image (sideloaded once, tracked by `_eta_source_image`). Rebuilds the
llms-full corpus afterwards.

## Deploy steps (next online session)

1. Merge `content-20-10-2026` into `main`, pin the theme commit in both deploy
   scripts (`VALIDATED_PR_COMMIT`), push.
2. cPanel: pull, create `~/PRODUCTION_APPROVED`, click Deploy HEAD Commit
   (theme to staging and production).
3. Publish the posts on staging first, then production, from the repo
   directory on the server:
   ```
   cd ~/repositories/envitechal-wordpres
   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-20-10-2026.php dry
   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-20-10-2026.php
   wp --path="$HOME/public_html" eval-file scripts/publish-posts-20-10-2026.php
   wp --path="$HOME/public_html" litespeed-purge all
   ```
   If the server cannot run WP-CLI from the session, the same three commands
   can be issued through cPanel Terminal.
4. Verify on live: each article renders, FAQPage schema present in the
   `<script type="application/ld+json">` block, featured image set, owner
   pages show the new links, llms-full.txt contains the three new sections.
5. Request indexing in Search Console for the three new URLs and the four
   changed pages (water services, Karachi lab, Lahore lab, accreditations).
6. Lighthouse on one article and the water page (budget: LCP under 2.5 s).

## Still with the owner

- Business Profile categories (Karachi and Lahore) are unsaved. Primary
  category Water Testing Laboratory on both; this is the largest remaining
  lever for "near me" and Lahore local results.
- Lahore profile rename to drop "Food Testing Lab Lahore" from the name.

## Measurement

Baseline recorded 17-10-2026; re-measure scheduled 12-11-2026. Live positions
on 20-10-2026 (Pakistan, non-personalised): Envi Tech AL 1st or 2nd organic
on all seven target keywords; Lahore profile absent from the map pack for
"water testing lab lahore".
