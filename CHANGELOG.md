# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

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
