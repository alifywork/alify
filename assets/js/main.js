// ==========================================================================
// Global GSAP Choreography: Hero Entrance + Modern Left/Right Section Reveals
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") return;

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const hasScrollTrigger = typeof ScrollTrigger !== "undefined";
  const isInitiallyVisible = (element) => {
    const rect = element.getBoundingClientRect();
    return rect.top < window.innerHeight && rect.bottom > 0;
  };
  if (hasScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
  }

  // --------------------------------------------------------------------------
  // 1. Home Hero Entrance (only when outside the first viewport)
  // --------------------------------------------------------------------------
  const homeHero = document.querySelector(".home-hero");
  if (homeHero) {
    // Keep above-the-fold hero content stable on first paint; entrance motion is
    // reserved for a hero loaded outside the initial viewport (for hash links).
    if (!reducedMotion && !isInitiallyVisible(homeHero)) {
      const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

      tl.from(".hero-status-pill", {
        y: -18,
        opacity: 0,
        duration: 0.48,
        clearProps: "transform,opacity",
      })
        .from(
          ".hero-display-title",
          {
            x: -42,
            opacity: 0,
            duration: 0.72,
            clearProps: "transform,opacity",
          },
          "-=0.24"
        )
        .from(
          ".hero-lead",
          {
            x: 42,
            opacity: 0,
            duration: 0.68,
            clearProps: "transform,opacity",
          },
          "-=0.52"
        )
        .from(
          ".hero-cta-group > *",
          {
            y: 18,
            opacity: 0,
            duration: 0.5,
            stagger: 0.09,
            clearProps: "transform,opacity",
          },
          "-=0.36"
        )
        .from(
          ".hero-stat-item",
          {
            y: 18,
            opacity: 0,
            duration: 0.48,
            stagger: 0.08,
            clearProps: "transform,opacity",
          },
          "-=0.3"
        );
    }

    // KPI values stay at their authored final values to avoid a visible reset
    // between first paint and the start of JavaScript.
  }

  // --------------------------------------------------------------------------
  // 2. Modern Left & Right Directional Section Scroll Animations (All Static Pages)
  // --------------------------------------------------------------------------
  if (reducedMotion || !hasScrollTrigger) return;

  const animated = new WeakSet();
  const getViewportScale = () => {
    const width = window.innerWidth;
    if (width < 768) return 0.38;
    if (width < 992) return 0.68;
    return 1;
  };
  const distStrong = 64;
  const distMed = 52;
  const distSoft = 42;

  const revealX = (target, fromX, opts = {}) => {
    if (!target || animated.has(target)) return;
    if (isInitiallyVisible(target)) {
      animated.add(target);
      return;
    }
    animated.add(target);

    gsap.from(target, {
      x: () => fromX * getViewportScale(),
      opacity: 0,
      duration: opts.duration || 0.88,
      delay: opts.delay || 0,
      ease: opts.ease || "power3.out",
      clearProps: "transform,opacity",
      scrollTrigger: {
        trigger: opts.trigger || target,
        start: opts.start || "top 86%",
        once: true,
        invalidateOnRefresh: true,
      },
    });
  };

  // A. Two-Column Section Header Rows (Left Heading <- | -> Right Desc/Controls)
  const headRowSelectors = [
    ".services-head-row",
    ".industries-head-row",
    ".projects-head-row",
    ".pricing-head-row",
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
  document.querySelectorAll(".studio-sec-head, .single-sec-head, .pricing-calc-head, .pricing-matrix-head").forEach((head) => {
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

  // 2) Start a Project Scope Builder + Sticky Roadmap & Pricing Investment Calculator
  document.querySelectorAll(".start-project-row").forEach((row) => {
    const leftBuilder = row.querySelector(".scope-builder-card");
    const rightRoadmap = row.querySelector(".project-roadmap-card");
    if (leftBuilder) revealX(leftBuilder, -distStrong, { trigger: row });
    if (rightRoadmap) revealX(rightRoadmap, distStrong, { trigger: row, delay: 0.1 });
  });

  document.querySelectorAll(".pricing-calc-row").forEach((row) => {
    const leftCalc = row.querySelector(".pricing-calc-builder");
    const rightSummary = row.querySelector(".pricing-calc-summary");
    if (leftCalc) revealX(leftCalc, -distStrong, { trigger: row });
    if (rightSummary) revealX(rightSummary, distStrong, { trigger: row, delay: 0.1 });
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
    ".pricing-guarantees-strip",
    ".pricing-matrix-wrap",
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
    ".pricing-tiers-row",
    ".pricing-guarantees-row",
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
    ".work-gallery-row",
    ".book-outcomes-row",
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

  // Fallback reveal for top-level content sections without a specialized reveal.
  // Keep detailed row/card choreography above intact and avoid stacking reveals.
  document.querySelectorAll("main section").forEach((section, idx) => {
    if (section.matches(".home-hero")) return; // has a dedicated entrance timeline
    const animatedSectionParent = section.parentElement.closest("section");
    if (animatedSectionParent && animated.has(animatedSectionParent)) return;
    if (animated.has(section)) return;
    const hasAnimatedContent = Array.from(section.querySelectorAll("*"))
      .some((element) => animated.has(element));
    if (hasAnimatedContent) return;

    revealX(section, idx % 2 === 0 ? -distSoft : distSoft, {
      trigger: section,
      start: "top 90%",
      duration: 0.78,
    });
  });

  // --------------------------------------------------------------------------
  // 3. Parallax & Multi-Depth Scroll Choreography (Home + All Static Pages)
  // --------------------------------------------------------------------------
  const getParallaxShift = () => {
    const width = window.innerWidth;
    if (width < 768) return 28;
    if (width < 992) return 40;
    return 54;
  };

  // A. Multi-Plane Hero Parallax (Background Media + Watermark + Foreground Depth)
  document
    .querySelectorAll(".home-hero, .inner-hero, .blog-single-hero, .thankyou-hero, .error-stage-section")
    .forEach((hero) => {
      const bg = hero.querySelector(".hero-bg-media");
      if (bg) {
        gsap.fromTo(
          bg,
          { yPercent: -5, scale: 1.04 },
          {
            yPercent: () => window.innerWidth < 768 ? 10 : window.innerWidth < 992 ? 13 : 17,
            scale: 1.12,
            ease: "none",
            scrollTrigger: {
              trigger: hero,
              start: "top top",
              end: "bottom top",
              scrub: 0.45,
              invalidateOnRefresh: true,
            },
          }
        );
      }

      const watermark = hero.querySelector(".inner-hero-watermark");
      if (watermark) {
        gsap.to(watermark, {
          yPercent: 28,
          xPercent: -6,
          ease: "none",
          scrollTrigger: {
            trigger: hero,
            start: "top top",
            end: "bottom top",
            scrub: 0.65,
          },
        });
      }

      const heroCopy = hero.querySelector(
        ".hero-content-col, .inner-hero-copy-col, .blog-single-hero-inner"
      );
      if (heroCopy) {
        gsap.to(heroCopy, {
          y: () => window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? -14 : -26,
          ease: "none",
          scrollTrigger: {
            trigger: hero,
            start: "top top",
            end: "bottom top",
            scrub: 0.55,
            invalidateOnRefresh: true,
          },
        });
      }

      const heroLeadCard = hero.querySelector(".hero-lead-form-card");
      if (heroLeadCard) {
        gsap.to(heroLeadCard, {
          y: () => window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? 10 : 20,
          ease: "none",
          scrollTrigger: {
            trigger: hero,
            start: "top top",
            end: "bottom top",
            scrub: 0.6,
            invalidateOnRefresh: true,
          },
        });
      }
    });

  // B. Scrubbed Section & Executive Banner Background Image Parallax (--sec-parallax-y)
  const parallaxSectionSelectors = [
    ".home-services-section",
    ".home-projects-section",
    ".home-case-studies-section",
    ".faq-section",
    ".reusable-contact-section",
    ".about-metrics-section",
    ".studio-process-section.has-section-bg",
    ".single-process-section",
    ".single-related-projects-section",
    ".blog-related-section",
    ".pricing-calc-section",
    ".cs-impact-banner",
    ".testimonials-metrics-bar",
    ".work-outcomes-banner",
    ".cs-single-scoreboard",
    ".faq-hub-cta-section",
    ".book-outcomes-section",
  ].join(", ");

  document.querySelectorAll(parallaxSectionSelectors).forEach((sec) => {
    gsap.fromTo(
      sec,
      { "--sec-parallax-y": () => `${-getParallaxShift()}px` },
      {
        "--sec-parallax-y": () => `${getParallaxShift()}px`,
        ease: "none",
        scrollTrigger: {
          trigger: sec,
          start: "top bottom",
          end: "bottom top",
          scrub: 0.5,
          invalidateOnRefresh: true,
        },
      }
    );
  });

  // C. Window-Portal Inner Image Parallax (Frames, Showcases & Portfolio Cards)
  document
    .querySelectorAll(
      ".about-visual-bg, .cs-visual-bg, .single-showcase-bg, .project-grid-card-bg, .project-panel-bg, .service-card-bg, .industry-card-bg, .work-gallery-media"
    )
    .forEach((bgEl) => {
      const parentFrame = bgEl.parentElement;
      if (!parentFrame) return;
      gsap.fromTo(
        bgEl,
        { yPercent: () => window.innerWidth < 768 ? -3.5 : window.innerWidth < 992 ? -4.5 : -5.5 },
        {
          yPercent: () => window.innerWidth < 768 ? 3.5 : window.innerWidth < 992 ? 4.5 : 5.5,
          ease: "none",
          scrollTrigger: {
            trigger: parentFrame,
            start: "top bottom",
            end: "bottom top",
            scrub: 0.55,
            invalidateOnRefresh: true,
          },
        }
      );
    });

  // Direct background-image frames on Work Single & Case Study Single
  document
    .querySelectorAll(".work-hero-frame, .cs-chapter-visual")
    .forEach((frame) => {
      gsap.fromTo(
        frame,
        { backgroundPositionY: "36%" },
        {
          backgroundPositionY: "64%",
          ease: "none",
          scrollTrigger: {
            trigger: frame,
            start: "top bottom",
            end: "bottom top",
            scrub: 0.55,
            invalidateOnRefresh: true,
          },
        }
      );
    });

  // D. Differential Column Speed Drift & Watermark Parallax (Parallax-Like Depth)
  document
    .querySelectorAll(
      ".about-visual-frame, .reusable-contact-card, .contact-directory-card, .thankyou-summary-card"
    )
    .forEach((card) => {
      const triggerRow = card.closest(".row") || card;
      gsap.fromTo(
        card,
        { y: () => window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? 12 : 24 },
        {
          y: () => window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? -12 : -24,
          ease: "none",
          scrollTrigger: {
            trigger: triggerRow,
            start: "top bottom",
            end: "bottom top",
            scrub: 0.75,
            invalidateOnRefresh: true,
          },
        }
      );
    });

  // Staggered odd/even column velocity scales by viewport, including tablet.
  document
    .querySelectorAll(
      ".about-principles-row, .studio-process-row, .single-process-row, .thankyou-steps-row"
    )
    .forEach((row) => {
      const cards = row.querySelectorAll(
        ".about-principle-card, .studio-process-card, .single-process-card, .thankyou-step-card"
      );
      cards.forEach((card, idx) => {
        const sign = idx % 2 === 0 ? -1 : 1;
        gsap.fromTo(
          card,
          { y: () => sign * (window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? 10 : 18) },
          {
            y: () => sign * (window.innerWidth < 768 ? 0 : window.innerWidth < 992 ? -10 : -18),
            ease: "none",
            scrollTrigger: {
              trigger: row,
              start: "top bottom",
              end: "bottom top",
              scrub: 0.75,
              invalidateOnRefresh: true,
            },
          }
        );
      });
    });

  document.querySelectorAll(".footer-cta-watermark").forEach((wm) => {
    const banner = wm.closest(".footer-cta-banner") || wm;
    gsap.fromTo(
      wm,
      { yPercent: 18, xPercent: 4 },
      {
        yPercent: -20,
        xPercent: -6,
        ease: "none",
        scrollTrigger: {
          trigger: banner,
          start: "top bottom",
          end: "bottom top",
          scrub: 0.7,
        },
      }
    );
  });

  // E. Interactive Spatial 3D Pointer Parallax on Visual Showcase Windows (Fine Pointer)
  if (window.matchMedia("(hover: hover) and (pointer: fine)").matches) {
    document
      .querySelectorAll(".about-visual-frame, .cs-visual-frame, .single-showcase-banner")
      .forEach((stage) => {
        const fg = stage.querySelector(
          ".about-visual-card, .cs-benchmark-dock, .single-banner-stats-bar"
        );
        const topBar = stage.querySelector(
          ".about-visual-top, .cs-visual-top, .single-banner-top"
        );
        if (!fg) return;

        const fgX = gsap.quickTo(fg, "x", { duration: 0.45, ease: "power3.out" });
        const fgY = gsap.quickTo(fg, "y", { duration: 0.45, ease: "power3.out" });
        const topX = topBar ? gsap.quickTo(topBar, "x", { duration: 0.45, ease: "power3.out" }) : null;
        const topY = topBar ? gsap.quickTo(topBar, "y", { duration: 0.45, ease: "power3.out" }) : null;

        stage.addEventListener("mousemove", (e) => {
          const rect = stage.getBoundingClientRect();
          const nx = (e.clientX - rect.left) / rect.width - 0.5;
          const ny = (e.clientY - rect.top) / rect.height - 0.5;
          fgX(nx * 14);
          fgY(ny * 10);
          if (topX && topY) {
            topX(nx * -10);
            topY(ny * -8);
          }
        });

        stage.addEventListener("mouseleave", () => {
          fgX(0);
          fgY(0);
          if (topX && topY) {
            topX(0);
            topY(0);
          }
        });
      });
  }

  // Refresh ScrollTrigger positions once fonts & images finish settling
  window.addEventListener(
    "load",
    () => {
      ScrollTrigger.refresh();
    },
    { once: true }
  );

  let resizeRefreshTimer;
  window.addEventListener(
    "resize",
    () => {
      window.clearTimeout(resizeRefreshTimer);
      resizeRefreshTimer = window.setTimeout(() => ScrollTrigger.refresh(), 140);
    },
    { passive: true }
  );
});
