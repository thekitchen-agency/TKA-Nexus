<?php

namespace thekitchenagency\nexus\links;

use Craft;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\models\Link;

class WhatsAppLink extends BaseLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'WhatsApp');
    }

    public function getUrl(Link $link): ?string
    {
        if (is_array($link->value) || $link->value === null) {
            return null;
        }

        // Clean phone number (strip spaces, symbols, keep digits)
        $phone = preg_replace('/[^0-9]/', '', (string) $link->value);
        if ($phone === '') {
            return null;
        }

        $url = 'https://wa.me/' . $phone;

        if (!empty($link->message)) {
            $url .= '?text=' . rawurlencode($link->message);
        }

        return $url;
    }

    public function getTitle(Link $link): ?string
    {
        if (!empty($link->customText)) {
            return $link->customText;
        }

        return $link->value ? 'WhatsApp: ' . $link->value : null;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        $view = Craft::$app->getView();
        $isWhatsApp = ($link->type === 'whatsapp');

        $phoneHtml = $view->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[values][whatsapp]',
            'value' => $isWhatsApp ? $link->value : '',
            'placeholder' => '+41 79 123 45 67 (International format)',
            'type' => 'tel',
        ]);

        $messageHtml = $view->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[message]',
            'value' => $isWhatsApp ? $link->message : '',
            'placeholder' => Craft::t('tka-nexus', 'Pre-filled message (optional)'),
        ]);

        return '<div class="nexus-whatsapp-fields">' .
               '<div class="nexus-field-group">' . $phoneHtml . '</div>' .
               '<div class="nexus-field-group" style="margin-top: 8px;">' . $messageHtml . '</div>' .
               '</div>';
    }
}
