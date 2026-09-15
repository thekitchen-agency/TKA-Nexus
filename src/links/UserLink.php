<?php

namespace thekitchenagency\nexus\links;

use Craft;
use craft\elements\User;
use thekitchenagency\nexus\base\ElementLinkType;
use thekitchenagency\nexus\models\Link;

class UserLink extends ElementLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'User');
    }

    public static function elementType(): string
    {
        return User::class;
    }

    public function getTitle(Link $link): ?string
    {
        if (!empty($link->customText)) {
            return $link->customText;
        }

        $user = $this->getElement($link);
        if (!$user instanceof User) {
            return null;
        }

        return $user->fullName ?: ($user->name ?: ($user->username ?: $user->email));
    }
}
