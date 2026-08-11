# Theme auto-deploy to Timeweb (FTP)

This repository can automatically deploy the WordPress theme from:

`wp-content/themes/cpp-courses-theme`

to your Timeweb hosting via GitHub Actions.

## 1) Add repository secrets

In GitHub: `Settings -> Secrets and variables -> Actions -> New repository secret`

Create the following secrets:

- `FTP_SERVER` = `vh352.timeweb.ru`
- `FTP_USERNAME` = `teebkech_cursor`
- `FTP_PASSWORD` = your FTP password
- `FTP_PORT` = `21`

Important for this Timeweb account: FTP root is `wordpress_cpp`, so deploy path in workflow must start from `/public_html/...` (not `/wordpress_cpp/public_html/...`).

## 2) How deployment works

Workflow file: `.github/workflows/deploy-theme.yml`

Deployment starts:

- automatically on push to `cursor/development-environment-setup-8ef2` if theme files changed
- manually from the GitHub Actions page (`Run workflow`)

## 3) Notes

- The deploy uploads only the theme directory, not the full WordPress install.
- If upload fails, check Actions logs first (auth/path/permissions errors are shown there).
