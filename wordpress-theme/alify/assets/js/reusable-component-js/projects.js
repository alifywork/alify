// ==========================================================================
// Reusable Projects / Work Component Script
// 1. Home Page Hover & Tap-Expanding Project Panels (#projectsAccordionStage)
// 2. Dedicated Work Page 2-Column Projects Filter (#projectsGrid)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. Home Page Hover / Focus / Tap-Expanding Accordion Panels
  // ------------------------------------------------------------------------
  const stage = document.getElementById("projectsAccordionStage");
  const activeCounterEl = document.getElementById("projectsActiveCounterNum");

  if (stage) {
    const panels = Array.from(stage.querySelectorAll(".project-panel"));

    const activatePanel = (targetPanel) => {
      if (!targetPanel) return;
      const previousPanel = panels.find((panel) => panel.classList.contains("is-active"));
      if (previousPanel === targetPanel) return;
      const isDesktop = window.innerWidth >= 992;
      const collapsedHeight = window.innerWidth < 768 ? 82 : 92;
      const previousHeight = previousPanel?.getBoundingClientRect().height || collapsedHeight;

      if (typeof gsap !== "undefined" && !prefersReducedMotion && isDesktop) {
        panels.forEach((panel) => {
          gsap.set(panel, { flexGrow: panel === previousPanel ? 4.2 : 1 });
        });
      }

      panels.forEach((panel, idx) => {
        const active = panel === targetPanel;
        panel.classList.toggle("is-active", active);
        panel.setAttribute("aria-expanded", active ? "true" : "false");
        if (active && activeCounterEl) {
          activeCounterEl.textContent = String(idx + 1).padStart(2, "0");
        }
      });

      if (typeof gsap === "undefined" || prefersReducedMotion) return;

      if (isDesktop) {
        const collapsedPanels = panels.filter((panel) => panel !== targetPanel);
        gsap.to(collapsedPanels, {
          flexGrow: 1,
          duration: 0.42,
          ease: "power2.inOut",
          overwrite: "auto",
          onComplete: () => gsap.set(collapsedPanels, { clearProps: "flexGrow" }),
        });
        gsap.to(targetPanel, {
          flexGrow: 4.2,
          duration: 0.42,
          ease: "power2.inOut",
          overwrite: "auto",
          onComplete() { gsap.set(targetPanel, { clearProps: "flexGrow" }); },
        });
      } else {
        if (previousPanel) {
          gsap.fromTo(previousPanel, { height: previousHeight }, {
            height: collapsedHeight,
            duration: 0.38,
            ease: "power2.inOut",
            overwrite: "auto",
            onComplete: () => gsap.set(previousPanel, { clearProps: "height" }),
          });
        }
        const targetHeight = Math.max(targetPanel.scrollHeight, targetPanel.offsetHeight);
        gsap.fromTo(targetPanel, { height: collapsedHeight, minHeight: 0 }, {
          height: targetHeight,
          minHeight: targetHeight,
          duration: 0.42,
          ease: "power2.inOut",
          overwrite: "auto",
          onComplete: () => gsap.set(targetPanel, { clearProps: "height,minHeight" }),
        });
      }

      panels.forEach((panel) => {
        const background = panel.querySelector(".project-panel-bg");
        if (!background) return;
        const wasActive = panel === previousPanel;
        const isActive = panel === targetPanel;
        gsap.fromTo(background, {
          scale: wasActive ? 1.01 : 1.08,
          filter: wasActive ? "brightness(1)" : "brightness(0.76)",
        }, {
          scale: isActive ? 1.01 : 1.08,
          filter: isActive ? "brightness(1)" : "brightness(0.76)",
          duration: 0.46,
          ease: "power2.out",
          overwrite: "auto",
          clearProps: "transform,filter",
        });
      });

      const expandedContent = targetPanel.querySelector(".project-panel-expanded");
      if (expandedContent) {
        gsap.fromTo(expandedContent, { autoAlpha: 0, y: 14 }, {
          autoAlpha: 1,
          y: 0,
          delay: 0.08,
          duration: 0.34,
          ease: "power2.out",
          overwrite: "auto",
          clearProps: "opacity,visibility,transform",
        });
      }
    };

    panels.forEach((panel, idx) => {
      // Hover expansion on desktop (>= 992px)
      panel.addEventListener("mouseenter", () => {
        if (window.innerWidth >= 992) {
          activatePanel(panel);
        }
      });

      // Focus expansion for keyboard users
      panel.addEventListener("focusin", () => {
        activatePanel(panel);
      });

      // Click / Tap expansion on mobile, tablet & desktop
      panel.addEventListener("click", (e) => {
        if (e.target.closest("a")) return;
        activatePanel(panel);
      });

      // Left / Right / Up / Down keyboard arrow navigation between panels
      panel.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          if (!e.target.closest("a")) {
            e.preventDefault();
            activatePanel(panel);
          }
        } else if (e.key === "ArrowRight" || e.key === "ArrowDown") {
          e.preventDefault();
          const next = panels[(idx + 1) % panels.length];
          next.focus();
          activatePanel(next);
        } else if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
          e.preventDefault();
          const prev = panels[(idx - 1 + panels.length) % panels.length];
          prev.focus();
          activatePanel(prev);
        }
      });
    });
  }

  // ------------------------------------------------------------------------
  // 2. Dedicated Projects / Work Page 2-Column Grid Filter (#projectsGrid)
  // ------------------------------------------------------------------------
  const grid = document.getElementById("projectsGrid");
  const filterBtns = document.querySelectorAll(".js-project-filter");
  const countLabel = document.getElementById("projectsCountLabel");

  if (grid && filterBtns.length > 0) {
    const cols = Array.from(grid.querySelectorAll(".project-grid-col"));

    filterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const selected = btn.dataset.filter || "all";

        filterBtns.forEach((b) => {
          b.classList.toggle("is-active", b === btn);
          b.setAttribute("aria-selected", b === btn ? "true" : "false");
        });

        let visibleCount = 0;
        const newlyVisible = [];

        cols.forEach((col) => {
          const cat = col.dataset.category || "";
          const match = selected === "all" || cat === selected;
          col.classList.toggle("is-hidden", !match);
          if (match) {
            visibleCount += 1;
            newlyVisible.push(col);
          }
        });

        if (countLabel) {
          countLabel.textContent = `Showing ${visibleCount} featured client project${
            visibleCount === 1 ? "" : "s"
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
              duration: 0.38,
              stagger: 0.05,
              ease: "power2.out",
              clearProps: "transform,opacity",
            }
          );
        }
      });
    });
  }
});
