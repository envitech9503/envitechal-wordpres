# Safe cPanel code import

This procedure imports only Envi Tech AL-owned custom code. It does not modify the live site.

## Never import

Do not copy `wp-config.php`, databases, uploads, caches, backups, logs, private keys, WordPress core, the GeneratePress parent theme, or third-party/licensed plugins.

## 1. Verify the source path

In cPanel **Domains**, confirm the document root for `envitechal.com`. The commands below assume it is `$HOME/public_html`.

In **Advanced → Terminal** run:

```bash
SITE="$HOME/public_html"
git --version
command -v rsync
test -d "$SITE/wp-content/themes/generatepress-envitechal" && echo "Theme found" || echo "STOP: verify the document root"
```

Stop unless the final line says `Theme found`.

## 2. Create a repository-scoped SSH key

```bash
umask 077
mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh"
ssh-keygen -t rsa -b 4096 -f "$HOME/.ssh/envitechal_github_import" -C "a2-envitechal-import" -N ""
cat "$HOME/.ssh/envitechal_github_import.pub"
```

Only copy the single line printed by the final command. Never reveal or download the private file without `.pub`.

In GitHub, open **Settings → Deploy keys → Add deploy key** for this repository:

- Title: `A2 cPanel import - temporary`
- Key: paste the public `.pub` line
- Allow write access: temporarily enabled for the initial import

Create or edit `$HOME/.ssh/config`:

```sshconfig
Host github-envitechal
  HostName github.com
  User git
  IdentityFile ~/.ssh/envitechal_github_import
  IdentitiesOnly yes
```

Then run:

```bash
chmod 600 "$HOME/.ssh/config"
ssh -T git@github-envitechal
```

On first contact, accept only after the fingerprint matches GitHub's official published fingerprint. A successful test says authentication succeeded but GitHub does not provide shell access; exit code 1 is normal.

## 3. Clone outside the web root

In cPanel **Files → Git Version Control → Create**:

- Clone a repository: enabled
- Clone URL: `git@github-envitechal:envitech9503/envitechal-wordpres.git`
- Repository path: `repositories/envitechal-wordpres`
- Repository name: `Envi Tech AL WordPress`

Never place the repository or its `.git` directory inside `public_html`.

## 4. Copy only the active child theme

```bash
SITE="$HOME/public_html"
REPO="$HOME/repositories/envitechal-wordpres"
mkdir -p "$REPO/wp-content/themes"
rsync -a --exclude='.git' "$SITE/wp-content/themes/generatepress-envitechal/" "$REPO/wp-content/themes/generatepress-envitechal/"
```

Do not copy all plugins or all mu-plugins. They must be reviewed and selected individually.

## 5. Review before committing

```bash
cd "$REPO"
find . -type f \( -name 'wp-config.php' -o -name '*.sql' -o -name '*.sql.gz' -o -name '*.pem' -o -name '*.key' \) -print
find . -type f -size +20M -print
grep -RIlE --exclude-dir=.git 'DB_PASSWORD|AUTH_KEY|SECURE_AUTH_KEY|BEGIN (RSA|OPENSSH|EC) PRIVATE KEY' . || true
git status --short
```

Stop here. The first three checks should return no output. Review `git status --short` before committing.

## 6. Initial commit

Only after the file list is approved:

```bash
cd "$REPO"
git config user.name "Envi Tech AL"
git config user.email "YOUR_GITHUB_NOREPLY_EMAIL"
git add wp-content/themes/generatepress-envitechal
git status --short
git commit -m "Import current GeneratePress child theme"
git push origin main
```

After the first push, replace the temporary write deploy key with a new read-only deploy key. Production deployment is not configured at this stage.
