<?php

/**
 *
 * Class GenerateThumb
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4.
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers\variables;

use cooltronicpl\documenthelpers\variables\GenerateThumbConfiguration;
use Craft;

/**
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     1.2.2
 *
 */

class GenerateThumb
{
    public function __construct()
    {

    }

    public function convert(GenerateThumbConfiguration $configuration): void
    {
        try {
            // Note: Now calling process on $this instead of self
            $this->process($configuration);
        } catch (\ImagickException $e) {
            Craft::error("Imagick Error: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            Craft::error("General Error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function process(GenerateThumbConfiguration $configuration): void
    {
        if (class_exists('\Imagick') === false) {
            Craft::error('Imagick is not installed.');
            exit;
        }
        $page = $configuration->page; // starting page
        $bgColor = $configuration->bgColor;
        Craft::info('Imagick is Reading PDF: ' . $configuration->pdfPath . "[{$page}]");
		$im = new \Imagick ();
		
        try {
            $im->readImage($configuration->pdfPath . "[" . $page . "]");
        } catch (\ImagickException $e) {
            Craft::error('Failed to read page ' . ($page + 1) . ' of the PDF: ' . $e->getMessage());
            throw $e; // rethrow the exception
        }

        if ($configuration->cols !== null && $configuration->rows !== null) {
            $im->scaleImage($configuration->cols, $configuration->rows, $configuration->bestfit);
        }
		if ($configuration->trim) {
			$im->trimImage(1);
		}
		
		// Calculate the offset for centering the image

		
		// Create a blank image with the desired background color
		$bgImage = new \Imagick();
		$bgImage->newImage($configuration->cols, $configuration->rows, new \ImagickPixel($bgColor));
		$bgImage->setImageFormat($configuration->format);
		if ($configuration->trim) {
			$offsetX = ($configuration->cols - $im->getImageWidth()) / 2;
			$offsetY = ($configuration->rows - $im->getImageHeight()) / 2;
			$bgImage->setImageBorderColor(new \ImagickPixel($configuration->frameColor));
		}
		else{
			$offsetX = 0;
			$offsetY = 0;			
		}
        // Composite the PDF page onto the blank image with the desired background color
        $bgImage->compositeImage($im, \Imagick::COMPOSITE_OVER,  $offsetX, $offsetY););

        // Assign the composite image to the original Imagick object
        $this->write($configuration->savePath, (string) $bgImage);
    }

    private function write(string $file, string $content,  ? int $mode = 0_666) : void
    {
        $this->createDir(dirname($file));
        if (@file_put_contents($file, $content) === false) { // @ is escalated to exception
            Craft::error(sprintf('Unable to write file "%s": %s', $file, self::getLastError()));
            exit;

        }
        if ($mode !== null && !@chmod($file, $mode)) { // @ is escalated to exception
            Craft::error(sprintf('Unable to chmod file "%s": %s', $file, self::getLastError()));
            exit;

        }
    }

    private function createDir(string $dir, int $mode = 0_777): void
    {
        if (!is_dir($dir) && !@mkdir($dir, $mode, true) && !is_dir($dir)) { // @ - dir may already exist
            Craft::error(sprintf('Unable to create directory "%s": %s', $dir, self::getLastError()));
            exit;

        }
    }

    private function getLastError(): string
    {
        return (string) preg_replace('#^\w+\(.*?\): #', '', error_get_last()['message'] ?? '');
    }
}
