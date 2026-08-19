---
name: wordpress-org-release
description: Build and verify the WordPress.org submission zip for the Saito Navi plugin (this repository). Use this whenever the user asks to build, rebuild, or regenerate the submission zip, prepare a new version for wordpress.org, or wants the file in their local submission folder updated — including short requests like "régénère le zip" or "prépare la release" after merging changes. Also use it any time code has just been merged to main and the user previously asked for a submission zip in an earlier turn, since that file goes stale on every version bump.
---

# WordPress.org release build (Saito Navi)

Produces the exact zip a reviewer would receive from wordpress.org/plugins/developers/add/,
and (re)validates it — instead of re-deriving the process from scratch each time.

## Why this exists

Building a WordPress.org submission zip by hand is easy to get subtly wrong in ways that
only surface during review: the plugin's `Text Domain` header has to match the slug
WordPress.org will actually assign (for Saito Navi, that's `saito-navi` — "Navi" alone
isn't available), the zip's top-level folder name has to match that same slug, and dev-only
files (`node_modules`, `composer.json`, `phpcs.xml.dist`, `.wordpress-org/`, `scripts/`,
`README.md`, `docker-compose.yml`, `.git/`...) must never leak into the package. Every one
of these has been a real mistake in this project's history — the Text Domain mismatch in
particular was flagged by Plugin Check after multiple releases had already gone out with it
wrong. This skill exists so that class of mistake never happens again silently.

## Steps

1. **Confirm the intended commit is on `main`, working tree clean.** The release always
   reflects `main`, never an in-progress branch — run `git status` and `git log -1` in the
   repo root to confirm before building.

2. **Run the build script**:
   ```bash
   ./scripts/build-wporg-release.sh --copy-to "C:\Users\LTR\Documents\plugi"
   ```
   (Use the WSL-mounted path form, e.g. `/mnt/c/Users/LTR/Documents/plugi`, when running
   from a Linux/WSL shell — `cp`/`rm` need the mounted path, not the raw Windows path.)

   This single script does everything by hand that used to take a dozen separate tool
   calls: reads the version from `navi.php`, **hard-fails if `Text Domain` isn't
   `saito-navi`** (the exact bug this skill was created to stop recurring), assembles the
   package via `rsync --exclude-from=.distignore` into a `build/saito-navi/` folder (never
   `build/navi/` — the folder name inside the zip must match the slug), checks no dev files
   leaked in, zips it as `build/saito-navi-<version>.zip`, verifies the zip's internal
   structure (top-level folder, embedded version, embedded Text Domain), and — if the local
   dev stack is running (`navi_wp_cli`/`navi_wp_web` containers from this repo's
   `docker-compose.yml`) — deploys it and runs the official `wp plugin check` for a final
   pass/fail before anything is copied anywhere.

   If the local dev stack isn't running, pass `--skip-plugin-check` rather than trying to
   start Docker yourself unless the user has already asked for that; report clearly that
   Plugin Check was skipped so the user knows the zip wasn't validated end-to-end.

3. **Read the script's own output rather than re-deriving success/failure.** It already
   prints exactly what a manual verification pass would confirm: file count, root folder
   name, version and Text Domain match, and the Plugin Check result. Don't re-run `wp
   plugin check` again separately or re-inspect the zip's contents by hand — that
   duplicates what the script just did and risks reporting a stale/different result if
   something drifted between the two checks.

4. **If `--copy-to` was used**, the script already removed any older `saito-navi-*.zip` in
   that folder before copying the new one — no separate cleanup step needed. Tell the user
   the final path and version plainly (e.g. "`saito-navi-0.6.0.zip` dans
   `C:\Users\LTR\Documents\plugi`, Plugin Check : 0 erreur").

5. **If the script fails**, its error message already names the exact problem (Text Domain
   mismatch, a leaked dev file, a Plugin Check finding) — fix that specific thing rather
   than reaching for a broader rebuild or reverting unrelated work. A Text Domain mismatch
   means something reintroduced the plain `'navi'` domain in a `__()`/`_e()` call or in
   `navi.php`'s header; grep for `, 'navi' )` (not `'saito-navi'`) across `includes/` and
   `navi.php` to find it.

## When NOT to use this

Don't run this for local development iteration — that's `./scripts/deploy-local.sh`
(deploys straight into the running dev container under its own `saito-navi` plugin folder,
no zip, no WordPress.org-specific checks, much faster). This skill is specifically for
producing the artifact a human will actually upload to WordPress.org.
