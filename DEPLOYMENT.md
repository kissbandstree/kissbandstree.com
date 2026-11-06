# Deployment

The site deploys through GitHub Actions using lftp over SFTP.

Open GitHub, then go to `Settings` > `Secrets and variables` > `Actions`.

Add these repository secrets:

- `SFTP_HOST`
- `SFTP_USERNAME`
- `SFTP_PASSWORD`

Add these repository variables:

- `SFTP_PORT`

Use port `22` unless the host says something else.
The workflow defines `SFTP_REMOTE_DIR` as `/home/flamenco/kissbandstree.com`.
The old destination variable and `SERVER_DIR` secret are not used.

Normal deploys run automatically when `main` is pushed to GitHub.

Normal deployment compares with the last successful upload on the same branch,
recovering failed commits. Successful checks and dry runs are not upload
baselines. Missing baselines stop explicitly rather than falling back to full.
If a tracked site file is deleted or renamed, the old server file is removed
after uploads.

Content now lives in `data/*.json`. Deploy these files, `content.php` and
`functions.php` with the PHP pages. The workflow uploads JSON collections
first, the content loader second, shared helpers third, and other changed
files next. It deletes obsolete files only after all uploads. Full sync also
uploads JSON, the loader and shared helpers before other site files.
For the first text-to-JSON deployment, use `changed` mode so the tracked old
text files are removed; full sync leaves remote-only files in place.

Tests, migration and benchmark tools live under `.github/`, so the existing
push filters and both deployment modes exclude them. Do not upload benchmark
outputs, pre-migration snapshots or research files.

`photos_bands_originals/` is local only while the archive awaits cleanup. It is
ignored by Git and excluded from changed and full deployments, including remote
deletions. These exclusions do not delete local originals or existing server
copies, and do not purge older Git history. Published images in `photos_bands/`
and `photos_bands_large/` remain deployable.

`.ftpignore` supplies the repository/archive exclusions for both GitHub
deployment modes and file-transfer clients that support it.

Run `bash .github/tests/deployment.sh .github/workflows/deploy.yml` to check
the upload ordering, archive exclusions, failed-commit recovery and quoting.
The tests exercise the Node deployment planner without contacting production.

To run a deploy by hand, open `Actions` > `Deploy site` > `Run workflow`.

Use mode `check` first to verify SFTP access and the destination without changes.
Use `dry-run` to preview counts without connecting. Use `changed` to deploy
changes since the last successful upload.

Use mode `full` if the server needs a repair sync. Full sync checks the whole site tree, so it is slower. Full sync does not delete remote-only files.

These local repository files are not uploaded:

- `.git/`
- `.github/`
- `.vscode/`
- `.ftpignore`
- `.gitignore`
- `DEPLOYMENT.md`
- `README.md`
- `kissbandstree.md`
- `LICENCE`
- `photos_bands_originals/`

Legacy `FTP_SERVER`, `FTP_USERNAME` and `FTP_PASSWORD` secrets
are accepted when corresponding SFTP settings are absent. The protocol remains
SFTP. Verify the path in SFTP before migration. See the shared
[deployment procedure](../SITES.md#deployment).