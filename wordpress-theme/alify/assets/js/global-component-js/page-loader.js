// Branded initial loader and fast same-origin page transitions.
(() => {
  const loader = document.getElementById("alifyPageLoader");
  if (!loader) return;

  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  const minimumVisibleMs = prefersReducedMotion ? 0 : 460;
  const navigationDelayMs = prefersReducedMotion ? 0 : 180;
  const startedAt = performance.now();
  let hideTimer = 0;
  let navigationTimer = 0;
  let failSafeTimer = 0;
  let navigating = false;

  const hideLoader = ({ immediate = false } = {}) => {
    window.clearTimeout(hideTimer);
    window.clearTimeout(navigationTimer);
    window.clearTimeout(failSafeTimer);
    navigating = false;
    loader.classList.remove("is-active");
    loader.setAttribute("aria-hidden", "true");
    if (immediate) {
      loader.style.transition = "none";
      requestAnimationFrame(() => {
        loader.style.removeProperty("transition");
      });
    }
  };

  const armFailSafe = () => {
    window.clearTimeout(failSafeTimer);
    failSafeTimer = window.setTimeout(() => {
      navigating = false;
      hideLoader();
    }, 10000);
  };

  const showLoader = () => {
    window.clearTimeout(hideTimer);
    loader.classList.add("is-active");
    loader.setAttribute("aria-hidden", "false");
    loader.style.removeProperty("transition");
    armFailSafe();
  };

  armFailSafe();

  const hideWhenReady = () => {
    const remaining = Math.max(0, minimumVisibleMs - (performance.now() - startedAt));
    hideTimer = window.setTimeout(() => hideLoader(), remaining);
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", hideWhenReady, { once: true });
  } else {
    hideWhenReady();
  }

  window.addEventListener("pageshow", (event) => {
    if (event.persisted) hideLoader({ immediate: true });
  });

  document.addEventListener("click", (event) => {
    if (
      event.defaultPrevented ||
      event.button !== 0 ||
      event.metaKey ||
      event.ctrlKey ||
      event.shiftKey ||
      event.altKey
    ) return;

    const clickedElement = event.target instanceof Element
      ? event.target
      : event.target?.parentElement;
    const link = clickedElement?.closest("a[href]");
    if (
      !link ||
      link.hasAttribute("download") ||
      link.target && link.target.toLowerCase() !== "_self" ||
      link.hasAttribute("data-no-page-loader")
    ) return;

    let destination;
    try {
      destination = new URL(link.href, window.location.href);
    } catch {
      return;
    }

    if (
      destination.origin !== window.location.origin ||
      !["http:", "https:"].includes(destination.protocol)
    ) return;

    const sameDocument =
      destination.pathname === window.location.pathname &&
      destination.search === window.location.search;
    if (sameDocument) return;
    if (navigating) {
      event.preventDefault();
      return;
    }

    event.preventDefault();
    navigating = true;
    showLoader();
    navigationTimer = window.setTimeout(() => {
      window.location.assign(destination.href);
    }, navigationDelayMs);
  });
})();
