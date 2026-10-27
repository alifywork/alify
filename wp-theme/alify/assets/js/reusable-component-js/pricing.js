// ==========================================================================
// Pricing Page Interactive Controller (pricing.html + start-project.html sync)
// 1. Fixed-Scope Sprint vs Monthly Retainer Pod Switcher
// 2. Live Project Investment & Sprint Scope Estimator
// 3. Pre-Fill Handoff to start-project.html
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  // --------------------------------------------------------------------------
  // 1. Engagement Model Switcher (Fixed-Scope Sprint vs Monthly Retainer Pod)
  // --------------------------------------------------------------------------
  const modeButtons = document.querySelectorAll(".js-pricing-mode");
  const tierCards = document.querySelectorAll(".js-pricing-card");

  if (modeButtons.length && tierCards.length) {
    modeButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const mode = btn.getAttribute("data-mode") || "sprint";
        modeButtons.forEach((b) => {
          const active = b === btn;
          b.classList.toggle("is-active", active);
          b.setAttribute("aria-pressed", String(active));
        });

        tierCards.forEach((card) => {
          const amountEl = card.querySelector(".js-price-amount");
          const cadenceEl = card.querySelector(".js-price-cadence");
          const metaEl = card.querySelector(".js-price-meta");
          const velocityEl = card.querySelector(".js-price-velocity");
          const ctaEl = card.querySelector(".js-tier-cta");
          const planSlug = card.getAttribute("data-plan") || "scale-ecosystem";

          const nextPrice = card.getAttribute(`data-${mode}-price`);
          const nextCadence = card.getAttribute(`data-${mode}-cadence`);
          const nextMeta = card.getAttribute(`data-${mode}-meta`);
          const nextVelocity = card.getAttribute(`data-${mode}-velocity`);

          if (amountEl && nextPrice) {
            amountEl.classList.add("is-switching");
            window.setTimeout(() => {
              amountEl.textContent = nextPrice;
              if (cadenceEl && nextCadence) cadenceEl.textContent = nextCadence;
              if (metaEl && nextMeta) metaEl.textContent = nextMeta;
              if (velocityEl && nextVelocity) velocityEl.textContent = nextVelocity;
              if (ctaEl) {
                const spBase = (window.ALIFY_WP && window.ALIFY_WP.startProjectUrl) || "start-project.html";
                const sep = spBase.includes("?") ? "&" : "?";
                ctaEl.setAttribute("href", `${spBase}${sep}plan=${planSlug}&mode=${mode}`);
              }
              amountEl.classList.remove("is-switching");
            }, 150);
          }
        });
      });
    });
  }

  // --------------------------------------------------------------------------
  // 2. Interactive Project Investment & Scope Estimator
  // --------------------------------------------------------------------------
  const calcRoot = document.getElementById("investmentCalculator");
  if (calcRoot) {
    const moduleInputs = Array.from(calcRoot.querySelectorAll("input[name='calc_module']"));
    const scaleInputs = Array.from(calcRoot.querySelectorAll("input[name='calc_scale']"));
    const speedInputs = Array.from(calcRoot.querySelectorAll("input[name='calc_velocity']"));

    const rangeEl = document.getElementById("calcRangeOutput");
    const timelineEl = document.getElementById("calcTimelineOutput");
    const squadEl = document.getElementById("calcSquadOutput");
    const modulesListEl = document.getElementById("calcModulesList");
    const lockBtn = document.getElementById("calcLockBtn");

    const formatUSD = (val) => `$${Math.round(val / 100) * 100}`.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

    const recalculate = () => {
      let checkedModules = moduleInputs.filter((i) => i.checked);
      // Ensure at least 1 module stays selected
      if (!checkedModules.length && moduleInputs[0]) {
        moduleInputs[0].checked = true;
        checkedModules = [moduleInputs[0]];
      }

      const baseSum = checkedModules.reduce((acc, el) => acc + Number(el.dataset.cost || 0), 0);
      const baseWeeks = checkedModules.reduce((acc, el) => acc + Number(el.dataset.weeks || 0), 0);

      const activeScale = scaleInputs.find((i) => i.checked);
      const scaleMult = Number(activeScale?.dataset.mult || 1);
      const scaleExtraWeeks = Number(activeScale?.dataset.extraWeeks || 0);
      const scaleLabel = activeScale?.value || "Focused (1–6 Views)";

      const activeSpeed = speedInputs.find((i) => i.checked);
      const speedMult = Number(activeSpeed?.dataset.mult || 1);
      const isFastTrack = activeSpeed?.value === "Priority Fast-Track";

      const lowEst = baseSum * scaleMult * speedMult;
      const highEst = lowEst * 1.22;

      const rawWeeks = Math.max(2, Math.round((baseWeeks + scaleExtraWeeks) * (isFastTrack ? 0.72 : 1)));
      const maxWeeks = rawWeeks + 2;

      let squadText = "2 Senior Specialists";
      if (checkedModules.length >= 4 || scaleMult > 1.5) {
        squadText = "4–5 Senior Specialists";
      } else if (checkedModules.length >= 2 || scaleMult > 1.1) {
        squadText = "3 Senior Specialists";
      }

      if (rangeEl) rangeEl.textContent = `${formatUSD(lowEst)} – ${formatUSD(highEst)}`;
      if (timelineEl) timelineEl.textContent = `${rawWeeks}–${maxWeeks} Weeks`;
      if (squadEl) squadEl.textContent = squadText;

      if (modulesListEl) {
        modulesListEl.innerHTML = checkedModules
          .map(
            (m) =>
              `<li class="pricing-summary-chip"><i class="fa-solid fa-check" aria-hidden="true"></i><span>${m.value}</span></li>`
          )
          .join("");
      }

      // Store latest estimate snapshot for seamless handoff to start-project.html
      if (lockBtn) {
        lockBtn.onclick = () => {
          const snapshot = {
            modules: checkedModules.map((m) => m.value),
            scale: scaleLabel,
            velocity: activeSpeed?.value || "Standard Sprint",
            range: `${formatUSD(lowEst)} – ${formatUSD(highEst)}`,
            timeline: `${rawWeeks}–${maxWeeks} Weeks`,
          };
          try {
            sessionStorage.setItem("alifyPricingEstimate", JSON.stringify(snapshot));
          } catch {
            // Ignore storage restrictions
          }
          const spBase = (window.ALIFY_WP && window.ALIFY_WP.startProjectUrl) || "start-project.html";
          window.location.href = `${spBase}${spBase.includes("?") ? "&" : "?"}source=calculator`;
        };
      }
    };

    [...moduleInputs, ...scaleInputs, ...speedInputs].forEach((input) => {
      input.addEventListener("change", recalculate);
    });

    recalculate();
  }

  // --------------------------------------------------------------------------
  // 3. Auto-Hydrate start-project.html if Visitor Came From pricing.html
  // --------------------------------------------------------------------------
  const params = new URLSearchParams(window.location.search);
  const fromPlan = params.get("plan");
  const fromMode = params.get("mode");
  const fromCalc = params.get("source") === "calculator";

  if ((fromPlan || fromCalc) && document.querySelector(".start-project-section")) {
    const summaryField = document.querySelector(".start-project-section textarea");
    if (fromPlan && summaryField && !summaryField.value.trim()) {
      const planNames = {
        "launch-sprint": "01 · Launch Sprint",
        "scale-ecosystem": "02 · Scale Ecosystem",
        "enterprise-squad": "03 · Enterprise Squad",
      };
      const modeName = fromMode === "retainer" ? "Monthly Retainer Pod" : "Fixed-Scope Sprint";
      summaryField.value = `Interested in ${planNames[fromPlan] || fromPlan} (${modeName}). Here are our key goals and target launch window: `;
    } else if (fromCalc && summaryField && !summaryField.value.trim()) {
      try {
        const est = JSON.parse(sessionStorage.getItem("alifyPricingEstimate") || "null");
        if (est) {
          summaryField.value = `Configured via ALIFY Investment Estimator: ${est.modules.join(", ")} | Scale: ${est.scale} | Velocity: ${est.velocity} | Est. Range: ${est.range} (${est.timeline}). Project goals: `;
        }
      } catch {
        // Ignore storage parse issues
      }
    }
  }
});
