# Envi Tech AL WordPress

Private, code-only source repository for the custom WordPress implementation of [envitechal.com](https://envitechal.com/).

## Included

- `wp-content/themes/generatepress-envitechal/`
- Envi Tech AL-owned custom plugins created for the site
- Deployment documentation and automated validation

## Never commit

- `wp-config.php`, `.env` files, passwords, tokens, cookies, SSH keys or certificates
- Database exports, customer/contact data or WordPress user data
- `wp-content/uploads/`, caches, backups or logs
- WordPress core, the GeneratePress parent theme, or third-party/licensed plugins

## Release policy

Changes are implemented and verified on staging first. Production deployment remains approval-gated and requires a current files/database backup plus rollback validation.
