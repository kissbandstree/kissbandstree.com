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

Run deployment from `Actions` > `Deploy site` > `Run workflow`.

The workflow uploads site files without deleting remote-only files.

These local repository files are not uploaded:

- `.git/`
- `.github/`
- `.vscode/`
- `.gitignore`
- `DEPLOYMENT.md`
- `README.md`
- `LICENCE`