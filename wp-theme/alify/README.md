# ALIFY Studio — Custom WordPress Theme (`wp-theme/alify`)

Complete bespoke WordPress theme conversion of the **ALIFY Creative Digital Agency** website (`https://alify.site`), preserving 100% of the custom CSS architecture, Bootstrap Grid-only layout (`container`, `container-fluid`, `row`, `col-*`), self-hosted **Garet / Montserrat / Poppins** typography, Font Awesome icons, **GSAP + ScrollTrigger** directional reveals & multi-depth parallax, interactive **Work Single Lightbox**, **Pricing Scope Calculator**, **20-FAQ Knowledge Hub**, and **Book a Strategy Call Slot-Picker Studio**.

---

## 1. Quick Installation in WordPress

### Option A — Upload via WordPress Admin (`.zip`)
1. Download `wp-theme/alify.zip` (or zip the `wp-theme/alify/` folder so `alify/style.css` is at the archive root).
2. In WordPress Admin, go to **Appearance → Themes → Add New Theme → Upload Theme**.
3. Select `alify.zip`, click **Install Now**, and click **Activate**.

### Option B — Direct Server / FTP Copy
1. Copy the folder `wp-theme/alify` into `wp-content/themes/alify`.
2. Activate **ALIFY Studio** under **Appearance → Themes**.

---

## 2. Automatic 21-Page Blueprint Seeding

On theme activation (`after_switch_theme`), ALIFY automatically creates all **21 studio pages** (if they don't already exist), assigns each page its dedicated template, and configures **Settings → Reading → Static Front Page = Home**:

- `/` — **Home** (`front-page.php`)
- `/about/` — **About Us** (`page-templates/template-about.php`)
- `/services/` — **Services** (`page-templates/template-services.php`)
- `/service-single/` — **Service Detail** (`page-templates/template-service-single.php`)
- `/industries/` — **Industries** (`page-templates/template-industries.php`)
- `/industry-single/` — **Industry Detail** (`page-templates/template-industry-single.php`)
- `/work/` — **Work** (`page-templates/template-work.php`)
- `/work-single/` — **Project Detail + Lightbox** (`page-templates/template-work-single.php`)
- `/case-studies/` — **Case Studies** (`page-templates/template-case-studies.php`)
- `/case-study-single/` — **Case Study Detail** (`page-templates/template-case-study-single.php`)
- `/pricing/` — **Pricing & Scope Calculator** (`page-templates/template-pricing.php`)
- `/testimonials/` — **Client Testimonials** (`page-templates/template-testimonials.php`)
- `/faq/` — **20-FAQ Knowledge Hub** (`page-templates/template-faq.php`)
- `/blog/` — **Blog & Insights** (`home.php` / `page-templates/template-blog.php`)
- `/blog-single/` — **3-Column Sticky Blog Article** (`single.php` / `page-templates/template-blog-single.php`)
- `/contact/` — **Contact Us** (`page-templates/template-contact.php`)
- `/start-project/` — **Start a Project** (`page-templates/template-start-project.php`)
- `/book-call/` — **Book a Strategy Call** (`page-templates/template-book-call.php`)
- `/thank-you/` — **Thank You Confirmation** (`page-templates/template-thank-you.php`)
- `/privacy-policy/` — **Privacy Policy** (`page-templates/template-privacy-policy.php`)
- `/terms-of-service/` — **Terms of Service** (`page-templates/template-terms-of-service.php`)
- Plus `404.php`, `archive.php`, `single.php`, `page.php`, and Custom Post Type single templates (`single-alify_service.php`, `single-alify_project.php`, `single-alify_industry.php`, `single-alify_case_study.php`).

> **Tip:** Set **Settings → Permalinks → Post name (`/%postname%/`)** and click **Save Changes** so clean URLs resolve immediately. You can also re-run page creation anytime from the one-click **Sync All 21 ALIFY Pages** button under **Appearance → Themes**.

---

## 3. Dynamic WordPress Menus (`Appearance → Menus`)

All **5 navigation areas** are dynamically managed through **Appearance → Menus** via custom ALIFY `Walker_Nav_Menu` classes (`inc/nav-walkers.php`) and are **automatically created & assigned** on theme activation:

1. **Primary Header Navigation (`primary`)** — Supports multi-level dropdown panels (`About Us`, `Services`, or any custom dropdown) with Font Awesome submenu icons and active route highlighting.
2. **Mobile Slide Drawer Navigation (`mobile_drawer`)** — Supports unlimited collapsible accordion submenus inside the right-side mobile drawer.
3. **Footer Quick Links (`footer_nav`)** — Populates Footer Column 2 (`Navigation`).
4. **Footer Services Links (`footer_services`)** — Populates Footer Column 3 (`Services`).
5. **Footer Bottom Legal Links (`footer_legal`)** — Populates Footer bottom legal links (`Privacy Policy`, `Terms of Service`).

- **Custom Submenu Icons:** Every menu item in **Appearance → Menus** includes an **"ALIFY Font Awesome Icon Class (Submenu)"** input field (e.g. `fa-solid fa-code`, `fa-solid fa-calendar-check`), plus automatic keyword icon fallback if left blank.

---

## 4. Dynamic Custom Post Types, **`Alify Custom Field`** Plugin Integration & 1-Click Starter Importer

ALIFY integrates first-class with ALIFY's own custom WordPress plugin **`Alify Custom Field`** (`https://github.com/alifywork/fieldsPlugin`, `v0.34.0+`) via `inc/alify-cf-integration.php` & `inc/alify-cf-render-bridge.php` (with graceful automatic fallback to native WordPress Meta Boxes & Customizer when the plugin is inactive):

### A. 7 Core Studio Options Pages + Paginated Page Copy Editor (`WP Admin → ALIFY Theme Settings → All Page Content`)
1. **Site Settings (`site`)** — Custom Brand Logo Image + Max Height, Agency Email (`info@alify.site`), WhatsApp (`+92 301 9328094`), HQ, Operating Hours, Header CTA Buttons & URLs, Pre-Footer CTA Banner, Footer Bio & Column Headings, Copyright, Social Media Links (`@alifywork`), and Global Reusable Sections (Testimonials Slider, Reusable FAQs, Blog Slider, Bottom Contact Section).
2. **Home Page Content (`alify_home`)** — Home Hero Background Image, Status Pill, 3-Part Display `H1`, Lead Paragraph, CTA Labels, **Hero Stat Counters Repeater** (`150+`, `98%`, `12+`), *Why Partner With ALIFY* section + **4 Pillars Repeater** + Studio Image & Quote, Home Slider Section Headings, Live 4-Client Case Studies Switcher heading/intro, and **4-Sprint Execution Process Repeater**.
3. **Archive & Hub Pages (`alify_archives`)** — Hero Eyebrows, `H1` Titles, Subtitles, Hero Background Images, and Grid Headings for `/services/`, `/work/`, `/industries/`, `/case-studies/`, and `/blog/` (including Blog Hero Weekly Card). All additional archive text is edited through **All Page Content**; the 404 target uses the `alify_conversion` option data.
4. **Pricing & About Us (`alify_pages`)** — **Pricing 3-Tier Plans Repeater** (`Launch Sprint`, `Scale Ecosystem`, `Enterprise Squad` with Sprint/Retainer prices, velocity & feature lists), Interactive Scope Calculator & Deliverable Comparison Matrix headings, About Story (2 paragraphs, **4 Story Pillars Repeater**, Founder Signature Note, studio visual & quote), Mission & Vision Bento Cards, **4 Operating Principles Bento Repeater**, **Studio Milestones Counters Repeater**, and **Leadership Principals Repeater** (Photo, Name, Role, Bio).
5. **Pricing Calc & Matrix (`alify_pricing_adv`)** — **Scope Calculator Capability Modules Repeater** (8 live modules with Font Awesome icon, USD investment `data-cost`, Sprint weeks `data-weeks`, price badge & default checked state) and **Deliverable Comparison Matrix Repeater** (14 group/feature rows across Launch Sprint, Scale Ecosystem & Enterprise Squad).
6. **Book Call, Legal & 404 (`alify_conversion`)** — Book a Strategy Call Hero, Host Avatar, Kicker, Name, Role & **3 Strategy Call Deliverables Repeater**, Contact & Start Project Heroes + Direct Studio Channels card, Testimonials & FAQ Hub Heroes, Thank You Hero + **3 Next-24-Hours Steps Repeater**, Privacy Policy & Terms of Service Last-Updated labels (plus live WordPress Block/Classic Editor `the_content()` support), and `404` Display Code/Heading/Description.
7. **Single Templates (`alify_singles`)** — Global section headings & labels for Service Single, Work Single, Industry Single, Case Study Single, and Blog Single (`Table of Contents`, Sticky Sidebar CTA Card, Default Hero Image, and Related Articles).

### B. 9 PHP-Registered Post/CPT/Page Field Groups + `alify-cf-json/` Local JSON Sync (15 JSON Schemas)
- Ships all **15 `alify-local-json` v1 (`schema_version: 12`) files** in `wp-theme/alify/alify-cf-json/` (`ALIFY_CF_JSON_DIR`) with automatic database-override detection (`_alify_cf_local_key`) so admins can optionally sync & customize any group visually under **Custom Fields → Local JSON** without duplicate field collisions.
- **Services (`alify_service`)** — Tabbed field group with Notch Code, Discipline Tag, Font Awesome Icon, Media Library Background Image, **Card Bullet Points Repeater**, Single-Page Stats, **6 Deliverables Repeater**, and **Single Tech Stack Pills**.
- **Projects (`alify_project`)** — Tabbed field group with Client, Sector, Year, Sprint Duration, Headline Metric, Deliverable Chips, Brief/Craft Narrative, **3 Verified Outcome Tiles Repeater**, Native **Multi-Image Gallery Picker**, and **Interactive Lightbox Screens Repeater** (Image + Screen Title + Tag + Caption).
- **Industries (`alify_industry`)** — Sector Code, Filter Group Select, Icon, Benchmark Stat, **Sector Deliverables Repeater**, **Single Verified Stats Repeater**, **3 Sector Friction & ALIFY Fix Cards Repeater**, and Background Image.
- **Case Studies (`alify_case_study`)** — Before/After Benchmarks, `01 The Bottleneck`, `02 The ALIFY Architecture`, 3 KPI Tiles, **Architecture Chips Repeater**, and Executive Quote.
- **Testimonials (`alify_testimonial`)** — Client Role & Company, Avatar Initials, Star Rating (`1–5`), Capability Filter Select, and Verified Outcome Pill.
- **FAQs (`alify_faq` + `faq_pillar` taxonomy)** — Knowledge Pillar grouping + Global Reusable FAQ toggle.
- **Blog Articles (`post`)** — Fallback Hero Image, Category Pill Override, Reading Time Override, and Author Display Name Override.
- **All Pre-filled Page Copy & UI Text** — The theme ships **6,076 editable fields** across **21 page templates plus the 404 page**, including headings, paragraphs, buttons, badges, stats, form labels and validation messages, placeholders, image alt text, and accessibility labels. Open **ALIFY Theme Settings → All Page Content**, choose a page, archive, 404 screen, or single-template item, then edit its prefilled copy in pages of 40 fields. Saves use the existing `_alify_cf_{field}` post meta or `alify_cf_options_{slug}` option arrays, so no giant field schemas are registered; the current page design/layout remains unchanged.
- **Structured Page Sections & Hero Controls** — `ALIFY Page Content — All Sections, Copy & Repeaters` adds per-page Hero Eyebrow/Title/Description/Tags/Media, right-side lead-card fields, nested section/card repeaters, rich copy, images, links and visibility. Section rows target the page template CSS key (e.g. `home-why-section`); existing card rows can target their CSS class, or render as new branded cards. Home is assigned a dedicated selectable template so its copy fields appear in the dashboard.
- **Page Section Visibility** — Per-page toggles control the Hero Lead Form, Testimonials, FAQs, Blog Slider, and Bottom Contact; hide reusable sections or manage a particular page's sections from the nested page repeater.
- **1-Click Starter + Media Library Importer (`Appearance → ALIFY Starter Import`)** — Imports all **67 starter items**, copies local starter images into WordPress Media Library (`_thumbnail_id` Featured Images + `Alify Custom Field` Attachment IDs), upgrades seeded `?service=` menu links to clean native `alify_service` CPT permalinks, and serves automatic `301` SEO redirects from legacy query-string single URLs.

---

## 5. Editable Heroes, Custom Logo Sizing & Dynamic Pricing Controls

- **Site Identity Custom Logo (`Appearance → Customize → Site Identity`)**:
  - Upload an image logo + adjust **Custom Logo Max Height (24–72px)**, or leave blank to display the signature `△LIFY` Garet logo.
- **Home Page Hero Customizer (`Appearance → Customize → ALIFY Home Page Hero`)**:
  - Edit Availability Status Pill, 3-part Display Headline (`Part 1`, `Highlighted`, `Part 2`), Lead Paragraph, Secondary CTA Label, Home Hero Background Image (`WP_Customize_Image_Control`), and all 3 Animated Stat Counters (`150+`, `98%`, `12+`).
- **Per-Page Hero & Visibility Meta Box (`WP Admin → Pages → Edit Page`)**:
  - Override any page's Hero Eyebrow, `H1` Heading, Subtitle, and Background Image (via Media Library picker or WordPress Featured Image), plus toggle visibility of the Right-Side Hero Lead Form, Testimonials Slider, FAQs, Blog Slider, and Bottom Contact section per page.
- **Pricing Tiers Customizer (`Appearance → Customize → ALIFY Pricing Tiers & Billing`)**:
  - Edit all 3 Studio Pricing Plans (`Launch Sprint`, `Scale Ecosystem`, `Enterprise Squad`) — Sprint prices, Monthly Retainer prices, Sprint timelines, summaries, and feature lists sync automatically with the interactive Sprint/Retainer toggle and `Start a Project` handoff.

---

## 6. Studio Contact, Executive Leads CRM, Form Templates & Forms Settings (`WP Admin → ALIFY Leads & Calls`)

- **Appearance → Customize → ALIFY Studio Contact & Brand**:
  - Studio Email (`info@alify.site`), WhatsApp (`+92 301 9328094` / `https://wa.me/923019328094`), Headquarters, Header Button Labels, and Social Links.
- **WP Admin → ALIFY Leads & Calls → All Leads & Calls (`inc/leads-crm.php`)**:
  - Captures Hero Lead Forms, Reusable Contact Forms, Contact Page, Start a Project Briefs (including Company, Budget & Timeline), Book a Strategy Call Slots, Global Strategy Call Modal, Footer Newsletter Subscribers (deduplicated by email), and any Custom Shortcode Form!
  - **Unread Counter Bubble** in WP Admin sidebar for `New` leads.
  - **Custom CRM Columns** (`Ref & Type`, `Status`, `Client & Direct WhatsApp / Email`, `Capability & Budget`, `Form Template / Booked Slot / Source`, `Received`).
  - **Admin Filter Dropdowns** by Lead Type & Pipeline Status + **1-Click `Export Leads CSV`** button + **Internal Studio Team Notes** meta box.
- **WP Admin → ALIFY Leads & Calls → Form Templates (`inc/forms-manager.php`)**:
  - Visual editor for all **6 Built-In Theme Forms** (`reusable_contact`, `contact_page`, `start_project`, `hero_lead`, `strategy_call`, `newsletter`) — edit Titles, Subtitles, Submit Button Labels, Services/Capabilities options, Budget ranges, Timeline pills, Strategy Call Tracks/Slots/Channels, Custom Redirect URLs, Per-Form Recipient Overrides, or switch any form to an **External Plugin Shortcode** (Contact Form 7, WPForms, Fluent Forms) or **Hidden**.
  - **WordPress Shortcode `[alify_form id="..."]`** — embed any built-in form or create unlimited **Custom Form Templates (`+ Create New Form Template`)** for any WordPress page or blog post.
- **WP Admin → ALIFY Leads & Calls → Forms Settings (`inc/forms-manager.php` & `inc/ajax-forms.php`)**:
  - Manage Admin Email Notifications (multiple To, CC, BCC, Sender Name/Email, Subject tags, and **1-Click Test Email**), **Client Auto-Responder Confirmation Emails**, **Redirect vs. Inline Confirmation Mode**, **Honeypot + IP Rate Limiting + Blocked Spam Domains**, and **Outbound CRM Webhooks (Zapier / Make / Slack)**.
- **WP Admin → ALIFY Leads & Calls → SMTP Mailer (`inc/smtp-mailer.php`)**:
  - Built-in authenticated SMTP delivery (`PHPMailer` TLS/SSL, AES-256 encrypted password storage, 1-click Hostinger / Google Workspace / Zoho / Brevo presets, live HTML Test Email sender, and `wp_mail_failed` diagnostic log).

---

## 7. Reusable Section Shortcodes, Gutenberg Blocks, Translation Catalog & Child Theme

- **11 Section Shortcodes (`inc/shortcodes-blocks.php`)** — Compose custom landing pages anywhere with `[alify_services layout="slider|grid"]`, `[alify_projects layout="panels|grid"]`, `[alify_industries layout="slider|grid"]`, `[alify_case_studies]`, `[alify_process]`, `[alify_pricing]`, `[alify_scope_calculator]`, `[alify_testimonials]`, `[alify_faqs]`, `[alify_blog_slider]`, and `[alify_contact_section]` (all support `eyebrow="..."` and `title="..."` attributes + automatic CSS/JS asset enqueuing).
- **8 Dynamic Gutenberg Blocks (`alify_register_block_type`)** — Available natively inside the WordPress Block Editor when **`Alify Custom Field`** is active (`alify/services-showcase`, `alify/projects-showcase`, `alify/industries-showcase`, `alify/case-studies-stage`, `alify/sprint-process`, `alify/pricing-tiers`, `alify/testimonials-slider`, `alify/faq-accordion`).
- **Translation Ready (`languages/alify.pot`)** — Ships complete Gettext `.pot` catalog (333 strings) for Loco Translate, Poedit, or WPML/Polylang.
- **Update-Safe Child Theme (`wp-theme/alify-child` / `wp-theme/alify-child.zip`)** — Ready-to-activate child theme (`Template: alify`) with automatic parent-before-child stylesheet cascade.

---

## 7. Sitewide Search, Comments, Conditional Performance & SEO Schema

- **Sitewide Multi-Post-Type Search (`search.php` & `searchform.php`)**:
  - Searches across Pages, Blog Articles, Services, Projects, Industries, and Case Studies (`/?s=...`) with post-type badges and empty-state quick links.
- **Native Threaded Comments (`comments.php` + `single.php`)**:
  - Bespoke ALIFY discussion list, avatar initials badges, threaded replies, and styled comment form.
- **Route-Wise Conditional Asset Loading & Font Preloading (`inc/enqueue.php`)**:
  - Enqueues only the reusable component CSS & JS required by the active route (e.g., 2 component pairs on Blog instead of all 13) + `<link rel="preload">` for self-hosted `Garet`, `Montserrat`, and `Poppins` `.woff2` fonts.
- **Automatic SEO Meta & JSON-LD Schema (`inc/seo-schema.php`)**:
  - Outputs Canonical URL, Meta Description, OpenGraph, Twitter Cards, and `@graph` JSON-LD (`ProfessionalService`, `WebSite` + `SearchAction`, `BlogPosting`, and dynamic `FAQPage`) — automatically stepping aside whenever Yoast SEO, Rank Math, AIOSEO, or SEOPress is active.
