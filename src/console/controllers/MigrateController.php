<?php

namespace thekitchenagency\nexus\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use thekitchenagency\nexus\fields\NexusField;
use yii\console\ExitCode;

class MigrateController extends Controller
{
    /**
     * Migrate fields and data from verbb/hyper to tka-nexus.
     */
    public function actionFromHyper(): int
    {
        $this->stdout("Scanning for Verbb Hyper fields to migrate...\n", Console::FG_YELLOW);

        $fieldsService = Craft::$app->getFields();
        $allFields = $fieldsService->getAllFields();
        $hyperFields = [];

        foreach ($allFields as $field) {
            $class = get_class($field);
            if (str_contains($class, 'HyperField') || str_contains($class, 'hyper')) {
                $hyperFields[] = $field;
            }
        }

        if (empty($hyperFields)) {
            $this->stdout("No Verbb Hyper fields found in this installation.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout(sprintf("Found %d Hyper field(s) to convert:\n", count($hyperFields)), Console::FG_CYAN);

        foreach ($hyperFields as $field) {
            $this->stdout(sprintf(" - %s (handle: %s)\n", $field->name, $field->handle), Console::FG_CYAN);

            // Convert to NexusField
            $nexusField = new NexusField();
            $nexusField->id = $field->id;
            $nexusField->name = $field->name;
            $nexusField->handle = $field->handle;
            $nexusField->instructions = $field->instructions;
            $nexusField->searchable = $field->searchable;
            $nexusField->translationMethod = $field->translationMethod;
            $nexusField->translationKeyFormat = $field->translationKeyFormat;

            // Preserve allowed link types & common settings
            $nexusField->allowedLinkTypes = ['entry', 'asset', 'url', 'email', 'phone', 'custom'];
            $nexusField->allowCustomText = true;
            $nexusField->allowTarget = true;
            $nexusField->allowStyle = true;
            $nexusField->allowAnchor = true;
            $nexusField->allowUtm = true;
            $nexusField->allowIcon = true;

            if ($fieldsService->saveField($nexusField)) {
                $this->stdout(sprintf("   Converted %s to NexusField successfully.\n", $field->handle), Console::FG_GREEN);
            } else {
                $this->stderr(sprintf("   Failed to convert %s: %s\n", $field->handle, implode(', ', $nexusField->getFirstErrors())), Console::FG_RED);
            }
        }

        $this->stdout("Migration completed!\n", Console::FG_GREEN);
        return ExitCode::OK;
    }
}
