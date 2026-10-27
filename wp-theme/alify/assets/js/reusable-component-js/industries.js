// ==========================================================================
// Reusable Industries Component Script
// 1. Home Page Container-Fluid Industries Slider (#industriesSliderTrack)
// 2. Industries Page Sector Filter (#industriesGrid)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. Home Page Responsive Container-Fluid Industries Slider
  // ------------------------------------------------------------------------
  const viewport = document.getElementById("industriesSliderViewport");
  const track = document.getElementById("industriesSliderTrack");
  const prevBtn = document.getElementById("industriesPrevBtn");
  const nextBtn = document.getElementById("industriesNextBtn");
  const dotsWrap = document.getElementById("industriesSliderDots");
  const progressBar = document.getElementById("industriesSliderProgressBar");

  if (viewport && track) {
    const slides = Array.from(track.querySelectorAll(".industry-slide"));
    let currentIndex = 0;
    let slidesPerView = 4;
    let maxIndex = 0;

    const getSlidesPerView = () => {
      const w = window.innerWidth;
      if (w >= 1400) return 4;
      if (w >= 992) return 3;
      if (w >= 680) return 2;
      return 1;
    };

    const buildDots = () => {
      if (!dotsWrap) return;
      dotsWrap.innerHTML = "";
      const totalPositions = maxIndex + 1;
      for (let i = 0; i < totalPositions; i++) {
        const dot = document.createElement("button");
        dot.type = "button";
        dot.className = `industries-slider-dot${
          i === currentIndex ? " is-active" : ""
        }`;
        dot.setAttribute("aria-label", `Go to industries slide ${i + 1}`);
        dot.addEventListener("click", () => goToSlide(i));
        dotsWrap.appendChild(dot);
      }
    };

    const updateUI = () => {
      if (!slides.length) return;
      const firstSlide = slides[0];
      const gap = parseFloat(getComputedStyle(track).gap) || 24;
      const stepWidth = firstSlide.getBoundingClientRect().width + gap;
      const offset = currentIndex * stepWidth;

      track.style.transform = `translate3d(${-offset.toFixed(2)}px, 0, 0)`;

      if (prevBtn) prevBtn.disabled = currentIndex <= 0;
      if (nextBtn) nextBtn.disabled = currentIndex >= maxIndex;

      if (dotsWrap) {
        Array.from(dotsWrap.children).forEach((dot, idx) => {
          dot.classList.toggle("is-active", idx === currentIndex);
        });
      }

      if (progressBar) {
        const pct =
          maxIndex === 0 ? 100 : ((currentIndex + 1) / (maxIndex + 1)) * 100;
        progressBar.style.width = `${pct}%`;
      }
    };

    const goToSlide = (index) => {
      currentIndex = Math.max(0, Math.min(index, maxIndex));
      updateUI();
    };

    const recalc = () => {
      slidesPerView = getSlidesPerView();
      maxIndex = Math.max(0, slides.length - slidesPerView);
      if (currentIndex > maxIndex) currentIndex = maxIndex;
      buildDots();
      updateUI();
    };

    prevBtn?.addEventListener("click", () => goToSlide(currentIndex - 1));
    nextBtn?.addEventListener("click", () => goToSlide(currentIndex + 1));

    // Pointer / Touch Swipe Scrubbing
    let startX = 0;
    let startOffsetPx = 0;
    let isPointerDown = false;

    viewport.addEventListener("pointerdown", (e) => {
      if (e.target.closest("a, button")) return;
      if (e.pointerType === "mouse" && e.button !== 0) return;
      isPointerDown = true;
      startX = e.clientX;
      const gap = parseFloat(getComputedStyle(track).gap) || 24;
      const stepWidth = slides[0].getBoundingClientRect().width + gap;
      startOffsetPx = currentIndex * stepWidth;
      viewport.classList.add("is-dragging");
      track.classList.add("is-dragging");
      viewport.setPointerCapture?.(e.pointerId);
    });

    viewport.addEventListener("pointermove", (e) => {
      if (!isPointerDown) return;
      const dx = e.clientX - startX;
      const rawOffset = startOffsetPx - dx;
      track.style.transform = `translate3d(${-rawOffset.toFixed(2)}px, 0, 0)`;
    });

    const endPointer = (e) => {
      if (!isPointerDown) return;
      isPointerDown = false;
      viewport.classList.remove("is-dragging");
      track.classList.remove("is-dragging");
      const dx = e.clientX - startX;
      if (dx < -48 && currentIndex < maxIndex) {
        goToSlide(currentIndex + 1);
      } else if (dx > 48 && currentIndex > 0) {
        goToSlide(currentIndex - 1);
      } else {
        updateUI();
      }
    };

    viewport.addEventListener("pointerup", endPointer);
    viewport.addEventListener("pointercancel", endPointer);

    // Keyboard Accessibility
    viewport.setAttribute("tabindex", "0");
    viewport.addEventListener("keydown", (e) => {
      if (e.key === "ArrowRight") {
        e.preventDefault();
        goToSlide(currentIndex + 1);
      } else if (e.key === "ArrowLeft") {
        e.preventDefault();
        goToSlide(currentIndex - 1);
      }
    });

    window.addEventListener("resize", recalc, { passive: true });
    recalc();
  }

  // ------------------------------------------------------------------------
  // 2. Dedicated Industries Page Grid Filter (#industriesGrid)
  // ------------------------------------------------------------------------
  const grid = document.getElementById("industriesGrid");
  const filterBtns = document.querySelectorAll(".js-industry-filter");
  const countLabel = document.getElementById("industriesCountLabel");

  if (grid && filterBtns.length > 0) {
    const cols = Array.from(grid.querySelectorAll(".industry-grid-col"));

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
          const sector = col.dataset.sector || "";
          const match = selected === "all" || sector === selected;
          col.classList.toggle("is-hidden", !match);
          if (match) {
            visibleCount += 1;
            newlyVisible.push(col);
          }
        });

        if (countLabel) {
          countLabel.textContent = `Showing ${visibleCount} specialized industry vertical${
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
            { y: 18, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.36,
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
