// ==========================================================================
// Reusable Single Detail Pages Dynamic Controller
// Powers:
// 1. service-single.html?service=<slug>      (All 9 Services)
// 2. industry-single.html?industry=<slug>    (All 9 Industries)
// 3. work-single.html?project=<slug>         (All 6 Projects)
// 4. case-study-single.html?case=<id>        (All 6 Case Studies)
// 5. blog-single.html?post=<slug>            (Related Projects matcher)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);

  // ------------------------------------------------------------------------
  // Canonical Projects Catalog (Shared across Related Projects & Work Single)
  // ------------------------------------------------------------------------
  const PROJECTS = {
    "finpulse-platform": {
      slug: "finpulse-platform",
      caseId: "cs-finpulse",
      bg: "assets/images/projects/project-finpulse.jpg",
      gallery2: "assets/images/services/service-web-dev.jpg",
      category: "FinTech & SaaS",
      year: "2026",
      sprint: "10 Weeks",
      metric: "+184% Demo Pipeline",
      client: "FinPulse AI",
      hq: "San Francisco, US",
      kicker: "FINPULSE AI · SAN FRANCISCO",
      shortTitle: "FinPulse AI Platform",
      title: "FinPulse Institutional Wealth & AI Analytics Platform",
      desc: "Re-architected the enterprise web ecosystem, design token system, and interactive portfolio dashboard to nearly triple enterprise demo bookings.",
      chips: ["Web Architecture", "Design System", "GSAP Motion"],
      brief:
        "FinPulse AI needed to evolve from an early-stage developer tool into an institutional-grade financial intelligence brand capable of winning Fortune 500 treasury teams.",
      craft:
        "ALIFY unified marketing and product surfaces under a 48-component token system, engineered sub-second static-edge pages, and built an interactive live portfolio simulator.",
      outcomes: [
        ["+184%", "Enterprise Demo Bookings"],
        ["0.7s", "Global LCP Load Time"],
        ["99/100", "Mobile Performance Score"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-finpulse.jpg",
          tag: "Flagship Platform",
          title: "Institutional Treasury Command Interface",
          caption: "Real-time multi-currency liquidity telemetry and sub-second market depth visualization.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Design Tokens",
          title: "48-Component Enterprise Design System",
          caption: "Unified color tokens, data-table density rules, and modular UI primitives across web & product.",
        },
        {
          src: "assets/images/services/service-web-dev.jpg",
          tag: "Edge Architecture",
          title: "Sub-Second Static-Edge Web Engineering",
          caption: "0.7s global LCP architecture with streaming telemetry previews and zero layout shift.",
        },
        {
          src: "assets/images/industries/industry-fintech.jpg",
          tag: "Analytics UX",
          title: "CFO Portfolio & Risk Scenario Simulator",
          caption: "Interactive yield and FX hedging calculator built directly into the enterprise evaluation funnel.",
        },
        {
          src: "assets/images/projects/case-ledgerflow-saas.jpg",
          tag: "Data Workspace",
          title: "Automated Reconciliation & Audit Views",
          caption: "Role-based institutional reporting dashboards engineered for SOC2-compliant workflows.",
        },
        {
          src: "assets/images/services/service-conversion-cro.jpg",
          tag: "Growth Funnel",
          title: "High-Intent Enterprise Demo Routing",
          caption: "Frictionless technical qualification flow that lifted institutional demo bookings by +184%.",
        },
      ],
    },
    "aura-atelier-commerce": {
      slug: "aura-atelier-commerce",
      caseId: "cs-aura",
      bg: "assets/images/projects/project-aura-atelier.jpg",
      gallery2: "assets/images/services/service-ecommerce.jpg",
      category: "Headless E-Commerce",
      year: "2026",
      sprint: "12 Weeks",
      metric: "+62% Checkout Rate",
      client: "Aura Atelier",
      hq: "Milan & London",
      kicker: "AURA ATELIER · MILAN & LONDON",
      shortTitle: "Aura Atelier Flagship",
      title: "Aura Atelier Luxury Headless Flagship Storefront",
      desc: "Crafted a sub-second headless commerce storefront and tactile editorial brand identity that lifted luxury checkout conversion by 62%.",
      chips: ["Brand Identity", "Headless DTC", "Checkout UX"],
      brief:
        "High-traffic seasonal capsule drops were stalling on mobile due to heavy unoptimized lookbooks and a rigid five-step legacy checkout sequence.",
      craft:
        "We paired bespoke art direction with an edge-rendered headless storefront, instant slide-out wardrobe bundling, and one-tap multi-currency express checkout.",
      outcomes: [
        ["+62%", "Checkout Completion Lift"],
        ["+38%", "Average Order Value"],
        ["0.6s", "Median Storefront LCP"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-aura-atelier.jpg",
          tag: "Flagship Storefront",
          title: "Editorial Lookbook & Flagship Commerce",
          caption: "Tactile luxury art direction paired with instant edge-cached product storytelling.",
        },
        {
          src: "assets/images/services/service-ecommerce.jpg",
          tag: "Headless Checkout",
          title: "Multi-Currency Sub-Second Bag & Checkout",
          caption: "Localized express checkout across 14 currencies that lifted mobile checkout completion by +62%.",
        },
        {
          src: "assets/images/blog/blog-headless-commerce.jpg",
          tag: "Merchandising",
          title: "Modular Campaign & Capsule Builder",
          caption: "Composable CMS blocks empowering editorial teams to launch seasonal drops in minutes.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Commerce Tokens",
          title: "Bespoke Typography & Storefront UI Library",
          caption: "Responsive PDP cards, tactile swatch selectors, and micro-interaction states across viewports.",
        },
        {
          src: "assets/images/industries/industry-ecommerce.jpg",
          tag: "Mobile Flagship",
          title: "Thumb-Zone Mobile Shopping Experience",
          caption: "Slide-out wardrobe bundling, predictive search, and sticky size-guide ergonomics.",
        },
        {
          src: "assets/images/services/service-interactive-motion.jpg",
          tag: "Motion Craft",
          title: "60fps Scroll-Choreographed Product Reveals",
          caption: "Hardware-accelerated GSAP lookbook transitions engineered for zero frame drops.",
        },
      ],
    },
    "medcore-telehealth": {
      slug: "medcore-telehealth",
      caseId: "cs-medcore",
      bg: "assets/images/projects/project-medcore-portal.jpg",
      gallery2: "assets/images/industries/industry-healthcare.jpg",
      category: "HealthTech Web",
      year: "2026",
      sprint: "14 Weeks",
      metric: "99.4% Patient CSAT",
      client: "MedCore Health",
      hq: "Toronto, CA",
      kicker: "MEDCORE HEALTH · TORONTO",
      shortTitle: "MedCore Patient Portal",
      title: "MedCore Telemedicine & Accessible Patient Web Portal",
      desc: "Designed and engineered WCAG AA accessible patient scheduling and virtual care web workflows that cut support calls by 64%.",
      chips: ["Web Portal", "Clinical UX", "WCAG AA"],
      brief:
        "Patients struggled with an 11-screen legacy appointment maze, causing high drop-off among older users and overwhelming clinic phone lines.",
      craft:
        "Through usability sessions with 40+ patients and clinicians, ALIFY compressed appointment booking, prescription refills, and browser video triage into three calm steps.",
      outcomes: [
        ["99.4%", "Patient Portal CSAT"],
        ["-51%", "Missed Appointments"],
        ["-64%", "Support Call Volume"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-medcore-portal.jpg",
          tag: "Patient Portal",
          title: "Unified 3-Step Patient Scheduling & Triage",
          caption: "WCAG AA accessible appointment booking flow achieving a 99.4% patient satisfaction score.",
        },
        {
          src: "assets/images/industries/industry-healthcare.jpg",
          tag: "Clinical Suite",
          title: "Clinician Live Consultation & Charting View",
          caption: "Single-screen browser telehealth video, vitals history, and prescription refill management.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Accessible Tokens",
          title: "WCAG AA Healthcare Component Library",
          caption: "High-contrast clinical typography, clear error states, and assistive-tech friendly UI primitives.",
        },
        {
          src: "assets/images/services/service-ui-ux-branding.jpg",
          tag: "UX Research",
          title: "Symptom-Guided Intake Journey Architecture",
          caption: "Calm progressive disclosure replacing an 11-screen legacy medical questionnaire maze.",
        },
        {
          src: "assets/images/services/service-web-dev.jpg",
          tag: "Portal Security",
          title: "Zero-Trust Encrypted Web Engineering",
          caption: "Sub-second portal rendering with strict session security and EHR interoperability.",
        },
        {
          src: "assets/images/blog/blog-conversion-ux.jpg",
          tag: "Care Continuity",
          title: "Automated Care Plan & Refill Dashboard",
          caption: "Self-serve follow-up reminders and prescription tracking that cut clinic phone volume by 64%.",
        },
      ],
    },
    "crestline-proptech": {
      slug: "crestline-proptech",
      caseId: "cs-crestline",
      bg: "assets/images/projects/project-crestline-proptech.jpg",
      gallery2: "assets/images/industries/industry-real-estate.jpg",
      category: "PropTech Platform",
      year: "2025",
      sprint: "12 Weeks",
      metric: "110k+ Active Buyers",
      client: "Crestline Developments",
      hq: "Dubai, UAE",
      kicker: "CRESTLINE DEVELOPMENTS · DUBAI",
      shortTitle: "Crestline 3D PropTech",
      title: "Crestline Interactive 3D Architectural Discovery Hub",
      desc: "Built an immersive 3D residence explorer, live mortgage configurator, and investor lead engine serving over 110,000 monthly active buyers.",
      chips: ["Interactive Web", "Map Search UX", "CRM Funnel"],
      brief:
        "Cross-border luxury buyers needed confidence to reserve off-plan waterfront residences digitally without relying on static PDF floorplans.",
      craft:
        "ALIFY engineered an interactive tower explorer with real-time unit availability, sun-path view previews, multi-currency payment schedules, and instant CRM routing.",
      outcomes: [
        ["110k+", "Monthly Active Buyers"],
        ["+136%", "Qualified Viewing Leads"],
        ["$48M+", "Digital Reservations"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-crestline-proptech.jpg",
          tag: "3D Explorer",
          title: "Interactive Waterfront Tower & Unit Explorer",
          caption: "Real-time floorplate selection with sun-path orientation and panoramic marina view previews.",
        },
        {
          src: "assets/images/industries/industry-real-estate.jpg",
          tag: "Live Inventory",
          title: "CRM-Synced Availability & Payment Configurator",
          caption: "Multi-currency milestone payment schedules and live unit reservation status for global investors.",
        },
        {
          src: "assets/images/services/service-interactive-motion.jpg",
          tag: "Spatial Motion",
          title: "Scroll-Choreographed Neighbourhood & Amenity Tour",
          caption: "Immersive architectural storytelling serving 110,000+ monthly active luxury property buyers.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Design System",
          title: "PropTech Digital Identity & Investor UI Kit",
          caption: "Modular floorplan comparison drawers, ROI yield widgets, and downloadable dossier templates.",
        },
        {
          src: "assets/images/services/service-conversion-cro.jpg",
          tag: "Concierge Funnel",
          title: "Private Viewing & Broker Attribution Flow",
          caption: "Instant private-viewing scheduler and advisor routing that grew qualified leads by +136%.",
        },
        {
          src: "assets/images/blog/blog-technical-seo.jpg",
          tag: "Search Engine",
          title: "International Property Schema & Landing System",
          caption: "Multilingual programmatic SEO hubs capturing cross-border real estate demand.",
        },
      ],
    },
    "voltmobility-ev": {
      slug: "voltmobility-ev",
      caseId: "cs-ledgerflow",
      bg: "assets/images/projects/project-voltmobility.jpg",
      gallery2: "assets/images/industries/industry-logistics-mobility.jpg",
      category: "Clean Mobility & AI",
      year: "2026",
      sprint: "11 Weeks",
      metric: "38s EV Unlock Flow",
      client: "VoltMobility",
      hq: "Berlin, DE",
      kicker: "VOLTMOBILITY · BERLIN",
      shortTitle: "VoltMobility EV Fleet",
      title: "VoltMobility Autonomous EV Fleet & Cloud Telemetry Hub",
      desc: "Streamlined real-time vehicle telemetry, instant fleet reservation, and predictive charger routing into a sub-38-second cloud web workflow.",
      chips: ["Cloud Platform", "Live Telemetry", "AI Routing"],
      brief:
        "Urban commuters needed instant keyless EV access and accurate range-aware charger routing across 18 European metro zones.",
      craft:
        "We crafted a high-contrast dark-mode rider interface with live battery telemetry, NFC tap-to-unlock, and one-swipe trip billing.",
      outcomes: [
        ["38s", "End-to-End EV Booking"],
        ["99.98%", "Telemetry Uptime"],
        ["+74%", "Repeat Weekly Riders"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-voltmobility.jpg",
          tag: "Fleet Telemetry",
          title: "Live EV State-of-Charge & Metro Zone Map",
          caption: "Real-time vehicle availability, battery range rings, and 38-second keyless reservation flow.",
        },
        {
          src: "assets/images/industries/industry-logistics-mobility.jpg",
          tag: "AI Routing",
          title: "Predictive Fast-Charger & Route Planner",
          caption: "Range-aware corridor routing across 18 European cities with live bay occupancy telemetry.",
        },
        {
          src: "assets/images/services/service-ai-product.jpg",
          tag: "Diagnostics UI",
          title: "Cloud Battery Health & Fleet Command Center",
          caption: "99.98% uptime operator dashboard surfacing predictive maintenance and depot balancing.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Control-Room UI",
          title: "High-Contrast Dark Telemetry Token System",
          caption: "Glare-free status tokens, map pin clusters, and one-swipe billing components.",
        },
        {
          src: "assets/images/services/service-design-systems.jpg",
          tag: "Modular Widgets",
          title: "Enterprise Fleet Sustainability & Usage Reports",
          caption: "CO2 savings analytics and automated corporate mobility invoicing dashboards.",
        },
        {
          src: "assets/images/blog/blog-gsap-motion.jpg",
          tag: "Interactive Web",
          title: "Connected Hardware & Rider Web Showcase",
          caption: "Scroll-driven product launch experience lifting repeat weekly rider adoption by +74%.",
        },
      ],
    },
    "solenne-skincare": {
      slug: "solenne-skincare",
      caseId: "cs-solenne",
      bg: "assets/images/projects/project-solenne-beauty.jpg",
      gallery2: "assets/images/services/service-digital-marketing.jpg",
      category: "Brand & Growth SEO",
      year: "2025",
      sprint: "10 Weeks",
      metric: "2.8x Organic Revenue",
      client: "Solenne Paris",
      hq: "Paris, FR",
      kicker: "SOLENNE PARIS · FRANCE",
      shortTitle: "Solenne Botanical DTC",
      title: "Solenne Botanical Rebrand, Bundle Builder & SEO Engine",
      desc: "Paired a Parisian packaging and digital rebrand with custom regimen builders and technical SEO architecture to grow organic revenue 2.8x.",
      chips: ["Visual Rebrand", "Bundle Builder", "Technical SEO"],
      brief:
        "Rising paid social CAC demanded an organic search moat and higher first-order basket sizes across Solenne’s botanical skincare line.",
      craft:
        "ALIFY overhauled technical schema and Core Web Vitals while launching an interactive 4-question skin diagnostic quiz that builds personalized routines.",
      outcomes: [
        ["2.8x", "Non-Paid Organic Revenue"],
        ["+47%", "Average Order Value"],
        ["98/100", "Core Web Vitals Score"],
      ],
      gallery: [
        {
          src: "assets/images/projects/project-solenne-beauty.jpg",
          tag: "Brand Flagship",
          title: "Parisian Botanical Rebrand & DTC Storefront",
          caption: "Editorial skincare storytelling balanced with dermatologist-backed clinical proof.",
        },
        {
          src: "assets/images/services/service-ecommerce.jpg",
          tag: "Bundle Builder",
          title: "4-Step Skin Diagnostic & Custom Regimen Quiz",
          caption: "Interactive routine builder recommending tailored AM/PM bundles and lifting AOV by +47%.",
        },
        {
          src: "assets/images/services/service-ui-ux-branding.jpg",
          tag: "Visual System",
          title: "Packaging Typography & Digital Art Direction",
          caption: "Unified brand system spanning tactile botanical packaging, iconography, and web UI.",
        },
        {
          src: "assets/images/projects/work-gallery-design-system.jpg",
          tag: "Storefront Kit",
          title: "Subscription & Replenishment UI Components",
          caption: "Frictionless auto-ship cadence toggles, free-gift progress bars, and ingredient drawers.",
        },
        {
          src: "assets/images/blog/blog-brand-identity.jpg",
          tag: "Ingredient UX",
          title: "Botanical Active & Clinical Study Cards",
          caption: "Transparent sourcing provenance and clinical efficacy visuals directly on product pages.",
        },
        {
          src: "assets/images/services/service-digital-marketing.jpg",
          tag: "Organic SEO",
          title: "Core Web Vitals & Editorial Search Architecture",
          caption: "98/100 CWV performance and structured schema growing non-paid organic revenue 2.8x.",
        },
      ],
    },
  };

  const projectPermalink = (slug) => (window.AlifyProjectUrls || {})[slug] || `work-single.html?project=${encodeURIComponent(slug)}`;
  const caseStudyPermalink = (slug) => (window.AlifyCaseStudyUrls || {})[slug] || `case-study-single.html?case=${encodeURIComponent(slug)}`;

  const renderRelatedProjectCard = (slug) => {
    const p = PROJECTS[slug] || PROJECTS["finpulse-platform"];
    const chips = p.chips
      .map((c) => `<span class="project-chip">${c}</span>`)
      .join("");
    return `
      <div class="col-12 col-md-6">
        <article class="project-grid-card">
          <div class="project-grid-card-bg" style="background-image: url('${p.bg}');" aria-hidden="true"></div>
          <div class="project-panel-top">
            <div class="project-panel-badges">
              <span class="project-badge-glass">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                ${p.category}
              </span>
              <span class="project-badge-glass">${p.year}</span>
            </div>
            <span class="project-metric-pill">
              <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
              ${p.metric}
            </span>
          </div>
          <div class="project-panel-bottom">
            <span class="project-client-label">${p.kicker}</span>
            <h3 class="project-panel-title">
              <a href="${projectPermalink(p.slug)}">${p.title}</a>
            </h3>
            <p class="project-panel-desc">${p.desc}</p>
            <div class="project-panel-footer">
              <div class="project-deliverable-chips">${chips}</div>
              <a href="${projectPermalink(p.slug)}" class="project-cta-btn">
                <span class="project-cta-pill">View Project</span>
                <span class="project-cta-circle" aria-hidden="true">
                  <i class="fa-solid fa-arrow-up project-cta-arrow"></i>
                </span>
              </a>
            </div>
          </div>
        </article>
      </div>`;
  };

  // ------------------------------------------------------------------------
  // A. Service Single Page (service-single.html?service=<slug>)
  // ------------------------------------------------------------------------
  const serviceRoot = document.getElementById("serviceSingleRoot");
  if (serviceRoot) {
    const SERVICES = {
      "web-development": {
        code: "01 · CORE CAPABILITY",
        title: "Web Design & Development",
        desc: "Bespoke responsive websites, enterprise web platforms, and performance-obsessed frontend architectures engineered to turn visitors into qualified pipeline.",
        bg: "assets/images/services/service-web-dev.jpg",
        tags: ["Semantic Architecture", "99+ Core Web Vitals", "CMS & API Ready"],
        stats: [
          ["0.7s", "Median Global LCP Speed"],
          ["99/100", "Lighthouse Performance"],
          ["+148%", "Avg. Conversion Lift"],
        ],
        deliverables: [
          ["fa-compass", "Information Architecture", "Conversion-led sitemaps, buyer journey mapping, and structured messaging hierarchies."],
          ["fa-pen-ruler", "Bespoke UI Direction", "Custom responsive layouts crafted from scratch around your brand authority — zero templates."],
          ["fa-gears", "Clean Frontend Engineering", "Modular semantic HTML5, custom CSS architecture, and hardware-accelerated interactions."],
          ["fa-layer-group", "Headless & CMS Integration", "Author-friendly content modeling connected cleanly to modern headless or enterprise stacks."],
          ["fa-shield-halved", "Security & Accessibility", "WCAG AA contrast compliance, hardened form endpoints, and enterprise security headers."],
          ["fa-rocket", "Edge Speed & Technical SEO", "Structured JSON-LD schema, sub-second asset delivery, and full analytics attribution."],
        ],
        stack: ["HTML5 & Custom CSS", "Modern JavaScript", "GSAP Motion", "Headless CMS", "Edge CDN", "Technical Schema"],
        related: ["finpulse-platform", "crestline-proptech"],
      },
      "ui-ux-branding": {
        code: "02 · CORE CAPABILITY",
        title: "UI/UX & Brand Identity",
        desc: "Research-backed product interfaces, iconic visual identities, and cohesive design languages that command instant category authority.",
        bg: "assets/images/services/service-ui-ux-branding.jpg",
        tags: ["Visual Identity", "User Research", "Product UI Systems"],
        stats: [
          ["3.1x", "Brand Recall Lift"],
          ["-44%", "Onboarding Drop-Off"],
          ["100%", "Figma Token Handoff"],
        ],
        deliverables: [
          ["fa-compass", "Brand Strategy & Positioning", "Competitive whitespace audits, verbal tone frameworks, and executive brand narratives."],
          ["fa-pen-ruler", "Logo & Visual Identity", "Distinctive mark systems, editorial typography scales, and comprehensive brand books."],
          ["fa-layer-group", "UX Research & Wireframes", "Jobs-to-be-done interviews, task-flow mapping, and interactive clickable prototypes."],
          ["fa-gears", "SaaS & Web Product UI", "High-clarity dashboards, complex data tables, and frictionless multi-step workflows."],
          ["fa-shield-halved", "Usability Validation", "Moderated user testing sessions to eliminate friction before engineering begins."],
          ["fa-rocket", "Design-to-Dev Handoff", "Annotated component specs, interaction states, and developer-ready Figma variables."],
        ],
        stack: ["Figma Variables", "Brand Guidelines", "Interactive Prototypes", "Design Tokens", "UX Testing", "Iconography"],
        related: ["solenne-skincare", "aura-atelier-commerce"],
      },
      "digital-marketing": {
        code: "03 · CORE CAPABILITY",
        title: "Digital Marketing & SEO",
        desc: "Technical SEO architecture, high-intent landing ecosystems, and full-funnel growth programs that compound non-paid revenue.",
        bg: "assets/images/services/service-digital-marketing.jpg",
        tags: ["Technical SEO", "Demand Funnels", "Revenue Attribution"],
        stats: [
          ["2.8x", "Organic Revenue Growth"],
          ["+165%", "Non-Branded Search Traffic"],
          ["-36%", "Blended Acquisition CAC"],
        ],
        deliverables: [
          ["fa-compass", "Technical SEO Architecture", "Crawl budget optimization, Core Web Vitals remediation, and clean URL hierarchies."],
          ["fa-layer-group", "Programmatic & Hub Content", "High-intent commercial landing clusters engineered around buyer search stages."],
          ["fa-pen-ruler", "Conversion Landing Systems", "Dedicated paid and organic campaign pages built for rapid message testing."],
          ["fa-gears", "Schema & Entity Authority", "Rich JSON-LD structured data for services, FAQs, reviews, and product entities."],
          ["fa-chart-line", "Full-Funnel Analytics", "Server-side event tracking, CRM pipeline attribution, and executive KPI dashboards."],
          ["fa-rocket", "Compounding Growth Sprints", "Monthly search expansion, link authority campaigns, and conversion iteration."],
        ],
        stack: ["Technical SEO", "Core Web Vitals", "JSON-LD Schema", "GA4 & CRM Attribution", "Landing Funnels", "CRO Sprints"],
        related: ["solenne-skincare", "finpulse-platform"],
      },
      ecommerce: {
        code: "04 · CORE CAPABILITY",
        title: "E-Commerce Solutions",
        desc: "Sub-second headless storefronts, custom bundle builders, and high-velocity checkout architectures built to maximize average order value.",
        bg: "assets/images/services/service-ecommerce.jpg",
        tags: ["Shopify Plus & Headless", "Bundle Builders", "Checkout Velocity"],
        stats: [
          ["+62%", "Checkout Rate Lift"],
          ["+47%", "Average Order Value"],
          ["0.6s", "Storefront Load Time"],
        ],
        deliverables: [
          ["fa-compass", "Storefront UX & Merchandising", "Editorial product discovery, smart filtering, and high-converting PDP layouts."],
          ["fa-pen-ruler", "Custom Regimen & Bundle UX", "Interactive bundle configurators and slide-out cart drawers with tiered rewards."],
          ["fa-gears", "Headless & Shopify Plus Builds", "Edge-cached storefronts connected to ERP, inventory, and subscription engines."],
          ["fa-bolt", "One-Tap Express Checkout", "Streamlined multi-currency checkout removing guest friction on mobile devices."],
          ["fa-shield-halved", "Subscription & Loyalty Portals", "Self-serve member portals that lift repeat purchase frequency and LTV."],
          ["fa-rocket", "Peak Drop Load Readiness", "Stress-tested edge caching built for flash sales and global campaign spikes."],
        ],
        stack: ["Shopify Plus", "Headless Storefronts", "Bundle Configurators", "Subscription UX", "Multi-Currency", "Edge Caching"],
        related: ["aura-atelier-commerce", "solenne-skincare"],
      },
      "design-systems": {
        code: "05 · CORE CAPABILITY",
        title: "Scalable Design Systems",
        desc: "Unified Figma variable libraries and accessible coded component kits that empower multi-squad product teams to ship 3x faster.",
        bg: "assets/images/services/service-design-systems.jpg",
        tags: ["Design Tokens", "WCAG AA Components", "Dev Governance"],
        stats: [
          ["3.0x", "Faster UI Assembly"],
          ["-60%", "Visual QA Regressions"],
          ["48+", "Tokenized Components"],
        ],
        deliverables: [
          ["fa-layer-group", "Semantic Token Architecture", "Primitive, semantic, and theme-level tokens supporting multi-brand theming."],
          ["fa-pen-ruler", "Figma Component Library", "Auto-layout variants, slot patterns, and strict property naming conventions."],
          ["fa-gears", "Coded UI Component Kit", "Zero-bloat production components mirrored 1:1 with Figma token definitions."],
          ["fa-shield-halved", "WCAG AA Accessibility Baked In", "Keyboard focus traps, ARIA semantics, and verified color contrast across states."],
          ["fa-compass", "Documentation & Usage Portal", "Clear do/don’t guidelines, spacing rules, and adoption playbooks for squads."],
          ["fa-rocket", "Team Onboarding & Governance", "Hands-on workshops and contribution workflows for internal designers and devs."],
        ],
        stack: ["Figma Variables", "CSS Custom Properties", "Token Pipelines", "WCAG AA", "Component Docs", "DesignOps"],
        related: ["finpulse-platform", "medcore-telehealth"],
      },
      "interactive-motion": {
        code: "06 · CORE CAPABILITY",
        title: "Interactive Motion & Storytelling",
        desc: "Silky 60fps GSAP scroll choreography, tactile micro-interactions, and flagship launch experiences without sacrificing Core Web Vitals.",
        bg: "assets/images/services/service-interactive-motion.jpg",
        tags: ["GSAP ScrollTrigger", "60fps Choreography", "Reduced-Motion Safe"],
        stats: [
          ["60fps", "GPU-Composited Motion"],
          ["3.4x", "Avg. Dwell Time Lift"],
          ["98+", "Performance Preserved"],
        ],
        deliverables: [
          ["fa-compass", "Narrative Storyboarding", "Frame-by-frame scroll pacing designed to explain complex products effortlessly."],
          ["fa-bolt", "GSAP ScrollTrigger Sequences", "Pinned product reveals, scrubbed timelines, and progressive feature storytelling."],
          ["fa-pen-ruler", "Tactile UI Micro-Interactions", "Magnetic buttons, state morphs, and responsive cursor feedback."],
          ["fa-gears", "GPU Transform Optimization", "Strict transform/opacity compositing for zero layout thrashing on mobile."],
          ["fa-shield-halved", "Accessible Motion Fallbacks", "Full prefers-reduced-motion support so every visitor enjoys instant clarity."],
          ["fa-rocket", "Launch Microsites & Reveals", "High-impact keynote-style product launch pages engineered to earn press."],
        ],
        stack: ["GSAP 3", "ScrollTrigger", "SVG Morphing", "GPU Compositing", "Interactive Demos", "Motion Guidelines"],
        related: ["crestline-proptech", "finpulse-platform"],
      },
      "conversion-cro": {
        code: "07 · CORE CAPABILITY",
        title: "Conversion Rate Optimization",
        desc: "Evidence-backed UX diagnostics, funnel teardowns, and structured A/B experimentation that turn existing traffic into measurable pipeline.",
        bg: "assets/images/services/service-conversion-cro.jpg",
        tags: ["Funnel Diagnostics", "A/B Experimentation", "Form & Checkout CRO"],
        stats: [
          ["3.4x", "Median Funnel ROI"],
          ["+58%", "Lead Form Completion"],
          ["14 Days", "Time to First Test"],
        ],
        deliverables: [
          ["fa-compass", "Quantitative Funnel Audit", "Drop-off cohort analysis across landing pages, pricing tables, and lead forms."],
          ["fa-chart-line", "Session & Heatmap Synthesis", "Scroll-depth, rage-click, and form-hesitation diagnostics on real buyer traffic."],
          ["fa-pen-ruler", "High-Contrast UX Variants", "Clear value-proposition rewrites, social-proof placement, and frictionless CTAs."],
          ["fa-gears", "Clean Experiment Engineering", "Zero-flicker variant builds that keep page speed fast during live split tests."],
          ["fa-shield-halved", "Statistical Significance QA", "Rigorous sample-size calculation and revenue-per-visitor measurement."],
          ["fa-rocket", "Winning Rollout Playbooks", "Permanent deployment of winning patterns across your entire web ecosystem."],
        ],
        stack: ["Funnel Analytics", "Heatmaps", "Zero-Flicker A/B", "Pricing UX", "Lead Form CRO", "Checkout Testing"],
        related: ["aura-atelier-commerce", "finpulse-platform"],
      },
      "ai-product-engineering": {
        code: "08 · CORE CAPABILITY",
        title: "AI Product & Workflow UX",
        desc: "Human-centered AI copilot interfaces, transparent citation UX, and intelligent workflow portals built for real-world enterprise adoption.",
        bg: "assets/images/services/service-ai-product.jpg",
        tags: ["Agentic & Copilot UI", "Streaming UX", "Enterprise Trust"],
        stats: [
          ["+82%", "AI Feature Adoption"],
          ["-55%", "Manual Task Time"],
          ["$40M+", "AI Client Funding"],
        ],
        deliverables: [
          ["fa-compass", "AI Interaction Paradigms", "Moving beyond generic chatboxes into structured canvas, command, and copilot UX."],
          ["fa-pen-ruler", "Streaming & Citation States", "Confidence indicators, source provenance chips, and instant human-in-the-loop edits."],
          ["fa-gears", "Frontend Copilot Engineering", "Low-latency token streaming interfaces, keyboard command palettes, and state undo."],
          ["fa-layer-group", "Knowledge & Search Portals", "Semantic enterprise search interfaces that surface exact answers in milliseconds."],
          ["fa-shield-halved", "Guardrails & Permission UX", "Clear workspace privacy controls, audit logs, and enterprise role visibility."],
          ["fa-rocket", "AI Product Launch Websites", "Interactive browser sandboxes that let buyers experience your AI product live."],
        ],
        stack: ["Copilot Interfaces", "Streaming UI", "Command Palettes", "Citation UX", "Workflow Portals", "Interactive Sandboxes"],
        related: ["finpulse-platform", "voltmobility-ev"],
      },
    };

    const slug = params.get("service") || ((window.AlifyCurrentItem || {}).type === "service" ? window.AlifyCurrentItem.slug : "") || "web-development";
    const srv = SERVICES[slug] || SERVICES["web-development"];

    document.title = `${srv.title} — Services | ALIFY`;
    const setTxt = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setTxt("singleBreadcrumbCurrent", srv.title);
    setTxt("srvHeroEyebrow", srv.code);
    setTxt("srvHeroTitle", srv.title);
    setTxt("srvHeroDesc", srv.desc);

    const tagsEl = document.getElementById("srvHeroTags");
    if (tagsEl) {
      tagsEl.innerHTML = srv.tags
        .map(
          (t) =>
            `<span class="inner-hero-tag"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>${t}</span></span>`
        )
        .join("");
    }

    const bgEl = document.getElementById("srvBannerBg");
    if (bgEl) bgEl.style.backgroundImage = `url('${srv.bg}')`;
    const heroBg = document.getElementById("innerHeroBg");
    if (heroBg) heroBg.style.backgroundImage = `url('${srv.bg}')`;
    setTxt("srvBannerPill", srv.title);

    const statsEl = document.getElementById("srvBannerStats");
    if (statsEl) {
      statsEl.innerHTML = srv.stats
        .map(
          ([v, l]) => `
          <div class="col-12 col-md-4">
            <div class="single-banner-stat">
              <span class="single-banner-stat-val">${v}</span>
              <span class="single-banner-stat-lbl">${l}</span>
            </div>
          </div>`
        )
        .join("");
    }

    const bentoEl = document.getElementById("srvDeliverablesGrid");
    if (bentoEl) {
      bentoEl.innerHTML = srv.deliverables
        .map(
          ([icon, title, text], idx) => `
          <div class="col-12 col-md-6 col-lg-4">
            <article class="single-bento-card">
              <div>
                <div class="single-bento-top">
                  <span class="single-bento-icon" aria-hidden="true">
                    <i class="fa-solid ${icon}"></i>
                  </span>
                  <span class="single-bento-num">0${idx + 1}</span>
                </div>
                <h3 class="single-bento-title">${title}</h3>
                <p class="single-bento-text">${text}</p>
              </div>
            </article>
          </div>`
        )
        .join("");
    }

    const stackEl = document.getElementById("srvStackPills");
    if (stackEl) {
      stackEl.innerHTML = srv.stack
        .map((s) => `<span class="single-stack-pill">${s}</span>`)
        .join("");
    }

    const relEl = document.getElementById("srvRelatedProjects");
    if (relEl) {
      relEl.innerHTML = srv.related.map(renderRelatedProjectCard).join("");
    }
  }

  // ------------------------------------------------------------------------
  // C. Industry Single Page (industry-single.html?industry=<slug>)
  // ------------------------------------------------------------------------
  const industryRoot = document.getElementById("industrySingleRoot");
  if (industryRoot) {
    const INDUSTRIES = {
      fintech: {
        code: "01 · SECTOR SPECIALIZATION",
        title: "FinTech & Digital Banking",
        desc: "High-trust payment interfaces, wealth management dashboards, and frictionless KYC onboarding architectures built for regulatory confidence and conversion.",
        bg: "assets/images/industries/industry-fintech.jpg",
        tags: ["KYC Onboarding UX", "Wealth & Ledger UI", "Institutional Trust"],
        stats: [
          ["+148%", "Qualified Demo Pipeline"],
          ["-46%", "KYC Onboarding Drop-Off"],
          ["99.99%", "Platform Reliability"],
        ],
        challenges: [
          ["KYC Friction", "High Abandonment During Identity Verification", "Progressive 3-step verification flows with instant document feedback that cut drop-off nearly in half."],
          ["Data Overload", "Dense Financial Dashboards Confusing Users", "Tokenized hierarchy, clear portfolio summaries, and progressive drill-downs for CFOs and retail users alike."],
          ["Trust Deficit", "Generic Startup Visuals Losing Enterprise Deals", "Editorial institutional brand systems and security transparency hubs that establish instant authority."],
        ],
        solutions: [
          "Digital Banking & Neobank Web Platforms",
          "Real-Time Treasury & Wealth Dashboards",
          "PCI-Conscious Payment & Checkout Flows",
          "Interactive Financial ROI Calculators",
          "Investor-Grade Pitch & Launch Microsites",
          "Multi-Product FinTech Design Systems",
        ],
        related: ["finpulse-platform", "aura-atelier-commerce"],
      },
      healthcare: {
        code: "02 · SECTOR SPECIALIZATION",
        title: "Healthcare & MedTech",
        desc: "WCAG-accessible patient portals, telemedicine web platforms, and calm clinical interfaces crafted around empathy, speed, and privacy.",
        bg: "assets/images/industries/industry-healthcare.jpg",
        tags: ["WCAG AA Accessible", "Telehealth Web UX", "Clinical Clarity"],
        stats: [
          ["99.4%", "Patient Portal CSAT"],
          ["-51%", "Appointment No-Shows"],
          ["3 Taps", "Median Triage Booking"],
        ],
        challenges: [
          ["Booking Complexity", "Legacy Portals Frustrating Patients", "3-tap appointment and video-consult scheduling designed and tested with diverse age groups."],
          ["Low Accessibility", "Poor Contrast & Tiny Tap Targets", "Strict WCAG AA typography, screen-reader semantics, and biometric one-tap sign-in."],
          ["Clinical Fatigue", "Cluttered Provider Dashboards", "Streamlined intake summaries and clear triage status cues that save clinicians minutes per visit."],
        ],
        solutions: [
          "HIPAA-Conscious Telemedicine Web Portals",
          "Accessible Clinic & Hospital Web Portals",
          "Medical Device Product Storytelling",
          "Digital Intake & Symptom Triage Flows",
          "Clinician Scheduling & Care Dashboards",
          "Healthcare SEO & Patient Acquisition Hubs",
        ],
        related: ["medcore-telehealth", "finpulse-platform"],
      },
      saas: {
        code: "03 · SECTOR SPECIALIZATION",
        title: "SaaS & Enterprise Cloud",
        desc: "Category-defining B2B marketing websites, interactive product tours, and multi-brand design systems that accelerate enterprise pipeline.",
        bg: "assets/images/industries/industry-saas.jpg",
        tags: ["B2B Pipeline Engines", "Interactive Demos", "DesignOps"],
        stats: [
          ["3.1x", "Enterprise Demo Velocity"],
          ["-44%", "Trial Onboarding Drop"],
          ["$6.5M+", "Seed & Series A Launches"],
        ],
        challenges: [
          ["Unclear Value Prop", "Complex Technical Features Lost in Jargon", "Visual product storytelling and interactive sandboxes that make value obvious in 10 seconds."],
          ["Slow Shipping", "Disconnected Marketing & Product UI", "Shared Figma-to-code token libraries so every new launch page ships in hours."],
          ["Leaky Self-Serve", "Trial Users Stalling Before Activation", "Guided activation checklists and empty-state templates that drive first-session value."],
        ],
        solutions: [
          "High-Converting B2B SaaS Websites",
          "Interactive Browser Product Tours",
          "Enterprise Pricing & ROI Configurators",
          "Self-Serve Trial Onboarding Flows",
          "Multi-Squad Token Design Systems",
          "Developer Documentation & Trust Hubs",
        ],
        related: ["finpulse-platform", "voltmobility-ev"],
      },
      "ecommerce-retail": {
        code: "04 · SECTOR SPECIALIZATION",
        title: "E-Commerce & Luxury DTC",
        desc: "Sub-second headless storefronts, custom bundle builders, and editorial merchandising systems built to lift conversion and AOV.",
        bg: "assets/images/industries/industry-ecommerce.jpg",
        tags: ["Headless Storefronts", "Bundle & Quiz UX", "Express Checkout"],
        stats: [
          ["+62%", "Checkout Completion"],
          ["+47%", "Average Order Value"],
          ["2.8x", "Organic Search Revenue"],
        ],
        challenges: [
          ["Mobile Drop-Off", "Bloated Scripts Slowing Product Pages", "Sub-second headless rendering that keeps mobile shoppers engaged from ad click to receipt."],
          ["Single-Item Carts", "Rising Ad Costs Compressing Margins", "Interactive regimen quizzes and tiered slide-out cart drawers that lift basket size."],
          ["Template Sameness", "Generic Themes Diluting Brand Equity", "Bespoke luxury art direction paired with ruthless conversion ergonomics."],
        ],
        solutions: [
          "Shopify Plus & Headless Flagship Stores",
          "Interactive Product Quiz & Bundle Builders",
          "Slide-Out Cart & Express Checkout UX",
          "Subscription & VIP Member Portals",
          "Editorial Lookbook & Drop Microsites",
          "Technical E-Commerce SEO Architecture",
        ],
        related: ["aura-atelier-commerce", "solenne-skincare"],
      },
      "real-estate": {
        code: "05 · SECTOR SPECIALIZATION",
        title: "Real Estate & PropTech",
        desc: "Immersive 3D tower showcases, interactive map search platforms, and investor reservation engines for modern developments.",
        bg: "assets/images/industries/industry-real-estate.jpg",
        tags: ["3D Property Discovery", "Map Search UX", "Investor Portals"],
        stats: [
          ["110k+", "Monthly Active Buyers"],
          ["+136%", "Qualified Viewing Leads"],
          ["$48M+", "Digital Reservations"],
        ],
        challenges: [
          ["Static PDFs", "Off-Plan Buyers Unable to Visualize Units", "Interactive floorplan and view-corridor explorers with live unit availability."],
          ["Unqualified Leads", "Generic Forms Flooding Sales Teams", "Multi-currency budget and payment-plan configurators that pre-qualify serious buyers."],
          ["Slow Map Portals", "Lagging Mobile Property Search", "Instant viewport-clustered map filtering engineered for mobile speed."],
        ],
        solutions: [
          "Interactive 3D Masterplan & Tower Hubs",
          "High-Speed Map & Filter Search Portals",
          "Multi-Currency Mortgage & Plan Calculators",
          "Luxury Branded Residence Microsites",
          "CRM & WhatsApp Automated Lead Routing",
          "Investor Data Rooms & Booking Flows",
        ],
        related: ["crestline-proptech", "aura-atelier-commerce"],
      },
      edtech: {
        code: "06 · SECTOR SPECIALIZATION",
        title: "EdTech & Learning Platforms",
        desc: "Engaging cohort learning portals, interactive study platforms, and high-converting enrollment funnels built to boost learner completion.",
        bg: "assets/images/industries/industry-edtech.jpg",
        tags: ["Interactive LMS UX", "Cohort Portals", "Enrollment Funnels"],
        stats: [
          ["+54%", "Course Completion Rate"],
          ["+72%", "Enrollment Conversion"],
          ["4.8★", "Learner Satisfaction"],
        ],
        challenges: [
          ["Mid-Course Churn", "Passive Video Portals Losing Attention", "Milestone progress streaks, interactive checkpoints, and clean distraction-free lessons."],
          ["Enrollment Hesitation", "Unclear Outcomes on Program Pages", "Curriculum previews, alumni outcome proof, and streamlined tuition checkout."],
          ["Fragmented Learner Portals", "Clunky Legacy LMS Interfaces", "Responsive browser-based study portals engineered for seamless learning anywhere."],
        ],
        solutions: [
          "Cohort & Self-Paced LMS Interfaces",
          "Interactive Cloud Learning Platforms",
          "High-Converting Course Enrollment Funnels",
          "Corporate L&D Training Dashboards",
          "Interactive Assessment & Progress UI",
          "Academic & EdTech Brand Systems",
        ],
        related: ["medcore-telehealth", "finpulse-platform"],
      },
      "ai-deep-tech": {
        code: "07 · SECTOR SPECIALIZATION",
        title: "AI & Autonomous Systems",
        desc: "Human-centered AI copilot interfaces, developer documentation portals, and flagship launch narratives for frontier technology companies.",
        bg: "assets/images/industries/industry-ai-deeptech.jpg",
        tags: ["Copilot & Agent UI", "Developer Hubs", "Launch Storytelling"],
        stats: [
          ["$40M+", "Venture Funding Raised"],
          ["5.3x", "Enterprise Pilot Signups"],
          ["60fps", "Interactive Demos"],
        ],
        challenges: [
          ["Black-Box Skepticism", "Buyers Unsure How AI Models Deliver Value", "Transparent citation UI, live interactive browser sandboxes, and clear benchmark proof."],
          ["Developer Friction", "Dense API Docs Slowing Integration", "Fast searchable documentation hubs with copy-ready SDK snippets."],
          ["Category Noise", "Every AI Startup Looking Identical", "Iconic architectural visual systems that signal serious engineering pedigree."],
        ],
        solutions: [
          "AI Copilot & Agentic Workspace UI",
          "Interactive Product Sandbox Websites",
          "Developer Docs & SDK Portals",
          "Seed / Series A Launch Brand Identities",
          "Enterprise AI Governance Dashboards",
          "Real-Time Telemetry & Model Analytics UI",
        ],
        related: ["finpulse-platform", "voltmobility-ev"],
      },
      "logistics-mobility": {
        code: "08 · SECTOR SPECIALIZATION",
        title: "Logistics & Clean Mobility",
        desc: "Real-time fleet telemetry dashboards, EV booking portals, and global supply-chain platforms engineered for operational speed.",
        bg: "assets/images/industries/industry-logistics-mobility.jpg",
        tags: ["EV & Fleet Web Portals", "Live Telemetry", "Freight Portals"],
        stats: [
          ["38s", "Avg. EV Booking Flow"],
          ["99.98%", "Live Dispatch Uptime"],
          ["+74%", "Repeat Fleet Usage"],
        ],
        challenges: [
          ["Slow Dispatch Workflows", "Clunky Legacy Fleet Dashboards", "High-contrast real-time web consoles engineered for instant dispatch clarity."],
          ["Blind Shipment Status", "Static Tracking Causing Support Tickets", "Live map telemetry, predictive ETA alerts, and self-serve exception handling."],
          ["Complex B2B Quoting", "Manual Freight Quote Delays", "Instant route and load quoting calculators integrated directly into dispatch."],
        ],
        solutions: [
          "EV Reservation & Smart Charging Portals",
          "Real-Time Fleet & Telemetry Dashboards",
          "B2B Freight Quoting & Shipper Portals",
          "Driver Dispatch & Last-Mile Web Consoles",
          "Clean-Tech Brand & Investor Websites",
          "IoT Hardware Companion Interfaces",
        ],
        related: ["voltmobility-ev", "crestline-proptech"],
      },
      "hospitality-travel": {
        code: "09 · SECTOR SPECIALIZATION",
        title: "Hospitality & Travel Tech",
        desc: "Direct-booking resort ecosystems, curated itinerary builders, and guest loyalty portals that eliminate third-party OTA commission leakage.",
        bg: "assets/images/industries/industry-hospitality-travel.jpg",
        tags: ["Direct Booking UX", "Itinerary Builders", "Guest Loyalty Portals"],
        stats: [
          ["+76%", "Direct Commission-Free Bookings"],
          ["+41%", "Ancillary Guest Spend"],
          ["0.8s", "Global Media Load Speed"],
        ],
        challenges: [
          ["OTA Leakage", "Guests Browsing Direct Site Then Booking on OTAs", "2-step rate-parity booking bars and member perks that lock in direct reservations."],
          ["Heavy Resort Media", "High-Res Photography Slowing Mobile", "Next-gen responsive media pipelines delivering cinema visuals at sub-second speed."],
          ["Disconnected Stay UX", "Separate Dining, Spa & Room Systems", "Unified digital concierge and itinerary builders that lift on-property spend."],
        ],
        solutions: [
          "Luxury Hotel & Resort Direct-Booking Web",
          "Interactive Suite & Itinerary Configurators",
          "Digital Guest Concierge Web Portals",
          "Multi-Property Hospitality Design Systems",
          "Destination Storytelling & Travel SEO",
          "VIP Membership & Loyalty Portals",
        ],
        related: ["crestline-proptech", "aura-atelier-commerce"],
      },
    };

    const slug = params.get("industry") || ((window.AlifyCurrentItem || {}).type === "industry" ? window.AlifyCurrentItem.slug : "") || "fintech";
    const ind = INDUSTRIES[slug] || INDUSTRIES.fintech;

    document.title = `${ind.title} — Industries | ALIFY`;
    const setTxt = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setTxt("singleBreadcrumbCurrent", ind.title);
    setTxt("indHeroEyebrow", ind.code);
    setTxt("indHeroTitle", ind.title);
    setTxt("indHeroDesc", ind.desc);

    const tagsEl = document.getElementById("indHeroTags");
    if (tagsEl) {
      tagsEl.innerHTML = ind.tags
        .map(
          (t) =>
            `<span class="inner-hero-tag"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>${t}</span></span>`
        )
        .join("");
    }

    const bgEl = document.getElementById("indBannerBg");
    if (bgEl) bgEl.style.backgroundImage = `url('${ind.bg}')`;
    const heroBg = document.getElementById("innerHeroBg");
    if (heroBg) heroBg.style.backgroundImage = `url('${ind.bg}')`;
    setTxt("indBannerPill", ind.title);

    const statsEl = document.getElementById("indBannerStats");
    if (statsEl) {
      statsEl.innerHTML = ind.stats
        .map(
          ([v, l]) => `
          <div class="col-12 col-md-4">
            <div class="single-banner-stat">
              <span class="single-banner-stat-val">${v}</span>
              <span class="single-banner-stat-lbl">${l}</span>
            </div>
          </div>`
        )
        .join("");
    }

    const chalEl = document.getElementById("indChallengesGrid");
    if (chalEl) {
      chalEl.innerHTML = ind.challenges
        .map(
          ([badge, title, fix], idx) => `
          <div class="col-12 col-lg-4">
            <article class="industry-challenge-card">
              <span class="industry-challenge-badge">0${idx + 1} · ${badge}</span>
              <h3 class="industry-challenge-title">${title}</h3>
              <p class="industry-challenge-problem">Common sector friction holding back digital conversion and user trust.</p>
              <p class="industry-challenge-fix">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>${fix}</span>
              </p>
            </article>
          </div>`
        )
        .join("");
    }

    const solEl = document.getElementById("indSolutionsGrid");
    if (solEl) {
      solEl.innerHTML = ind.solutions
        .map(
          (s, idx) => `
          <div class="col-12 col-md-6 col-lg-4">
            <article class="single-bento-card">
              <div>
                <div class="single-bento-top">
                  <span class="single-bento-icon" aria-hidden="true">
                    <i class="fa-solid fa-layer-group"></i>
                  </span>
                  <span class="single-bento-num">0${idx + 1}</span>
                </div>
                <h3 class="single-bento-title">${s}</h3>
                <p class="single-bento-text">Custom-engineered around ${ind.title} buyer psychology, speed benchmarks, and scalability.</p>
              </div>
            </article>
          </div>`
        )
        .join("");
    }

    const relEl = document.getElementById("indRelatedProjects");
    if (relEl) {
      relEl.innerHTML = ind.related.map(renderRelatedProjectCard).join("");
    }
  }

  // ------------------------------------------------------------------------
  // D. Work / Project Single Page (work-single.html?project=<slug>)
  // ------------------------------------------------------------------------
  const workRoot = document.getElementById("workSingleRoot");
  if (workRoot) {
    const slug = params.get("project") || ((window.AlifyCurrentItem || {}).type === "project" ? window.AlifyCurrentItem.slug : "") || "finpulse-platform";
    const proj = PROJECTS[slug] || PROJECTS["finpulse-platform"];

    document.title = `${proj.shortTitle} — Work | ALIFY`;
    const setTxt = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setTxt("singleBreadcrumbCurrent", proj.shortTitle);
    setTxt("workHeroEyebrow", proj.kicker);
    setTxt("workHeroTitle", proj.title);
    setTxt("workHeroDesc", proj.desc);

    const tagsEl = document.getElementById("workHeroTags");
    if (tagsEl) {
      tagsEl.innerHTML = proj.chips
        .map(
          (c) =>
            `<span class="inner-hero-tag"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>${c}</span></span>`
        )
        .join("");
    }

    setTxt("workMetaClient", proj.client);
    setTxt("workMetaCategory", proj.category);
    setTxt("workMetaSprint", `${proj.sprint} · ${proj.year}`);
    setTxt("workMetaOutcome", proj.metric);

    const heroFrame = document.getElementById("workHeroFrame");
    if (heroFrame) heroFrame.style.backgroundImage = `url('${proj.bg}')`;
    const heroBg = document.getElementById("innerHeroBg");
    if (heroBg) heroBg.style.backgroundImage = `url('${proj.bg}')`;

    setTxt("workStoryBrief", proj.brief);
    setTxt("workStoryCraft", proj.craft);

    // Build Project Gallery Cards & Interactive Lightbox Viewer
    const galleryItems = Array.isArray(proj.gallery) && proj.gallery.length
      ? proj.gallery
      : [
          {
            src: proj.bg,
            tag: "Flagship Interface",
            title: `${proj.shortTitle} — Primary Interface`,
            caption: proj.desc,
          },
          {
            src: proj.gallery2 || proj.bg,
            tag: "Architecture",
            title: `${proj.shortTitle} — Component System`,
            caption: proj.craft,
          },
        ];

    const padNum = (n) => String(n).padStart(2, "0");
    const colClasses = [
      "col-12 col-md-6 col-lg-7",
      "col-12 col-md-6 col-lg-5",
      "col-12 col-md-6 col-lg-5",
      "col-12 col-md-6 col-lg-7",
      "col-12 col-md-6 col-lg-6",
      "col-12 col-md-6 col-lg-6",
    ];

    setTxt("workHeroLightboxLabel", `Explore Gallery · ${padNum(galleryItems.length)} Screens`);
    setTxt("workOpenLightboxText", `Open Lightbox (${padNum(galleryItems.length)})`);

    const galleryGrid = document.getElementById("workGalleryGrid");
    if (galleryGrid) {
      galleryGrid.innerHTML = galleryItems
        .map((item, idx) => {
          const num = padNum(idx + 1);
          const colCls = colClasses[idx % colClasses.length];
          return `
            <div class="${colCls}">
              <button
                type="button"
                class="work-gallery-card"
                data-lightbox-index="${idx}"
                aria-label="View ${num} ${item.title} in lightbox"
              >
                <span class="work-gallery-media">
                  <img
                    src="${item.src}"
                    alt="${item.title}"
                    class="work-gallery-img"
                    loading="lazy"
                  />
                </span>
                <span class="work-gallery-top">
                  <span class="work-gallery-tag">${num} · ${item.tag}</span>
                  <span class="work-gallery-zoom-icon" aria-hidden="true">
                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                  </span>
                </span>
                <span class="work-gallery-meta">
                  <span class="work-gallery-title">${item.title}</span>
                  <span class="work-gallery-desc">${item.caption}</span>
                </span>
              </button>
            </div>`;
        })
        .join("");
    }

    // Lightbox State & Event Wiring
    const lightbox = document.getElementById("workLightbox");
    const lbBackdrop = document.getElementById("workLightboxBackdrop");
    const lbStage = document.getElementById("workLightboxStage");
    const lbFigure = document.getElementById("workLightboxFigure");
    const lbImg = document.getElementById("workLightboxImg");
    const lbCounter = document.getElementById("workLightboxCounter");
    const lbTag = document.getElementById("workLightboxTag");
    const lbTitle = document.getElementById("workLightboxTitle");
    const lbCaption = document.getElementById("workLightboxCaption");
    const lbThumbs = document.getElementById("workLightboxThumbs");
    const lbPrev = document.getElementById("workLightboxPrev");
    const lbNext = document.getElementById("workLightboxNext");
    const lbClose = document.getElementById("workLightboxClose");
    const lbZoom = document.getElementById("workLightboxZoom");
    const openBtn = document.getElementById("workOpenLightboxBtn");

    if (lightbox && lbImg) {
      let activeIndex = 0;
      let isZoomed = false;
      let lastTriggerEl = null;
      let touchStartX = 0;
      let touchStartY = 0;

      const setZoomState = (zoomVal) => {
        isZoomed = Boolean(zoomVal);
        if (lbFigure) lbFigure.classList.toggle("is-zoomed", isZoomed);
        if (lbZoom) {
          lbZoom.innerHTML = isZoomed
            ? '<i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>'
            : '<i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>';
        }
      };

      if (lbThumbs) {
        lbThumbs.innerHTML = galleryItems
          .map(
            (item, idx) => `
            <button
              type="button"
              class="work-lightbox-thumb${idx === 0 ? " is-active" : ""}"
              data-thumb-index="${idx}"
              role="tab"
              aria-selected="${idx === 0 ? "true" : "false"}"
              aria-label="Show screen ${padNum(idx + 1)}: ${item.title}"
            >
              <img src="${item.src}" alt="" loading="lazy" />
            </button>`
          )
          .join("");
      }

      const updateLightboxSlide = (index) => {
        const total = galleryItems.length;
        activeIndex = ((index % total) + total) % total;
        const current = galleryItems[activeIndex];
        setZoomState(false);

        lbImg.src = current.src;
        lbImg.alt = current.title;
        if (lbCounter) lbCounter.textContent = `${padNum(activeIndex + 1)} / ${padNum(total)}`;
        if (lbTag) lbTag.textContent = current.tag;
        if (lbTitle) lbTitle.textContent = current.title;
        if (lbCaption) lbCaption.textContent = current.caption;

        if (lbThumbs) {
          lbThumbs.querySelectorAll(".work-lightbox-thumb").forEach((btn, idx) => {
            const active = idx === activeIndex;
            btn.classList.toggle("is-active", active);
            btn.setAttribute("aria-selected", active ? "true" : "false");
            if (active && typeof btn.scrollIntoView === "function") {
              btn.scrollIntoView({ behavior: "smooth", inline: "center", block: "nearest" });
            }
          });
        }
      };

      const openLightbox = (index, triggerEl) => {
        lastTriggerEl = triggerEl || document.activeElement;
        updateLightboxSlide(index);
        lightbox.classList.add("is-open");
        lightbox.setAttribute("aria-hidden", "false");
        document.body.classList.add("is-lightbox-open");
        if (lbClose) lbClose.focus({ preventScroll: true });
      };

      const closeLightbox = () => {
        if (!lightbox.classList.contains("is-open")) return;
        setZoomState(false);
        lightbox.classList.remove("is-open");
        lightbox.setAttribute("aria-hidden", "true");
        document.body.classList.remove("is-lightbox-open");
        if (lastTriggerEl && typeof lastTriggerEl.focus === "function") {
          lastTriggerEl.focus({ preventScroll: true });
        }
      };

      if (galleryGrid) {
        galleryGrid.addEventListener("click", (e) => {
          const card = e.target.closest("[data-lightbox-index]");
          if (!card) return;
          openLightbox(Number(card.getAttribute("data-lightbox-index")) || 0, card);
        });
      }

      if (heroFrame) {
        heroFrame.addEventListener("click", () => openLightbox(0, heroFrame));
        heroFrame.addEventListener("keydown", (e) => {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openLightbox(0, heroFrame);
          }
        });
      }

      if (openBtn) {
        openBtn.addEventListener("click", () => openLightbox(0, openBtn));
      }

      if (lbPrev) lbPrev.addEventListener("click", () => updateLightboxSlide(activeIndex - 1));
      if (lbNext) lbNext.addEventListener("click", () => updateLightboxSlide(activeIndex + 1));
      if (lbClose) lbClose.addEventListener("click", closeLightbox);
      if (lbBackdrop) lbBackdrop.addEventListener("click", closeLightbox);

      if (lbStage) {
        lbStage.addEventListener("click", (e) => {
          if (e.target === lbStage) closeLightbox();
        });
        lbStage.addEventListener(
          "touchstart",
          (e) => {
            if (!e.touches || !e.touches.length) return;
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
          },
          { passive: true }
        );
        lbStage.addEventListener(
          "touchend",
          (e) => {
            if (isZoomed || !e.changedTouches || !e.changedTouches.length) return;
            const dx = e.changedTouches[0].clientX - touchStartX;
            const dy = e.changedTouches[0].clientY - touchStartY;
            if (Math.abs(dx) > 44 && Math.abs(dx) > Math.abs(dy)) {
              updateLightboxSlide(dx < 0 ? activeIndex + 1 : activeIndex - 1);
            }
          },
          { passive: true }
        );
      }

      if (lbZoom) lbZoom.addEventListener("click", () => setZoomState(!isZoomed));
      lbImg.addEventListener("click", () => setZoomState(!isZoomed));

      if (lbThumbs) {
        lbThumbs.addEventListener("click", (e) => {
          const thumb = e.target.closest("[data-thumb-index]");
          if (!thumb) return;
          updateLightboxSlide(Number(thumb.getAttribute("data-thumb-index")) || 0);
        });
      }

      document.addEventListener("keydown", (e) => {
        if (!lightbox.classList.contains("is-open")) return;
        if (e.key === "Escape") {
          e.preventDefault();
          closeLightbox();
        } else if (e.key === "ArrowRight") {
          e.preventDefault();
          updateLightboxSlide(activeIndex + 1);
        } else if (e.key === "ArrowLeft") {
          e.preventDefault();
          updateLightboxSlide(activeIndex - 1);
        }
      });
    }

    const outEl = document.getElementById("workOutcomesMetrics");
    if (outEl) {
      outEl.innerHTML = proj.outcomes
        .map(
          ([v, l]) => `
          <div>
            <span class="work-outcome-num">${v}</span>
            <span class="work-outcome-lbl">${l}</span>
          </div>`
        )
        .join("");
    }

    const csLink = document.getElementById("workMatchingCaseLink");
    if (csLink) {
      csLink.setAttribute("href", caseStudyPermalink(proj.caseId));
    }

    const otherSlugs = Object.keys(PROJECTS)
      .filter((k) => k !== proj.slug)
      .slice(0, 2);
    const relEl = document.getElementById("workMoreProjects");
    if (relEl) {
      relEl.innerHTML = otherSlugs.map(renderRelatedProjectCard).join("");
    }
  }

  // ------------------------------------------------------------------------
  // E. Case Study Single Page (case-study-single.html?case=<id>)
  // ------------------------------------------------------------------------
  const csRoot = document.getElementById("caseStudySingleRoot");
  if (csRoot) {
    const CASES = {
      "cs-finpulse": {
        client: "FinPulse AI",
        hq: "San Francisco, US",
        sector: "Enterprise FinTech SaaS",
        sprint: "10-Week Transformation",
        bg: "assets/images/projects/project-finpulse.jpg",
        title: "Nearly Tripling Institutional Demo Pipeline With a Sub-Second Web Ecosystem",
        summary: "How ALIFY re-architected FinPulse AI’s brand positioning, token design system, and enterprise demo funnel to lift qualified pipeline by 184%.",
        kpis: [
          ["+184%", "Qualified Enterprise Demos"],
          ["0.7s", "Global LCP Load Time"],
          ["3.4x", "Pipeline ROI in 60 Days"],
        ],
        benchmarks: [
          ["Mobile Speed", "41", "99"],
          ["Demo CVR", "1.3%", "3.8%"],
          ["Bounce Rate", "64%", "29%"],
        ],
        deliverables: "Web Architecture, 48-Token Design System, Interactive ROI Calculator, CRM Attribution",
        ch1: "Legacy marketing pages took 4.8 seconds to render on mobile and buried FinPulse’s core institutional differentiation beneath dense technical jargon, causing CFO buyers to exit before requesting a demo.",
        ch2: "We ran stakeholder interviews and funnel diagnostics, restructuring the site around clear treasury outcomes and an interactive ROI simulator that quantifies value before asking for contact details.",
        ch3: "Our engineering squad shipped a zero-bloat semantic frontend paired with hardware-accelerated GSAP product walkthroughs and a frictionless 2-step enterprise calendar flow.",
        ch4: "Within 60 days of launch, mobile Lighthouse hit 99/100, organic dwell time more than doubled, and qualified enterprise demo bookings jumped 184%.",
        quote: "ALIFY rebuilt our enterprise marketing architecture from the ground up. Page speed jumped to 99 on mobile and inbound enterprise demo bookings nearly tripled within sixty days.",
        author: "Sarah Jenkins — VP of Marketing, FinPulse AI",
        related: ["aura-atelier-commerce", "medcore-telehealth"],
      },
      "cs-aura": {
        client: "Aura Atelier",
        hq: "Milan & London",
        sector: "Luxury Headless E-Commerce",
        sprint: "12-Week Flagship Build",
        bg: "assets/images/projects/project-aura-atelier.jpg",
        title: "Blending Editorial Luxury With Sub-Second Headless Checkout Velocity",
        summary: "How ALIFY united Milanese editorial storytelling with an edge-cached storefront and one-tap checkout to lift completion rates by 62%.",
        kpis: [
          ["+62%", "Checkout Completion Lift"],
          ["+38%", "Average Order Value"],
          ["99.99%", "Drop-Day Edge Uptime"],
        ],
        benchmarks: [
          ["Storefront LCP", "3.9s", "0.6s"],
          ["Checkout Rate", "29%", "47%"],
          ["Cart AOV", "$210", "$290"],
        ],
        deliverables: "Visual Identity, Headless Storefront, Slide-Out Wardrobe Builder, Express Multi-Currency Checkout",
        ch1: "High-demand seasonal capsule launches suffered from sluggish mobile media loading and a rigid multi-page checkout that lost 71% of international mobile carts.",
        ch2: "ALIFY designed an editorial grid system where shoppable lookbook hotspots open instant size-and-bundle drawers without breaking browsing momentum.",
        ch3: "By decoupling the storefront onto a globally cached edge layer with preloaded product routes and Apple Pay / Shop Pay express rails, page transitions became instantaneous.",
        ch4: "Aura’s next global drop handled peak traffic with zero downtime, lifting checkout completion by 62% and average order value by 38%.",
        quote: "Rarely do you find a studio that understands both luxury editorial typography and ruthless checkout conversion psychology.",
        author: "Marcus Vance — Founder & CEO, Aura Atelier",
        related: ["solenne-skincare", "finpulse-platform"],
      },
      "cs-medcore": {
        client: "MedCore Health",
        hq: "Toronto, Canada",
        sector: "Clinical Web Platform",
        sprint: "14-Week Platform Overhaul",
        bg: "assets/images/projects/project-medcore-portal.jpg",
        title: "Transforming Legacy Patient Scheduling Into a High-Trust Telemedicine Web Portal",
        summary: "How clinical UX research and WCAG AA web engineering compressed an 11-screen maze into 3 calm steps and halved missed appointments.",
        kpis: [
          ["99.4%", "Patient Portal CSAT"],
          ["-51%", "Missed Appointment Rate"],
          ["-64%", "Support Call Volume"],
        ],
        benchmarks: [
          ["Portal CSAT", "61%", "99.4%"],
          ["Booking Flow", "11 Screens", "3 Steps"],
          ["Task Success", "54%", "96%"],
        ],
        deliverables: "Clinical UX Research, WCAG AA Design System, Patient Web Portal UI, Telehealth Triage Flow",
        ch1: "Patients faced tiny typography, confusing insurance fields, and an 11-step legacy web booking flow that drove completion rates down to 54%.",
        ch2: "Testing prototypes directly with 40+ patients guided a calm, high-contrast web portal featuring smart symptom routing and one-click family profiles.",
        ch3: "We delivered a unified WCAG AA component library with automated appointment reminders and browser-native encrypted video consultations.",
        ch4: "Patient satisfaction climbed to 99.4% while clinic support tickets dropped 64% and missed consultations fell by 51%.",
        quote: "Our patient booking portal went from clunky legacy workflows to an effortless WCAG AA web experience. Their product designers obsessed over every micro-interaction.",
        author: "Zainab Ahmed — Chief Product Officer, MedCore Health",
        related: ["voltmobility-ev", "finpulse-platform"],
      },
      "cs-ledgerflow": {
        client: "LedgerFlow Cloud",
        hq: "New York, US",
        sector: "B2B Cloud & Brand",
        sprint: "8-Week Launch Sprint",
        bg: "assets/images/projects/case-ledgerflow-saas.jpg",
        title: "Positioning an Autonomous Treasury Startup to Close an Oversubscribed $6.5M Seed",
        summary: "How ALIFY crafted category-defining brand authority and an interactive product sandbox ahead of LedgerFlow’s institutional fundraise.",
        kpis: [
          ["$6.5M", "Oversubscribed Seed Closed"],
          ["5.3x", "Enterprise Pilot Signups"],
          ["-44%", "Onboarding Drop-Off"],
        ],
        benchmarks: [
          ["Dwell Time", "0:48", "3:24"],
          ["Pilot Leads", "12/mo", "64/mo"],
          ["Activation", "38%", "68%"],
        ],
        deliverables: "Brand Identity, Interactive Product Tour, SaaS Marketing Website, Self-Serve Onboarding UI",
        ch1: "LedgerFlow had powerful automated reconciliation algorithms, but an early developer-built UI made enterprise finance leaders hesitant to run pilots.",
        ch2: "ALIFY established a sharp institutional visual language and embedded an interactive browser sandbox right inside the homepage hero.",
        ch3: "We simultaneously streamlined first-session product onboarding into a guided 4-minute ledger connection flow with sample data previews.",
        ch4: "Enterprise pilot requests grew 5.3x and LedgerFlow closed an oversubscribed $6.5M seed round with tier-one fintech investors.",
        quote: "Investors specifically complimented our product narrative and interactive web experience during our $6.5M seed round. ALIFY operates like an in-house founding design team.",
        author: "Hamza Siddiqui — Co-Founder, LedgerFlow Cloud",
        related: ["finpulse-platform", "crestline-proptech"],
      },
      "cs-crestline": {
        client: "Crestline Developments",
        hq: "Dubai, UAE",
        sector: "3D Real Estate PropTech",
        sprint: "12-Week Platform Build",
        bg: "assets/images/projects/project-crestline-proptech.jpg",
        title: "Scaling Off-Plan Luxury Property Sales With Interactive 3D Tower Discovery",
        summary: "How ALIFY replaced static PDF brochures with real-time 3D unit exploration and multi-currency payment engines for 110k+ active buyers.",
        kpis: [
          ["110k+", "Monthly Active Investors"],
          ["+136%", "Qualified Viewing Leads"],
          ["$48M+", "Attributed Digital Reservations"],
        ],
        benchmarks: [
          ["Lead Quality", "18%", "52%"],
          ["Page Load", "5.2s", "0.9s"],
          ["Mobile Share", "44%", "79%"],
        ],
        deliverables: "3D Residence Explorer, Multi-Currency Mortgage Engine, Investor Lead Routing, Multilingual UX",
        ch1: "Overseas buyers struggled to evaluate floor elevations, sea-view angles, and milestone payment schedules from heavy static PDF downloads.",
        ch2: "We designed a mobile-first interactive tower selector allowing investors to filter live inventory by floor, bedroom count, aspect, and currency.",
        ch3: "Custom progressive asset loading kept initial render under 0.9 seconds while syncing high-intent reservation inquiries straight into sales reps’ CRM.",
        ch4: "Qualified viewing requests rose 136% and digital funnels contributed over $48M in attributed off-plan unit reservations.",
        quote: "From interactive 3D property tours to instant mortgage calculators, ALIFY engineered our flagship platform on schedule with zero post-launch regressions.",
        author: "Omar Al-Mansoor — Managing Director, Crestline",
        related: ["aura-atelier-commerce", "voltmobility-ev"],
      },
      "cs-solenne": {
        client: "Solenne Paris",
        hq: "Paris, France",
        sector: "DTC Brand & Technical SEO",
        sprint: "10-Week Growth Sprint",
        bg: "assets/images/projects/project-solenne-beauty.jpg",
        title: "Compounding Non-Paid DTC Revenue 2.8x Through Technical SEO & Regimen UX",
        summary: "How ALIFY paired a technical search overhaul with an interactive skin-diagnostic bundle builder to transform unit economics.",
        kpis: [
          ["2.8x", "Non-Paid Organic Revenue"],
          ["+47%", "Average Order Value Lift"],
          ["98/100", "Core Web Vitals Score"],
        ],
        benchmarks: [
          ["Organic Clicks", "19k/mo", "68k/mo"],
          ["Bundle AOV", "$64", "$94"],
          ["Mobile CLS", "0.28", "0.01"],
        ],
        deliverables: "Technical SEO Overhaul, Skin Diagnostic Quiz, Custom Bundle Builder, Editorial Landing Hubs",
        ch1: "Duplicate filter URLs and layout-shifting scripts suppressed organic rankings while single-product orders limited paid acquisition profitability.",
        ch2: "ALIFY restructured collection taxonomy around high-intent skincare concerns and crafted a 60-second skin quiz recommending personalized 3-step regimens.",
        ch3: "Clean structured product schema, zero-CLS image dimensions, and lightweight custom JS brought Core Web Vitals to 98/100.",
        ch4: "Within four months, non-paid organic revenue multiplied 2.8x and average order value climbed from $64 to $94.",
        quote: "Their technical SEO overhaul paired with high-converting landing systems grew our non-paid organic revenue 2.8x in four months without increasing ad spend.",
        author: "Claire Moreau — CMO, Solenne Skincare",
        related: ["aura-atelier-commerce", "medcore-telehealth"],
      },
    };

    const caseId = params.get("case") || ((window.AlifyCurrentItem || {}).type === "case" ? window.AlifyCurrentItem.slug : "") || "cs-finpulse";
    const cs = CASES[caseId] || CASES["cs-finpulse"];

    document.title = `${cs.client} Case Study — ALIFY`;
    const setTxt = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.textContent = val;
    };

    setTxt("singleBreadcrumbCurrent", `${cs.client} Case Study`);
    setTxt("csHeroEyebrow", `${cs.sector} · ${cs.sprint}`);
    setTxt("csHeroTitle", cs.title);
    setTxt("csHeroDesc", cs.summary);

    const scoreEl = document.getElementById("csSingleScoreboard");
    if (scoreEl) {
      scoreEl.innerHTML = cs.kpis
        .map(
          ([v, l]) => `
          <div class="col-12 col-md-4">
            <div class="cs-single-score-item">
              <span class="cs-single-score-val">${v}</span>
              <span class="cs-single-score-lbl">${l}</span>
            </div>
          </div>`
        )
        .join("");
    }

    setTxt("csProfileClient", cs.client);
    setTxt("csProfileHq", cs.hq);
    setTxt("csProfileSector", cs.sector);
    setTxt("csProfileSprint", cs.sprint);
    setTxt("csProfileDeliverables", cs.deliverables);

    const benchEl = document.getElementById("csSidebarBenchmarks");
    if (benchEl) {
      benchEl.innerHTML = cs.benchmarks
        .map(
          ([lbl, oldV, newV]) => `
          <div class="cs-bench-sidebar-row">
            <span>${lbl}</span>
            <span class="cs-bench-sidebar-vals">
              <span class="cs-bench-old">${oldV}</span>
              <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              <strong>${newV}</strong>
            </span>
          </div>`
        )
        .join("");
    }

    const visEl = document.getElementById("csChapterVisual");
    if (visEl) visEl.style.backgroundImage = `url('${cs.bg}')`;
    const heroBg = document.getElementById("innerHeroBg");
    if (heroBg) heroBg.style.backgroundImage = `url('${cs.bg}')`;

    setTxt("csCh1", cs.ch1);
    setTxt("csCh2", cs.ch2);
    setTxt("csCh3", cs.ch3);
    setTxt("csCh4", cs.ch4);
    setTxt("csExecQuote", `“${cs.quote}”`);
    setTxt("csExecAuthor", cs.author);

    const relEl = document.getElementById("csRelatedProjects");
    if (relEl) {
      relEl.innerHTML = cs.related.map(renderRelatedProjectCard).join("");
    }
  }
});
