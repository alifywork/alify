<?php
/**
 * Enqueue Stylesheets, GSAP & Custom Scripts in Exact Load Order
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_file_ver( $rel ) {
	$path = ALIFY_THEME_DIR . '/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : ALIFY_THEME_VERSION;
}

function alify_enqueue_assets() {
	// 1. Core Vendor & Global Component CSS
	wp_enqueue_style( 'alify-bootstrap-grid', alify_asset_uri( 'assets/css/bootstrap-css/bootstrap.min.css' ), array(), alify_file_ver( 'assets/css/bootstrap-css/bootstrap.min.css' ) );
	wp_enqueue_style( 'alify-fontawesome', alify_asset_uri( 'assets/icons/css/all.min.css' ), array(), alify_file_ver( 'assets/icons/css/all.min.css' ) );
	wp_enqueue_style( 'alify-header', alify_asset_uri( 'assets/css/global-component-css/header.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/header.css' ) );
	wp_enqueue_style( 'alify-footer', alify_asset_uri( 'assets/css/global-component-css/footer.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/footer.css' ) );
	wp_enqueue_style( 'alify-ui-polish', alify_asset_uri( 'assets/css/global-component-css/ui-polish.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/ui-polish.css' ) );

	// 2. Reusable Component CSS (Shared + Template-Specific)
	$reusable_css = array(
		'inner-hero'   => 'assets/css/reusable-component-css/inner-hero.css',
		'services'     => 'assets/css/reusable-component-css/services.css',
		'industries'   => 'assets/css/reusable-component-css/industries.css',
		'projects'     => 'assets/css/reusable-component-css/projects.css',
		'case-studies' => 'assets/css/reusable-component-css/case-studies.css',
		'about-studio' => 'assets/css/reusable-component-css/about-studio.css',
		'single-pages' => 'assets/css/reusable-component-css/single-pages.css',
		'pricing'      => 'assets/css/reusable-component-css/pricing.css',
		'book-call'    => 'assets/css/reusable-component-css/book-call.css',
		'testimonials' => 'assets/css/reusable-component-css/testimonials.css',
		'faq'          => 'assets/css/reusable-component-css/faq.css',
		'blog'         => 'assets/css/reusable-component-css/blog.css',
		'forms'        => 'assets/css/reusable-component-css/forms.css',
	);

	foreach ( $reusable_css as $handle => $rel ) {
		wp_enqueue_style( 'alify-' . $handle, alify_asset_uri( $rel ), array( 'alify-bootstrap-grid' ), alify_file_ver( $rel ) );
	}

	// 3. Main Custom CSS (@font-face Garet, Montserrat, Poppins + `:root` Tokens) & Theme Root style.css
	wp_enqueue_style( 'alify-main-style', alify_asset_uri( 'assets/css/style.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/style.css' ) );
	wp_enqueue_style( 'alify-wp-style', get_stylesheet_uri(), array( 'alify-main-style' ), alify_file_ver( 'style.css' ) );

	// 4. Vendor & Global Scripts (Loaded in Footer)
	wp_enqueue_script( 'alify-bootstrap-bundle', alify_asset_uri( 'assets/js/bootstrap-js/bootstrap.bundle.min.js' ), array(), alify_file_ver( 'assets/js/bootstrap-js/bootstrap.bundle.min.js' ), true );
	wp_enqueue_script( 'alify-gsap', alify_asset_uri( 'assets/gsap/gsap.min.js' ), array(), alify_file_ver( 'assets/gsap/gsap.min.js' ), true );
	wp_enqueue_script( 'alify-scrolltrigger', alify_asset_uri( 'assets/gsap/ScrollTrigger.min.js' ), array( 'alify-gsap' ), alify_file_ver( 'assets/gsap/ScrollTrigger.min.js' ), true );

	wp_enqueue_script( 'alify-header-js', alify_asset_uri( 'assets/js/global-component-js/header.js' ), array(), alify_file_ver( 'assets/js/global-component-js/header.js' ), true );
	wp_enqueue_script( 'alify-footer-js', alify_asset_uri( 'assets/js/global-component-js/footer.js' ), array(), alify_file_ver( 'assets/js/global-component-js/footer.js' ), true );
	wp_enqueue_script( 'alify-ui-polish-js', alify_asset_uri( 'assets/js/global-component-js/ui-polish.js' ), array(), alify_file_ver( 'assets/js/global-component-js/ui-polish.js' ), true );

	// Localize WordPress runtime config for AJAX forms, URLs & asset paths
	wp_localize_script(
		'alify-ui-polish-js',
		'ALIFY_WP',
		array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'alify_ajax_nonce' ),
			'themeUri'        => untrailingslashit( ALIFY_THEME_URI ),
			'homeUrl'         => home_url( '/' ),
			'thankYouUrl'     => alify_page_url( 'thank-you' ),
			'bookCallUrl'     => alify_page_url( 'book-call' ),
			'startProjectUrl' => alify_page_url( 'start-project' ),
			'blogUrl'         => alify_page_url( 'blog' ),
			'workUrl'         => alify_page_url( 'work' ),
			'workSingleUrl'   => alify_page_url( 'work-single' ),
			'caseSingleUrl'   => alify_page_url( 'case-study-single' ),
			'serviceSingleUrl'=> alify_page_url( 'service-single' ),
			'blogSingleUrl'   => alify_page_url( 'blog-single' ),
			'email'           => get_theme_mod( 'alify_email', 'info@alify.site' ),
			'whatsapp'        => get_theme_mod( 'alify_whatsapp', '+90 301 9328094' ),
			'whatsappUrl'     => get_theme_mod( 'alify_whatsapp_url', 'https://wa.me/903019328094' ),
		)
	);

	// 5. Reusable Component Scripts (Before main.js)
	$reusable_js = array(
		'inner-hero-js'   => 'assets/js/reusable-component-js/inner-hero.js',
		'services-js'     => 'assets/js/reusable-component-js/services.js',
		'industries-js'   => 'assets/js/reusable-component-js/industries.js',
		'projects-js'     => 'assets/js/reusable-component-js/projects.js',
		'case-studies-js' => 'assets/js/reusable-component-js/case-studies.js',
		'about-studio-js' => 'assets/js/reusable-component-js/about-studio.js',
		'single-pages-js' => 'assets/js/reusable-component-js/single-pages.js',
		'pricing-js'      => 'assets/js/reusable-component-js/pricing.js',
		'book-call-js'    => 'assets/js/reusable-component-js/book-call.js',
		'testimonials-js' => 'assets/js/reusable-component-js/testimonials.js',
		'faq-js'          => 'assets/js/reusable-component-js/faq.js',
		'blog-js'         => 'assets/js/reusable-component-js/blog.js',
		'forms-js'        => 'assets/js/reusable-component-js/forms.js',
	);

	$deps_for_main = array( 'alify-gsap', 'alify-scrolltrigger', 'alify-ui-polish-js' );
	foreach ( $reusable_js as $handle => $rel ) {
		wp_enqueue_script( 'alify-' . $handle, alify_asset_uri( $rel ), array( 'alify-ui-polish-js' ), alify_file_ver( $rel ), true );
		$deps_for_main[] = 'alify-' . $handle;
	}

	// 6. Main Custom JS (Loaded LAST so dynamic cards/galleries are already in DOM)
	wp_enqueue_script( 'alify-main-js', alify_asset_uri( 'assets/js/main.js' ), $deps_for_main, alify_file_ver( 'assets/js/main.js' ), true );

	// 7. Localize Live CPT Single Post Data (`window.ALIFY_CPT_SINGLE`) for CPT Permalinks & Query-String Detail Routes
	if ( function_exists( 'alify_build_single_cpt_override' ) ) {
		$cpt_post = null;
		if ( is_singular( array( 'alify_service', 'alify_project', 'alify_industry', 'alify_case_study' ) ) ) {
			$cpt_post = get_queried_object();
		} else {
			$qs_map = array(
				'service'  => 'alify_service',
				'project'  => 'alify_project',
				'industry' => 'alify_industry',
				'case'     => 'alify_case_study',
			);
			foreach ( $qs_map as $q_key => $ptype ) {
				if ( ! empty( $_GET[ $q_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$q_slug   = sanitize_title( wp_unslash( $_GET[ $q_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$cpt_post = get_page_by_path( $q_slug, OBJECT, $ptype );
					if ( $cpt_post ) {
						break;
					}
				}
			}
		}

		if ( $cpt_post ) {
			$override = alify_build_single_cpt_override( $cpt_post );
			if ( ! empty( $override ) ) {
				wp_localize_script( 'alify-single-pages-js', 'ALIFY_CPT_SINGLE', $override );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'alify_enqueue_assets' );
