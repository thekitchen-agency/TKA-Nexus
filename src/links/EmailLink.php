<?php

namespace thekitchenagency\nexus\links;

use Craft;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\models\Link;

class EmailLink extends BaseLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Email');
    }

    public function getUrl(Link $link): ?string
    {
        if (is_array($link->value) || $link->value === null) {
            return null;
        }

        $email = trim((string) $link->value);
        if ($email === '') {
            return null;
        }

        $url = 'mailto:' . $email;
        $params = [];

        if (!empty($link->subject)) {
            $params['subject'] = $link->subject;
        }
        if (!empty($link->body)) {
            $params['body'] = $link->body;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[values][email]',
            'value' => ($link->type === 'email') ? $link->value : '',
            'placeholder' => 'hello@example.com',
            'type' => 'email',
        ]);
    }
}
