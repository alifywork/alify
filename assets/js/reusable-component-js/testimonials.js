// ==========================================================================
// Reusable Testimonials Component Script
// 1. User-Controlled 2-Row Slider (Prev/Next + Drag)
// 2. Dedicated Testimonials Page Category Filter
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. User-controlled 2-Row Testimonials Slider (.js-testimonials-slider)
  // ------------------------------------------------------------------------
  document.querySelectorAll(".js-testimonials-slider").forEach((section) => {
    const stage = section.querySelector(".testimonials-dual-stage");
    const row1Track = section.querySelector(
      '.testimonials-row-viewport[data-row="1"] .testimonials-track'
    );
    const row2Track = section.querySelector(
      '.testimonials-row-viewport[data-row="2"] .testimonials-track'
    );
    const prevBtn = section.querySelector(".js-testi-prev");
    const nextBtn = section.querySelector(".js-testi-next");
    if (!stage || !row1Track || !row2Track) return;

    const cloneTrackItems = (track) => {
      const originals = Array.from(track.children);
      originals.forEach((item) => {
        const clone = item.cloneNode(true);
        clone.setAttribute("aria-hidden", "true");
        clone.dataset.clone = "true";
        clone.querySelectorAll("a, button, input").forEach((el) => {
          el.setAttribute("tabindex", "-1");
        });
        track.appendChild(clone);
      });
      return originals[0];
    };

    const row1FirstOrig = cloneTrackItems(row1Track);
    const row2FirstOrig = cloneTrackItems(row2Track);
    const row1FirstClone = row1Track.querySelector('[data-clone="true"]');
    const row2FirstClone = row2Track.querySelector('[data-clone="true"]');
    let period1 = 2400;
    let period2 = 2400;
    let stepSize = 428;

    const measure = () => {
      if (row1FirstOrig && row1FirstClone) {
        const period = row1FirstClone.offsetLeft - row1FirstOrig.offsetLeft;
        if (period > 0) period1 = period;
        stepSize = row1FirstOrig.offsetWidth + 22;
      }
      if (row2FirstOrig && row2FirstClone) {
        const period = row2FirstClone.offsetLeft - row2FirstOrig.offsetLeft;
        if (period > 0) period2 = period;
      }
    };

    measure();
    window.addEventListener("resize", measure, { passive: true });
    if (document.fonts?.ready) document.fonts.ready.then(measure);

    const wrapOffset = (value, period) => {
      if (period <= 0) return 0;
      const mod = ((value % period) + period) % period;
      return mod === 0 ? 0 : mod - period;
    };

    let pos1 = 0;
    let pos2 = -Math.round(stepSize * 0.55);
    let isDragging = false;
    let dragLastX = 0;
    let manualTween = null;
    let marqueeTweens = [];
    let marqueeRunning = false;
    let autoplayEnabled = !prefersReducedMotion;
    let isInView = false;
    let isHovering = false;
    let hasFocus = false;
    let autoplayButton = null;

    const render = () => {
      const x1 = wrapOffset(pos1, period1);
      const x2 = wrapOffset(pos2, period2);
      if (typeof gsap !== "undefined") {
        gsap.set(row1Track, { x: x1 });
        gsap.set(row2Track, { x: x2 });
      } else {
        row1Track.style.transform = `translate3d(${x1.toFixed(2)}px, 0, 0)`;
        row2Track.style.transform = `translate3d(${x2.toFixed(2)}px, 0, 0)`;
      }
    };

    const readTrackX = (track, fallback) => {
      const value = parseFloat(gsap.getProperty(track, "x"));
      return Number.isFinite(value) ? value : fallback;
    };

    const stopAutoplay = () => {
      if (marqueeRunning && typeof gsap !== "undefined") {
        pos1 = readTrackX(row1Track, pos1);
        pos2 = readTrackX(row2Track, pos2);
      }
      marqueeTweens.forEach((tween) => tween.kill());
      marqueeTweens = [];
      marqueeRunning = false;
    };

    const startAutoplay = () => {
      stopAutoplay();
      if (
        !autoplayEnabled ||
        !isInView ||
        document.hidden ||
        isDragging ||
        isHovering ||
        hasFocus ||
        prefersReducedMotion ||
        typeof gsap === "undefined"
      ) return;

      pos1 = readTrackX(row1Track, pos1);
      pos2 = readTrackX(row2Track, pos2);
      const duration1 = Math.max(34, period1 / 42);
      const duration2 = Math.max(34, period2 / 42);
      marqueeTweens = [
        gsap.to(row1Track, {
          x: pos1 - period1,
          duration: duration1,
          ease: "none",
          repeat: -1,
          overwrite: "auto",
        }),
        gsap.to(row2Track, {
          x: pos2 + period2,
          duration: duration2,
          ease: "none",
          repeat: -1,
          overwrite: "auto",
        }),
      ];
      marqueeRunning = true;
    };

    const moveBy = (distance) => {
      stopAutoplay();
      manualTween?.kill();
      if (prefersReducedMotion || typeof gsap === "undefined") {
        pos1 = wrapOffset(pos1 + distance, period1);
        pos2 = wrapOffset(pos2 + distance, period2);
        render();
        return;
      }

      const start1 = pos1;
      const start2 = pos2;
      const progress = { value: 0 };
      manualTween = gsap.to(progress, {
        value: 1,
        duration: 0.62,
        ease: "power3.out",
        onUpdate() {
          pos1 = start1 + distance * progress.value;
          pos2 = start2 + distance * progress.value;
          render();
        },
        onComplete() {
          pos1 = wrapOffset(pos1, period1);
          pos2 = wrapOffset(pos2, period2);
          manualTween = null;
          render();
          startAutoplay();
        },
      });
    };

    if (!prefersReducedMotion && (prevBtn || nextBtn)) {
      autoplayButton = document.createElement("button");
      autoplayButton.type = "button";
      autoplayButton.className = "testi-nav-btn js-testi-autoplay";
      autoplayButton.setAttribute("aria-pressed", "true");
      autoplayButton.setAttribute("aria-label", "Pause automatic testimonials");
      autoplayButton.innerHTML = '<i class="fa-solid fa-pause" aria-hidden="true"></i>';
      (prevBtn || nextBtn).parentElement?.insertBefore(autoplayButton, prevBtn || nextBtn);
      autoplayButton.addEventListener("click", () => {
        autoplayEnabled = !autoplayEnabled;
        autoplayButton.setAttribute("aria-pressed", String(autoplayEnabled));
        autoplayButton.setAttribute(
          "aria-label",
          autoplayEnabled ? "Pause automatic testimonials" : "Play automatic testimonials"
        );
        autoplayButton.innerHTML = autoplayEnabled
          ? '<i class="fa-solid fa-pause" aria-hidden="true"></i>'
          : '<i class="fa-solid fa-play" aria-hidden="true"></i>';
        if (autoplayEnabled) startAutoplay();
        else stopAutoplay();
      });
    }

    render();
    prevBtn?.addEventListener("click", () => moveBy(stepSize));
    nextBtn?.addEventListener("click", () => moveBy(-stepSize));

    const interactionTargets = [
      stage,
      section.querySelector(".testimonials-slider-controls"),
    ].filter(Boolean);
    interactionTargets.forEach((target) => {
      target.addEventListener("mouseenter", () => {
        isHovering = true;
        stopAutoplay();
      });
      target.addEventListener("mouseleave", () => {
        isHovering = false;
        startAutoplay();
      });
      target.addEventListener("focusin", () => {
        hasFocus = true;
        stopAutoplay();
      });
      target.addEventListener("focusout", (event) => {
        if (!target.contains(event.relatedTarget)) {
          hasFocus = false;
          startAutoplay();
        }
      });
    });
    document.addEventListener("visibilitychange", startAutoplay);

    if ("IntersectionObserver" in window) {
      const autoplayObserver = new IntersectionObserver(([entry]) => {
        isInView = entry.isIntersecting && entry.intersectionRatio >= 0.25;
        if (isInView) startAutoplay();
        else stopAutoplay();
      }, { threshold: [0, 0.25] });
      autoplayObserver.observe(section);
    } else {
      isInView = true;
      startAutoplay();
    }

    stage.addEventListener("pointerdown", (event) => {
      if (event.button !== 0 && event.pointerType === "mouse") return;
      stopAutoplay();
      isDragging = true;
      dragLastX = event.clientX;
      manualTween?.kill();
      manualTween = null;
      stage.classList.add("is-dragging");
      stage.setPointerCapture?.(event.pointerId);
    });

    stage.addEventListener("pointermove", (event) => {
      if (!isDragging) return;
      const delta = event.clientX - dragLastX;
      dragLastX = event.clientX;
      pos1 += delta;
      pos2 += delta;
      render();
    });

    const stopDrag = () => {
      if (!isDragging) return;
      isDragging = false;
      stage.classList.remove("is-dragging");
      startAutoplay();
    };
    stage.addEventListener("pointerup", stopDrag);
    stage.addEventListener("pointercancel", stopDrag);
  });

  // ------------------------------------------------------------------------
  // 2. Dedicated Testimonials Page Category Filter (#testimonialsGrid)
  // ------------------------------------------------------------------------
  const grid = document.getElementById("testimonialsGrid");
  const filterBtns = document.querySelectorAll(".js-testi-filter");
  const countLabel = document.getElementById("testimonialsCountLabel");

  if (grid && filterBtns.length > 0) {
    const cards = Array.from(grid.querySelectorAll(".testimonial-grid-col"));

    filterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const selected = btn.dataset.filter || "all";

        filterBtns.forEach((b) => {
          b.classList.toggle("is-active", b === btn);
          b.setAttribute("aria-selected", b === btn ? "true" : "false");
        });

        let visibleCount = 0;
        const newlyVisible = [];

        cards.forEach((col) => {
          const cat = col.dataset.category || "";
          const match = selected === "all" || cat === selected;
          col.classList.toggle("is-hidden", !match);
          if (match) {
            visibleCount += 1;
            newlyVisible.push(col);
          }
        });

        if (countLabel) {
          countLabel.textContent = `Showing ${visibleCount} verified client review${
            visibleCount === 1 ? "" : "s"
          }`;
        }

        if (typeof gsap !== "undefined" && !prefersReducedMotion && newlyVisible.length) {
          gsap.fromTo(
            newlyVisible,
            { y: 18, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.38,
              stagger: 0.04,
              ease: "power2.out",
              clearProps: "transform,opacity",
            }
          );
        }
      });
    });
  }
});
