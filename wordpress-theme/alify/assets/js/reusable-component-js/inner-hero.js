// ==========================================================================
// Reusable Inner Page Hero & Breadcrumbs Left/Right Entrance Script
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  const innerHero = document.querySelector(".inner-hero");
  if (!innerHero) return;

  const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

  tl.from(".breadcrumb-list", {
    x: -36,
    opacity: 0,
    duration: 0.5,
    clearProps: "transform,opacity",
  })
    .from(
      ".inner-hero-eyebrow, .inner-hero-title, .inner-hero-desc",
      {
        x: -48,
        opacity: 0,
        duration: 0.65,
        stagger: 0.09,
        clearProps: "transform,opacity",
      },
      "-=0.3"
    )
    .from(
      ".inner-hero-tag",
      {
        x: -24,
        opacity: 0,
        duration: 0.45,
        stagger: 0.05,
        clearProps: "transform,opacity",
      },
      "-=0.35"
    );

  const rightCards = innerHero.querySelectorAll(".inner-hero-card, .hero-lead-form-card");
  if (rightCards.length) {
    tl.from(
      rightCards,
      {
        x: 56,
        opacity: 0,
        duration: 0.75,
        clearProps: "transform,opacity",
      },
      "-=0.55"
    );
  }
});
