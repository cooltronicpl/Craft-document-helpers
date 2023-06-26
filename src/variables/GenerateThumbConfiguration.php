<?php
/**
 *
 * Class GenerateThumbCoinfiguration
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4.
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

 namespace cooltronicpl\documenthelpers\variables;
 use Craft;
 
 /**
  * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
  * @author    Pawel Potacki
  * @since     1.2.2
  * 
  */

class GenerateThumbConfiguration
{
	public const
		FormatJpg = 'jpg',
		FormatPng = 'png',
		FormatGif = 'gif',
		FormatWebp = 'webp',
		FormatAvif = 'avif';

	public const SupportedFormats = [self::FormatJpg, self::FormatPng, self::FormatGif, self::FormatWebp, self::FormatAvif];


	public function __construct(
		public  $pdfPath,
		public  $savePath,
		public  $format = 'jpg',
		public  $trim = false,
		public  $cols = 200,
		public  $rows = 200,
		public  $bestfit = false,
		public  $bgColor = "white",
		public  $page = 0,
		public  $frameColor="black"
        ) {
		$this->format = strtolower($format);
		if (in_array($this->format, self::SupportedFormats, true) === false) {
			Craft::error(sprintf(
				'Format "%s" is not supported. Did you mean "%s"?',
				$this->format,
				implode('", "', self::SupportedFormats)
			));
            exit;

		}
		if (is_file($pdfPath) === false) {
			Craft::error(sprintf('File "%s" does not exist.', $pdfPath));
            exit;

		}
	}


	public function from(
        $pdfPath,
        $savePath,
        $format = 'jpg',
        $trim = false
    ) {
        $this->pdfPath = $pdfPath;
        $this->savePath = $savePath;
        $this->format = $format;
        $this->trim = $trim;

        // Add checks for the format and existence of the file
        if (in_array($this->format, self::SupportedFormats, true) === false) {
            Craft::error(sprintf(
                'Format "%s" is not supported. Did you mean "%s"?',
                $this->format,
                implode('", "', self::SupportedFormats)
            ));
            exit;
        }
        if (is_file($this->pdfPath) === false) {
            Craft::error(sprintf('File "%s" does not exist.', $this->pdfPath));
            exit;
        }
    }
}