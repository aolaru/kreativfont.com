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
5. Deploy to the WordPress environment using your normal hosting workflow.

## Automated Deployment

This repository includes a GitHub Actions workflow at `.github/workflows/deploy-theme.yml` that can deploy the theme after every push to `main`.

Add these GitHub repository secrets before enabling it:

- `WP_SSH_HOST`: WordPress.com SFTP/SSH host
- `WP_SSH_PORT`: SSH port
- `WP_SSH_USER`: WordPress.com SSH username
- `WP_SSH_PASSWORD`: WordPress.com SSH password
- `WP_REMOTE_PATH`: Absolute remote path to the live theme folder

Example `WP_REMOTE_PATH`:

```text
/htdocs/wp-content/themes/kreativfont.com/
```

Important: the remote path must point to the theme directory only. The current workflow deploys over SFTP, so treat it as production infrastructure and verify credentials carefully before enabling automatic deployments.

## Important Files

- `functions.php`: lightweight loader for theme modules
- `inc/assets.php`: enqueue logic and asset versioning
- `inc/template-helpers.php`: shared page and font-card helpers
- `header.php`: meta tags, global scripts, analytics, top navigation
- `footer.php`: footer content, dark mode toggle script, `wp_footer()`
- `assets/package.json`: frontend asset tooling entry point
- `assets/Gruntfile.js`: legacy asset build workflow

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
