# PDF Generator for Craft 3 or 4

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
