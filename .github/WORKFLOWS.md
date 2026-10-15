# GitHub Actions — Aegis Plugin

| Workflow | File | Purpose |
|----------|------|---------|
| CI | `workflows/ci.yml` | `npm ci` + `npm run build`, Composer install, PHPCS, required-file checks |
| Spelling | `workflows/spelling.yml` | Typos via `.typos.toml` |
| Stale | `workflows/stale.yml` | Auto-close inactive issues/PRs |
| Release | `workflows/release.yml` | Tag `v*` → build assets, package zip with `.distignore`, draft GitHub Release |

## Release packaging

- **Included:** `aegis.php`, `uninstall.php`, `src/`, `config/`, `patterns/`, `templates/`, `languages/`, `public/`, compiled block/admin/editor assets
- **Excluded:** `.github/`, `docs/`, Node/Composer manifests, `vendor/`, admin/editor **sources**, tests/tools — see root `.distignore`
- **Parity:** `.gitattributes` `export-ignore` mirrors `.distignore` for `git archive`

Local dry-run:

```bash
rsync -am ./ /tmp/aegis-dist/ --exclude-from='.distignore'
```
