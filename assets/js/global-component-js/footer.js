// ==========================================================================
// Global Footer Script (Newsletter, Dynamic Year & Back-to-Top)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  // Dynamic Copyright Year
  const yearEl = document.getElementById("footerYear");
  if (yearEl) {
    yearEl.textContent = String(new Date().getFullYear());
  }

  // Instant Back-to-Top Button
  const backToTopBtn = document.getElementById("backToTopBtn");
  backToTopBtn?.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "auto" });
  });

  // Newsletter Form Feedback
  const newsletterForm = document.getElementById("footerNewsletterForm");
  const newsletterInput = document.getElementById("footerNewsletterInput");
  const newsletterMsg = document.getElementById("footerNewsletterMsg");

  newsletterForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    const email = newsletterInput?.value.trim() || "";
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
      if (newsletterMsg) {
        newsletterMsg.textContent = "Please enter a valid work email address.";
      }
      newsletterInput?.focus();
      return;
    }

    if (newsletterMsg) {
      newsletterMsg.textContent = "Thank you for subscribing to ALIFY insights!";
    }
    if (newsletterInput) {
      newsletterInput.value = "";
    }
  });
});
