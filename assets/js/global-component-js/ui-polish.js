// ==========================================================================
// Global UI Micro-Polish Controller:
// 1. Fast △LIFY Studio Intro Preloader (First-Visit Counter + Quick Internal Nav)
// 2. Top Viewport Scroll Reading Progress Bar
// 3. 60fps Custom Interactive Desktop Cursor (Fine-Pointer Devices Only)
// ==========================================================================

(function () {
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // --------------------------------------------------------------------------
  // 1. Inject & Drive Fast △LIFY Intro Preloader + Scroll Progress Bar
  // --------------------------------------------------------------------------
  const mountGlobalPolishChrome = () => {
    if (!document.body) return;

    // Top Scroll Reading Progress Bar
    let progressBar = document.querySelector(".alify-scroll-progress");
    if (!progressBar) {
      progressBar = document.createElement("div");
      progressBar.className = "alify-scroll-progress";
      progressBar.setAttribute("aria-hidden", "true");
      document.body.prepend(progressBar);
    }

    const updateScrollProgress = () => {
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const ratio = docHeight > 0 ? Math.min(Math.max(window.scrollY / docHeight, 0), 1) : 0;
      progressBar.style.transform = `scaleX(${ratio.toFixed(4)})`;
    };

    let progressTicking = false;
    window.addEventListener(
      "scroll",
      () => {
        if (!progressTicking) {
          progressTicking = true;
          window.requestAnimationFrame(() => {
            progressTicking = false;
            updateScrollProgress();
          });
        }
      },
      { passive: true }
    );
    updateScrollProgress();

    // Fast △LIFY Intro Preloader (skip if reduced motion)
    if (!reducedMotion && !document.querySelector(".alify-preloader")) {
      let seenInSession = false;
      try {
        seenInSession = sessionStorage.getItem("alifyPreloaderSeen") === "1";
        sessionStorage.setItem("alifyPreloaderSeen", "1");
      } catch {
        seenInSession = false;
      }

      const preloader = document.createElement("div");
      preloader.className = seenInSession ? "alify-preloader is-quick" : "alify-preloader";
      preloader.setAttribute("aria-hidden", "true");
      preloader.innerHTML = `
        <div class="alify-preloader-inner">
          <div class="alify-preloader-brand">
            <span class="alify-preloader-mark">△</span>LIFY
          </div>
          <div class="alify-preloader-bar-wrap">
            <div class="alify-preloader-bar-fill" id="alifyPreloaderFill"></div>
          </div>
          <div class="alify-preloader-meta">
            <span>Digital Craft</span>
            <span id="alifyPreloaderPct">${seenInSession ? "100%" : "00%"}</span>
          </div>
        </div>
      `;
      document.body.prepend(preloader);

      const fillEl = preloader.querySelector("#alifyPreloaderFill");
      const pctEl = preloader.querySelector("#alifyPreloaderPct");

      const dismissPreloader = () => {
        preloader.classList.add("is-done");
        window.setTimeout(() => {
          preloader.remove();
        }, 700);
      };

      if (seenInSession) {
        if (fillEl) fillEl.style.transform = "scaleX(1)";
        window.requestAnimationFrame(() => {
          window.setTimeout(dismissPreloader, 140);
        });
      } else {
        const duration = 560;
        const start = performance.now();
        const step = (now) => {
          const t = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - t, 3);
          const pct = Math.round(eased * 100);
          if (fillEl) fillEl.style.transform = `scaleX(${Math.max(0.12, eased).toFixed(3)})`;
          if (pctEl) pctEl.textContent = `${String(pct).padStart(2, "0")}%`;
          if (t < 1) {
            window.requestAnimationFrame(step);
          } else {
            window.setTimeout(dismissPreloader, 90);
          }
        };
        window.requestAnimationFrame(step);
      }
    }
  };

  // --------------------------------------------------------------------------
  // 2. Custom Interactive Desktop Cursor (Fine-Pointer Devices Only)
  // --------------------------------------------------------------------------
  const mountCustomCursor = () => {
    if (reducedMotion) return;
    if (!window.matchMedia("(hover: hover) and (pointer: fine)").matches) return;
    if (document.querySelector(".alify-cursor-ring")) return;

    const ring = document.createElement("div");
    ring.className = "alify-cursor-ring";
    ring.setAttribute("aria-hidden", "true");
    ring.innerHTML = `<span class="alify-cursor-label">VIEW ↗</span>`;

    const dot = document.createElement("div");
    dot.className = "alify-cursor-dot";
    dot.setAttribute("aria-hidden", "true");

    document.body.appendChild(ring);
    document.body.appendChild(dot);

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let ringX = mouseX;
    let ringY = mouseY;
    let isVisible = false;
    let rafActive = false;

    const renderLoop = () => {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;

      ring.style.transform = `translate3d(${ringX.toFixed(2)}px, ${ringY.toFixed(2)}px, 0)`;
      dot.style.transform = `translate3d(${mouseX.toFixed(2)}px, ${mouseY.toFixed(2)}px, 0)`;

      if (Math.abs(mouseX - ringX) > 0.15 || Math.abs(mouseY - ringY) > 0.15) {
        window.requestAnimationFrame(renderLoop);
      } else {
        rafActive = false;
      }
    };

    const kickLoop = () => {
      if (!rafActive) {
        rafActive = true;
        window.requestAnimationFrame(renderLoop);
      }
    };

    const cardSelector =
      ".project-panel, .project-grid-card, .service-card-wrap, .industry-card, .cs-visual-frame, .blog-card-media";
    const linkSelector =
      "a, button, summary, label.alify-choice-pill, [role='button'], .faq-trigger, .cs-tab-btn";
    const inputSelector = "input, textarea, select";

    window.addEventListener(
      "mousemove",
      (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        if (!isVisible) {
          isVisible = true;
          ringX = mouseX;
          ringY = mouseY;
          ring.classList.add("is-visible");
          dot.classList.add("is-visible");
        }
        kickLoop();

        const target = e.target;
        if (!(target instanceof Element)) return;

        const onInput = Boolean(target.closest(inputSelector));
        const onCard = !onInput && Boolean(target.closest(cardSelector));
        const onLink = !onInput && !onCard && Boolean(target.closest(linkSelector));

        ring.classList.toggle("is-input", onInput);
        dot.classList.toggle("is-input", onInput);
        ring.classList.toggle("is-card", onCard);
        dot.classList.toggle("is-card", onCard);
        ring.classList.toggle("is-link", onLink);
        dot.classList.toggle("is-link", onLink);
      },
      { passive: true }
    );

    window.addEventListener(
      "mousedown",
      () => {
        ring.classList.add("is-pressed");
      },
      { passive: true }
    );

    window.addEventListener(
      "mouseup",
      () => {
        ring.classList.remove("is-pressed");
      },
      { passive: true }
    );

    document.addEventListener("mouseleave", () => {
      isVisible = false;
      ring.classList.remove("is-visible");
      dot.classList.remove("is-visible");
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      mountGlobalPolishChrome();
      mountCustomCursor();
    });
  } else {
    mountGlobalPolishChrome();
    mountCustomCursor();
  }
})();
