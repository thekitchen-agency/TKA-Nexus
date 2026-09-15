# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## 1.1.0 - 2026-09-15

### Added
- **Headless & GraphQL Support:** First-class `NexusLink` GraphQL schema type in Craft Pro.
- **Asset Superpowers & Download Helpers:** Added `.extension`, `.fileSize`, `.formattedFileSize`, `.mimeType`, `download: true`, and `appendFileInfo: true` support.
- **Modern Link Types:** Added **WhatsApp** (`https://wa.me/...` with message builder) and **User** (link to Craft Users) link types.
- **SEO & Accessibility (a11y):** Added `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"` directives and dedicated `aria-label` input.
- **CLI Broken Link Checker:** Added `php craft tka-nexus/links/check` console command for auditing site-wide link health and missing elements.
- **CP UI Polish:** Added element status indicators (Draft, Disabled) and asset metadata badges in the field preview bar.

## 1.0.1 - 2026-09-15

### Added
- Added high-resolution brand icon (`icon.svg`) and monochrome icon mask (`icon-mask.svg`) for Craft Control Panel and Plugin Store integration.

## 1.0.0 - 2026-09-15

### Added
- Initial release of **TKA Nexus** (`thekitchen-agency/craft-tka-nexus`) for Craft CMS 5.
- Support for Entry, Asset, Category, URL, Email, Phone, and Custom link types.
- Smart Twig API with `.link()`, `.url`, `.text`, `.isActive`, `.isExternal`, and title fallback.
- Configurable Button / CTA style presets selectable in the Control Panel.
- Integrated UTM Campaign parameter builder.
- Page jump anchor (`#section`) support.
- Built-in Lucide SVG icon picker with custom SVG support.
- Native Craft 5 relation tracking in `craft_relations` with eager loading support.
- CLI migration tool from `verbb/hyper` (`php craft tka-nexus/migrate/from-hyper`).
