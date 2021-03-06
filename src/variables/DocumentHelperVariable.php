<?php
/**
 * Document helpers plugin for Craft CMS 3.x
 *
 * Document helpers
 *
 * @link      https://cooltronic.pl
 * @copyright Copyright (c) 2021 Paweł Potacki
 */

namespace cooltronicpl\documenthelpers\variables;

use cooltronicpl\documenthelpers\DocumentHelper;

use Craft;
use cooltronicpl\documenthelpers\services\DocumentHelperService as DocumentHelperServiceService;

use craft\helpers\Template;


/**
 * @author    Paweł Potacki
 * @package   DocumentHelpers
 * @since     1.0.0
 */
class DocumentHelperVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param null $optional
     * @return string
     */

    public function pdf($template, $destination, $filename, $variables, $attributes)
    {
        
        if(file_exists ( $filename ) && isset($attributes['date'])){
            if(filemtime($filename) > $attributes['date']){ return $filename;}
        }        
        $vars['entry'] = $variables->getFieldValues();
        if(isset($variables['title'])){
            $vars['title'] = $variables['title'];
        }
        $html = Craft::$app->getView()->renderTemplate($template, $vars);
        if(isset($attributes['header'])) $html_header = Craft::$app->getView()->renderTemplate($attributes['header']);
        if(isset($attributes['footer'])) $html_footer = Craft::$app->getView()->renderTemplate($attributes['footer']);

        if(isset($attributes['margin_top'])){
            $margin_top = $attributes['margin_top'];
        }
        else{
            $margin_top = 30;
        }
        if(isset($attributes['margin_left'])){
            $margin_left = $attributes['margin_left'];
        }
        else{
            $margin_left = 15;
        }
        if(isset($attributes['margin_right'])){
            $margin_right = $attributes['margin_right'];
        }
        else{
            $margin_right = 15;
        }
        if(isset($attributes['margin_bottom'])){
            $margin_bottom = $attributes['margin_bottom'];
        }
        else{
            $margin_bottom = 30;
        }
        if(isset($attributes['mirrorMargins'])){
            $mirrorMargins = $attributes['mirrorMargins'];
        }
        else{
            $mirrorMargins = 0;
        }
        $pdf = new \Mpdf\Mpdf([
            'margin_top' => $margin_top,
            'margin_left' => $margin_left,
            'margin_right' => $margin_right,
            'margin_bottom' => $margin_bottom,
            'mirrorMargins' => $mirrorMargins
        ]);

        if(isset($attributes['header'])) $pdf_string = $pdf->SetHTMLHeader($html_header);
        if(isset($attributes['footer'])) $pdf_string = $pdf->SetHTMLFooter($html_footer);
        if(isset($attributes['pageNumbers'])) $pdf_string = $pdf->setFooter('{PAGENO}');
        $pdf_string = $pdf->WriteHTML($html);
        if(isset($variables['title'])) $pdf->SetTitle($variables['title']);

        switch ($destination){
            case "file": $output=\Mpdf\Output\Destination::FILE; break;
            case "inline": $output=\Mpdf\Output\Destination::INLINE; break;
            case "download": $output=\Mpdf\Output\Destination::DOWNLOAD; break;
            case "string": $output=\Mpdf\Output\Destination::STRING_RETURN; break;
            default: $output=\Mpdf\Output\Destination::FILE; break;

        }    
        $return = $pdf->Output($filename, $output);
        if ($destination == "file"){
            return $filename;
        }
        if ($destination == "download"){
            return $filename;
        }
        if($destination == "inline"){
            return $return;
        }
        if($destination == "string"){
            return $return;
        }
        return null;
    }


}
