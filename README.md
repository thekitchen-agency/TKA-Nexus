# TKA Nexus (`thekitchen-agency/craft-tka-nexus`)

A modern, high-performance link and CTA field plugin for Craft CMS 5 by **thekitchen.agency**.

---

## Features

- **Craft 5 Native:** Zero third-party framework overhead, uses native JSON data storage and element relation tracking.
- **Versatile Link Types:** Entries, Assets, Categories, External URLs, Emails, Phone numbers, and Page Anchors.
- **Smart Twig API:**
  - Auto `rel="noopener noreferrer"` for `target="_blank"`.
  - Fallback text automatically derived from target element title or asset filename.
  - Active state detection (`link.isActive`) and external URL check (`link.isExternal`).
- **CTA Button Styles:** Built-in style presets selectable directly by content editors.
- **UTM Campaign Builder:** Expandable UTM query parameter builder for external marketing campaigns.
- **Integrated Icon Picker:** Visual SVG icon picker with built-in Lucide icons or custom project SVGs.
- **Relation Tracking:** Referenced entries and assets are indexed in `craft_relations` for integrity and eager loading.

---

## Twig Usage

```twig
{# Automatic anchor tag with class and fallbacks #}
{{ entry.myLink.link({ class: 'btn btn-primary' }) }}

{# Direct properties #}
<a href="{{ entry.myLink.url }}" 
   target="{{ entry.myLink.target }}"
   class="{{ entry.myLink.isActive ? 'is-active' : '' }} {{ entry.myLink.style ? 'btn-' ~ entry.myLink.style : '' }}">
  
  {% if entry.myLink.icon %}
    {{ entry.myLink.renderIcon({ class: 'w-4 h-4 mr-2' }) }}
  {% endif %}

  {{ entry.myLink.text }}
</a>

{# Check if external #}
{% if entry.myLink.isExternal %}
  <span class="badge">External</span>
{% endif %}
```

---

## Migration from Verbb Hyper

```bash
php craft tka-nexus/migrate/from-hyper
```
