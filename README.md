# PDF Generator for Craft CMS 3.x and 4.x. 

With ❤ by [CoolTRONIC.pl sp. z o.o.](https://cooltronic.pl) developed by [Pawel Potacki](https://potacki.com)

This plugin allows you to generate PDF files from a Craft CMS entry using a Twig template. It supports the creation and download of multiple PDF files from entries within a specific channel section on a website running Craft CMS 3 or 4. You can find example implementations within this README file.

Please note that MPDF for PHP8.x and Craft 4 may require a GD extension.

![Icon](resources/pdf-black.png#gh-light-mode-only)
![Icon](resources/pdf-light.png#gh-dark-mode-only)

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

## Description of Parameters

* `template` - This is the location of the template file for the PDF, which should be located in the /templates directory.

* `destination` - This indicates where the PDF file will be generated. It can be one of four options: "file", "inline", "download", or "string". The "file" and "download" options have been extensively debugged. To download multiple files, refer to the JavaScript example provided in the README.md file.

* `filename` - This is the name of the generated PDF file.

* `entry` - This represents the data that will be inputted into the template to generate the PDF. This data is contained within an 'entry' array.

* `pdfOptions` - This parameter allows you to customize the generation of the PDF. The available options are described in the section on overriding default options.

## Basic Usage Example

```
{{craft.documentHelper.pdf("template.twig", "file", "document.pdf", entry, pdfOptions)}} 
```

## Advanced Usage Example

```
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf', entry, pdfOptions)}}" 
download>
</a>
```

## Securely Displaying PDF Documents in the Browser Without Saving to the /web Folder

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

## Overriding Default Options

You can override the default options with `pdfOptions` as shown above. Here are the available options:

* `date` - This is disabled by default. If you provide a date (in timestamp format) that is older than the creation date of the file, the existing file will be overwritten.  
* `header` - This is the header Twig template, which is disabled by default.
* `footer` - This is the footer Twig template, also disabled by default.
* `margin_top` - The top margin defaults to 30.
* `margin_bottom` - The bottom margin defaults to 30.
* `margin_left` - The left margin defaults to 15.
* `margin_right` - The right margin defaults to 15.
* `mirrorMargins` - This defaults to 0 but can be set to 1.
* `pageNumbers` - This adds page numbers in the footer.
* `title` - This replaces the default title of the generated PDF document.
* `custom` - This allows you to add custom variable or variables.
* `password` - This can be used to add password protection to your PDF. The password should be provided as a string.
* `no_auto_page_break`  - This disables automatic system page breaks. This can be useful if you need to manually add page breaks. For example, you can add a custom page to documents with more than one page break using <pagebreak>. This may fix page break issues in some cases, but not all.
* `author` - This sets the author metadata. It should be provided as a string.
* `keywords` - This sets the keyword metadata. It should be provided as a string in the following format: "keyword1, longer keyword2, keyword3".
* `fonts`:
    * `fontdata`, and `fontDir`  - These allow you to set custom fonts described above.
* `tempDir` - This sets the path to the temporary directory used for generating PDFs. We have tested this with the main /tmp directory on the server with success. This could potentially improve performance when generating multiple PDFs.
* `landscape` - If this is set, the PDF will be generated in landscape mode.
* `portrait` - If this is set, the PDF will be generated in portrait mode.
* `format` This sets the paper size for the PDF. The default is "A4", but you can set other sizes compatible with MPDF. Other popular formats include:
  * A3
  * A4 (default)
  * A5
  * Letter (8,5 x 11 in)
  * Legal (8,5 x 14 in)
  * Executive (7,25 x 10,5 in)
  * B4
  * B5

## Custom fonts

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

## Returned values

* If the destination is `inline` or `string`, the plugin returns a string.
* If the destination is `download` or `file`, it returns the filename in the /web folder.

## Example in a loop

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

## Twig template example

```
<h1>
{{title}}
</h1>
<p>
{{entry.variables}}
</p>
...
```

## Including Images in PDF

There are two ways to include images in the PDF template.

If you're using the Image Toolbox plugin, you can include images like this:

```
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

### Thumbnail of Generated PDF

You can use [PDF Thumbnails by @scandel](https://github.com/scandel/pdfThumbnails) for client-side generation of PDF thumbnails. This requires the files pdfThumbnails.js, pdf.js, and pdf.worker.js to be loaded in the /web folder. The pdf.js and pdf.worker.js files from  [PDF.js can be found here](https://mozilla.github.io/pdf.js/getting_started/). 

Here's an example:

```
<script src="{{alias('@web')}}/pdfThumbnails.js" data-pdfjs-src="{{alias('@web')}}/pdf_js/build/"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.js"></script>
<script src="{{alias('@web')}}/pdf_js/build/pdf.worker.js"></script>
				{% header "Cache-Control: no-cache" %}
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/example.pdf'  , entry, pdfOptions))}}">
<img class="img-responsive" data-pdf-thumbnail-file="{{alias('@web')}}/pdf/example.pdf" src="{{alias('@web')}}/pdfjs_placeholder.png">
```

## Custom Title of PDF Document

To set a custom title for your PDF, use the `title` option in the `pdfOptions` as follows:

```
{% set pdfOptions = {
	...,
	title: "My awesome Title"
} %}
```

## Custom Variables

### String or Number

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

### Arrays

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
## Custom Page Break in PDF Document

You can add a page break in your HTML content using MPDF tags. The following tag represents a page break:

```
<pagebreak>
```

## Page and All Pages Numbers

To include the current page number, use the `{PAGENO}` tag:

```
{PAGENO}
```

To include the total number of pages in the document, use the `{nbpg}` tag:

```
{nbpg}
```

## Generate PDF File Only When Data of Entry is Changed

Use the following `pdfOptions` to ensure that a PDF is only generated when the data in an entry has been updated:

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
```

## About Filename Characters

When selecting a filename for your PDF, ensure that you only use safe characters. The following characters are not allowed in Windows filenames: ":", "/", "?", "|", "<", ">" or "/".

## Browser Caching Problems of PDF Files in Some Servers and Hosting

If you are experiencing issues with your server or hosting caching PDF files, you can use the [Static Files Autoversioning](https://github.com/cooltronicpl/craft-files-autoversioning)plugin. This plugin adds a timestamp to your PDF, helping to avoid caching issues.

```
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/book'  ~ '.pdf'  ,entry, pdfOptions))}}">LINK </a>
```

With this plugin, your PDF will have a timestamp and any caching policy problems with your hosting will be resolved. The following is an example of what the PDF link will look like:

```
<a href="http://some-domain.com/pdf/book.pdf?v=1668157143">LINK </a>
```

## Requirements

Craft CMS >= 3.0.0 for 0.x branch
Craft CMS >= 4.0.0 for 1.x branch

## Multiple PDF Files Downloading with JavaScript on Any Page

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

## Credits

Special thanks to the developers and testers who have contributed to this project and helped identify and fix bugs:

* [@mokopan](https://github.com/mokopan)
* [@AramLoosman](https://github.com/AramLoosman)
* [@iwe-hi](https://github.com/iwe-hi)

## License

The MIT License (MIT)

Copyright (c) 2022 CoolTRONIC.pl sp. z o.o. by Pawel Potacki

More about [CoolTRONIC.pl sp. z o.o. Interactive Agency](https://cooltronic.pl/)

More about [main developer Pawel Potacki](https://potacki.com/)

CoolTRONIC.pl sp. z o.o. hereby holds all copyright interest in the program “PDF Generator” written by Pawel Potacki.

LICENSE.md file contains full License notices.