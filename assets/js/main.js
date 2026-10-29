// Subtle, one-time content reveals; no parallax, looping motion, or logo animation.
(() => {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const sections = document.querySelectorAll(
    "main > section:not(.home-hero):not(.inner-hero):not(.reusable-contact-section)"
  );

  if (reduceMotion || !("IntersectionObserver" in window) || !sections.length) return;

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
