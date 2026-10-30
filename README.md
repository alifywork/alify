# ALIFY Website

This repository contains both the original static ALIFY site and its custom WordPress theme conversion.

- **Static source:** standalone HTML pages and shared `assets/` at the repository root.
- **WordPress theme:** `wordpress-theme/alify/` — self-contained theme code and a local copy of the assets.

The WordPress theme requires ACF Pro, disables Gutenberg, defines ALIFY content types and templates, provides theme/archive/form/SEO options, and includes a WP-CLI importer for migrating the existing static-page sections into ACF flexible content. See [`wordpress-theme/alify/README.md`](wordpress-theme/alify/README.md) for installation, migration, content model, and URL setup instructions.
