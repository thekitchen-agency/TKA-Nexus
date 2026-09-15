<?php

namespace thekitchenagency\nexus\models;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\helpers\Html;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use thekitchenagency\nexus\base\LinkTypeInterface;
use thekitchenagency\nexus\Nexus;
use Twig\Markup;

class Link extends Model implements \JsonSerializable, \Stringable
{
    public ?string $type = null;
    public ?string $value = null;
    public ?int $elementId = null;
    public ?int $siteId = null;
    public ?string $customText = null;
    public ?string $title = null;
    public ?string $target = null;
    public ?string $style = null;
    public ?string $icon = null;
    public ?string $anchor = null;
    public ?array $utmParams = [];
    public ?string $subject = null;
    public ?string $body = null;
    public ?string $ariaLabel = null;
    public ?array $customAttributes = [];

    protected ?LinkTypeInterface $_linkType = null;
    protected ?ElementInterface $_element = null;
    protected bool $_elementLoaded = false;

    public function rules(): array
    {
        return [
            [['type', 'value', 'customText', 'title', 'target', 'style', 'icon', 'anchor', 'subject', 'body', 'ariaLabel'], 'string'],
            [['elementId', 'siteId'], 'integer'],
            [['utmParams', 'customAttributes'], 'safe'],
        ];
    }

    public function getLinkType(): ?LinkTypeInterface
    {
        if ($this->_linkType === null && $this->type) {
            $this->_linkType = Nexus::getInstance()->getService()->getLinkType($this->type);
        }
        return $this->_linkType;
    }

    public function getUrl(): ?string
    {
        if (!$this->type) {
            return null;
        }

        $linkType = $this->getLinkType();
        return $linkType ? $linkType->getUrl($this) : null;
    }

    public function getText(): ?string
    {
        if (!empty($this->customText)) {
            return $this->customText;
        }

        $linkType = $this->getLinkType();
        if ($linkType) {
            $title = $linkType->getTitle($this);
            if (!empty($title)) {
                return $title;
            }
        }

        return $this->getUrl();
    }

    public function getElement(): ?ElementInterface
    {
        if (!$this->_elementLoaded) {
            $linkType = $this->getLinkType();
            $this->_element = $linkType ? $linkType->getElement($this) : null;
            $this->_elementLoaded = true;
        }
        return $this->_element;
    }

    public function setElement(?ElementInterface $element): void
    {
        $this->_element = $element;
        $this->_elementLoaded = true;
        if ($element) {
            $this->elementId = (int) $element->id;
            $this->siteId = (int) $element->siteId;
        }
    }

    public function getElementId(): ?int
    {
        if ($this->elementId) {
            return (int) $this->elementId;
        }
        $linkType = $this->getLinkType();
        return $linkType ? $linkType->getElementId($this) : null;
    }

    public function getIsExternal(): bool
    {
        $url = $this->getUrl();
        if (!$url) {
            return false;
        }

        if (str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '#')) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        $siteHost = parse_url(Craft::$app->getSites()->getCurrentSite()->baseUrl, PHP_URL_HOST);
        return strtolower($host) !== strtolower((string) $siteHost);
    }

    public function getIsActive(): bool
    {
        $url = $this->getUrl();
        if (!$url) {
            return false;
        }

        $currentUrl = Craft::$app->getRequest()->getAbsoluteUrl();
        $currentPath = Craft::$app->getRequest()->getPathInfo();

        if ($url === $currentUrl || $url === '/' . $currentPath || $url === $currentPath) {
            return true;
        }

        return false;
    }

    public function getIsEmpty(): bool
    {
        if (empty($this->type)) {
            return true;
        }

        if (!empty($this->elementId)) {
            return false;
        }

        if ($this->value !== null && trim((string) $this->value) !== '') {
            return false;
        }

        if (!empty($this->customText) || !empty($this->anchor)) {
            return false;
        }

        return true;
    }

    /**
     * Render the full <a> HTML tag with intelligent attributes.
     */
    public function link(array $attributes = []): Markup
    {
        return $this->render($attributes);
    }

    public function render(array $attributes = []): Markup
    {
        $url = $this->getUrl();
        if (!$url) {
            return Template::raw('');
        }

        $text = $attributes['text'] ?? $this->getText();
        unset($attributes['text']);

        $defaultAttributes = [
            'href' => $url,
        ];

        // Handle target
        $target = $attributes['target'] ?? $this->target;
        if ($target) {
            $defaultAttributes['target'] = $target;
            if ($target === '_blank') {
                $defaultAttributes['rel'] = 'noopener noreferrer';
            }
        }

        // Handle button style classes
        if ($this->style && !isset($attributes['class'])) {
            $defaultAttributes['class'] = 'btn btn-' . $this->style;
        } elseif ($this->style && isset($attributes['class'])) {
            // Append or prepend style
            if (!str_contains($attributes['class'], 'btn-' . $this->style)) {
                $attributes['class'] = trim($attributes['class'] . ' btn-' . $this->style);
            }
        }

        // Handle title / aria-label
        if (!empty($this->title) && !isset($attributes['title'])) {
            $defaultAttributes['title'] = $this->title;
        }
        if (!empty($this->ariaLabel) && !isset($attributes['aria-label'])) {
            $defaultAttributes['aria-label'] = $this->ariaLabel;
        }

        // Merge custom attributes
        if (!empty($this->customAttributes) && is_array($this->customAttributes)) {
            foreach ($this->customAttributes as $key => $val) {
                if ($val !== null && $val !== '') {
                    $defaultAttributes[$key] = $val;
                }
            }
        }

        $finalAttributes = array_merge($defaultAttributes, $attributes);

        // Render HTML tag
        $html = Html::tag('a', Html::encode($text), $finalAttributes);
        return Template::raw($html);
    }

    /**
     * Render the icon if set.
     */
    public function renderIcon(array $options = []): Markup
    {
        if (empty($this->icon)) {
            return Template::raw('');
        }

        $iconHtml = Nexus::getInstance()->getIcons()->render($this->icon, $options);
        return Template::raw($iconHtml);
    }

    public function __toString(): string
    {
        return (string) ($this->getUrl() ?? '');
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
            'elementId' => $this->elementId,
            'siteId' => $this->siteId,
            'customText' => $this->customText,
            'title' => $this->title,
            'target' => $this->target,
            'style' => $this->style,
            'icon' => $this->icon,
            'anchor' => $this->anchor,
            'utmParams' => $this->utmParams,
            'subject' => $this->subject,
            'body' => $this->body,
            'ariaLabel' => $this->ariaLabel,
            'customAttributes' => $this->customAttributes,
        ];
    }
}
