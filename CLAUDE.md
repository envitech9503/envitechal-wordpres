# Envi Tech AL — Working Rules

Standing rules for this project. See `AGENTS.md` for the safety and deployment policy.

## Stack — fixed

- WordPress child theme `wp-content/themes/generatepress-envitechal/` (parent: GeneratePress).
- Front-end motion: **Three.js**, **GSAP + ScrollTrigger**, **Lenis**. Nothing else.
- Do not introduce another animation, 3D, or smooth-scroll library — no Framer Motion,
  Anime.js, Locomotive Scroll, AOS, or jQuery animation plugins.
- Page content is hardcoded PHP in `functions.php`, dispatched by slug in `page.php`.

## Deployment

- Deploy to **staging.envitechal.com** only, via `scripts/deploy-staging-theme.sh`.
- **Never touch the live site** (`envitechal.com`). Do not run `deploy-production.sh`
  or `rollback-production.sh`; production promotion stays approval-gated by the user.

## Language and links

- British English throughout: organisation, programme, analyse, licence (noun), centre,
  colour, recognise. Applies to page copy, docs, and commit messages.
- The contact URL is always `/contact-us-envi-tech-al/`, never `/contact/`.
- **Never use an em dash to bolt an explanatory clause onto a sentence**
  ("...for teams working to a deadline — scope confirmed before work begins").
  It is the clearest tell of machine-written copy and the client has rejected it.
  Write two sentences, or use a colon, comma, or brackets. For label separators
  use the middot (·) the site already uses, as in "PNAC ISO/IEC 17025 · LAB-285".
- Avoid the other stock cadences that read as generated: "in today's landscape",
  "it's not just X, it's Y", "unlock/elevate/seamless/robust", triplets of adjectives
  used for rhythm rather than meaning, and closing sentences that restate the opening.

## Regulatory wording

- Name bodies and standards exactly: Sindh EPA, Punjab EPA, SEQS, PEQS, NEQS, WHO,
  PNAC, ISO/IEC 17025.
- Accreditation is scope-bound: state that it applies to methods within the relevant
  approved scope. Cite lab numbers as LAB-285 (Karachi) and LAB-347 (Lahore).
- No marketing claims about compliance outcomes. Never write or imply "guaranteed
  compliance", "we ensure you pass", or "100% approval". Describe what is measured and
  reported — never the result the client will obtain.

## Performance budget (homepage)

- LCP under 2.5 s, CLS under 0.1, total JS under 300 KB gzipped.
- Honour `prefers-reduced-motion`: the scroll story must degrade to the static hero.
- Serve sized image variants with `srcset`; never a full-size PNG in a small slot.
- Keep LiteSpeed "Remove Query Strings" OFF, or asset versioning breaks and browsers
  cache stale CSS/JS for up to a year.

## Evidence — no task is complete without it

- Every change ships with proof: a Chrome screenshot, console/network output, or
  Lighthouse numbers. Quote the actual figures, not a summary of intent.
- If you cannot produce that evidence, say **"not verified"** and state why.
  Never report success from code inspection alone.
