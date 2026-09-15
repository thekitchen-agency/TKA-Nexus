<?php

namespace thekitchenagency\nexus\links;

use Craft;
use craft\elements\Category;
use thekitchenagency\nexus\base\ElementLinkType;
use thekitchenagency\nexus\models\Link;

class CategoryLink extends ElementLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Category');
    }

    public static function elementType(): string
    {
        return Category::class;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        if (!class_exists(Category::class)) {
            return '';
        }

        $element = ($link->type === 'category') ? $this->getElement($link) : null;
        $elements = $element ? [$element] : [];
        $sources = $context['sources'] ?? null;

        return Craft::$app->getView()->renderTemplate('_includes/forms/elementSelect', [
            'name' => $context['name'] . '[elements][category]',
            'elements' => $elements,
            'elementType' => Category::class,
            'limit' => 1,
            'sources' => $sources,
            'viewMode' => 'list',
        ]);
    }
}
