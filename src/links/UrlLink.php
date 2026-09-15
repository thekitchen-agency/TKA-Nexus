<?php

namespace thekitchenagency\nexus\links;

use Craft;
use thekitchenagency\nexus\base\BaseLinkType;
use thekitchenagency\nexus\models\Link;

class UrlLink extends BaseLinkType
{
    public static function displayName(): string
    {
        return Craft::t('tka-nexus', 'Custom URL');
    }

    public function getUrl(Link $link): ?string
    {
        if (is_array($link->value) || $link->value === null) {
            return null;
        }

        $url = trim((string) $link->value);
        if ($url === '') {
            return null;
        }

        // Handle relative URLs vs absolute URLs
        if (!preg_match('~^(?:f|ht)tps?://~i', $url) && !str_starts_with($url, '/') && !str_starts_with($url, '#') && !str_starts_with($url, '//')) {
            $url = 'https://' . $url;
        }

        // Append UTM parameters if present
        if (!empty($link->utmParams) && is_array($link->utmParams)) {
            $validUtms = array_filter($link->utmParams, fn($v) => $v !== null && $v !== '');
            if (!empty($validUtms)) {
                $parts = parse_url($url);
                $existingQuery = [];
                if (!empty($parts['query'])) {
                    parse_str($parts['query'], $existingQuery);
                }
                $merged = array_merge($existingQuery, $validUtms);
                $queryString = http_build_query($merged);

                $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
                $host = $parts['host'] ?? '';
                $port = isset($parts['port']) ? ':' . $parts['port'] : '';
                $path = $parts['path'] ?? '';
                $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

                $url = $scheme . $host . $port . $path . ($queryString ? '?' . $queryString : '') . $fragment;
            }
        }

        // Append anchor if present
        if (!empty($link->anchor)) {
            $anchor = ltrim($link->anchor, '#');
            if (!str_contains($url, '#')) {
                $url .= '#' . $anchor;
            }
        }

        return $url;
    }

    public function renderInputHtml(Link $link, array $context): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $context['name'] . '[values][url]',
            'value' => ($link->type === 'url') ? $link->value : '',
            'placeholder' => 'https://example.com',
        ]);
    }
}
