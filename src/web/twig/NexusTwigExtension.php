<?php

namespace thekitchenagency\nexus\web\twig;

use craft\helpers\Template;
use thekitchenagency\nexus\models\Link;
use thekitchenagency\nexus\Nexus;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NexusTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nexusLink', [$this, 'renderLink'], ['is_safe' => ['html']]),
            new TwigFunction('nexusIcon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('nexusLink', [$this, 'renderLink'], ['is_safe' => ['html']]),
            new TwigFilter('nexusIcon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
        ];
    }

    public function renderLink(mixed $link, array $attributes = []): Markup
    {
        if ($link instanceof Link) {
            return $link->render($attributes);
        }

        if (is_string($link)) {
            $model = Nexus::getInstance()->getService()->normalizeValue($link);
            if ($model instanceof Link) {
                return $model->render($attributes);
            }
        }

        return Template::raw('');
    }

    public function renderIcon(string $name, array $options = []): Markup
    {
        $html = Nexus::getInstance()->getIcons()->render($name, $options);
        return Template::raw($html);
    }
}
