<?php

namespace thekitchenagency\nexus\base;

use Craft;
use craft\base\ElementInterface;
use thekitchenagency\nexus\models\Link;

abstract class ElementLinkType extends BaseLinkType
{
    abstract public static function elementType(): string;

    public function getElement(Link $link): ?ElementInterface
    {
        $id = $this->getElementId($link);
        if (!$id) {
            return null;
        }

        $elementType = static::elementType();
        $query = $elementType::find()->id($id);

        if ($link->siteId) {
            $query->siteId($link->siteId);
        }

        return $query->one();
    }

    public function getElementId(Link $link): ?int
    {
        if ($link->elementId) {
            return (int) $link->elementId;
        }

        if (is_numeric($link->value)) {
            return (int) $link->value;
        }

        return null;
    }

    public function getUrl(Link $link): ?string
    {
        $element = $this->getElement($link);
        if (!$element) {
            return null;
        }

        $url = $element->getUrl();

        if ($url && $link->anchor) {
            $anchor = ltrim($link->anchor, '#');
            $url .= '#' . $anchor;
        }

        return $url;
    }

    public function getTitle(Link $link): ?string
    {
        if (!empty($link->customText)) {
            return $link->customText;
        }

        $element = $this->getElement($link);
        return $element ? (string) $element->title : null;
    }
}
