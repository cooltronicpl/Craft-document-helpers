# Document helpers plugin for Craft CMS 3.x

![Icon](resources/document.png)

THIS PLUGIN IS IN BETA AND SHOULD NOT BE USED IN PRODUCTION

## Installation

```
composer require cooltronicpl/document-helper
```

```
craft.documentHelpers.pdf(template_string, destination, filename (folder in /web), data of entry, pdfOptions)
```

## Example
```
<a href="{{alias('@web')}}/
{{craft.documentHelper.pdf("_pdf/document.twig", "file",  'pdf/' ~ entry.id ~ '.pdf' ,entry, pdfOptions)}}"
download>
</a>
```
## Variables

All variables is in template is in entry array
```
{{entry.VAR}}
```
The title is avaible at variable:
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
### Twig template
```
<h1>
{{title}}
</h1>
<p>
{{entry.variables}}
</p>
...
```