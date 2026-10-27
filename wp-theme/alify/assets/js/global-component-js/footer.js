// ==========================================================================
// Global Footer Script (Newsletter, Dynamic Year, Back-to-Top & GSAP Reveal)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  // Dynamic Copyright Year
  const yearEl = document.getElementById("footerYear");
  if (yearEl) {
    yearEl.textContent = String(new Date().getFullYear());
  }

  // Smooth Back-to-Top Button
  const backToTopBtn = document.getElementById("backToTopBtn");
  backToTopBtn?.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // Newsletter Form Feedback
  const newsletterForm = document.getElementById("footerNewsletterForm");
  const newsletterInput =
    document.getElementById("footerNewsletterInput") ||
    newsletterForm?.querySelector("input[type='email']");
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

    if (window.alifyPostLeadToWP) {
      window.alifyPostLeadToWP({
        formTemplate: newsletterForm.dataset.formTemplate || "newsletter",
        alify_hp: newsletterForm.querySelector("input[name='alify_hp']")?.value || "",
        email,
        leadType: "newsletter",
        capability: "Studio Insights Newsletter",
      });
    }

    if (newsletterMsg) {
      newsletterMsg.textContent =
        newsletterForm.dataset.successMsg ||
        "Thank you for subscribing to ALIFY insights!";
    }
    if (newsletterInput) {
      newsletterInput.value = "";
    }
  });

  // Subtle GSAP ScrollTrigger Entrance on Footer CTA Banner
  if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);
    const ctaBanner = document.querySelector(".footer-cta-banner");
    if (ctaBanner) {
      gsap.from(ctaBanner, {
        y: 28,
        opacity: 0,
        duration: 0.7,
        ease: "power2.out",
        clearProps: "transform,opacity",
        scrollTrigger: {
          trigger: ctaBanner,
          start: "top 92%",
          once: true,
        },
      });
    }
  }
});
