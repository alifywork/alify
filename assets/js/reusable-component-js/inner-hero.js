// ==========================================================================
// Reusable Inner Page Hero & Breadcrumbs Entrance Script
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") return;

  const innerHero = document.querySelector(".inner-hero");
  if (!innerHero) return;

  const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

  tl.from(".breadcrumb-list", {
    y: 14,
    opacity: 0,
    duration: 0.45,
    clearProps: "transform,opacity",
  })
    .from(
      ".inner-hero-eyebrow, .inner-hero-title, .inner-hero-desc",
      {
        y: 22,
        opacity: 0,
        duration: 0.55,
        stagger: 0.08,
        clearProps: "transform,opacity",
      },
      "-=0.25"
    )
    .from(
      ".inner-hero-tag",
      {
        y: 14,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        clearProps: "transform,opacity",
      },
      "-=0.3"
    )
    .from(
      ".inner-hero-card, .hero-lead-form-card",
      {
        y: 24,
        opacity: 0,
        duration: 0.6,
        clearProps: "transform,opacity",
      },
      "-=0.45"
    );
});
