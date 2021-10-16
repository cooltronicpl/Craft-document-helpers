# Document helpers plugin for Craft CMS 3.x. 
This plugin can generate PDF documents from entry with Twig template to an PDF file or String output. 

![Icon](resources/document.png)

## Installation

```
composer require cooltronicpl/document-helper
```

## Usage
```
craft.documentHelpers.pdf(template_string, destination, filename, entry, pdfOptions)
```
## Variables

* template_string - the location of template file for PDF file

* destionation - where the file will be ganerated, the "file" option is excessively debbuged 

* filename - name of genarated file 

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
## Entry variables inserted to temlate

All variables of entry in generated template is in entry array
```
{{entry.VAR}}
```
The title of current entry avaible at variable:
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
   * date (in timestamp) default disabled, if date is provided in this parameter was smaller than file date, the file was overwwritten  
   * header (header twig template) default disabled
   * footer (footer twig template) default disabled
   * margin_top default 30
   * margin_bottom default 30
   * margin_left default 15
   * margin_right default 15
   * mirrorMargins default 0 (possible 1)
   * pageNumbers adds page number in footer
   * title replaces default title of generated PDF document
   * custom adds custom variable or variables

## Returened values

* If destination is 'inline' or 'string' the plugin returns string
* If destination is 'download' or 'file' it returns filename in /web folder

## Example in loop
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

Example to include img as tag into PDF document without plugin.

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

## Page and all pages numbers

Actual page number

```
{PAGENO}
```

All pages of generated document

```
{nbpg}
```
