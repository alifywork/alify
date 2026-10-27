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

      // Extract lead details for CRM, Email & Thank You page confirmation
      const hpInput = form.querySelector("input[name='alify_hp']");
      const nameInput = Array.from(form.querySelectorAll("input[type='text']")).find((el) => el !== hpInput);
      const emailInput = form.querySelector("input[type='email']");
      const selectEl = form.querySelector("select");
      const checkedPills = Array.from(
        form.querySelectorAll("input[name='scope_services']:checked")
      )
        .map((cb) => cb.value || cb.parentElement?.textContent?.trim())
        .filter(Boolean);

      let capability = "Custom Digital Strategy & Build";
      if (checkedPills.length) {
        capability = checkedPills.slice(0, 3).join(", ");
      } else if (selectEl && selectEl.selectedIndex > 0) {
        capability = selectEl.options[selectEl.selectedIndex].text.trim();
      }

      const refPrefix = (window.ALIFY_WP && window.ALIFY_WP.leadRefPrefix) || "ALF-";
      const refNum = `${refPrefix}${Math.floor(1000 + Math.random() * 9000)}`;
      const payload = {
        formTemplate: form.dataset.formTemplate || "",
        leadType: form.dataset.leadType || "",
        alify_hp: hpInput?.value || "",
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
      const compEl = form.querySelector("input[name='company'], input[id*='Company'], input[id*='Website']");
      const companyVal = (compEl && compEl !== nameInput ? compEl.value : "").trim();
      const subjEl = form.querySelector("input[name='subject'], input[id*='Subject']");
      const subjectVal = (subjEl && subjEl !== nameInput ? subjEl.value : "").trim();
      const timelineRadio = form.querySelector("input[name='project_timeline']:checked");
      const timelineVal = timelineRadio ? (timelineRadio.value || "") : "";
      const channelRadio = form.querySelector("input[name='preferred_channel']:checked");
      const channelVal = channelRadio ? (channelRadio.value || "") : "";

      const checkedBudget = form.querySelector("input[name='project_budget']:checked");
      let budgetVal = checkedBudget ? (checkedBudget.value || checkedBudget.parentElement?.textContent?.trim() || "") : "";
      if (!budgetVal) {
        const budgetSelect = form.querySelector("select[name='budget'], #cpBudget");
        if (budgetSelect && budgetSelect.selectedIndex > 0) {
          budgetVal = budgetSelect.options[budgetSelect.selectedIndex].text.trim();
        }
      }

      const redirectMode =
        form.dataset.redirectMode ||
        (window.ALIFY_WP && window.ALIFY_WP.submissionBehavior) ||
        "redirect";
      const inlineMsg =
        form.dataset.successMsg ||
        "Thank you! Your request has been received — our team will reply within 24 hours.";

      if (feedbackText) {
        feedbackText.textContent =
          redirectMode === "inline"
            ? `${inlineMsg} (Ref: ${refNum})`
            : "Thank you! Redirecting to your confirmation summary...";
      }
      feedback?.classList.add("is-visible");
      form.reset();

      const sendPromise = window.alifyPostLeadToWP
        ? window.alifyPostLeadToWP({
            ...payload,
            phone: phVal,
            company: companyVal,
            budget: budgetVal,
            timeline: timelineVal,
            subject: subjectVal,
            channel: channelVal,
            message: msgVal,
          })
        : Promise.resolve(null);

      sendPromise.then((res) => {
        const finalMode =
          (res && res.data && res.data.submissionBehavior) || redirectMode;
        if (finalMode === "inline") {
          return;
        }
        const targetUrl =
          form.dataset.redirectUrl ||
          (res && res.data && res.data.redirectUrl) ||
          (window.ALIFY_WP && window.ALIFY_WP.thankYouUrl) ||
          "thank-you.html";
        setTimeout(() => {
          window.location.href = targetUrl;
        }, 320);
      });
    });
  });
});
