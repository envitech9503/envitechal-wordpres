# Google Search Console Indexation Snapshot

Captured: 8 August 2026
Property: `https://envitechal.com/`
Search Console data last updated: 5 August 2026

This is an operations record, not a claim that Google will rank, recrawl or
replace a search result by a particular date. No frontend file or WordPress
record was changed.

## Coverage state

- 87 indexed URLs.
- 308 not-indexed URLs across ten visible reasons.
- 115 excluded by `noindex`; 52 pages with redirects; 27 historical 404s.
- 96 crawled but currently not indexed.
- Three duplicate-without-canonical URLs were PDF download resources, not current
  credential/entity authority pages.
- Three category archives where Google selected another canonical now return
  `follow, noindex`, consistent with their intended non-authority role.
- Two historical 5xx examples now return a single 301 to the canonical analytical
  laboratory services URL, followed by a 200 response.
- The coverage report showed zero current 403 examples.

## Transactions completed

1. The indexed homepage was submitted once to the priority crawl queue.
2. The indexed credentials authority page was submitted once to the priority
   crawl queue; Search Console detected one valid breadcrumb item.
3. `sitemap_index.xml` was resubmitted successfully on 8 August 2026.
4. Validation was started for the fixed historical 5xx group.
5. Validation was started for the obsolete Google-selected-canonical category
   group, whose current intended state is `noindex`.

## Interpretation

Redirect, noindex and historical-not-found exclusions are not automatically
defects. Only current sitemap authority URLs are required to be direct 200,
self-canonical and indexable. Search Console's counts predate the final release
and will change only after Google processes the refreshed sitemap and validation
queues. Repeated URL submission does not increase crawl priority.
