Changelog
=========

1.3.3 (November 6, 2026)
--------------------
- Fix: Restrict `<script>` tag in homepage content to system administrators only

1.3.2 (October 8, 2025)
--------------------
- Fix: Text color inside buttons (when using the HTML editor)

1.3.1 (September 22, 2025)
--------------------
- Fix: Images overflow in 1.18 - replace TinyMCE Bootstrap 3 class `img-responsive` by bootstrap 5 class `img-fluid`

1.3.0 (September 2, 2025)
--------------------
- Enh: Migration to Bootstrap 5 for HumHub 1.18
- Enh: Use new `ControllerHelper::isActivePath` & `$module?->isEnabled`
- Fix: Compatibility with Custom Pages 1.11+ for Dashboard widgets

1.2.3 (August 6, 2025)
--------------------
- Enh: Allow `<style>` tags in the HTML content

1.2.2 (June 10, 2025)
--------------------
- Enh: Add "No frame around the content" option
- Fix: Compatibility with Custom Pages 1.11+

1.2.1 (May 17, 2025)
--------------------
- Enh: Add "Birthday" widget

1.2.0 (May 11, 2025)
--------------------
- Enh: Add "Online Users" and "Most Active Users" widgets

1.1.4 (April 15, 2025)
--------------------
- Enh: Add a toggle button to display available tags, to avoid having too much text in the form
- Enh: In the module configuration, add a warning if the Custom Pages module is not enabled

1.1.3 (November 12, 2024)
--------------------
- Fix: Missing translations
- Enh: Use CSS variables to allow compatibility with Dark Mode (see [#7134](https://github.com/humhub/humhub/issues/7143))
- Enh: Add GitHub HumHub PHP workflows (tests & CS fixer)
- Fix: link colors shouldn't apply to widgets

1.1.2 (July 17, 2024)
--------------------
- Fix: If "Allow visitors limited access to content without an account" is checked in the administration User settings, when viewing a private space as a guest, the "Login" button doesn't display the login form.

1.1.1 (July 11, 2024)
--------------------
- Fix: If the theme has panel borders, remove double panel borders in HTML homepages

1.1 (May 17, 2024)
--------------------
- Enh: Add Title. If not empty, it is possible to collapse the Homepage Content.
- Enh: Add `HomepageContent` widget.
- Fix: dropdown menus text color
- Chg: Default layout is Content on the left, Dashboard below, and sidebar at the right

1.0 (April 29, 2024)
--------------------
- Enh: Initial release
