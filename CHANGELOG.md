# PDF Generator for Craft 3 or 4

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
