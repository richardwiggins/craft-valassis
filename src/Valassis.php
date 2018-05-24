<?php
/**
 * Valassis plugin for Craft CMS 3.x
 *
 * Attach Valassis coupons to Contact form requests
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\valassis;

use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use craft\helpers\UrlHelper;
use superbig\valassis\services\Coupons as CouponsService;
use superbig\valassis\services\Customers as CustomersService;
use superbig\valassis\services\Imports as ImportsService;
use superbig\valassis\variables\ValassisVariable;
use superbig\valassis\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Valassis
 *
 * @author    Superbig
 * @package   Valassis
 * @since     1.0.0
 *
 * @property  CouponsService   $coupons
 * @property  CustomersService $customers
 * @property  ImportsService   $imports
 */
class Valassis extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Valassis
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'imports' => ImportsService::class,
        ]);

        $this->registerEventListeners();

        Craft::info(
            Craft::t(
                'valassis',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function registerEventListeners()
    {

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('valassis', ValassisVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function() {
                // Add in our event listeners that are needed for every request
                $this->installGlobalEventListeners();
                // Only respond to non-console site requests
                $request = Craft::$app->getRequest();
                if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleSiteRequest();
                }
                // AdminCP magic
                if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleCpRequest();
                }
            }
        );
    }

    public function installGlobalEventListeners()
    {

    }

    public function handleSiteRequest()
    {
        Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $e) {

        });
    }

    public function handleCpRequest()
    {
        // Plugin events
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('valassis/settings'));
                }
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_CP_URL_RULES',
                    __METHOD__
                );
                // Register our AdminCP routes
                $event->rules = array_merge(
                    $event->rules,
                    $this->customAdminCpRoutes()
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): array
    {
        $nav           = parent::getCpNavItem();
        $nav['subnav'] = [
            'coupons' => ['label' => 'Coupons', 'url' => UrlHelper::cpUrl('valassis/coupons')],
            'imports' => ['label' => 'Imports', 'url' => UrlHelper::cpUrl('valassis/imports')],
        ];

        return $nav;
    }

    protected function customAdminCpRoutes(): array
    {
        return [
            'valassis/coupons'          => 'valassis/coupon/index',
            'valassis/coupons/<id:\d+>' => 'valassis/coupon/edit',
            'valassis/imports'          => 'valassis/import/index',
            'valassis/imports/new'      => 'valassis/import/new',
            'valassis/imports/<id:\d+>' => 'valassis/import/details',
            //'valassis/settings'         => 'valassis/settings',
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'valassis/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
