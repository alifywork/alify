/**
 * ALIFY — Reusable Studio About, Process Counters & Legal TOC Controller
 * Powers animated KPI counters on scroll + smooth legal TOC highlighting.
 */
document.addEventListener("DOMContentLoaded", () => {
  // 1. Animated KPI Counters (.js-studio-counter)
  const counters = document.querySelectorAll(".js-studio-counter");
  if (counters.length && "IntersectionObserver" in window) {
    const animateCounter = (el) => {
      const target = parseFloat(el.dataset.target || "0");
      const prefix = el.dataset.prefix || "";
      const suffix = el.dataset.suffix || "";
      const duration = 1400;
      const startTime = performance.now();

      const tick = (now) => {
        const progress = Math.min(1, (now - startTime) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(target * eased);
        el.textContent = `${prefix}${current}${suffix}`;
        if (progress < 1) {
          requestAnimationFrame(tick);
        } else {
          el.textContent = `${prefix}${target}${suffix}`;
        }
      };
      requestAnimationFrame(tick);
    };

    const io = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.35 }
    );

    counters.forEach((c) => io.observe(c));
  }

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
          target.scrollIntoView({ behavior: "smooth", block: "start" });
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
});
