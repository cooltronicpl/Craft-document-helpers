# PDF Generator for Craft 3 or 4

## 2.0.2 - 2023-12-18

### Changed

- Changed option from `URLmode` into `URLMode`.

### Fixed

- Correct `endPage` settings in the plugin panel.
- Fixed error to save stream with another encoding than `UTF-8` when setting `encoding` with enabled `URLPurify` in `DocumentHelperVariable`.
## 2.0.1 - 2023-12-18

### Fixed

- Restore `URLmode` and correct `startPage` settings in the plugin panel for the `master` branch that was missing in the previous release.
- Correct the `URLMode` description in the README.md file.

## 2.0.0 - 2023-12-18 [CRITICAL]

### Added

- `thumbBestfit` option to adjust thumbnail size with best-fit mode when using `dumbThumb` or `assetThumb` options. This improves the quality and appearance of thumbnails.
- `protection` mode and helper options to enhance password protection of PDFs with ISO, DIN, and BSI standards (**Plus** Plan required). This enhances the security and privacy of documents.
- Option to remove the plugin’s invisible watermark from generated documents (**Plus** Plan required). This improves the aesthetics and professionalism of documents.
- `URLTwigRender` option to render Twig variables on parsed custom URLs as optional. This allows to use of attributes like `{{custom}}` or QR Code as `{{qrimg}}` from parsed custom URL.
- `startPage` is the first page of the PDF to be generated. You can use this option to trim unnecessary pages from the beginning of the PDF.
- `endPage` is the last page of the PDF to be generated. You can use this option to trim unnecessary pages from the end of the PDF.
- `URLMode` option can be set to `curl`, to get URLs via CURL instead of file_get_contents.

### Changed

- [BREAKING] Merged two Craft CMS branches `craft3` and `master` into one package with version `2.0.0`. This requires updating the plugin to use it on Craft CMS 3.x and 4.x.
- Improved the layout and design of the settings control panel tabs. This improves the usability and responsiveness of the settings control panel.
- Refactored code of `DocumentHelperVariable.php` to remove duplicate code and improve readability. This improves the maintainability and compatibility of the code.
- Implemented **Plus** functionality in the plugin to restrict some new features for paying users. These features include the new `protection` mode and the option to remove the plugin’s watermark. Please consider upgrading to the **Plus** plan to use them.

### Fixed

- [SECURITY] Bug in the functions when parsing URL content with unset `URLPurify` option. This bug could allow malicious users to inject arbitrary code into your website by sending specially crafted URLs. This fix prevents this vulnerability by sanitizing the input before passing without setting `URLPurify`.
- Missing `encoding` setting in the plugin panel for Craft CMS 3.x branch.
- Improper handle of a plugin into `document-helpers` which is used in the Plugin Store.

### Removed

- [BREAKING] Discontinued the separate `craft3` branch for Craft CMS 3 and unified the codebase for both Craft CMS 3 and 4 in the `master` branch. This requires updating the plugin to the latest version to use it on either Craft CMS version.

## 1.3.2 - 2023-12-11

### Fixed
[resources](resources)
- Corrected spelling errors in the plugin settings panel descriptions.
- Added checks for the existence of Twig or HTML files in the `template`, `header`, and `footer` parameters.
- Added checks for the existence of external classes of optional QR code generator package.
- Resolved an issue where setting an option to `false` could cause problems with the `isset()` function.

### Added

- Enabled the generation of PDFs from inline code block content or custom URLs as the `template`, `header`, and `footer` parameters.

### Changed

- Included an optional package for scraping external URLs with HTML Purifier.
- Added the `URLPurify` option to sanitize the HTML content scraped from URLs when installed optional package.
- Added the `encoding` option to specify the encoding for the `URLPurify` and `mPDF` input streams.

## 1.3.1 - 2023-12-09

### **Enhancement**

- Made QR Code package as optional and not installed by default.
- Implemented plugin default settings screen.

### **New Feature**

- Added a new option to configure the plugin globally.
- Enabled installing additional packages from the Settings menu.

## 1.3.0 - 2023-11-22

- **Bug Fix**: Fixed a bug that caused incorrect date validation of PDF file.
- **Bug Fix**: Fixed a bug that caused an error when using headers and footers.
- **New Feature**: Added a feature that allows you to include a QR code image in the template using `qrdata` and `qrimg` variables.

## 1.2.5 - 2023-08-24

- **Bug Fix**: Solved a problem that was caused by the new `GenerateThumb` class, which had many errors in the code. This fix makes the thumbnail generation work correctly.

## 1.2.4 - 2023-08-24

- **Bug Fix**: Fixed a problem that occurred due to the introduction of the `ExtendedAsset` class, which had a conflicting declaration of setter and getter methods and an incorrect `getMimeType` getter return type declaration. This fix resolves the issues with thumbnail generation.

## 1.2.3 - 2023-07-16

- **Bug Fix**: Solved a problem that was introduced by importing the `FileHelper` class.

## 1.2.2 - 2023-06-26

- **New Feature**: Introduced additional parameters for image thumbnail generation in `pdfOptions` for `pdfAsset` and `pdf` methods:
  - `assetThumb`: Generates an image thumbnail of the Asset using `pdfAsset`. Individual options include:
    - `assetThumbVolumeHandle`: An optional parameter for specifying the Volume Handle for the Thumbnail.
  - `dumbThumb`: Generates a simple thumbnail image (without Asset) using the `pdf` method.
  - Thumbnail customization options for both `assetThumb` and `dumbThumb` include:
    - `thumbType`: Allows selection of thumbnail format from `jpg`, `gif`, `webp`, `avif`, and `png`.
    - `thumbWidth`: Specifies the width of the thumbnail.
    - `thumbHeight`: Specifies the height of the thumbnail.
    - `thumbPage`: Specifies the page to generate (first page is 0).
    - `thumbTrim`: Trims and centers the page.
    - `thumbBgColor`: Sets the background color of the thumbnail (default is white).
    - `thumbTrimFrameColor`: Changes the color of the trim frame.
- **Bug Fix**: Resolved an issue causing deletion of PDF files with the `pdfAsset` method, even without `assetDelete`. Also fixed minor bugs.
- **Enhancement**: Updated `README.md` with information about the newly added thumbnail generation options.

## 1.2.1 - 2023-06-23

- **Fix**: Corrected the `pluginVendorName` for accurate synchronization with the plugin store.
- **Fix**: Corrected the `composer.json` file for accurate synchronization with the plugin store.
- **Fix**: Made revisions to `README.md` and fixed minor bugs.

## 1.2.0 - 2023-06-22

- **Feature**: Introduced `pdfAsset` method, which can create, update, or retrieve a Craft CMS Asset.
- **Enhancement**: Added additional options to `pdfOptions` for the new `pdfAsset` method:
  - `assetDelete`: Deletes the temporary file after adding the Asset,
  - `assetTitle`: Sets a custom title for the stored Asset,
  - `assetFilename`: Sets a custom filename for the stored Asset,
  - `assetSiteId`: Sets a custom siteId for the Asset.
- **Feature**: Updated README with descriptions of the new method, options, and a guide on generating thumbnails for PDF Assets.
- **Enhancement**: Switched to the Craft License for legal reasons.

## 1.1.1 - 2023-06-13

- Introduced an example for right-to-left (RTL) text
- Expanded pdfOptions with support for Watermark Images and Text
- Implemented functionality for automatic Table of Contents generation
- Implemented functionality for automatic Bookmarks generation

## 1.1.0 - 2023-05-02

- Performed necessary code refactoring for the plugin.
- Introduced a new attribute to specify a custom temporary directory path.
- Added capability to set the PDF orientation to either portrait or landscape mode.
- Expanded format options beyond the default A4, effective since version 1.0.4.
- Included an example of a PDF thumbnail in the README.md documentation.

## 1.0.4 - 2022-11-14

- Update CHANEGLOG and README

## 1.0.3 - 2022-11-12

- Feature update for Craft CMS 4
- Add author option
- Add keywords option
- Enable manual page break mode (no_auto_page_break) option
- Add option to password protect of PDF

## 1.0.2 - 2022-11-10

- Merged with @AramLoosman
- Remove unused function
- Add custom variables to footer and header
- Changed fontDirs variable in plugin code to fontDir as in README.md

## 1.0.1 - 2022-11-02

- Minor changes
- Fix some typos in README.md
- Fixed encoding issues and minor mislead of comments in non-latin extended systems in files

## 1.0.0 - 2022-11-01

- Update for Craft 4
- Now autoupdate of files works properly with PHP8.0.x like on examples

## 0.4.2 - 2023-12-11

### Fixed

- Corrected spelling errors in the plugin settings panel descriptions.
- Added checks for the existence of Twig or HTML files in the `template`, `header`, and `footer` parameters.
- Added checks for the existence of external classes of optional QR code generator package.
- Resolved an issue where setting an option to `false` could cause problems with the `isset()` function.

### Added

- Enabled the generation of PDFs from inline code block content or custom URLs as the `template`, `header`, and `footer` parameters.

### Changed

- Included an optional package for scraping external URLs with HTML Purifier.
- Added the `URLPurify` option to sanitize the HTML content scraped from URLs when installed optional package.
- Added the `encoding` option to specify the encoding for the `URLPurify` and `mPDF` input streams.

## 0.4.1 - 2023-12-09

### **Enhancement**

- Made QR Code package as optional and not installed by default.
- Implemented plugin default settings screen.

### **New Feature**

- Added a new option to configure the plugin globally.
- Enabled installing additional packages from the Settings menu.

## 0.4.0 - 2023-11-22

- **Bug Fix**: Fixed a bug that caused incorrect date validation of PDF file.
- **Bug Fix**: Fixed a bug that caused an error when using headers and footers.
- **New Feature**: Added a feature that allows you to include a QR code image in the template using `qrdata` and `qrimg` variables.

## 0.3.2 - 2023-06-26

- **New Feature**: Introduced additional parameters for image thumbnail generation in `pdfOptions` for `pdfAsset` and `pdf` methods:
  - `assetThumb`: Generates an image thumbnail of the Asset using `pdfAsset`. Individual options include:
    - `assetThumbVolumeHandle`: An optional parameter for specifying the Volume Handle for the Thumbnail.
  - `dumbThumb`: Generates a simple thumbnail image (without Asset) using the `pdf` method.
  - Thumbnail customization options for both `assetThumb` and `dumbThumb` include:
    - `thumbType`: Allows selection of thumbnail format from `jpg`, `gif`, `webp`, `avif`, and `png`.
    - `thumbWidth`: Specifies the width of the thumbnail.
    - `thumbHeight`: Specifies the height of the thumbnail.
    - `thumbPage`: Specifies the page to generate (first page is 0).
    - `thumbTrim`: Trims and centers the page.
    - `thumbBgColor`: Sets the background color of the thumbnail (default is white).
    - `thumbTrimFrameColor`: Changes the color of the trim frame.
- **Bug Fix**: Resolved an issue causing deletion of PDF files with the `pdfAsset` method, even without `assetDelete`. Also fixed minor bugs.
- **Enhancement**: Updated `README.md` with information about the newly added thumbnail generation options.

## 0.3.1 - 2023-06-23

- **Fix**: Corrected the `composer.json` file for accurate synchronization with the plugin store.
- **Fix**: Made revisions to `README.md` and fixed minor bugs.

## 0.3.0 - 2023-06-22

- **Feature**: Introduced `pdfAsset` method, which can create, update, or retrieve a Craft CMS Asset.
- **Enhancement**: Added additional options to `pdfOptions` for the new `pdfAsset` method:
  - `assetDelete`: Deletes the temporary file after adding the Asset
  - `assetTitle`: Sets a custom title for the stored Asset
  - `assetFilename`: Sets a custom filename for the stored Asset
  - `assetSiteId`: Sets a custom siteId for the Asset
- **Feature**: Updated README with descriptions of the new method, options, and a guide on generating thumbnails for PDF Assets.
- **Enhancement**: Switched to the Craft License for legal reasons.

## 0.2.1 - 2023-06-13

- Introduced an example for right-to-left (RTL) text
- Expanded pdfOptions with support for Watermark Images and Text
- Implemented functionality for automatic Table of Contents generation
- Implemented functionality for automatic Bookmarks generation

## 0.2.0 - 2023-05-02

- Performed necessary code refactoring for the plugin.
- Introduced a new attribute to specify a custom temporary directory path.
- Added capability to set the PDF orientation to either portrait or landscape mode.
- Expanded format options beyond the default A4, effective since version 1.0.4.
- Included an example of a PDF thumbnail in the README.md documentation.

## 0.1.1 - 2022-11-14

- Update Icon's, CHANEGLOG and README for Craft CMS 3

## 0.1.0 - 2022-11-12

- Feature update for Craft CMS 3
- Add author option
- Add keywords option
- Enable manual page break mode (no_auto_page_break) option
- Add option to password protect of PDF

## 0.0.9 - 2021-11-11 [CRITICAL]

- Fix of fontDir bug, thanks to @iwe-hi

## 0.0.8 - 2021-11-10

- Merged with @AramLoosman
- remove unused function
- add custom variables to footer and header
- changed fontDirs variable in plugin code to fontDir as in README.md

## 0.0.7 - 2022-02-01

- Add custom fonts to include in template

## 0.0.6 - 2021-10-14

- Added custom title to attributes
- Added custom variables to attributes
- Update README.MD

## 0.0.5 - 2021-06-21

- Updated README.MD
- Rename the plugin

## 0.0.2 - 2021-03-21

- Initial release
- PDF Generator for Craft 3
