// ==========================================================================
// Dedicated Book a Strategy Call Studio Controller (book-call.html)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const studioRoot = document.getElementById("bookCallStudio");
  if (!studioRoot) return;

  const datesWrap = document.getElementById("bookPageDates");
  const slotsWrap = document.getElementById("bookPageSlots");
  const tracksWrap = document.getElementById("bookPageTracks");
  const channelsWrap = document.getElementById("bookPageChannels");
  const tzSelect = document.getElementById("bookPageTz");
  const form = document.getElementById("bookPageForm");

  const sumTrack = document.getElementById("bookSumTrack");
  const sumDate = document.getElementById("bookSumDate");
  const sumTime = document.getElementById("bookSumTime");
  const sumChannel = document.getElementById("bookSumChannel");

  // Build 8 upcoming Mon-Sat business dates starting tomorrow
  const dates = [];
  const cursor = new Date();
  const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  while (dates.length < 8) {
    cursor.setDate(cursor.getDate() + 1);
    if (cursor.getDay() === 0) continue;
    dates.push({
      wk: days[cursor.getDay()],
      md: `${cursor.getDate()} ${months[cursor.getMonth()]}`,
      full: `${days[cursor.getDay()]}, ${cursor.getDate()} ${months[cursor.getMonth()]} ${cursor.getFullYear()}`,
    });
  }

  let activeTrack = "New Web / SaaS Build";
  let activeDateIdx = 0;
  let activeSlot = "11:30 AM";
  let activeChannel = "Google Meet";

  if (datesWrap) {
    datesWrap.innerHTML = dates
      .map(
        (d, idx) => `
        <button type="button" class="book-date-tile${idx === 0 ? " is-active" : ""}" data-date-idx="${idx}">
          <span class="book-date-wk">${d.wk}</span>
          <span class="book-date-md">${d.md}</span>
        </button>`
      )
      .join("");
  }

  const updateSummary = () => {
    const tz = tzSelect ? tzSelect.value : "PKT (UTC+5)";
    if (sumTrack) sumTrack.textContent = activeTrack;
    if (sumDate) sumDate.textContent = dates[activeDateIdx].full;
    if (sumTime) sumTime.textContent = `${activeSlot} · ${tz}`;
    if (sumChannel) sumChannel.textContent = activeChannel;
  };
  updateSummary();

  tracksWrap?.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-track]");
    if (!btn) return;
    activeTrack = btn.getAttribute("data-track") || activeTrack;
    tracksWrap.querySelectorAll(".book-track-btn").forEach((b) => b.classList.toggle("is-active", b === btn));
    updateSummary();
  });

  datesWrap?.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-date-idx]");
    if (!btn) return;
    activeDateIdx = Number(btn.getAttribute("data-date-idx")) || 0;
    datesWrap.querySelectorAll(".book-date-tile").forEach((b, idx) => b.classList.toggle("is-active", idx === activeDateIdx));
    updateSummary();
  });

  slotsWrap?.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-slot]");
    if (!btn) return;
    activeSlot = btn.getAttribute("data-slot") || activeSlot;
    slotsWrap.querySelectorAll(".book-slot-pill").forEach((b) => b.classList.toggle("is-active", b === btn));
    updateSummary();
  });

  channelsWrap?.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-channel]");
    if (!btn) return;
    activeChannel = btn.getAttribute("data-channel") || activeChannel;
    channelsWrap.querySelectorAll(".book-channel-pill").forEach((b) => b.classList.toggle("is-active", b === btn));
    updateSummary();
  });

  tzSelect?.addEventListener("change", updateSummary);

  form?.querySelectorAll(".alify-input, .alify-textarea").forEach((inp) => {
    inp.addEventListener("input", () => {
      inp.closest(".alify-field")?.classList.remove("has-error");
    });
  });

  form?.addEventListener("submit", (e) => {
    e.preventDefault();
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let ok = true;
    let firstErr = null;

    form.querySelectorAll("[data-required='true']").forEach((ctrl) => {
      const val = ctrl.value.trim();
      let valid = val.length > 0;
      if (valid && ctrl.type === "email") valid = emailRe.test(val);
      const field = ctrl.closest(".alify-field");
      if (!valid) {
        ok = false;
        field?.classList.add("has-error");
        if (!firstErr) firstErr = ctrl;
      } else {
        field?.classList.remove("has-error");
      }
    });

    if (!ok) {
      firstErr?.focus();
      return;
    }

    const nameVal = form.querySelector("#bookVisitorName")?.value.trim() || "";
    const emailVal = form.querySelector("#bookVisitorEmail")?.value.trim() || "";
    const tz = tzSelect ? tzSelect.value : "PKT (UTC+5)";
    const slotSummary = `${dates[activeDateIdx].md}, ${activeSlot} (${tz})`;
    const refCode = `CALL-${Math.floor(1000 + Math.random() * 9000)}`;

    try {
      sessionStorage.setItem(
        "alifyLeadSubmission",
        JSON.stringify({
          name: nameVal,
          email: emailVal,
          capability: `${activeTrack} · ${activeChannel}`,
          bookingSlot: slotSummary,
          isStrategyCall: true,
          ref: refCode,
        })
      );
    } catch {
      // Ignore storage restrictions
    }

    const fb = form.querySelector(".alify-form-feedback");
    fb?.classList.add("is-visible");

    const phVal = form.querySelector("#bookVisitorWhatsapp")?.value.trim() || "";
    const noteVal = form.querySelector("#bookVisitorAgenda")?.value.trim() || "";
    const sendPromise = window.alifyPostLeadToWP ? window.alifyPostLeadToWP({ name: nameVal, email: emailVal, phone: phVal, capability: `${activeTrack} · ${activeChannel}`, bookingSlot: slotSummary, message: noteVal, ref: refCode }) : Promise.resolve();
    sendPromise.finally(() => {
      setTimeout(() => {
        window.location.href = (window.ALIFY_WP && window.ALIFY_WP.thankYouUrl) || "thank-you.html";
      }, 300);
    });
  });
});
