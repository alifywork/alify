// ==========================================================================
// Reusable Forms Validation, Session Context & Thank You Redirect Handler
// Handles:
// - Inner Hero Right-Side Lead Forms (.js-alify-form)
// - Reusable Contact Section Forms (.js-alify-form)
// - Complete Contact Us Form (.js-alify-form)
// - Start a Project Scope Builder Form (.js-alify-form)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  document.querySelectorAll(".js-alify-form").forEach((form) => {
    const feedback = form.querySelector(".alify-form-feedback");
    const feedbackText = feedback?.querySelector(".alify-feedback-text");

    // Clear error highlight as user types/changes
    form.querySelectorAll(".alify-input, .alify-select, .alify-textarea").forEach((control) => {
      control.addEventListener("input", () => {
        control.closest(".alify-field")?.classList.remove("has-error");
      });
      control.addEventListener("change", () => {
        control.closest(".alify-field")?.classList.remove("has-error");
      });
    });

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      let isValid = true;
      let firstInvalid = null;

      form.querySelectorAll("[data-required='true']").forEach((control) => {
        const field = control.closest(".alify-field");
        const val = control.value.trim();
        let fieldValid = val.length > 0;

        if (fieldValid && control.type === "email") {
          fieldValid = emailRegex.test(val);
        }

        if (!fieldValid) {
          isValid = false;
          field?.classList.add("has-error");
          if (!firstInvalid) firstInvalid = control;
        } else {
          field?.classList.remove("has-error");
        }
      });

      if (!isValid) {
        firstInvalid?.focus();
        return;
      }

      // Extract lead details for personalized Thank You page confirmation
      const nameInput = form.querySelector("input[type='text']");
      const emailInput = form.querySelector("input[type='email']");
      const selectEl = form.querySelector("select");
      const checkedPills = Array.from(
        form.querySelectorAll("input[type='checkbox']:checked")
      )
        .map((cb) => cb.parentElement?.textContent?.trim())
        .filter(Boolean);

      let capability = "Custom Digital Strategy & Build";
      if (checkedPills.length) {
        capability = checkedPills.slice(0, 2).join(", ");
      } else if (selectEl && selectEl.selectedIndex > 0) {
        capability = selectEl.options[selectEl.selectedIndex].text.trim();
      }

      const refNum = `ALF-${Math.floor(1000 + Math.random() * 9000)}`;
      const payload = {
        name: nameInput?.value.trim() || "",
        email: emailInput?.value.trim() || "",
        capability,
        ref: refNum,
      };

      try {
        sessionStorage.setItem("alifyLeadSubmission", JSON.stringify(payload));
      } catch {
        // Ignore storage restrictions
      }

      const msgVal = (form.querySelector("textarea")?.value || "").trim();
      const phVal = (form.querySelector("input[type='tel']")?.value || "").trim();
      const compEl = form.querySelector("input[name*='company' i], input[id*='company' i], input[placeholder*='Company' i]");
      const companyVal = (compEl && compEl !== nameInput ? compEl.value : "").trim();
      const checkedBudget = form.querySelector("input[type='radio']:checked");
      let budgetVal = checkedBudget ? (checkedBudget.parentElement?.textContent?.trim() || checkedBudget.value || "") : "";
      if (!budgetVal) {
        const selects = Array.from(form.querySelectorAll("select"));
        if (selects.length > 1 && selects[1].selectedIndex > 0) {
          budgetVal = selects[1].options[selects[1].selectedIndex].text.trim();
        }
      }

      if (feedbackText) {
        feedbackText.textContent =
          "Thank you! Redirecting to your confirmation summary...";
      }
      feedback?.classList.add("is-visible");
      form.reset();

      const sendPromise = window.alifyPostLeadToWP
        ? window.alifyPostLeadToWP({ ...payload, phone: phVal, company: companyVal, budget: budgetVal, message: msgVal })
        : Promise.resolve();
      sendPromise.finally(() => {
        setTimeout(() => {
          window.location.href = (window.ALIFY_WP && window.ALIFY_WP.thankYouUrl) || "thank-you.html";
        }, 320);
      });
    });
  });
});
