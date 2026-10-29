// Site-wide GSAP entrances and one-time scroll reveals (no loader or parallax).
(() => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  const { gsap, ScrollTrigger } = window;
  const timelineTracks = Array.from(
    document.querySelectorAll(".studio-process-track, .single-process-track")
  );

  const alignTimelineTracks = () => {
    timelineTracks.forEach((track) => {
      const row = track.parentElement;
      const columns = Array.from(row.children).filter((child) =>
        Array.from(child.classList).some((className) => className.startsWith("col-"))
      );
      if (columns.length < 2) return;

      const rowRect = row.getBoundingClientRect();
      const firstRect = columns[0].getBoundingClientRect();
      const lastRect = columns[columns.length - 1].getBoundingClientRect();
      const firstCenter = firstRect.left + firstRect.width / 2;
      const lastCenter = lastRect.left + lastRect.width / 2;
      const left = firstCenter - rowRect.left;
      const width = Math.max(0, lastCenter - firstCenter);
      track.style.left = `${left}px`;
      track.style.right = "auto";
      track.style.width = `${width}px`;
    });
  };

  alignTimelineTracks();
  window.addEventListener("resize", () => requestAnimationFrame(alignTimelineTracks), {
    passive: true,
  });
  window.addEventListener("load", alignTimelineTracks, { once: true });

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
    // Keep hero copy visible while it moves; opacity fades here caused a
    // flash of hidden content before the bottom-of-page script initialized.
    gsap.from(heroItems, {
      y: 14,
      duration: 0.5,
      delay: 0,
      stagger: 0.06,
      ease: "power2.out",
      clearProps: "transform",
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
      y: sectionColumns.length ? 20 : 24,
      scale: 0.99,
      duration: 0.6,
      stagger: sectionColumns.length > 1 ? 0.07 : 0,
      ease: "power2.out",
      clearProps: "opacity,visibility,transform",
      scrollTrigger: {
        trigger: section,
        start: "top 88%",
        once: true,
      },
    });
  });

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
