# Kreativ Font Theme

Private WordPress theme repository for [kreativfont.com](https://kreativfont.com).

This codebase is used for the live Kreativ Font site and is maintained as a practical working theme, not as a public/distributable product. Changes are optimized for the needs of the site itself.

## What This Repo Contains

- Custom WordPress theme templates for the main site
- Theme assets, styles, scripts, and webfonts
- Branded site icons and `manifest.json`
- Marketplace/archive/filter templates used by the site

## Structure

```text
.
├── assets/               Source assets and compiled theme CSS
├── css/                  Additional stylesheet overrides
├── inc/                  Theme bootstrap modules and helper functions
├── img/                  Theme images and icons
├── js/                   Frontend JavaScript
├── partials/             Shared reusable template fragments
├── scripts/              Production audit utilities
├── webfonts/             Theme webfonts
├── functions.php         Theme loader for `inc/` modules
├── header.php            Global head markup and site header
├── footer.php            Global footer markup
└── template-*.php        Custom page/filter templates
```

## Main Theme Behavior

- Registers a primary navigation menu
- Supports featured images and dynamic document titles
- Loads compiled theme styles from `assets/dist/main.min.css`
- Loads frontend initialization from `assets/assets/components/init.js`
- Uses custom archive/filter templates for site-specific content flows
- Loads analytics, ads, affiliate scripts, and theme JavaScript through WordPress enqueue hooks

## Development Notes

- This is a private project, so code can stay tailored to `kreativfont.com`
- Prefer direct, maintainable edits over premature abstraction
- Keep generated/system files out of git with `.gitignore`
- Be careful with line endings; some legacy files still trigger CRLF normalization warnings

## Typical Workflow

1. Make theme changes locally.
2. Review the affected PHP, CSS, and JS templates.
3. Commit focused changes to Git.
4. Push to GitHub.
5. Let the `main` branch workflow deploy and audit production.

## Automated Deployment

This repository includes a GitHub Actions workflow at `.github/workflows/deploy-theme.yml` that can deploy the theme after every push to `main`.

Add these GitHub repository secrets before enabling it:

- `WP_SSH_HOST`: WordPress.com SFTP/SSH host
- `WP_SSH_PORT`: SSH port
- `WP_SSH_USER`: WordPress.com SSH username
- `WP_SSH_PASSWORD`: WordPress.com SSH password

The deployment workflow keeps the remote theme and WordPress root paths as reviewed, non-secret environment values:

```text
WP_REMOTE_PATH=/htdocs/wp-content/themes/kreativfont.com/
WP_ROOT_PATH=/htdocs/
```

The theme is mirrored only into `WP_REMOTE_PATH`. The deployment is an exact mirror, so files removed from the repository are removed from that theme directory on the next deployment. The repository `robots.txt` is uploaded separately to `WP_ROOT_PATH`, and `scripts/` is excluded from the public theme deployment.

Pull requests run the same PHP and active-JavaScript validation, but they never deploy, publish, merge, or create pull requests. Only pushes to `main` and manual workflow runs deploy production.

The workflow deploys the committed `assets/dist/main.min.css`; it does not run the legacy Grunt build. When editing SCSS in `assets/assets/`, regenerate and commit the matching compiled files in `assets/dist/` with the source change.

After deployment, the workflow runs the production audit. Run the same checks locally with:

```bash
php scripts/audit-production.php https://kreativfont.com
```

The audit verifies top-page HTTP responses, document structure, metadata, search ordering, template routing, `robots.txt`, the sitemap, and deployment markers.

## Important Files

- `functions.php`: lightweight loader for theme modules
- `inc/assets.php`: enqueue logic and asset versioning
- `inc/collections.php`: collection registry, routing, queries, and shared rendering
- `inc/font-taxonomy.php`: font classification, taxonomy branches, facets, and archive queries
- `inc/search.php`: enhanced font search, suggestions, ranking, and cache handling
- `inc/template-helpers.php`: shared page and font-card helpers
- `header.php`: meta tags, global scripts, analytics, top navigation
- `footer.php`: footer content, dark mode toggle script, `wp_footer()`
- `assets/package.json`: frontend asset tooling entry point
- `assets/Gruntfile.js`: legacy asset build workflow
- `scripts/audit-production.php`: repeatable post-deploy production checks

## Quarantined Legacy Templates

These files are kept for backward compatibility, but their public `Template Name` headers have been removed so they are not offered for new page assignments:

- `template-filter-market.php`
- `template-popular.php`
- `template-sitemap.php`

## Templates To Verify In WordPress Admin

These files may still be assigned to pages from the WordPress dashboard. Do not delete them until you confirm actual usage in the admin:

- `template-filter-free.php`
- `template-wide.php`
- `links.php`
- `image.php`
- `frontend-menu.php`

## Legacy Assets

Unused older JavaScript files have been moved to `legacy/js/` instead of being deleted. Active frontend scripts should live in `js/` and be loaded from `inc/assets.php`.

## Repository Status

This repository was initialized from the currently functional live theme and will continue from that baseline.
