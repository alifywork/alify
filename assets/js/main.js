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
      opacity: 0.9,
      y: 12,
      duration: 0.48,
      stagger: 0.055,
      ease: "power2.out",
      clearProps: "opacity,transform",
    });
  }

  const sections = gsap.utils.toArray(
    "main > section:not(.home-hero):not(.inner-hero):not(.reusable-contact-section)"
  );
  sections.forEach((section, sectionIndex) => {
    const rows = Array.from(section.querySelectorAll(".row")).filter((row) => {
      if (row.closest("section") !== section) return false;
      let parent = row.parentElement;
      while (parent && parent !== section) {
        if (parent.classList.contains("row")) return false;
        parent = parent.parentElement;
      }
      return true;
    });

    const rowGroups = rows
      .map((row) => ({
        row,
        columns: Array.from(row.children).filter((child) =>
          Array.from(child.classList).some((className) => className.startsWith("col-"))
        ),
      }))
      .filter(({ columns }) => columns.length);

    if (!rowGroups.length) {
      const fromAbove = sectionIndex % 3 === 1;
      gsap.from(section, {
        autoAlpha: 0,
        y: fromAbove ? -20 : 20,
        duration: 0.56,
        ease: "power2.out",
        clearProps: "opacity,visibility,transform",
        scrollTrigger: {
          trigger: section,
          start: "top 88%",
          once: true,
        },
      });
      return;
    }

    rowGroups.forEach(({ row, columns }, groupIndex) => {
      const scrollTrigger = {
        trigger: row,
        start: "top 88%",
        once: true,
      };
      const clearProps = "opacity,visibility,transform";

      if (columns.length === 2) {
        gsap.from(columns, {
          autoAlpha: 0,
          x: (index) => (index === 0 ? -22 : 22),
          duration: 0.56,
          stagger: 0.04,
          ease: "power2.out",
          clearProps,
          scrollTrigger,
        });
      } else {
        const fromAbove = columns.length === 1 && (sectionIndex + groupIndex) % 3 === 1;
        gsap.from(columns, {
          autoAlpha: 0,
          y: fromAbove ? -18 : 18,
          duration: 0.54,
          stagger: columns.length > 1 ? 0.065 : 0,
          ease: "power2.out",
          clearProps,
          scrollTrigger,
        });
      }
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
