# ALIFY Studio WordPress Theme

A self-contained custom WordPress theme converted from the ALIFY static website. The theme keeps the existing CSS, images, fonts, icon fonts, and JavaScript locally under `assets/` and adds ACF Pro-driven content, custom post types, form handling, navigation, archive settings, and SEO output without an external SEO plugin.

## Requirements

- WordPress 6.4+
- PHP 7.4+
- ACF Pro (required for the editable sections and options pages)
- WP-CLI and PHP DOM extension only if you want to run the static-content importer

## Install

1. Upload the `alify` folder to `wp-content/themes/` (or zip this folder and upload it through **Appearance → Themes → Add New → Upload Theme**).
2. Install and activate ACF Pro.
3. Activate **ALIFY Studio**. The ACF field groups are registered in theme PHP; there is no JSON sync step.
4. Set **Settings → Permalinks → Post name** (pretty permalinks are required for the short service URLs) and click **Save Changes** once, then create/assign menus if you did not use the importer.
5. Fill in **ALIFY Options**: Global Theme Settings, Forms & Email Settings, Global SEO Settings, and the individual Home/About/Services/Industries/Case Studies/Work/Blog/Testimonials/FAQ/Pricing option screens.

Gutenberg is disabled on this site by design. Page body content is edited through the ACF Flexible Content field; each section has a hide/unhide toggle. The reusable structured layouts are available for new content. Imported legacy sections preserve the original markup and interactions, while each text node, link destination, image, alt text, placeholder, and accessible label is represented by its own editable ACF row/field. The preserved markup is read-only and serves only as the design skeleton.

## Import the existing static site once

Run the dependency-free static coverage audit first; it reports how many individual text, link, image, and accessibility fields will be generated from each source page:

```sh
python wordpress-theme/alify/tools/audit-content-fields.py
```

Then run the importer from a WordPress installation with this theme active and ACF Pro enabled. Point `--source` at the existing ALIFY static-site directory (the directory containing `index.html` and `assets/`):

```sh
wp alify import-static --source=/absolute/path/to/alify
```

The importer creates/updates the main pages, the existing sample single entries (service, industry, case study, project, and blog post), imports each top-level `<main><section>` as an ACF legacy section, rewrites local asset references and `.html` internal links, replaces legacy forms with the matching native form template, imports page/archive SEO text, sets the front page and posts page, and creates/assigns starter Primary/Footer/Legal menus. Review the imported content, menu hierarchy, links, fields, and SEO in staging before launch. Run the importer only once on a clean site or after taking a database backup; it updates matching slugs and overwrites the imported ACF section rows.

The source audit files are intentionally left untouched in the repository root. This theme lives at `wordpress-theme/alify/`.

## Content model

- **Native Posts**: blog articles; archive uses the WordPress posts page and the Blog option screen.
- **Services**: `alify_service` CPT, hierarchical; `alify_service_category` taxonomy.
- **Industries**: `alify_industry` CPT and hierarchical industry taxonomy.
- **Case Studies, Work/Projects, Testimonials, FAQs**: separate CPTs with their own archive/single templates.
- **Forms**: Contact, Project, Booking, and Newsletter have independent PHP templates in `template-parts/forms/`. Entries are stored centrally under **Form Entries** in wp-admin; recipient, subject, and success message can be configured per form in ALIFY Options.
- **Menus**: Primary, Footer, and Legal menu locations; the mobile drawer shares the Primary menu.
- **Archive content**: archive sections and SEO settings live on their ALIFY Options subpages. The dynamic CPT listing can be enabled per archive; hide the imported legacy cards section first to avoid duplicates.
- **SEO**: per-page/post/CPT and service-taxonomy title, description, canonical, noindex, social title/description/image, and schema type; global default description/social image and Organization schema. Core WordPress XML sitemaps remain enabled.

## Service URL structure

Service links are generated as `/{service-category}/{parent-slug}/{service-slug}/`; the literal taxonomy name such as `service-category` is omitted. The term archive is `/{service-category}/`. Hierarchical service parents are included automatically. Category names/slugs are editable in **Services → Service Categories**. Keep category and page slugs unique because these short root-level routes can conflict with pages; after changing taxonomy structures, save Permalinks again. Review redirects/canonicals when changing an established slug.
