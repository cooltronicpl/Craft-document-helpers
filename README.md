# PDF Generator for Craft CMS 3.x
This plugin can generate PDF documents from an entry with a Twig template to a PDF file. We can generate, or download multiple PDFs from channel section entries on our website with CraftCMS 3 or 4. Some examples are in this README file. 

![Icon](resources/pdf-black.png#gh-light-mode-only)
![Icon](resources/pdf-light.png#gh-dark-mode-only)

## Installation

```
composer require cooltronicpl/document-helper
```

## Usage

```
craft.documentHelpers.pdf(template_string, destination, filename, entry, pdfOptions)
```

## Variables

* template_string - the location of the template file for the PDF file

* destionation - where the file will be generated, the "file" and "download" option is excessively debugged, but you can pass only one download file, when you want to download multiple files, there is a JavaScript example in bottom of README.md 

* filename - the name of a generated file 

* entry - data inserted to generated template

## Simple example

```
{{craft.documentHelper.pdf("template.twig", "file", "document.pdf", entry, options)}} 
```

## Advanced example

```
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf' ,entry, pdfOptions)}}" 
download>
</a>
```

## Entry variables inserted into a template

All variables of entry in generated template is in entry array
```
{{entry.VAR}}
```

The title of the current entry is available at variable:

```
{{title}}
```

## Parameters

* template (in /templates folder) required
* destination (file, inline, download, string) required
* filename required
* variables (like entry) required

```
{% set pdfOptions = {
	date: entry.dateUpdated|date('U'),
} %}
```

## Custom default options overriding

* pdfOptions like above:
   * date (in timestamp) default disabled, if a date is provided in this parameter was smaller than a file created date, the file was overwritten  
   * header (header twig template) default disabled
   * footer (footer twig template) default disabled
   * margin_top default 30
   * margin_bottom default 30
   * margin_left default 15
   * margin_right default 15
   * mirrorMargins default 0 (possible 1)
   * pageNumbers add page numbers in the footer
   * title replaces default title of a generated PDF document
   * custom adds custom variable or variables
   * custom fonts:
      * fontdata
      * fontDir
   
## Custom fonts

Custom fonts example for Roboto-Regular.ttf and Roboto-Italic.ttf placed in config folder:
```
fontdata: { 'roboto' : {
            'R' : 'Roboto-Regular.ttf',
            'I' : 'Roboto-Italic.ttf'
        }},
		fontDir: "{{craft.app.path.configPath}}/",
```
Thanks to: https://github.com/mokopan

## Returned values

* If the destination is 'inline' or 'string' the plugin returns a string
* If the destination is 'download' or 'file' it returns the filename in /web folder

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

## Images in PDF

There are two tricks to render images in the PDF template.

It is possible when using this plugin: https://plugins.craftcms.com/image-toolbox 

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

For example, include an image as a tag in a PDF document without a plugin.

```
{% set image = entry.photoFromCMS.first() %}
	{% if image is not null %}
		<img src="{{image.url}}" alt="">
	{% endif %}
```

## Custom title

```
{% set pdfOptions = {
	...,
	title: "My awesome Title"
} %}
```

## Custom variables

### String or number

```
{% set pdfOptions = {
	...,
	custom: variable
} %}
```

In PDF template

```
{{custom}}
```

### Arrays

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

```
{{custom.slug}}
{{custom.created.format('d/m/Y')}}
```
## Custom page break

You can add tags of MPDF in HTML content. This is a page break;

```
<pagebreak>
```

## Page and all pages numbers

Actual page number

```
{PAGENO}
```

All pages of the generated document

```
{nbpg}
```

## Generate PDF file only when data of entry is changed

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U'),
	} %}
```

## About filename characters

When you chose your PDF filename, you must use safe characters. In the example this is forbidden characters in Windows filename ":", "/", "?", "|", "<", ">" or "\/".

## Browser caching problems of PDF files in some servers and hosting

Also you can use another plugin with [Static Files Autoversioning](https://github.com/cooltronicpl/craft-files-autoversioning) when your hosting or server is caching PDF files.

```
<a href="{{alias('@web')}}{{version("/" ~ craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/book'  ~ '.pdf'  ,entry, pdfOptions))}}">LINK </a>
```

This generate PDF with timestamp and caching policy problems of your hosting is gone.

```
<a href="http://some-domain.com/pdf/book.pdf?v=1668157143">LINK </a>
```

## Multiple PDF files downloading with JavaScript on some page

A browser may ask for permission to download multiple files to the end user. Simple example for some static files:

```
<script>
{% set pdfOptions = {
		date: entry.dateUpdated|date('U')
	} %}
    var files = ["{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date("Y-m-d") ~ random(10) ~ '.pdf'  ,entry, pdfOptions)}}", "{{ alias('@web') }}/{{craft.documentHelper.pdf('_pdf/document.twig', 'file', 'pdf/' ~ entry.dateCreated|date("Y-m-d") ~ random(10) ~ '.pdf' , entry, pdfOptions)}}"];
    for (var i = files.length - 1; i >= 0; i--) {
        var a = document.createElement("a");
        a.target = "_blank";
        a.download = "download";
        a.href = files[i];
        a.click();
    };
</script>
```

Simple example with loop:

```
{% set pdfOptions = {
		date: entry.dateUpdated|date('U')
	} %}
<script>
    var files = [
     {% for item in craft.entries.section('xxx').orderBy('title asc').all() %}
	
	    "{{alias('@web')}}/
	    {{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ item.id ~ '.pdf', item, pdfOptions)}}"
	
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

With ❤ by [CoolTRONIC.pl sp. z o.o.](https://cooltronic.pl) by [Pawel Potacki](https://potacki.com)

## License

The MIT License (MIT)

Copyright (c) 2022 CoolTRONIC.pl sp. z o.o. by Pawel Potacki

More about CoolTRONIC.pl sp. z o.o. Interactive Agency https://cooltronic.pl/

More about main developer Pawel Potacki https://potacki.com/

CoolTRONIC.pl sp. z o.o., hereby disclaims all copyright interest in the program “PDF Generator” written by Pawel Potacki.

LICENSE.md file contains full License notices.