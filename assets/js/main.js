// Site-wide, one-time reveals and small hero entrances. No page loader, parallax, or logo motion.
(() => {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduceMotion) return;

  const heroItems = document.querySelectorAll(
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

  heroItems.forEach((item, index) => {
    item.classList.add("hero-motion-item");
    item.style.setProperty("--hero-motion-delay", `${Math.min(index, 5) * 70}ms`);
  });

  if (heroItems.length) {
    document.documentElement.classList.add("motion-enabled");
    requestAnimationFrame(() => {
      heroItems.forEach((item) => item.classList.add("is-visible"));
    });
  }

  const sections = document.querySelectorAll(
    "main > section:not(.home-hero):not(.inner-hero):not(.reusable-contact-section)"
  );
  if (!("IntersectionObserver" in window) || !sections.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.08, rootMargin: "0px 0px -32px 0px" }
  );

  sections.forEach((section) => {
    section.classList.add("motion-reveal-pending");
    observer.observe(section);
  });
})();
