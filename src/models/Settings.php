<?php
/**
 *
 * Class Settings
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers\models;

use craft\base\Model;

/**
 * Class Settings
 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     1.3.1
 * @since     0.4.1
 */

class Settings extends Model
{

    public $header = null;
    public $footer = null;
    public $margin_top = null;
    public $margin_bottom = null;
    public $margin_left = null;
    public $margin_right = null;
    public $pageNumbers = null;
    public $title = null;
    public $password = null;
    public $author = null;
    public $no_auto_page_break = false;
    public $keywords = null;
    public $tempDir = null;
    public $landscape = false;
    public $portrait = false;
    public $format = null;
    public $watermarkImage = null;
    public $watermarkText = null;
    public $autoToC = false;
    public $autoBookmarks = false;
    public $assetTitle = null;
    public $assetFilename = null;
    public $assetDelete = false;
    public $assetSiteId = null;
    public $assetThumb = null;
    public $assetThumbVolumeHandle = null;
    public $dumbThumb = null;
    public $qrdata = null;
    public $thumbType = null;
    public $thumbWidth = null;
    public $thumbHeight = null;
    public $thumbPage = null;
    public $thumbBgColor = null;
    public $thumbTrim = null;
    public $thumbTrimFrameColor = null;
    public $phpPath = null;
    public $encoding = null;
    public $URLPurify = null;
    
    public function rules(): array
    {
        return [
            [['no_auto_page_break'], 'boolean'],
            [['landscape'], 'boolean'],
            [['portrait'], 'boolean'],
            [['assetDelete'], 'boolean'],
            [['autoToC'], 'boolean'],
            [['autoBookmarks'], 'boolean'],
        ];
    }
}
