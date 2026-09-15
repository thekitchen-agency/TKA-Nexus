<?php

namespace thekitchenagency\nexus\base;

use craft\base\ElementInterface;
use thekitchenagency\nexus\models\Link;

interface LinkTypeInterface
{
    public static function displayName(): string;

    public static function identifier(): string;

    public function getUrl(Link $link): ?string;

    public function getTitle(Link $link): ?string;

    public function getElement(Link $link): ?ElementInterface;

    public function getElementId(Link $link): ?int;

    public function renderInputHtml(Link $link, array $context): string;

    public function validateLink(Link $link): bool;
}
