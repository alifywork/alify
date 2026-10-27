<div
      id="workLightbox"
      class="work-lightbox"
      role="dialog"
      aria-modal="true"
      aria-hidden="true"
      aria-labelledby="workLightboxTitle"
    >
      <div class="work-lightbox-backdrop" id="workLightboxBackdrop"></div>

      <div class="work-lightbox-dialog">
        <!-- Top Bar -->
        <div class="work-lightbox-topbar">
          <div class="work-lightbox-meta-pill">
            <span class="work-lightbox-counter" id="workLightboxCounter">01 / 06</span>
            <span class="work-lightbox-divider" aria-hidden="true"></span>
            <span class="work-lightbox-tag" id="workLightboxTag">Flagship Platform</span>
          </div>

          <div class="work-lightbox-actions">
            <button
              type="button"
              class="work-lightbox-icon-btn"
              id="workLightboxZoom"
              aria-label="Toggle image zoom"
              title="Zoom in / out"
            >
              <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
            </button>
            <button
              type="button"
              class="work-lightbox-icon-btn is-close"
              id="workLightboxClose"
              aria-label="Close gallery lightbox"
              title="Close (Esc)"
            >
              <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <!-- Center Stage -->
        <div class="work-lightbox-stage" id="workLightboxStage">
          <button
            type="button"
            class="work-lightbox-nav is-prev"
            id="workLightboxPrev"
            aria-label="Previous project image"
          >
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          </button>

          <figure class="work-lightbox-figure" id="workLightboxFigure">
            <img
              id="workLightboxImg"
              class="work-lightbox-img"
              src="assets/images/projects/project-finpulse.jpg"
              alt="Institutional Treasury Command Interface"
            />
          </figure>

          <button
            type="button"
            class="work-lightbox-nav is-next"
            id="workLightboxNext"
            aria-label="Next project image"
          >
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </button>
        </div>

        <!-- Bottom Caption & Thumbnail Strip -->
        <div class="work-lightbox-footer">
          <div class="work-lightbox-caption-wrap">
            <h3 class="work-lightbox-title" id="workLightboxTitle">
              Institutional Treasury Command Interface
            </h3>
            <p class="work-lightbox-caption" id="workLightboxCaption">
              Real-time multi-currency liquidity telemetry and sub-second market depth visualization.
            </p>
          </div>

          <div class="work-lightbox-thumbs" id="workLightboxThumbs" role="tablist" aria-label="Project gallery thumbnails"></div>
        </div>
      </div>
    </div>
