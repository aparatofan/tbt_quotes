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

## Required variable

Set this under **Settings > Secrets and variables > Actions**, on the
**Variables** tab (not Secrets - it is a public URL, and keeping it unmasked
makes the deploy log readable):

| Variable | Meaning |
| --- | --- |
| `SITE_URL` | Public root URL of the site, e.g. `https://thebluetree.pl`. |

Without it the deploy still runs, but the verification step below is skipped and
the run only warns.

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

## Confirming the deploy actually landed

A green FTP upload is weaker evidence than it looks. `FTP-Deploy-Action` creates
whatever path `FTP_SERVER_DIR` names, uploads into it and reports success. It
has no idea whether that directory is the plugin folder WordPress loads. A typo,
the wrong site root, or a second copy of the plugin under a different folder
name all produce a green run and an unchanged site.

So after uploading, the workflow fetches

    <SITE_URL>/wp-content/plugins/tbt-quotes/assets/css/tbt-quotes.css?ver=<version>

and compares it byte for byte with the stylesheet it just built. The `?ver=` is
the same one the plugin enqueues, so this tests the exact URL a browser asks
for. The job fails if the file cannot be fetched, or if the live copy differs.

It also reads `readme.txt` back and checks the stable tag. Some hosts block
direct `.txt` reads under `wp-content`, so that one only warns.

## When the deploy is green but the site does not change

Work through these in order:

1. **Is there a second plugin folder?** Open **Plugins** and look for two
   entries called *The Blue Tree Quotes*, or browse `wp-content/plugins/` over
   FTP. If the plugin was first installed by hand the folder may be called
   something other than `tbt-quotes`, in which case the deploy has been writing
   to a new, inactive folder all along. Fix `FTP_SERVER_DIR` to match the folder
   that is actually active, or rename the folder to `tbt-quotes` and reactivate.
2. **Is `FTP_SERVER_DIR` the right path?** It must point at the plugin folder
   itself and be correct relative to the FTP account's own home directory, which
   is often already the site root. If the FTP user lands in `/public_html`, then
   `/public_html/wp-content/plugins/tbt-quotes/` resolves to
   `/public_html/public_html/wp-content/plugins/tbt-quotes/`. Run the workflow
   by hand with the dry-run box ticked and read the paths in the log.
3. **Is a cache serving the old files?** The version number is the CSS
   cache-buster, so a version bump handles browser caching. It does not clear a
   host page cache, a CDN, or Divi's combined static CSS. In Divi, clear it
   under **Divi > Theme Options > Builder > Advanced > Static CSS File
   Generation**.
4. **Is PHP opcache holding the old code?** Rare, and only on hosts with
   `opcache.validate_timestamps=0`. Deactivating and reactivating the plugin
   forces a reload.

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
