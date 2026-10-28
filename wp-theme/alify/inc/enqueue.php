<?php
/**
 * Smart Conditional Route-Wise Enqueue for Stylesheets, GSAP, Fonts & Custom Scripts
 * Eliminates unused per-page CSS/JS payloads while preserving exact dependency order.
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

/**
 * Preload Critical Self-Hosted WOFF2 Fonts in `<head>` for Zero-CLS Typography.
 */
function alify_preload_critical_fonts() {
	$fonts = array(
		'assets/fonts/garet/Garet-Heavy.woff2',
		'assets/fonts/montserrat/Montserrat-SemiBold.woff2',
		'assets/fonts/poppins/Poppins-Regular.woff2',
	);
	foreach ( $fonts as $font_rel ) {
		echo '<link rel="preload" href="' . esc_url( alify_asset_uri( $font_rel ) ) . '" as="font" type="font/woff2" crossorigin />' . "\n";
	}
}
add_action( 'wp_head', 'alify_preload_critical_fonts', 2 );

/**
 * Exact Route-to-Component CSS & JS Map (Verified 1-to-1 Against All 22 ALIFY Templates).
 */
function alify_get_route_components( $route ) {
	$map = array(
		'index'             => array(
			'css' => array( 'blog', 'faq', 'forms', 'testimonials', 'industries', 'services', 'projects', 'case-studies', 'about-studio' ),
			'js'  => array( 'blog', 'faq', 'forms', 'testimonials', 'industries', 'services', 'projects', 'case-studies', 'about-studio' ),
		),
		'about'             => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'about-studio' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'about-studio' ),
		),
		'services'          => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'services' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'services' ),
		),
		'service-single'    => array(
			'css' => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
		),
		'industries'        => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'industries' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'industries' ),
		),
		'industry-single'   => array(
			'css' => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
		),
		'work'              => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'projects' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'projects' ),
		),
		'work-single'       => array(
			'css' => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
		),
		'case-studies'      => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'case-studies' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'case-studies' ),
		),
		'case-study-single' => array(
			'css' => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'projects', 'single-pages', 'testimonials', 'faq', 'blog' ),
		),
		'pricing'           => array(
			'css' => array( 'inner-hero', 'pricing', 'testimonials', 'faq', 'blog', 'forms' ),
			'js'  => array( 'inner-hero', 'pricing', 'testimonials', 'faq', 'blog', 'forms' ),
		),
		'start-project'     => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials', 'pricing' ),
		),
		'book-call'         => array(
			'css' => array( 'inner-hero', 'book-call', 'testimonials', 'faq', 'blog', 'forms' ),
			'js'  => array( 'inner-hero', 'book-call', 'testimonials', 'faq', 'blog', 'forms' ),
		),
		'testimonials'      => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms', 'testimonials' ),
		),
		'faq'               => array(
			'css' => array( 'inner-hero', 'testimonials', 'faq', 'blog', 'forms' ),
			'js'  => array( 'inner-hero', 'testimonials', 'faq', 'blog', 'forms' ),
		),
		'blog'              => array(
			'css' => array( 'inner-hero', 'blog' ),
			'js'  => array( 'inner-hero', 'blog' ),
		),
		'blog-single'       => array(
			'css' => array( 'inner-hero', 'blog' ),
			'js'  => array( 'inner-hero', 'blog' ),
		),
		'contact'           => array(
			'css' => array( 'inner-hero', 'faq', 'blog', 'forms' ),
			'js'  => array( 'inner-hero', 'faq', 'blog', 'forms' ),
		),
		'thank-you'         => array(
			'css' => array( 'inner-hero', 'projects', 'single-pages', 'about-studio', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'projects', 'about-studio', 'testimonials', 'faq', 'blog' ),
		),
		'privacy-policy'    => array(
			'css' => array( 'inner-hero', 'forms', 'about-studio', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'about-studio', 'testimonials', 'faq', 'blog' ),
		),
		'terms-of-service'  => array(
			'css' => array( 'inner-hero', 'forms', 'about-studio', 'testimonials', 'faq', 'blog' ),
			'js'  => array( 'inner-hero', 'forms', 'about-studio', 'testimonials', 'faq', 'blog' ),
		),
		'404'               => array(
			'css' => array( 'forms', 'about-studio', 'faq', 'blog' ),
			'js'  => array( 'forms', 'faq', 'blog' ),
		),
		'search'            => array(
			'css' => array( 'inner-hero', 'blog', 'forms' ),
			'js'  => array( 'inner-hero', 'blog', 'forms' ),
		),
	);

	if ( isset( $map[ $route ] ) ) {
		return $map[ $route ];
	}

	// Default for custom Gutenberg/Classic pages (`page.php`)
	return array(
		'css' => array( 'inner-hero', 'faq', 'blog', 'forms' ),
		'js'  => array( 'inner-hero', 'faq', 'blog', 'forms' ),
	);
}

function alify_enqueue_assets() {
	$route      = alify_current_route_slug();
	$components = alify_get_route_components( $route );

	// 1. Core Vendor & Global Component CSS
	wp_enqueue_style( 'alify-bootstrap-grid', alify_asset_uri( 'assets/css/bootstrap-css/bootstrap.min.css' ), array(), alify_file_ver( 'assets/css/bootstrap-css/bootstrap.min.css' ) );
	wp_enqueue_style( 'alify-fontawesome', alify_asset_uri( 'assets/icons/css/all.min.css' ), array(), alify_file_ver( 'assets/icons/css/all.min.css' ) );
	wp_enqueue_style( 'alify-header', alify_asset_uri( 'assets/css/global-component-css/header.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/header.css' ) );
	wp_enqueue_style( 'alify-footer', alify_asset_uri( 'assets/css/global-component-css/footer.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/footer.css' ) );
	wp_enqueue_style( 'alify-ui-polish', alify_asset_uri( 'assets/css/global-component-css/ui-polish.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/global-component-css/ui-polish.css' ) );

	// 2. Conditional Reusable Component CSS (Only What Current Route Uses)
	$all_reusable_css = array(
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

	foreach ( $components['css'] as $handle ) {
		if ( isset( $all_reusable_css[ $handle ] ) ) {
			$rel = $all_reusable_css[ $handle ];
			wp_enqueue_style( 'alify-' . $handle, alify_asset_uri( $rel ), array( 'alify-bootstrap-grid' ), alify_file_ver( $rel ) );
		}
	}

	// 3. Main Custom CSS & Theme Root style.css
	wp_enqueue_style( 'alify-main-style', alify_asset_uri( 'assets/css/style.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/style.css' ) );
	wp_enqueue_style( 'alify-wp-style', get_stylesheet_uri(), array( 'alify-main-style' ), alify_file_ver( 'style.css' ) );

	// 4. Vendor & Global Scripts (Loaded in Footer)
	wp_enqueue_script( 'alify-bootstrap-bundle', alify_asset_uri( 'assets/js/bootstrap-js/bootstrap.bundle.min.js' ), array(), alify_file_ver( 'assets/js/bootstrap-js/bootstrap.bundle.min.js' ), true );
	wp_enqueue_script( 'alify-gsap', alify_asset_uri( 'assets/gsap/gsap.min.js' ), array(), alify_file_ver( 'assets/gsap/gsap.min.js' ), true );
	wp_enqueue_script( 'alify-scrolltrigger', alify_asset_uri( 'assets/gsap/ScrollTrigger.min.js' ), array( 'alify-gsap' ), alify_file_ver( 'assets/gsap/ScrollTrigger.min.js' ), true );

	wp_enqueue_script( 'alify-header-js', alify_asset_uri( 'assets/js/global-component-js/header.js' ), array(), alify_file_ver( 'assets/js/global-component-js/header.js' ), true );
	wp_enqueue_script( 'alify-footer-js', alify_asset_uri( 'assets/js/global-component-js/footer.js' ), array(), alify_file_ver( 'assets/js/global-component-js/footer.js' ), true );
	wp_enqueue_script( 'alify-ui-polish-js', alify_asset_uri( 'assets/js/global-component-js/ui-polish.js' ), array(), alify_file_ver( 'assets/js/global-component-js/ui-polish.js' ), true );

	$forms_js_cfg = function_exists( 'alify_get_frontend_forms_js_config' ) ? alify_get_frontend_forms_js_config() : array();

	wp_localize_script(
		'alify-ui-polish-js',
		'ALIFY_WP',
		array_merge(
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'alify_ajax_nonce' ),
				'themeUri'         => untrailingslashit( ALIFY_THEME_URI ),
				'homeUrl'          => home_url( '/' ),
				'thankYouUrl'      => alify_page_url( 'thank-you' ),
				'bookCallUrl'      => alify_page_url( 'book-call' ),
				'startProjectUrl'  => alify_page_url( 'start-project' ),
				'blogUrl'          => alify_page_url( 'blog' ),
				'workUrl'          => alify_page_url( 'work' ),
				'workSingleUrl'    => alify_page_url( 'work-single' ),
				'caseSingleUrl'    => alify_page_url( 'case-study-single' ),
				'serviceSingleUrl' => alify_page_url( 'service-single' ),
				'blogSingleUrl'    => alify_page_url( 'blog-single' ),
				'email'            => get_theme_mod( 'alify_email', 'info@alify.site' ),
				'whatsapp'         => get_theme_mod( 'alify_whatsapp', '+92 301 9328094' ),
				'whatsappUrl'      => get_theme_mod( 'alify_whatsapp_url', 'https://wa.me/923019328094' ),
			),
			$forms_js_cfg
		)
	);

	// 5. Conditional Reusable Component Scripts (Before main.js)
	$all_reusable_js = array(
		'inner-hero'   => 'assets/js/reusable-component-js/inner-hero.js',
		'services'     => 'assets/js/reusable-component-js/services.js',
		'industries'   => 'assets/js/reusable-component-js/industries.js',
		'projects'     => 'assets/js/reusable-component-js/projects.js',
		'case-studies' => 'assets/js/reusable-component-js/case-studies.js',
		'about-studio' => 'assets/js/reusable-component-js/about-studio.js',
		'single-pages' => 'assets/js/reusable-component-js/single-pages.js',
		'pricing'      => 'assets/js/reusable-component-js/pricing.js',
		'book-call'    => 'assets/js/reusable-component-js/book-call.js',
		'testimonials' => 'assets/js/reusable-component-js/testimonials.js',
		'faq'          => 'assets/js/reusable-component-js/faq.js',
		'blog'         => 'assets/js/reusable-component-js/blog.js',
		'forms'        => 'assets/js/reusable-component-js/forms.js',
	);

	$deps_for_main = array( 'alify-gsap', 'alify-scrolltrigger', 'alify-ui-polish-js' );
	foreach ( $components['js'] as $key ) {
		if ( isset( $all_reusable_js[ $key ] ) ) {
			$rel        = $all_reusable_js[ $key ];
			$script_hdl = 'alify-' . $key . '-js';
			wp_enqueue_script( $script_hdl, alify_asset_uri( $rel ), array( 'alify-ui-polish-js' ), alify_file_ver( $rel ), true );
			$deps_for_main[] = $script_hdl;
		}
	}

	// 6. Main Custom JS (Loaded LAST so dynamic cards/galleries are already in DOM)
	wp_enqueue_script( 'alify-main-js', alify_asset_uri( 'assets/js/main.js' ), $deps_for_main, alify_file_ver( 'assets/js/main.js' ), true );

	// 7. Native Threaded Comment Reply Script on Single Posts
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// 8. Localize Live CPT Single Post Data (`window.ALIFY_CPT_SINGLE`) When `single-pages` Script Is Enqueued
	if ( wp_script_is( 'alify-single-pages-js', 'enqueued' ) && function_exists( 'alify_build_single_cpt_override' ) ) {
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
