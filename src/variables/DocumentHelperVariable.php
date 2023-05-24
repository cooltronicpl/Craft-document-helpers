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
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

        if (file_exists($filename) && isset($attributes['date'])) {
            if (filemtime($filename) > $attributes['date']) {
                return $filename;
            }
        }
        $vars['entry'] = $variables->getFieldValues();
        if (isset($attributes['custom'])) {
            $vars['custom'] = $attributes['custom'];
        }
        if (isset($variables['title'])) {
            $vars['title'] = $variables['title'];
        }
        $html = Craft::$app->getView()->renderTemplate($template, $vars);
        if (isset($attributes['header'])) {
            $html_header = Craft::$app->getView()->renderTemplate($attributes['header'], $vars);
        }
        if (isset($attributes['footer'])) {
            $html_footer = Craft::$app->getView()->renderTemplate($attributes['footer'], $vars);
        }

        if (isset($attributes['margin_top'])) {
            $margin_top = $attributes['margin_top'];
        } else {
            $margin_top = 30;
        }
        if (isset($attributes['margin_left'])) {
            $margin_left = $attributes['margin_left'];
        } else {
            $margin_left = 15;
        }
        if (isset($attributes['margin_right'])) {
            $margin_right = $attributes['margin_right'];
        } else {
            $margin_right = 15;
        }
        if (isset($attributes['margin_bottom'])) {
            $margin_bottom = $attributes['margin_bottom'];
        } else {
            $margin_bottom = 30;
        }
        if (isset($attributes['mirrorMargins'])) {
            $mirrorMargins = $attributes['mirrorMargins'];
        } else {
            $mirrorMargins = 0;
        }
        if (isset($attributes['fontDir'])) {
            $fontDir = $attributes['fontDir'];
        } else {
            $fontDir = $defaultConfig['fontDir'];
        }
        if (isset($attributes['fontdata'])) {
            $fontData = $attributes['fontdata'];
        } else {
            $fontData = $defaultFontConfig['fontdata'];
        }
        $arrParameters['margin_top'] = $margin_top;
        $arrParameters['margin_left'] = $margin_left;
        $arrParameters['margin_right'] = $margin_right;
        $arrParameters['margin_bottom'] = $margin_bottom;
        $arrParameters['mirrorMargins'] = $mirrorMargins;
        $arrParameters['fontDir'] = $fontDir;
        $arrParameters['fontdata'] = $fontData;
        if (isset($attributes["no_auto_page_break"])) {
            $arrParameters['autoPageBreak'] = false;
        }
        if (isset($attributes["tempDir"])) {
            $arrParameters['tempDir'] = $attributes["tempDir"];
        }
        if (isset($attributes['format'])) {
            $arrParameters['format'] = $attributes["format"];
        }
        if (isset($attributes["landscape"])) {
            $arrParameters['orientation'] = 'L';
        } elseif (isset($attributes["portrait"])) {
            $arrParameters['orientation'] = 'P';
        }

        $pdf = new \Mpdf\Mpdf(
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
}
