<?php
/**
 * Dynamic WordPress Navigation Walkers, Menu Icon Meta Fields, Fallbacks & Default Menu Seeder
 * Powers all 5 ALIFY Menu Locations via Appearance -> Menus:
 *   1. `primary`         — Desktop Header Navigation (with multi-item dropdown panels + icons)
 *   2. `mobile_drawer`   — Right-Side Mobile Slide Drawer (with collapsible accordion submenus + icons)
 *   3. `footer_nav`      — Footer Column 2 Quick Links
 *   4. `footer_services` — Footer Column 3 Services Links
 *   5. `footer_legal`    — Footer Bottom Bar Legal Links
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Infer or retrieve Font Awesome icon class for a submenu item.
 */
function alify_get_menu_item_icon( $item ) {
	if ( ! empty( $item->ID ) ) {
		$saved_icon = get_post_meta( (int) $item->ID, '_alify_menu_icon', true );
		if ( ! empty( $saved_icon ) ) {
			return sanitize_text_field( $saved_icon );
		}
	}

	// Support entering Font Awesome classes in WP's native "CSS Classes" menu field
	if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
		$fa_parts = array();
		foreach ( $item->classes as $cls ) {
			if ( 0 === strpos( $cls, 'fa-' ) ) {
				$fa_parts[] = sanitize_html_class( $cls );
			}
		}
		if ( ! empty( $fa_parts ) ) {
			if ( ! in_array( 'fa-solid', $fa_parts, true ) && ! in_array( 'fa-brands', $fa_parts, true ) && ! in_array( 'fa-regular', $fa_parts, true ) ) {
				array_unshift( $fa_parts, 'fa-solid' );
			}
			return implode( ' ', $fa_parts );
		}
	}

	// Smart fallback based on URL / title keywords
	$haystack = strtolower( ( isset( $item->url ) ? $item->url : '' ) . ' ' . ( isset( $item->title ) ? $item->title : '' ) );
	$keyword_icons = array(
		'overview'          => 'fa-solid fa-layer-group',
		'all services'      => 'fa-solid fa-layer-group',
		'web-development'   => 'fa-solid fa-code',
		'web design'        => 'fa-solid fa-code',
		'ui-ux'             => 'fa-solid fa-pen-nib',
		'brand'             => 'fa-solid fa-pen-nib',
		'digital-marketing' => 'fa-solid fa-chart-line',
		'seo'               => 'fa-solid fa-chart-line',
		'ecommerce'         => 'fa-solid fa-cart-shopping',
		'e-commerce'        => 'fa-solid fa-cart-shopping',
		'design-system'     => 'fa-solid fa-cubes',
		'motion'            => 'fa-solid fa-wand-magic-sparkles',
		'conversion'        => 'fa-solid fa-bullseye',
		'ai-product'        => 'fa-solid fa-microchip',
		'testimonial'       => 'fa-solid fa-star',
		'faq'               => 'fa-solid fa-circle-question',
		'book-call'         => 'fa-solid fa-calendar-check',
		'strategy call'     => 'fa-solid fa-calendar-check',
		'about'             => 'fa-solid fa-building',
		'pricing'           => 'fa-solid fa-tags',
		'work'              => 'fa-solid fa-briefcase',
		'case-stud'         => 'fa-solid fa-chart-pie',
		'industr'           => 'fa-solid fa-globe',
		'blog'              => 'fa-solid fa-newspaper',
		'contact'           => 'fa-solid fa-envelope',
	);

	foreach ( $keyword_icons as $needle => $icon_cls ) {
		if ( false !== strpos( $haystack, $needle ) ) {
			return $icon_cls;
		}
	}

	return 'fa-solid fa-arrow-right';
}

/**
 * Determine whether a menu item corresponds to the active route (including detail-to-parent mapping).
 */
function alify_is_menu_item_active( $item ) {
	$classes = ( ! empty( $item->classes ) && is_array( $item->classes ) ) ? $item->classes : array();
	$wp_active_flags = array(
		'current-menu-item',
		'current-menu-parent',
		'current-menu-ancestor',
		'current_page_item',
		'current_page_parent',
		'current_page_ancestor',
	);

	foreach ( $wp_active_flags as $flag ) {
		if ( in_array( $flag, $classes, true ) ) {
			return true;
		}
	}

	$route = alify_current_route_slug();
	$parent_map = array(
		'service-single'    => 'services',
		'industry-single'   => 'industries',
		'work-single'       => 'work',
		'case-study-single' => 'case-studies',
		'blog-single'       => 'blog',
	);
	$effective_slug = isset( $parent_map[ $route ] ) ? $parent_map[ $route ] : $route;

	if ( empty( $item->url ) ) {
		return false;
	}

	$item_path = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );
	$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( 'index' === $effective_slug ) {
		return $item_path === $home_path;
	}

	$segments  = empty( $item_path ) ? array() : explode( '/', $item_path );
	$last_seg  = end( $segments );
	$last_slug = preg_replace( '/\.html$/i', '', (string) $last_seg );

	return ( ! empty( $last_slug ) && ( $last_slug === $route || $last_slug === $effective_slug ) );
}

/**
 * 1. Desktop Primary Header Walker (`primary` location)
 */
class ALIFY_Desktop_Nav_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '<div class="dropdown-panel" role="menu"><ul class="dropdown-list">';
		} else {
			$output .= '<ul class="dropdown-list">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</ul></div>';
		} else {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item         = $data_object;
		$classes      = ( ! empty( $item->classes ) && is_array( $item->classes ) ) ? $item->classes : array();
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$is_active    = alify_is_menu_item_active( $item );

		$url    = ! empty( $item->url ) ? $item->url : '#';
		$title  = apply_filters( 'the_title', $item->title, $item->ID );
		$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$rel    = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : ( '_blank' === $item->target ? ' rel="noopener noreferrer"' : '' );
		$attr_t = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';

		if ( 0 === $depth ) {
			$li_cls  = 'header-nav-item' . ( $has_children ? ' has-dropdown' : '' );
			$lnk_cls = 'header-nav-link' . ( $is_active ? ' is-active' : '' );
			$output .= '<li class="' . esc_attr( $li_cls ) . '">';

			if ( $has_children ) {
				$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $lnk_cls ) . '" aria-expanded="false" aria-haspopup="true"' . $target . $rel . $attr_t . '>';
				$output .= '<span>' . esc_html( $title ) . '</span>';
				$output .= '<i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>';
				$output .= '</a>';
			} else {
				$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $lnk_cls ) . '"' . $target . $rel . $attr_t . '>' . esc_html( $title ) . '</a>';
			}
		} else {
			$icon_cls = alify_get_menu_item_icon( $item );
			$sub_cls  = 'dropdown-item-link' . ( $is_active ? ' is-active' : '' );
			$output  .= '<li>';
			$output  .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $sub_cls ) . '" role="menuitem"' . $target . $rel . $attr_t . '>';
			$output  .= '<span class="dropdown-item-icon"><i class="' . esc_attr( $icon_cls ) . '" aria-hidden="true"></i></span>';
			$output  .= '<span>' . esc_html( $title ) . '</span>';
			$output  .= '</a>';
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * 2. Mobile Slide Drawer Walker (`mobile_drawer` location)
 */
class ALIFY_Mobile_Drawer_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '<div class="drawer-submenu-wrap"><ul class="drawer-submenu">';
		} else {
			$output .= '<ul class="drawer-submenu">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</ul></div>';
		} else {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item         = $data_object;
		$classes      = ( ! empty( $item->classes ) && is_array( $item->classes ) ) ? $item->classes : array();
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$is_active    = alify_is_menu_item_active( $item );

		$url    = ! empty( $item->url ) ? $item->url : '#';
		$title  = apply_filters( 'the_title', $item->title, $item->ID );
		$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$rel    = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : ( '_blank' === $item->target ? ' rel="noopener noreferrer"' : '' );

		if ( 0 === $depth ) {
			$li_cls  = 'drawer-nav-item' . ( $has_children ? ' has-submenu' : '' );
			$output .= '<li class="' . esc_attr( $li_cls ) . '">';

			if ( $has_children ) {
				$btn_cls = 'drawer-dropdown-toggle' . ( $is_active ? ' is-active' : '' );
				$output .= '<button type="button" class="' . esc_attr( $btn_cls ) . '" aria-expanded="false">';
				$output .= '<span>' . esc_html( $title ) . '</span>';
				$output .= '<i class="fa-solid fa-chevron-down drawer-chevron" aria-hidden="true"></i>';
				$output .= '</button>';
			} else {
				$lnk_cls = 'drawer-nav-link' . ( $is_active ? ' is-active' : '' );
				$output .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $lnk_cls ) . '"' . $target . $rel . '>' . esc_html( $title ) . '</a>';
			}
		} else {
			$icon_cls = alify_get_menu_item_icon( $item );
			$sub_cls  = 'drawer-submenu-link' . ( $is_active ? ' is-active' : '' );
			$output  .= '<li>';
			$output  .= '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $sub_cls ) . '"' . $target . $rel . '>';
			$output  .= '<span class="drawer-submenu-icon"><i class="' . esc_attr( $icon_cls ) . '" aria-hidden="true"></i></span>';
			$output  .= '<span>' . esc_html( $title ) . '</span>';
			$output  .= '</a>';
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * 3. Footer Column Link List Walker (`footer_nav` & `footer_services` locations)
 */
class ALIFY_Footer_Link_Walker extends Walker_Nav_Menu {

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item   = $data_object;
		$url    = ! empty( $item->url ) ? $item->url : '#';
		$title  = apply_filters( 'the_title', $item->title, $item->ID );
		$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$rel    = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : ( '_blank' === $item->target ? ' rel="noopener noreferrer"' : '' );

		$output .= '<li><a href="' . esc_url( $url ) . '" class="footer-link"' . $target . $rel . '>' . esc_html( $title ) . '</a>';
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * 4. Footer Bottom Legal Links Walker (`footer_legal` location — flat inline anchors)
 */
class ALIFY_Footer_Legal_Walker extends Walker_Nav_Menu {

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item   = $data_object;
		$url    = ! empty( $item->url ) ? $item->url : '#';
		$title  = apply_filters( 'the_title', $item->title, $item->ID );
		$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$rel    = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : ( '_blank' === $item->target ? ' rel="noopener noreferrer"' : '' );

		$output .= '<a href="' . esc_url( $url ) . '" class="footer-legal-link"' . $target . $rel . '>' . esc_html( $title ) . '</a>';
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		// Flat anchors inside .footer-bottom-right
	}
}

/**
 * Add Custom Font Awesome Icon Field in WP Admin -> Appearance -> Menus
 */
function alify_menu_item_icon_field( $item_id, $item, $depth, $args ) {
	$icon_val = get_post_meta( $item_id, '_alify_menu_icon', true );
	?>
	<p class="field-alify-icon description description-wide">
		<label for="edit-menu-item-alify-icon-<?php echo esc_attr( $item_id ); ?>">
			<?php esc_html_e( 'ALIFY Font Awesome Icon Class (Submenu)', 'alify' ); ?><br />
			<input
				type="text"
				id="edit-menu-item-alify-icon-<?php echo esc_attr( $item_id ); ?>"
				class="widefat code edit-menu-item-custom"
				name="menu-item-alify-icon[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $icon_val ); ?>"
				placeholder="e.g. fa-solid fa-code"
			/>
		</label>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'alify_menu_item_icon_field', 10, 4 );

function alify_save_menu_item_icon_field( $menu_id, $menu_item_db_id ) {
	if ( isset( $_POST['menu-item-alify-icon'][ $menu_item_db_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sanitized = sanitize_text_field( wp_unslash( $_POST['menu-item-alify-icon'][ $menu_item_db_id ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $sanitized ) ) {
			update_post_meta( $menu_item_db_id, '_alify_menu_icon', $sanitized );
		} else {
			delete_post_meta( $menu_item_db_id, '_alify_menu_icon' );
		}
	}
}
add_action( 'wp_update_nav_menu_item', 'alify_save_menu_item_icon_field', 10, 2 );

/**
 * Fallback Renderers (Used whenever a menu location has not been assigned yet)
 */
function alify_fallback_primary_menu() {
	$route = alify_current_route_slug();
	$nav_map = array(
		'service-single'    => 'services',
		'industry-single'   => 'industries',
		'work-single'       => 'work',
		'case-study-single' => 'case-studies',
		'blog-single'       => 'blog',
		'testimonials'      => 'about',
		'faq'               => 'about',
		'book-call'         => 'about',
	);
	$active = isset( $nav_map[ $route ] ) ? $nav_map[ $route ] : $route;
	?>
	<ul class="header-nav-list">
		<li class="header-nav-item">
			<a href="<?php echo esc_url( alify_page_url( 'home' ) ); ?>" class="header-nav-link<?php echo 'index' === $active ? ' is-active' : ''; ?>">Home</a>
		</li>
		<li class="header-nav-item has-dropdown">
			<a href="<?php echo esc_url( alify_page_url( 'about' ) ); ?>" class="header-nav-link<?php echo 'about' === $active ? ' is-active' : ''; ?>" aria-expanded="false" aria-haspopup="true">
				<span>About Us</span>
				<i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>
			</a>
			<div class="dropdown-panel" role="menu">
				<ul class="dropdown-list">
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'about' ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
							<span>About ALIFY Studio</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'testimonials' ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
							<span>Client Testimonials</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'faq' ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span>
							<span>FAQs Knowledge Hub</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'book-call' ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
							<span>Book a Strategy Call</span>
						</a>
					</li>
				</ul>
			</div>
		</li>
		<li class="header-nav-item has-dropdown">
			<a href="<?php echo esc_url( alify_page_url( 'services' ) ); ?>" class="header-nav-link<?php echo 'services' === $active ? ' is-active' : ''; ?>" aria-expanded="false" aria-haspopup="true">
				<span>Services</span>
				<i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>
			</a>
			<div class="dropdown-panel" role="menu">
				<ul class="dropdown-list">
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'web-development' ) ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-code" aria-hidden="true"></i></span>
							<span>Web Design &amp; Development</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'ui-ux-branding' ) ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-pen-nib" aria-hidden="true"></i></span>
							<span>UI/UX &amp; Brand Identity</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'digital-marketing' ) ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
							<span>Digital Marketing &amp; SEO</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'ecommerce' ) ) ); ?>" class="dropdown-item-link" role="menuitem">
							<span class="dropdown-item-icon"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
							<span>E-Commerce Solutions</span>
						</a>
					</li>
				</ul>
			</div>
		</li>
		<li class="header-nav-item"><a href="<?php echo esc_url( alify_page_url( 'industries' ) ); ?>" class="header-nav-link<?php echo 'industries' === $active ? ' is-active' : ''; ?>">Industries</a></li>
		<li class="header-nav-item"><a href="<?php echo esc_url( alify_page_url( 'work' ) ); ?>" class="header-nav-link<?php echo 'work' === $active ? ' is-active' : ''; ?>">Work</a></li>
		<li class="header-nav-item"><a href="<?php echo esc_url( alify_page_url( 'pricing' ) ); ?>" class="header-nav-link<?php echo 'pricing' === $active ? ' is-active' : ''; ?>">Pricing</a></li>
		<li class="header-nav-item"><a href="<?php echo esc_url( alify_page_url( 'blog' ) ); ?>" class="header-nav-link<?php echo 'blog' === $active ? ' is-active' : ''; ?>">Blog</a></li>
		<li class="header-nav-item"><a href="<?php echo esc_url( alify_page_url( 'case-studies' ) ); ?>" class="header-nav-link<?php echo 'case-studies' === $active ? ' is-active' : ''; ?>">Case Studies</a></li>
	</ul>
	<?php
}

function alify_fallback_drawer_menu() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'drawer-nav-list',
				'depth'          => 2,
				'walker'         => new ALIFY_Mobile_Drawer_Walker(),
			)
		);
		return;
	}

	$route = alify_current_route_slug();
	?>
	<ul class="drawer-nav-list">
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'home' ) ); ?>" class="drawer-nav-link<?php echo 'index' === $route ? ' is-active' : ''; ?>">Home</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'about' ) ); ?>" class="drawer-nav-link<?php echo 'about' === $route ? ' is-active' : ''; ?>">About Us</a></li>
		<li class="drawer-nav-item has-submenu">
			<button type="button" class="drawer-dropdown-toggle" aria-expanded="false">
				<span>Services</span>
				<i class="fa-solid fa-chevron-down drawer-chevron" aria-hidden="true"></i>
			</button>
			<div class="drawer-submenu-wrap">
				<ul class="drawer-submenu">
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'services' ) ); ?>" class="drawer-submenu-link">
							<span class="drawer-submenu-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
							<span>All Services Overview</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'web-development' ) ) ); ?>" class="drawer-submenu-link">
							<span class="drawer-submenu-icon"><i class="fa-solid fa-code" aria-hidden="true"></i></span>
							<span>Web Design &amp; Development</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'ui-ux-branding' ) ) ); ?>" class="drawer-submenu-link">
							<span class="drawer-submenu-icon"><i class="fa-solid fa-pen-nib" aria-hidden="true"></i></span>
							<span>UI/UX &amp; Brand Identity</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'digital-marketing' ) ) ); ?>" class="drawer-submenu-link">
							<span class="drawer-submenu-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
							<span>Digital Marketing &amp; SEO</span>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( alify_page_url( 'service-single', array( 'service' => 'ecommerce' ) ) ); ?>" class="drawer-submenu-link">
							<span class="drawer-submenu-icon"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
							<span>E-Commerce Solutions</span>
						</a>
					</li>
				</ul>
			</div>
		</li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'industries' ) ); ?>" class="drawer-nav-link<?php echo 'industries' === $route ? ' is-active' : ''; ?>">Industries</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'work' ) ); ?>" class="drawer-nav-link<?php echo 'work' === $route ? ' is-active' : ''; ?>">Work</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'pricing' ) ); ?>" class="drawer-nav-link<?php echo 'pricing' === $route ? ' is-active' : ''; ?>">Pricing</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'blog' ) ); ?>" class="drawer-nav-link<?php echo 'blog' === $route ? ' is-active' : ''; ?>">Blog</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'case-studies' ) ); ?>" class="drawer-nav-link<?php echo 'case-studies' === $route ? ' is-active' : ''; ?>">Case Studies</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'faq' ) ); ?>" class="drawer-nav-link<?php echo 'faq' === $route ? ' is-active' : ''; ?>">FAQs</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'book-call' ) ); ?>" class="drawer-nav-link<?php echo 'book-call' === $route ? ' is-active' : ''; ?>">Book a Strategy Call</a></li>
		<li class="drawer-nav-item"><a href="<?php echo esc_url( alify_page_url( 'testimonials' ) ); ?>" class="drawer-nav-link<?php echo 'testimonials' === $route ? ' is-active' : ''; ?>">Testimonials</a></li>
	</ul>
	<?php
}

function alify_fallback_footer_nav() {
	$links = array(
		'home'         => 'Home',
		'about'        => 'About Us',
		'services'     => 'Services',
		'industries'   => 'Industries',
		'work'         => 'Work',
		'pricing'      => 'Pricing',
		'blog'         => 'Blog',
		'case-studies' => 'Case Studies',
		'testimonials' => 'Testimonials',
		'faq'          => 'FAQs',
		'book-call'    => 'Book a Strategy Call',
	);
	echo '<ul class="footer-link-list">';
	foreach ( $links as $slug => $label ) {
		echo '<li><a href="' . esc_url( alify_page_url( $slug ) ) . '" class="footer-link">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

function alify_fallback_footer_services() {
	$services = array(
		'web-development'   => 'Web Design & Development',
		'ui-ux-branding'    => 'UI/UX & Brand Identity',
		'design-systems'    => 'Design Systems & UI Kits',
		'digital-marketing' => 'Digital Marketing & SEO',
		'ecommerce'         => 'E-Commerce Solutions',
	);
	echo '<ul class="footer-link-list">';
	foreach ( $services as $svc_slug => $label ) {
		echo '<li><a href="' . esc_url( alify_page_url( 'service-single', array( 'service' => $svc_slug ) ) ) . '" class="footer-link">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

function alify_fallback_footer_legal() {
	echo '<a href="' . esc_url( alify_page_url( 'privacy-policy' ) ) . '" class="footer-legal-link">Privacy Policy</a>';
	echo '<a href="' . esc_url( alify_page_url( 'terms-of-service' ) ) . '" class="footer-legal-link">Terms of Service</a>';
}

/**
 * Helper to add a menu item (page or custom URL) + optional icon meta.
 */
function alify_add_seeded_menu_item( $menu_id, $title, $slug_or_url, $parent_id = 0, $icon = '', $query_args = array() ) {
	$page_obj = ( 0 !== strpos( $slug_or_url, 'http' ) ) ? get_page_by_path( ( 'home' === $slug_or_url ) ? 'home' : $slug_or_url ) : null;

	if ( $page_obj && empty( $query_args ) ) {
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-object-id' => $page_obj->ID,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	} else {
		$url     = ( 0 === strpos( $slug_or_url, 'http' ) ) ? $slug_or_url : alify_page_url( $slug_or_url, $query_args );
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-url'       => $url,
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	}

	if ( ! is_wp_error( $item_id ) && $item_id > 0 && ! empty( $icon ) ) {
		update_post_meta( $item_id, '_alify_menu_icon', $icon );
	}

	return is_wp_error( $item_id ) ? 0 : (int) $item_id;
}

/**
 * Seed & Assign All 5 Default WordPress Menus so Appearance -> Menus is Pre-Populated Out-of-the-Box!
 */
function alify_seed_default_menus() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}

	// 1. Primary Header Navigation
	if ( empty( $locations['primary'] ) || ! wp_get_nav_menu_object( $locations['primary'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'ALIFY Primary Header' );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : (int) wp_create_nav_menu( 'ALIFY Primary Header' );

		if ( $menu_id > 0 && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			alify_add_seeded_menu_item( $menu_id, 'Home', 'home' );

			$about_parent = alify_add_seeded_menu_item( $menu_id, 'About Us', 'about' );
			alify_add_seeded_menu_item( $menu_id, 'About ALIFY Studio', 'about', $about_parent, 'fa-solid fa-building' );
			alify_add_seeded_menu_item( $menu_id, 'Client Testimonials', 'testimonials', $about_parent, 'fa-solid fa-star' );
			alify_add_seeded_menu_item( $menu_id, 'FAQs Knowledge Hub', 'faq', $about_parent, 'fa-solid fa-circle-question' );
			alify_add_seeded_menu_item( $menu_id, 'Book a Strategy Call', 'book-call', $about_parent, 'fa-solid fa-calendar-check' );

			$svc_parent = alify_add_seeded_menu_item( $menu_id, 'Services', 'services' );
			alify_add_seeded_menu_item( $menu_id, 'Web Design & Development', 'service-single', $svc_parent, 'fa-solid fa-code', array( 'service' => 'web-development' ) );
			alify_add_seeded_menu_item( $menu_id, 'UI/UX & Brand Identity', 'service-single', $svc_parent, 'fa-solid fa-pen-nib', array( 'service' => 'ui-ux-branding' ) );
			alify_add_seeded_menu_item( $menu_id, 'Digital Marketing & SEO', 'service-single', $svc_parent, 'fa-solid fa-chart-line', array( 'service' => 'digital-marketing' ) );
			alify_add_seeded_menu_item( $menu_id, 'E-Commerce Solutions', 'service-single', $svc_parent, 'fa-solid fa-cart-shopping', array( 'service' => 'ecommerce' ) );

			alify_add_seeded_menu_item( $menu_id, 'Industries', 'industries' );
			alify_add_seeded_menu_item( $menu_id, 'Work', 'work' );
			alify_add_seeded_menu_item( $menu_id, 'Pricing', 'pricing' );
			alify_add_seeded_menu_item( $menu_id, 'Blog', 'blog' );
			alify_add_seeded_menu_item( $menu_id, 'Case Studies', 'case-studies' );
		}
		if ( $menu_id > 0 ) {
			$locations['primary'] = $menu_id;
		}
	}

	// 2. Mobile Slide Drawer Navigation
	if ( empty( $locations['mobile_drawer'] ) || ! wp_get_nav_menu_object( $locations['mobile_drawer'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'ALIFY Mobile Drawer' );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : (int) wp_create_nav_menu( 'ALIFY Mobile Drawer' );

		if ( $menu_id > 0 && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			alify_add_seeded_menu_item( $menu_id, 'Home', 'home' );
			alify_add_seeded_menu_item( $menu_id, 'About Us', 'about' );

			$svc_parent = alify_add_seeded_menu_item( $menu_id, 'Services', 'services' );
			alify_add_seeded_menu_item( $menu_id, 'All Services Overview', 'services', $svc_parent, 'fa-solid fa-layer-group' );
			alify_add_seeded_menu_item( $menu_id, 'Web Design & Development', 'service-single', $svc_parent, 'fa-solid fa-code', array( 'service' => 'web-development' ) );
			alify_add_seeded_menu_item( $menu_id, 'UI/UX & Brand Identity', 'service-single', $svc_parent, 'fa-solid fa-pen-nib', array( 'service' => 'ui-ux-branding' ) );
			alify_add_seeded_menu_item( $menu_id, 'Digital Marketing & SEO', 'service-single', $svc_parent, 'fa-solid fa-chart-line', array( 'service' => 'digital-marketing' ) );
			alify_add_seeded_menu_item( $menu_id, 'E-Commerce Solutions', 'service-single', $svc_parent, 'fa-solid fa-cart-shopping', array( 'service' => 'ecommerce' ) );

			alify_add_seeded_menu_item( $menu_id, 'Industries', 'industries' );
			alify_add_seeded_menu_item( $menu_id, 'Work', 'work' );
			alify_add_seeded_menu_item( $menu_id, 'Pricing', 'pricing' );
			alify_add_seeded_menu_item( $menu_id, 'Blog', 'blog' );
			alify_add_seeded_menu_item( $menu_id, 'Case Studies', 'case-studies' );
			alify_add_seeded_menu_item( $menu_id, 'FAQs', 'faq' );
			alify_add_seeded_menu_item( $menu_id, 'Book a Strategy Call', 'book-call' );
			alify_add_seeded_menu_item( $menu_id, 'Testimonials', 'testimonials' );
		}
		if ( $menu_id > 0 ) {
			$locations['mobile_drawer'] = $menu_id;
		}
	}

	// 3. Footer Quick Links
	if ( empty( $locations['footer_nav'] ) || ! wp_get_nav_menu_object( $locations['footer_nav'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'ALIFY Footer Navigation' );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : (int) wp_create_nav_menu( 'ALIFY Footer Navigation' );

		if ( $menu_id > 0 && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			$footer_pages = array(
				'home'         => 'Home',
				'about'        => 'About Us',
				'services'     => 'Services',
				'industries'   => 'Industries',
				'work'         => 'Work',
				'pricing'      => 'Pricing',
				'blog'         => 'Blog',
				'case-studies' => 'Case Studies',
				'testimonials' => 'Testimonials',
				'faq'          => 'FAQs',
				'book-call'    => 'Book a Strategy Call',
			);
			foreach ( $footer_pages as $slug => $label ) {
				alify_add_seeded_menu_item( $menu_id, $label, $slug );
			}
		}
		if ( $menu_id > 0 ) {
			$locations['footer_nav'] = $menu_id;
		}
	}

	// 4. Footer Services Links
	if ( empty( $locations['footer_services'] ) || ! wp_get_nav_menu_object( $locations['footer_services'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'ALIFY Footer Services' );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : (int) wp_create_nav_menu( 'ALIFY Footer Services' );

		if ( $menu_id > 0 && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			$footer_svcs = array(
				'web-development'   => 'Web Design & Development',
				'ui-ux-branding'    => 'UI/UX & Brand Identity',
				'design-systems'    => 'Design Systems & UI Kits',
				'digital-marketing' => 'Digital Marketing & SEO',
				'ecommerce'         => 'E-Commerce Solutions',
			);
			foreach ( $footer_svcs as $svc_slug => $label ) {
				alify_add_seeded_menu_item( $menu_id, $label, 'service-single', 0, '', array( 'service' => $svc_slug ) );
			}
		}
		if ( $menu_id > 0 ) {
			$locations['footer_services'] = $menu_id;
		}
	}

	// 5. Footer Bottom Legal Links
	if ( empty( $locations['footer_legal'] ) || ! wp_get_nav_menu_object( $locations['footer_legal'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'ALIFY Footer Legal' );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : (int) wp_create_nav_menu( 'ALIFY Footer Legal' );

		if ( $menu_id > 0 && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			alify_add_seeded_menu_item( $menu_id, 'Privacy Policy', 'privacy-policy' );
			alify_add_seeded_menu_item( $menu_id, 'Terms of Service', 'terms-of-service' );
		}
		if ( $menu_id > 0 ) {
			$locations['footer_legal'] = $menu_id;
		}
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}
