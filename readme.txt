=== News PhotoCard Pro ===
Contributors: nexolbd
Tags: news, elementor, social media, template, design
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.11.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create branded 1080×1080 news cards with PhotoCard Studio by Nexol BD.

== Description ==

News PhotoCard Pro turns post headlines and featured images into downloadable PNG cards. PhotoCard Studio provides twelve designs, grouped image and color controls, and a live preview with an editable sample headline.

* Twelve PHP/HTML/CSS templates with individual saved settings.
* Editorial Cream, Midnight Focus, Red Bulletin, Split Dispatch, and Emerald Frame join the original seven styles.
* Per-template headline, date, CTA/footer, and website text sizes with sliders, precise pixel inputs, and Auto defaults (where those elements exist).
* Responsive studio dashboard with visual template selection.
* Image library selectors, hex and RGB/RGBA colors, and custom card text.
* Automatic post button, shortcode, and optional Elementor widget.
* Browser-based 1080×1080 PNG export using bundled html2canvas.
* Locally bundled Solaiman Lipi (default) and Shurjo regular/bold fonts for Bangla typography.
* GitHub release updates through the standard WordPress Updates screen.

Developed and maintained by Nexol BD.
Website: https://nexolbd.com/
Email: info@nexolbd.com
Phone: +8801737963893

== Installation ==

1. Upload the news-photocard-pro folder to wp-content/plugins/ and activate the plugin.
2. Open PhotoCard > License and activate the NX-... license from your Nexol BD order.
3. Choose a design, customize its fields, and select Save changes.
4. Open a published post with a featured image to download its photo card.

Existing 3.6 settings and template option keys are preserved.

== Subscription Activation ==

This version requires an active Nexol subscription, including on existing installations. The API origin is https://nexolbd.com and the integration product code is photocard-pro. Deploy the Nexol Laravel license API before distributing this version. Configure your store product with Download + license activation and this exact product code. Monthly, yearly, and lifetime licenses use the same plugin; validity and renewal are controlled by the API.

PhotoCard > License provides activation, status, expiry, renewal checks, and deactivation. Only administrators can change licenses, with nonce verification on every submission. Keys are stored in non-autoload WordPress options and sent only in HTTPS JSON request bodies to /api/v1/licenses/activate, /check, and /deactivate. Requests include the license key, product code, persistent installation UUID, and site home URL including any subdirectory. No post content is sent to the license API.

Card generation, the design editor, front-end buttons, Elementor rendering, AJAX downloads, and GitHub update downloads require active entitlement. Reader downloads remain public on a licensed site. Invalid subscriptions never delete posts, media, or saved designs. A cached page may retain a button after expiry, but its download request is rejected server-side.

Successful verification is valid for at most six hours, capped by subscription expiry. The hourly WP-Cron job checks when validation is due; feature access also refreshes due checks. WP-Cron depends on traffic. 403/409/422 and unexpected responses disable entitlement; network/5xx/429 errors only retain an existing, unexpired cache. Retries use bounded backoff without extending the original validity period. Refunds and revocations may take up to six hours to reach a cached client.

After renewal, select Check subscription / renewal. A failed activation can be retried with Retry activation. To change keys, deactivate first. Remove local key is a recovery option for an incorrect key or unavailable server; it does not free a server activation slot.

Before moving the site, deactivate its license. If the URL has already changed, Deactivate still sends the original URL and UUID; activate again to create a new identity. A database clone with a different URL cannot reuse the old entitlement. Each multisite subsite needs its own activation. Network plugin updates use the current site's entitlement.

Deactivate and release installation before removing the plugin if you want to free the license slot. Normal WordPress plugin deactivation/uninstall clears license cron jobs and retains credentials/settings for recovery without contacting the server. Reinstalling preserves the original identity.

The Nexol API does not provide automatic update packages. The existing GitHub updater remains the release source, with subscription checks in this plugin. Delivered PHP/JavaScript is editable by site owners; these checks are not tamper-proof DRM.

Verification: run php tests/license-test.php, php tests/updater-test.php, php tests/typography-test.php, and node tests/template-test.js. License tests mock the HTTP boundary; complete a real activation/check/deactivation with a purchased test license after deploying the API.

== Usage ==

Automatic button: Enable Show automatically on posts and choose before or after content.
Shortcode: [news_photocard_button text="Download PhotoCard"]
Elementor: Add the News PhotoCard Button widget and customize its label, icon, and styles.

Shortcode and widget buttons use the current post or page, which needs a featured image. Reader downloads are available for public, published content without password protection. In the WordPress Posts list, editors can download cards for posts they can edit, including drafts, private posts, and password-protected posts. An active subscription and featured image are required.

Open PhotoCard > Fonts to choose Solaiman Lipi or Shurjo. The same selector is available under Make it yours in the design editor. The preview changes immediately; save to apply the font to all designs and future downloads. Solaiman Lipi remains the default. Both fonts are bundled locally, and PNG export waits for the selected font to load.

Under Make it yours > Text sizing, turn off Auto for a text element and use its slider or enter a pixel size. Headline sizes range from 24–120 px; date, CTA/footer, and website sizes range from 12–60 px. Enable Auto again to restore the original template sizing. Each design saves its own sizes. Templates without website text do not show a website size control. Manual headline sizes override word-count and banner-based font sizes. Larger text can wrap or overflow, so check the preview with your headline before saving.

The studio preview uses sample artwork and a sample headline. It does not change post content. Fonts and CSS rendering can differ slightly between browser preview and PNG export. Remote images must allow cross-origin access for image export.

Template selection retains drafts while switching designs in the current session. Saving persists the selected template's fields. Save each design before leaving the page to keep its changes.

== GitHub Updates ==

Default repository: https://github.com/programmerashraful/news-photocard-pro
The repository URL can be changed under PhotoCard > Plugin updates. Leave it blank to disable GitHub checks. On multisite, only administrators with network plugin management permissions can change this shared source.

To publish an update:
1. Put the complete plugin at the GitHub repository root, including news-photocard-pro.php, admin/, assets/, includes/, and templates/.
2. Update the plugin header version, UDNPC_VERSION, and this readme's Stable tag together.
3. Commit and push the files, then create and push a stable numeric tag, for example v3.11.1.
4. The included GitHub Actions workflow tests the code, builds news-photocard-pro.zip, and publishes a GitHub Release for the tag. A commit or tag without a published Release is not an update.
5. WordPress checks the latest release and offers newer versions through Dashboard > Updates or Plugins. The release metadata is cached for one hour; failed checks are cached for five minutes.

The updater prefers the news-photocard-pro.zip release asset, with the tagged source archive as a fallback, and preserves the installed plugin folder name. Keep the plugin at the repository root. GitHub's latest release endpoint supplies the release; draft and prerelease versions are excluded.

This updater supports public GitHub repositories without customer tokens. Private repositories are not supported.

The GitHub Update URI header identifies this Nexol BD distribution and prevents WordPress.org from replacing it with the similarly named upstream plugin. GitHub is the actual release source. Updates remain under the site's normal WordPress update controls.

== External Services ==

Nexol BD (https://nexolbd.com/) receives the license key, product code, installation UUID, and site URL for subscription activation, periodic validation, and deactivation as described above. Contact info@nexolbd.com for license and order support.

When GitHub updates are configured, the plugin contacts the configured GitHub host to read release metadata and, when an update is requested, download the release's repository archive. The default host is github.com. No post content or generated image is sent to GitHub.

GitHub terms: https://docs.github.com/en/site-policy/github-terms/github-terms-of-service
GitHub privacy: https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement

The font, preview illustrations, and html2canvas are bundled locally. User-selected remote image URLs may contact their respective hosts. Generated cards are downloaded in the browser and are not uploaded to this plugin's server.

== Changelog ==

= 3.11.1 =
* Added a PhotoCard download button to the WordPress Posts list, with permission, subscription, and featured-image checks.
* Allow authorized editors to generate cards for unpublished and protected posts.
* Corrected the Prothom Alo template canvas to 1080 x 1080 pixels.
* Refined the admin menu icon sizing and post-list download button styling.

= 3.11.0 =
* Added a PhotoCard icon and Nexol BD creator branding.
* Switched updates to programmerashraful/news-photocard-pro on GitHub.
* Added automatic release packaging when a version tag is pushed.

= 3.10.0 =
* Added Nexol subscription activation, validation, renewal checks, and installation release.
* Require active entitlement for the editor, card downloads, and GitHub updates.
* Added bounded license caching, background checks, outage backoff, and site migration handling.
* Preserve existing content and designs when subscriptions expire.

= 3.9.1 =
* Added PhotoCard > Fonts and a shared font selector in the design editor, with immediate live previews.
* Bundled Shurjo regular and bold; Solaiman Lipi remains the default for existing sites.
* Apply the saved font to all twelve designs and wait for the selected font before PNG export.

= 3.9.0 =
* Added five original layouts: Editorial Cream, Midnight Focus, Red Bulletin, Split Dispatch, and Emerald Frame.
* Added matching design thumbnails, editable section labels and palettes, and all four text size controls for each new style.
* New styles display the site name when no logo is configured and use larger automatic headlines for short titles.
* Added explicit sample SVG dimensions for reliable logo and artwork rendering in PNG exports.

= 3.8.3 =
* Enlarged the Jugantor Teal bottom-left logo from 70px to 90px high.

= 3.8.2 =
* Added Jugantor Teal with a bottom-left logo, right-aligned CTA/domain, and navy-to-teal background.
* Retained the original Jugantor design, sizing controls, and optional ad banner.
* Keep template thumbnails mapped to their designs when new templates are added.

= 3.8.1 =
* Added per-template text sizing with sliders, numeric inputs, Auto reset, and matching preview/export overrides.

= 3.8.0 =
* Nexol BD branding, website, and support contact information.
* New responsive PhotoCard Studio dashboard with live preview and six visual design choices.
* Grouped customization fields, media selectors, draft-aware template switching, and shortcode copying.
* GitHub release updater with version checks, caching, and archive directory normalization.
* Modern download button, accessible loading feedback, and recoverable error states.
* Preserve RGB/RGBA colors and escape template placeholder values.
* Use each button's own post ID and restrict downloads to public published content.

= 3.6.0 =
* Original release with six templates, settings, shortcode, Elementor support, and html2canvas export.

== Credits ==

Current development and support: Nexol BD — https://nexolbd.com/
Original project attribution is preserved in NOTICE.txt.
html2canvas retains its bundled license notice. SolaimanLipi and Shurjo retain their embedded font metadata.

== License ==

GPLv2 or later. You may modify and redistribute this plugin under the same license.
