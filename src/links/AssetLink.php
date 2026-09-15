<?php

namespace thekitchenagency\nexus\links;

use Craft;
use craft\elements\Asset;
use thekitchenagency\nexus\base\ElementLinkType;
use thekitchenagency\nexus\models\Link;

class AssetLink extends ElementLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Asset');
    }

    public static function elementType(): string
    {
        return Asset::class;
    }

    public function getTitle(Link $link): ?string
    {
        if (!empty($link->customText)) {
            return $link->customText;
        }

        $asset = $this->getElement($link);
        if (!$asset) {
            return null;
        }

        return $asset->title ?: $asset->filename;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        $element = ($link->type === 'asset') ? $this->getElement($link) : null;
        $elements = $element ? [$element] : [];
        $sources = $context['sources'] ?? null;

        return Craft::$app->getView()->renderTemplate('_includes/forms/elementSelect', [
            'name' => $context['name'] . '[elements][asset]',
            'elements' => $elements,
            'elementType' => Asset::class,
            'limit' => 1,
            'sources' => $sources,
            'viewMode' => 'list',
        ]);
    }
}
