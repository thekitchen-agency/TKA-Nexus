<?php

namespace thekitchenagency\nexus;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use thekitchenagency\nexus\fields\NexusField;
use thekitchenagency\nexus\services\Icons;
use thekitchenagency\nexus\services\NexusService;
use thekitchenagency\nexus\web\twig\NexusTwigExtension;
use yii\base\Event;

/**
 * Nexus Plugin
 *
 * @property-read NexusService $service
 * @property-read Icons $icons
 */
class Nexus extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                'service' => NexusService::class,
                'icons' => Icons::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Register components
        $this->setComponents([
            'service' => NexusService::class,
            'icons' => Icons::class,
        ]);

        // Register field type
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = NexusField::class;
            }
        );

        // Register Twig extension
        Craft::$app->getView()->registerTwigExtension(new NexusTwigExtension());

        // Register console commands if in console
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'thekitchenagency\nexus\console\controllers';
        }
    }

    public function getService(): NexusService
    {
        return $this->get('service');
    }

    public function getIcons(): Icons
    {
        return $this->get('icons');
    }
}
