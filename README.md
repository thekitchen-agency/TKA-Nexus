# TKA Nexus (`thekitchen-agency/craft-tka-nexus`)

A modern, high-performance link, CTA, and media field plugin for **Craft CMS 5** by **thekitchen.agency**.

Designed as a lightweight, zero-bloat replacement for legacy link plugins with native Craft 5 architecture, intelligent fallback handling, CTA button styles, integrated SVG icon picking, Asset download superpowers, Headless GraphQL support, and automatic relation indexing.

---

## Requirements

* **Craft CMS:** `>= 5.0.0`
* **PHP:** `>= 8.2`

---

## Installation

### 1. Require the Package
```bash
composer require thekitchen-agency/craft-tka-nexus
```

*(For local plugin development with VCS repository in `composer.json`:)*
```bash
composer require thekitchen-agency/craft-tka-nexus:"^1.1"
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
  - **User:** Link directly to Craft User profiles and author accounts.
  - **WhatsApp:** Direct WhatsApp chat links (`https://wa.me/...`) with optional pre-filled message builder.
  - **Custom URL:** External links with automatic `https://` protocol handling.
  - **Email:** `mailto:` links with optional pre-filled subject and body.
  - **Phone:** `tel:` links with phone number sanitization.
  - **Custom / Anchor:** Page anchors (`#section`) or standalone URIs.
- **Asset Superpowers & Download Helpers:**
  - Direct access to `.extension` (`PDF`, `ZIP`), `.fileSize`, `.formattedFileSize` (`2.4 MB`), and `.mimeType`.
  - Automatic `download` attribute and `appendFileInfo` label formatting in Twig.
- **SEO & Accessibility (a11y):**
  - Search engine directives: `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"`.
  - Dedicated `ariaLabel` field in Control Panel for screen reader accessibility.
  - Intelligent `rel` generation combining `noopener noreferrer` for `_blank`.
- **Headless & GraphQL Support:** First-class `NexusLink` type in Craft Pro's GraphQL schema.
- **Built-In CTA Button Styles:** Define style presets (e.g. *Primary*, *Secondary*, *Ghost*, *Outline*) selectable directly in the CP.
- **UTM Campaign Builder:** Campaign tracking parameters (`utm_source`, `utm_medium`, `utm_campaign`) appended directly to external URLs.
- **Integrated Icon Picker:** Visual SVG icon picker with built-in Lucide icons or custom SVG icons from your theme.
- **CLI Tools:** Built-in migration from `verbb/hyper` and a health check audit command (`php craft tka-nexus/links/check`).

---

## Twig API & Usage Examples

### 1. Automatic Tag Generation (`.link()`)
Generates the complete `<a href="...">...</a>` HTML tag with all attributes, style classes, and security headers:

```twig
{# Output: <a href="https://example.com" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Learn More</a> #}
{{ entry.buttonLink.link({ class: 'btn btn-primary' }) }}

{# Override text on the fly #}
{{ entry.buttonLink.link({ text: 'Custom Click Here' }) }}
```

---

### 2. Asset Downloads with File Info
When linking to an Asset (e.g. PDF brochure, specification sheet, media):

```twig
{# Renders: <a href="..." download>Download Catalog (PDF, 2.4 MB)</a> #}
{{ entry.catalogLink.link({
  download: true,
  appendFileInfo: true
}) }}

{# Or access file metadata directly #}
{% if entry.catalogLink.isAsset %}
  <span class="file-badge">{{ entry.catalogLink.extension }}</span>
  <span class="file-size">{{ entry.catalogLink.formattedFileSize }}</span>
{% endif %}
```

---

### 3. Manual HTML Tag & Granular Property Access
Full granular control over HTML markup and classes:

```twig
{% set link = entry.buttonLink %}

{% if not link.isEmpty %}
  <a href="{{ link.url }}"
     {% if link.target %}target="{{ link.target }}"{% endif %}
     {% if link.rel %}rel="{{ link.rel }}"{% endif %}
     {% if link.ariaLabel %}aria-label="{{ link.ariaLabel }}"{% endif %}
     {% if link.title %}title="{{ link.title }}"{% endif %}
     class="nav-link {{ link.isActive ? 'is-active' : '' }} {{ link.style ? 'btn-' ~ link.style : '' }}">
    
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

### 4. WhatsApp & Direct Messaging
When a WhatsApp link type is configured with phone number and optional message:

```twig
{# Automatically generates https://wa.me/41791234567?text=Hello%20there #}
<a href="{{ entry.contactLink.url }}" target="_blank" rel="noopener noreferrer" class="whatsapp-btn">
  {{ entry.contactLink.renderIcon() }}
  <span>{{ entry.contactLink.text ?: 'Chat on WhatsApp' }}</span>
</a>
```

---

### 5. Accessing the Target Element
Access the underlying Element model directly (Entry, Asset, Category, or User):

```twig
{% set targetElement = entry.buttonLink.element %}

{% if targetElement and entry.buttonLink.type == 'entry' %}
  <p>Published on: {{ targetElement.postDate|date('short') }}</p>
  <p>Author: {{ targetElement.author.fullName }}</p>
{% elseif targetElement and entry.buttonLink.type == 'user' %}
  <p>Author Name: {{ targetElement.fullName }}</p>
  <p>Email: {{ targetElement.email }}</p>
{% endif %}
```

---

### 6. Standalone Twig Helpers

```twig
{# Standalone icon helper #}
{{ nexusIcon('arrow-right', { class: 'icon-lg' }) }}

{# Filter syntax #}
{{ entry.buttonLink|nexusLink({ class: 'btn' }) }}
```

---

## Headless & GraphQL Support

TKA Nexus registers a native `NexusLink` object in Craft Pro's GraphQL schema:

```graphql
query GetPageData {
  entries(section: "pages") {
    ... on Page {
      ctaLink {
        type
        url
        text
        customText
        title
        target
        rel
        style
        icon
        ariaLabel
        isExternal
        isActive
        isAsset
        extension
        fileSize
        formattedFileSize
        mimeType
        element {
          id
          title
          url
        }
      }
    }
  }
}
```

---

## Model Properties Reference

| Property | Type | Description |
| :--- | :--- | :--- |
| `link.url` | `string\|null` | Fully resolved destination URL (with protocols, UTM params, or hash). |
| `link.text` | `string\|null` | Display label (with automatic fallback to target element title or file name). |
| `link.customText` | `string\|null` | Custom text override entered by the editor. |
| `link.type` | `string` | Type identifier: `entry`, `asset`, `category`, `user`, `whatsapp`, `url`, `email`, `phone`, `custom`. |
| `link.elementId` | `int\|null` | Target element ID (for Entry, Asset, Category, or User links). |
| `link.element` | `ElementInterface\|null` | The resolved Craft element object. |
| `link.target` | `string\|null` | Target window (`_self` or `_blank`). |
| `link.rel` | `string\|null` | Computed `rel` attribute (`noopener noreferrer`, `nofollow`, `sponsored`, `ugc`). |
| `link.relNofollow` | `bool` | Whether `nofollow` directive is enabled. |
| `link.relSponsored` | `bool` | Whether `sponsored` directive is enabled. |
| `link.relUgc` | `bool` | Whether `ugc` directive is enabled. |
| `link.style` | `string\|null` | Selected button style key (e.g. `primary`, `secondary`, `ghost`). |
| `link.icon` | `string\|null` | Selected icon identifier. |
| `link.anchor` | `string\|null` | Page jump anchor (e.g. `#contact`). |
| `link.ariaLabel` | `string\|null` | Screen reader accessibility label. |
| `link.isExternal` | `bool` | Returns `true` if the URL points to an external domain. |
| `link.isActive` | `bool` | Returns `true` if the URL matches current request URI. |
| `link.isAsset` | `bool` | Returns `true` if destination is an Asset. |
| `link.extension` | `string\|null` | Uppercase asset file extension (`PDF`, `ZIP`, `JPG`). |
| `link.fileSize` | `int\|null` | File size in raw bytes. |
| `link.formattedFileSize` | `string\|null` | Formatted file size (`2.4 MB`, `850 KB`). |
| `link.mimeType` | `string\|null` | MIME type string (`application/pdf`). |
| `link.isEmpty` | `bool` | Returns `true` if no destination has been set. |

---

## CLI Commands

### 1. Link Health & Integrity Checker
Scans all Nexus fields across your site to detect deleted target elements, drafts, or broken 404 URLs:

```bash
php craft tka-nexus/links/check
```

Options:
* `--check-external=0` : Skip HTTP HEAD requests to external URLs for faster offline scanning.

### 2. Migrating from Verbb Hyper
Converts existing `verbb/hyper` fields to `tka-nexus` without losing configurations:

```bash
php craft tka-nexus/migrate/from-hyper
```

---

## License & Support

* **Author:** thekitchen.agency
* **License:** Proprietary / Private
* **Repository:** [https://github.com/thekitchen-agency/TKA-Nexus](https://github.com/thekitchen-agency/TKA-Nexus)
* **Support:** tech@thekitchen.agency
