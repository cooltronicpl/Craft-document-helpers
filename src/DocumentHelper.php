<?php
/**
 *
 * Class DocumentHelper
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2024 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers;

use cooltronicpl\documenthelpers\controller\PluginInstallController;
use cooltronicpl\documenthelpers\models\Settings;
use cooltronicpl\documenthelpers\variables\DocumentHelperVariable;
use cooltronicpl\documenthelpers\variables\PackageManager;
use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use craft\services\Plugins;

/**
 * Class DocumentHelpers.

 * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
 * @author    Pawel Potacki
 * @since     0.0.2
 */

class DocumentHelper extends Plugin
{

    // Static Properties
    // =========================================================================

    /**
     * @var DocumentHelper
     */
    public static $plugin;

    const EDITION_LITE = 'lite';
    const EDITION_PLUS = 'plus';
    const EDITION_PRO = 'pro';

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PLUS,
            self::EDITION_PRO,
        ];
    }

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        self::$plugin = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('documentHelper', DocumentHelperVariable::class);
                if (Craft::$app->getRequest()->getIsCpRequest()) {
                    $variable->set('packageManagerPDFGenerator', PackageManager::class);
                }
            }
        );
        Craft::setAlias('@document-helpers', __DIR__);
        parent::init();
    }

    public function isPlusEdition()
    {
        return $this->is(self::EDITION_PLUS);
    }

    public function hasCpSection()
    {
        return false;
    }

    public function hasSettings()
    {
        return true;
    }

    public $controllerMap = [
        'install' => PluginInstallController::class,
    ];

    public function rules()
    {
        $rules = parent::rules();
        $attributes = ['protection', 'disableCopyright', 'protectionCopy',
            'protectionPrint', 'protectionModify', 'protectionAnnotForms',
            'protectionExtract', 'protectionAssemble', 'protectionFillForms',
            'protectionPrintHighres', 'protectionNoUserPassword', 'generateMode', 'convertImgToCMYK'];

        foreach ($attributes as $attribute) {
            $rules[] = [$attribute, function ($attribute, $params, $validator) {
                if (!$this->isPlusEdition()) {
                    $this->$attribute = false;
                }
            }];
        }
    }

    // Protected Methods
    // =========================================================================

    protected function settingsHtml(): string
    {
        $settings = $this->getSettings();

        return \Craft::$app->getView()->renderTemplate(
            'document-helpers/_settings',
            [
                'settings' => $settings,
            ]
        );
    }

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

}
