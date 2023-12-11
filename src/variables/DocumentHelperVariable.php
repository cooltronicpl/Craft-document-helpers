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
use Craft;
use craft\helpers\FileHelper;

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

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $plugin = Craft::$app->plugins->getPlugin('documenthelpers');
        // Get the settings
        $settings = $plugin->getSettings();
        $settings = $settings->toArray();
        foreach ($settings as $key => $value) {
            if ($value === '') {
                $settings[$key] = null;
            }
            if ($value == false) {
                $settings[$key] = null;
            }
        }
        $settings = array_merge($settings, $attributes);
        if (!isset($settings['dumbThumb'])) {
            if ((file_exists($filename) && isset($settings['date']) && filemtime($filename) > $settings['date'])) {
                return $filename;
            }
        }
        if (isset($settings['qrdata'])) {
            if (class_exists('chillerlan\\QRCode\\QRCode')) {
                $settings['qrdata'] = (new \chillerlan\QRCode\QRCode)->render($settings['qrdata']);
            } else {
                $settings['qrdata'] = Craft::getAlias('@documenthelpers') . '/' . "resources/QR.jpg";
            }
        }

        $vars = [
            'entry' => $variables->getFieldValues(),
            'custom' => $settings['custom'] ?? null,
            'title' => $variables['title'] ?? null,
            'qrimg' => $settings['qrdata'] ?? null,
        ];
        if (file_exists(Craft::getAlias('@templates') . '/' . $template) && is_file(Craft::getAlias('@templates') . '/' . $template)) {
            $html = Craft::$app->getView()->renderTemplate($template, $vars);
        }
        elseif (filter_var($template, FILTER_VALIDATE_URL)) {
            $html = file_get_contents($template);
            if (isset($settings['URLPurify']) && $settings['URLPurify'] == true && file_exists(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php') && is_file(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php')) {
                require_once Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php';
                $config = \HTMLPurifier_Config::createDefault();
                if (isset($settings['encoding'])) {
                    $config->set('Core.Encoding', $settings['encoding']);
                }
                $config->set('HTML.Allowed', 'img[src|alt|width|height]');
                $config->set('URI.DisableExternalResources', false);
                $purifier = new \HTMLPurifier($config);
                $html = $purifier->purify($html);

            }
        } elseif ($template != strip_tags($template)) {
            $html = Craft::$app->getView()->renderString($template, $vars);
        } 
        else {
            $html = "<h1>Error in retriving a template, contents:<br> ".$template."</h1>";
        }
        if(isset($settings['header'])){
            if (file_exists(Craft::getAlias('@templates') . '/' . $settings['header']) && is_file(Craft::getAlias('@templates') . '/' . $settings['header'])) {
                $html_header = Craft::$app->getView()->renderTemplate($settings['header'], $vars);
            }
            elseif (filter_var($settings['header'], FILTER_VALIDATE_URL)) {
                $html_header = file_get_contents($settings['header']);
                if (isset($settings['URLPurify']) && $settings['URLPurify'] == true && file_exists(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php') && is_file(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php')) {
                    require_once Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php';
                    $config = \HTMLPurifier_Config::createDefault();
                    if (isset($settings['encoding'])) {
                        $config->set('Core.Encoding', $settings['encoding']);
                    }
                    $config->set('HTML.Allowed', 'img[src|alt|width|height]');
                    $config->set('URI.DisableExternalResources', false);
                    $purifier = new \HTMLPurifier($config);
                    $html = $purifier->purify($html);
    
                }
            } elseif ($settings['header'] != strip_tags($settings['header'])) {
                $html_header = Craft::$app->getView()->renderString($settings['header'], $vars);
            } 
            else {
                $html_header = "<h1>Error in retriving a template of header, contents:<br> ".$settings['header']."</h1>";
            }
        }
        if(isset($settings['footer'])){
            if (file_exists(Craft::getAlias('@templates') . '/' . $settings['footer']) && is_file(Craft::getAlias('@templates') . '/' . $settings['footer'])) {
                $html_footer = Craft::$app->getView()->renderTemplate($settings['footer'], $vars);
            }
            elseif (filter_var($settings['footer'], FILTER_VALIDATE_URL)) {
                $html_footer = file_get_contents($settings['footer']);
                if (isset($settings['URLPurify']) && $settings['URLPurify'] == true && file_exists(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php') && is_file(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php')) {
                    require_once Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php';
                    $config = \HTMLPurifier_Config::createDefault();
                    if (isset($settings['encoding'])) {
                        $config->set('Core.Encoding', $settings['encoding']);
                    }
                    $config->set('HTML.Allowed', 'img[src|alt|width|height]');
                    $config->set('URI.DisableExternalResources', false);
                    $purifier = new \HTMLPurifier($config);
                    $html_footer = $purifier->purify($html);
    
                }
            } elseif ($settings['footer'] != strip_tags($settings['footer'])) {
                $html_footer = Craft::$app->getView()->renderString($settings['footer'], $vars);
            } 
            else {
                $html_footer = "<h1>Error in retriving a template of header, contents:<br> ".$settings['header']."</h1>";
            }
        }

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

        $pdf = new \Mpdf\Mpdf(
            $arrParameters
        );
        if (isset($settings['URLPurify']) && $settings['URLPurify'] == true && file_exists(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php') && is_file(Craft::getAlias('@root') . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php')) {
            $pdf->allow_charset_conversion = true;
            $html = iconv('UTF-8', 'UTF-8//IGNORE', $html);
        }
        else{
            $html = iconv('UTF-8', 'UTF-8//IGNORE', $html);
        }
        if (isset($settings['encoding'])) {
            $pdf->charset_in = $settings['encoding'];
        }
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
                        pdfPath: $filename,
                        savePath: $dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType,
                        format: $assetType,
                        cols: $cols,
                        rows: $rows,
                        bgColor: $thumbBgColor,
                        page: $thumbPage,
                        trim: $thumbTrim,
                        frameColor: $thumbTrimFrameColor
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
        $settings = $plugin->getSettings();
        $settings = $settings->toArray();
        foreach ($settings as $key => $value) {
            if ($value === '') {
                $settings[$key] = null;
            }
            if ($value == false) {
                $settings[$key] = null;
            }
        }
        $settings = array_merge($settings, $attributes);
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
                $asset = new \craft\elements\Asset();
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
                    pdfPath: $pdfPath,
                    savePath: $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType,
                    format: $assetType,
                    cols: $cols,
                    rows: $rows,
                    bgColor: $thumbBgColor,
                    page: $thumbPage,
                    trim: $thumbTrim,
                    frameColor: $thumbTrimFrameColor
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
                    $assetThumb = new \craft\elements\Asset();
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
