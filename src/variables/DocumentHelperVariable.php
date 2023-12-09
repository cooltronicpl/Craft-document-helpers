<?php

/**
 *
 * Class DocumentHelpersVariable
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4.
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers\variables;

use cooltronicpl\documenthelpers\classes\ExtendedAsset;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use Craft;
/**
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     0.0.2
 */
class DocumentHelperVariable
{
    /**
     * Fuction generates PDF with settings
     * @param string $template twig template.
     * @param string $destination type of generated document.
     * @param string $filename the filename.
     * @param array $variables Craft vars to parse.
     * @param array $attributes optional atts passed to funtcion
     *
     * @return string
     */
    public function pdf($template, $destination, $filename, $variables, $attributes)
    {
        $runtimePath = Craft::$app->getPath()->getRuntimePath();
        $pdfGeneratorPath = FileHelper::normalizePath($runtimePath . '/temp/pdfgenerator');

        if (!is_dir($pdfGeneratorPath)) {
            FileHelper::createDirectory($pdfGeneratorPath);
        }
        
        $defaultConfig = (new \Mpdf\Config\ConfigVariables ())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables ())->getDefaults();
        $plugin = Craft::$app->plugins->getPlugin('documenthelpers');
        // Get the settings
        $settings = $plugin->getSettings();
        $settings = $settings->toArray();
        foreach ($settings as $key => $value) {
            // Check if the value is an empty string
            if ($value === '') {
              // Set the value to null
              $settings[$key] = null;
            }
        }
        Craft::info("PDF Settins: " . StringHelper::toString($settings));
        $settings = array_merge($settings, $attributes);
        Craft::info("PDF Settins2: " . StringHelper::toString($settings));

        if (!isset($settings['dumbThumb'])) {
            if ((file_exists($filename) && isset($settings['date']) && filemtime($filename) > $settings['date'])) {
                return $filename;
            }
        }
        if (isset($settings['qrdata'])){
            $settings['qrdata'] = (new \chillerlan\QRCode\QRCode)->render($settings['qrdata']);
        }

        $vars = [
            'entry' => $variables->getFieldValues(),
            'custom' => $settings['custom'] ?? null,
            'title' => $variables['title'] ?? null,
            'qrimg' => $settings['qrdata'] ?? null
        ];

        $html = Craft::$app->getView()->renderTemplate($template, $vars);
        $html_header = isset($settings['header']) ? Craft::$app->getView()->renderTemplate($settings['header'], $vars) : null;
        $html_footer = isset($settings['footer']) ? Craft::$app->getView()->renderTemplate($settings['footer'], $vars) : null;

        $arrParameters = [
            'margin_top' => $settings['margin_top'] ?? 30,
            'margin_left' => $settings['margin_left'] ?? 15,
            'margin_right' => $settings['margin_right'] ?? 15,
            'margin_bottom' => $settings['margin_bottom'] ?? 30,
            'mirrorMargins' => $settings['mirrorMargins'] ?? 0,
            'fontDir' => $settings['fontDir'] ?? $defaultConfig['fontDir'],
            'fontdata' => $settings['fontdata'] ?? $defaultFontConfig['fontdata'],
            'autoPageBreak' => $settings["no_auto_page_break"] ?? true,
            'tempDir' => $settings["tempDir"] ?? $pdfGeneratorPath,
            'format' => $settings['format'] ?? null,
            'orientation' => ($settings["landscape"] ?? false) ? 'L' : (($settings["portrait"] ?? false) ? 'P' : null),
        ];

        $pdf = new \Mpdf\Mpdf (
            $arrParameters
        );
        if (isset($settings['header'])) {
            $pdf_string = $pdf->SetHTMLHeader($html_header);
        }
        if (isset($settings['footer'])) {
            $pdf_string = $pdf->SetHTMLFooter($html_footer);
        }
        if (isset($settings['pageNumbers'])) {
            $pdf_string = $pdf->setFooter('{PAGENO}');
        }
        if (isset($settings['watermarkImage'])) {
            $pdf->SetWatermarkImage($settings['watermarkImage']);
            $pdf->showWatermarkImage = true;
        }
        if (isset($settings['watermarkText'])) {
            $pdf->SetWatermarkText($settings['watermarkText']);
            $pdf->showWatermarkText = true;
        }
        if (isset($settings['autoToC'])) {
            $pdf->h2toc = array(
                'H1' => 0,
                'H2' => 1,
                'H3' => 2,
                'H4' => 3,
                'H5' => 4,
                'H6' => 5,
            );
        }
        if (isset($settings['autoBookmarks'])) {
            $pdf->h2bookmarks = array(
                'H1' => 0,
                'H2' => 1,
                'H3' => 2,
                'H4' => 3,
                'H5' => 4,
                'H6' => 5,
            );
        }
        $pdf_string = $pdf->WriteHTML($html);
        if (isset($settings['title'])) {
            $pdf->SetTitle($settings['title']);
        } elseif (isset($variables['title'])) {
            $pdf->SetTitle($variables['title']);
        }
        if (isset($settings['author'])) {
            $pdf->SetAuthor($settings['author']);
        } else {
            $pdf->SetAuthor("Made by CoolTRONIC.pl PDF Generator https://cooltronic.pl");
        }
        $pdf->SetCreator("Made by CoolTRONIC.pl PDF Generator https://cooltronic.pl");
        if (isset($settings['keywords'])) {
            $pdf->SetKeywords($settings['keywords'] . ", PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        } else {
            $pdf->SetKeywords("PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        }
        if (isset($settings['password'])) {
            $pdf->SetProtection(array(), 'UserPassword', $settings['password']);
        }

        switch ($destination) {
            case 'file':
                $output = \Mpdf\Output\Destination::FILE;
                break;
            case 'inline':
                $output = \Mpdf\Output\Destination::INLINE;
                break;
            case 'download':
                $output = \Mpdf\Output\Destination::DOWNLOAD;
                break;
            case 'string':
                $output = \Mpdf\Output\Destination::STRING_RETURN;
                break;
            default:
                $output = \Mpdf\Output\Destination::FILE;
                break;
        }
        $return = $pdf->Output($filename, $output);
        if (isset($settings['dumbThumb'])) {
            if (isset($settings['thumbType'])) {
                $assetType = $settings['thumbType'];
            } else { $assetType = "jpg";}
            // Get the pathinfo
            $infoDumb = pathinfo($filename);

            // Get the filename without extension
            $dumbThumbFilename = $infoDumb['filename'];

            // Get the directory path
            $dumpDir = $infoDumb['dirname'];
            if (!file_exists($dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType)) {
                if (isset($settings['thumbWidth'])) {
                    $cols = $settings['thumbWidth'];
                } else { $cols = 210;}
                if (isset($settings['thumbHeight'])) {
                    $rows = $settings['thumbHeight'];
                } else { $rows = 297;}
                if (isset($settings['thumbBgColor'])) {$thumbBgColor = $settings['thumbBgColor'];} else { $thumbBgColor = 'white';}
                if (isset($settings['thumbPage'])) {$thumbPage = $settings['thumbPage'];} else { $thumbPage = 0;}
                if (isset($settings['thumbTrim'])) {$thumbTrim = $settings['thumbTrim'];} else { $thumbTrim = false;}
                if (isset($settings['thumbTrimFrameColor'])) {$thumbTrimFrameColor = $settings['thumbTrimFrameColor'];} else { $thumbTrimFrameColor = false;}
                try {
                    $thumb = new GenerateThumbConfiguration(
                        $filename,
                        $dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType,
                        $assetType,
                        $thumbTrim,
                        $cols,
                        $rows,
                        false,
                        $thumbBgColor,
                        $thumbPage,
                        $thumbTrimFrameColor
                    );
                    $thumbGenerator = new GenerateThumb();
                    $thumbGenerator->convert($thumb);
                } catch (\Exception $e) {
                    // Log the error message
                    Craft::error('Error generating thumbnail: ' . $e->getMessage());
                }
            }
        }
        if ($destination == 'file') {
            return $filename;
        }
        if ($destination == 'download') {
            unset($pdf);
            return $filename;
        }
        if ($destination == 'inline') {
            unset($pdf);
            return $return;
        }
        if ($destination == 'string') {
            unset($pdf);
            return $return;
        }
        return null;
    }

    public function pdfAsset($template, $tempFilename, $variables, $attributes, $volumeHandle)
    {
        $plugin = Craft::$app->plugins->getPlugin('documenthelpers');
        // Get the settings
        $settings = $plugin->getSettings();
        $settings = $settings->toArray();
        Craft::info("PDF Settins: " . StringHelper::toString($settings));
        foreach ($settings as $key => $value) {
            // Check if the value is an empty string
            if ($value === '') {
              // Set the value to null
              $settings[$key] = null;
            }
        }
        $settings = array_merge($settings, $attributes);
        Craft::info("PDF Settins2: " . StringHelper::toString($settings));
        // Generate the PDF using the existing pdf method
        $pdfPath = $this->pdf($template, 'file', $tempFilename, $variables, $attributes);
        $info = pathinfo($pdfPath);
        $plugin = Craft::$app->plugins->getPlugin('documenthelpers');
        if (isset($settings['assetFilename'])) {
            $filename = $settings['assetFilename'];
        } else {
            $filename = $info['basename'];
        }
        // Set the volume ID of the asset
        $volumeId = Craft::$app->volumes->getVolumeByHandle($volumeHandle)->id;

        // Find the existing asset
        $assetQuery = \craft\elements\Asset::find();
        $assetQuery->filename = $filename;
        $assetQuery->volumeId = $volumeId;
        $asset = $assetQuery->one();

        // If the asset doesn't exist or the temp file is newer, create or update the asset
        if (!$asset || filemtime($tempFilename) > $asset->dateModified->getTimestamp()) {
            if (!$asset) {
                $asset = new \craft\elements\Asset ();
            }

            if (isset($settings['assetTitle'])) {
                $asset->title = $settings['assetTitle'];
            }

            if (isset($settings['assetSiteId'])) {
                $asset->siteId = $settings['assetSiteId'];
            }

            $asset->volumeId = $volumeId;

            // Find the folder where the asset will be stored
            $folder = Craft::$app->assets->getRootFolderByVolumeId($volumeId);

            // Get the ID of the folder
            $folderId = $folder->id;
            $tempCopyInfo = pathinfo($pdfPath);
            $tempName = $tempCopyInfo['basename'];
            $tempDirName = $tempCopyInfo['dirname'];
            // Add these lines
            $copyFilename = $tempDirName . '/copy_' . $tempName;
            if (!copy($tempFilename, $copyFilename)) {
                Craft::error('Failed to copy file: ' . $tempFilename);
            }

            // Set the temporary file path of the asset to the path of the generated PDF
            $asset->tempFilePath = $copyFilename;

            // Set the filename of the asset to the basename of the generated PDF
            $asset->filename = $filename;

            // Set the new folder ID of the asset to the ID of the folder
            $asset->newFolderId = $folderId;

            // Set the scenario of the asset to create
            $asset->setScenario(\craft\elements\Asset::SCENARIO_DEFAULT);
            // Save the asset
            
            $result = Craft::$app->getElements()->saveElement($asset);
            // Check if the asset was saved successfully
            if (!$result) {
                return false;
                Craft::error("Can't find asset: " . $filename . ', in volume: ' . $volumeHandle);
            }
        }

        if (isset($settings['assetThumb'])) {
            if (isset($settings['thumbType'])) {
                $assetType = $settings['thumbType'];
            } else { $assetType = "jpg";}
            if (isset($settings['assetFilename'])) {
                $finalNameThumb = $settings['assetFilename'] . '.' . $assetType;
            } else {
                $finalNameThumb = $info['basename'] . '.' . $assetType;
            }
            // Get the pathinfo
            $infoThumb = pathinfo($tempFilename);

            // Get the filename without extension
            $fileTempName = $infoThumb['filename'];

            // Get the directory path
            $dirTemp = $infoThumb['dirname'];
            if (isset($settings['thumbWidth'])) {
                $cols = $settings['thumbWidth'];
            } else { $cols = 210;}
            if (isset($settings['thumbHeight'])) {
                $rows = $settings['thumbHeight'];
            } else { $rows = 297;}
            if (isset($settings['thumbBgColor'])) {$thumbBgColor = $settings['thumbBgColor'];} else { $thumbBgColor = 'white';}
            if (isset($settings['thumbPage'])) {$thumbPage = $settings['thumbPage'];} else { $thumbPage = 0;}
            if (isset($settings['thumbTrim'])) {$thumbTrim = $settings['thumbTrim'];} else { $thumbTrim = false;}
            if (isset($settings['thumbTrimFrameColor'])) {$thumbTrimFrameColor = $settings['thumbTrimFrameColor'];} else { $thumbTrimFrameColor = false;}
            try {
                $thumb = new GenerateThumbConfiguration(
                    $pdfPath,
                    $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType,
                    $assetType,
                    $thumbTrim,
                    $cols,
                    $rows,
                    false,
                    $thumbBgColor,
                    $thumbPage,
                    $thumbTrimFrameColor
                );
                $thumbGenerator = new GenerateThumb();
                $thumbGenerator->convert($thumb);
            } catch (\Exception $e) {
                // Log the error message
                Craft::error('Error generating thumbnail: ' . $e->getMessage());
            }

            if (isset($settings['assetThumbVolumeHandle'])) {
                $thumbVolumeHandle = $settings['assetThumbVolumeHandle'];
                $thumbVolumeId = Craft::$app->volumes->getVolumeByHandle($settings['assetThumbVolumeId'])->id;
            } else {
                $thumbVolumeHandle = $volumeHandle;
                $thumbVolumeId = Craft::$app->volumes->getVolumeByHandle($volumeHandle)->id;
            }
            // Find the existing asset
            $assetQueryThumb = \craft\elements\Asset::find();
            $assetQueryThumb->filename = $finalNameThumb;
            $assetQueryThumb->volumeId = $thumbVolumeId;
            $assetThumb = $assetQueryThumb->one();

            // If the asset doesn't exist or the temp file is newer, create or update the asset
            if (!$assetThumb || (file_exists($dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType) && filemtime($dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType) > $assetThumb->dateModified->getTimestamp())) {
                if (!$assetThumb) {
                    $assetThumb = new \craft\elements\Asset ();
                }

                if (isset($settings['assetTitle'])) {
                    $assetThumb->title = $settings['assetTitle'];
                }

                if (isset($settings['assetSiteId'])) {
                    $assetThumb->siteId = $settings['assetSiteId'];
                }

                $assetThumb->volumeId = $thumbVolumeId;

                $folder = Craft::$app->assets->getRootFolderByVolumeId($thumbVolumeId);

                $folderId = $folder->id;

                $assetThumb->tempFilePath = $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType;

                $assetThumb->filename = $finalNameThumb;

                $assetThumb->newFolderId = $folderId;

                $assetThumb->setScenario(\craft\elements\Asset::SCENARIO_DEFAULT);

                try {
                    $resultThumb = Craft::$app->getElements()->saveElement($assetThumb);
                } catch (\Exception $e) {
                    Craft::error('Error relocating thumbnail: ' . $e->getMessage());
                }
                if (!isset($resultThumb)) {
                    return false;
                    Craft::error("Can't find assetThumb: " . $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType . " and save it into: " . $filename . '.' . $assetType . ", in volume: " . $assetVolumeHandle);
                }
            }
        }
        $extendedAsset = new ExtendedAsset();
        foreach ($asset->getAttributes() as $name => $value) {
            $extendedAsset->$name = $value;
        }
        if (isset($settings['assetThumb'])) {$extendedAsset->assetThumb = $assetThumb;}

        if (isset($settings['assetDelete'])) {
            if (file_exists($tempFilename)) {
                if (unlink($tempFilename)) {
                    Craft::info("Deleted (unlink) temporary PDF file on path: " . $tempFilename);
                } else {
                    Craft::error("Deletion error (unlink) of temporary PDF file on path: " . $tempFilename);
                }
            }

            if (isset($settings['assetThumb'])) {
                if (file_exists($dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType)) {
                    if (unlink($dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType)) {
                        Craft::info("Deleted (unlink) temporary PDF Thumb file on path: " . $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType);
                    } else {
                        Craft::error("Deletion error (unlink) of temporary PDF Thumb on path: " . $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType);
                    }
                }
            }
        }

        return $extendedAsset;
    }

}
