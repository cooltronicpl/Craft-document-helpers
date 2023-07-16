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

        if (!isset($attributes['dumbThumb'])) {
            if ((file_exists($filename) && $attributes['date'] ?? false && filemtime($filename) > $attributes['date'])) {
                return $filename;
            }
        }

        $vars = [
            'entry' => $variables->getFieldValues(),
            'custom' => $attributes['custom'] ?? null,
            'title' => $variables['title'] ?? null,
        ];

        $html = Craft::$app->getView()->renderTemplate($template, $vars);
        $html_header = $attributes['header'] ? Craft::$app->getView()->renderTemplate($attributes['header'], $vars) : null;
        $html_footer = $attributes['footer'] ? Craft::$app->getView()->renderTemplate($attributes['footer'], $vars) : null;

        $arrParameters = [
            'margin_top' => $attributes['margin_top'] ?? 30,
            'margin_left' => $attributes['margin_left'] ?? 15,
            'margin_right' => $attributes['margin_right'] ?? 15,
            'margin_bottom' => $attributes['margin_bottom'] ?? 30,
            'mirrorMargins' => $attributes['mirrorMargins'] ?? 0,
            'fontDir' => $attributes['fontDir'] ?? $defaultConfig['fontDir'],
            'fontdata' => $attributes['fontdata'] ?? $defaultFontConfig['fontdata'],
            'autoPageBreak' => $attributes["no_auto_page_break"] ?? true,
            'tempDir' => $attributes["tempDir"] ?? $pdfGeneratorPath,
            'format' => $attributes['format'] ?? null,
            'orientation' => ($attributes["landscape"] ?? false) ? 'L' : (($attributes["portrait"] ?? false) ? 'P' : null),
        ];

        $pdf = new \Mpdf\Mpdf (
            $arrParameters
        );
        if (isset($attributes['header'])) {
            $pdf_string = $pdf->SetHTMLHeader($html_header);
        }
        if (isset($attributes['footer'])) {
            $pdf_string = $pdf->SetHTMLFooter($html_footer);
        }
        if (isset($attributes['pageNumbers'])) {
            $pdf_string = $pdf->setFooter('{PAGENO}');
        }
        if (isset($attributes['watermarkImage'])) {
            $pdf->SetWatermarkImage($attributes['watermarkImage']);
            $pdf->showWatermarkImage = true;
        }
        if (isset($attributes['watermarkText'])) {
            $pdf->SetWatermarkText($attributes['watermarkText']);
            $pdf->showWatermarkText = true;
        }
        if (isset($attributes['autoToC'])) {
            $pdf->h2toc = array(
                'H1' => 0,
                'H2' => 1,
                'H3' => 2,
                'H4' => 3,
                'H5' => 4,
                'H6' => 5,
            );
        }
        if (isset($attributes['autoBookmarks'])) {
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
        if (isset($attributes['title'])) {
            $pdf->SetTitle($attributes['title']);
        } elseif (isset($variables['title'])) {
            $pdf->SetTitle($variables['title']);
        }
        if (isset($attributes['author'])) {
            $pdf->SetAuthor($attributes['author']);
        } else {
            $pdf->SetAuthor("Made by CoolTRONIC.pl PDF Generator https://cooltronic.pl");
        }
        $pdf->SetCreator("Made by CoolTRONIC.pl PDF Generator https://cooltronic.pl");
        if (isset($attributes['keywords'])) {
            $pdf->SetKeywords($attributes['keywords'] . ", PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        } else {
            $pdf->SetKeywords("PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        }
        if (isset($attributes['password'])) {
            $pdf->SetProtection(array(), 'UserPassword', $attributes['password']);
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
        if (isset($attributes['dumbThumb'])) {
            if (isset($attributes['thumbType'])) {
                $assetType = $attributes['thumbType'];
            } else { $assetType = "jpg";}
            // Get the pathinfo
            $infoDumb = pathinfo($filename);

            // Get the filename without extension
            $dumbThumbFilename = $infoDumb['filename'];

            // Get the directory path
            $dumpDir = $infoDumb['dirname'];
            if (!file_exists($dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType)) {
                if (isset($attributes['thumbWidth'])) {
                    $cols = $attributes['thumbWidth'];
                } else { $cols = 210;}
                if (isset($attributes['thumbHeight'])) {
                    $rows = $attributes['thumbHeight'];
                } else { $rows = 297;}
                if (isset($attributes['thumbBgColor'])) {$thumbBgColor = $attributes['thumbBgColor'];} else { $thumbBgColor = 'white';}
                if (isset($attributes['thumbPage'])) {$thumbPage = $attributes['thumbPage'];} else { $thumbPage = 0;}
                if (isset($attributes['thumbTrim'])) {$thumbTrim = $attributes['thumbTrim'];} else { $thumbTrim = false;}
                if (isset($attributes['thumbTrimFrameColor'])) {$thumbTrimFrameColor = $attributes['thumbTrimFrameColor'];} else { $thumbTrimFrameColor = false;}
                try {
                    $thumb = new GenerateThumbConfiguration(
                        pdfPath:$filename,
                        savePath:$dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType,
                        format:$assetType,
                        cols:$cols,
                        rows:$rows,
                        bgColor:$thumbBgColor,
                        page:$thumbPage,
                        trim:$thumbTrim,
                        frameColor:$thumbTrimFrameColor
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
        // Generate the PDF using the existing pdf method
        $pdfPath = $this->pdf($template, 'file', $tempFilename, $variables, $attributes);
        $info = pathinfo($pdfPath);
        if (isset($attributes['assetFilename'])) {
            $filename = $attributes['assetFilename'];
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

            if (isset($attributes['assetTitle'])) {
                $asset->title = $attributes['assetTitle'];
            }

            if (isset($attributes['assetSiteId'])) {
                $asset->siteId = $attributes['assetSiteId'];
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

        if (isset($attributes['assetThumb'])) {
            if (isset($attributes['thumbType'])) {
                $assetType = $attributes['thumbType'];
            } else { $assetType = "jpg";}
            if (isset($attributes['assetFilename'])) {
                $finalNameThumb = $attributes['assetFilename'] . '.' . $assetType;
            } else {
                $finalNameThumb = $info['basename'] . '.' . $assetType;
            }
            // Get the pathinfo
            $infoThumb = pathinfo($tempFilename);

            // Get the filename without extension
            $fileTempName = $infoThumb['filename'];

            // Get the directory path
            $dirTemp = $infoThumb['dirname'];
            if (isset($attributes['thumbWidth'])) {
                $cols = $attributes['thumbWidth'];
            } else { $cols = 210;}
            if (isset($attributes['thumbHeight'])) {
                $rows = $attributes['thumbHeight'];
            } else { $rows = 297;}
            if (isset($attributes['thumbBgColor'])) {$thumbBgColor = $attributes['thumbBgColor'];} else { $thumbBgColor = 'white';}
            if (isset($attributes['thumbPage'])) {$thumbPage = $attributes['thumbPage'];} else { $thumbPage = 0;}
            if (isset($attributes['thumbTrim'])) {$thumbTrim = $attributes['thumbTrim'];} else { $thumbTrim = false;}
            if (isset($attributes['thumbTrimFrameColor'])) {$thumbTrimFrameColor = $attributes['thumbTrimFrameColor'];} else { $thumbTrimFrameColor = false;}
            try {
                $thumb = new GenerateThumbConfiguration(
                    pdfPath:$pdfPath,
                    savePath:$dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType,
                    format:$assetType,
                    cols:$cols,
                    rows:$rows,
                    bgColor:$thumbBgColor,
                    page:$thumbPage,
                    trim:$thumbTrim,
                    frameColor:$thumbTrimFrameColor
                );
                $thumbGenerator = new GenerateThumb();
                $thumbGenerator->convert($thumb);
            } catch (\Exception $e) {
                // Log the error message
                Craft::error('Error generating thumbnail: ' . $e->getMessage());
            }

            if (isset($attributes['assetThumbVolumeHandle'])) {
                $thumbVolumeHandle = $attributes['assetThumbVolumeHandle'];
                $thumbVolumeId = Craft::$app->volumes->getVolumeByHandle($attributes['assetThumbVolumeId'])->id;
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

                if (isset($attributes['assetTitle'])) {
                    $assetThumb->title = $attributes['assetTitle'];
                }

                if (isset($attributes['assetSiteId'])) {
                    $assetThumb->siteId = $attributes['assetSiteId'];
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
        if (isset($attributes['assetThumb'])) {$extendedAsset->assetThumb = $assetThumb;}

        if (isset($attributes['assetDelete'])) {
            if (file_exists($tempFilename)) {
                if (unlink($tempFilename)) {
                    Craft::info("Deleted (unlink) temporary PDF file on path: " . $tempFilename);
                } else {
                    Craft::error("Deletion error (unlink) of temporary PDF file on path: " . $tempFilename);
                }
            }

            if (isset($attributes['assetThumb'])) {
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
