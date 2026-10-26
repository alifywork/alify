// ==========================================================================
// Reusable Blog System Script:
// 1. Responsive Home/Global Blog Slider (1 / 2 / 3 per view + Drag/Swipe + Dots)
// 2. Blog Archive Category & Search Filtering (blog.html)
// 3. Single Blog Dynamic Post Loader + Synchronized Sticky TOC & Sidebar Scroll
// ==========================================================================

const ALIFY_BLOG_POSTS = {
  "scalable-design-systems": {
    title: "Scalable Design Systems: How Modern Teams Ship UI 3x Faster",
    category: "Design Systems",
    date: "Oct 18, 2026",
    readTime: "6 Min Read",
    image: "assets/images/blog/blog-design-systems.jpg",
    lead:
      "When product teams scale across multiple web and mobile surfaces without a shared token architecture, design debt compounds every sprint. Here is how ALIFY structures multi-brand design systems built for velocity.",
    quote:
      "A great design system doesn’t restrict creativity — it eliminates repetitive decisions so designers and engineers can focus on high-leverage product craft.",
    sections: [
      {
        id: "why-design-systems-stall",
        heading: "1. Why Most Design Systems Stall After Launch",
        paragraphs: [
          "Many teams treat a design system as a static UI kit rather than a living product infrastructure. Without synchronized design tokens and strict component ownership, Figma libraries and production CSS drift apart within weeks.",
          "Successful systems start by codifying foundational primitives — color variables, typographic scales, spacing rhythms, and responsive grid rules — before building complex organisms.",
          "When product managers, designers, and engineers share the exact same vocabulary for spacing, elevation, and interactive states, handoff friction disappears and visual consistency becomes automatic.",
        ],
      },
      {
        id: "token-first-architecture",
        heading: "2. Structuring a Token-First CSS Architecture",
        paragraphs: [
          "By anchoring every component to semantic custom properties, engineering teams gain effortless maintainability. Updating a primary brand hue or typographic hierarchy cascades cleanly across headers, cards, drawers, and interactive states.",
          "Rather than scattering hardcoded hex values across dozens of stylesheets, a disciplined four-color studio token architecture guarantees crisp contrast across every viewport.",
        ],
        bullets: [
          "Define strict root color and font primitives before component styling.",
          "Separate global layout chrome from reusable UI components.",
          "Keep third-party grid usage minimal so custom styles stay predictable.",
        ],
      },
      {
        id: "component-api-discipline",
        heading: "3. Designing Composable Component Patterns",
        paragraphs: [
          "Whether rendering a blog card inside a horizontal home carousel or a three-column archive grid, the component itself should remain layout-agnostic. Parent containers control column widths while the card manages its own internal hierarchy.",
          "This separation of layout responsibility means a single card stylesheet powers carousels, filtered archives, and related-post modules without fragile overrides.",
        ],
      },
      {
        id: "governance-and-adoption",
        heading: "4. Driving Cross-Functional Adoption",
        paragraphs: [
          "Documentation must live alongside real interactive examples. Pairing clear naming conventions with accessible markup ensures every developer on the team adopts shared patterns faster than writing one-off overrides.",
          "Regular design-engineering critiques keep the component library lean, retiring redundant variants before they bloat production bundles.",
        ],
      },
      {
        id: "measuring-velocity-gains",
        heading: "5. Measuring Real Engineering Velocity",
        paragraphs: [
          "Across ALIFY client engagements, transitioning to a token-backed component library reduces UI QA regressions by over 60% and cuts new page assembly from days to hours.",
          "Teams spend less time debating pixel discrepancies and far more time testing conversion hypotheses, refining motion, and shipping customer value.",
        ],
      },
    ],
    tags: ["Design Systems", "Frontend Architecture", "UI Tokens", "CSS Craft"],
  },

  "mastering-gsap-motion": {
    title: "Mastering Web Motion: Crafting High-Performance Micro-Interactions with GSAP",
    category: "Motion & GSAP",
    date: "Oct 12, 2026",
    readTime: "7 Min Read",
    image: "assets/images/blog/blog-gsap-motion.jpg",
    lead:
      "Purposeful motion guides user attention, communicates spatial hierarchy, and elevates perceived brand quality — provided every frame runs at a locked 60fps.",
    quote:
      "Motion should feel like natural physics responding to user intent, never a decorative delay standing between a visitor and their goal.",
    sections: [
      {
        id: "choreography-over-chaos",
        heading: "1. Choreography Over Scattered Animations",
        paragraphs: [
          "When every element animates independently, interfaces feel noisy. Using GSAP timelines with subtle 40ms–80ms staggers creates a cohesive editorial rhythm from eyebrow badge to headline and call-to-action.",
          "Well-paced choreography directs the eye naturally from the value proposition toward primary conversion actions without overwhelming first-time visitors.",
        ],
      },
      {
        id: "gpu-composited-properties",
        heading: "2. Sticking to GPU-Composited Properties",
        paragraphs: [
          "Animating layout properties like top, left, or height triggers costly browser reflows. Restricting tweens to transform (translate, scale, rotate) and opacity keeps motion silky smooth even on mid-range mobile devices.",
          "Cleaning up inline transforms once entrance animations finish ensures sticky positioning and responsive breakpoints behave predictably.",
        ],
        bullets: [
          "Use clearProps after entrance timelines so responsive CSS stays clean.",
          "Pair ScrollTrigger with once: true for editorial content reveals.",
          "Keep micro-interaction durations crisp between 220ms and 380ms.",
        ],
      },
      {
        id: "tactile-button-feedback",
        heading: "3. Designing Tactile CTA Micro-Interactions",
        paragraphs: [
          "Signature details — such as a connected split-pill button whose circular arrow subtly shifts and rotates on hover — create memorable brand moments that measurably lift click-through rates.",
          "Consistent hover curves across navigation links, cards, and drawer triggers give the entire platform an unmistakable studio polish.",
        ],
      },
      {
        id: "scroll-driven-storytelling",
        heading: "4. ScrollTrigger Patterns That Respect the Reader",
        paragraphs: [
          "Scroll-triggered motion works best when it reinforces comprehension, such as progressive data bars or live reading progress indicators that orient readers inside long-form guides.",
          "Synchronized table-of-contents highlighting keeps technical decision-makers anchored as they scan deep architectural articles.",
        ],
      },
      {
        id: "progressive-enhancement",
        heading: "5. Building Motion as Progressive Enhancement",
        paragraphs: [
          "Every page should remain 100% readable and navigable even before scripts execute. Treating GSAP choreography as a progressive layer guarantees resilience and zero invisible-content flashes.",
        ],
      },
    ],
    tags: ["GSAP", "Web Animation", "ScrollTrigger", "Micro-Interactions"],
  },

  "conversion-ux-architecture": {
    title: "Conversion-Led UX Architecture: Turning B2B Traffic Into Qualified Pipeline",
    category: "UX Strategy",
    date: "Oct 05, 2026",
    readTime: "5 Min Read",
    image: "assets/images/blog/blog-conversion-ux.jpg",
    lead:
      "Beautiful visuals capture attention in the first three seconds, but information architecture and friction-free conversion pathways are what turn visitors into long-term clients.",
    quote:
      "Every high-converting page answers three questions immediately: What do you build, who is it for, and why should I trust your team?",
    sections: [
      {
        id: "above-the-fold-clarity",
        heading: "1. Engineering Above-the-Fold Clarity",
        paragraphs: [
          "Vague taglines force prospects to work too hard. Pairing a specific outcome-driven headline with immediate social proof metrics establishes instant credibility above the fold.",
          "Visual proof — such as real conversion trajectories and capability breakdowns — reassures stakeholders that aesthetic craft is backed by measurable execution.",
        ],
      },
      {
        id: "dual-intent-ctas",
        heading: "2. Supporting High-Intent and Exploratory Visitors",
        paragraphs: [
          "Not every visitor is ready to book a discovery call on their first visit. Offering a clear primary action ('Start a Project') alongside a low-friction secondary path ('Explore Our Work') captures both buyer stages.",
        ],
        bullets: [
          "Keep primary navigation focused on core decision pages.",
          "Use breadcrumb trails on inner pages to orient deep-linked visitors.",
          "Place contextual proof cards near primary conversion actions.",
        ],
      },
      {
        id: "reducing-cognitive-friction",
        heading: "3. Reducing Cognitive Load Across Inner Pages",
        paragraphs: [
          "Consistent visual hierarchy, generous whitespace, and scannable section headings help busy executives evaluate technical capabilities in under two minutes.",
          "Dedicated FAQ modules right above closing sections resolve timeline, scope, and support objections before a prospect ever hesitates.",
        ],
      },
      {
        id: "mobile-drawer-ergonomics",
        heading: "4. Optimizing Mobile Navigation Ergonomics",
        paragraphs: [
          "Over half of initial discovery visits happen on mobile devices. A responsive right-side slide drawer with thumb-friendly targets and persistent action buttons keeps conversion paths effortless on small screens.",
        ],
      },
      {
        id: "continuous-cro-iteration",
        heading: "5. Iterating With Post-Launch Analytics",
        paragraphs: [
          "Measuring scroll depth, CTA engagement, and form completion allows product teams to refine messaging and double conversion velocity within ninety days of launch.",
        ],
      },
    ],
    tags: ["UX Strategy", "Conversion Optimization", "B2B Web", "Information Architecture"],
  },

  "iconic-brand-identity": {
    title: "Beyond the Logo: Building Iconic Digital Brand Identities That Endure",
    category: "Brand Identity",
    date: "Sep 28, 2026",
    readTime: "6 Min Read",
    image: "assets/images/blog/blog-brand-identity.jpg",
    lead:
      "In crowded digital categories, visual restraint is a superpower. Discover how disciplined color systems and distinctive typography create unmistakable brand recall.",
    quote:
      "Restraint is what separates a forgettable template from an iconic studio identity — four intentional colors outperform twenty random accents every time.",
    sections: [
      {
        id: "power-of-visual-restraint",
        heading: "1. The Strategic Power of Palette Restraint",
        paragraphs: [
          "World-class digital brands commit to a focused palette — pairing a signature deep primary tone with warm architectural neutrals and crisp typographic contrast.",
          "When every surface respects that strict color discipline, brand recognition strengthens on every scroll.",
        ],
      },
      {
        id: "typographic-voice",
        heading: "2. Pairing Display Character With Editorial Legibility",
        paragraphs: [
          "Combining a geometric wordmark typeface like Garet with bold Montserrat headlines and clean Poppins body copy gives a brand both unmistakable personality and effortless readability.",
        ],
        bullets: [
          "Use geometric display weights for wordmarks and key KPI counters.",
          "Reserve high-contrast bold headings for clear section hierarchy.",
          "Maintain comfortable 1.65–1.8 line-height for long-form reading.",
        ],
      },
      {
        id: "cohesive-art-direction",
        heading: "3. Aligning Imagery With Interface Tokens",
        paragraphs: [
          "When editorial illustrations and photography echo the exact navy, cream, and monochrome tones of the UI, the entire digital experience feels custom-crafted.",
          "Cohesive art direction turns blog archives and case study grids into visual extensions of the brand rather than disconnected stock collages.",
        ],
      },
      {
        id: "signature-ui-motifs",
        heading: "4. Creating Repeatable Signature Motifs",
        paragraphs: [
          "Repeating subtle geometric cues — from a delta symbol watermark to connected pill-and-circle buttons — builds subconscious familiarity across every page.",
        ],
      },
      {
        id: "scaling-across-touchpoints",
        heading: "5. Scaling Brand Systems Across Touchpoints",
        paragraphs: [
          "Documenting voice, motion, and grid rules ensures your brand feels equally sharp across web platforms, pitch decks, and product interfaces.",
        ],
      },
    ],
    tags: ["Brand Identity", "Typography", "Art Direction", "Visual Systems"],
  },

  "headless-ecommerce-2026": {
    title: "Headless E-Commerce in 2026: Speed, Storytelling, and Checkout Velocity",
    category: "E-Commerce",
    date: "Sep 19, 2026",
    readTime: "8 Min Read",
    image: "assets/images/blog/blog-headless-commerce.jpg",
    lead:
      "Modern DTC and luxury retail brands demand sub-second page transitions alongside rich editorial storytelling. Here is when decoupled commerce pays off — and how to architect it.",
    quote:
      "Every 100ms shaved off mobile product discovery directly lifts add-to-cart momentum and customer lifetime value.",
    sections: [
      {
        id: "decoupled-storefront-advantage",
        heading: "1. Why High-Growth Brands Choose Decoupled Frontends",
        paragraphs: [
          "Traditional monolithic templates often force teams to choose between rich brand storytelling and fast page loads. Decoupling the presentation layer unlocks bespoke UX without sacrificing backend reliability.",
          "Merchandising teams gain creative freedom to launch campaign lookbooks while checkout infrastructure remains rock-solid.",
        ],
      },
      {
        id: "merchandising-meets-speed",
        heading: "2. Blending Editorial Storytelling With Instant Filtering",
        paragraphs: [
          "Shoppers expect tactile product discovery — instant collection filtering, crisp imagery, and zero layout shift as assets load.",
        ],
        bullets: [
          "Reserve explicit aspect ratios on all product and editorial media.",
          "Streamline mobile cart drawers for single-thumb checkout.",
          "Surface shipping, warranty, and trust signals beside primary actions.",
        ],
      },
      {
        id: "frictionless-checkout-flows",
        heading: "3. Designing Frictionless Checkout Journeys",
        paragraphs: [
          "Eliminating unnecessary form fields, supporting express wallets, and providing clear progress cues dramatically reduces cart abandonment.",
          "Clear micro-copy around delivery windows and returns builds buyer confidence at the decisive moment.",
        ],
      },
      {
        id: "edge-caching-strategy",
        heading: "4. Smart Caching for Global Storefronts",
        paragraphs: [
          "Serving static shell assets from the edge while hydrating live inventory asynchronously keeps international shoppers experiencing instant responses.",
        ],
      },
      {
        id: "post-purchase-retention",
        heading: "5. Turning First-Time Buyers Into Repeat Advocates",
        paragraphs: [
          "Thoughtful order tracking portals and personalized replenishment flows turn checkout completion into the start of a long-term brand relationship.",
        ],
      },
    ],
    tags: ["E-Commerce", "Headless Commerce", "Performance", "Checkout UX"],
  },

  "technical-seo-guide": {
    title: "Core Web Vitals & Technical SEO: The Engineering Guide to Organic Growth",
    category: "Technical SEO",
    date: "Sep 10, 2026",
    readTime: "6 Min Read",
    image: "assets/images/blog/blog-technical-seo.jpg",
    lead:
      "Sustainable organic acquisition starts in the codebase. Clean semantic HTML, self-hosted WOFF2 fonts, and zero-CLS layouts give search engines and users exactly what they reward.",
    quote:
      "Search engines reward the exact same engineering fundamentals that delight human readers: semantic structure, instant rendering, and effortless navigation.",
    sections: [
      {
        id: "semantic-html-foundations",
        heading: "1. Semantic HTML & Structured Heading Hierarchies",
        paragraphs: [
          "Proper landmark elements — header, nav, main, article, aside, and footer — paired with a logical H1-to-H2 outline allow crawlers and assistive technologies to parse topical authority effortlessly.",
          "Structured FAQ sections and clear table-of-contents anchors also help search engines surface rich passage snippets directly in search results.",
        ],
      },
      {
        id: "self-hosted-font-optimization",
        heading: "2. Self-Hosted WOFF2 Fonts & Zero Layout Shift",
        paragraphs: [
          "Self-hosting compressed WOFF2 typefaces with font-display: swap eliminates third-party DNS lookups and keeps First Contentful Paint blazingly fast.",
        ],
        bullets: [
          "Use explicit aspect-ratio rules on every card and hero image.",
          "Implement semantic breadcrumb navigation on all inner pages.",
          "Keep internal anchor and category linking clean and crawlable.",
        ],
      },
      {
        id: "breadcrumb-internal-linking",
        heading: "3. Breadcrumbs & Internal Link Equity",
        paragraphs: [
          "Clear breadcrumb trails and contextual sidebar links distribute authority across service, industry, and journal pages while reducing bounce rates.",
          "Connecting service pages, industry verticals, and educational articles creates a cohesive topical cluster.",
        ],
      },
      {
        id: "interaction-to-next-paint",
        heading: "4. Keeping Interaction to Next Paint (INP) Crisp",
        paragraphs: [
          "Lightweight vanilla JavaScript event handlers outperform bloated framework bundles, ensuring menus, sliders, accordions, and filters respond instantaneously.",
        ],
      },
      {
        id: "compounding-organic-pipeline",
        heading: "5. Turning Technical Health Into Compounding Traffic",
        paragraphs: [
          "When deep editorial guides sit atop a fast, semantically sound architecture, organic visibility compounds month after month.",
        ],
      },
    ],
    tags: ["Technical SEO", "Core Web Vitals", "Semantic HTML", "Web Performance"],
  },
};

document.addEventListener("DOMContentLoaded", () => {
  initAllBlogSliders();
  initBlogArchiveFilter();
  initSingleBlogPage();
});

// ---------------------------------------------------------------------------
// 1. Responsive Blog Slider (Supports Home & All Inner Pages)
// ---------------------------------------------------------------------------
function initAllBlogSliders() {
  const viewport = document.getElementById("homeBlogSliderViewport");
  const track = document.getElementById("homeBlogSliderTrack");
  const prevBtn = document.getElementById("blogSliderPrev");
  const nextBtn = document.getElementById("blogSliderNext");
  const dotsWrap = document.getElementById("blogSliderDots");
  const progressBar = document.getElementById("blogSliderProgressBar");

  if (!viewport || !track) return;

  const slides = Array.from(track.querySelectorAll(".blog-slide"));
  if (!slides.length) return;

  let currentIndex = 0;
  let slidesPerView = 3;
  let maxIndex = 0;

  const getSlidesPerView = () => {
    if (window.innerWidth < 768) return 1;
    if (window.innerWidth < 992) return 2;
    return 3;
  };

  const buildDots = () => {
    if (!dotsWrap) return;
    dotsWrap.innerHTML = "";
    const totalPositions = maxIndex + 1;
    for (let i = 0; i < totalPositions; i++) {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = `blog-slider-dot${i === currentIndex ? " is-active" : ""}`;
      dot.setAttribute("aria-label", `Go to slide ${i + 1}`);
      dot.addEventListener("click", () => goToSlide(i));
      dotsWrap.appendChild(dot);
    }
  };

  const updateUI = () => {
    const firstSlide = slides[0];
    const gap = parseFloat(getComputedStyle(track).gap) || 24;
    const stepWidth = firstSlide.getBoundingClientRect().width + gap;
    const offset = currentIndex * stepWidth;

    track.style.transform = `translate3d(${-offset}px, 0, 0)`;

    if (prevBtn) prevBtn.disabled = currentIndex <= 0;
    if (nextBtn) nextBtn.disabled = currentIndex >= maxIndex;

    if (dotsWrap) {
      Array.from(dotsWrap.children).forEach((dot, idx) => {
        dot.classList.toggle("is-active", idx === currentIndex);
      });
    }

    if (progressBar) {
      const progressPct = maxIndex === 0 ? 100 : ((currentIndex + 1) / (maxIndex + 1)) * 100;
      progressBar.style.width = `${progressPct}%`;
    }
  };

  const goToSlide = (index) => {
    currentIndex = Math.max(0, Math.min(index, maxIndex));
    updateUI();
  };

  const recalc = () => {
    slidesPerView = getSlidesPerView();
    maxIndex = Math.max(0, slides.length - slidesPerView);
    if (currentIndex > maxIndex) currentIndex = maxIndex;
    buildDots();
    updateUI();
  };

  prevBtn?.addEventListener("click", () => goToSlide(currentIndex - 1));
  nextBtn?.addEventListener("click", () => goToSlide(currentIndex + 1));

  // Touch & Pointer Swipe Support
  let startX = 0;
  let isPointerDown = false;

  viewport.addEventListener("pointerdown", (e) => {
    if (e.target.closest("a, button")) return;
    isPointerDown = true;
    startX = e.clientX;
    track.classList.add("is-dragging");
  });

  window.addEventListener("pointerup", (e) => {
    if (!isPointerDown) return;
    isPointerDown = false;
    track.classList.remove("is-dragging");
    const deltaX = e.clientX - startX;
    if (deltaX < -45 && currentIndex < maxIndex) {
      goToSlide(currentIndex + 1);
    } else if (deltaX > 45 && currentIndex > 0) {
      goToSlide(currentIndex - 1);
    } else {
      updateUI();
    }
  });

  window.addEventListener("resize", recalc);
  recalc();
}

// ---------------------------------------------------------------------------
// 2. Blog Archive Page Category & Search Filter (blog.html)
// ---------------------------------------------------------------------------
function initBlogArchiveFilter() {
  const grid = document.getElementById("blogArchiveGrid");
  if (!grid) return;

  const cols = Array.from(grid.querySelectorAll(".blog-grid-col"));
  const filterBtns = Array.from(document.querySelectorAll(".blog-filter-btn"));
  const countEl = document.getElementById("blogArchiveCount");
  const emptyState = document.getElementById("blogEmptyState");
  const resetBtn = document.getElementById("blogResetFilterBtn");

  const applyFilter = (category, query = "") => {
    const normalizedCat = (category || "all").toLowerCase();
    const normalizedQuery = query.trim().toLowerCase();
    let visible = 0;

    cols.forEach((col) => {
      const cardCat = (col.getAttribute("data-category") || "").toLowerCase();
      const text = col.textContent.toLowerCase();
      const matchesCat = normalizedCat === "all" || cardCat === normalizedCat;
      const matchesQuery = !normalizedQuery || text.includes(normalizedQuery);
      const show = matchesCat && matchesQuery;

      col.classList.toggle("is-hidden", !show);
      if (show) visible++;
    });

    filterBtns.forEach((btn) => {
      const btnCat = (btn.getAttribute("data-filter") || "all").toLowerCase();
      btn.classList.toggle("is-active", btnCat === normalizedCat && !normalizedQuery);
    });

    if (countEl) {
      countEl.textContent = normalizedQuery
        ? `Showing ${visible} result${visible === 1 ? "" : "s"} for "${query}"`
        : `Showing ${visible} of ${cols.length} articles`;
    }

    emptyState?.classList.toggle("is-visible", visible === 0);
  };

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      applyFilter(btn.getAttribute("data-filter") || "all");
    });
  });

  resetBtn?.addEventListener("click", () => applyFilter("all"));

  const params = new URLSearchParams(window.location.search);
  const urlCat = params.get("category");
  const urlQuery = params.get("q");
  if (urlQuery) {
    applyFilter("all", urlQuery);
  } else if (urlCat) {
    applyFilter(urlCat);
  } else {
    applyFilter("all");
  }
}

// ---------------------------------------------------------------------------
// 3. Single Blog Page Loader + Synchronized Sticky TOC & Sidebar Scroll
// ---------------------------------------------------------------------------
function initSingleBlogPage() {
  const articleContainer = document.getElementById("blogSingleProse");
  const tocList = document.getElementById("blogTocList");
  if (!articleContainer || !tocList) return;

  const params = new URLSearchParams(window.location.search);
  const slug = params.get("post");

  if (slug && ALIFY_BLOG_POSTS[slug]) {
    const post = ALIFY_BLOG_POSTS[slug];
    document.title = `${post.title} — ALIFY`;

    const bcCurrent = document.getElementById("blogSingleBreadcrumbCurrent");
    const catBadge = document.getElementById("blogSingleCategory");
    const titleEl = document.getElementById("blogSingleTitle");
    const dateEl = document.getElementById("blogSingleDate");
    const readEl = document.getElementById("blogSingleReadTime");
    const imgEl = document.getElementById("blogSingleFeaturedImg");
    const tagsWrap = document.getElementById("blogSingleTags");

    if (bcCurrent) bcCurrent.textContent = post.title;
    if (catBadge) catBadge.textContent = post.category;
    if (titleEl) titleEl.textContent = post.title;
    if (dateEl) dateEl.textContent = post.date;
    if (readEl) readEl.textContent = post.readTime;
    if (imgEl) {
      imgEl.src = post.image;
      imgEl.alt = post.title;
    }

    let html = `<p class="blog-lead-paragraph">${post.lead}</p>`;
    post.sections.forEach((sec, idx) => {
      html += `<h2 id="${sec.id}">${sec.heading}</h2>`;
      sec.paragraphs.forEach((p) => {
        html += `<p>${p}</p>`;
      });
      if (sec.bullets?.length) {
        html += `<ul class="blog-key-list">`;
        sec.bullets.forEach((b) => {
          html += `<li><span class="blog-key-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span>${b}</span></li>`;
        });
        html += `</ul>`;
      }
      if (idx === 1 && post.quote) {
        html += `
          <blockquote class="blog-callout-quote">
            <i class="fa-solid fa-quote-left blog-callout-quote-icon" aria-hidden="true"></i>
            <p>${post.quote}</p>
            <cite>— ALIFY Product &amp; Design Studio</cite>
          </blockquote>`;
      }
    });
    articleContainer.innerHTML = html;

    if (tagsWrap && post.tags) {
      tagsWrap.innerHTML = post.tags
        .map(
          (t) =>
            `<a href="blog.html?q=${encodeURIComponent(t)}" class="blog-post-tag-pill"><i class="fa-solid fa-tag" aria-hidden="true"></i><span>${t}</span></a>`
        )
        .join("");
    }
  }

  // Build Left Table of Contents from Article H2 Headings
  const headings = Array.from(articleContainer.querySelectorAll("h2[id]"));
  tocList.innerHTML = headings
    .map(
      (h, idx) =>
        `<li><a href="#${h.id}" class="blog-toc-link${idx === 0 ? " is-active" : ""}">${h.textContent}</a></li>`
    )
    .join("");

  const tocLinks = Array.from(tocList.querySelectorAll(".blog-toc-link"));
  const tocCard = document.getElementById("blogTocCard");
  const tocMobileBtn = document.getElementById("blogTocMobileBtn");
  const readingProgressFill = document.getElementById("blogReadingProgressFill");
  const sidebarSticky = document.querySelector(".blog-sidebar-sticky");
  const tocSticky = document.querySelector(".blog-toc-sticky");

  tocLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = link.getAttribute("href")?.slice(1);
      const targetEl = targetId ? document.getElementById(targetId) : null;
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: "smooth", block: "start" });
        if (window.innerWidth < 992) {
          tocCard?.classList.add("is-collapsed");
        }
      }
    });
  });

  tocMobileBtn?.addEventListener("click", () => {
    const collapsed = tocCard?.classList.toggle("is-collapsed");
    tocMobileBtn.setAttribute("aria-expanded", String(!collapsed));
  });

  // Bidirectional Smart Sticky Controller for Sidebar + TOC + Scroll-Spy
  const HEADER_OFFSET = 106;
  const BOTTOM_GAP = 24;
  let lastScrollY = window.scrollY;
  let sidebarTop = HEADER_OFFSET;

  const updateBidirectionalSticky = (el, currentTop) => {
    if (!el || window.innerWidth < 992) {
      if (el) el.style.top = "";
      return HEADER_OFFSET;
    }

    const vh = window.innerHeight;
    const elHeight = el.offsetHeight;

    // Fits comfortably inside viewport -> stay pinned right below sticky header
    if (elHeight + HEADER_OFFSET + BOTTOM_GAP <= vh) {
      el.style.top = `${HEADER_OFFSET}px`;
      return HEADER_OFFSET;
    }

    // Taller than viewport -> smoothly scroll up & down with reader
    const minTop = vh - elHeight - BOTTOM_GAP;
    const delta = window.scrollY - lastScrollY;
    const nextTop = Math.max(minTop, Math.min(HEADER_OFFSET, currentTop - delta));
    el.style.top = `${nextTop}px`;
    return nextTop;
  };

  const onScrollSync = () => {
    const scrollPos = window.scrollY + 150;
    let activeId = headings[0]?.id;

    headings.forEach((h) => {
      if (h.offsetTop <= scrollPos) {
        activeId = h.id;
      }
    });

    tocLinks.forEach((link) => {
      const isActive = link.getAttribute("href") === `#${activeId}`;
      link.classList.toggle("is-active", isActive);
    });

    if (readingProgressFill) {
      const rect = articleContainer.getBoundingClientRect();
      const articleTop = window.scrollY + rect.top - 140;
      const articleHeight = Math.max(1, articleContainer.offsetHeight - window.innerHeight * 0.4);
      const pct = Math.max(0, Math.min(100, ((window.scrollY - articleTop) / articleHeight) * 100));
      readingProgressFill.style.width = `${pct}%`;
    }

    if (tocSticky && window.innerWidth >= 992) {
      tocSticky.style.top = `${HEADER_OFFSET}px`;
    } else if (tocSticky) {
      tocSticky.style.top = "";
    }

    sidebarTop = updateBidirectionalSticky(sidebarSticky, sidebarTop);
    lastScrollY = window.scrollY;
  };

  window.addEventListener("scroll", onScrollSync, { passive: true });
  window.addEventListener("resize", onScrollSync);
  onScrollSync();

  // Copy Share Link Button
  const copyBtn = document.getElementById("blogCopyLinkBtn");
  const shareStatus = document.getElementById("blogShareStatus");
  copyBtn?.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(window.location.href);
      if (shareStatus) shareStatus.textContent = "Article link copied!";
    } catch {
      if (shareStatus) shareStatus.textContent = "Link ready in address bar.";
    }
    setTimeout(() => {
      if (shareStatus) shareStatus.textContent = "";
    }, 2500);
  });

  // Dynamic Related Blogs Grid (End of Single Blog Main Content)
  const relatedGrid = document.getElementById("blogRelatedGrid");
  if (relatedGrid) {
    const activeSlug = slug && ALIFY_BLOG_POSTS[slug] ? slug : "scalable-design-systems";
    const relatedMap = {
      "scalable-design-systems": [
        "mastering-gsap-motion",
        "conversion-ux-architecture",
        "technical-seo-guide",
      ],
      "mastering-gsap-motion": [
        "scalable-design-systems",
        "conversion-ux-architecture",
        "iconic-brand-identity",
      ],
      "conversion-ux-architecture": [
        "headless-ecommerce-2026",
        "scalable-design-systems",
        "technical-seo-guide",
      ],
      "iconic-brand-identity": [
        "scalable-design-systems",
        "headless-ecommerce-2026",
        "mastering-gsap-motion",
      ],
      "headless-ecommerce-2026": [
        "conversion-ux-architecture",
        "technical-seo-guide",
        "iconic-brand-identity",
      ],
      "technical-seo-guide": [
        "scalable-design-systems",
        "conversion-ux-architecture",
        "headless-ecommerce-2026",
      ],
    };

    const chosenSlugs = (
      relatedMap[activeSlug] ||
      Object.keys(ALIFY_BLOG_POSTS).filter((k) => k !== activeSlug).slice(0, 3)
    ).filter((k) => ALIFY_BLOG_POSTS[k] && k !== activeSlug);

    relatedGrid.innerHTML = chosenSlugs
      .map((relSlug) => {
        const rp = ALIFY_BLOG_POSTS[relSlug];
        const excerpt =
          rp.lead.length > 142 ? `${rp.lead.slice(0, 139).trim()}...` : rp.lead;
        return `
          <div class="col-12 col-md-6 col-lg-4 blog-grid-col">
            <article class="blog-card">
              <a href="blog-single.html?post=${relSlug}" class="blog-card-media">
                <span class="blog-card-category">${rp.category}</span>
                <img
                  src="${rp.image}"
                  alt="${rp.title}"
                  class="blog-card-img"
                  loading="lazy"
                />
              </a>
              <div class="blog-card-body">
                <div class="blog-card-meta">
                  <span class="blog-card-meta-item">
                    <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                    <span>${rp.date}</span>
                  </span>
                  <span class="blog-card-meta-item">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                    <span>${rp.readTime}</span>
                  </span>
                </div>
                <h3 class="blog-card-title">
                  <a href="blog-single.html?post=${relSlug}">${rp.title}</a>
                </h3>
                <p class="blog-card-excerpt">${excerpt}</p>
                <div class="blog-card-footer">
                  <a href="blog-single.html?post=${relSlug}" class="blog-read-more">
                    <span class="blog-read-more-pill">Read More</span>
                    <span class="blog-read-more-circle" aria-hidden="true">
                      <i class="fa-solid fa-arrow-up blog-read-more-arrow"></i>
                    </span>
                  </a>
                </div>
              </div>
            </article>
          </div>`;
      })
      .join("");
  }
}
