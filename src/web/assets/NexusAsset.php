<?php

namespace thekitchenagency\nexus\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class NexusAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'nexus.css',
        ];

        $this->js = [
            'nexus.js',
        ];

        parent::init();
    }
}
