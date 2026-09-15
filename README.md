# TKA Nexus (`thekitchen-agency/craft-tka-nexus`)

A modern, high-performance link and CTA field plugin for **Craft CMS 5** by **thekitchen.agency**.

Designed as a lightweight, zero-bloat replacement for legacy link plugins with native Craft 5 architecture, intelligent fallback handling, CTA button styles, integrated SVG icon picking, and automatic relation indexing.

---

## Requirements

* **Craft CMS:** `>= 5.0.0`
* **PHP:** `>= 8.2`

---

## Installation

### 1. Require the Package
In your Craft project's `composer.json`, add the repository (if using local development) and require the package:

```bash
composer require thekitchen-agency/craft-tka-nexus
```

*(For DDEV local plugin development with path repository:)*
```bash
ddev composer require thekitchen-agency/craft-tka-nexus:"*@dev"
```

### 2. Install the Plugin
```bash
php craft plugin/install tka-nexus
```

---

## Key Features

- **100% Craft 5 Native:** Zero third-party framework overhead; stores link data cleanly in the element content row while indexing relations in `craft_relations`.
- **Comprehensive Link Types:**
  - **Entry:** Internal entries with section/type filtering & multi-site support.
  - **Asset:** Files & media with thumbnail browsing, folder navigation, and volume restrictions.
  - **Category:** Category element linking.
  - **Custom URL:** External links with automatic `https://` protocol handling.
  - **Email:** `mailto:` links with optional pre-filled subject and body.
  - **Phone:** `tel:` links with phone number sanitization.
  - **Custom / Anchor:** Page anchors (`#section`) or standalone URIs.
- **Smart Fallback Text:** If no custom link text is provided by the editor, it automatically defaults to the target Entry title or Asset filename.
- **Auto-Security Defaults:** Whenever `target="_blank"` is selected, `rel="noopener noreferrer"` is automatically injected.
- **Built-In CTA Button Styles:** Define style presets (e.g. *Primary*, *Secondary*, *Ghost*, *Outline*) that editors can select directly.
- **UTM Campaign Builder:** Expandable campaign tracking parameters (`utm_source`, `utm_medium`, `utm_campaign`) appended directly to external URLs.
- **Integrated Icon Picker:** Visual SVG icon picker with built-in Lucide icons or custom SVG icons from your theme.
- **Active & External State Helpers:** Direct boolean helpers for menu active states and outbound link indicators.

---

## Twig API & Usage Examples

### 1. Automatic Tag Generation (`.link()`)
Generates the complete `<a href="...">...</a>` HTML tag with all attributes and security headers:

```twig
{# Output: <a href="https://example.com" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Learn More</a> #}
{{ entry.buttonLink.link({ class: 'btn btn-primary' }) }}

{# Override text on the fly #}
{{ entry.buttonLink.link({ text: 'Custom Click Here' }) }}
```

---

### 2. Manual HTML Tag & Property Access
Full granular control over HTML markup and classes:

```twig
{% set link = entry.buttonLink %}

{% if not link.isEmpty %}
  <a href="{{ link.url }}"
     {% if link.target %}target="{{ link.target }}"{% endif %}
     class="nav-link {{ link.isActive ? 'is-active' : '' }} {{ link.style ? 'btn-' ~ link.style : '' }}"
     {% if link.ariaLabel %}aria-label="{{ link.ariaLabel }}"{% endif %}
     {% if link.title %}title="{{ link.title }}"{% endif %}>
    
    {# Render SVG Icon if selected #}
    {% if link.icon %}
      {{ link.renderIcon({ class: 'w-4 h-4 mr-2 inline-block' }) }}
    {% endif %}

    {# Auto-falls back to target entry title or asset filename if custom text is empty #}
    <span>{{ link.text }}</span>

    {# Display external badge if outbound #}
    {% if link.isExternal %}
      <span class="badge badge-external">External</span>
    {% endif %}
  </a>
{% endif %}
```

---

### 3. Accessing the Target Element
When linking to internal Craft Elements (Entries or Assets), you can access the underlying Element model directly:

```twig
{% set targetElement = entry.buttonLink.element %}

{% if targetElement and entry.buttonLink.type == 'entry' %}
  <p>Published on: {{ targetElement.postDate|date('short') }}</p>
  <p>Author: {{ targetElement.author.fullName }}</p>
{% elseif targetElement and entry.buttonLink.type == 'asset' %}
  <p>File Size: {{ targetElement.formattedSize }}</p>
  <p>Extension: {{ targetElement.extension|upper }}</p>
{% endif %}
```

---

### 4. Standalone Global Twig Functions & Filters

```twig
{# Standalone icon helper #}
{{ nexusIcon('arrow-right', { class: 'icon-lg' }) }}

{# Filter syntax #}
{{ entry.buttonLink|nexusLink({ class: 'btn' }) }}
```

---

## Model Properties Reference

| Property | Type | Description |
| :--- | :--- | :--- |
| `link.url` | `string\|null` | The fully resolved destination URL (including protocol, UTMs, and hash). |
| `link.text` | `string\|null` | The display text (falls back to target Entry title or Asset filename). |
| `link.customText` | `string\|null` | The raw custom text override entered by the editor. |
| `link.type` | `string` | Link type handle: `entry`, `asset`, `category`, `url`, `email`, `phone`, `custom`. |
| `link.elementId` | `int\|null` | Target element ID (for Entry, Asset, or Category links). |
| `link.element` | `ElementInterface\|null` | The resolved Craft element object. |
| `link.target` | `string\|null` | Window target (`_self` or `_blank`). |
| `link.style` | `string\|null` | Selected button style key (e.g., `primary`, `secondary`, `ghost`). |
| `link.icon` | `string\|null` | Selected icon identifier (e.g., `arrow-right`, `external-link`). |
| `link.anchor` | `string\|null` | Page jump anchor (e.g., `#contact-form`). |
| `link.utmParams` | `array` | Dictionary of UTM parameters (`utm_source`, `utm_medium`, `utm_campaign`). |
| `link.ariaLabel` | `string\|null` | Accessibility label. |
| `link.title` | `string\|null` | Hover title attribute. |
| `link.isExternal` | `bool` | Returns `true` if the URL points to an external domain. |
| `link.isActive` | `bool` | Returns `true` if the URL matches the current request URI. |
| `link.isEmpty` | `bool` | Returns `true` if no destination has been set. |

---

## Field Settings Configuration

When adding or editing a **Nexus Link** field in the Craft Control Panel:

1. **Allowed Link Types:** Choose which tabs (Entry, Asset, URL, Email, Phone, Custom) are accessible to editors.
2. **Allow Custom Text:** Toggle whether editors can override the link text.
3. **Allow CTA / Button Styles:** Enable style presets and define available classes:
   - `primary` &rarr; `Primary Button`
   - `secondary` &rarr; `Secondary Button`
   - `ghost` &rarr; `Ghost Button`
4. **Allow Icon Picker:** Enable the visual SVG icon picker.
5. **Allow Page Anchors:** Enable the `#anchor` jump link field.
6. **Allow UTM Parameters:** Enable the campaign tracking builder for external URLs.

---

## Custom Icons

In addition to the built-in Lucide SVG icons (`arrow-right`, `arrow-up-right`, `external-link`, `download`, `mail`, `phone`, `globe`, `chevron-right`, `file-text`, `sparkles`, `calendar`, `user`), you can add custom project icons:

1. Place your `.svg` files in your web root under `web/icons/`:
   ```
   web/
   └── icons/
       ├── custom-logo.svg
       └── checkmark.svg
   ```
2. Render in Twig:
   ```twig
   {{ nexusIcon('custom-logo', { class: 'w-6 h-6' }) }}
   ```

---

## Migrating from Verbb Hyper

If you are migrating an existing project from `verbb/hyper` to `tka-nexus`, run the built-in migration command:

```bash
php craft tka-nexus/migrate/from-hyper
```

This command automatically:
* Scans for all existing `verbb\hyper\fields\HyperField` fields.
* Converts field definitions and layouts to `thekitchenagency\nexus\fields\NexusField`.
* Preserves field handles, instructions, and translation settings.

---

## License & Support

* **Author:** thekitchen.agency
* **License:** Proprietary / Private
* **Support:** tech@thekitchen.agency
