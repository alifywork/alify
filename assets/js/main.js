// Site-wide GSAP entrances and one-time scroll reveals (no loader or parallax).
(() => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  const { gsap, ScrollTrigger } = window;
  if (prefersReducedMotion || !gsap || !ScrollTrigger) return;

  gsap.registerPlugin(ScrollTrigger);

  const heroItems = gsap.utils.toArray(
    [
      ".home-hero .hero-status-pill",
      ".home-hero .hero-display-title",
      ".home-hero .hero-lead",
      ".home-hero .hero-cta-group",
      ".home-hero .hero-stats-bar",
      ".inner-hero .inner-hero-eyebrow",
      ".inner-hero .inner-hero-title",
      ".inner-hero .inner-hero-desc",
      ".inner-hero .inner-hero-tags",
      ".inner-hero .inner-hero-card",
    ].join(",")
  );

  if (heroItems.length) {
    gsap.from(heroItems, {
      autoAlpha: 0,
      y: 14,
      duration: 0.48,
      delay: 0.04,
      stagger: 0.075,
      ease: "power2.out",
      clearProps: "opacity,visibility,transform",
    });
  }

  const sections = gsap.utils.toArray(
    "main > section:not(.home-hero):not(.inner-hero):not(.reusable-contact-section)"
  );
  sections.forEach((section) => {
    const sectionColumns = Array.from(
      section.querySelectorAll(".row > [class*='col-']")
    ).filter((column) => column.closest("section") === section);
    const revealTargets = sectionColumns.length ? sectionColumns : [section];

    gsap.from(revealTargets, {
      autoAlpha: 0,
      y: sectionColumns.length ? 28 : 36,
      scale: 0.985,
      duration: 0.72,
      stagger: sectionColumns.length > 1 ? 0.09 : 0,
      ease: "power3.out",
      clearProps: "opacity,visibility,transform",
      scrollTrigger: {
        trigger: section,
        start: "top 88%",
        once: true,
      },
    });
  });

  const timelineTracks = gsap.utils.toArray(
    ".studio-process-track, .single-process-track"
  );
  timelineTracks.forEach((track) => {
    gsap.fromTo(
      track,
      { scaleX: 0 },
      {
        scaleX: 1,
        duration: 0.72,
        ease: "power1.out",
        transformOrigin: "left center",
        scrollTrigger: {
          trigger: track.parentElement,
          start: "top 84%",
          once: true,
        },
      }
    );
  });

  window.addEventListener("load", () => ScrollTrigger.refresh(), { once: true });
})();
