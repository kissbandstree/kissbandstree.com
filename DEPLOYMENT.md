# Deployment

The site deploys through GitHub Actions using SFTP.

Open GitHub, then go to `Settings` > `Secrets and variables` > `Actions`.

Add these repository secrets:

- `SFTP_HOST`
- `SFTP_USERNAME`
- `SFTP_PASSWORD`

Add these repository variables:

- `SFTP_REMOTE_DIR`
- `SFTP_PORT`

Use port `22` unless the host says something else.

Normal deploys run automatically when `main` is pushed to GitHub.

The normal deploy uploads only site files changed in the latest commit. If a tracked site file is deleted or renamed in Git, the workflow removes the old server file too.

To run a deploy by hand, open `Actions` > `Deploy site` > `Run workflow`.

Use mode `changed` for a fast latest-commit deploy.

Use mode `full` if the server needs a repair sync. Full sync checks the whole site tree, so it is slower. Full sync does not delete remote-only files.

These local repository files are not uploaded:

- `.git/`
- `.github/`
- `.vscode/`
- `.gitignore`
- `DEPLOYMENT.md`
- `README.md`
- `LICENCE`