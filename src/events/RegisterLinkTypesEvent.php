<?php

namespace thekitchenagency\nexus\events;

use yii\base\Event;

class RegisterLinkTypesEvent extends Event
{
    public array $linkTypes = [];
}
