# PDF Generator for Craft CMS 3.x and 4.x.

With ❤ by [CoolTRONIC.pl sp. z o.o.](https://cooltronic.pl) developed by [Pawel Potacki](https://potacki.com)

This plugin allows you to generate PDF files from a Craft CMS entry using a Twig template. It supports the creation and download of multiple PDF files from entries within a specific channel section on a website running Craft CMS 3 or 4. You can find example implementations within this README file.

Please note that MPDF for PHP8.x and Craft 4 may require a GD extension.

![Icon](resources/pdf-black.png#gh-light-mode-only)
![Icon](resources/pdf-light.png#gh-dark-mode-only)

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)

  - [Parameters pdf method](#parameters-pdf-method)
    - [Usage Example of pdf method](#usage-example-of-pdf-method)
  - [Parameters pdfAsset method](#parameters-pdfasset-method)
    - [Usage Example of pdfAsset method](#usage-example-of-pdf-method)
  - [Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder](#securely-displaying-pdf-documents-in-the-browser-without-saving-to-the-web-folder)
  - [Variables within a Template](#variables-within-a-template)
  - [Overriding Default Options](#overriding-default-options)
  - [Custom fonts](#custom-fonts)
  - [Returned values](#returned-values)
  - [Example in a loop](#example-in-a-loop)
  - [Twig template example](#twig-template-example)
  - [Adding PDFs to Assets](#adding-pdfs-to-assets)
  - [Including Images in PDF](#including-images-in-pdf)
    - [Thumbnail of Generated PDF on Frontend](#thumbnail-of-generated-pdf-on-frontend)
    - [Generating and Displaying Thumbnails of PDF Assets](#generating-and-displaying-thumbnails-of-pdf-assets)
  - [Custom Title of PDF Document](#custom-title-of-pdf-document)
  - [Custom Variables](#custom-variables)
  - [Custom Page Break in PDF Document](#custom-page-break-in-pdf-document)
  - [Page and All Pages Numbers](#page-and-all-pages-numbers)
  - [Generate PDF File Only When Data of Entry is Changed](#generate-pdf-file-only-when-data-of-entry-is-changed)
  - [About Filename Characters](#about-filename-characters)
  - [Browser Caching Problems of PDF Files in Some Servers and Hosting](#browser-caching-problems-of-pdf-files-in-some-servers-and-hosting)
  - [Multiple PDF Files Downloading with JavaScript on Any Page](#multiple-pdf-files-downloading-with-javascript-on-any-page)
  - [RTL Direction](#rtl-direction)
  - [Add Watermark](#add-watermark)
  - [Generate PDF Table of Contents](#generate-pdf-table-of-contents)
  - [Generate Bookmarks](#generate-bookmarks)

- [Requirements](#requirements)
- [Support](#support)
- [Credits](#credits)
- [License](#license)

## Installation

You can install this plugin by running the following command:

```
composer require cooltronicpl/document-helper
```

## Usage

The plugin can be used as follows:

```
craft.documentHelpers.pdf(template_string, destination, filename, entry, pdfOptions)
```

### Parameters `pdf` method

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory.

- `destination` - This indicates where the PDF file will be generated. It can be one of four options: `file`, `inline`, `download`, or `string`. To download multiple files, refer to the JavaScript example provided in the README.md file.

- `filename` - This is the name of the generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

Method returns string with filename for anchors or string content for PDF files to send content as attachment.

```
{{craft.documentHelper.pdf("template.twig", "file", "document.pdf", entry, pdfOptions)}}
```

#### Usage Example of `pdf` method

```
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf', entry, pdfOptions)}}"
download>
</a>
```

### Parameters `pdfAsset` method

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory.

- `filename` - This is the name of temporary or / and final of generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

- `volumeHandle` - This parameter should contains volume handle name on which we need to add PDF as Craft CMS asset from system.

#### Usage Example of `pdfAsset` method

```twig
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
    <a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">Download your PDF</a>
{% else %}
    File was not generated.
{% endif %}
```

### Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder

Here is an example of a Twig layout that allows you to securely display a PDF document in the browser:

```
						{% set pdfOptions = {
						date: entry.dateUpdated|date('U'),
						header: "_pdf/header.twig",
						footer: "_pdf/footer.twig"
                        } %}
				{% header "Content-Type: application/pdf" %}
{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'inline', '../book_example'  ~ '.pdf', entry, pdfOptions))}}

```

## Variables within a Template

All variables from the entry in a generated template are placed in an 'entry' array:

```
{{entry.VAR}}
```

The title of the current entry can be accessed via:

```
{{title}}
```

### Overriding Default Options

You can override the default options with `pdfOptions` as shown above. Here are the available options:

- `date` - This is disabled by default. If you provide a date (in timestamp format) that is older than the creation date of the file, the existing file will be overwritten.
- `header` - This is the header Twig template, which is disabled by default.
- `footer` - This is the footer Twig template, also disabled by default.
- `margin_top` - The top margin defaults to 30.
- `margin_bottom` - The bottom margin defaults to 30.
- `margin_left` - The left margin defaults to 15.
- `margin_right` - The right margin defaults to 15.
- `mirrorMargins` - This defaults to 0 but can be set to 1.
- `pageNumbers` - This adds page numbers in the footer.
- `title` - This replaces the default title of the generated PDF document.
- `custom` - This allows you to add custom variable or variables.
- `password` - This can be used to add password protection to your PDF. The password should be provided as a string.
- `no_auto_page_break` - This disables automatic system page breaks. This can be useful if you need to manually add page breaks. For example, you can add a custom page to documents with more than one page break using <pagebreak>. This may fix page break issues in some cases, but not all.
- `author` - This sets the author metadata. It should be provided as a string.
- `keywords` - This sets the keyword metadata. It should be provided as a string in the following format: "keyword1, longer keyword2, keyword3".
- `fonts`:
  - `fontdata`, and `fontDir` - These allow you to set custom fonts described above.
- `tempDir` - This sets the path to the temporary directory used for generating PDFs. We have tested this with the main /tmp directory on the server with success. This could potentially improve performance when generating multiple PDFs.
- `landscape` - If this is set, the PDF will be generated in landscape mode.
- `portrait` - If this is set, the PDF will be generated in portrait mode.
- `format` This sets the paper size for the PDF. The default is "A4", but you can set other sizes compatible with MPDF. Other popular formats include:
  - A3
  - A4 (default)
  - A5
  - Letter (8,5 x 11 in)
  - Legal (8,5 x 14 in)
  - Executive (7,25 x 10,5 in)
  - B4
  - B5
- `watermarkImage` - This option creates a watermark using the image file specified by the provided path.
- `watermarkText` - This option creates a watermark using the text provided.
- `autoToC` - This option automatically generates a Table of Contents using the H1-H6 tags in your document.
- `autoBookmarks` - This option automatically generates bookmarks using the H1-H6 tags in your document.
- `assetTitle` - This option allows you to set a custom title for the Asset in the Craft CMS system when using the `pdfAsset` method.
- `assetFilename` - This option allows you to change the target filename of the file in the Craft CMS Asset when using the `pdfAsset` method.
- `assetDelete` - This option enables the deletion of the internally generated file in the `@root` path. Please note that this operation is irreversible and may consume more resources. This is because the Asset is updated and the PDF is generated on every load when using the `pdfAsset` method.
- `assetSiteId` - This option allows you to assign a custom `siteId` to the Asset of `pdfAsset` method. The `siteId` should be passed as a number, representing the ID of the site to which the generated asset should belong.

### Custom fonts

This is an example of how to use custom fonts, specifically Roboto-Regular.ttf and Roboto-Italic.ttf, which should be placed in the config folder:

```
fontdata: { 'roboto' : {
            'R' : 'Roboto-Regular.ttf',
            'I' : 'Roboto-Italic.ttf'
        }},
		fontDir: "{{craft.app.path.configPath}}/",
```

After the update of MPDF, which is used by our PDF Generator, we have resolved an issue with passed paths. Now, you must provide an absolute path on the server to the config directory. Alternatively, you can pass the main folder. For instance, on ISP Config 3.2 host, you can use: `fontDir`: "/var/www/clients/client0/web21/private/config/".

If you're running a single site, it should be an absolute path to the /config folder, like: fontDir: "/path_to/config/".

For XAMPP in Windows hosts, the confirmed format is: fontDir: "file:///C:/xampp/htdocs/craft4/config/".

### Returned values

- If the destination is `inline` or `string`, the plugin returns a string.
- If the destination is `download` or `file`, it returns the filename in the /web folder.

### Example in a loop

```
{% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
	{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
	<a href="
	{{alias('@web')}}/
	{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ item.id ~ '.pdf' ,item, pdfOptions)}}
	" download>
		DOWNLOAD {{item.title}}
	</a>
{% endfor %}
```

### Twig template example

```
<h1>
{{title}}
</h1>
<p>
{{entry.variables}}
</p>
...
```

### Adding PDFs to Assets

To include a PDF file into your assets, you need to specify the filename using the `@root` path. The following example demonstrates its usage with a volume that has the handle `pdffiles`:

```twig
{% set pdfOptions = { date: entry.dateUpdated|date('U') } %}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
    <a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">Download PDF</a>
{% else %}
    File was not generated.
{% endif %}
```

In this example, a timestamp is added to the file URL to ensure the file is refreshed when it changes, beneficial for various caching solutions like [Varnish Cache & Preload](https://plugins.craftcms.com/varnishcache), Blitz, or CDNs like Cloudflare.

Advanced pdfAsset Method Options
By default, the title of the PDF is based on the filename. However, you can override this along with other settings using pdfOptions:

```twig
{% set pdfOptions = {
    ...
    assetTitle: "My Awesome Title",
    assetFilename: "MyAwesome_Filename.pdf",
    assetDelete: true,
    assetSiteId: 2,
    }
%}
```

In this example:

- assetTitle sets a custom title.
- assetFilename sets a custom filename.
- assetDelete when set to true, the file in @root path is deleted and regenerated every time this code runs.
- assetSiteId sets the site ID of the generated PDF to site with 2 id.
  You can also use string variables for assetTitle and assetFilename, such as the entry title:

```twig
{% set pdfOptions = {
    ...
    assetTitle: entry.title,
    assetFilename: entry.title~".pdf",
    }
%}
```

These options give you greater flexibility in customizing the generated PDF assets.

### Including Images in PDF

There are two ways to include images in the PDF template.

If you're using the Image Toolbox plugin, you can include images like this:

```twig
{% set image = entry.photoFromCMS.one() %}
{% set transformSettings = {
    width: 100,
    height: 200,
    mode: 'stretch'
} %}
{% set options = {
  class: '',
  alt: '',
} %}
...
{{craft.images.picture(image, transformSettings, options)}}
```

You can also include an image in a PDF document without a plugin, like this:

```
{% set image = entry.photoFromCMS.first() %}
	{% if image is not null %}
		<img src="{{image.url}}" alt="">
	{% endif %}
```

#### Thumbnail of Generated PDF on Frontend

You can use [PDF Thumbnails by @scandel](https://github.com/scandel/pdfThumbnails) for client-side generation of PDF thumbnails. This requires the files pdfThumbnails.js, pdf.js, and pdf.worker.js to be loaded in the /web folder. The pdf.js and pdf.worker.js files from [PDF.js can be found here](https://mozilla.github.io/pdf.js/getting_started/).

Here's an example:

```
<script src="{{alias('@web')}}/pdfThumbnails.js" data-pdfjs-src="{{alias('@web')}}/pdf_js/build/"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.js"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.worker.js"></script>
				{% header "Cache-Control: no-cache" %}
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/example.pdf'  , entry, pdfOptions))}}">
<img class="img-responsive" data-pdf-thumbnail-file="{{alias('@web')}}/pdf/example.pdf" src="{{alias('@web')}}/pdfjs_placeholder.png">
```

#### Generating and Displaying Thumbnails of PDF Assets

To generate an image thumbnail of a PDF asset, you can use the PDF Transform plugin. Please note, this feature requires ImageMagick.

Here's an example of how to generate and display a thumbnail:

```twig
{% set pdfOptions = { date: entry.dateUpdated|date('U') } %}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example2.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
<a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">
    {% set transformedPdf = craft.pdfTransform.render(asset) %}
    <img src="{{ transformedPdf.url }}?v={{transformedPdf.dateModified|date('U')}}" />
</a>
{% endif %}
```

If you encounter the following error with PDF Transform:

```
attempt to perform an operation not allowed by the security policy `PDF' @ error/constitute.c/IsCoderAuthorized/421
```

This means ImageMagick's security policy is preventing operations on PDF files. To fix this, you'll need to modify the policy.xml file located at `/etc/ImageMagick-6/policy.xml` or `/etc/ImageMagick-7/policy.xml`, depending on your ImageMagick version.

Find and modify the policy related to PDFs to allow the desired operation. Be cautious, as changing this file can have security implications. Make sure you understand the risks and consequences.

Here's an example of how to change a policy from disallowing all operations to allowing read and write operations:

```xml
<!-- Before -->
<policymap>
  <policymap domain="coder" rights="none" pattern="PDF" />
</policymap>

<!-- After -->
<policymap>
  <policymap domain="coder" rights="read|write" pattern="PDF" />
</policymap>
```

If you're unsure about modifying this file, consider reaching out to your hosting provider's support team for assistance.

### Custom Title of PDF Document

To set a custom title for your PDF, use the `title` option in the `pdfOptions` as follows:

```
{% set pdfOptions = {
	...,
	title: "My awesome Title"
} %}
```

### Custom Variables

#### String or Number

To pass a string or number to the PDF template, set it as a `custom` variable in the `pdfOptions`:

```
{% set pdfOptions = {
	...,
	custom: variable
} %}
```

Then, in your PDF template, you can call upon this custom variable:

```
{{custom}}
```

#### Arrays

If you want to pass an array to the PDF template, define the array in the `custom` variable in the `pdfOptions`:

```
{% set pdfOptions = {
	...,
		custom: {
       slug: entry.slug,
       created: entry.dateCreated,
       ...
    }
} %}
```

You can then access the array variables in your PDF template:

```
{{custom.slug}}
{{custom.created.format('d/m/Y')}}
```

### Custom Page Break in PDF Document

You can add a page break in your HTML content using MPDF tags. The following tag represents a page break:

```
<pagebreak>
```

### Page and All Pages Numbers

To include the current page number, use the `{PAGENO}` tag:

```
{PAGENO}
```

To include the total number of pages in the document, use the `{nbpg}` tag:

```
{nbpg}
```

### Generate PDF File Only When Data of Entry is Changed

Use the following `pdfOptions` to ensure that a PDF is only generated when the data in an entry has been updated:

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
```

### About Filename Characters

When selecting a filename for your PDF, ensure that you only use safe characters. The following characters are not allowed in Windows filenames: ":", "/", "?", "|", "<", ">" or "/".

### Browser Caching Problems of PDF Files in Some Servers and Hosting

If you are experiencing issues with your server or hosting caching PDF files, you can use the [Static Files Autoversioning](https://plugins.craftcms.com/craft3-files-autoversioning) plugin. This plugin adds a timestamp to your PDF, helping to avoid caching issues.

```
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/book'  ~ '.pdf'  ,entry, pdfOptions))}}">LINK </a>
```

With this plugin, your PDF will have a timestamp and any caching policy problems with your hosting will be resolved. The following is an example of what the PDF link will look like:

```
<a href="http://some-domain.com/pdf/book.pdf?v=1668157143">LINK </a>
```

### Multiple PDF Files Downloading with JavaScript on Any Page

Obtaining user permission may be necessary for browsers to download multiple files. Below is a straightforward example of downloading static files:

```
<script>
{% set pdfOptions = {
    date: entry.dateUpdated|date('U')
} %}
var files = [
    "{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date('Y-m-d') ~ random(10) ~ '.pdf', entry, pdfOptions)}}",
    "{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date('Y-m-d') ~ random(10) ~ '.pdf', entry, pdfOptions)}}"
];
for (var i = files.length - 1; i >= 0; i--) {
    var a = document.createElement("a");
    a.target = "_blank";
    a.download = "download";
    a.href = files[i];
    a.click();
};
</script>
```

Example with a Loop:

```
{% set pdfOptions = {
    date: entry.dateUpdated|date('U')
} %}
<script>
var files = [
{% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
    "{{alias('@web')}}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ item.id ~ '.pdf', item, pdfOptions)}}"
    {% if loop.last %}{% else %}, {% endif %}
{% endfor %}
];
for (var i = files.length - 1; i >= 0; i--) {
    var a = document.createElement("a");
    a.target = "_blank";
    a.download = "download";
    a.href = files[i];
    a.click();
};
</script>
```

### RTL Direction

The direction of the text can be set in mPDF by using the HTML dir attribute in your HTML Twig template markup. For example:

```
<div dir="rtl">This is some text in a right-to-left language.</div>
```

#### Language

To specify a language in mPDF, you can use the lang attribute in your HTML. For example:

```
<div lang="ar">هذا نص باللغة العربية</div>
```

#### Font

In order for mPDF to display the correct characters, you'll also need to use a font that supports the characters of the language you're using. mPDF comes with several fonts that support a wide range of characters. You can set the font using the CSS font-family property. For example, to use the 'Arial' font:

```
div {
    font-family: 'Arial';
}
```

#### RTL full example

```
<div dir="rtl" lang="ar" style="font-family: Arial;">هذا نص باللغة العربية</div>
```

### Add Watermark

Watermarks can be added to your PDFs using mPDF parameters. This can be either in the form of an image or text.

#### Add Image Watermark

You can include a PNG or JPG image as a watermark in your PDF. Specify the path and file extension of your image in the 'watermarkImage' parameter.

For example:

```
{% set pdfOptions = {
    'watermarkImage': 'path/to/your/image.ext'
} %}
```

Replace 'path/to/your/image.ext' with the actual path and file extension of your image.

#### Add Text Watermark

You can also add a text watermark to your PDF. Simply specify your desired text in the 'watermarkText' parameter.

For example:

```
{% set pdfOptions = {
    'watermarkText': 'My text watermark example'
} %}
```

Replace 'My text watermark example' with the actual text you want to use as a watermark.

### Generate PDF Table of Contents

You have the ability to create your own Table of Contents as per the guidelines in the [mPDF ToC documentantion](https://mpdf.github.io/what-else-can-i-do/table-of-contents.html).

Additionally, we can enable automatic generation of a Table of Contents from H1-H6 tags present in your PDF document. You can activate this feature by setting the `autoToC` option to true.

For instance:

```
{% set pdfOptions = {
    'autoToC': true
} %}
```

### Generate Bookmarks

Manually adding bookmarks can be done with the bookmark tag `<bookmark content="Text" />`. The content attribute is used to set the text of the bookmark. You can also use the optional `level` attribute to set the nesting level of the bookmark.

Additionally, we can enable automatic generation of a Bookmarks from H1-H6 tags present in your PDF document. You can activate this feature by setting the `autoBookmarks` option to true.

For instance:

```
{% set pdfOptions = {
    'autoBookmarks': true
} %}
```

## Requirements

Craft CMS >= 3.0.0 for 0.x branch
Craft CMS >= 4.0.0 for 1.x branch

## Support

For inquiries regarding custom PDF work, such as generating templates or modifying plugins, please reach out to us via [our contact page](https://cooltronic.pl/contact/).

## Credits

Special thanks to the developers and testers who have contributed to this project and helped identify and fix bugs:

- [@mokopan](https://github.com/mokopan)
- [@AramLoosman](https://github.com/AramLoosman)
- [@iwe-hi](https://github.com/iwe-hi)

## License

This project is licensed under the Craft License. See the [LICENSE.md](https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md) file for details.


You can install this plugin by running the following command:

```
composer require cooltronicpl/document-helper
```

## Usage

The plugin can be used as follows:

```
craft.documentHelpers.pdf(template_string, destination, filename, entry, pdfOptions)
```

### Description of Parameters `pdf` method

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory.

- `destination` - This indicates where the PDF file will be generated. It can be one of four options: `file`, `inline`, `download`, or `string`. To download multiple files, refer to the JavaScript example provided in the README.md file.

- `filename` - This is the name of the generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

Method returns string with filename for anchors or string content for PDF files to send content as attachment.

```
{{craft.documentHelper.pdf("template.twig", "file", "document.pdf", entry, pdfOptions)}}
```

#### Usage Example of `pdf` method

```
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf', entry, pdfOptions)}}"
download>
</a>
```

### Description of Parameters `pdfAsset` method

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory.

- `filename` - This is the name of temporary or / and final of generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

- `volumeHandle` - This parameter should contains volume handle name on which we need to add PDF as Craft CMS asset from system.

#### Usage Example of `pdfAsset` method

```twig
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
    <a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">Download your PDF</a>
{% else %}
    File was not generated.
{% endif %}
```

### Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder

Here is an example of a Twig layout that allows you to securely display a PDF document in the browser:

```
						{% set pdfOptions = {
						date: entry.dateUpdated|date('U'),
						header: "_pdf/header.twig",
						footer: "_pdf/footer.twig"
                        } %}
				{% header "Content-Type: application/pdf" %}
{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'inline', '../book_example'  ~ '.pdf', entry, pdfOptions))}}

```

## Variables within a Template

All variables from the entry in a generated template are placed in an 'entry' array:

```
{{entry.VAR}}
```

The title of the current entry can be accessed via:

```
{{title}}
```

### Overriding Default Options

You can override the default options with `pdfOptions` as shown above. Here are the available options:

- `date` - This is disabled by default. If you provide a date (in timestamp format) that is older than the creation date of the file, the existing file will be overwritten.
- `header` - This is the header Twig template, which is disabled by default.
- `footer` - This is the footer Twig template, also disabled by default.
- `margin_top` - The top margin defaults to 30.
- `margin_bottom` - The bottom margin defaults to 30.
- `margin_left` - The left margin defaults to 15.
- `margin_right` - The right margin defaults to 15.
- `mirrorMargins` - This defaults to 0 but can be set to 1.
- `pageNumbers` - This adds page numbers in the footer.
- `title` - This replaces the default title of the generated PDF document.
- `custom` - This allows you to add custom variable or variables.
- `password` - This can be used to add password protection to your PDF. The password should be provided as a string.
- `no_auto_page_break` - This disables automatic system page breaks. This can be useful if you need to manually add page breaks. For example, you can add a custom page to documents with more than one page break using <pagebreak>. This may fix page break issues in some cases, but not all.
- `author` - This sets the author metadata. It should be provided as a string.
- `keywords` - This sets the keyword metadata. It should be provided as a string in the following format: "keyword1, longer keyword2, keyword3".
- `fonts`:
  - `fontdata`, and `fontDir` - These allow you to set custom fonts described above.
- `tempDir` - This sets the path to the temporary directory used for generating PDFs. We have tested this with the main /tmp directory on the server with success. This could potentially improve performance when generating multiple PDFs.
- `landscape` - If this is set, the PDF will be generated in landscape mode.
- `portrait` - If this is set, the PDF will be generated in portrait mode.
- `format` This sets the paper size for the PDF. The default is "A4", but you can set other sizes compatible with MPDF. Other popular formats include:
  - A3
  - A4 (default)
  - A5
  - Letter (8,5 x 11 in)
  - Legal (8,5 x 14 in)
  - Executive (7,25 x 10,5 in)
  - B4
  - B5
- `watermarkImage` - This option creates a watermark using the image file specified by the provided path.
- `watermarkText` - This option creates a watermark using the text provided.
- `autoToC` - This option automatically generates a Table of Contents using the H1-H6 tags in your document.
- `autoBookmarks` - This option automatically generates bookmarks using the H1-H6 tags in your document.
- `assetTitle` - This option allows you to set a custom title for the Asset in the Craft CMS system when using the `pdfAsset` method.
- `assetFilename` - This option allows you to change the target filename of the file in the Craft CMS Asset when using the `pdfAsset` method.
- `assetDelete` - This option enables the deletion of the internally generated file in the `@root` path. Please note that this operation is irreversible and may consume more resources. This is because the Asset is updated and the PDF is generated on every load when using the `pdfAsset` method.
- `assetSiteId` - This option allows you to assign a custom `siteId` to the Asset of `pdfAsset` method. The `siteId` should be passed as a number, representing the ID of the site to which the generated asset should belong.

### Custom fonts

This is an example of how to use custom fonts, specifically Roboto-Regular.ttf and Roboto-Italic.ttf, which should be placed in the config folder:

```
fontdata: { 'roboto' : {
            'R' : 'Roboto-Regular.ttf',
            'I' : 'Roboto-Italic.ttf'
        }},
		fontDir: "{{craft.app.path.configPath}}/",
```

After the update of MPDF, which is used by our PDF Generator, we have resolved an issue with passed paths. Now, you must provide an absolute path on the server to the config directory. Alternatively, you can pass the main folder. For instance, on ISP Config 3.2 host, you can use: `fontDir`: "/var/www/clients/client0/web21/private/config/".

If you're running a single site, it should be an absolute path to the /config folder, like: fontDir: "/path_to/config/".

For XAMPP in Windows hosts, the confirmed format is: fontDir: "file:///C:/xampp/htdocs/craft4/config/".

### Returned values

- If the destination is `inline` or `string`, the plugin returns a string.
- If the destination is `download` or `file`, it returns the filename in the /web folder.

### Example in a loop

```
{% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
	{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
	<a href="
	{{alias('@web')}}/
	{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ item.id ~ '.pdf' ,item, pdfOptions)}}
	" download>
		DOWNLOAD {{item.title}}
	</a>
{% endfor %}
```

### Twig template example

```
<h1>
{{title}}
</h1>
<p>
{{entry.variables}}
</p>
...
```

### Adding PDFs to Assets

To include a PDF file into your assets, you need to specify the filename using the `@root` path. The following example demonstrates its usage with a volume that has the handle `pdffiles`:

```twig
{% set pdfOptions = { date: entry.dateUpdated|date('U') } %}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
    <a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">Download PDF</a>
{% else %}
    File was not generated.
{% endif %}
```

In this example, a timestamp is added to the file URL to ensure the file is refreshed when it changes, beneficial for various caching solutions like [Varnish Cache & Preload](https://plugins.craftcms.com/varnishcache), Blitz, or CDNs like Cloudflare.

Advanced pdfAsset Method Options
By default, the title of the PDF is based on the filename. However, you can override this along with other settings using pdfOptions:

```twig
{% set pdfOptions = {
    ...
    assetTitle: "My Awesome Title",
    assetFilename: "MyAwesome_Filename.pdf",
    assetDelete: true,
    assetSiteId: 2,
    }
%}
```

In this example:

- assetTitle sets a custom title.
- assetFilename sets a custom filename.
- assetDelete when set to true, the file in @root path is deleted and regenerated every time this code runs.
- assetSiteId sets the site ID of the generated PDF to site with 2 id.
  You can also use string variables for assetTitle and assetFilename, such as the entry title:

```twig
{% set pdfOptions = {
    ...
    assetTitle: entry.title,
    assetFilename: entry.title~".pdf",
    }
%}
```

These options give you greater flexibility in customizing the generated PDF assets.

### Including Images in PDF

There are two ways to include images in the PDF template.

If you're using the Image Toolbox plugin, you can include images like this:

```twig
{% set image = entry.photoFromCMS.one() %}
{% set transformSettings = {
    width: 100,
    height: 200,
    mode: 'stretch'
} %}
{% set options = {
  class: '',
  alt: '',
} %}
...
{{craft.images.picture(image, transformSettings, options)}}
```

You can also include an image in a PDF document without a plugin, like this:

```
{% set image = entry.photoFromCMS.first() %}
	{% if image is not null %}
		<img src="{{image.url}}" alt="">
	{% endif %}
```

#### Thumbnail of Generated PDF on Frontend

You can use [PDF Thumbnails by @scandel](https://github.com/scandel/pdfThumbnails) for client-side generation of PDF thumbnails. This requires the files pdfThumbnails.js, pdf.js, and pdf.worker.js to be loaded in the /web folder. The pdf.js and pdf.worker.js files from [PDF.js can be found here](https://mozilla.github.io/pdf.js/getting_started/).

Here's an example:

```
<script src="{{alias('@web')}}/pdfThumbnails.js" data-pdfjs-src="{{alias('@web')}}/pdf_js/build/"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.js"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.worker.js"></script>
				{% header "Cache-Control: no-cache" %}
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/example.pdf'  , entry, pdfOptions))}}">
<img class="img-responsive" data-pdf-thumbnail-file="{{alias('@web')}}/pdf/example.pdf" src="{{alias('@web')}}/pdfjs_placeholder.png">
```

#### Generating and Displaying Thumbnails of PDF Assets

To generate an image thumbnail of a PDF asset, you can use the PDF Transform plugin. Please note, this feature requires ImageMagick.

Here's an example of how to generate and display a thumbnail:

```twig
{% set pdfOptions = { date: entry.dateUpdated|date('U') } %}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example2.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
<a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">
    {% set transformedPdf = craft.pdfTransform.render(asset) %}
    <img src="{{ transformedPdf.url }}?v={{asset.transformedPdf.dateModified|date('U')}}" />
</a>
{% endif %}
```

If you encounter the following error with PDF Transform:

```
attempt to perform an operation not allowed by the security policy `PDF' @ error/constitute.c/IsCoderAuthorized/421
```

This means ImageMagick's security policy is preventing operations on PDF files. To fix this, you'll need to modify the policy.xml file located at `/etc/ImageMagick-6/policy.xml` or `/etc/ImageMagick-7/policy.xml`, depending on your ImageMagick version.

Find and modify the policy related to PDFs to allow the desired operation. Be cautious, as changing this file can have security implications. Make sure you understand the risks and consequences.

Here's an example of how to change a policy from disallowing all operations to allowing read and write operations:

```xml
<!-- Before -->
<policymap>
  <policymap domain="coder" rights="none" pattern="PDF" />
</policymap>

<!-- After -->
<policymap>
  <policymap domain="coder" rights="read|write" pattern="PDF" />
</policymap>
```

If you're unsure about modifying this file, consider reaching out to your hosting provider's support team for assistance.

### Custom Title of PDF Document

To set a custom title for your PDF, use the `title` option in the `pdfOptions` as follows:

```
{% set pdfOptions = {
	...,
	title: "My awesome Title"
} %}
```

### Custom Variables

#### String or Number

To pass a string or number to the PDF template, set it as a `custom` variable in the `pdfOptions`:

```
{% set pdfOptions = {
	...,
	custom: variable
} %}
```

Then, in your PDF template, you can call upon this custom variable:

```
{{custom}}
```

#### Arrays

If you want to pass an array to the PDF template, define the array in the `custom` variable in the `pdfOptions`:

```
{% set pdfOptions = {
	...,
		custom: {
       slug: entry.slug,
       created: entry.dateCreated,
       ...
    }
} %}
```

You can then access the array variables in your PDF template:

```
{{custom.slug}}
{{custom.created.format('d/m/Y')}}
```

### Custom Page Break in PDF Document

You can add a page break in your HTML content using MPDF tags. The following tag represents a page break:

```
<pagebreak>
```

### Page and All Pages Numbers

To include the current page number, use the `{PAGENO}` tag:

```
{PAGENO}
```

To include the total number of pages in the document, use the `{nbpg}` tag:

```
{nbpg}
```

### Generate PDF File Only When Data of Entry is Changed

Use the following `pdfOptions` to ensure that a PDF is only generated when the data in an entry has been updated:

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
```

### About Filename Characters

When selecting a filename for your PDF, ensure that you only use safe characters. The following characters are not allowed in Windows filenames: ":", "/", "?", "|", "<", ">" or "/".

### Browser Caching Problems of PDF Files in Some Servers and Hosting

If you are experiencing issues with your server or hosting caching PDF files, you can use the [Static Files Autoversioning](https://plugins.craftcms.com/craft3-files-autoversioning) plugin. This plugin adds a timestamp to your PDF, helping to avoid caching issues.

```
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/book'  ~ '.pdf'  ,entry, pdfOptions))}}">LINK </a>
```

With this plugin, your PDF will have a timestamp and any caching policy problems with your hosting will be resolved. The following is an example of what the PDF link will look like:

```
<a href="http://some-domain.com/pdf/book.pdf?v=1668157143">LINK </a>
```

### Multiple PDF Files Downloading with JavaScript on Any Page

Obtaining user permission may be necessary for browsers to download multiple files. Below is a straightforward example of downloading static files:

```
<script>
{% set pdfOptions = {
    date: entry.dateUpdated|date('U')
} %}
var files = [
    "{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date('Y-m-d') ~ random(10) ~ '.pdf', entry, pdfOptions)}}",
    "{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date('Y-m-d') ~ random(10) ~ '.pdf', entry, pdfOptions)}}"
];
for (var i = files.length - 1; i >= 0; i--) {
    var a = document.createElement("a");
    a.target = "_blank";
    a.download = "download";
    a.href = files[i];
    a.click();
};
</script>
```

Example with a Loop:

```
{% set pdfOptions = {
    date: entry.dateUpdated|date('U')
} %}
<script>
var files = [
{% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
    "{{alias('@web')}}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ item.id ~ '.pdf', item, pdfOptions)}}"
    {% if loop.last %}{% else %}, {% endif %}
{% endfor %}
];
for (var i = files.length - 1; i >= 0; i--) {
    var a = document.createElement("a");
    a.target = "_blank";
    a.download = "download";
    a.href = files[i];
    a.click();
};
</script>
```

### RTL Direction

The direction of the text can be set in mPDF by using the HTML dir attribute in your HTML Twig template markup. For example:

```
<div dir="rtl">This is some text in a right-to-left language.</div>
```

#### Language

To specify a language in mPDF, you can use the lang attribute in your HTML. For example:

```
<div lang="ar">هذا نص باللغة العربية</div>
```

#### Font

In order for mPDF to display the correct characters, you'll also need to use a font that supports the characters of the language you're using. mPDF comes with several fonts that support a wide range of characters. You can set the font using the CSS font-family property. For example, to use the 'Arial' font:

```
div {
    font-family: 'Arial';
}
```

#### RTL full example

```
<div dir="rtl" lang="ar" style="font-family: Arial;">هذا نص باللغة العربية</div>
```

### Add Watermark

Watermarks can be added to your PDFs using mPDF parameters. This can be either in the form of an image or text.

#### Add Image Watermark

You can include a PNG or JPG image as a watermark in your PDF. Specify the path and file extension of your image in the 'watermarkImage' parameter.

For example:

```
{% set pdfOptions = {
    'watermarkImage': 'path/to/your/image.ext'
} %}
```

Replace 'path/to/your/image.ext' with the actual path and file extension of your image.

#### Add Text Watermark

You can also add a text watermark to your PDF. Simply specify your desired text in the 'watermarkText' parameter.

For example:

```
{% set pdfOptions = {
    'watermarkText': 'My text watermark example'
} %}
```

Replace 'My text watermark example' with the actual text you want to use as a watermark.

### Generate PDF Table of Contents

You have the ability to create your own Table of Contents as per the guidelines in the [mPDF ToC documentantion](https://mpdf.github.io/what-else-can-i-do/table-of-contents.html).

Additionally, we can enable automatic generation of a Table of Contents from H1-H6 tags present in your PDF document. You can activate this feature by setting the `autoToC` option to true.

For instance:

```
{% set pdfOptions = {
    'autoToC': true
} %}
```

### Generate Bookmarks

Manually adding bookmarks can be done with the bookmark tag `<bookmark content="Text" />`. The content attribute is used to set the text of the bookmark. You can also use the optional `level` attribute to set the nesting level of the bookmark.

Additionally, we can enable automatic generation of a Bookmarks from H1-H6 tags present in your PDF document. You can activate this feature by setting the `autoBookmarks` option to true.

For instance:

```
{% set pdfOptions = {
    'autoBookmarks': true
} %}
```

## Requirements

Craft CMS >= 3.0.0 for 0.x branch
Craft CMS >= 4.0.0 for 1.x branch

## Support

For inquiries regarding custom PDF work, such as generating templates or modifying plugins, please reach out to us via [our contact page](https://cooltronic.pl/contact/).

## Credits

Special thanks to the developers and testers who have contributed to this project and helped identify and fix bugs:

- [@mokopan](https://github.com/mokopan)
- [@AramLoosman](https://github.com/AramLoosman)
- [@iwe-hi](https://github.com/iwe-hi)

## License

This project is licensed under the Craft License. See the [LICENSE.md](https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md) file for details.
