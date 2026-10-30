// Static connector alignment only. Motion effects are intentionally disabled.
(() => {
  const timelineTracks = Array.from(
    document.querySelectorAll(".studio-process-track, .single-process-track")
  );

  const alignTimelineTracks = () => {
    timelineTracks.forEach((track) => {
      const row = track.parentElement;
      const columns = Array.from(row.children).filter((child) =>
        Array.from(child.classList).some((className) => className.startsWith("col-"))
      );
      if (columns.length < 2) return;

      const rowRect = row.getBoundingClientRect();
      const firstRect = columns[0].getBoundingClientRect();
      const lastRect = columns[columns.length - 1].getBoundingClientRect();
      const firstCenter = firstRect.left + firstRect.width / 2;
      const lastCenter = lastRect.left + lastRect.width / 2;
      track.style.left = `${firstCenter - rowRect.left}px`;
      track.style.right = "auto";
      track.style.width = `${Math.max(0, lastCenter - firstCenter)}px`;
    });
  };

  alignTimelineTracks();
  window.addEventListener("resize", () => requestAnimationFrame(alignTimelineTracks), {
    passive: true,
  });
  window.addEventListener("load", alignTimelineTracks, { once: true });
})();
