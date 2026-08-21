# Deploying to WordPress

The plugin is deployed by the `Deploy plugin to WordPress` GitHub Actions
workflow in `.github/workflows/deploy-wordpress.yml`.

## When it runs

The workflow runs automatically when plugin code reaches `main`, which in
practice means immediately after a branch is merged. It also runs on demand from
the **Actions** tab.

Only these paths trigger a deploy:

    tbt-quotes.php
    uninstall.php
    readme.txt
    assets/**
    data/**
    bin/build-plugin.sh
    .github/workflows/deploy-wordpress.yml

A change that touches only `README.md`, `docs/` or `tests/` does not touch the
live site. Use the manual run if you ever want to deploy anyway.

## Required secrets

Set these under **Settings > Secrets and variables > Actions**:

| Secret | Meaning |
| --- | --- |
| `FTP_SERVER` | Hostname of the FTP server. |
| `FTP_USERNAME` | FTP account name. |
| `FTP_PASSWORD` | FTP account password. |
| `FTP_SERVER_DIR` | Absolute path of the plugin folder on the server. |

`FTP_SERVER_DIR` must point at the plugin folder itself, not at the plugins
directory above it. For a typical shared host that is:

    /public_html/wp-content/plugins/tbt-quotes/

A trailing slash is added automatically if it is missing, so both spellings
work. If the secret is empty the workflow stops before connecting.

The connection uses FTPS, so credentials are not sent in the clear.

## What actually gets uploaded

`bin/build-plugin.sh` stages the plugin into `build/tbt-quotes/` and only the
contents of that folder are uploaded.

The script works from an **allowlist**:

    tbt-quotes.php
    uninstall.php
    readme.txt
    assets/
    data/

Anything not on that list stays in the repository. Tests, CI configuration,
`docs/` and `README.md` never reach `wp-content/plugins`, and a file added to
the repository later cannot appear on the live site unless it is added to that
list on purpose.

## Checks that run before uploading

A bad file in `wp-content/plugins` can take a WordPress site down, and FTP has
no rollback, so the workflow verifies the code first and stops on any failure:

1. `php -l` on every PHP file.
2. Every `tests/*.test.mjs` file.
3. `data/quotes.json` is parsed.
4. The version in the plugin header, the `VERSION` constant and the
   `Stable tag` in `readme.txt` must all match.

Point 4 matters because the version is used as the CSS cache-buster. If the
three disagree the build stops rather than shipping a stylesheet that returning
visitors keep loading from cache.

## Doing a dry run first

Before the first real deploy, confirm the credentials and the path without
writing anything:

1. Open **Actions > Deploy plugin to WordPress**.
2. Choose **Run workflow**.
3. Tick **Connect and list the changes without uploading anything**.

The log shows what it would transfer. If the path is wrong you will see it here
instead of finding out on the live site.

## Building the same package locally

    bin/build-plugin.sh

This produces:

- `build/tbt-quotes/` - the exact folder the workflow uploads.
- `build/tbt-quotes.zip` - installable through **Plugins > Add New Plugin >
  Upload Plugin**.

Useful for testing on a staging site, or for installing by hand if FTP is down.

## Notes

- **First run uploads everything.** After that, the action keeps a
  `.ftp-deploy-sync-state.json` file inside the plugin folder on the server and
  transfers only what changed. Leave that file alone. Deleting it is harmless
  but forces a full re-upload.
- **Deploys do not overlap.** A `concurrency` group serialises them, so a second
  merge waits for the first deploy to finish rather than corrupting the sync
  state.
- **Activation is still manual, once.** The workflow copies files into place; it
  does not activate the plugin. Activate it once under **Plugins**, and later
  deploys update it in place with no further action.
- **Adding an approval step.** The job runs in a `production` environment. To
  make deploys pause for a manual approval, add a required reviewer under
  **Settings > Environments > production**. Until you do, nothing is held up.
