// Pricing page controller: engagement model switcher and plan handoff.
document.addEventListener("DOMContentLoaded", () => {
  const modeButtons = document.querySelectorAll(".js-pricing-mode");
  const tierCards = document.querySelectorAll(".js-pricing-card");

  if (modeButtons.length && tierCards.length) {
    modeButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const mode = btn.getAttribute("data-mode") || "sprint";
        modeButtons.forEach((button) => {
          const active = button === btn;
          button.classList.toggle("is-active", active);
          button.setAttribute("aria-pressed", String(active));
        });

        tierCards.forEach((card) => {
          const amountEl = card.querySelector(".js-price-amount");
          const cadenceEl = card.querySelector(".js-price-cadence");
          const metaEl = card.querySelector(".js-price-meta");
          const velocityEl = card.querySelector(".js-price-velocity");
          const ctaEl = card.querySelector(".js-tier-cta");
          const planSlug = card.getAttribute("data-plan") || "scale-ecosystem";
          const values = {
            price: card.getAttribute(`data-${mode}-price`),
            cadence: card.getAttribute(`data-${mode}-cadence`),
            meta: card.getAttribute(`data-${mode}-meta`),
            velocity: card.getAttribute(`data-${mode}-velocity`),
          };

          if (amountEl && values.price) {
            amountEl.textContent = values.price;
            if (cadenceEl && values.cadence) cadenceEl.textContent = values.cadence;
            if (metaEl && values.meta) metaEl.textContent = values.meta;
            if (velocityEl && values.velocity) velocityEl.textContent = values.velocity;
            if (ctaEl) ctaEl.setAttribute("href", `start-project.html?plan=${planSlug}&mode=${mode}`);
          }
        });
      });
    });
  }

  // Prefill the project brief when a visitor selects a pricing tier.
  const params = new URLSearchParams(window.location.search);
  const fromPlan = params.get("plan");
  const fromMode = params.get("mode");
  const summaryField = document.querySelector(".start-project-section textarea");
  if (fromPlan && summaryField && !summaryField.value.trim()) {
    const planNames = {
      "launch-sprint": "01 · Launch Sprint",
      "scale-ecosystem": "02 · Scale Ecosystem",
      "enterprise-squad": "03 · Enterprise Squad",
    };
    const modeName = fromMode === "retainer" ? "Monthly Retainer Pod" : "Fixed-Scope Sprint";
    summaryField.value = `Interested in ${planNames[fromPlan] || fromPlan} (${modeName}). Here are our key goals and target launch window: `;
  }
});
