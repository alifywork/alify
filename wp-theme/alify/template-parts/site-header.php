<header class="site-header">
      <div class="container">
        <div class="row header-row">
          <!-- Brand Logo Column -->
          <div class="col-6 col-xl-auto header-logo-col">
            <a href="index.html" class="brand-logo" aria-label="ALIFY Home">
              <span class="brand-logo-mark" aria-hidden="true">△</span>LIFY
            </a>
          </div>

          <!-- Desktop Navigation Column -->
          <div class="col-xl header-nav-col">
            <nav class="header-nav" aria-label="Primary Navigation">
              <ul class="header-nav-list">
                <li class="header-nav-item">
                  <a href="index.html" class="header-nav-link">Home</a>
                </li>
                <li class="header-nav-item has-dropdown">
                  <a
                    href="about.html"
                    class="header-nav-link"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <span>About Us</span>
                    <i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>
                  </a>
                  <div class="dropdown-panel" role="menu">
                    <ul class="dropdown-list">
                      <li>
                        <a href="about.html" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                          <span>About ALIFY Studio</span>
                        </a>
                      </li>
                      <li>
                        <a href="testimonials.html" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                          <span>Client Testimonials</span>
                        </a>
                      </li>
                      <li>
                        <a href="faq.html" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span>
                          <span>FAQs Knowledge Hub</span>
                        </a>
                      </li>
                      <li>
                        <a href="book-call.html" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
                          <span>Book a Strategy Call</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </li>
                <li class="header-nav-item has-dropdown">
                  <a
                    href="services.html"
                    class="header-nav-link"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <span>Services</span>
                    <i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>
                  </a>
                  <div class="dropdown-panel" role="menu">
                    <ul class="dropdown-list">
                      <li>
                        <a href="service-single.html?service=web-development" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon">
                            <i class="fa-solid fa-code" aria-hidden="true"></i>
                          </span>
                          <span>Web Design &amp; Development</span>
                        </a>
                      </li>
                      <li>
                        <a href="service-single.html?service=ui-ux-branding" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon">
                            <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
                          </span>
                          <span>UI/UX &amp; Brand Identity</span>
                        </a>
                      </li>
                      <li>
                        <a href="service-single.html?service=digital-marketing" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon">
                            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                          </span>
                          <span>Digital Marketing &amp; SEO</span>
                        </a>
                      </li>
                      <li>
                        <a href="service-single.html?service=ecommerce" class="dropdown-item-link" role="menuitem">
                          <span class="dropdown-item-icon">
                            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                          </span>
                          <span>E-Commerce Solutions</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </li>
                <li class="header-nav-item">
                  <a href="industries.html" class="header-nav-link">Industries</a>
                </li>
                <li class="header-nav-item">
                  <a href="work.html" class="header-nav-link">Work</a>
                </li>
                <li class="header-nav-item">
                  <a href="pricing.html" class="header-nav-link">Pricing</a>
                </li>
                <li class="header-nav-item">
                  <a href="blog.html" class="header-nav-link">Blog</a>
                </li>
                <li class="header-nav-item">
                  <a href="case-studies.html" class="header-nav-link">Case Studies</a>
                </li>
              </ul>
            </nav>
          </div>

          <!-- Header Actions & Mobile Drawer Trigger Column -->
          <div class="col-6 col-xl-auto header-actions-col">
            <div class="header-actions header-desktop-ctas">
              <a href="contact.html" class="btn-contact">Contact Us</a>
              <a href="start-project.html" class="btn-start-project">
                <span class="btn-start-pill">Start a Project</span>
                <span class="btn-start-circle" aria-hidden="true">
                  <i class="fa-solid fa-arrow-up btn-start-arrow"></i>
                </span>
              </a>
            </div>

            <button
              type="button"
              class="mobile-menu-toggle"
              aria-label="Open Menu"
              aria-controls="mobileDrawer"
              aria-expanded="false"
            >
              <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Right-Side Slide Drawer Backdrop -->
    <div class="drawer-overlay" id="drawerOverlay"></div>

    <!-- Right-Side Slide Drawer Menu -->
    <aside
      class="mobile-drawer"
      id="mobileDrawer"
      aria-label="Mobile Navigation Drawer"
      aria-hidden="true"
    >
      <div class="drawer-header">
        <a href="index.html" class="brand-logo" aria-label="ALIFY Home">
          <span class="brand-logo-mark" aria-hidden="true">△</span>LIFY
        </a>
        <button type="button" class="drawer-close" aria-label="Close Menu">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>

      <div class="drawer-body">
        <nav aria-label="Mobile Navigation">
          <ul class="drawer-nav-list">
            <li class="drawer-nav-item">
              <a href="index.html" class="drawer-nav-link">Home</a>
            </li>
            <li class="drawer-nav-item">
              <a href="about.html" class="drawer-nav-link">About Us</a>
            </li>
            <li class="drawer-nav-item has-submenu">
              <button
                type="button"
                class="drawer-dropdown-toggle"
                aria-expanded="false"
              >
                <span>Services</span>
                <i class="fa-solid fa-chevron-down drawer-chevron" aria-hidden="true"></i>
              </button>
              <div class="drawer-submenu-wrap">
                <ul class="drawer-submenu">
                  <li>
                    <a href="services.html" class="drawer-submenu-link">
                      <span class="drawer-submenu-icon">
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                      </span>
                      <span>All Services Overview</span>
                    </a>
                  </li>
                  <li>
                    <a href="service-single.html?service=web-development" class="drawer-submenu-link">
                      <span class="drawer-submenu-icon">
                        <i class="fa-solid fa-code" aria-hidden="true"></i>
                      </span>
                      <span>Web Design &amp; Development</span>
                    </a>
                  </li>
                  <li>
                    <a href="service-single.html?service=ui-ux-branding" class="drawer-submenu-link">
                      <span class="drawer-submenu-icon">
                        <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
                      </span>
                      <span>UI/UX &amp; Brand Identity</span>
                    </a>
                  </li>
                  <li>
                    <a href="service-single.html?service=digital-marketing" class="drawer-submenu-link">
                      <span class="drawer-submenu-icon">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                      </span>
                      <span>Digital Marketing &amp; SEO</span>
                    </a>
                  </li>
                  <li>
                    <a href="service-single.html?service=ecommerce" class="drawer-submenu-link">
                      <span class="drawer-submenu-icon">
                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                      </span>
                      <span>E-Commerce Solutions</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="drawer-nav-item">
              <a href="industries.html" class="drawer-nav-link">Industries</a>
            </li>
            <li class="drawer-nav-item">
              <a href="work.html" class="drawer-nav-link">Work</a>
            </li>
            <li class="drawer-nav-item">
              <a href="pricing.html" class="drawer-nav-link">Pricing</a>
            </li>
            <li class="drawer-nav-item">
              <a href="blog.html" class="drawer-nav-link">Blog</a>
            </li>
            <li class="drawer-nav-item">
              <a href="case-studies.html" class="drawer-nav-link">Case Studies</a>
            </li>
            <li class="drawer-nav-item">
              <a href="faq.html" class="drawer-nav-link">FAQs</a>
            </li>
            <li class="drawer-nav-item">
              <a href="book-call.html" class="drawer-nav-link">Book a Strategy Call</a>
            </li>
            <li class="drawer-nav-item">
              <a href="testimonials.html" class="drawer-nav-link">Testimonials</a>
            </li>
          </ul>
        </nav>
      </div>

      <div class="drawer-footer">
        <a href="contact.html" class="btn-contact">Contact Us</a>
        <a href="start-project.html" class="btn-start-project">
          <span class="btn-start-pill">Start a Project</span>
          <span class="btn-start-circle" aria-hidden="true">
            <i class="fa-solid fa-arrow-up btn-start-arrow"></i>
          </span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
