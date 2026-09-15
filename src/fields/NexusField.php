<?php

namespace thekitchenagency\nexus\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use GraphQL\Type\Definition\Type;
use thekitchenagency\nexus\gql\types\NexusLinkType;
use thekitchenagency\nexus\models\Link;
use thekitchenagency\nexus\Nexus;
use thekitchenagency\nexus\web\assets\NexusAsset;

class NexusField extends Field
{
    public array $allowedLinkTypes = ['entry', 'asset', 'category', 'user', 'url', 'email', 'phone', 'whatsapp', 'custom'];
    public array $allowedSources = ['*'];
    public bool $allowCustomText = true;
    public bool $allowTarget = true;
    public bool $allowTitle = false;
    public bool $allowAriaLabel = true;
    public bool $allowRel = true;
    public bool $allowAnchor = true;
    public bool $allowUtm = true;
    public bool $allowStyle = false;
    public string $layoutMode = 'standard';
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

    public static function icon(): string
    {
        return 'link';
    }

    public static function valueType(): string
    {
        return Link::class . '|null';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['allowedLinkTypes', 'allowedSources', 'availableStyles'], 'safe'],
            [['allowCustomText', 'allowTarget', 'allowTitle', 'allowAriaLabel', 'allowRel', 'allowAnchor', 'allowUtm', 'allowStyle', 'allowIcon'], 'boolean'],
            [['defaultTarget', 'layoutMode'], 'string'],
        ]);
    }

    public function getContentGqlType(): \GraphQL\Type\Definition\Type|\craft\gql\base\ObjectType
    {
        return NexusLinkType::getType();
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

        $styleRows = [];
        if (is_array($this->availableStyles)) {
            foreach ($this->availableStyles as $key => $val) {
                if (is_array($val) && isset($val['handle'])) {
                    $styleRows[] = $val;
                } else {
                    $styleRows[] = [
                        'handle' => is_string($key) ? $key : ($val['handle'] ?? ''),
                        'label' => is_string($val) ? $val : ($val['label'] ?? ''),
                    ];
                }
            }
        }

        return Craft::$app->getView()->renderTemplate('tka-nexus/_field/settings', [
            'field' => $this,
            'linkTypeOptions' => $linkTypeOptions,
            'styleRows' => $styleRows,
        ]);
    }

    public function getFormattedStyles(): array
    {
        $styles = [];
        if (is_array($this->availableStyles)) {
            foreach ($this->availableStyles as $key => $val) {
                if (is_array($val)) {
                    $k = $val['handle'] ?? (string) $key;
                    $l = $val['label'] ?? $k;
                    if ($k !== '') {
                        $styles[$k] = (string) $l;
                    }
                } elseif (is_string($val)) {
                    $styles[(string) $key] = $val;
                }
            }
        }
        return $styles;
    }

    public function setAvailableStyles(mixed $styles): void
    {
        if (is_array($styles)) {
            $normalized = [];
            foreach ($styles as $key => $row) {
                if (is_array($row) && !empty($row['handle'])) {
                    $normalized[$row['handle']] = $row['label'] ?? $row['handle'];
                } elseif (is_string($row)) {
                    $normalized[$key] = $row;
                }
            }
            $this->availableStyles = $normalized;
        } else {
            $this->availableStyles = [];
        }
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
        
        if ($this->id && $element->id) {
            $db = Craft::$app->getDb();
            
            // Delete old relations for this field & source element
            $db->createCommand()->delete(\craft\db\Table::RELATIONS, [
                'fieldId' => $this->id,
                'sourceId' => $element->id,
            ])->execute();

            if (!empty($targetIds)) {
                $rows = [];
                foreach ($targetIds as $i => $targetId) {
                    $rows[] = [
                        $this->id,
                        $element->id,
                        $element->siteId,
                        (int) $targetId,
                        $i + 1,
                    ];
                }
                $db->createCommand()->batchInsert(\craft\db\Table::RELATIONS, [
                    'fieldId',
                    'sourceId',
                    'sourceSiteId',
                    'targetId',
                    'sortOrder',
                ], $rows)->execute();
            }
        }

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
            'styles' => $this->getFormattedStyles(),
            'icons' => $icons,
            'element' => $element,
        ]);
    }
}
