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
        $element = $this->getElement($link);
        $elements = $element ? [$element] : [];
        $sources = $context['sources'] ?? null;

        return Craft::$app->getView()->renderTemplate('_includes/forms/elementSelect', [
            'name' => $context['name'] . '[elementId]',
            'elements' => $elements,
            'elementType' => Entry::class,
            'limit' => 1,
            'sources' => $sources,
            'viewMode' => 'list',
        ]);
    }
}
