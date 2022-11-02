<?php
/**
 * PDF Generator plugin for Craft CMS 4.x.
 *
 * Document helpers
 *
 * @see      https://cooltronic.pl
 * @see      https://potacki.com

 *
 * @copyright Copyright (c) 2022 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

namespace cooltronicpl\documenthelpers;

use cooltronicpl\documenthelpers\models\Settings;
use cooltronicpl\documenthelpers\services\DocumentHelperService as DocumentHelperServiceService;
use cooltronicpl\documenthelpers\variables\DocumentHelperVariable;
use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

/**
 * Class DocumentHelpers.
 *
 * @author    Pawel Potacki
 *
 * @since     0.0.1
 *
 * @property DocumentHelperServiceService $documentHelpersService
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
    public string  $schemaVersion = '1.0.1';

    // Public Methods
    // =========================================================================

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
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

        Craft::info(
            Craft::t(
                'document-helpers',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

}
