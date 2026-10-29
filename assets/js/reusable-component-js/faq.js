// ==========================================================================
// Reusable FAQs Accordion & Dedicated FAQ Hub Controller (faq.html)
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  const setAccordionItem = (item, open) => {
    const button = item.querySelector(".faq-question-btn");
    const answer = item.querySelector(".faq-answer-inner");
    const canAnimate = typeof gsap !== "undefined" && !prefersReducedMotion && answer;

    if (!canAnimate) {
      item.classList.toggle("is-open", open);
      button?.setAttribute("aria-expanded", String(open));
      return;
    }

    gsap.killTweensOf(answer);
    if (open) {
      item.classList.add("is-open");
      button?.setAttribute("aria-expanded", "true");
      gsap.fromTo(answer, { height: 0, autoAlpha: 0, y: -5 }, {
        height: "auto",
        autoAlpha: 1,
        y: 0,
        duration: 0.28,
        ease: "power2.out",
        onComplete: () => gsap.set(answer, { clearProps: "height,opacity,visibility,transform" }),
      });
    } else if (item.classList.contains("is-open")) {
      button?.setAttribute("aria-expanded", "false");
      gsap.to(answer, {
        height: 0,
        autoAlpha: 0,
        y: -5,
        duration: 0.2,
        ease: "power1.in",
        onComplete: () => {
          item.classList.remove("is-open");
          gsap.set(answer, { clearProps: "height,opacity,visibility,transform" });
        },
      });
    }
  };

  // 1. Standard Accordion Toggle (All Pages + Hub Groups)
  document.querySelectorAll(".faq-accordion-list").forEach((accordion) => {
    const items = Array.from(accordion.querySelectorAll(".faq-item"));

    items.forEach((item) => {
      const btn = item.querySelector(".faq-question-btn");
      btn?.addEventListener("click", () => {
        const isAlreadyOpen = item.classList.contains("is-open");
        if (!accordion.hasAttribute("data-allow-multiple")) {
          items.forEach((other) => {
            if (other !== item) setAccordionItem(other, false);
          });
        }
        setAccordionItem(item, !isAlreadyOpen);
      });
    });
  });

  // 2. Dedicated FAQ Knowledge Hub Search, Category Filter & Expand-All (faq.html)
  const hubRoot = document.getElementById("faqHubSection");
  if (!hubRoot) return;

  const searchInput = document.getElementById("faqHubSearchInput");
  const filterPills = Array.from(hubRoot.querySelectorAll(".faq-hub-pill"));
  const catLinks = Array.from(hubRoot.querySelectorAll(".faq-hub-cat-link"));
  const groups = Array.from(hubRoot.querySelectorAll(".faq-hub-group"));
  const expandAllBtn = document.getElementById("faqHubExpandAllBtn");
  const emptyBox = document.getElementById("faqHubEmpty");
  const resetBtn = document.getElementById("faqHubResetBtn");

  let activeCat = "all";
  let allExpanded = false;

  const applyHubFilters = () => {
    const query = (searchInput?.value || "").trim().toLowerCase();
    let totalVisible = 0;

    groups.forEach((group) => {
      const gCat = group.getAttribute("data-faq-cat") || "";
      const catMatches = activeCat === "all" || activeCat === gCat;
      let groupVisible = 0;

      group.querySelectorAll(".faq-item").forEach((item) => {
        const text = item.textContent.toLowerCase();
        const searchMatches = !query || text.includes(query);
        const show = catMatches && searchMatches;
        item.classList.toggle("is-search-hidden", !show);
        if (show) {
          groupVisible += 1;
          totalVisible += 1;
        }
      });

      group.classList.toggle("is-hidden", groupVisible === 0);
    });

    if (emptyBox) {
      emptyBox.classList.toggle("is-visible", totalVisible === 0);
    }
  };

  filterPills.forEach((pill) => {
    pill.addEventListener("click", () => {
      activeCat = pill.getAttribute("data-filter-cat") || "all";
      filterPills.forEach((p) => p.classList.toggle("is-active", p === pill));
      catLinks.forEach((link) => {
        const targetCat = link.getAttribute("data-cat-link") || "";
        link.classList.toggle("is-active", activeCat === targetCat);
      });
      applyHubFilters();
    });
  });

  catLinks.forEach((link) => {
    link.addEventListener("click", () => {
      catLinks.forEach((l) => l.classList.toggle("is-active", l === link));
      if (activeCat !== "all") {
        activeCat = "all";
        filterPills.forEach((p) =>
          p.classList.toggle("is-active", p.getAttribute("data-filter-cat") === "all")
        );
        applyHubFilters();
      }
    });
  });

  searchInput?.addEventListener("input", applyHubFilters);

  resetBtn?.addEventListener("click", () => {
    if (searchInput) searchInput.value = "";
    activeCat = "all";
    filterPills.forEach((p) =>
      p.classList.toggle("is-active", p.getAttribute("data-filter-cat") === "all")
    );
    applyHubFilters();
  });

  expandAllBtn?.addEventListener("click", () => {
    allExpanded = !allExpanded;
    hubRoot.querySelectorAll(".faq-hub-group .faq-accordion-list").forEach((acc) => {
      if (allExpanded) acc.setAttribute("data-allow-multiple", "true");
      else acc.removeAttribute("data-allow-multiple");
    });

    hubRoot.querySelectorAll(".faq-hub-group .faq-item").forEach((item) => {
      item.classList.toggle("is-open", allExpanded);
      item
        .querySelector(".faq-question-btn")
        ?.setAttribute("aria-expanded", allExpanded ? "true" : "false");
    });

    const labelSpan = expandAllBtn.querySelector("span");
    if (labelSpan) {
      labelSpan.textContent = allExpanded ? "Collapse All Answers" : "Expand All Answers";
    }
  });
});
