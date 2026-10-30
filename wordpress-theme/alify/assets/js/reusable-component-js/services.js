// ==========================================================================
// Reusable Services Component Script
// 1. Home Page Container-Fluid Services Slider (#servicesSliderTrack)
// 2. Services Page Discipline Filter (#servicesGrid)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  // ------------------------------------------------------------------------
  // 1. Home Page Responsive Container-Fluid Services Slider
  // ------------------------------------------------------------------------
  const viewport = document.getElementById("servicesSliderViewport");
  const track = document.getElementById("servicesSliderTrack");
  const prevBtn = document.getElementById("servicesPrevBtn");
  const nextBtn = document.getElementById("servicesNextBtn");
  const dotsWrap = document.getElementById("servicesSliderDots");
  const progressBar = document.getElementById("servicesSliderProgressBar");

  if (viewport && track) {
    const slides = Array.from(track.querySelectorAll(".service-slide"));
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
        dot.className = `services-slider-dot${
          i === currentIndex ? " is-active" : ""
        }`;
        dot.setAttribute("aria-label", `Go to services slide ${i + 1}`);
        dot.addEventListener("click", () => goToSlide(i));
        dotsWrap.appendChild(dot);
      }
    };

    const setTrackX = (x, animate = false) => {
      if (window.gsap && !prefersReducedMotion) {
        if (animate) {
          window.gsap.to(track, {
            x,
            duration: 0.62,
            ease: "power3.out",
            overwrite: "auto",
          });
        } else {
          window.gsap.set(track, { x });
        }
      } else {
        track.style.transform = `translate3d(${x.toFixed(2)}px, 0, 0)`;
      }
    };

    const updateUI = (animate = false) => {
      if (!slides.length) return;
      const firstSlide = slides[0];
      const gap = parseFloat(getComputedStyle(track).gap) || 24;
      const stepWidth = firstSlide.getBoundingClientRect().width + gap;
      const offset = currentIndex * stepWidth;

      setTrackX(-offset, animate);

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
      updateUI(true);
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
      window.gsap?.killTweensOf(track);
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
      setTrackX(-rawOffset);
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
  // 2. Dedicated Services Page Grid Filter (#servicesGrid)
  // ------------------------------------------------------------------------
  const grid = document.getElementById("servicesGrid");
  const filterBtns = document.querySelectorAll(".js-service-filter");
  const countLabel = document.getElementById("servicesCountLabel");

  if (grid && filterBtns.length > 0) {
    const cols = Array.from(grid.querySelectorAll(".service-grid-col"));

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
          countLabel.textContent = `Showing ${visibleCount} core studio capabilit${
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
