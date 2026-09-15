<?php

namespace thekitchenagency\nexus\services;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\base\ElementLinkType;
use thekitchenagency\nexus\base\LinkTypeInterface;
use thekitchenagency\nexus\events\RegisterLinkTypesEvent;
use thekitchenagency\nexus\links\AssetLink;
use thekitchenagency\nexus\links\CategoryLink;
use thekitchenagency\nexus\links\CustomLink;
use thekitchenagency\nexus\links\EmailLink;
use thekitchenagency\nexus\links\EntryLink;
use thekitchenagency\nexus\links\PhoneLink;
use thekitchenagency\nexus\links\UrlLink;
use thekitchenagency\nexus\links\UserLink;
use thekitchenagency\nexus\links\WhatsAppLink;
use thekitchenagency\nexus\models\Link;

class NexusService extends Component
{
    public const EVENT_REGISTER_LINK_TYPES = 'registerLinkTypes';

    protected ?array $_linkTypes = null;

    public function getLinkTypes(): array
    {
        if ($this->_linkTypes !== null) {
            return $this->_linkTypes;
        }

        $defaultClasses = [
            'entry' => EntryLink::class,
            'asset' => AssetLink::class,
            'category' => CategoryLink::class,
            'user' => UserLink::class,
            'url' => UrlLink::class,
            'email' => EmailLink::class,
            'phone' => PhoneLink::class,
            'whatsapp' => WhatsAppLink::class,
            'custom' => CustomLink::class,
        ];

        $event = new RegisterLinkTypesEvent([
            'linkTypes' => $defaultClasses,
        ]);
        $this->trigger(self::EVENT_REGISTER_LINK_TYPES, $event);

        $this->_linkTypes = [];
        foreach ($event->linkTypes as $key => $class) {
            if (is_string($class) && class_exists($class)) {
                $instance = Craft::createObject($class);
                if ($instance instanceof LinkTypeInterface) {
                    $identifier = is_string($key) && !is_numeric($key) ? $key : $class::identifier();
                    $this->_linkTypes[$identifier] = $instance;
                }
            } elseif ($class instanceof LinkTypeInterface) {
                $identifier = is_string($key) && !is_numeric($key) ? $key : $class::identifier();
                $this->_linkTypes[$identifier] = $class;
            }
        }

        return $this->_linkTypes;
    }

    public function getLinkType(string $identifier): ?LinkTypeInterface
    {
        $types = $this->getLinkTypes();
        return $types[$identifier] ?? null;
    }

    /**
     * Normalize stored/posted value into Link object(s).
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value instanceof Link) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            // Check if multi-link array (indexed list)
            if (array_is_list($value) && !empty($value) && is_array($value[0])) {
                $links = [];
                foreach ($value as $item) {
                    $links[] = $this->createLinkModel($item);
                }
                return $links;
            }

            return $this->createLinkModel($value);
        }

        return new Link();
    }

    public function createLinkModel(array $data, ?ElementInterface $element = null): Link
    {
        $link = new Link();

        $type = $data['type'] ?? null;
        $link->type = $type;

        // Resolve elementId based on selected type or direct property
        if ($type && isset($data['elements'][$type])) {
            $elemVal = $data['elements'][$type];
            $link->elementId = !empty($elemVal) ? (int) (is_array($elemVal) ? ($elemVal[0] ?? null) : $elemVal) : null;
        } elseif (isset($data['elementId'])) {
            $elemVal = $data['elementId'];
            $link->elementId = !empty($elemVal) ? (int) (is_array($elemVal) ? ($elemVal[0] ?? null) : $elemVal) : null;
        }

        // Resolve value based on selected type or direct property
        if ($type && isset($data['values'][$type])) {
            $val = $data['values'][$type];
            $link->value = is_array($val) ? null : ($val !== null ? (string) $val : null);
        } elseif (isset($data['value'])) {
            $val = $data['value'];
            $link->value = is_array($val) ? null : ($val !== null ? (string) $val : null);
        }

        $link->siteId = isset($data['siteId']) ? (int) $data['siteId'] : ($element ? (int) $element->siteId : null);
        $link->customText = isset($data['customText']) && is_string($data['customText']) ? $data['customText'] : (isset($data['text']) && is_string($data['text']) ? $data['text'] : null);
        $link->title = isset($data['title']) && is_string($data['title']) ? $data['title'] : null;
        $link->target = isset($data['target']) && is_string($data['target']) ? $data['target'] : null;
        $link->style = isset($data['style']) && is_string($data['style']) ? $data['style'] : null;
        $link->icon = isset($data['icon']) && is_string($data['icon']) ? $data['icon'] : null;
        $link->anchor = isset($data['anchor']) && is_string($data['anchor']) ? $data['anchor'] : null;
        $link->utmParams = isset($data['utmParams']) && is_array($data['utmParams']) ? $data['utmParams'] : [];
        $link->subject = isset($data['subject']) && is_string($data['subject']) ? $data['subject'] : null;
        $link->body = isset($data['body']) && is_string($data['body']) ? $data['body'] : null;
        $link->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $link->ariaLabel = isset($data['ariaLabel']) && is_string($data['ariaLabel']) ? $data['ariaLabel'] : null;
        $link->relNofollow = !empty($data['relNofollow']);
        $link->relSponsored = !empty($data['relSponsored']);
        $link->relUgc = !empty($data['relUgc']);
        $link->customAttributes = isset($data['customAttributes']) && is_array($data['customAttributes']) ? $data['customAttributes'] : [];

        return $link;
    }

    /**
     * Serialize for database storage.
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (is_array($value)) {
            $serialized = [];
            foreach ($value as $item) {
                if ($item instanceof Link) {
                    if (!$item->getIsEmpty()) {
                        $serialized[] = $item->jsonSerialize();
                    }
                } elseif (is_array($item)) {
                    $serialized[] = $item;
                }
            }
            return empty($serialized) ? null : json_encode($serialized);
        }

        if ($value instanceof Link) {
            if ($value->getIsEmpty()) {
                return null;
            }
            return json_encode($value->jsonSerialize());
        }

        return null;
    }

    /**
     * Extract element IDs from link(s) for craft relation tracking.
     */
    public function getElementRelations(mixed $value): array
    {
        $ids = [];

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($item instanceof Link && $item->getElementId()) {
                    $ids[] = $item->getElementId();
                }
            }
        } elseif ($value instanceof Link && $value->getElementId()) {
            $ids[] = $value->getElementId();
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
