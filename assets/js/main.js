// ==========================================================================
// Global GSAP Choreography: Hero Entrance + Modern Left/Right Section Reveals
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") return;

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const hasScrollTrigger = typeof ScrollTrigger !== "undefined";
  if (hasScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
  }

  // --------------------------------------------------------------------------
  // 1. Home Hero Left / Right Entrance & KPI Counters
  // --------------------------------------------------------------------------
  const homeHero = document.querySelector(".home-hero");
  if (homeHero) {
    if (!reducedMotion) {
      const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

      tl.from(".hero-status-pill", {
        x: -40,
        opacity: 0,
        duration: 0.5,
        clearProps: "transform,opacity",
      })
        .from(
          ".hero-display-title, .hero-lead",
          {
            x: -54,
            opacity: 0,
            duration: 0.72,
            stagger: 0.1,
            clearProps: "transform,opacity",
          },
          "-=0.28"
        )
        .from(
          ".hero-cta-group > *",
          {
            x: -36,
            opacity: 0,
            duration: 0.52,
            stagger: 0.08,
            clearProps: "transform,opacity",
          },
          "-=0.38"
        )
        .from(
          ".hero-stat-item",
          {
            x: -28,
            opacity: 0,
            duration: 0.48,
            stagger: 0.08,
            clearProps: "transform,opacity",
          },
          "-=0.32"
        )
        .from(
          ".hero-showcase-card",
          {
            x: 60,
            opacity: 0,
            duration: 0.78,
            clearProps: "transform,opacity",
          },
          "-=0.75"
        )
        .from(
          ".hero-bar-fill",
          {
            scaleY: 0,
            transformOrigin: "bottom center",
            duration: 0.65,
            stagger: 0.08,
            ease: "power2.out",
            clearProps: "transform",
          },
          "-=0.38"
        )
        .from(
          ".hero-float-badge, .hero-float-review",
          {
            x: 36,
            opacity: 0,
            duration: 0.55,
            stagger: 0.1,
            clearProps: "transform,opacity",
          },
          "-=0.45"
        );
    }

    // Smooth Number Counter Animation for Hero KPIs
    document.querySelectorAll(".hero-stat-number[data-count]").forEach((el) => {
      const target = Number(el.getAttribute("data-count") || 0);
      const suffix = el.getAttribute("data-suffix") || "";
      if (!target) return;

      const counter = { val: 0 };
      gsap.to(counter, {
        val: target,
        duration: 1.5,
        ease: "power2.out",
        onUpdate: () => {
          el.textContent = `${Math.round(counter.val)}${suffix}`;
        },
      });
    });
  }

  // --------------------------------------------------------------------------
  // 2. Modern Left & Right Directional Section Scroll Animations (All 19 Pages)
  // --------------------------------------------------------------------------
  if (reducedMotion || !hasScrollTrigger) return;

  const animated = new WeakSet();
  const isMobile = window.innerWidth < 768;
  const distStrong = isMobile ? 38 : 64;
  const distMed = isMobile ? 32 : 52;
  const distSoft = isMobile ? 26 : 42;

  const revealX = (target, fromX, opts = {}) => {
    if (!target || animated.has(target)) return;
    animated.add(target);

    gsap.from(target, {
      x: fromX,
      opacity: 0,
      duration: opts.duration || 0.88,
      delay: opts.delay || 0,
      ease: opts.ease || "power3.out",
      clearProps: "transform,opacity",
      scrollTrigger: {
        trigger: opts.trigger || target,
        start: opts.start || "top 86%",
        once: true,
      },
    });
  };

  // A. Two-Column Section Header Rows (Left Heading <- | -> Right Desc/Controls)
  const headRowSelectors = [
    ".services-head-row",
    ".industries-head-row",
    ".projects-head-row",
    ".studio-sec-head-row",
    ".cs-head-row",
    ".testimonials-head-row",
    ".blog-section-head-row",
    ".blog-related-head-row",
    ".single-related-head-row",
  ].join(", ");

  document.querySelectorAll(headRowSelectors).forEach((row) => {
    const cols = Array.from(row.children).filter((el) => el.matches('[class*="col-"]'));
    if (cols[0]) revealX(cols[0], -distMed, { trigger: row });
    if (cols[1]) revealX(cols[1], distMed, { trigger: row, delay: 0.08 });
  });

  // B. Centered Single-Column Section Headers (Eyebrow + H2 from Left, Desc from Right)
  document.querySelectorAll(".studio-sec-head, .single-sec-head").forEach((head) => {
    const kids = Array.from(head.children);
    kids.forEach((child, idx) => {
      const dir = child.matches("p") ? distSoft : -distSoft;
      revealX(child, dir, { trigger: head, delay: idx * 0.06 });
    });
  });

  // C. Two-Column Split Content Sections (Left Col from Left, Right Col from Right)
  const splitRowSelectors = [
    ".about-story-row",
    ".about-mv-row",
    ".reusable-contact-row",
    ".contact-page-row",
    ".work-story-row",
    ".thankyou-row",
    ".footer-cta-row",
  ].join(", ");

  document.querySelectorAll(splitRowSelectors).forEach((row) => {
    const cols = Array.from(row.children).filter((el) => el.matches('[class*="col-"]'));
    if (cols[0]) revealX(cols[0], -distStrong, { trigger: row, duration: 0.94 });
    if (cols[1]) revealX(cols[1], distStrong, { trigger: row, duration: 0.94, delay: 0.1 });
  });

  // D. Sticky-Safe Split Sections (Animate inner cards so position: sticky stays 100% intact)
  // 1) FAQ Section: Left intro children from Left, Right accordion items from Right
  document.querySelectorAll(".faq-section").forEach((sec) => {
    const introKids = sec.querySelectorAll(".faq-intro-sticky > *");
    introKids.forEach((el, idx) => {
      revealX(el, -distMed, { trigger: sec, delay: idx * 0.07 });
    });
    const faqItems = sec.querySelectorAll(".faq-item");
    faqItems.forEach((item, idx) => {
      revealX(item, distMed, { trigger: item, start: "top 90%", delay: Math.min(idx * 0.05, 0.25) });
    });
  });

  // 2) Start a Project Scope Builder + Sticky Roadmap
  document.querySelectorAll(".start-project-row").forEach((row) => {
    const leftBuilder = row.querySelector(".scope-builder-card");
    const rightRoadmap = row.querySelector(".project-roadmap-card");
    if (leftBuilder) revealX(leftBuilder, -distStrong, { trigger: row });
    if (rightRoadmap) revealX(rightRoadmap, distStrong, { trigger: row, delay: 0.1 });
  });

  // 3) Blog Single Sticky 3-Column Layout
  document.querySelectorAll(".blog-single-row").forEach((row) => {
    const tocCard = row.querySelector(".blog-toc-card");
    if (tocCard) revealX(tocCard, -distSoft, { trigger: row });
    row.querySelectorAll(".blog-sidebar-widget, .blog-sidebar-cta").forEach((w, idx) => {
      revealX(w, distSoft, { trigger: w, start: "top 90%", delay: idx * 0.06 });
    });
  });

  // 4) Legal Document Sticky Layout (Privacy Policy & Terms)
  document.querySelectorAll(".legal-doc-row").forEach((row) => {
    const toc = row.querySelector(".legal-toc-card, .legal-toc-sticky > *");
    if (toc) revealX(toc, -distSoft, { trigger: row });
    row.querySelectorAll(".legal-clause-card").forEach((card, idx) => {
      revealX(card, idx % 2 === 0 ? distMed : -distSoft, { trigger: card, start: "top 88%" });
    });
  });

  // 5) Case Study Single Sticky Profile + Narrative Chapters
  document.querySelectorAll(".cs-single-main-row").forEach((row) => {
    const profile = row.querySelector(".cs-profile-card");
    if (profile) revealX(profile, -distMed, { trigger: row });
    row.querySelectorAll(".cs-chapter-card").forEach((ch, idx) => {
      revealX(ch, idx % 2 === 0 ? distMed : -distSoft, { trigger: ch, start: "top 88%" });
    });
  });

  // E. Executive Case Studies Alternating Left/Right Stage Cards
  document.querySelectorAll(".cs-editorial-card, .cs-stage-panel.is-active").forEach((card) => {
    const isReversed = card.classList.contains("is-reversed");
    const visual = card.querySelector(".cs-visual-frame");
    const narrative = card.querySelector(".cs-narrative-col");
    if (visual) revealX(visual, isReversed ? distStrong : -distStrong, { trigger: card, duration: 0.95 });
    if (narrative) revealX(narrative, isReversed ? -distStrong : distStrong, { trigger: card, duration: 0.95, delay: 0.1 });
  });

  // F. Opposing Left / Right Sliders, Marquee Rows & Filter/Score Bars
  // 1) 2-Row Testimonials Slider: Row 1 glides from Left, Row 2 glides from Right
  document.querySelectorAll(".testimonials-slider-section").forEach((sec) => {
    const rows = sec.querySelectorAll(".testimonials-row-viewport");
    if (rows[0]) revealX(rows[0], -distStrong, { trigger: rows[0], duration: 0.95 });
    if (rows[1]) revealX(rows[1], distStrong, { trigger: rows[1], duration: 0.95, delay: 0.1 });
  });

  // 2) Services, Industries & Blog Slider Viewports + Controls
  document.querySelectorAll(".services-slider-viewport").forEach((vp) => revealX(vp, distStrong, { duration: 0.95 }));
  document.querySelectorAll(".industries-slider-viewport").forEach((vp) => revealX(vp, -distStrong, { duration: 0.95 }));
  document.querySelectorAll(".blog-slider-viewport").forEach((vp) => revealX(vp, distStrong, { duration: 0.95 }));

  // 3) Home Hover-Expanding Projects Accordion Stage (Left half from Left, Right half from Right)
  const accordionStage = document.getElementById("projectsAccordionStage");
  if (accordionStage) {
    const panels = Array.from(accordionStage.querySelectorAll(".project-panel"));
    const mid = panels.length / 2;
    panels.forEach((panel, idx) => {
      const fromX = idx < mid ? -distMed : distMed;
      const staggerDelay = Math.abs(idx - (mid - 0.5)) * 0.06;
      revealX(panel, fromX, { trigger: accordionStage, delay: staggerDelay, duration: 0.85 });
    });
  }

  // 4) Filter Bars, Showcase Banners & Scoreboards
  const bannerBarSelectors = [
    ".services-filter-bar",
    ".projects-filter-bar",
    ".cs-impact-banner",
    ".cs-filter-bar",
    ".cs-selector-bar",
    ".testimonials-metrics-bar",
    ".testimonials-filter-bar",
    ".blog-filter-bar",
    ".single-showcase-banner",
    ".work-meta-bar",
    ".work-hero-frame",
    ".cs-single-scoreboard",
  ].join(", ");

  document.querySelectorAll(bannerBarSelectors).forEach((bar, idx) => {
    revealX(bar, idx % 2 === 0 ? -distMed : distMed, { duration: 0.85 });
  });

  // G. Multi-Column Card Grids (Position-Aware Left & Right Entrance)
  const gridRowSelectors = [
    ".projects-grid-row",
    ".services-grid-row",
    ".industries-grid-row",
    ".testimonials-grid-row",
    ".blog-grid-row",
    "#blogRelatedGrid",
    ".single-bento-row",
    ".industry-challenge-row",
    ".about-principles-row",
    ".about-team-row",
    ".about-metrics-row",
    ".studio-process-row",
    ".single-process-row",
    ".thankyou-steps-row",
    ".cs-tabs-row",
    "#relatedProjectsGrid",
  ].join(", ");

  document.querySelectorAll(gridRowSelectors).forEach((gridRow) => {
    const cols = Array.from(gridRow.children).filter((el) => el.matches('[class*="col-"]'));
    if (!cols.length) return;
    const rowRect = gridRow.getBoundingClientRect();
    const rowCenter = rowRect.left + rowRect.width / 2;

    cols.forEach((col, idx) => {
      const rect = col.getBoundingClientRect();
      const colCenter = rect.left + rect.width / 2;
      const delta = colCenter - rowCenter;

      let fromX;
      if (Math.abs(delta) > 28) {
        // Left-side columns enter from Left; Right-side columns enter from Right
        fromX = delta < 0 ? -distMed : distMed;
      } else {
        // Center column (in 3-col grids) or stacked 1-col mobile alternates Left / Right
        fromX = idx % 2 === 0 ? -distSoft : distSoft;
      }

      revealX(col, fromX, {
        trigger: col,
        start: "top 89%",
        delay: (idx % 3) * 0.06,
        duration: 0.86,
      });
    });
  });

  // Refresh ScrollTrigger positions once fonts & images finish settling
  window.addEventListener(
    "load",
    () => {
      ScrollTrigger.refresh();
    },
    { once: true }
  );
});
