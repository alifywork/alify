// ==========================================================================
// Global Header & Right-Side Slide Drawer Script
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".site-header");
  const menuToggle = document.querySelector(".mobile-menu-toggle");
  const drawer = document.getElementById("mobileDrawer");
  const drawerClose = document.querySelector(".drawer-close");
  const drawerOverlay = document.getElementById("drawerOverlay");

  // Desktop Dropdowns (Supports all dropdown items)
  const desktopDropdownItems = Array.from(document.querySelectorAll(".header-nav-item.has-dropdown"));

  // Mobile Accordion Submenus inside Drawer (Supports multiple submenus)
  const drawerDropdownItems = Array.from(document.querySelectorAll(".drawer-nav-item.has-submenu"));

  // Highlight Active Page Link Automatically (including single detail pages)
  const rawFile = window.location.pathname.split("/").pop() || "index.html";
  const parentPageMap = {
    "blog-single.html": "blog.html",
    "service-single.html": "services.html",
    "industry-single.html": "industries.html",
    "work-single.html": "work.html",
    "case-study-single.html": "case-studies.html",
  };
  const currentFile = parentPageMap[rawFile] || rawFile;
  document.querySelectorAll(".header-nav-link, .dropdown-item-link, .drawer-nav-link").forEach((link) => {
    const href = link.getAttribute("href");
    if (!href) return;
    const linkFile = href.split("#")[0];
    if (linkFile === currentFile) {
      link.classList.add("is-active");
      link.closest(".header-nav-item.has-dropdown")
        ?.querySelector(":scope > .header-nav-link")
        ?.classList.add("is-active");
    } else {
      link.classList.remove("is-active");
    }
  });

  // Fixed Overlapping Header Scroll State (Transparent on Hero -> Solid Frosted on Scroll)
  let isScrolledState = false;
  let scrollTicking = false;
  const syncHeaderScroll = () => {
    scrollTicking = false;
    if (!header) return;
    const y = window.scrollY;
    if (!isScrolledState && y > 24) {
      isScrolledState = true;
      header.classList.add("is-scrolled");
    } else if (isScrolledState && y <= 10) {
      isScrolledState = false;
      header.classList.remove("is-scrolled");
    }
  };

  const handleScroll = () => {
    if (!scrollTicking) {
      scrollTicking = true;
      window.requestAnimationFrame(syncHeaderScroll);
    }
  };

  window.addEventListener("scroll", handleScroll, { passive: true });
  syncHeaderScroll();

  // Open / Close Right-Side Slide Drawer
  const openDrawer = () => {
    if (!drawer || !drawerOverlay) return;
    drawer.classList.add("is-open");
    drawerOverlay.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    menuToggle?.setAttribute("aria-expanded", "true");
    document.body.classList.add("drawer-open");


  };

  const closeDrawer = () => {
    if (!drawer || !drawerOverlay) return;
    drawer.classList.remove("is-open");
    drawerOverlay.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    menuToggle?.setAttribute("aria-expanded", "false");
    document.body.classList.remove("drawer-open");
  };

  menuToggle?.addEventListener("click", openDrawer);
  drawerClose?.addEventListener("click", closeDrawer);
  drawerOverlay?.addEventListener("click", closeDrawer);

  // Close drawer when any link inside drawer is clicked
  drawer?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      closeDrawer();
    });
  });

  // Mobile Submenu Accordion Toggles (All .has-submenu items)
  drawerDropdownItems.forEach((item) => {
    const toggleBtn = item.querySelector(".drawer-dropdown-toggle");
    toggleBtn?.addEventListener("click", () => {
      const isOpen = item.classList.toggle("is-open");
      toggleBtn.setAttribute("aria-expanded", String(isOpen));
    });
  });

  // Desktop Dropdowns Keyboard Accessibility (All .has-dropdown items)
  desktopDropdownItems.forEach((item) => {
    const trigger = item.querySelector(".header-nav-link");
    item.addEventListener("focusin", () => {
      item.classList.add("is-open");
      trigger?.setAttribute("aria-expanded", "true");
    });

    item.addEventListener("focusout", (e) => {
      if (!item.contains(e.relatedTarget)) {
        item.classList.remove("is-open");
        trigger?.setAttribute("aria-expanded", "false");
      }
    });
  });

  // Escape Key & Desktop Resize Cleanup
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeDrawer();
      desktopDropdownItems.forEach((item) => {
        item.classList.remove("is-open");
        item.querySelector(".header-nav-link")?.setAttribute("aria-expanded", "false");
      });
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 1200 && drawer?.classList.contains("is-open")) {
      closeDrawer();
    }
  });
});
