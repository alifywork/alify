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

## 4. Dynamic Custom Post Types, Native Meta Boxes & 1-Click Starter Importer

All **6 Custom Post Types** + native WordPress **Blog Posts** are powered dynamically from the WordPress database (with zero third-party ACF plugin required):

- **Services (`alify_service`)** — Notch code, discipline tag, Font Awesome icon, Media Library background image, bullet points, single-page stats & deliverables (`#servicesSliderTrack`, `#servicesGrid`, and `/services-item/<slug>/`).
- **Projects (`alify_project`)** — Client, sector, year, sprint duration, headline metric, deliverable chips, brief/craft narrative, and **6-Screen Interactive Lightbox Gallery picker** (`#projectsAccordionStage`, `#projectsGrid`, and `/project/<slug>/`).
- **Industries (`alify_industry`)** — Sector code, filter group, icon, benchmark stat, deliverables, and background image (`#industriesSliderTrack`, `#industriesGrid`, and `/industry/<slug>/`).
- **Case Studies (`alify_case_study`)** — Before/After benchmarks, 01 Bottleneck, 02 ALIFY Architecture, 3 KPI tiles, architecture chips, and executive quote (`#caseStudiesStack` and `/case-study/<slug>/`).
- **Testimonials (`alify_testimonial`)** — Client role, avatar initials, star rating, outcome pill, and capability filter (`.testimonials-dual-stage` 2-row carousel + `#testimonialsGrid`).
- **FAQs (`alify_faq` + `faq_pillar` taxonomy)** — Knowledge Pillar grouping + global reusable FAQ toggle (`.faq-section` + `#faqHubGroupsWrap` 20-FAQ Hub).
- **1-Click Starter Importer (`Appearance → ALIFY Starter Import`)** — Imports all **67 starter items** (8 Services, 6 Projects + 36 Lightbox Screens, 9 Industries, 6 Case Studies, 12 Testimonials, 20 FAQs, and 6 Blog Articles) idempotently in one click.

---

## 5. WordPress Customizer & Leads CRM Inbox

- **Appearance → Customize → ALIFY Studio Settings**:
  - Studio Email (`info@alify.site`)
  - WhatsApp Display Number (`+90 301 9328094`) & `wa.me` Link (`https://wa.me/903019328094`)
  - Studio Headquarters (`Karachi, PK — Global Delivery`)
  - Header CTA Label (`Start a Project`) & Social Profile Links
- **WP Admin → ALIFY Leads & Calls**:
  - Every submission from the Hero Lead Form, Reusable Contact Form, Contact Page, Start a Project Brief, Book a Call Page, and Global Strategy Call Modal is saved in WordPress Admin **AND** emailed via `wp_mail()` to your configured Studio Email (`info@alify.site`).
