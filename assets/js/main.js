// ==========================================================================
// Main Home Page Script (GSAP Hero Choreography & Animated Metrics)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") return;

  if (typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);
  }

  const homeHero = document.querySelector(".home-hero");
  if (!homeHero) return;

  const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

  tl.from(".hero-status-pill", {
    y: 16,
    opacity: 0,
    duration: 0.45,
    clearProps: "transform,opacity",
  })
    .from(
      ".hero-display-title, .hero-lead",
      {
        y: 26,
        opacity: 0,
        duration: 0.65,
        stagger: 0.1,
        clearProps: "transform,opacity",
      },
      "-=0.25"
    )
    .from(
      ".hero-cta-group > *",
      {
        y: 18,
        opacity: 0,
        duration: 0.5,
        stagger: 0.08,
        clearProps: "transform,opacity",
      },
      "-=0.35"
    )
    .from(
      ".hero-stat-item",
      {
        y: 16,
        opacity: 0,
        duration: 0.45,
        stagger: 0.08,
        clearProps: "transform,opacity",
      },
      "-=0.3"
    )
    .from(
      ".hero-showcase-card",
      {
        y: 30,
        opacity: 0,
        duration: 0.7,
        clearProps: "transform,opacity",
      },
      "-=0.7"
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
      "-=0.35"
    )
    .from(
      ".hero-float-badge, .hero-float-review",
      {
        y: 18,
        opacity: 0,
        duration: 0.5,
        stagger: 0.1,
        clearProps: "transform,opacity",
      },
      "-=0.45"
    );

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
});
