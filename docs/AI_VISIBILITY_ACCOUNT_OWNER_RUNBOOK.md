# Account-owner AI Visibility Runbook

Prepared: 8 August 2026
Boundary: third-party accounts and private operations only; no WordPress frontend change.

## External profile transaction

For each `READY_ACCOUNT_ACTION` row in `AI_VISIBILITY_OPERATIONS_REGISTER.csv`:

1. Confirm the account is genuinely owned or administered by Envi Tech AL.
2. Capture a private before-state with profile URL, UTC timestamp and current fields.
3. Compare name, address, phone, website, category and credential statements with
   `CREDENTIAL_CLAIM_LEDGER.md` and `AI_VISIBILITY_EXTERNAL_DESCRIPTIONS.md`.
4. Apply only the approved platform-length description. Never inherit one branch's
   credential or scope into another branch.
5. Capture the private after-state and record the account owner and timestamp in
   `AI_VISIBILITY_EXTERNAL_ENTITY_REGISTER.csv` through a reviewed repository change.
6. Recheck the public profile in a logged-out browser. If the platform rewrites,
   rejects or delays the change, retain `ACCOUNT_ACTION_REQUIRED` and record the fact.

Passwords, recovery codes, tokens, cookies and screenshots containing private
account data must remain outside Git.

## Lahore reputation transaction

- Confirm exactly one genuine claimed profile for the Lahore location and match its
  NAP to the controlled branch record.
- Upload only original, current, rights-cleared Lahore photographs. Do not reuse a
  Karachi photograph as Lahore evidence.
- Ask every eligible customer neutrally: “Thank you for working with Envi Tech AL.
  If you wish, you may share an honest review of your experience at [official review
  link]. Feedback of every kind helps us improve.”
- Do not offer incentives, ask staff/family, review-gate, suppress negative feedback,
  keyword-stuff the name or disclose client/sample information in a response.
- Respond to a factual negative review with acknowledgement and a private resolution
  route; do not debate test results publicly.

Monthly owner check: duplicates, NAP, hours, services, photos, unanswered reviews,
credential wording and profile links. Evidence stays private; the register stores
only status and non-sensitive timestamps.
