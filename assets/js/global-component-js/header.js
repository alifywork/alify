// ==========================================================================
// Global Header & Right-Side Slide Drawer Script
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".site-header");
  const menuToggle = document.querySelector(".mobile-menu-toggle");
  const drawer = document.getElementById("mobileDrawer");
  const drawerClose = document.querySelector(".drawer-close");
  const drawerOverlay = document.getElementById("drawerOverlay");

  // Desktop Services Dropdown
  const desktopDropdownItem = document.querySelector(".header-nav-item.has-dropdown");
  const desktopDropdownTrigger = desktopDropdownItem?.querySelector(".header-nav-link");

  // Mobile Services Accordion inside Drawer
  const drawerDropdownItem = document.querySelector(".drawer-nav-item.has-submenu");
  const drawerDropdownToggle = drawerDropdownItem?.querySelector(".drawer-dropdown-toggle");

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
  document.querySelectorAll(".header-nav-link, .drawer-nav-link").forEach((link) => {
    const href = link.getAttribute("href");
    if (!href) return;
    const linkFile = href.split("#")[0];
    if (linkFile === currentFile) {
      link.classList.add("is-active");
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

    if (typeof gsap !== "undefined") {
      gsap.fromTo(
        ".drawer-nav-item, .drawer-footer > *",
        { x: 24, opacity: 0 },
        {
          x: 0,
          opacity: 1,
          duration: 0.35,
          stagger: 0.04,
          ease: "power2.out",
          clearProps: "transform,opacity",
        }
      );
    }
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

  // Mobile Services Submenu Accordion Toggle
  drawerDropdownToggle?.addEventListener("click", () => {
    const isOpen = drawerDropdownItem.classList.toggle("is-open");
    drawerDropdownToggle.setAttribute("aria-expanded", String(isOpen));
  });

  // Desktop Services Dropdown Keyboard Accessibility
  desktopDropdownItem?.addEventListener("focusin", () => {
    desktopDropdownItem.classList.add("is-open");
    desktopDropdownTrigger?.setAttribute("aria-expanded", "true");
  });

  desktopDropdownItem?.addEventListener("focusout", (e) => {
    if (!desktopDropdownItem.contains(e.relatedTarget)) {
      desktopDropdownItem.classList.remove("is-open");
      desktopDropdownTrigger?.setAttribute("aria-expanded", "false");
    }
  });

  // Escape Key & Desktop Resize Cleanup
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeDrawer();
      desktopDropdownItem?.classList.remove("is-open");
      desktopDropdownTrigger?.setAttribute("aria-expanded", "false");
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 1200 && drawer?.classList.contains("is-open")) {
      closeDrawer();
    }
  });
});
