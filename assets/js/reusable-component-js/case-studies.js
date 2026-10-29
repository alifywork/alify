// ==========================================================================
// Reusable Case Studies Component Script
// 1. Home Page Interactive Executive Case Study Switcher (manual)
// 2. Case Studies Page Filter & Technical Blueprint Drawer Toggles
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. Home Page Interactive Case Study Command Stage (.js-home-case-studies)
  // ------------------------------------------------------------------------
  const homeSection = document.querySelector(".js-home-case-studies");

  if (homeSection) {
    const tabs = Array.from(homeSection.querySelectorAll(".js-cs-tab"));
    const panels = Array.from(homeSection.querySelectorAll(".js-cs-panel"));
    const fills = tabs.map((t) => t.querySelector(".cs-tab-progress-fill"));

    let activeIdx = 0;

    const resetFills = () => {
      fills.forEach((f, idx) => {
        if (!f) return;
        f.style.width = idx === activeIdx ? "100%" : "0%";
      });
    };

    const switchCase = (nextIdx, animate = true) => {
      if (nextIdx < 0 || nextIdx >= tabs.length) return;
      activeIdx = nextIdx;

      tabs.forEach((tab, idx) => {
        const isActive = idx === activeIdx;
        tab.classList.toggle("is-active", isActive);
        tab.setAttribute("aria-selected", isActive ? "true" : "false");
        tab.setAttribute("tabindex", isActive ? "0" : "-1");
      });

      panels.forEach((panel, idx) => {
        const isActive = idx === activeIdx;
        panel.classList.toggle("is-active", isActive);
        panel.setAttribute("aria-hidden", isActive ? "false" : "true");

        if (
          isActive &&
          animate &&
          typeof gsap !== "undefined" &&
          !prefersReducedMotion
        ) {
          gsap.fromTo(
            panel,
            { y: 14, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.42,
              ease: "power2.out",
              clearProps: "transform,opacity",
            }
          );
        }
      });

      resetFills();
    };

    tabs.forEach((tab, idx) => {
      tab.addEventListener("click", () => {
        switchCase(idx, true);
      });

      tab.addEventListener("keydown", (e) => {
        if (e.key === "ArrowRight") {
          e.preventDefault();
          const next = (idx + 1) % tabs.length;
          tabs[next].focus();
          switchCase(next, true);
        } else if (e.key === "ArrowLeft") {
          e.preventDefault();
          const prev = (idx - 1 + tabs.length) % tabs.length;
          tabs[prev].focus();
          switchCase(prev, true);
        }
      });
    });

    switchCase(activeIdx, false);
  }

  // ------------------------------------------------------------------------
  // 2. Dedicated Case Studies Page Filter & Blueprint Drawers
  // ------------------------------------------------------------------------
  const stack = document.getElementById("caseStudiesStack");
  const filterBtns = document.querySelectorAll(".js-cs-filter");
  const countLabel = document.getElementById("csCountLabel");

  if (stack && filterBtns.length > 0) {
    const cards = Array.from(stack.querySelectorAll(".cs-editorial-card"));

    filterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const selected = btn.dataset.filter || "all";

        filterBtns.forEach((b) => {
          b.classList.toggle("is-active", b === btn);
          b.setAttribute("aria-selected", b === btn ? "true" : "false");
        });

        let visibleCount = 0;
        const newlyVisible = [];

        cards.forEach((card, idx) => {
          const cat = card.dataset.category || "";
          const match = selected === "all" || cat === selected;
          card.classList.toggle("is-hidden", !match);
          if (match) {
            // Re-stripe alternating left/right order cleanly among visible cards
            card.classList.toggle("is-reversed", visibleCount % 2 === 1);
            visibleCount += 1;
            newlyVisible.push(card);
          }
        });

        if (countLabel) {
          countLabel.textContent = `Showing ${visibleCount} verified executive case stud${
            visibleCount === 1 ? "y" : "ies"
          }`;
        }

        if (
          typeof gsap !== "undefined" &&
          !prefersReducedMotion &&
          newlyVisible.length
        ) {
          gsap.fromTo(
            newlyVisible,
            { y: 20, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.4,
              stagger: 0.06,
              ease: "power2.out",
              clearProps: "transform,opacity",
            }
          );
        }
      });
    });
  }

  // Blueprint Technical Stack Toggle inside Editorial Cards
  document.querySelectorAll(".js-cs-blueprint-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("aria-controls");
      const panel = targetId ? document.getElementById(targetId) : null;
      if (!panel) return;
      const isOpen = panel.classList.toggle("is-open");
      btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  });
});
