<?php

namespace thekitchenagency\nexus\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use thekitchenagency\nexus\models\Link;
use thekitchenagency\nexus\Nexus;
use thekitchenagency\nexus\web\assets\NexusAsset;

class NexusField extends Field
{
    public array $allowedLinkTypes = ['entry', 'asset', 'url', 'email', 'phone', 'custom'];
    public array $allowedSources = ['*'];
    public bool $allowCustomText = true;
    public bool $allowTarget = true;
    public bool $allowTitle = false;
    public bool $allowAriaLabel = true;
    public bool $allowAnchor = true;
    public bool $allowUtm = true;
    public bool $allowStyle = false;
    public array $availableStyles = [
        'primary' => 'Primary Button',
        'secondary' => 'Secondary Button',
        'ghost' => 'Ghost Button',
    ];
    public bool $allowIcon = false;
    public string $defaultTarget = '';

    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Nexus Link');
    }

    public static function valueType(): string
    {
        return Link::class . '|null';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['allowedLinkTypes', 'allowedSources', 'availableStyles'], 'safe'],
            [['allowCustomText', 'allowTarget', 'allowTitle', 'allowAriaLabel', 'allowAnchor', 'allowUtm', 'allowStyle', 'allowIcon'], 'boolean'],
            [['defaultTarget'], 'string'],
        ]);
    }

    public function getSettingsHtml(bool $inline = false): ?string
    {
        $allLinkTypes = Nexus::getInstance()->getService()->getLinkTypes();

        $linkTypeOptions = [];
        foreach ($allLinkTypes as $key => $instance) {
            $linkTypeOptions[] = [
                'label' => $instance::displayName(),
                'value' => $key,
            ];
        }

        return Craft::$app->getView()->renderTemplate('tka-nexus/_field/settings', [
            'field' => $this,
            'linkTypeOptions' => $linkTypeOptions,
        ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return Nexus::getInstance()->getService()->normalizeValue($value, $element);
    }

    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return Nexus::getInstance()->getService()->serializeValue($value, $element);
    }

    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        $value = $element->getFieldValue($this->handle);
        $targetIds = Nexus::getInstance()->getService()->getElementRelations($value);
        Craft::$app->getRelations()->saveRelations($this, $element, $targetIds);

        parent::afterElementSave($element, $isNew);
    }

    public function searchKeywords(mixed $value, ElementInterface $element): string
    {
        if ($value instanceof Link) {
            return (string) ($value->getText() . ' ' . $value->getUrl());
        }
        return '';
    }

    public function inputHtml(mixed $value, ?ElementInterface $element = null, bool $inline = false): string
    {
        Craft::$app->getView()->registerAssetBundle(NexusAsset::class);

        /** @var Link $link */
        $link = $value instanceof Link ? $value : new Link();

        $allLinkTypes = Nexus::getInstance()->getService()->getLinkTypes();
        $availableTypes = [];
        foreach ($this->allowedLinkTypes as $typeKey) {
            if (isset($allLinkTypes[$typeKey])) {
                $availableTypes[$typeKey] = $allLinkTypes[$typeKey];
            }
        }

        if (empty($availableTypes)) {
            $availableTypes = $allLinkTypes;
        }

        // Selected link type
        $currentType = $link->type;
        if (!$currentType || !isset($availableTypes[$currentType])) {
            $currentType = array_key_first($availableTypes);
        }

        $icons = $this->allowIcon ? Nexus::getInstance()->getIcons()->getAvailableIcons() : [];

        return Craft::$app->getView()->renderTemplate('tka-nexus/_field/input', [
            'name' => $this->handle,
            'field' => $this,
            'link' => $link,
            'currentType' => $currentType,
            'availableTypes' => $availableTypes,
            'icons' => $icons,
            'element' => $element,
        ]);
    }
}
