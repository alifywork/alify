// ==========================================================================
// Reusable Forms Validation & Submission Feedback Handler
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

      const successMessage =
        form.getAttribute("data-success-msg") ||
        "Thank you! Your inquiry has been received — a △LIFY project director will contact you within 24 hours.";

      if (feedbackText) {
        feedbackText.textContent = successMessage;
      }
      feedback?.classList.add("is-visible");
      form.reset();
    });
  });
});
