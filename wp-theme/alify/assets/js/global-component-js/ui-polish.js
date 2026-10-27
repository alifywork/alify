// ==========================================================================
// Global UI Micro-Polish Controller:
// 1. Fast △LIFY Studio Intro Preloader (First-Visit Counter + Quick Internal Nav)
// 2. Top Viewport Scroll Reading Progress Bar
// 3. 60fps Custom Interactive Desktop Cursor (Fine-Pointer Devices Only)
// ==========================================================================

(function () {

  window.alifyPostLeadToWP = function (payload) {
    const cfg = window.ALIFY_WP;
    if (!cfg || !cfg.ajaxUrl) return Promise.resolve(null);
    const fd = new FormData();
    fd.append("action", "alify_submit_lead");
    fd.append("nonce", cfg.nonce || "");
    if (!payload || !payload.sourceUrl) {
      fd.append("sourceUrl", window.location.pathname + window.location.search);
    }
    Object.keys(payload || {}).forEach((k) => fd.append(k, payload[k] || ""));
    return fetch(cfg.ajaxUrl, { method: "POST", body: fd, credentials: "same-origin" })
      .then((r) => r.json())
      .catch(() => null);
  };

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

  // --------------------------------------------------------------------------
  // 3. Global Strategy Call Slot-Picker Modal + Quick WhatsApp / Call Dock
  // --------------------------------------------------------------------------
  const mountStrategyCallModal = () => {
    if (!document.body || document.getElementById("alifyBookCallModal")) return;

    const isBookCallPage = /book-call/i.test(window.location.pathname);

    // Build 6 upcoming Mon-Sat business days starting tomorrow
    const upcomingDates = [];
    const cursorDate = new Date();
    const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    while (upcomingDates.length < 6) {
      cursorDate.setDate(cursorDate.getDate() + 1);
      if (cursorDate.getDay() === 0) continue; // skip Sunday
      upcomingDates.push({
        day: dayNames[cursorDate.getDay()],
        label: `${cursorDate.getDate()} ${monthNames[cursorDate.getMonth()]}`,
        full: `${dayNames[cursorDate.getDay()]}, ${cursorDate.getDate()} ${monthNames[cursorDate.getMonth()]} ${cursorDate.getFullYear()}`,
      });
    }

    const scCfg = (window.ALIFY_WP && window.ALIFY_WP.strategyCall) || {};
    const scDisabled = scCfg.mode === "disabled";
    const timeSlots = Array.isArray(scCfg.slots) && scCfg.slots.length
      ? scCfg.slots
      : ["10:00 AM", "11:30 AM", "02:00 PM", "03:30 PM", "05:00 PM", "06:30 PM"];
    const rawChannels = Array.isArray(scCfg.channels) && scCfg.channels.length
      ? scCfg.channels
      : ["Google Meet", "Zoom Video", "WhatsApp Call"];
    const channels = rawChannels.map((lbl) => ({
      id: lbl,
      icon: /zoom/i.test(lbl) ? "fa-solid fa-desktop" : /whatsapp/i.test(lbl) ? "fa-brands fa-whatsapp" : "fa-solid fa-video",
      label: lbl,
    }));
    const waUrl = (window.ALIFY_WP && window.ALIFY_WP.whatsappUrl) || "https://wa.me/903019328094";
    const waLabel = (window.ALIFY_WP && window.ALIFY_WP.whatsapp) || "+90 301 9328094";

    // Floating Quick Action Dock (WhatsApp + Book Strategy Call)
    if (!document.querySelector(".alify-quick-dock")) {
      const dock = document.createElement("div");
      dock.className = "alify-quick-dock";
      dock.innerHTML = `
        <a
          href="${waUrl}"
          target="_blank"
          rel="noopener noreferrer"
          class="alify-dock-wa"
          aria-label="Chat on WhatsApp ${waLabel}"
          title="WhatsApp: ${waLabel}"
        >
          <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        </a>
        ${
          isBookCallPage || scDisabled
            ? ""
            : `<button type="button" class="alify-dock-call-btn" data-open-book-call>
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                <span>Book a Call</span>
              </button>`
        }
      `;
      document.body.appendChild(dock);
    }

    if (scDisabled) return;

    const modal = document.createElement("div");
    modal.id = "alifyBookCallModal";
    modal.className = "alify-book-modal";
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.setAttribute("aria-hidden", "true");
    modal.setAttribute("aria-labelledby", "alifyBookModalTitle");

    modal.innerHTML = `
      <div class="alify-book-backdrop" data-close-book-call></div>
      <div class="alify-book-dialog">
        <button type="button" class="alify-book-close" data-close-book-call aria-label="Close strategy call scheduler">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <div class="row alify-book-grid">
          <div class="col-12 col-lg-5 alify-book-aside-col">
            <div class="alify-book-aside">
              <div>
                <span class="alify-book-badge">
                  <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                  ${scCfg.badge || "30-Min Executive Discovery"}
                </span>
                <h2 class="alify-book-aside-title" id="alifyBookModalTitle">
                  ${scCfg.title || "Book a Strategy Call With ALIFY."}
                </h2>
                <p class="alify-book-aside-desc">
                  ${scCfg.subtitle || "Speak directly with a Senior Product Architect &amp; Managing Partner — zero junior sales scripts, concrete roadmap &amp; fixed sprint clarity."}
                </p>

                <ul class="alify-book-agenda">
                  <li>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Architecture, UX &amp; growth bottleneck audit</span>
                  </li>
                  <li>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Recommended tech stack &amp; milestone sprint plan</span>
                  </li>
                  <li>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span>Fixed-scope investment range within 24 hours</span>
                  </li>
                </ul>

                <div class="alify-book-live-slot">
                  <span class="alify-book-live-lbl">Selected Meeting Window</span>
                  <span class="alify-book-live-val" id="alifyBookLiveSummary">
                    ${upcomingDates[0].full} · ${timeSlots[1]} (PKT UTC+5)
                  </span>
                </div>
              </div>

              <div class="alify-book-aside-foot">
                <a href="mailto:info@alify.site" class="alify-book-direct-link">
                  <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                  <span>info@alify.site</span>
                </a>
                <a href="https://wa.me/903019328094" target="_blank" rel="noopener noreferrer" class="alify-book-direct-link">
                  <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                  <span>+90 301 9328094</span>
                </a>
                <a href="${(window.ALIFY_WP && window.ALIFY_WP.bookCallUrl) || 'book-call.html'}" class="alify-book-fullpage-link">
                  <span>Open Full Booking Page</span>
                  <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-7 alify-book-main-col">
            <form class="alify-book-body" id="alifyBookModalForm" novalidate>
              <div class="alify-book-step-label">
                <span>01 · Choose a Date</span>
                <select class="alify-book-tz-select" id="alifyBookTzSelect" aria-label="Select Timezone">
                  <option value="PKT (UTC+5)">PKT (UTC+5)</option>
                  <option value="TRT (UTC+3)">TRT (UTC+3)</option>
                  <option value="GST (UTC+4)">GST (UTC+4)</option>
                  <option value="GMT / UK">GMT / UK</option>
                  <option value="EST (UTC-5)">EST (UTC-5)</option>
                </select>
              </div>

              <div class="alify-book-dates" id="alifyBookDates">
                ${upcomingDates
                  .map(
                    (d, i) => `
                  <button type="button" class="alify-book-date-btn${i === 0 ? " is-active" : ""}" data-date-idx="${i}">
                    <span class="alify-book-date-day">${d.day}</span>
                    <span class="alify-book-date-num">${d.label}</span>
                  </button>`
                  )
                  .join("")}
              </div>

              <div class="alify-book-step-label">
                <span>02 · Select Time Slot</span>
              </div>
              <div class="alify-book-slots" id="alifyBookSlots">
                ${timeSlots
                  .map(
                    (s, i) => `
                  <button type="button" class="alify-book-slot-btn${i === 1 ? " is-active" : ""}" data-slot="${s}">
                    ${s}
                  </button>`
                  )
                  .join("")}
              </div>

              <div class="alify-book-step-label">
                <span>03 · Meeting Channel &amp; Details</span>
              </div>
              <div class="alify-book-channels" id="alifyBookChannels">
                ${channels
                  .map(
                    (c, i) => `
                  <button type="button" class="alify-book-channel-btn${i === 0 ? " is-active" : ""}" data-channel="${c.id}">
                    <i class="${c.icon}" aria-hidden="true"></i>
                    <span>${c.label}</span>
                  </button>`
                  )
                  .join("")}
              </div>

              <div class="alify-book-fields">
                <input type="text" class="alify-book-input" id="alifyBookName" placeholder="Full Name *" aria-label="Full Name" required />
                <input type="email" class="alify-book-input" id="alifyBookEmail" placeholder="Work Email *" aria-label="Work Email" required />
              </div>

              <textarea class="alify-book-note" id="alifyBookNote" rows="2" placeholder="Optional: Share website URL or what you want to build..." aria-label="Project agenda notes"></textarea>

              <p class="alify-book-err" id="alifyBookError">Please enter your full name and a valid email address.</p>

              <button type="submit" class="alify-book-submit">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                <span>Confirm 30-Min Strategy Call</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    let selDateIdx = 0;
    let selSlot = timeSlots[1];
    let selChannel = channels[0].id;
    let lastFocus = null;

    const liveEl = modal.querySelector("#alifyBookLiveSummary");
    const tzSelect = modal.querySelector("#alifyBookTzSelect");
    const nameInp = modal.querySelector("#alifyBookName");
    const emailInp = modal.querySelector("#alifyBookEmail");
    const errEl = modal.querySelector("#alifyBookError");

    const syncSummary = () => {
      const tz = tzSelect ? tzSelect.value : "PKT (UTC+5)";
      if (liveEl) {
        liveEl.textContent = `${upcomingDates[selDateIdx].full} · ${selSlot} (${tz}) via ${selChannel}`;
      }
    };
    syncSummary();

    const openModal = (trigger) => {
      lastFocus = trigger || document.activeElement;
      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("is-bookcall-open");
      if (nameInp) nameInp.focus({ preventScroll: true });
    };

    const closeModal = () => {
      if (!modal.classList.contains("is-open")) return;
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("is-bookcall-open");
      if (lastFocus && typeof lastFocus.focus === "function") {
        lastFocus.focus({ preventScroll: true });
      }
    };

    document.addEventListener("click", (e) => {
      const openTrig = e.target.closest("[data-open-book-call], a[href='#book-call']");
      if (openTrig) {
        e.preventDefault();
        openModal(openTrig);
        return;
      }
      if (e.target.closest("[data-close-book-call]")) {
        e.preventDefault();
        closeModal();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("is-open")) {
        e.preventDefault();
        closeModal();
      }
    });

    modal.querySelector("#alifyBookDates")?.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-date-idx]");
      if (!btn) return;
      selDateIdx = Number(btn.getAttribute("data-date-idx")) || 0;
      modal.querySelectorAll(".alify-book-date-btn").forEach((b, idx) => b.classList.toggle("is-active", idx === selDateIdx));
      syncSummary();
    });

    modal.querySelector("#alifyBookSlots")?.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-slot]");
      if (!btn) return;
      selSlot = btn.getAttribute("data-slot") || timeSlots[0];
      modal.querySelectorAll(".alify-book-slot-btn").forEach((b) => b.classList.toggle("is-active", b === btn));
      syncSummary();
    });

    modal.querySelector("#alifyBookChannels")?.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-channel]");
      if (!btn) return;
      selChannel = btn.getAttribute("data-channel") || channels[0].id;
      modal.querySelectorAll(".alify-book-channel-btn").forEach((b) => b.classList.toggle("is-active", b === btn));
      syncSummary();
    });

    tzSelect?.addEventListener("change", syncSummary);

    [nameInp, emailInp].forEach((inp) => {
      inp?.addEventListener("input", () => {
        inp.classList.remove("has-error");
        errEl?.classList.remove("is-visible");
      });
    });

    modal.querySelector("#alifyBookModalForm")?.addEventListener("submit", (e) => {
      e.preventDefault();
      const nameVal = (nameInp?.value || "").trim();
      const emailVal = (emailInp?.value || "").trim();
      const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);

      if (!nameVal) nameInp?.classList.add("has-error");
      if (!emailOk) emailInp?.classList.add("has-error");

      if (!nameVal || !emailOk) {
        errEl?.classList.add("is-visible");
        (!nameVal ? nameInp : emailInp)?.focus();
        return;
      }

      const tz = tzSelect ? tzSelect.value : "PKT (UTC+5)";
      const slotSummary = `${upcomingDates[selDateIdx].label}, ${selSlot} (${tz})`;
      const callPrefix = (window.ALIFY_WP && window.ALIFY_WP.callRefPrefix) || "CALL-";
      const refNum = `${callPrefix}${Math.floor(1000 + Math.random() * 9000)}`;

      try {
        sessionStorage.setItem(
          "alifyLeadSubmission",
          JSON.stringify({
            name: nameVal,
            email: emailVal,
            capability: `30-Min Strategy Call · ${selChannel}`,
            bookingSlot: slotSummary,
            isStrategyCall: true,
            ref: refNum,
          })
        );
      } catch {
        // Ignore storage restrictions
      }

      window
        .alifyPostLeadToWP({
          formTemplate: "strategy_call",
          leadType: "strategy_call",
          name: nameVal,
          email: emailVal,
          capability: `30-Min Strategy Call · ${selChannel}`,
          bookingSlot: slotSummary,
          message: (modal.querySelector("#alifyBookNote")?.value || "").trim(),
          ref: refNum,
        })
        .then((res) => {
          closeModal();
          const behavior = (res && res.data && res.data.submissionBehavior) || (window.ALIFY_WP && window.ALIFY_WP.submissionBehavior) || "redirect";
          if (behavior === "inline") return;
          const targetUrl =
            (scCfg && scCfg.redirectUrl) ||
            (res && res.data && res.data.redirectUrl) ||
            (window.ALIFY_WP && window.ALIFY_WP.thankYouUrl) ||
            "thank-you.html";
          window.location.href = targetUrl;
        });
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      mountGlobalPolishChrome();
      mountCustomCursor();
      mountStrategyCallModal();
    });
  } else {
    mountGlobalPolishChrome();
    mountCustomCursor();
    mountStrategyCallModal();
  }
})();

// Sync Dynamic WordPress CPT Counts on Archive Filter Pills & Count Labels
document.addEventListener("DOMContentLoaded", () => {
  const archiveConfigs = [
    { btnSel: ".js-service-filter", itemSel: ".service-grid-col", attr: "data-category", countId: "servicesCountLabel", suffix: "core studio capabilities" },
    { btnSel: ".js-industry-filter", itemSel: ".industry-grid-col", attr: "data-sector", countId: "industriesCountLabel", suffix: "specialized industry verticals" },
    { btnSel: ".js-project-filter", itemSel: ".project-grid-col", attr: "data-category", countId: "projectsCountLabel", suffix: "featured client projects" },
    { btnSel: ".js-testi-filter", itemSel: ".testimonial-grid-col", attr: "data-category", countId: null, suffix: "" },
  ];

  archiveConfigs.forEach(({ btnSel, itemSel, attr, countId, suffix }) => {
    const items = Array.from(document.querySelectorAll(itemSel));
    if (!items.length) return;
    document.querySelectorAll(btnSel).forEach((btn) => {
      const f = btn.getAttribute("data-filter");
      const cnt = f === "all" ? items.length : items.filter((el) => el.getAttribute(attr) === f).length;
      btn.textContent = btn.textContent.trim().replace(/\(\d+\)$/, `(${cnt})`);
    });
    if (countId) {
      const lbl = document.getElementById(countId);
      if (lbl) lbl.textContent = `Showing ${items.length} ${suffix}`;
    }
  });

  // Sync FAQ Knowledge Hub Pillar Pill Counts
  const faqGroups = Array.from(document.querySelectorAll(".faq-hub-group"));
  if (faqGroups.length) {
    const totalFaq = document.querySelectorAll("#faqHubGroupsWrap .faq-item").length;
    document.querySelectorAll(".faq-hub-pill").forEach((pill) => {
      const cat = pill.getAttribute("data-filter-cat");
      const badge = pill.querySelector(".faq-hub-pill-count");
      if (!badge) return;
      if (cat === "all") {
        badge.textContent = String(totalFaq);
      } else {
        const grp = document.querySelector(`.faq-hub-group[data-faq-cat="${cat}"]`);
        badge.textContent = String(grp ? grp.querySelectorAll(".faq-item").length : 0);
      }
    });
  }
});
