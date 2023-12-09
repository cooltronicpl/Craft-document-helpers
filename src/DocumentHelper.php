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
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers;

use cooltronicpl\documenthelpers\variables\DocumentHelperVariable;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use cooltronicpl\documenthelpers\models\Settings;
use cooltronicpl\documenthelpers\controller\PluginInstallController;
use Craft;
use yii\base\Event;

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

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================
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

    protected function settingsHtml(): string
    {
        // Get the settings model
        $settings = $this->getSettings();

        return \Craft::$app->getView()->renderTemplate(
            'documenthelpers/_settings',
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
            }
        );
        Craft::setAlias('@documenthelpers', __DIR__);
        parent::init();
    }

}
