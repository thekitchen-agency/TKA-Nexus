<?php

namespace thekitchenagency\nexus\links;

use Craft;
use craft\elements\Entry;
use thekitchenagency\nexus\base\ElementLinkType;
use thekitchenagency\nexus\models\Link;

class EntryLink extends ElementLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Entry');
    }

    public static function elementType(): string
    {
        return Entry::class;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        $element = ($link->type === 'entry') ? $this->getElement($link) : null;
        $elements = $element ? [$element] : [];
        $sources = $context['sources'] ?? null;

        $config = [
            'name' => $context['name'] . '[elements][entry]',
            'elements' => $elements,
            'elementType' => Entry::class,
            'limit' => 1,
            'viewMode' => 'list',
        ];

        if (!empty($sources) && $sources !== ['*'] && $sources !== '*') {
            $config['sources'] = $sources;
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/elementSelect', $config);
    }
}
