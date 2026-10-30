/**
 * ALIFY — Reusable Studio About, Process Counters & Legal TOC Controller
 * Sets KPI values immediately and provides legal TOC highlighting.
 */
document.addEventListener("DOMContentLoaded", () => {
  // KPI values are shown immediately; no counter animation is applied.
  document.querySelectorAll(".js-studio-counter").forEach((el) => {
    const prefix = el.dataset.prefix || "";
    const suffix = el.dataset.suffix || "";
    const target = el.dataset.target || "0";
    el.textContent = `${prefix}${target}${suffix}`;
  });

  // 2. Legal Document Sticky TOC Scroll-Spy (privacy-policy.html & terms-of-service.html)
  const legalLinks = Array.from(document.querySelectorAll(".legal-toc-link"));
  if (legalLinks.length) {
    const clauses = legalLinks
      .map((l) => {
        const id = l.getAttribute("href")?.slice(1);
        return id ? document.getElementById(id) : null;
      })
      .filter(Boolean);

    legalLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        const id = link.getAttribute("href")?.slice(1);
        const target = id ? document.getElementById(id) : null;
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: "auto", block: "start" });
        }
      });
    });

    const syncLegalToc = () => {
      const scrollY = window.scrollY + 150;
      let activeId = clauses[0]?.id;
      clauses.forEach((c) => {
        if (c.offsetTop <= scrollY) activeId = c.id;
      });
      legalLinks.forEach((l) => {
        l.classList.toggle("is-active", l.getAttribute("href") === `#${activeId}`);
      });
    };

    window.addEventListener("scroll", syncLegalToc, { passive: true });
    syncLegalToc();
  }

  // 3. Personalized Thank You Page Hydration (thank-you.html)
  const thankYouRoot = document.getElementById("thankYouRoot");
  if (thankYouRoot) {
    let lead = null;
    try {
      const raw = sessionStorage.getItem("alifyLeadSubmission");
      if (raw) lead = JSON.parse(raw);
    } catch {
      lead = null;
    }

    const params = new URLSearchParams(window.location.search);
    const visitorName = (params.get("name") || lead?.name || "").trim();
    const visitorEmail = (params.get("email") || lead?.email || "").trim();
    const capability = (params.get("service") || lead?.capability || "").trim();
    const refCode = (params.get("ref") || lead?.ref || "ALF-2026").trim();

    const refEl = document.getElementById("thankYouRefCode");
    if (refEl) refEl.textContent = `REF #${refCode}`;

    if (visitorName) {
      const firstName = visitorName.split(/\s+/)[0].replace(/[<>&"']/g, "");
      const titleEl = document.getElementById("thankYouTitle");
      if (titleEl && firstName) {
        titleEl.innerHTML = `Thank You, ${firstName} — <span>Your Brief Is Confirmed.</span>`;
      }
    }

    if (visitorEmail) {
      const emailEl = document.getElementById("thankYouEmailVal");
      if (emailEl) emailEl.textContent = visitorEmail;
    }

    if (capability) {
      const capEl = document.getElementById("thankYouServiceVal");
      if (capEl) capEl.textContent = capability;
    }

    if (lead?.isStrategyCall && lead?.bookingSlot) {
      const statusTextEl = document.querySelector(".thankyou-status-pill span:last-child");
      if (statusTextEl) {
        statusTextEl.textContent = `Strategy Call Reserved · ${lead.bookingSlot}`;
      }
      const titleEl = document.getElementById("thankYouTitle");
      if (titleEl) {
        const firstName = visitorName ? visitorName.split(/\s+/)[0].replace(/[<>&"']/g, "") : "";
        titleEl.innerHTML = firstName
          ? `Confirmed, ${firstName} — <span>Your Strategy Call Is Booked.</span>`
          : `Confirmed — <span>Your Strategy Call Is Booked.</span>`;
      }
      const summaryList = document.querySelector(".thankyou-summary-list");
      if (summaryList && !document.getElementById("thankYouSlotRow")) {
        const li = document.createElement("li");
        li.id = "thankYouSlotRow";
        li.innerHTML = `
          <span class="thankyou-sum-key">Reserved Slot</span>
          <span class="thankyou-sum-val">${lead.bookingSlot}</span>
        `;
        summaryList.prepend(li);
      }
    }
  }
});
