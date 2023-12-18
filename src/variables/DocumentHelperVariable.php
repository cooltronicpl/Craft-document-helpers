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
use cooltronicpl\documenthelpers\classes\ExtendedAssetv3;
use cooltronicpl\documenthelpers\DocumentHelper as DocumentHelpers;
use Craft;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     0.0.2
 */
class DocumentHelperVariable
{
    /**
     * Fuction generates PDF with settings
     * @param string $template Input content as path of Twig template, URL or Code block
     * @param string $destination type of generated document
     * @param string $filename Generated PDF filename
     * @param array $variables Craft variables to parse into template
     * @param array $attributes Optional attributes passed to funtcion as `pdfOptions` which is merged and overwriten over global plugin Settings
     *
     * @return string Filename, contents of PDF file, or null on error
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
        $plugin = Craft::$app->plugins->getPlugin('document-helpers');
        $settings = $plugin->getSettings()->toArray();
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
                $settings['qrdata'] = Craft::getAlias('@document-helpers') . '/' . "resources/QR.jpg";
            }
        }

        $vars = [
            'entry' => $variables->getFieldValues(),
            'custom' => $settings['custom'] ?? null,
            'title' => $variables['title'] ?? null,
            'qrimg' => $settings['qrdata'] ?? null,
        ];
        if (isset($template)) {
            $html = $this->generateContent($template, $vars, $settings);
        }
        if (isset($settings['header'])) {
            $html_header = $this->generateContent($settings['header'], $vars, $settings);
        }
        if (isset($settings['footer'])) {
            $html_footer = $this->generateContent($settings['footer'], $vars, $settings);
        }
        if (isset($settings['mirrorMargins']) && $settings['mirrorMargins'] == true) {
            $settings['mirrorMargins'] = 1;
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
        if (isset($settings['encoding']) && !empty($settings['encoding']) && $settings['encoding'] == "utf-8" || isset($settings['encoding']) && !empty($settings['encoding']) && $settings['encoding'] == "UTF-8") {
            $pdf->charset_in = 'UTF-8';
            $pdf->allow_charset_conversion = false;
        } elseif (isset($settings['encoding']) && !empty($settings['encoding'])) {
            $pdf->charset_in = $settings['encoding'];
            $pdf->allow_charset_conversion = true;
        } else {
            $pdf->allow_charset_conversion = false;
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
        if (isset($html)) {
            $pdf_string = $pdf->WriteHTML($html);
        } else {
            $pdf_string = $pdf->WriteHTML("<p>No provided template variable</p>");
        }
        if (isset($settings['title'])) {
            $pdf->SetTitle($settings['title']);
        } elseif (isset($variables['title'])) {
            $pdf->SetTitle($variables['title']);
        }
        if (DocumentHelpers::getInstance()->isPlusEdition() && $settings['disableCopyright'] && isset($settings['author'])) {
            $pdf->SetAuthor($settings['author']);
        } elseif (isset($settings['author'])) {
            $pdf->SetAuthor($settings['author'] . " by CoolTRONIC.pl PDF Generator");
        } elseif (DocumentHelpers::getInstance()->isPlusEdition() && $settings['disableCopyright']) {
            $pdf->SetAuthor("");
        } else {
            $pdf->SetAuthor("Made by CoolTRONIC.pl PDF Generator https://cooltronic.pl");
        }
        if (DocumentHelpers::getInstance()->isPlusEdition()) {
            $pdf->SetCreator("Made by CoolTRONIC.pl PDF Generator Plus https://cooltronic.pl");
        } else {
            $pdf->SetCreator("Made by CoolTRONIC.pl PDF Generator Standard https://cooltronic.pl");
        }
        if (DocumentHelpers::getInstance()->isPlusEdition() && $settings['disableCopyright'] && isset($settings['keywords'])) {
            $pdf->SetKeywords($settings['keywords']);
        } elseif (isset($settings['keywords'])) {
            $pdf->SetKeywords($settings['keywords'] . ", PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        } elseif (DocumentHelpers::getInstance()->isPlusEdition() && $settings['disableCopyright']) {
            $pdf->SetKeywords("");
        } else {
            $pdf->SetKeywords("PDF Generator, CoolTRONIC.pl, https://cooltronic.pl");
        }
        if (isset($settings['password'])) {
            if (DocumentHelpers::getInstance()->isPlusEdition() && isset($settings['protection'])) {
                $userPassword = 'UserPassword';
                if ($settings['protection'] == 1) {
                    $settings['protection'] = array();
                    $protectionOptions = array(
                        "copy" => $settings['protectionCopy'],
                        "modify" => $settings['protectionModify'],
                        "print" => $settings['protectionPrint'],
                        "annot-forms" => $settings['protectionAnnotForms'],
                        "extract" => $settings['protectionExtract'],
                        "assemble" => $settings['protectionAssemble'],
                        "print-highres" => $settings['protectionPrintHighres'],
                        "no-user-password" => $settings['protectionNoUserPassword'],
                    );
                    foreach ($protectionOptions as $key => $value) {
                        if ($value == 1) {
                            array_push($settings['protection'], $key);
                        }
                    }
                }
                if ($this->is_json($settings['protection'])) {
                    $settings['protection'] = json_decode($settings['protection'], true);
                }
                if (is_array($settings['protection']) && !empty($settings['protection'])) {
                    foreach ($settings['protection'] as $key => $value) {
                        if ($value == "no-user-password") {
                            unset($settings['protection'][$key]);
                            $userPassword = '';
                        }
                    }
                    $pdf->SetProtection($settings['protection'], $userPassword, $settings['password']);
                } else {
                    $pdf->SetProtection(array(), 'UserPassword', $settings['password']);
                }
            } else {
                $pdf->SetProtection(array(), 'UserPassword', $settings['password']);
            }
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
        if (isset($settings['startPage']) || isset($settings['endPage'])) {
            $dir = StringHelper::toString(pathinfo($filename));
            $basename = StringHelper::toString(basename($filename));
            $tempPath = StringHelper::toString(FileHelper::normalizePath($runtimePath . '/temp/pdfgenerator'));
            if (!is_dir($tempPath . '/' . $dir)) {
                FileHelper::createDirectory($tempPath . '/' . $dir);
            }
            $pdf->Output($tempPath . '/' . $dir . '/' . $basename, 'F');
            $mpdf = new \Mpdf\Mpdf();
            if (isset($settings['startPage']) && filter_var($settings['startPage'], FILTER_VALIDATE_INT) !== false) {
                $startPage = $settings['startPage'];
            } else {
                $startPage = 1;
            }
            if (isset($settings['endPage']) && filter_var($settings['endPage'], FILTER_VALIDATE_INT) !== false) {
                $mpdf->setSourceFile($tempPath . '/' . $dir . '/' . $basename);
                $endPage = $settings['endPage'];
            } else {
                $endPage = $mpdf->setSourceFile($tempPath . '/' . $dir . '/' . $basename);
            }
            for ($i = $startPage; $i <= $endPage; $i++) {
                $tplId = $mpdf->importPage($startPage);
                $mpdf->useTemplate($tplId);
                $mpdf->AddPage();
            }
            $pdf = $mpdf;
            unset($mpdf);
        }
        $return = $pdf->Output($filename, $output);

        if (isset($settings['dumbThumb'])) {
            if (isset($settings['thumbType'])) {
                $assetType = $settings['thumbType'];
            } else {
                $assetType = "jpg";
            }
            $infoDumb = pathinfo($filename);
            $dumbThumbFilename = $infoDumb['filename'];
            $dumpDir = $infoDumb['dirname'];
            if (!file_exists($dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType)) {
                $this->makeThumb($filename, $dumpDir . DIRECTORY_SEPARATOR . $dumbThumbFilename . '.' . $assetType, $assetType, $settings);
            }
        }
        unset($pdf);

        if ($destination == 'file') {
            return $filename;
        }
        if ($destination == 'download') {
            return $filename;
        }
        if ($destination == 'inline') {
            return $return;
        }
        if ($destination == 'string') {
            return $return;
        }
        return null;
    }
    /**
     * Fuction generates PDF added to Assets
     * @param string $template Input content as path of Twig template, URL or Code block
     * @param string $tempFilename Temporary filename
     * @param array $variables Craft variables to parse into template
     * @param array $attributes Optional attributes passed to funtcion as `pdfOptions` which is merged and overwriten over global plugin Settings
     * @param string $volumeHandle Volume handle when PDF should be saved
     *
     * @return ExtendedAsset CraftCMS (4.x) or ExtendedAssetv3 (3.x) Asset with optional field assetThumb which is an Asset of image of generated ExtendedAsseet associated with PDF
     */
    public function pdfAsset($template, $tempFilename, $variables, $attributes, $volumeHandle)
    {
        $plugin = Craft::$app->plugins->getPlugin('document-helpers');
        $settings = $plugin->getSettings()->toArray();
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
        $plugin = Craft::$app->plugins->getPlugin('document-helpers');
        if (isset($settings['assetFilename'])) {
            $filename = $settings['assetFilename'];
        } else {
            $filename = $info['basename'];
        }

        // Set the volume ID of the asset
        $volumeId = Craft::$app->volumes->getVolumeByHandle($volumeHandle)->id;
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
            $folderId = $folder->id;
            $tempCopyInfo = pathinfo($pdfPath);
            $tempName = $tempCopyInfo['basename'];
            $tempDirName = $tempCopyInfo['dirname'];
            $copyFilename = $tempDirName . '/copy_' . $tempName;
            if (!copy($tempFilename, $copyFilename)) {
                Craft::error('Failed to copy file: ' . $tempFilename);
            }

            // Set the temporary file path of the asset to the path of the generated PDF
            $asset->tempFilePath = $copyFilename;
            $asset->filename = $filename;
            $asset->newFolderId = $folderId;
            $asset->setScenario(\craft\elements\Asset::SCENARIO_DEFAULT);
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
            } else {
                $assetType = "jpg";
            }
            if (isset($settings['assetFilename'])) {
                $finalNameThumb = $settings['assetFilename'] . '.' . $assetType;
            } else {
                $finalNameThumb = $info['basename'] . '.' . $assetType;
            }
            $infoThumb = pathinfo($tempFilename);
            $fileTempName = $infoThumb['filename'];
            $dirTemp = $infoThumb['dirname'];
            $this->makeThumb($pdfPath, $dirTemp . DIRECTORY_SEPARATOR . $fileTempName . '.' . $assetType, $assetType, $settings);

            if (isset($settings['assetThumbVolumeHandle'])) {
                $thumbVolumeId = Craft::$app->volumes->getVolumeByHandle($settings['assetThumbVolumeHandle'])->id;
            } else {
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

        $craftVersion = Craft::$app->getVersion();
        if (version_compare($craftVersion, '4.0', '>=')) {
            $extendedAsset = new ExtendedAsset();
        } elseif (version_compare($craftVersion, '3.0', '>=')) {
            $extendedAsset = new ExtendedAssetv3();
        }
        foreach ($asset->getAttributes() as $name => $value) {
            $extendedAsset->$name = $value;
        }
        if (isset($settings['assetThumb'])) {
            $extendedAsset->assetThumb = $assetThumb;
        }

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
        unset($asset);
        unset($assetThumb);

        return $extendedAsset;
    }

    /**
     * Fuction checking string is JSON
     * @param string $string input string
     * @return boolean
     */
    private function is_json($string)
    {
        if (is_string($string)) {
            json_decode($string);
            if (json_last_error() == JSON_ERROR_NONE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fuction getting URL
     * @param string $url input URL
     * @param boolean $isPurify sets HTMLPurifier method when possible
     * @param string $encoding sets encoding of output stream
     * @return boolean
     */
    private function getURL($url, $settings)
    {
        if (isset($settings['URLMode']) && $settings['URLMode'] == "curl") {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $html = curl_exec($ch);
            curl_close($ch);
        } else {
            $html = file_get_contents($url);
        }
        $loader = '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.autoload.php';
        if (isset($settings['URLPurify']) &&
            $settings['URLPurify'] == true &&
            file_exists(Craft::getAlias('@root') . $loader) &&
            is_file(Craft::getAlias('@root') . $loader)) {
            require_once Craft::getAlias('@root') . $loader;
            $config = \HTMLPurifier_Config::createDefault();
            if (isset($settings['encoding']) && !empty($settings['encoding'])) {
                $config->set('Core.Encoding', $settings['encoding']);
            } else {
                $config->set('Core.Encoding', 'UTF-8');
            }
            $config->set('HTML.Allowed', 'img[src|alt|width|height]');
            $config->set('URI.DisableExternalResources', false);
            $purifier = new \HTMLPurifier($config);
            $html = $purifier->purify($html);
        } elseif (isset($settings['encoding']) && !empty($settings['encoding'])) {
            $html = mb_convert_encoding($html, $settings['encoding'], mb_detect_encoding($html));
        } else {
            $html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html));
        }
        return $html;
    }

    /**
     * Fuction which is running generating Thumbnails (required ImageMagick)
     * @param string $filneame input filename
     * @param string $savePath Path thumbnails
     * @param string $type sets type of image ie. jpg, png, webp, avif
     * @param array $setting Plugin settings from control panel or parsed in $attributes array from `pdfOptions`
     * @return boolean When true image is genated
     */
    private function makeThumb($filename, $savePath, $type, $settings)
    {
        if (isset($settings['thumbWidth'])) {
            $cols = $settings['thumbWidth'];
        } else {
            $cols = 210;
        }
        if (isset($settings['thumbHeight'])) {
            $rows = $settings['thumbHeight'];
        } else {
            $rows = 297;
        }
        if (isset($settings['thumbBgColor'])) {
            $thumbBg = $settings['thumbBgColor'];
        } else {
            $thumbBg = 'white';
        }
        if (isset($settings['thumbPage'])) {
            $thumbPage = $settings['thumbPage'];
        } else {
            $thumbPage = 0;
        }
        if (isset($settings['thumbTrim'])) {
            $thumbTrim = $settings['thumbTrim'];
        } else {
            $thumbTrim = false;
        }
        if (isset($settings['thumbTrimFrameColor'])) {
            $thumbTrimFrame = $settings['thumbTrimFrameColor'];
        } else {
            $thumbTrimFrame = false;
        }
        if (isset($settings['thumbBestfit']) && $settings['thumbBestfit']) {
            $bestfit = $settings['thumbBestfit'];
        } else {
            $bestfit = false;
        }
        try {
            $thumb = new GenerateThumbConfiguration(
                $filename,
                $savePath,
                $type,
                $thumbTrim,
                $cols,
                $rows,
                $bestfit,
                $thumbBg,
                $thumbPage,
                $thumbTrimFrame
            );
            $thumbGenerator = new GenerateThumb();
            $thumbGenerator->convert($thumb);
            return true;
        } catch (\Exception $e) {
            Craft::error('Error generating thumbnail: ' . StringHelper::toString($e->getMessage()));
        }
        return false;
    }
    /**
     * Fuction which is running generating content from input content from Twig template, URL or HTML code block
     * @param string $input_content Input content as path of Twig template, URL or Code block
     * @param string $vars Variables like entry from Craft CMS
     * @param array $settings Settings parsed from `pdfOptions` or plugin Settings
     * @return string $html_content Returned HTML Content
     */
    private function generateContent($input_content, $vars, $settings)
    {
        if (file_exists(Craft::getAlias('@templates') . '/' . $input_content) && is_file(Craft::getAlias('@templates') . '/' . $input_content)) {
            try {
                $html_content = Craft::$app->getView()->renderTemplate($input_content, $vars);
            } catch (LoaderError | RuntimeError | SyntaxError | Exception $e) {
                $html_content = "<p>Error in retriving a Twig template. Error: " . StringHelper::toString($e) . "  Not compatibile \$template path:<br> " . StringHelper::toString($input_content) . "</p>";
            }
        } elseif (filter_var($input_content, FILTER_VALIDATE_URL)) {
            $html_content = $this->getURL($input_content, $settings);
            if (!isset($html_content)) {
                $html_content = "<p>Error in getting a URL template from $input_content URL. </p>";
            }
            if (isset($settings['URLTwigRender']) && $settings['URLTwigRender'] == true) {}
            try {
                $html_content = Craft::$app->getView()->renderString($html_content, $vars);
            } catch (LoaderError | SyntaxError $e) {
                $html_content = "<p>Error in retriving a URL template. Error: " . StringHelper::toString($e) . " contents:<br> " . StringHelper::toString($html_content) . "</p>";
            }

        } elseif (strip_tags($input_content) != $input_content) {
            try {
                $html_content = Craft::$app->getView()->renderString($input_content, $vars);
            } catch (LoaderError | SyntaxError $e) {
                $html_content = "<p>Error in retriving a code block template. Error: " . StringHelper::toString($e) . " contents:<br> " . StringHelper::toString($input_content) . "</p>";
            }
        } else {
            $html_content = "<p>Error in retriving a template of header. Not compatibile type of \$template, contents:<br> " . $input_content . "</p>";
        }
        return $html_content;
    }
}
