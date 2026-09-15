<?php

namespace thekitchenagency\nexus\base;

use craft\base\Component;
use craft\base\ElementInterface;
use thekitchenagency\nexus\models\Link;

abstract class BaseLinkType extends Component implements LinkTypeInterface
{
    public static function identifier(): string
    {
        $class = static::class;
        $basename = substr($class, strrpos($class, '\\') + 1);
        return strtolower(preg_replace('/Link$/', '', $basename));
    }

    public function getElement(Link $link): ?ElementInterface
    {
        return null;
    }

    public function getElementId(Link $link): ?int
    {
        return null;
    }

    public function getTitle(Link $link): ?string
    {
        return $link->customText ?: $this->getUrl($link);
    }

    public function validateLink(Link $link): bool
    {
        return !empty($link->value) || !empty($link->elementId);
    }
}
