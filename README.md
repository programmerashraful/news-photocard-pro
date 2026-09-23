# News PhotoCard Pro

<img src="assets/plugin-icon.png" alt="News PhotoCard Pro" width="128" height="128">

Create branded 1080 × 1080 news cards with twelve designs, Bangla fonts, live previews, shortcode and Elementor buttons.

**Created by [Nexol BD](https://nexolbd.com/) · Version 3.11.0**

## Install

1. Download `news-photocard-pro.zip` from [Releases](https://github.com/programmerashraful/news-photocard-pro/releases).
2. In WordPress, open **Plugins → Add New → Upload Plugin**, upload the ZIP, and activate.
3. Open **PhotoCard → License** and activate your Nexol `NX-...` key.
4. Choose a design and save. Published posts with featured images can generate photo cards.

Requires WordPress 5.8+ and PHP 7.4+. An active Nexol subscription is required for the studio, downloads, and updates. The API uses `https://nexolbd.com/api/v1/licenses/` with product code `photocard-pro`. Posts and media are never deleted when a license expires. See [readme.txt](readme.txt) for licensing, migration, privacy, and complete usage details.

## GitHub updates

The plugin checks this public repository's **latest published stable release**. A commit alone does not trigger a plugin update. No GitHub account or token is needed on customer sites. Private repositories are not supported by this updater.

For a new release:

1. Bump the plugin header version, `UDNPC_VERSION`, `Stable tag` in readme.txt, and these release notes together.
2. Update `RELEASE-NOTES.md`, run tests, commit and push to `main`.
3. Create and push a matching tag, for example `git tag v3.11.1` then `git push origin v3.11.1`.
4. GitHub Actions verifies the code, packages the plugin, and publishes a release with `news-photocard-pro.zip`.

Updates appear through normal WordPress update checks. Release metadata is cached for one hour; failures for five minutes. A valid subscription is still required. Sites running an older GitLab updater need to install 3.11.0 manually once to switch sources.

## Verification

```sh
php tests/license-test.php
php tests/updater-test.php
php tests/typography-test.php
node tests/template-test.js
```

`php tests/license-wordpress-test.php` additionally checks screens against a local WordPress installation without saving fixture licenses.

## Attribution

Maintained by Nexol BD. GPLv2 or later. Original attribution is retained in [NOTICE.txt](NOTICE.txt).
