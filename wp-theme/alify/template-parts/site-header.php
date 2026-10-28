<?php
/**
 * Global Site Header & Right-Side Mobile Slide Drawer Partial
 * Powered dynamically by WordPress `wp_nav_menu()` (`primary` & `mobile_drawer` locations)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alify_contact_label = get_theme_mod( 'alify_contact_btn_text', 'Contact Us' );
$alify_cta_label     = get_theme_mod( 'alify_cta_text', 'Start a Project' );
$alify_contact_url   = alify_page_url( 'contact' );
$alify_start_url     = alify_page_url( 'start-project' );
?>
<header class="site-header">
	<div class="container">
		<div class="row header-row">
			<!-- Brand Logo Column -->
			<div class="col-6 col-xl-auto header-logo-col">
				<?php alify_render_brand_logo( 'brand-logo', 'brand-logo-mark' ); ?>
			</div>

			<!-- Desktop Navigation Column (`primary` Menu Location) -->
			<div class="col-xl header-nav-col">
				<nav class="header-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'alify' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'header-nav-list',
							'depth'          => 2,
							'walker'         => new ALIFY_Desktop_Nav_Walker(),
							'fallback_cb'    => 'alify_fallback_primary_menu',
						)
					);
					?>
				</nav>
			</div>

			<!-- Header Actions & Mobile Drawer Trigger Column -->
			<div class="col-6 col-xl-auto header-actions-col">
				<div class="header-actions header-desktop-ctas">
					<a href="<?php echo esc_url( $alify_contact_url ); ?>" class="btn-contact"><?php echo esc_html( $alify_contact_label ); ?></a>
					<a href="<?php echo esc_url( $alify_start_url ); ?>" class="btn-start-project">
						<span class="btn-start-pill"><?php echo esc_html( $alify_cta_label ); ?></span>
						<span class="btn-start-circle" aria-hidden="true">
							<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
						</span>
					</a>
				</div>

				<button
					type="button"
					class="mobile-menu-toggle"
					aria-label="<?php esc_attr_e( 'Open Menu', 'alify' ); ?>"
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

<!-- Right-Side Slide Drawer Menu (`mobile_drawer` Menu Location) -->
<aside
	class="mobile-drawer"
	id="mobileDrawer"
	aria-label="<?php esc_attr_e( 'Mobile Navigation Drawer', 'alify' ); ?>"
	aria-hidden="true"
>
	<div class="drawer-header">
		<?php alify_render_brand_logo( 'brand-logo', 'brand-logo-mark' ); ?>
		<button type="button" class="drawer-close" aria-label="<?php esc_attr_e( 'Close Menu', 'alify' ); ?>">
			<i class="fa-solid fa-xmark" aria-hidden="true"></i>
		</button>
	</div>

	<div class="drawer-body">
		<nav aria-label="<?php esc_attr_e( 'Mobile Navigation', 'alify' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'mobile_drawer',
					'container'      => false,
					'menu_class'     => 'drawer-nav-list',
					'depth'          => 2,
					'walker'         => new ALIFY_Mobile_Drawer_Walker(),
					'fallback_cb'    => 'alify_fallback_drawer_menu',
				)
			);
			?>
		</nav>
	</div>

	<div class="drawer-footer">
		<a href="<?php echo esc_url( $alify_contact_url ); ?>" class="btn-contact"><?php echo esc_html( $alify_contact_label ); ?></a>
		<a href="<?php echo esc_url( $alify_start_url ); ?>" class="btn-start-project">
			<span class="btn-start-pill"><?php echo esc_html( $alify_cta_label ); ?></span>
			<span class="btn-start-circle" aria-hidden="true">
				<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
			</span>
		</a>
	</div>
</aside>
