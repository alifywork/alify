// ==========================================================================
// Reusable Testimonials Component Script
// 1. Seamless 2-Row Opposite-Direction Slider (Auto-glide + Prev/Next + Drag)
// 2. Dedicated Testimonials Page Category Filter
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. Initialize 2-Row Testimonials Sliders (.js-testimonials-slider)
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

    // Clone original cards in each row for seamless infinite wraparound
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
        const p1 = row1FirstClone.offsetLeft - row1FirstOrig.offsetLeft;
        if (p1 > 0) period1 = p1;
        stepSize = row1FirstOrig.offsetWidth + 22;
      }
      if (row2FirstOrig && row2FirstClone) {
        const p2 = row2FirstClone.offsetLeft - row2FirstOrig.offsetLeft;
        if (p2 > 0) period2 = p2;
      }
    };

    measure();
    window.addEventListener("resize", measure, { passive: true });
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(measure);
    }

    // Wrap any signed coordinate smoothly into (-period, 0]
    const wrapOffset = (val, period) => {
      if (period <= 0) return 0;
      const mod = ((val % period) + period) % period;
      return mod === 0 ? 0 : mod - period;
    };

    // Row 1 starts at 0 and glides left (-); Row 2 starts staggered and glides right (+)
    let pos1 = 0;
    let pos2 = -Math.round(stepSize * 0.55);

    // Smooth manual step impulse when clicking Prev / Next buttons
    let manualVelocity = 0;
    let isUserPaused = prefersReducedMotion;
    let isHovered = false;
    let isDragging = false;
    let dragLastX = 0;

    const baseSpeedPxPerSec = 36; // Gentle, readable luxury glide speed
    let currentSpeedFactor = isUserPaused ? 0 : 1;

    const render = () => {
      const x1 = wrapOffset(pos1, period1);
      const x2 = wrapOffset(pos2, period2);
      row1Track.style.transform = `translate3d(${x1.toFixed(2)}px, 0, 0)`;
      row2Track.style.transform = `translate3d(${x2.toFixed(2)}px, 0, 0)`;
    };

    let lastTime = performance.now();
    const tick = (now) => {
      const dt = Math.min((now - lastTime) / 1000, 0.064);
      lastTime = now;

      const targetFactor =
        isUserPaused || isHovered || isDragging ? 0 : 1;
      currentSpeedFactor += (targetFactor - currentSpeedFactor) * Math.min(1, dt * 6);

      if (!isDragging) {
        const autoDelta = baseSpeedPxPerSec * currentSpeedFactor * dt;
        // Row 1 moves left, Row 2 moves right for dynamic counter-motion
        pos1 -= autoDelta;
        pos2 += autoDelta * 0.92;

        if (Math.abs(manualVelocity) > 0.4) {
          const stepDelta = manualVelocity * dt * 7.5;
          pos1 += stepDelta;
          pos2 += stepDelta;
          manualVelocity -= stepDelta;
        } else {
          manualVelocity = 0;
        }
      }

      render();
      requestAnimationFrame(tick);
    };

    render();
    requestAnimationFrame(tick);

    // Prev / Next Controls
    prevBtn?.addEventListener("click", () => {
      manualVelocity += stepSize;
    });

    nextBtn?.addEventListener("click", () => {
      manualVelocity -= stepSize;
    });

    // Hover & Focus Pause
    stage.addEventListener("mouseenter", () => {
      isHovered = true;
    });
    stage.addEventListener("mouseleave", () => {
      isHovered = false;
    });
    stage.addEventListener("focusin", () => {
      isHovered = true;
    });
    stage.addEventListener("focusout", () => {
      isHovered = false;
    });

    // Pointer / Touch Drag Scrubbing
    stage.addEventListener("pointerdown", (e) => {
      if (e.button !== 0 && e.pointerType === "mouse") return;
      isDragging = true;
      dragLastX = e.clientX;
      manualVelocity = 0;
      stage.classList.add("is-dragging");
      stage.setPointerCapture?.(e.pointerId);
    });

    stage.addEventListener("pointermove", (e) => {
      if (!isDragging) return;
      const dx = e.clientX - dragLastX;
      dragLastX = e.clientX;
      pos1 += dx;
      pos2 += dx;
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
