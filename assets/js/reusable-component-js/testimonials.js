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

    const render = () => {
      row1Track.style.transform = `translate3d(${wrapOffset(pos1, period1).toFixed(2)}px, 0, 0)`;
      row2Track.style.transform = `translate3d(${wrapOffset(pos2, period2).toFixed(2)}px, 0, 0)`;
    };

    const moveBy = (distance) => {
      if (prefersReducedMotion || typeof gsap === "undefined") {
        pos1 = wrapOffset(pos1 + distance, period1);
        pos2 = wrapOffset(pos2 + distance, period2);
        render();
        return;
      }

      manualTween?.kill();
      const start1 = pos1;
      const start2 = pos2;
      const progress = { value: 0 };
      manualTween = gsap.to(progress, {
        value: 1,
        duration: 0.42,
        ease: "power2.out",
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
        },
      });
    };

    render();
    prevBtn?.addEventListener("click", () => moveBy(stepSize));
    nextBtn?.addEventListener("click", () => moveBy(-stepSize));

    stage.addEventListener("pointerdown", (event) => {
      if (event.button !== 0 && event.pointerType === "mouse") return;
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
