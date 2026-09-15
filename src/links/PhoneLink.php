<?php

namespace thekitchenagency\nexus\links;

use Craft;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\models\Link;

class PhoneLink extends BaseLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Phone');
    }

    public function getUrl(Link $link): ?string
    {
        if (is_array($link->value) || $link->value === null) {
            return null;
        }

        $phone = trim((string) $link->value);
        if ($phone === '') {
            return null;
        }

        // Strip non-numeric except leading plus
        $clean = preg_replace('/[^\d+]/', '', $phone);
        return 'tel:' . $clean;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[values][phone]',
            'value' => ($link->type === 'phone') ? $link->value : '',
            'placeholder' => '+41 44 123 45 67',
            'type' => 'tel',
        ]);
    }
}
