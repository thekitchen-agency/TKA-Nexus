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
        return $link->value ? (string) $link->value : null;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[value]',
            'value' => $link->value,
            'placeholder' => '#section-anchor or /custom/path',
        ]);
    }
}
