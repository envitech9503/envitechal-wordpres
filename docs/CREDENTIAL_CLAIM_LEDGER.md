# Credential Claim Ledger

Evidence review date: 8 August 2026
This ledger complements the parameter-level
`PNAC_CREDENTIAL_CLAIM_LEDGER.md`. Lower-ranked company or marketing evidence
cannot override a current issuer register.

## Credential register

| ID | Entity/location | Category | Identifier / validity | Controlling evidence and SHA-256 | Public status | Approved qualification |
| --- | --- | --- | --- | --- | --- | --- |
| PNAC-KHI | Karachi permanent laboratory | ISO/IEC 17025:2017 accreditation | LAB-285; active register 05-05-2026 to 04-05-2029; surveillance applies | Current PNAC direct PDF and owner-supplied `285.pdf`; `483D72222E5761A9DC488DAA56FCDFFC708B0F1AC92D880C51477DE4C526020B` | VERIFIED PRIMARY | State Karachi LAB-285 and only the matrix/parameter/method in its current scope. |
| PNAC-LHE | Lahore permanent laboratory | ISO/IEC 17025:2017 accreditation | LAB-347; 22-09-2025 to 21-09-2028; surveillance applies | Current PNAC direct PDF and owner-supplied `LAB 347 LHR.pdf`; `641B0645F44601668F39B6723249F2E34E69D95EBBCBD36AC085871F1A3BBE80` | VERIFIED PRIMARY | State Lahore LAB-347 and only the matrix/parameter/method in its current scope. |
| PEPA-LHE | Lahore laboratory | Punjab EPA environmental laboratory certification | Company schema records validity through 23-03-2028 | Official Punjab EPA certified-laboratories page and certificate PDF; `9AF4288163C32A398A595A7365BE020A9CBBDA71F5D58C9ED624300038B22518` | VERIFIED PRIMARY, SCOPE LIMITED | Describe as a Punjab EPA listed/certified environmental laboratory only within the current certificate conditions and listed parameters. |
| SEPA-KHI | Karachi laboratory | Sindh EPA environmental laboratory certificate | `EPA/Lab/L.C/ENVI TECH AL AL-2/20/2020`; document states 10-06-2026 to 09-06-2028 | Company-published SEPA renewal document; `0B46482E7DA9F3C9F5926E7EDC18B1864BE8A55AA85AD55FCD49A6B0767E5DCA` | CURRENT DOCUMENT; ISSUER-REGISTER CONFIRMATION NOT CAPTURED | Label as a published Sindh EPA document whose current status/conditions should be confirmed; never imply PNAC scope. |
| QMS | Organization management system | ISO 9001:2015 certificate | `TPAK-080177324-QMS`; company schema records validity through 27-08-2027 | Company-published certificate image PDF; `BE83C0023FBAE5770C61C3576108AC0E1033E1F75EBFAF56176067650213AB73` | DOCUMENT CAPTURED; ISSUER-CURRENT CONFIRMATION REQUIRED | Describe only as a quality-management-system certificate; not laboratory accreditation or service scope. |
| EMS | Organization management system | ISO 14001:2015 certificate | `TPAK-080177424-EMS`; company schema records validity through 27-08-2027 | Company-published certificate image PDF; `B6A57D651CC2CD9B74E9E6C4FB6A372B9AA863B18E105D8EB983E63CCADFA916` | DOCUMENT CAPTURED; ISSUER-CURRENT CONFIRMATION REQUIRED | Describe only as an environmental-management-system certificate; not laboratory accreditation or service scope. |

## Approved organization wording

> Envi Tech AL operates PNAC-accredited permanent laboratory premises in Karachi
> (LAB-285) and Lahore (LAB-347). Accreditation is location-, matrix-, parameter-
> and method-specific; only work listed in the applicable current PNAC scope may
> be described as accredited. EPA and management-system credentials are separate
> categories and must be checked against their current issuer records.

## Negative controls

- LAB-284 belongs to another organization and must never be attributed to Envi
  Tech AL.
- `Green Lab Gold`, generic `TUV-certified laboratory`, universal regulator/buyer
  acceptance, legal standing, and `200+ clients` are not approved evidence-backed
  credential claims.
- TDAP/PTA testing support must not be described as authority approval,
  affiliation, registration or guaranteed eligibility.
- A capability, service, report, method, sample, location or employee must not
  inherit a credential merely from the Organization node.
- Expired, superseded or independently unconfirmed documents fail closed and must
  remain qualified or unpublished.

## Review triggers

Recheck this ledger on any issuer-register change, new certificate, surveillance
decision, scope revision, expiry/renewal, branch/address change, schema change,
assistant-catalogue change or external-profile update. Record a new document hash;
never overwrite historical evidence.
