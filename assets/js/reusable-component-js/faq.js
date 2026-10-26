// ==========================================================================
// Reusable FAQs Accordion Component Script
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".faq-accordion-list").forEach((accordion) => {
    const items = Array.from(accordion.querySelectorAll(".faq-item"));

    items.forEach((item) => {
      const btn = item.querySelector(".faq-question-btn");
      btn?.addEventListener("click", () => {
        const isAlreadyOpen = item.classList.contains("is-open");

        items.forEach((other) => {
          other.classList.remove("is-open");
          other
            .querySelector(".faq-question-btn")
            ?.setAttribute("aria-expanded", "false");
        });

        if (!isAlreadyOpen) {
          item.classList.add("is-open");
          btn.setAttribute("aria-expanded", "true");
        }
      });
    });
  });
});
