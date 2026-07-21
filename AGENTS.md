# Envi Tech AL AI visibility project

## Scope

This repository contains the custom WordPress theme, AI-discovery artifacts,
deployment transactions, and validation for envitechal.com.

## Safety rules

- Never commit passwords, API tokens, cookies, private keys, WordPress database
  data, uploads, backups, or production logs.
- Treat `envitechal.com` as production and `staging.envitechal.com` as staging.
- Make code changes on a branch and validate them before proposing a pull
  request.
- Do not run production deployment or rollback scripts from a generic Codex
  cloud container. Those transactions are designed to run inside the cPanel
  account, where `uapi`, WP-CLI, the document roots, and private recovery paths
  are available.
- Production promotion remains approval-gated. Follow
  `docs/AI_VISIBILITY_DEPLOYMENT.md` and validate on staging first.
- Keep firewall/WAF protections enabled. Crawler exceptions must use the
  provider's verified identity mechanism, not only a User-Agent string.

## Validation

Run the complete repository-local validation suite:

```bash
bash scripts/test-ai-visibility.sh
```

Run the read-only public production monitor only when live network validation
is part of the task:

```bash
bash scripts/test-ai-visibility.sh --include-live
```

The live monitor may encounter transient edge rate limiting. Report every 429;
do not weaken the WAF or silently ignore it.

## Cloud environment

Use this setup command in the Codex cloud environment:

```bash
bash scripts/cloud-setup.sh
```

Use the same command as the maintenance script; it is idempotent. Agent-phase
internet access should be limited to `envitechal.com`,
`staging.envitechal.com`, and GitHub unless a task explicitly requires another
trusted host. Prefer GET, HEAD, and OPTIONS for normal analysis and monitoring.
