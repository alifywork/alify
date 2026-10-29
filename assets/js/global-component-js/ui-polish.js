// Global strategy call scheduler and quick-contact dock (no animation chrome).

(function () {
  // --------------------------------------------------------------------------
  // 3. Global Strategy Call Slot-Picker Modal + Quick WhatsApp / Call Dock
  // --------------------------------------------------------------------------
  const mountStrategyCallModal = () => {
    if (!document.body || document.getElementById("alifyBookCallModal")) return;

    const isBookCallPage = /book-call\.html/i.test(window.location.pathname);

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

    const timeSlots = ["10:00 AM", "11:30 AM", "02:00 PM", "03:30 PM", "05:00 PM", "06:30 PM"];
    const channels = [
      { id: "Google Meet", icon: "fa-solid fa-video", label: "Google Meet" },
      { id: "Zoom Video", icon: "fa-solid fa-desktop", label: "Zoom Video" },
      { id: "WhatsApp Call", icon: "fa-brands fa-whatsapp", label: "WhatsApp Call" },
    ];

    // Floating Quick Action Dock (WhatsApp + Book Strategy Call)
    if (!document.querySelector(".alify-quick-dock")) {
      const dock = document.createElement("div");
      dock.className = "alify-quick-dock";
      dock.innerHTML = `
        <a
          href="https://wa.me/923019328094"
          target="_blank"
          rel="noopener noreferrer"
          class="alify-dock-wa"
          aria-label="Chat on WhatsApp +92 301 9328094"
          title="WhatsApp: +92 301 9328094"
        >
          <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        </a>
        ${
          isBookCallPage
            ? ""
            : `<button type="button" class="alify-dock-call-btn" data-open-book-call>
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                <span>Book a Call</span>
              </button>`
        }
      `;
      document.body.appendChild(dock);
    }

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
                  30-Min Executive Discovery
                </span>
                <h2 class="alify-book-aside-title" id="alifyBookModalTitle">
                  Book a Strategy Call With ALIFY.
                </h2>
                <p class="alify-book-aside-desc">
                  Speak directly with a Senior Product Architect &amp; Managing Partner — zero junior sales scripts, concrete roadmap &amp; fixed sprint clarity.
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
                <a href="https://wa.me/923019328094" target="_blank" rel="noopener noreferrer" class="alify-book-direct-link">
                  <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                  <span>+92 301 9328094</span>
                </a>
                <a href="book-call.html" class="alify-book-fullpage-link">
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
      const refNum = `CALL-${Math.floor(1000 + Math.random() * 9000)}`;

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

      closeModal();
      window.location.href = "thank-you.html";
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      mountStrategyCallModal();
    });
  } else {
    mountStrategyCallModal();
  }
})();
