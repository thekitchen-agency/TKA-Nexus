<?php

namespace thekitchenagency\nexus\links;

use Craft;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\models\Link;

class CustomLink extends BaseLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Custom / Anchor');
    }

    public function getUrl(Link $link): ?string
    {
        if (is_array($link->value) || $link->value === null) {
            return null;
        }
        return (string) $link->value;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[values][custom]',
            'value' => ($link->type === 'custom') ? $link->value : '',
            'placeholder' => '#section-anchor or /custom/path',
        ]);
    }
}
