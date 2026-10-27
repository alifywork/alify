<?php
/**
 * Theme Setup, Navigation Menus & Automatic Page Seeder
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_theme_setup() {
	load_theme_textdomain( 'alify', ALIFY_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary'         => __( 'Primary Header Navigation', 'alify' ),
			'mobile_drawer'   => __( 'Mobile Slide Drawer Navigation', 'alify' ),
			'footer_nav'      => __( 'Footer Quick Links', 'alify' ),
			'footer_services' => __( 'Footer Services Links', 'alify' ),
		)
	);
}
add_action( 'after_setup_theme', 'alify_theme_setup' );

/**
 * Canonical ALIFY Pages Map (Slug => [Title, Template])
 */
function alify_get_blueprint_pages() {
	return array(
		'home'               => array( 'Home', '' ),
		'about'              => array( 'About Us', 'page-templates/template-about.php' ),
		'services'           => array( 'Services', 'page-templates/template-services.php' ),
		'service-single'     => array( 'Service Detail', 'page-templates/template-service-single.php' ),
		'industries'         => array( 'Industries', 'page-templates/template-industries.php' ),
		'industry-single'    => array( 'Industry Detail', 'page-templates/template-industry-single.php' ),
		'work'               => array( 'Work', 'page-templates/template-work.php' ),
		'work-single'        => array( 'Project Detail', 'page-templates/template-work-single.php' ),
		'case-studies'       => array( 'Case Studies', 'page-templates/template-case-studies.php' ),
		'case-study-single'  => array( 'Case Study Detail', 'page-templates/template-case-study-single.php' ),
		'pricing'            => array( 'Pricing', 'page-templates/template-pricing.php' ),
		'testimonials'       => array( 'Testimonials', 'page-templates/template-testimonials.php' ),
		'faq'                => array( 'FAQs', 'page-templates/template-faq.php' ),
		'blog'               => array( 'Blog', 'page-templates/template-blog.php' ),
		'blog-single'        => array( 'Article Detail', 'page-templates/template-blog-single.php' ),
		'contact'            => array( 'Contact Us', 'page-templates/template-contact.php' ),
		'start-project'      => array( 'Start a Project', 'page-templates/template-start-project.php' ),
		'book-call'          => array( 'Book a Strategy Call', 'page-templates/template-book-call.php' ),
		'thank-you'          => array( 'Thank You', 'page-templates/template-thank-you.php' ),
		'privacy-policy'     => array( 'Privacy Policy', 'page-templates/template-privacy-policy.php' ),
		'terms-of-service'   => array( 'Terms of Service', 'page-templates/template-terms-of-service.php' ),
	);
}

/**
 * Automatically create missing ALIFY pages & set Front Page on theme activation.
 */
function alify_seed_default_pages() {
	$pages   = alify_get_blueprint_pages();
	$home_id = 0;

	foreach ( $pages as $slug => $info ) {
		list( $title, $template ) = $info;
		$existing                 = get_page_by_path( $slug );

		if ( ! $existing ) {
			$page_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
				)
			);
		} else {
			$page_id = $existing->ID;
		}

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			if ( ! empty( $template ) ) {
				update_post_meta( $page_id, '_wp_page_template', $template );
			}
			if ( 'home' === $slug ) {
				$home_id = (int) $page_id;
			}
		}
	}

	if ( $home_id > 0 && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'alify_seed_default_pages' );

/**
 * Allow manual one-click page sync from WP Admin (?alify_seed_pages=1).
 */
function alify_handle_manual_page_seed() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_GET['alify_seed_pages'] ) && check_admin_referer( 'alify_seed_pages_action' ) ) {
		alify_seed_default_pages();
		wp_safe_redirect( admin_url( 'edit.php?post_type=page&alify_seeded=1' ) );
		exit;
	}
}
add_action( 'admin_init', 'alify_handle_manual_page_seed' );

function alify_admin_seed_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'edit-page' ), true ) ) {
		return;
	}
	if ( isset( $_GET['alify_seeded'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>ALIFY Studio:</strong> All 21 theme pages and templates have been synced and published.</p></div>';
		return;
	}
	$about_page = get_page_by_path( 'about' );
	if ( ! $about_page ) {
		$seed_url = wp_nonce_url( admin_url( 'themes.php?alify_seed_pages=1' ), 'alify_seed_pages_action' );
		echo '<div class="notice notice-info"><p><strong>ALIFY Studio Theme Ready:</strong> Click <a href="' . esc_url( $seed_url ) . '" class="button button-primary" style="margin-left:8px;">Create All ALIFY Pages Automatically</a> to publish all 21 agency pages &amp; assign templates in one click.</p></div>';
	}
}
add_action( 'admin_notices', 'alify_admin_seed_notice' );
