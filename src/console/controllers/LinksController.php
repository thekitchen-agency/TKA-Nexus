<?php

namespace thekitchenagency\nexus\console\controllers;

use Craft;
use craft\base\Element;
use craft\console\Controller;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\Console;
use GuzzleHttp\Client;
use thekitchenagency\nexus\fields\NexusField;
use thekitchenagency\nexus\models\Link;
use yii\console\ExitCode;

/**
 * Audit and verify all TKA Nexus links across the Craft CMS installation.
 */
class LinksController extends Controller
{
    public $defaultAction = 'check';

    /**
     * @var bool Whether to verify external URLs via HTTP HEAD requests
     */
    public bool $checkExternal = true;

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID === 'check') {
            $options[] = 'checkExternal';
        }
        return $options;
    }

    /**
     * Scan all Nexus fields and check for broken links, missing elements, or dead URLs.
     */
    public function actionCheck(): int
    {
        $this->stdout("\n🔗 TKA Nexus - Link Health & Integrity Checker\n", Console::FG_CYAN, Console::BOLD);
        $this->stdout("===================================================\n\n");

        // Find all Nexus fields
        $fieldsService = Craft::$app->getFields();
        $allFields = $fieldsService->getAllFields();
        $nexusFields = [];

        foreach ($allFields as $field) {
            if ($field instanceof NexusField) {
                $nexusFields[] = $field;
            }
        }

        if (empty($nexusFields)) {
            $this->stdout("No Nexus fields found in this Craft installation.\n\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout(sprintf("Found %d Nexus field(s) to scan:\n", count($nexusFields)));
        foreach ($nexusFields as $f) {
            $this->stdout(" - {$f->name} ({$f->handle})\n", Console::FG_GREY);
        }
        $this->stdout("\nScanning entries, assets, and categories...\n\n");

        $client = new Client([
            'timeout' => 3.0,
            'connect_timeout' => 2.0,
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'TKA-Nexus-Link-Checker/1.1 (Craft CMS)',
            ],
        ]);

        $totalChecked = 0;
        $totalOk = 0;
        $totalWarnings = 0;
        $totalErrors = 0;

        $rows = [];

        foreach ($nexusFields as $field) {
            // Find elements with this field populated in Craft 5 content column
            $siteRecords = (new \craft\db\Query())
                ->select(['elementId', 'siteId'])
                ->from('{{%elements_sites}}')
                ->where(['like', 'content', '"' . $field->handle . '"'])
                ->all();

            foreach ($siteRecords as $rec) {
                $element = Craft::$app->getElements()->getElementById($rec['elementId'], null, $rec['siteId']);
                if (!$element) {
                    continue;
                }

                $link = $element->getFieldValue($field->handle);
                if (!$link instanceof Link || $link->getIsEmpty()) {
                    continue;
                }

                $totalChecked++;
                $type = $link->type;
                $url = $link->getUrl();
                $elemDesc = ($element->title ?? $element->slug ?? (string) $element->id) . ' (' . $element::displayName() . ' #' . $element->id . ')';

                $status = 'OK';
                $statusColor = Console::FG_GREEN;
                $message = 'Valid';

                if ($link->getElementId()) {
                    $targetElem = $link->getElement();
                    if (!$targetElem) {
                        $status = 'BROKEN';
                        $statusColor = Console::FG_RED;
                        $message = 'Target element #' . $link->getElementId() . ' deleted/missing';
                        $totalErrors++;
                    } elseif ($targetElem->getStatus() !== Element::STATUS_ENABLED) {
                        $status = 'WARNING';
                        $statusColor = Console::FG_YELLOW;
                        $message = 'Target element is ' . ucfirst((string) $targetElem->getStatus());
                        $totalWarnings++;
                    } else {
                        $totalOk++;
                    }
                } elseif ($type === 'url' && $url) {
                    if ($this->checkExternal && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                        try {
                            $res = $client->request('HEAD', $url);
                            $code = $res->getStatusCode();
                            if ($code >= 400) {
                                $status = 'BROKEN';
                                $statusColor = Console::FG_RED;
                                $message = 'HTTP status ' . $code;
                                $totalErrors++;
                            } elseif ($code >= 300) {
                                $status = 'WARNING';
                                $statusColor = Console::FG_YELLOW;
                                $message = 'Redirect (HTTP ' . $code . ')';
                                $totalWarnings++;
                            } else {
                                $totalOk++;
                            }
                        } catch (\Throwable $e) {
                            $status = 'BROKEN';
                            $statusColor = Console::FG_RED;
                            $message = 'Connection error: ' . $e->getMessage();
                            $totalErrors++;
                        }
                    } else {
                        $totalOk++;
                    }
                } else {
                    $totalOk++;
                }

                $this->stdout(sprintf(
                    "[%s] %s | Field: %s | Type: %s | %s (%s)\n",
                    $status,
                    $elemDesc,
                    $field->handle,
                    $type,
                    $url ?: '(no url)',
                    $message
                ), $statusColor);
            }
        }

        $this->stdout("\n---------------------------------------------------\n");
        $this->stdout(sprintf("Scan complete: %d link(s) checked.\n", $totalChecked), Console::BOLD);
        $this->stdout(sprintf("✅ OK: %d\n", $totalOk), Console::FG_GREEN);
        if ($totalWarnings > 0) {
            $this->stdout(sprintf("⚠️  Warnings: %d\n", $totalWarnings), Console::FG_YELLOW);
        }
        if ($totalErrors > 0) {
            $this->stdout(sprintf("❌ Errors: %d\n\n", $totalErrors), Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("All checked links are healthy!\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }
}
