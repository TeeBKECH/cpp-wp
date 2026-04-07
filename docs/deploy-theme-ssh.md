# Theme auto-deploy to Beget via SSH

This workflow deploys only theme files from:

`wp-content/themes/cpp-courses-theme/`

to the server path:

`wp-content/themes/cpp-courses-theme/`

## 1) Generate an SSH key pair for GitHub Actions

On your local machine:

`ssh-keygen -t ed25519 -C "github-actions-deploy" -f ./beget_deploy_key`

You will get:

- private key: `beget_deploy_key`
- public key: `beget_deploy_key.pub`

## 2) Add public key on Beget

Add content of `beget_deploy_key.pub` to SSH authorized keys for user:

`teebkech_cursor`

## 3) Add GitHub repository secrets

In GitHub:

`Settings -> Secrets and variables -> Actions -> New repository secret`

Create:

- `SSH_HOST` = `teebkech.beget.tech`
- `SSH_PORT` = `22`
- `SSH_USER` = `teebkech_cursor`
- `SSH_PRIVATE_KEY` = full content of `beget_deploy_key` (private key)

## 4) Trigger deploy

Deployment starts automatically on push to:

`cursor/development-environment-setup-8ef2`

when theme files or workflow file change.
