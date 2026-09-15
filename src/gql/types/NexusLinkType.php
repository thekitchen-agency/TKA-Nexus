<?php

namespace thekitchenagency\nexus\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\Element as ElementInterfaceGql;
use GraphQL\Type\Definition\Type;
use thekitchenagency\nexus\models\Link;

class NexusLinkType extends ObjectType
{
    public static function getName(): string
    {
        return 'NexusLink';
    }

    public static function getType(): ObjectType
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new self([
            'name' => self::getName(),
            'description' => 'Represents the value of a TKA Nexus link field.',
            'fields' => function() {
                return [
                    'type' => [
                        'name' => 'type',
                        'type' => Type::string(),
                        'description' => 'Link type identifier (e.g. entry, asset, user, url, whatsapp, email, phone, custom)',
                    ],
                    'url' => [
                        'name' => 'url',
                        'type' => Type::string(),
                        'description' => 'Full resolved URL or action URI',
                        'resolve' => function(Link $source) {
                            return $source->getUrl();
                        },
                    ],
                    'text' => [
                        'name' => 'text',
                        'type' => Type::string(),
                        'description' => 'Resolved label / anchor text (with fallback to target element title or URL)',
                        'resolve' => function(Link $source) {
                            return $source->getText();
                        },
                    ],
                    'customText' => [
                        'name' => 'customText',
                        'type' => Type::string(),
                        'description' => 'Custom text entered in the Control Panel',
                    ],
                    'title' => [
                        'name' => 'title',
                        'type' => Type::string(),
                        'description' => 'Optional HTML title attribute',
                    ],
                    'target' => [
                        'name' => 'target',
                        'type' => Type::string(),
                        'description' => 'Target window (_blank, _self, etc.)',
                    ],
                    'rel' => [
                        'name' => 'rel',
                        'type' => Type::string(),
                        'description' => 'Computed rel attribute (noopener, noreferrer, nofollow, sponsored, ugc)',
                        'resolve' => function(Link $source) {
                            return $source->getRel();
                        },
                    ],
                    'style' => [
                        'name' => 'style',
                        'type' => Type::string(),
                        'description' => 'Selected button / CTA style preset handle',
                    ],
                    'icon' => [
                        'name' => 'icon',
                        'type' => Type::string(),
                        'description' => 'Selected icon name',
                    ],
                    'anchor' => [
                        'name' => 'anchor',
                        'type' => Type::string(),
                        'description' => 'Page jump anchor without #',
                    ],
                    'ariaLabel' => [
                        'name' => 'ariaLabel',
                        'type' => Type::string(),
                        'description' => 'Accessibility aria-label for screen readers',
                    ],
                    'isExternal' => [
                        'name' => 'isExternal',
                        'type' => Type::boolean(),
                        'description' => 'Whether link points to an external domain',
                        'resolve' => function(Link $source) {
                            return $source->getIsExternal();
                        },
                    ],
                    'isActive' => [
                        'name' => 'isActive',
                        'type' => Type::boolean(),
                        'description' => 'Whether link URL matches the current request URI',
                        'resolve' => function(Link $source) {
                            return $source->getIsActive();
                        },
                    ],
                    'isAsset' => [
                        'name' => 'isAsset',
                        'type' => Type::boolean(),
                        'description' => 'Whether target is an Asset',
                        'resolve' => function(Link $source) {
                            return $source->getIsAsset();
                        },
                    ],
                    'extension' => [
                        'name' => 'extension',
                        'type' => Type::string(),
                        'description' => 'Uppercase file extension for asset links (e.g. PDF, ZIP, JPG)',
                        'resolve' => function(Link $source) {
                            return $source->getExtension();
                        },
                    ],
                    'fileSize' => [
                        'name' => 'fileSize',
                        'type' => Type::int(),
                        'description' => 'File size in raw bytes',
                        'resolve' => function(Link $source) {
                            return $source->getFileSize();
                        },
                    ],
                    'formattedFileSize' => [
                        'name' => 'formattedFileSize',
                        'type' => Type::string(),
                        'description' => 'Human-readable file size (e.g. 2.4 MB)',
                        'resolve' => function(Link $source) {
                            return $source->getFormattedFileSize();
                        },
                    ],
                    'mimeType' => [
                        'name' => 'mimeType',
                        'type' => Type::string(),
                        'description' => 'MIME type of asset (e.g. application/pdf)',
                        'resolve' => function(Link $source) {
                            return $source->getMimeType();
                        },
                    ],
                    'element' => [
                        'name' => 'element',
                        'type' => ElementInterfaceGql::getType(),
                        'description' => 'Target element (Entry, Asset, Category, User)',
                        'resolve' => function(Link $source) {
                            return $source->getElement();
                        },
                    ],
                ];
            },
        ]));

        return $type;
    }
}
