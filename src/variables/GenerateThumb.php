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
 * @since     0.3.2
 * @since     2.0.0
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
            Craft::error("Imagick Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        } catch (\Exception $e) {
            Craft::error("General Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    private function process(GenerateThumbConfiguration $configuration): void
    {
        if (class_exists('\Imagick') === false) {
            Craft::error('Imagick is not installed.');
            exit;
        }

        $page = $configuration->page;
        $bgColor = $configuration->bgColor;
        Craft::info('Imagick is Reading PDF: ' . $configuration->pdfPath . "[" . $page . "]");
		$im = new \Imagick ();
        try {
            $im->readImage($configuration->pdfPath . "[" . $page . "]");
        } catch (\ImagickException $e) {
            Craft::error('Failed to read page ' . ($page + 1) . ' of the PDF: ' . $e->getMessage(). "\n" . $e->getTraceAsString());
            throw $e;
        }

        if ($configuration->cols !== null && $configuration->rows !== null) {
            $im->scaleImage($configuration->cols, $configuration->rows, $configuration->bestfit);
        }

        if ($configuration->trim) {
            $im->trimImage(1);
        }

        try {
            $bgImage = new \Imagick();
            $bgImage->newImage($configuration->cols, $configuration->rows, new \ImagickPixel($bgColor));
            $bgImage->setImageFormat($configuration->format);

            if ($configuration->trim) {
                $offsetX = ($configuration->cols - $im->getImageWidth()) / 2;
                $offsetY = ($configuration->rows - $im->getImageHeight()) / 2;
                $bgImage->setImageBorderColor(new \ImagickPixel($configuration->frameColor));
            } else {
                $offsetX = 0;
                $offsetY = 0;
            }

            $bgImage->compositeImage($im, \Imagick::COMPOSITE_OVER, $offsetX, $offsetY);
        } catch (\ImagickException $e) {
            Craft::error('Failed to composite images: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }

        $fileHandle = fopen($configuration->savePath, "w");

        if ($fileHandle) {
            $bgImage->writeImageFile($fileHandle);
            fclose($fileHandle);
            Craft::debug("Saved file: " . $configuration->savePath);
        } else {
            Craft::error("Failed to open file: " . $configuration->savePath);
        }
    }

    private function getLastError(): string
    {
        $lastError = error_get_last();
        if ($lastError && isset($lastError['message'])) {
            return (string) preg_replace('#^\w+\(.*?\): #', '', $lastError['message']);
        } else {
            return 'Undefined error';
        }
    }

    private function processCommand($command)
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, "/");

        if (!is_resource($process)) {
            return ['output' => '', 'returnValue' => false];
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $return = "";

        $returnValue = proc_close($process);
        if (!empty($stdout)) {
            $return = $stdout;
        } elseif (!empty($stderr)) {
            $return = $stderr;
        }
        return $return;
    }

}
