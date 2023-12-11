# Craft CMS PDF Generator (Versions 3.x and 4.x)

Developed by [CoolTRONIC.pl sp. z o.o.](https://cooltronic.pl) and [Pawel Potacki](https://potacki.com). This plugin allows you to generate PDF files from Twig templates using the mPDF library. You can customize the PDF output with various options and save it as a file, an asset, or a string. You can also use URL or HTML code blocks as templates.

![Icon](resources/pdf-black.png#gh-light-mode-only)
![Icon](resources/pdf-light.png#gh-dark-mode-only)

## Contents

- [Installation](#installation)
- [Usage](#usage)
  - [`pdf` method Parameters](#pdf-method-parameters)
  - [Parameters `pdfAsset` method](#parameters-pdfasset-method)
  - [Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder](#securely-displaying-pdf-documents-in-the-browser-without-saving-to-the-web-folder)
  - [Variables in template](#variables-in-template)
  - [How to use custom templates by code block and URL](#how-to-use-custom-templates-by-code-block-and-url)
    - [How to use custom variables with code block](#how-to-use-custom-variables-with-code-block)
  - [Overriding Default Options](#overriding-default-options)
  - [Custom fonts](#custom-fonts)
  - [Return values](#return-values)
  - [Loop example](#loop-example)
  - [Twig template example](#twig-template-example)
  - [Adding PDFs to Assets](#adding-pdfs-to-assets)
  - [Advanced `pdfAsset` Method Options](#advanced-pdfasset-method-options)
  - [Including Images in PDF](#including-images-in-pdf)
    - [Thumbnail of Generated PDF on Frontend](#thumbnail-of-generated-pdf-on-frontend)
    - [Generating on backend and Displaying Thumbnails by PDF Generator by `assetThumb` option to add into Assets on `pdfAsset` method](#generating-on-backend-and-displaying-thumbnails-by-pdf-generator-by-assetthumb-option-to-add-into-assets-on-pdfasset-method)
    - [Generating on backend and Displaying Dumb Thumbnails by PDF Generator by `dumbThumb` option to default `pdf` method](#generating-on-backend-and-displaying-dumb-thumbnails-by-pdf-generator-by-dumbthumb-option-to-default-pdf-method)
    - [Generating on backend and Displaying Thumbnails of PDF Assets by external PDF Transform plugin](#generating-on-backend-and-displaying-thumbnails-of-pdf-assets-by-external-pdf-transform-plugin)
    - [What if I cannot generate Thumbnails with enabled ImageMagick?](#what-if-i-cannot-generate-thumbnails-with-enabled-imagemagick)
  - [Custom Title of PDF Document](#custom-title-of-pdf-document)
  - [Custom Variables](#custom-variables)
  - [Custom Page Break in PDF Document](#custom-page-break-in-pdf-document)
  - [Page and Total Page Numbers](#page-and-all-pages-numbers)
  - [Generate PDF File Only When Entry Data is Changed](#generate-pdf-file-only-when-entry-data-is-changed)
  - [Filename Characters](#filename-characters)
  - [Browser Caching Issues of PDF Files on Some Servers and Hosting](#browser-caching-issues-of-pdf-files-on-some-servers-and-hosting)
  - [Downloading Multiple PDF Files with JavaScript on Any Page](#downloading-multiple-pdf-files-with-javascript-on-any-page)
  - [Optional packages](#optional-packages)
  - [Display QRCode](#display-qrcode)
  - [RTL Text Direction](#rtl-text-direction)
  - [Add Watermark](#add-watermark)
  - [Generate PDF Table of Contents](#generate-pdf-table-of-contents)
  - [Generate PDF Bookmarks](#generate-pdf-bookmarks)
- [Requirements](#requirements)
- [Support](#support)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the PDF Generator plugin from the Craft Plugin Store or through Composer.

Go to the [Craft CMS Plugin Store](https://plugins.craftcms.com/document-helpers) in your project’s Control Panel and search for "PDF Generator". Then click on the "Install" button in its modal window.

Open your terminal and go to your Craft project:

```bash
cd /path/to/project
```

Then tell Composer to require the plugin:

```bash
composer require cooltronic/document-helpers
```

## Usage

### `pdf` method Parameters

The pdf method generates a PDF file from a Twig template and returns a URL to the file. The method accepts an array of parameters:

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory. Also you can pass URL or HTML code block from 1.3.2 or 0.4.2.

- `destination` - This indicates where the PDF file will be generated. It can be one of four options: `file`, `inline`, `download`, or `string`. To download multiple files, refer to the JavaScript example provided in the README.md file.

- `filename` - This is the name of the generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

Method returns string with filename for anchors or string content for PDF files to send content as attachment.

```twig
{{craft.documentHelper.pdf("template.twig", "file", "document.pdf", entry, pdfOptions)}}
```

Example of `pdf` method:

```twig
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf', entry, pdfOptions)}}"
download>
</a>
```

### Parameters `pdfAsset` method

The `pdfAsset` method generates a PDF file from a Twig template, saves it as an asset, and returns an Asset model. The method accepts an array of parameters:

- `template` - This is the location of the template file for the PDF, which should be located in the /templates directory. Also you can pass here now a URL or a HTML code block from 1.3.2 or 0.4.2.

- `filename` - This is the name of temporary or / and final of generated PDF file.

- `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

- `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

- `volumeHandle` - This parameter should contains volume handle name on which we need to add PDF as Craft CMS asset from system. The volume handle must have a `Base URL` in your test: `@web\pdffiles` in Craft CMS Filesystems, Assets settings.

Example of `pdfAsset` method:

```twig
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
    <a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">Download your PDF</a>
{% else %}
    File was not generated.
{% endif %}
```

### Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder

You can securely display PDF documents in the browser without saving them to the /web folder as follows:

```twig
						{% set pdfOptions = {
						date: entry.dateUpdated|date('U'),
						header: "_pdf/header.twig",
						footer: "_pdf/footer.twig"
                        } %}
				{% header "Content-Type: application/pdf" %}
{{craft.documentHelper.pdf('_pdf/document.twig', 'inline', '../book_example'  ~ '.pdf', entry, pdfOptions)}}

```
## Variables in template

Within the PDF Twig template, you can access the passed `entry` in a generated twig template array:

```twig
{{entry.VAR}}
```

The title of the current entry can be accessed via:

```twig
{{title}}
```

## How to use custom templates by code block and URL

You can use a URL or an HTML code block as a template for the PDF file. To do this, pass the URL or the HTML code block as the `template` parameter.

Example of using a URL as a template:

```
{% set pdfOptions = {

	}
%} 
<a href="{{alias('@web')}}{{craft.documentHelper.pdf('https://cooltronic.pl/', 'file', 'pdf/exampleURL.pdf'  , entry, pdfOptions)}}">URL Example</a>
```

After installing custom URL Purifier (HTMLPurifier) package in plugin settings you can solve problems with scraping of external websites and enable `URLPurify` option. When you encouter problems try to install this package in `@root` path:
```
# Craft CMS 4
composer require ezyang/htmlpurifier:^4.17 
# Craft CMS 3
composer require ezyang/htmlpurifier:^4.13 
```

Example of code block:

```
{% set pdfOptions = {

	}
%}    
{% set html %}
    <h1>This is a basic example</h1>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.</p>
	<br>
	<p>HTML Contents</p>
{% endset %}

<a href="{{alias('@web')}}{{craft.documentHelper.pdf(html, 'file', 'pdf/exampleHTML.pdf'  , entry, pdfOptions)}}">HTML PDF</a>
```

### How to use custom variables with code block

You can pass custom variables to the PDF template using the `custom` or `qrimg` options in the `pdfOptions` array. The `custom` option allows you to pass any variable, while the `qrimg` option allows you to pass a QR code image from `qrdata` variable.

Example of using custom variables:

```
{% set pdfOptions = {
	header: "_pdf/header.twig",
	footer: "_pdf/footer.twig",
	qrdata: "https://cooltronic.pl"
    custom: entry.var
    }
%}
{% set pdf_content %}
    <h1>This is a basic example</h1>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.</p>
	<br>
	{% verbatim %}
    <p>QR Code:</p>
	<img src="{{qrimg}}" alt="QR Code"/>
    {{custom}}
	{% endverbatim %}
{% endset %}
<a href="{{alias('@web')}}{{craft.documentHelper.pdf(pdf_content, 'file', 'pdf/html.pdf', entry , pdfOptions)}}">HTML QR Code & Custom PDF</a>
```

### Overriding Default Options

You can override the default options with `pdfOptions` as shown above. Here are the available options:

- `date` - This is disabled by default. If you provide a date (in timestamp format) that is older than the creation date of the file, the existing file will be overwritten.
- `header` - The optional location of the template file for the header, which should be in the `/templates` directory. You can also use a URL or an HTML code block as a template.
- `footer` - The optional location of the template file for the footer, which should be in the `/templates` directory. You can also use a URL or an HTML code block as a template.
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
- `assetThumb`: This option generates a thumbnail image of a Craft CMS image Asset using the `pdfAsset` method (requires ImageMagick). It can be accessed in the Twig template as `asset.assetThumb`.
  - `assetThumbVolumeHandle`: This is an optional parameter that specifies the Volume Handle for the thumbnail. If not provided, the PDF Volume Handle is used. The volume handle must have a `Base URL` in your test: `@web\pdffiles` in Craft CMS Filesystems, Assets settings.
- `dumbThumb`: This option generates a basic thumbnail image (without an Asset) using the `pdf` method (requires ImageMagick).
- `qrdata`: This option allows you to generate a QR Code image from any data you provide. The image will be available on the Twig template with `{{qrimg}}` variable. Required to install optional package from plugin settings.
- `encoding` - This can set encoding of input stream like URL, HTML or Twig template.
- `URLPurify` - This enable external library to make HTML Purify provided URL in `template` when set to true.

Both `assetThumb` and `dumbThumb` support the following optional customizations:

- `thumbType`: This parameter allows you to choose the format of the thumbnail. Options include `jpg`, `gif`, `webp`, `avif`, and `png`. The default format is `jpg`.
- `thumbWidth`: This parameter specifies the width of the thumbnail in pixels. The default width is `210`.
- `thumbHeight`: This parameter specifies the height of the thumbnail in pixels. The default height is `297`.
- `thumbPage`: This parameter specifies the page to generate the thumbnail from. The default is the first page, which is numbered from `0`.
- `thumbBgColor`: This parameter specifies the background color of the thumbnail. Options include `black`, `rgb(33,66,99)`, and `#123456`. The default color is `white`.
- `thumbTrim`: This parameter, when set to `true`, trims your page and centers the content. The default value is `false`.
- `thumbTrimFrameColor`: This parameter changes the color of the trim frame. Colors can be specified as `black` or in RGB format (e.g., `rgb(12,1,2)`) or in HEX format (e.g., `#662266`).

All settings can be set globally in plugin settings page and be overwritten individually by `pdfOptions`.

### Custom fonts

This is an example of how to use custom fonts, specifically `Roboto-Regular.ttf` and `Roboto-Italic.ttf`, which should be placed in the config folder:

```
fontdata: { 'roboto' : {
            'R' : 'Roboto-Regular.ttf',
            'I' : 'Roboto-Italic.ttf'
        }},
		fontDir: "{{craft.app.path.configPath}}/",
```

After the update of MPDF, which is used by our PDF Generator, we have resolved an issue with passed paths. Now, you must provide an absolute path on the server to the config directory. Alternatively, you can pass the main folder. For instance, on ISP Config 3.2 host, you can use: `fontDir`: `/var/www/clients/client0/web21/private/config/`.

If you're running a single site, it should be an absolute path to the `/config` folder, like: fontDir: `/path_to/config/`.

For XAMPP in Windows hosts, the confirmed format is working `fontDir`: `file:///C:/xampp/htdocs/craft4/config/`.

### Return values

- If the destination is `inline` or `string`, the plugin returns a string.
- If the destination is `download` or `file`, it returns the filename in the /web folder.
- If an error occurs during the PDF generation, the methods will return `false`.

### Loop example

You can generate multiple PDF files in a loop. For example, you can generate a PDF file for each entry in a section:

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

Here is an example of a Twig template that can be used to generate a PDF document:

```
<!DOCTYPE html>
<html>
<head>
    <title>{{ entry.title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>
<body>
    <h1>{{ entry.title }}</h1>
    <p>{{ entry.variable }}</p>
</body>
</html>
...
```

### Adding PDFs to Assets

You can add generated PDF files to your Craft assets. To do this you need to specify the filename using the `@root` path. The following example demonstrates its usage with a volume that has the handle `pdffiles`:

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

### Advanced `pdfAsset` Method Options

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

#### Generating on backend and Displaying Thumbnails by PDF Generator by `assetThumb` option to add into Assets on `pdfAsset` method

To display Image Thumbnail added into Craft CMS Twig template you may use following options:

```twig
{% set pdfOptionsAsset = {
	assetThumb: true
	}
%}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/test.pdf', entry, pdfOptionsAsset, 'pdfFiles') %}
{% if asset %}
{% set assetThumb = asset.assetThumb %}

<a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">
  {% if assetThumb %}
  <img src="{{ assetThumb.url() }}?v={{ assetThumb.dateModified|date('U') }}" />
  {% else %}
  Thumbnail is not available
  {% endif %}
</a>
{% else %}
```

If you want add image into another Volume than PDF file you may override PDF options with option `assetThumbVolumeHandle`, in this example this will be set into `pdfimages` volume handle:

```twig
{% set pdfOptionsAsset = {
	assetThumb: true,
    assetThumbVolumeHandle: "pdfimages"
	}
%}
```

#### Generating on backend and Displaying Dumb Thumbnails by PDF Generator by `dumbThumb` option to default `pdf` method

To add thumbnails generated with old `pdf` method you may use following code:

```
{% set pdfOptionsDumb = {
	date: entry.dateUpdated|date('U'),
	dumbThumb: true,
	}
%}
<a href="{{alias('@web')}}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/example.pdf'  , entry, pdfOptionsDumb)}}">
    <img src="{{alias('@web')}}{{ '/pdf/example.jpg' }}" />
</a>
```

#### Generating on backend and Displaying Thumbnails of PDF Assets by external PDF Transform plugin

To generate an image thumbnail of a PDF asset, you can use the [PDF Transform](https://plugins.craftcms.com/pdf-transform) plugin. Please note, this feature requires ImageMagick.

Here's an example of how to generate and display a thumbnail:

```twig
{% set pdfOptions = { date: entry.dateUpdated|date('U') } %}
{% set asset = craft.documentHelper.pdfAsset('_pdf/document.twig', alias('@root')~'/example.pdf', entry, pdfOptions, 'pdffiles') %}
{% if asset %}
<a href="{{asset.url()}}?v={{asset.dateModified|date('U')}}">
    {% set transformedPdf = craft.pdfTransform.render(asset) %}
    <img src="{{ transformedPdf.url }}?v={{transformedPdf.dateModified|date('U')}}" />
</a>
{% endif %}
```

#### What if I cannot generate Thumbnails with enabled ImageMagick?

If you encounter the following error with PDF Transform:

```
attempt to perform an operation not allowed by the security policy `PDF' @ error/constitute.c/IsCoderAuthorized/421
```

On our plugin error is in your Craft CMS in `/runtime/logs/web.log` and image is not generated in `pdf` or `pdfAsset`:

```
Imagick Error: attempt to perform an operation not allowed by the security policy `PDF' @ error/constitute.c/IsCoderAuthorized/421
```

This means ImageMagick's security policy is preventing operations on PDF files. To fix this, you'll need to modify the policy.xml file located at `/etc/ImageMagick-6/policy.xml` or `/etc/ImageMagick-7/policy.xml`, depending on your ImageMagick version.

Find and modify the policy related to PDFs to allow the desired operation. Be cautious, as changing this file can have security implications. Make sure you understand the risks and consequences. Here's an example of how to change a policy from disallowing all operations to allowing read and write operations:

```xml
<!-- Before -->
<policymap>
  <policymap domain="coder" rights="none" pattern="PDF" />
</policymap>

<!-- After -->
<policymap>
  <policymap domain="coder" rights="read | write" pattern="PDF" />
</policymap>
```

Then restart your PHP FPM service if you are using. If you're unsure about modifying this file, consider reaching out to your hosting provider's support team for assistance.

### Custom Title of PDF Document

To set a custom title for your PDF, use the `title` option in the `pdfOptions` as follows:

```
{% set pdfOptions = {
	...,
	title: "My awesome Title"
} %}
```

### Custom Variables

You can use `custom` variables in your Twig template. To do this, add an associative array or variable to the `custom` parameter in the pdfOptions array. The keys of this array or passed variable will be available as variables in your Twig template.

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

```twig
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

```twig
{{custom.slug}}
{{custom.created.format('d/m/Y')}}
```

### Custom Page Break in PDF Document

You can add a page break in your PDF document by using the `<pagebreak>` tag in your Twig template.

```twig
<p>Content before the page break.</p>
<pagebreak />
<p>Content after the page break.</p>
```

### Page and All Pages Numbers

You can add page numbers and the total number of pages to your PDF document by using the `{PAGENO}` and `{nbpg}` placeholders in your Twig template, header or footer.

```twig
<p>Page {PAGENO} of {nbpg}</p>
```

### Generate PDF File Only When Entry Data is Changed

You can set the PDF Generator plugin to only generate a new PDF file when the data of an entry is changed. To do this, use the following `pdfOptions` to ensure that a PDF is only generated when the data in an entry has been updated:

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
```

### Filename Characters

The filename of the generated PDF file can only contain alphanumeric characters, underscores, and hyphens. When selecting a filename for your PDF, ensure that you only use safe characters. The following characters are not allowed in Windows filenames: ":", "/", "?", "|", "<", ">" or "/".

### Browser Caching Issues of PDF Files on Some Servers and Hosting

Some servers and hosting environments may cache PDF files, which can cause issues when you're trying to view the latest version of a generated PDF file. To prevent this, you can add a unique query string to the URL of the PDF file.

If you are experiencing issues with your server or hosting caching PDF files, you can use the [Static Files Autoversioning](https://plugins.craftcms.com/craft3-files-autoversioning) plugin. This plugin adds a timestamp to your PDF, helping to avoid caching issues.

```
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/book'  ~ '.pdf'  ,entry, pdfOptions))}}">LINK </a>
```

With this plugin, your PDF will have a timestamp and any caching policy problems with your hosting will be resolved. The following is an example of what the PDF link will look like:

```
<a href="http://some-domain.com/pdf/book.pdf?v=1668157143">LINK </a>
```

### Downloading Multiple PDF Files with JavaScript on Any Page

You can download multiple PDF files with JavaScript on any page. To do this, you can use this example with `pdf` method to specify entries for which you want to generate PDF files.

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

Example with a loop on channel section `xxx` for items on array:

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

#### Example with pdfAsset method

You can download multiple PDF files with JavaScript on any page. To do this, you can use the `pdfs` parameter looped through `pdfAsset` method to specify an array of entries, in this example items of channel section `xxx` for which you want to generate PDF files.

```twig
{% set pdfs = [] %}
{% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
    {% set pdf = craft.documentHelper.pdfAsset('_pdf/document.twig', 'file', 'pdf/' ~ item.id ~ '.pdf', item, pdfOptions) %}
    {% set pdfs = pdfs|merge([pdf]) %}
{% endfor %}
```

In your JavaScript code pass `pdfs` from Craft CMS, you can then loop over the passed `pdfs` array and trigger the download of each PDF file.

```JavaScript
<script>
    var pdfs = JSON.parse('{{ pdfs|json_encode|raw }}');
    pdfs.forEach(function(pdf) {
    window.open(pdf.url, '_blank');
    });
</script>
```
### Optional packages

Navigate to the PDF Generator plugin, and click on the "Optional functions to enable" section. You will see a list of buttons to install for each optional package and set individual settings.

### Display QRCode

Prepare the `qrdata` string that contains the information you want to encode in the QRCode. The format of the `qrdata` string depends on the type of information you want to attach. You need to install optional package in Plugin Settings `Optional` pane. Here are some common formats:

- For a website link, you can just use the URL of the website, such as `https://cooltronic.pl/`
- For a plain text, you can use any message you want, such as `Hello, world!`
- For a contact information, you can use the vCard format, which is a standard for exchanging personal data, such as `BEGIN:VCARD\nVERSION:3.0\nN:Potacki;Pawel\nTEL;TYPE=work,voice;VALUE=uri:tel:+99-888-777-666\nEMAIL:pawel@cooltronic.pl\nEND:VCARD`
- For a WiFi configuration, you can use the WIFI format, which is a simple way to share network settings, such as `WIFI:S:MyNetwork;T:WPA;P:MyPassword;;`

Example:

```
{% set pdfOptions = {
	...
	qrdata: "https://cooltronic.pl"
	...
}
%}    
```

In the Twig template, insert the QRCode image where you want by using this code:

```
<img src="{{qrimg}}">
```

Where `{{qrimg}}` is the variable that holds the image from the package [PHP QRCode generator](https://php-qrcode.readthedocs.io/).

You can install package manually when you encounter problems in automatic installation for Craft CMS 3.x (you need 3.4) and for 4.x (you need 4.3) version of optional package for display QR Code generation. This is example to how install to your main Craft (`@root`) installation directory:

```
# Craft CMS 4
composer require chillerlan/php-qrcode:^4.3
# Craft CMS 3
composer require chillerlan/php-qrcode:^3.4
```

### RTL Text Direction

The PDF Generator plugin supports right-to-left (RTL) text direction. To enable RTL text direction set HTML `dir` attribute in your HTML Twig template markup. For example:

```
<div dir="rtl">This is some text in a right-to-left language.</div>
```

#### Language

To specify a language in mPDF, you can use the `lang` attribute in your HTML. For example:

```
<div lang="ar">هذا نص باللغة العربية</div>
```

#### Font

In order for mPDF to display the correct characters, you'll also need to use a font that supports the characters of the language you're using. mPDF comes with several fonts that support a wide range of characters. You can set the font using the CSS font-family property. For example, to use the `Arial` font:

```
div {
    font-family: 'Arial';
}
```

#### RTL full example

Full example on arabic text:

```
<div dir="rtl" lang="ar" style="font-family: Arial;">هذا نص باللغة العربية</div>
```

### Add Watermark

Watermarks can be added to your PDFs using mPDF parameters. This can be either in the form of an image or text.

#### Add Image Watermark

You can include a PNG or JPG image as a watermark in your PDF. Specify the path and file extension of your image in the `watermarkImage` parameter.

For example:

```
{% set pdfOptions = {
    'watermarkImage': 'path/to/your/image.ext'
} %}
```

Replace `path/to/your/image.ext` with the actual path and file extension of your image.

#### Add Text Watermark

You can also add a text watermark to your PDF. Simply specify your desired text in the `watermarkText` parameter.

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

### Generate PDF Bookmarks

Manually adding bookmarks can be done with the bookmark tag `<bookmark content="Text" />`. The content attribute is used to set the text of the bookmark. You can also use the optional `level` attribute to set the nesting level of the bookmark.

Additionally, we can enable automatic generation of a Bookmarks from H1-H6 tags present in your PDF document. You can activate this feature by setting the `autoBookmarks` option to true. For do that set:

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
- [@d-karstens](https://github.com/d-karstens)
- [@chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode/)
  
## License

This project is licensed under the Craft License. See the [LICENSE.md](https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md) file for details.
