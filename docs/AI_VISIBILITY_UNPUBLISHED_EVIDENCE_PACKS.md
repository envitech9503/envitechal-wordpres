# Unpublished Evidence Packs

Status: **HOLD — NOT PUBLIC CONTENT, NOT A CLAIM, NOT APPROVED FOR SCHEMA**
Prepared: 8 August 2026

These record structures allow evidence-led work to continue without inventing
people, clients, results or datasets. Empty fields are deliberate blockers. No
material in this document authorizes publication.

## Named expert/author/reviewer pack

Create one private record per proposed person:

| Required field | Evidence requirement |
| --- | --- |
| Legal and approved display name | Government/company record plus written display approval |
| Current role and relationship | Current employment/contract record and effective dates |
| Qualifications | Issuer, qualification, date and public identifier where permitted |
| Bounded expertise | Specific matrices, methods, regulations or disciplines supported by evidence |
| Original biography | Person-approved, fact-checked text |
| Photograph | Original file, consent, date, rights and privacy/EXIF review |
| Same-person profile | Authoritative public profile controlled by or clearly identifying the person |
| Publication approval | Person, business owner, date and approved surfaces |
| Review date | Renewal trigger and responsible owner |

Article reviewer assignment additionally requires the exact article version,
review scope, completion date and reviewer sign-off. Until every applicable field
is complete, keep Organization authorship and do not emit `Person` schema.

## Technical case-study pack

Each candidate remains HOLD until it contains:

- client-consent record or documented defensible anonymization basis;
- problem and decision context;
- non-identifying site/sample description and date range;
- applicable standard, sampling design, method, equipment and units;
- QA/QC, detection limits, uncertainty and material limitations;
- actual source-linked results without invented percentage improvements;
- documented action/outcome;
- confidentiality/legal review; and
- technical author, independent reviewer, review date and source-record owner.

Prohibited without explicit permission: client identity/logo, report screenshot,
certificate, quotation, precise site coordinates or a combination of facts that
can re-identify the customer.

## Original dataset/research pack

Required protocol fields:

- purpose, population, geography, matrix and time window;
- method/version, units, detection/quantification limits and exclusions;
- de-identification and minimum aggregation threshold;
- statistical method, missing/censored-data treatment and limitations;
- dataset version, licence, owner, author, reviewer and reproducibility result.

Machine-readable draft schema:

```text
dataset_version,period_start,period_end,region_group,matrix_group,
parameter,method_reference,unit,result_band,n_observations,
n_below_detection,aggregation_rule,quality_flag
```

Never place sample IDs, client names, addresses, coordinates, report numbers,
phone/email data, raw reports or regulated personal data in a public dataset or
Git repository. Aggregates below the approved anonymity threshold are suppressed.

## Photograph/chart/technical-visual pack

Before publication record provenance, creator, rights/consent, capture date,
location sensitivity, confidentiality review, caption, methodology note, axes,
units, sample size, accessible colour/alt text, non-misleading scale, export size,
compression, EXIF removal and responsive/LCP check. Existing media positions and
visual components remain frozen; a new placement or component requires a separate
frontend decision.

## Release state

No expert profile, case study, original dataset or new technical visual can leave
HOLD until its complete evidence record and item-specific publication approval are
stored outside the public repository. This protects the brand from fabricated or
confidential evidence while leaving publication-ready structures available.
