<?php
/**
 * Official Integration With `Alify Custom Field` Plugin (`alifywork/fieldsPlugin`).
 *
 * Provides:
 *   1. 5 Options Pages (`site` Global + `alify_home` + `alify_archives` + `alify_pages` + `alify_singles`)
 *   2. 13 Native Field Groups With Tabs, Repeaters, Multi-Image Galleries, Media Pickers & Toggles
 *   3. Automatic Pre-Seeding From Starter Content (`_alify_*` -> `_alify_cf_*` Repeater/Scalar Meta)
 *   4. Two-Way Save Sync (`save_post` & Options Save -> CPT Renderers, Lightbox Gallery & Customizer)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ALIFY_THEME_DIR . '/inc/page-copy-editor.php';

/**
 * Check Whether `Alify Custom Field` Plugin Is Active.
 */
function alify_has_cf_plugin() {
	return function_exists( 'alify_register_field_group' )
		&& function_exists( 'alify_add_options_page' )
		&& function_exists( 'alify_get_field' )
		&& function_exists( 'alify_get_option' );
}

/**
 * Helper Builders Ensuring 100% Compliance With `Alify_CF::validate_schema()`.
 */
function alify_cf_tab_def( $name, $label ) {
	return array(
		'name'  => $name,
		'label' => $label,
		'type'  => 'tab',
	);
}

function alify_cf_field_def( $name, $label, $type = 'text', $extra = array() ) {
	$f = array(
		'name'         => $name,
		'label'        => $label,
		'type'         => $type,
		'instructions' => isset( $extra['desc'] ) ? (string) $extra['desc'] : '',
	);
	if ( isset( $extra['std'] ) && ! in_array( $type, array( 'repeater', 'group', 'gallery', 'flexible' ), true ) ) {
		$f['default'] = (string) $extra['std'];
	}
	if ( isset( $extra['choices'] ) && is_array( $extra['choices'] ) ) {
		$lines = array();
		foreach ( $extra['choices'] as $k => $v ) {
			$lines[] = $k . ' : ' . $v;
		}
		$f['choices'] = implode( "\n", $lines );
	}
	if ( 'repeater' === $type && isset( $extra['sub_fields'] ) ) {
		$f['sub_fields'] = $extra['sub_fields'];
		if ( isset( $extra['max_rows'] ) ) {
			$f['max_rows'] = (int) $extra['max_rows'];
		}
	}
	if ( 'gallery' === $type && isset( $extra['max_items'] ) ) {
		$f['max_items'] = (int) $extra['max_items'];
	}
	return $f;
}

/**
 * 1. Register Additional Options Pages on `init` (Priority 12 — Before `admin_menu` & Group Registration).
 */
function alify_register_cf_options_pages() {
	if ( ! alify_has_cf_plugin() ) {
		return;
	}
	alify_add_options_page( 'alify_home', __( 'Home Page Content', 'alify' ) );
	alify_add_options_page( 'alify_archives', __( 'Archive & Hub Pages', 'alify' ) );
	alify_add_options_page( 'alify_pages', __( 'Pricing & About Us', 'alify' ) );
	alify_add_options_page( 'alify_pricing_adv', __( 'Pricing Calc & Matrix', 'alify' ) );
	alify_add_options_page( 'alify_conversion', __( 'Book Call, Legal & 404', 'alify' ) );
	alify_add_options_page( 'alify_singles', __( 'Single Templates', 'alify' ) );
}
add_action( 'init', 'alify_register_cf_options_pages', 12 );

/**
 * Add Top-Level `ALIFY Theme Settings` Shortcut Menu in WP Admin Linking to Theme Settings and the Page Copy Editor.
 */
function alify_register_cf_theme_settings_shortcut_menu() {
	add_menu_page(
		__( 'ALIFY Theme Settings', 'alify' ),
		__( 'ALIFY Theme Settings', 'alify' ),
		'manage_options',
		'alify-theme-settings-hub',
		'alify_render_cf_theme_settings_hub',
		'dashicons-admin-customizer',
		2
	);

	if ( alify_has_cf_plugin() ) {
		$links = array(
			'site'              => __( 'Global, Header & Footer', 'alify' ),
			'alify_home'        => __( 'Home Page Content', 'alify' ),
			'alify_archives'    => __( 'Archive & Hub Pages', 'alify' ),
			'alify_pages'       => __( 'Pricing & About Us', 'alify' ),
			'alify_pricing_adv' => __( 'Pricing Calc & Matrix', 'alify' ),
			'alify_conversion'  => __( 'Book Call, Legal & 404', 'alify' ),
			'alify_singles'     => __( 'Single Templates', 'alify' ),
		);
		foreach ( $links as $slug => $label ) {
			add_submenu_page(
				'alify-theme-settings-hub',
				$label,
				$label,
				'manage_options',
				'alify-cf-jump-' . $slug,
				'alify_render_cf_theme_settings_hub'
			);
		}
	}
}
add_action( 'admin_menu', 'alify_register_cf_theme_settings_shortcut_menu' );

function alify_redirect_cf_theme_settings_shortcuts() {
	if ( ! is_admin() || ! isset( $_GET['page'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$pg = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! alify_has_cf_plugin() ) {
		return;
	}
	if ( 'alify-theme-settings-hub' === $pg ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=alify_field_group&page=alify-options-site' ) );
		exit;
	}
	if ( 0 === strpos( $pg, 'alify-cf-jump-' ) ) {
		$slug = substr( $pg, strlen( 'alify-cf-jump-' ) );
		wp_safe_redirect( admin_url( 'edit.php?post_type=alify_field_group&page=alify-options-' . $slug ) );
		exit;
	}
}
add_action( 'admin_init', 'alify_redirect_cf_theme_settings_shortcuts', 5 );

function alify_render_cf_theme_settings_hub() {
	echo '<div class="wrap"><h1>' . esc_html__( 'ALIFY Theme Settings', 'alify' ) . '</h1>';
	echo '<div class="notice notice-info" style="padding:14px 18px;margin-top:16px;"><p><strong>' . esc_html__( 'Activate the "Alify Custom Field" plugin', 'alify' ) . '</strong> ' . esc_html__( 'to unlock all 7 core visual Options Pages and Repeater/Gallery editors on every post type. Page text and interface copy are available in the Page Copy Editor.', 'alify' ) . '</p></div></div>';
}

/**
 * 2. Register All 13 Field Groups on `init` (Priority 15 — After CPTs at 10 & Options Pages at 12).
 */
function alify_register_all_cf_field_groups() {
	if ( ! alify_has_cf_plugin() ) {
		return;
	}

	$groups = array();

	// =========================================================================
	// A. 8 POST TYPE & PAGE FIELD GROUPS
	// =========================================================================

	// 1. Services CPT (`alify_service`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_service',
		'title'    => 'ALIFY Service — Card, Single Stats & Deliverables',
		'location' => 'alify_service',
		'fields'   => array(
			alify_cf_tab_def( 'svc_tab_card', 'Service Card & Archive' ),
			alify_cf_field_def( 'svc_notch', 'Notch Code Badge', 'text', array( 'desc' => 'Example: 01 · WEB' ) ),
			alify_cf_field_def( 'svc_tag', 'Discipline Pill Label', 'text', array( 'desc' => 'Example: Engineering' ) ),
			alify_cf_field_def(
				'svc_filter',
				'Archive Filter Tab',
				'select',
				array(
					'std'     => 'engineering',
					'choices' => array(
						'design'      => 'Design & Brand',
						'engineering' => 'Web & Engineering',
						'growth'      => 'Growth, CRO & SEO',
					),
				)
			),
			alify_cf_field_def( 'svc_icon', 'Font Awesome Icon Class', 'text', array( 'desc' => 'Example: fa-solid fa-code' ) ),
			alify_cf_field_def( 'svc_bg_img', 'Upload Card & Single Hero Image (Media Library)', 'image', array( 'desc' => 'Pick an image from Media Library to override the starter image URL.' ) ),
			alify_cf_field_def( 'svc_bg_url', 'Current / Fallback Image URL', 'text' ),
			alify_cf_field_def(
				'svc_points_rows',
				'Card Bullet Deliverables (Repeater)',
				'repeater',
				array(
					'max_rows'   => 12,
					'sub_fields' => array(
						alify_cf_field_def( 'point', 'Deliverable Bullet Text', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'svc_tab_single', 'Single Page Stats & Bento Deliverables' ),
			alify_cf_field_def(
				'svc_stats_rows',
				'Single Hero Stats (Repeater)',
				'repeater',
				array(
					'max_rows'   => 6,
					'sub_fields' => array(
						alify_cf_field_def( 'val', 'Stat Value (e.g. 99+)', 'text' ),
						alify_cf_field_def( 'label', 'Stat Label (e.g. Core Web Vitals)', 'text' ),
					),
				)
			),
			alify_cf_field_def(
				'svc_deliv_rows',
				'Single Page Deliverable Bento Cards (Repeater)',
				'repeater',
				array(
					'max_rows'   => 12,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-bolt)', 'text', array( 'std' => 'fa-solid fa-layer-group' ) ),
						alify_cf_field_def( 'title', 'Deliverable Card Title', 'text' ),
						alify_cf_field_def( 'desc', 'Deliverable Card Description', 'textarea' ),
					),
				)
			),
			alify_cf_field_def( 'svc_stack_pills', 'Single Page Tech Stack Pills (comma-separated)', 'text', array( 'desc' => 'Example: HTML5 & CSS3, GSAP ScrollTrigger, WordPress Custom CMS, Technical Schema' ) ),
		),
	);

	// 2. Projects / Work CPT (`alify_project`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_project',
		'title'    => 'ALIFY Project — Showcase, Single Story & Lightbox Gallery',
		'location' => 'alify_project',
		'fields'   => array(
			alify_cf_tab_def( 'proj_tab_card', 'Project Card & Metadata' ),
			alify_cf_field_def( 'proj_num', 'Panel Number (e.g. 01)', 'text' ),
			alify_cf_field_def( 'proj_short_title', 'Short Spine Title', 'text' ),
			alify_cf_field_def( 'proj_client', 'Client & Location Label', 'text' ),
			alify_cf_field_def( 'proj_sector', 'Sector Badge Label', 'text' ),
			alify_cf_field_def(
				'proj_filter',
				'Work Grid Filter Category',
				'select',
				array(
					'std'     => 'web-saas',
					'choices' => array(
						'web-saas'       => 'Web & SaaS',
						'commerce-brand' => 'E-Commerce & Brand',
						'ai-cloud'       => 'AI, Health & PropTech',
					),
				)
			),
			alify_cf_field_def( 'proj_year', 'Launch Year (e.g. 2026)', 'text' ),
			alify_cf_field_def( 'proj_sprint', 'Sprint Duration (e.g. 10-Week Sprint)', 'text' ),
			alify_cf_field_def( 'proj_metric', 'Headline Outcome Metric (e.g. +184% Demo Pipeline)', 'text' ),
			alify_cf_field_def( 'proj_chips', 'Deliverable Chips (comma-separated)', 'text' ),
			alify_cf_field_def( 'proj_case_slug', 'Linked Case Study Slug (optional)', 'text' ),
			alify_cf_field_def( 'proj_bg_img', 'Upload Primary Project Image (Media Library)', 'image' ),
			alify_cf_field_def( 'proj_bg_url', 'Current / Fallback Project Image URL', 'text' ),

			alify_cf_tab_def( 'proj_tab_story', 'Single Page Story (Brief, Craft & Outcomes)' ),
			alify_cf_field_def( 'proj_brief', 'The Brief / Challenge', 'textarea' ),
			alify_cf_field_def( 'proj_craft', 'Engineering & Design Craft', 'textarea' ),
			alify_cf_field_def(
				'proj_outcomes_rows',
				'Single Page Verified Outcome Tiles (Repeater — 3 Metrics)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'val', 'Outcome Value (e.g. +184%)', 'text' ),
						alify_cf_field_def( 'label', 'Outcome Label (e.g. Demo Pipeline)', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'proj_tab_gallery', 'Interactive Lightbox Gallery' ),
			alify_cf_field_def( 'proj_gallery_media', 'Quick Multi-Image Lightbox Gallery Picker', 'gallery', array( 'max_items' => 24, 'desc' => 'Optional: drag-and-drop multiple Media Library images at once, or manage detailed screen tags/captions in the Repeater below.' ) ),
			alify_cf_field_def(
				'proj_gallery_rows',
				'Detailed Lightbox Screens With Tags & Captions (Repeater)',
				'repeater',
				array(
					'max_rows'   => 24,
					'sub_fields' => array(
						alify_cf_field_def( 'img_id', 'Upload Screen Image (Media Library)', 'image' ),
						alify_cf_field_def( 'url', 'Current / Fallback Screen Image URL', 'text' ),
						alify_cf_field_def( 'tag', 'Screen Tag Badge (e.g. UI Design)', 'text' ),
						alify_cf_field_def( 'title', 'Screen Title', 'text' ),
						alify_cf_field_def( 'caption', 'Lightbox Caption', 'text' ),
					),
				)
			),
		),
	);

	// 3. Industries CPT (`alify_industry`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_industry',
		'title'    => 'ALIFY Industry — Sector Card & Single Stats',
		'location' => 'alify_industry',
		'fields'   => array(
			alify_cf_tab_def( 'ind_tab_card', 'Industry Card & Archive' ),
			alify_cf_field_def( 'ind_code', 'Industry Code Badge (e.g. 01 · FINTECH)', 'text' ),
			alify_cf_field_def(
				'ind_sector_group',
				'Archive Filter Group',
				'select',
				array(
					'std'     => 'finance-cloud',
					'choices' => array(
						'finance-cloud'       => 'FinTech, SaaS & AI',
						'commerce-property'   => 'Commerce, PropTech & Travel',
						'health-edu-mobility' => 'Health, EdTech & Mobility',
					),
				)
			),
			alify_cf_field_def( 'ind_icon', 'Font Awesome Icon Class', 'text' ),
			alify_cf_field_def( 'ind_stat', 'Card Footer Benchmark Stat', 'text' ),
			alify_cf_field_def( 'ind_bg_img', 'Upload Industry Image (Media Library)', 'image' ),
			alify_cf_field_def( 'ind_bg_url', 'Current / Fallback Image URL', 'text' ),
			alify_cf_field_def(
				'ind_points_rows',
				'Sector Deliverables (Repeater)',
				'repeater',
				array(
					'max_rows'   => 12,
					'sub_fields' => array(
						alify_cf_field_def( 'point', 'Sector Deliverable Bullet', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'ind_tab_single', 'Single Page Stats & Sector Challenges' ),
			alify_cf_field_def(
				'ind_stats_rows',
				'Single Page Verified Stats (Repeater)',
				'repeater',
				array(
					'max_rows'   => 6,
					'sub_fields' => array(
						alify_cf_field_def( 'val', 'Stat Value', 'text' ),
						alify_cf_field_def( 'label', 'Stat Label', 'text' ),
					),
				)
			),
			alify_cf_field_def(
				'ind_challenges_rows',
				'Sector Friction & ALIFY Fix Cards (Repeater — 3 Cards)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'badge', 'Friction Badge (e.g. KYC Friction)', 'text' ),
						alify_cf_field_def( 'title', 'Challenge Heading', 'text' ),
						alify_cf_field_def( 'fix', 'ALIFY Architectural Fix', 'textarea' ),
					),
				)
			),
		),
	);

	// 4. Case Studies CPT (`alify_case_study`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_case_study',
		'title'    => 'ALIFY Case Study — Benchmarks, KPIs & Executive Quote',
		'location' => 'alify_case_study',
		'fields'   => array(
			alify_cf_tab_def( 'cs_tab_meta', 'Card & Switcher Metadata' ),
			alify_cf_field_def(
				'cs_filter',
				'Archive Filter Category',
				'select',
				array(
					'std'     => 'fintech-saas',
					'choices' => array(
						'fintech-saas'    => 'FinTech & SaaS',
						'commerce-brand'  => 'E-Commerce & Brand',
						'health-proptech' => 'Health & PropTech',
					),
				)
			),
			alify_cf_field_def( 'cs_tab_index', 'Home Switcher Tab Label (e.g. 01 · FINTECH SAAS)', 'text' ),
			alify_cf_field_def( 'cs_client_short', 'Client Short Name', 'text' ),
			alify_cf_field_def( 'cs_client_kicker', 'Client & HQ Kicker', 'text' ),
			alify_cf_field_def( 'cs_sector_badge', 'Visual Badge 1 (Sector)', 'text' ),
			alify_cf_field_def( 'cs_duration_badge', 'Visual Badge 2 (Duration)', 'text' ),
			alify_cf_field_def( 'cs_bg_img', 'Upload Case Study Visual (Media Library)', 'image' ),
			alify_cf_field_def( 'cs_bg_url', 'Current / Fallback Image URL', 'text' ),

			alify_cf_tab_def( 'cs_tab_kpis', 'Challenge, Benchmarks & KPIs' ),
			alify_cf_field_def( 'cs_bottleneck', '01 · The Bottleneck', 'textarea' ),
			alify_cf_field_def( 'cs_architecture', '02 · ALIFY Architecture', 'textarea' ),
			alify_cf_field_def( 'cs_chips', 'Architecture Blueprint Chips (comma-separated)', 'text' ),
			alify_cf_field_def(
				'cs_bench_rows',
				'Before vs. After Benchmarks (Repeater)',
				'repeater',
				array(
					'max_rows'   => 8,
					'sub_fields' => array(
						alify_cf_field_def( 'metric', 'Benchmark Label (e.g. LCP Speed)', 'text' ),
						alify_cf_field_def( 'before', 'Before Value (e.g. 4.9s)', 'text' ),
						alify_cf_field_def( 'after', 'After Value (e.g. 0.6s)', 'text' ),
					),
				)
			),
			alify_cf_field_def(
				'cs_kpi_rows',
				'Verified KPI Tiles (Repeater)',
				'repeater',
				array(
					'max_rows'   => 6,
					'sub_fields' => array(
						alify_cf_field_def( 'val', 'KPI Value (e.g. +184%)', 'text' ),
						alify_cf_field_def( 'label', 'KPI Label (e.g. Demo Pipeline)', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'cs_tab_quote', 'Executive Client Quote' ),
			alify_cf_field_def( 'cs_quote', 'Executive Quote Text', 'textarea' ),
			alify_cf_field_def( 'cs_quote_author', 'Executive Quote Author & Role', 'text' ),
		),
	);

	// 5. Testimonials CPT (`alify_testimonial`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_testimonial',
		'title'    => 'ALIFY Testimonial — Client Role, Rating & Outcome',
		'location' => 'alify_testimonial',
		'fields'   => array(
			alify_cf_field_def( 'testi_role', 'Client Role & Company', 'text' ),
			alify_cf_field_def( 'testi_initials', 'Avatar Initials (2 Letters)', 'text' ),
			alify_cf_field_def( 'testi_rating', 'Star Rating Score', 'text', array( 'std' => '5.0' ) ),
			alify_cf_field_def(
				'testi_filter',
				'Archive Filter Tab',
				'select',
				array(
					'std'     => 'web',
					'choices' => array(
						'web'      => 'Web & Engineering',
						'branding' => 'UI/UX & Branding',
						'systems'  => 'Design Systems',
						'growth'   => 'E-Commerce & SEO',
					),
				)
			),
			alify_cf_field_def( 'testi_outcome', 'Verified Outcome Pill (e.g. +184% Demo Pipeline)', 'text' ),
			alify_cf_field_def( 'testi_tag', 'Footer Capability Tag', 'text' ),
		),
	);

	// 6. FAQs CPT (`alify_faq`)
	$groups[] = array(
		'key'      => 'alify_theme_cpt_faq',
		'title'    => 'ALIFY FAQ — Knowledge Pillar & Global Visibility',
		'location' => 'alify_faq',
		'fields'   => array(
			alify_cf_field_def(
				'faq_pillar',
				'Knowledge Hub Pillar',
				'select',
				array(
					'std'     => 'scope-sprints',
					'choices' => array(
						'scope-sprints'     => '01 · Scope, Sprints & Onboarding',
						'pricing-billing'   => '02 · Pricing, Proposals & Billing',
						'engineering-stack' => '03 · Engineering, Speed & SEO',
						'design-ownership'  => '04 · Design Systems & IP Ownership',
						'support-retainers' => '05 · Post-Launch & Growth Retainers',
					),
				)
			),
			alify_cf_field_def( 'faq_show_reusable', 'Include in Global 6-Item Reusable FAQ Section?', 'toggle' ),
		),
	);

	// 7. Blog Posts (`post`)
	$groups[] = array(
		'key'      => 'alify_theme_blog_post',
		'title'    => 'ALIFY Blog Article — Hero Image & Byline Settings',
		'location' => 'post',
		'fields'   => array(
			alify_cf_field_def( 'post_bg_img', 'Upload Single Hero / Card Image Override', 'image' ),
			alify_cf_field_def( 'post_bg_url', 'Current / Fallback Image URL', 'text' ),
			alify_cf_field_def( 'post_badge', 'Custom Hero Category Badge Override', 'text', array( 'desc' => 'Leave empty to use Post Category.' ) ),
			alify_cf_field_def( 'post_read_time', 'Custom Reading Time Override (e.g. 6 Min Read)', 'text' ),
			alify_cf_field_def( 'post_author_label', 'Custom Author Byline Override (e.g. ALIFY Editorial Team)', 'text' ),
		),
	);

	// 8. Inner Pages Universal Hero, Right-Side Hero Card & Reusable Section Controls (`page`)
	$groups[] = array(
		'key'      => 'alify_theme_page_controls',
		'title'    => 'ALIFY Inner Page — Hero Banner, Right Hero Card & Bottom Sections',
		'location' => 'page',
		'rules'    => array(
			array(
				array( 'param' => 'template', 'operator' => '!=', 'value' => 'page-templates/template-home.php' ),
				array( 'param' => 'template', 'operator' => '!=', 'value' => 'page-templates/template-thank-you.php' ),
			),
		),
		'fields'   => array(
			alify_cf_tab_def( 'pg_tab_hero', 'Inner Hero Banner' ),
			alify_cf_field_def( 'page_hero_eyebrow', 'Hero Eyebrow Badge Text', 'text' ),
			alify_cf_field_def( 'page_hero_title', 'Hero Main H1 Heading (`<span>` allowed for accent)', 'text' ),
			alify_cf_field_def( 'page_hero_desc', 'Hero Lead Description', 'textarea' ),
			alify_cf_field_def( 'page_hero_tags', 'Hero Feature Pills / Tags (comma-separated)', 'text', array( 'desc' => 'Example: 35+ Senior Specialists, Global Client Partnerships, Design-First Culture' ) ),
			alify_cf_field_def( 'page_hero_bg_img', 'Upload Hero Background Image (Media Library)', 'image' ),
			alify_cf_field_def( 'page_hero_bg_url', 'Current / Fallback Hero Background URL', 'text' ),

			alify_cf_tab_def( 'pg_tab_herocard', 'Right-Side Hero Form / Info Card' ),
			alify_cf_field_def( 'page_hero_form_badge', 'Hero Form Top Badge (e.g. Free Strategy Consultation)', 'text' ),
			alify_cf_field_def( 'page_hero_form_sla', 'Hero Form SLA Pill (e.g. Replies in 24h)', 'text' ),
			alify_cf_field_def( 'page_hero_form_title', 'Hero Form Heading (e.g. Request a Quick Estimate)', 'text' ),
			alify_cf_field_def( 'page_hero_form_sub', 'Hero Form Subtitle', 'textarea' ),
			alify_cf_field_def( 'page_hero_form_btn', 'Hero Form Submit Button Text', 'text' ),
			alify_cf_field_def( 'page_hero_card_code', 'Hero Info Card Code (Blog / Start Project, e.g. △ JOURNAL 04)', 'text' ),
			alify_cf_field_def( 'page_hero_card_metric', 'Hero Info Card Metric (e.g. Weekly / 48 Hrs)', 'text' ),
			alify_cf_field_def( 'page_hero_card_label', 'Hero Info Card Description', 'textarea' ),
			alify_cf_field_def( 'page_hero_card_cta', 'Hero Info Card CTA Link Label', 'text' ),

			alify_cf_tab_def( 'pg_tab_vis', 'Bottom Sections & Visibility' ),
			alify_cf_field_def( 'page_hide_hero_form', 'Hide Right-Side Hero Lead Form / Card', 'toggle' ),
			alify_cf_field_def( 'page_hide_testimonials', 'Hide Reusable Testimonials Slider', 'toggle' ),
			alify_cf_field_def( 'page_hide_faq', 'Hide Reusable FAQs Section', 'toggle' ),
			alify_cf_field_def( 'page_hide_blog_slider', 'Hide Reusable Blog Slider', 'toggle' ),
			alify_cf_field_def( 'page_hide_contact', 'Hide Bottom Reusable Contact Section', 'toggle' ),
			alify_cf_field_def( 'page_sec_testi_title', 'Custom Testimonials Section Heading (This Page)', 'text' ),
			alify_cf_field_def( 'page_sec_faq_title', 'Custom FAQs Section Heading (This Page)', 'text' ),
			alify_cf_field_def( 'page_sec_blog_title', 'Custom Blog Slider Heading (This Page)', 'text' ),
			alify_cf_field_def( 'page_sec_contact_title', 'Custom Bottom Contact Heading (This Page)', 'text' ),
		),
	);


	// 9. Complete Per-Page CMS Content Fields — every static template has its own dashboard editor.
	$groups[] = array(
		'key'      => 'alify_theme_all_page_content',
		'title'    => 'ALIFY Page Content — All Sections, Copy & Repeaters',
		'location' => 'page',
		'fields'   => array(
			alify_cf_tab_def( 'allpg_tab_hero', 'Page Hero & Page-Specific Content' ),
			alify_cf_field_def( 'pg_content_hero_eyebrow', 'Hero Eyebrow', 'text' ),
			alify_cf_field_def( 'pg_content_hero_title', 'Hero Heading (HTML accent span is supported)', 'textarea' ),
			alify_cf_field_def( 'pg_content_hero_desc', 'Hero Description', 'textarea' ),
			alify_cf_field_def( 'pg_content_hero_tags', 'Hero Tag Pills — one per line', 'textarea' ),
			alify_cf_field_def( 'pg_content_hero_image', 'Hero Background Image', 'image' ),
			alify_cf_field_def( 'pg_content_hero_image_url', 'Hero Background Image URL Fallback', 'url' ),
			alify_cf_field_def( 'pg_content_hero_cta', 'Hero CTA / Card Label', 'text' ),
			alify_cf_field_def( 'pg_content_hero_card_metric', 'Hero Card Metric / Code', 'text' ),
			alify_cf_field_def( 'pg_content_hero_card_copy', 'Hero Card Description', 'textarea' ),
			alify_cf_field_def( 'pg_content_hero_lead_title', 'Hero Lead Form Heading', 'text' ),
			alify_cf_field_def( 'pg_content_hero_lead_desc', 'Hero Lead Form Description', 'textarea' ),
			alify_cf_field_def( 'pg_content_hero_lead_button', 'Hero Lead Form Button Label', 'text' ),
			alify_cf_field_def( 'pg_content_hero_lead_badge', 'Hero Lead Form Badge', 'text' ),
			alify_cf_field_def( 'pg_content_hero_lead_sla', 'Hero Lead Form Response SLA', 'text' ),

			alify_cf_tab_def( 'allpg_tab_sections', 'All Page Sections & Repeater Content' ),
			alify_cf_field_def( 'pg_content_sections', 'Page Sections — add, edit, reorder and remove sections', 'repeater', array(
				'max_rows' => 50,
				'sub_fields' => array(
					alify_cf_field_def( 'section_key', 'Section Key / Template Slug (e.g. home_why, pricing_tiers)', 'text' ),
					alify_cf_field_def( 'section_eyebrow', 'Section Eyebrow', 'text' ),
					alify_cf_field_def( 'section_title', 'Section Heading (HTML accent span supported)', 'textarea' ),
					alify_cf_field_def( 'section_description', 'Section Description', 'textarea' ),
					alify_cf_field_def( 'section_body', 'Section Rich Content / Policy Text', 'wysiwyg' ),
					alify_cf_field_def( 'section_image', 'Section Image', 'image' ),
					alify_cf_field_def( 'section_image_url', 'Section Image URL Fallback', 'url' ),
					alify_cf_field_def( 'section_items', 'Cards, Steps, Metrics, Guarantees, Questions or List Rows', 'repeater', array(
						'max_rows' => 50,
						'sub_fields' => array(
							alify_cf_field_def( 'item_kicker', 'Item Badge / Category / Kicker', 'text' ),
							alify_cf_field_def( 'item_target_class', 'Existing Title CSS Class To Replace (Optional)', 'text', array( 'desc' => 'Example: about-pillar-title, pricing-tier-name, faq-hub-group-title. Leave blank to only add a new card.' ) ),
							alify_cf_field_def( 'item_title', 'Item Title / Question / Name', 'text' ),
							alify_cf_field_def( 'item_description_target_class', 'Existing Description CSS Class To Replace (Optional)', 'text' ),
							alify_cf_field_def( 'item_description', 'Item Description / Answer / Biography', 'textarea' ),
							alify_cf_field_def( 'item_body', 'Item Rich Content / Long Answer', 'wysiwyg' ),
							alify_cf_field_def( 'item_value', 'Item Metric / Price / Time / Value', 'text' ),
							alify_cf_field_def( 'item_label', 'Item Metric Label / Role / CTA', 'text' ),
							alify_cf_field_def( 'item_image', 'Item Image / Portrait / Artwork', 'image' ),
							alify_cf_field_def( 'item_link', 'Item Link URL', 'url' ),
							alify_cf_field_def( 'item_icon', 'Item Icon Class / Symbol', 'text' ),
							alify_cf_field_def( 'item_enabled', 'Show This Item', 'toggle' ),
						),
				)),
				alify_cf_field_def( 'section_enabled', 'Display This Section', 'toggle' ),
			)),
		),
	),
	);

	// =========================================================================
	// B. 5 OPTIONS PAGE FIELD GROUPS (`options:site`, `options:alify_home`, etc.)
	// =========================================================================

	// 9. Site Settings (`options:site` — Global Brand, Header, Footer & Reusable Sections)
	$groups[] = array(
		'key'      => 'alify_opt_group_global',
		'title'    => 'Global Brand, Header, Footer & Reusable Sections',
		'location' => 'options:site',
		'fields'   => array(
			alify_cf_tab_def( 'gtab_brand', 'Brand & Contact' ),
			alify_cf_field_def( 'opt_brand_logo', 'Custom Brand Logo Image (Optional)', 'image', array( 'desc' => 'Leave empty to render the signature geometric △LIFY Garet logo.' ) ),
			alify_cf_field_def( 'opt_brand_logo_height', 'Custom Logo Max Height (px)', 'number', array( 'std' => '40' ) ),
			alify_cf_field_def( 'opt_email', 'Official Studio Email', 'email', array( 'std' => 'info@alify.site' ) ),
			alify_cf_field_def( 'opt_whatsapp', 'WhatsApp Display Number', 'text', array( 'std' => '+92 301 9328094' ) ),
			alify_cf_field_def( 'opt_whatsapp_url', 'WhatsApp Direct Link (`wa.me`)', 'url', array( 'std' => 'https://wa.me/923019328094' ) ),
			alify_cf_field_def( 'opt_hq', 'Studio Headquarters Label', 'text', array( 'std' => 'Karachi, PK — Global Delivery' ) ),
			alify_cf_field_def( 'opt_hours', 'Studio Operating Hours', 'text', array( 'std' => 'Mon – Fri, 9:00 AM – 7:00 PM PKT' ) ),

			alify_cf_tab_def( 'gtab_header', 'Header & Mobile Drawer' ),
			alify_cf_field_def( 'opt_header_contact_text', 'Header Secondary Button Label', 'text', array( 'std' => 'Contact Us' ) ),
			alify_cf_field_def( 'opt_header_contact_url', 'Header Secondary Button Slug / URL', 'text', array( 'std' => 'contact' ) ),
			alify_cf_field_def( 'opt_header_cta_text', 'Header Primary Split-Pill CTA Label', 'text', array( 'std' => 'Start a Project' ) ),
			alify_cf_field_def( 'opt_header_cta_url', 'Header Primary Split-Pill Slug / URL', 'text', array( 'std' => 'start-project' ) ),

			alify_cf_tab_def( 'gtab_footer', 'Footer & Social Profiles' ),
			alify_cf_field_def( 'opt_footer_cta_eyebrow', 'Pre-Footer Banner Eyebrow', 'text', array( 'std' => 'Ready to Scale Your Brand?' ) ),
			alify_cf_field_def( 'opt_footer_cta_title', 'Pre-Footer Banner Heading', 'text', array( 'std' => 'Let’s Build Something Remarkable Together.' ) ),
			alify_cf_field_def( 'opt_footer_cta_desc', 'Pre-Footer Banner Description', 'textarea', array( 'std' => 'Partner with ALIFY to craft high-converting digital products, iconic brand identities, and scalable web experiences.' ) ),
			alify_cf_field_def( 'opt_footer_brand_desc', 'Footer Brand Bio Paragraph', 'textarea', array( 'std' => 'A design-first digital studio engineering bespoke websites, brand systems, and scalable products for ambitious companies worldwide.' ) ),
			alify_cf_field_def( 'opt_footer_col1_title', 'Footer Menu Column 1 Heading', 'text', array( 'std' => 'Navigation' ) ),
			alify_cf_field_def( 'opt_footer_col2_title', 'Footer Menu Column 2 Heading', 'text', array( 'std' => 'Services' ) ),
			alify_cf_field_def( 'opt_footer_col3_title', 'Footer Contact Column Heading', 'text', array( 'std' => 'Get in Touch' ) ),
			alify_cf_field_def( 'opt_footer_copyright', 'Footer Copyright Text', 'text', array( 'std' => 'ALIFY Studio. All rights reserved.' ) ),
			alify_cf_field_def( 'opt_social_twitter', 'X (Twitter) URL', 'url', array( 'std' => 'https://x.com/alifywork' ) ),
			alify_cf_field_def( 'opt_social_instagram', 'Instagram URL', 'url', array( 'std' => 'https://www.instagram.com/alifywork/' ) ),
			alify_cf_field_def( 'opt_social_facebook', 'Facebook URL', 'url', array( 'std' => 'https://www.facebook.com/alifywork/' ) ),
			alify_cf_field_def( 'opt_social_tiktok', 'TikTok URL', 'url', array( 'std' => 'https://www.tiktok.com/@alifywork' ) ),

			alify_cf_tab_def( 'gtab_reusable', 'Global Reusable Sections' ),
			alify_cf_field_def( 'opt_testi_eyebrow', 'Testimonials Slider — Eyebrow', 'text', array( 'std' => 'Client Stories & Proof' ) ),
			alify_cf_field_def( 'opt_testi_title', 'Testimonials Slider — Heading (`<span>` allowed)', 'text', array( 'std' => 'Trusted by Ambitious <span>Founders & Product Leaders.</span>' ) ),
			alify_cf_field_def( 'opt_faq_eyebrow', 'Reusable FAQs — Eyebrow', 'text', array( 'std' => 'Frequently Asked Questions' ) ),
			alify_cf_field_def( 'opt_faq_title', 'Reusable FAQs — Heading (`<span>` allowed)', 'text', array( 'std' => 'Got Questions? We’ve Got <span>Clear Answers.</span>' ) ),
			alify_cf_field_def( 'opt_faq_desc', 'Reusable FAQs — Intro Description', 'textarea', array( 'std' => 'Everything you need to know about our design sprints, custom engineering workflow, timelines, and ongoing studio partnerships.' ) ),
			alify_cf_field_def( 'opt_blog_eyebrow', 'Blog Slider — Eyebrow', 'text', array( 'std' => 'Studio Journal & Insights' ) ),
			alify_cf_field_def( 'opt_blog_title', 'Blog Slider — Heading (`<span>` allowed)', 'text', array( 'std' => 'Latest Thinking on <span>Design, Motion & Growth.</span>' ) ),
			alify_cf_field_def( 'opt_rcontact_eyebrow', 'Bottom Contact Section — Eyebrow', 'text', array( 'std' => 'Let’s Collaborate' ) ),
			alify_cf_field_def( 'opt_rcontact_title', 'Bottom Contact Section — Heading (`<span>` allowed)', 'text', array( 'std' => 'Ready to Elevate Your <span>Digital Presence?</span>' ) ),
			alify_cf_field_def( 'opt_rcontact_desc', 'Bottom Contact Section — Description', 'textarea', array( 'std' => 'Tell us what you are building — our design directors and lead engineers will respond within one business day with clear next steps.' ) ),
		),
	);

	// 10. Home Page Content (`options:alify_home`)
	$groups[] = array(
		'key'      => 'alify_opt_group_home',
		'title'    => 'Home Page — Hero, Stats Repeater, Sections & 4-Sprint Process',
		'location' => 'options:alify_home',
		'fields'   => array(
			alify_cf_tab_def( 'htab_hero', 'Home Hero & Stat Counters' ),
			alify_cf_field_def( 'home_hero_bg', 'Home Hero Background Image', 'image' ),
			alify_cf_field_def( 'home_status_pill', 'Availability Status Pill', 'text', array( 'std' => 'Creative Digital Studio — Available for New Projects' ) ),
			alify_cf_field_def( 'home_title_before', 'Hero H1 — Part 1', 'text', array( 'std' => 'We Craft Bold' ) ),
			alify_cf_field_def( 'home_title_accent', 'Hero H1 — Highlighted Accent', 'text', array( 'std' => 'Digital Experiences' ) ),
			alify_cf_field_def( 'home_title_after', 'Hero H1 — Part 2', 'text', array( 'std' => 'That Scale Brands.' ) ),
			alify_cf_field_def( 'home_lead', 'Hero Lead Paragraph', 'textarea', array( 'std' => 'ALIFY partners with visionary startups and category-defining brands to design, engineer, and launch high-converting websites, design systems, and digital products.' ) ),
			alify_cf_field_def( 'home_primary_btn', 'Hero Primary CTA Button Label', 'text', array( 'std' => 'Start a Project' ) ),
			alify_cf_field_def( 'home_sec_btn', 'Hero Secondary CTA Button Label', 'text', array( 'std' => 'Explore Our Work' ) ),
			alify_cf_field_def(
				'home_stats_rows',
				'Home Hero Animated Stat Counters (Repeater)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'num', 'Counter Number (e.g. 150)', 'text' ),
						alify_cf_field_def( 'suffix', 'Suffix (e.g. + or %)', 'text' ),
						alify_cf_field_def( 'label', 'Counter Label', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'htab_why', 'Why Partner With ALIFY' ),
			alify_cf_field_def( 'home_why_eyebrow', 'Section Eyebrow', 'text', array( 'std' => 'Why Partner With ALIFY' ) ),
			alify_cf_field_def( 'home_why_title', 'Section Heading (`<span>` allowed)', 'text', array( 'std' => 'Senior Design Craft Meets <span>Revenue-Grade Engineering.</span>' ) ),
			alify_cf_field_def( 'home_why_desc', 'Lead Paragraph', 'textarea', array( 'std' => 'Most agencies hand off static slides or bloated themes. ALIFY embeds a dedicated senior squad of strategists, system designers, and engineers to ship sub-second digital flagships built to dominate your category.' ) ),
			alify_cf_field_def( 'home_why_image', 'Right-Column Studio Visual Image', 'image' ),
			alify_cf_field_def( 'home_why_quote', 'Right-Column Studio Card Quote', 'textarea' ),
			alify_cf_field_def(
				'home_why_pillars_rows',
				'Why Partner With ALIFY — 4 Pillar Badges (Repeater)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-users)', 'text' ),
						alify_cf_field_def( 'title', 'Pillar Title', 'text' ),
						alify_cf_field_def( 'sub', 'Pillar Subtitle', 'text' ),
					),
				)
			),

			alify_cf_tab_def( 'htab_sliders', 'Services, Industries, Work & Case Studies Headings' ),
			alify_cf_field_def( 'home_srv_eyebrow', 'Services Section — Eyebrow', 'text', array( 'std' => 'Core Capabilities' ) ),
			alify_cf_field_def( 'home_srv_title', 'Services Section — Heading (`<span>` allowed)', 'text', array( 'std' => 'End-to-End Digital Craft Built to <span>Move Markets.</span>' ) ),
			alify_cf_field_def( 'home_ind_eyebrow', 'Industries Section — Eyebrow', 'text', array( 'std' => 'Industries We Transform' ) ),
			alify_cf_field_def( 'home_ind_title', 'Industries Section — Heading (`<span>` allowed)', 'text', array( 'std' => 'Tailored Digital Ecosystems for <span>High-Growth Verticals.</span>' ) ),
			alify_cf_field_def( 'home_prj_eyebrow', 'Featured Work — Eyebrow', 'text', array( 'std' => 'Selected Portfolio' ) ),
			alify_cf_field_def( 'home_prj_title', 'Featured Work — Heading (`<span>` allowed)', 'text', array( 'std' => 'Flagship Digital Products That <span>Redefined Their Category.</span>' ) ),
			alify_cf_field_def( 'home_cs_eyebrow', 'Case Studies — Eyebrow', 'text', array( 'std' => 'Executive Case Studies' ) ),
			alify_cf_field_def( 'home_cs_title', 'Case Studies — Heading (`<span>` allowed)', 'text', array( 'std' => 'Where Design Craft Meets <span>Hard Revenue Metrics.</span>' ) ),
			alify_cf_field_def( 'home_cs_desc', 'Case Studies — Lead Description', 'textarea', array( 'std' => 'Select a client engagement below to inspect the exact bottleneck, architectural intervention, before-and-after benchmarks, and verified business ROI.' ) ),

			alify_cf_tab_def( 'htab_process', '4-Sprint Execution Process' ),
			alify_cf_field_def( 'home_proc_eyebrow', 'Process Eyebrow', 'text', array( 'std' => 'Execution Methodology' ) ),
			alify_cf_field_def( 'home_proc_title', 'Process Heading (`<span>` allowed)', 'text', array( 'std' => 'From Strategic Discovery to <span>Sub-Second Launch.</span>' ) ),
			alify_cf_field_def( 'home_proc_desc', 'Process Lead Description', 'textarea', array( 'std' => 'Every ALIFY engagement runs on transparent weekly sprints with senior specialists — zero black-box handoffs, zero guesswork.' ) ),
			alify_cf_field_def(
				'home_proc_rows',
				'Sprint Execution Process Cards (Repeater)',
				'repeater',
				array(
					'max_rows'   => 6,
					'sub_fields' => array(
						alify_cf_field_def( 'badge', 'Sprint Badge (e.g. SPRINT 01)', 'text' ),
						alify_cf_field_def( 'title', 'Sprint Title', 'text' ),
						alify_cf_field_def( 'desc', 'Sprint Description', 'textarea' ),
						alify_cf_field_def( 'deliverable', 'Footer Deliverable Label', 'text' ),
					),
				)
			),
		),
	);

	// 11. Archive & Hub Pages (`options:alify_archives`)
	$groups[] = array(
		'key'      => 'alify_opt_group_archives',
		'title'    => 'Archive & Hub Pages (Services, Work, Industries, Case Studies & Blog)',
		'location' => 'options:alify_archives',
		'fields'   => array(
			alify_cf_tab_def( 'atab_srv', 'Services Archive (/services/)' ),
			alify_cf_field_def( 'arch_srv_hero_eyebrow', 'Hero Eyebrow', 'text', array( 'std' => 'Our Capabilities' ) ),
			alify_cf_field_def( 'arch_srv_hero_title', 'Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'End-to-End <span class="inner-hero-title-accent">Digital Solutions</span> Engineered for Growth.' ) ),
			alify_cf_field_def( 'arch_srv_hero_desc', 'Hero Description', 'textarea', array( 'std' => 'From brand positioning and tokenized UI/UX systems to custom full-stack platforms and technical SEO — explore our 8 core disciplines.' ) ),
			alify_cf_field_def( 'arch_srv_hero_bg', 'Services Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_srv_sec_title', 'Grid Section Heading (`<span>` allowed)', 'text', array( 'std' => 'Specialized Squads Across <span>Design, Code & Growth.</span>' ) ),

			alify_cf_tab_def( 'atab_wrk', 'Work Archive (/work/)' ),
			alify_cf_field_def( 'arch_wrk_hero_eyebrow', 'Hero Eyebrow', 'text', array( 'std' => 'Selected Portfolio' ) ),
			alify_cf_field_def( 'arch_wrk_hero_title', 'Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Crafted Platforms & Brands That <span class="inner-hero-title-accent">Move the Needle.</span>' ) ),
			alify_cf_field_def( 'arch_wrk_hero_desc', 'Hero Description', 'textarea', array( 'std' => 'Explore flagship websites, SaaS platforms, headless storefronts, and design systems shipped by ALIFY squads.' ) ),
			alify_cf_field_def( 'arch_wrk_hero_bg', 'Work Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_wrk_sec_title', 'Grid Section Heading (`<span>` allowed)', 'text', array( 'std' => 'Engineered for Impact Across <span>Every Digital Touchpoint.</span>' ) ),

			alify_cf_tab_def( 'atab_ind_cs', 'Industries & Case Studies' ),
			alify_cf_field_def( 'arch_ind_hero_eyebrow', 'Industries Hero Eyebrow', 'text', array( 'std' => 'Vertical Expertise' ) ),
			alify_cf_field_def( 'arch_ind_hero_title', 'Industries Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Domain-Focused Digital Craft Across <span class="inner-hero-title-accent">Global Industries.</span>' ) ),
			alify_cf_field_def( 'arch_ind_hero_desc', 'Industries Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_ind_hero_bg', 'Industries Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_ind_sec_title', 'Industries Grid Heading (`<span>` allowed)', 'text', array( 'std' => 'Digital Products Engineered for <span>Every Vertical’s Reality.</span>' ) ),
			alify_cf_field_def( 'arch_cs_hero_eyebrow', 'Case Studies Hero Eyebrow', 'text', array( 'std' => 'Proven Impact' ) ),
			alify_cf_field_def( 'arch_cs_hero_title', 'Case Studies Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Deep Dives Into <span class="inner-hero-title-accent">Measurable Client</span> Transformations.' ) ),
			alify_cf_field_def( 'arch_cs_hero_desc', 'Case Studies Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_cs_hero_bg', 'Case Studies Hero Background Image', 'image' ),

			alify_cf_tab_def( 'atab_blog', 'Blog Archive (/blog/)' ),
			alify_cf_field_def( 'arch_blog_hero_eyebrow', 'Blog Hero Eyebrow', 'text', array( 'std' => 'Studio Journal' ) ),
			alify_cf_field_def( 'arch_blog_hero_title', 'Blog Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Perspectives on <span class="inner-hero-title-accent">Design, Code</span> & Digital Growth.' ) ),
			alify_cf_field_def( 'arch_blog_hero_desc', 'Blog Hero Description', 'textarea', array( 'std' => 'Actionable guides, engineering deep dives, and brand strategy insights written by the product designers and developers at ALIFY.' ) ),
			alify_cf_field_def( 'arch_blog_hero_bg', 'Blog Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_blog_card_metric', 'Hero Right Card Metric', 'text', array( 'std' => 'Weekly' ) ),
			alify_cf_field_def( 'arch_blog_card_label', 'Hero Right Card Description', 'text', array( 'std' => 'Fresh perspectives on modern web craft, GSAP motion systems, and scalable UX.' ) ),
			alify_cf_field_def( 'arch_blog_card_cta', 'Hero Right Card Link Label', 'text', array( 'std' => 'Work With Our Team' ) ),
		),
	);

	// 12. Pricing & About Us (`options:alify_pages`)
	$groups[] = array(
		'key'      => 'alify_opt_group_pages',
		'title'    => 'Pricing Tiers, Scope Calculator & About Us Studio Sections',
		'location' => 'options:alify_pages',
		'fields'   => array(
			alify_cf_tab_def( 'ptab_pricing', 'Pricing & Scope Calculator (/pricing/)' ),
			alify_cf_field_def( 'arch_prc_hero_eyebrow', 'Pricing Hero Eyebrow', 'text', array( 'std' => 'Transparent Investment' ) ),
			alify_cf_field_def( 'arch_prc_hero_title', 'Pricing Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Predictable <span class="inner-hero-title-accent">Pricing Models.</span> Senior Studio Execution.' ) ),
			alify_cf_field_def( 'arch_prc_hero_desc', 'Pricing Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_prc_hero_bg', 'Pricing Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_prc_save_pill', 'Retainer Toggle Discount Pill', 'text', array( 'std' => 'Save 15%' ) ),
			alify_cf_field_def(
				'pricing_tiers_rows',
				'Pricing Engagement Tiers (Repeater — 3 Cards)',
				'repeater',
				array(
					'max_rows'   => 3,
					'sub_fields' => array(
						alify_cf_field_def( 'name', 'Tier Name', 'text' ),
						alify_cf_field_def( 'sprint_price', 'Sprint Price (e.g. $4,500)', 'text' ),
						alify_cf_field_def( 'retainer_price', 'Retainer Price (e.g. $3,800)', 'text' ),
						alify_cf_field_def( 'velocity', 'Sprint Timeline (e.g. 3–4 Weeks)', 'text' ),
						alify_cf_field_def( 'summary', 'Tier Summary Paragraph', 'textarea' ),
						alify_cf_field_def( 'features', 'Included Features (1 per line)', 'textarea' ),
					),
				)
			),
			alify_cf_field_def( 'prc_calc_eyebrow', 'Scope Calculator Eyebrow', 'text', array( 'std' => 'Interactive Scope Estimator' ) ),
			alify_cf_field_def( 'prc_calc_title', 'Scope Calculator Heading (`<span>` allowed)', 'text', array( 'std' => 'Configure Your Custom <span>Sprint Estimate</span> in Seconds.' ) ),
			alify_cf_field_def( 'prc_calc_desc', 'Scope Calculator Description', 'textarea' ),
			alify_cf_field_def( 'prc_matrix_eyebrow', 'Comparison Matrix Eyebrow', 'text', array( 'std' => 'Side-by-Side Breakdown' ) ),
			alify_cf_field_def( 'prc_matrix_title', 'Comparison Matrix Heading (`<span>` allowed)', 'text', array( 'std' => 'Compare Every <span>Deliverable & SLA.</span>' ) ),
			alify_cf_field_def( 'prc_matrix_desc', 'Comparison Matrix Description', 'textarea' ),

			alify_cf_tab_def( 'ptab_about_story', 'About Story, Milestones & Mission (/about/)' ),
			alify_cf_field_def( 'arch_abt_hero_eyebrow', 'About Hero Eyebrow', 'text', array( 'std' => 'Who We Are' ) ),
			alify_cf_field_def( 'arch_abt_hero_title', 'About Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Design-Driven Minds Building <span class="inner-hero-title-accent">Iconic Digital</span> Brands.' ) ),
			alify_cf_field_def( 'arch_abt_hero_desc', 'About Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_abt_hero_bg', 'About Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_abt_story_eyebrow', 'Studio Story Eyebrow', 'text', array( 'std' => 'Inside ALIFY Studio' ) ),
			alify_cf_field_def( 'arch_abt_story_title', 'Studio Story Heading (`<span>` allowed)', 'text', array( 'std' => 'Built for Founders Who Refuse <span>Average Digital Products.</span>' ) ),
			alify_cf_field_def( 'arch_abt_story_p1', 'Studio Story Paragraph 1', 'textarea' ),
			alify_cf_field_def( 'arch_abt_story_p2', 'Studio Story Paragraph 2', 'textarea' ),
			alify_cf_field_def( 'arch_abt_story_img', 'Studio Story Visual Image', 'image' ),
			alify_cf_field_def( 'arch_abt_story_quote', 'Studio Story Card Quote', 'textarea' ),
			alify_cf_field_def( 'arch_abt_founder_name', 'Founder Signature Name', 'text', array( 'std' => 'Zayd Alvi & Senior Partners' ) ),
			alify_cf_field_def( 'arch_abt_founder_role', 'Founder Signature Role', 'text', array( 'std' => 'ALIFY Product & Design Leadership' ) ),
			alify_cf_field_def(
				'about_story_pillars_rows',
				'About Story — 4 Pillar Badges (Repeater)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-bolt)', 'text' ),
						alify_cf_field_def( 'title', 'Pillar Title', 'text' ),
						alify_cf_field_def( 'sub', 'Pillar Subtitle', 'text' ),
					),
				)
			),
			alify_cf_field_def( 'arch_abt_mission_title', 'Our Mission Card Heading', 'text', array( 'std' => 'Eliminate the Gap Between Aesthetic Craft and Revenue Performance.' ) ),
			alify_cf_field_def( 'arch_abt_mission_text', 'Our Mission Card Text', 'textarea' ),
			alify_cf_field_def( 'arch_abt_vision_title', 'Our Vision Card Heading', 'text', array( 'std' => 'Set the Global Benchmark for Token-Driven Product Engineering.' ) ),
			alify_cf_field_def( 'arch_abt_vision_text', 'Our Vision Card Text', 'textarea' ),
			alify_cf_field_def(
				'about_principles_rows',
				'About Operating Principles — 4 Bento Cards (Repeater)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-gem)', 'text' ),
						alify_cf_field_def( 'num', 'Card Number (e.g. 01)', 'text' ),
						alify_cf_field_def( 'title', 'Principle Title', 'text' ),
						alify_cf_field_def( 'desc', 'Principle Description', 'textarea' ),
					),
				)
			),

			alify_cf_tab_def( 'ptab_about_team', 'About Milestones & Leadership Repeaters' ),
			alify_cf_field_def(
				'about_metrics_rows',
				'Studio Milestone Counters (Repeater — 4 Boxes)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'prefix', 'Prefix (e.g. $)', 'text' ),
						alify_cf_field_def( 'num', 'Counter Number (e.g. 150)', 'text' ),
						alify_cf_field_def( 'suffix', 'Suffix (e.g. + or M+)', 'text' ),
						alify_cf_field_def( 'title', 'Milestone Title', 'text' ),
						alify_cf_field_def( 'desc', 'Milestone Description', 'textarea' ),
					),
				)
			),
			alify_cf_field_def( 'arch_abt_team_title', 'Leadership Section Heading (`<span>` allowed)', 'text', array( 'std' => 'Meet the Principals Leading <span>Every Engagement.</span>' ) ),
			alify_cf_field_def(
				'about_leaders_rows',
				'Leadership Principals (Repeater — 4 Cards)',
				'repeater',
				array(
					'max_rows'   => 8,
					'sub_fields' => array(
						alify_cf_field_def( 'photo', 'Leader Photo (Media Library)', 'image' ),
						alify_cf_field_def( 'name', 'Leader Name', 'text' ),
						alify_cf_field_def( 'role', 'Leader Role / Title', 'text' ),
						alify_cf_field_def( 'bio', 'Leader Bio', 'textarea' ),
					),
				)
			),
		),
	);

	// 12B. Pricing Scope Calculator Modules & Comparison Matrix (`options:alify_pricing_adv`)
	$groups[] = array(
		'key'      => 'alify_opt_group_pricing_adv',
		'title'    => 'Pricing Interactive Scope Calculator Modules & Comparison Matrix',
		'location' => 'options:alify_pricing_adv',
		'fields'   => array(
			alify_cf_tab_def( 'patab_calc', 'Scope Calculator Capability Modules' ),
			alify_cf_field_def(
				'pricing_calc_modules_rows',
				'Calculator Capability Modules (Repeater — Live USD & Sprint Weeks)',
				'repeater',
				array(
					'max_rows'   => 12,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-code)', 'text' ),
						alify_cf_field_def( 'title', 'Module Name (e.g. Custom Web Platform)', 'text' ),
						alify_cf_field_def( 'cost', 'Base Investment USD (Numeric, e.g. 3200)', 'number' ),
						alify_cf_field_def( 'weeks', 'Base Sprint Weeks (Numeric, e.g. 2)', 'number' ),
						alify_cf_field_def( 'price_label', 'Badge Label (e.g. +$3.2k)', 'text' ),
						alify_cf_field_def( 'checked', 'Checked by Default?', 'toggle' ),
					),
				)
			),

			alify_cf_tab_def( 'patab_matrix', 'Deliverable Comparison Matrix Rows' ),
			alify_cf_field_def(
				'pricing_matrix_rows',
				'Comparison Matrix Table Rows (Set Tier 1 = GROUP for Section Subheader)',
				'repeater',
				array(
					'max_rows'   => 20,
					'sub_fields' => array(
						alify_cf_field_def( 'feature', 'Deliverable / Section Group Title', 'text' ),
						alify_cf_field_def( 'tier1', 'Launch Sprint (`Included`, `—`, `GROUP`, or custom text)', 'text' ),
						alify_cf_field_def( 'tier2', 'Scale Ecosystem (`Included`, `—`, or custom text)', 'text' ),
						alify_cf_field_def( 'tier3', 'Enterprise Squad (`Included`, `—`, or custom text)', 'text' ),
					),
				)
			),
		),
	);

	// 13. Book Call, Contact, Legal & 404 (`options:alify_conversion`)
	$groups[] = array(
		'key'      => 'alify_opt_group_conversion',
		'title'    => 'Book a Call, Contact, Start Project, Thank You, Legal & 404',
		'location' => 'options:alify_conversion',
		'fields'   => array(
			alify_cf_tab_def( 'ctab_book', 'Book a Strategy Call (/book-call/)' ),
			alify_cf_field_def( 'arch_bc_hero_eyebrow', 'Book Call Hero Eyebrow', 'text', array( 'std' => 'Executive Strategy Session' ) ),
			alify_cf_field_def( 'arch_bc_hero_title', 'Book Call Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Book a <span class="inner-hero-title-accent">Strategy Call</span> With Our Principals.' ) ),
			alify_cf_field_def( 'arch_bc_hero_desc', 'Book Call Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_bc_hero_bg', 'Book Call Hero Background Image', 'image' ),
			alify_cf_field_def( 'arch_bc_host_avatar', 'Host Avatar Image', 'image' ),
			alify_cf_field_def( 'arch_bc_host_kicker', 'Host Kicker Label', 'text', array( 'std' => 'Executive Discovery Host' ) ),
			alify_cf_field_def( 'arch_bc_host_name', 'Host Name', 'text', array( 'std' => 'ALIFY Principal Pod' ) ),
			alify_cf_field_def( 'arch_bc_host_role', 'Host Role & Duration', 'text', array( 'std' => 'Managing Partner & Lead Architect · 30 Min' ) ),
			alify_cf_field_def( 'arch_bc_outcomes_title', 'Call Deliverables Heading (`<span>` allowed)', 'text', array( 'std' => 'What You Walk Away With <span>After Our Call.</span>' ) ),
			alify_cf_field_def(
				'book_outcomes_rows',
				'Strategy Call Deliverable Cards (Repeater — 3 Cards)',
				'repeater',
				array(
					'max_rows'   => 6,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-sitemap)', 'text' ),
						alify_cf_field_def( 'title', 'Card Title', 'text' ),
						alify_cf_field_def( 'desc', 'Card Description', 'textarea' ),
					),
				)
			),

			alify_cf_tab_def( 'ctab_contact', 'Contact, Start Project, Reviews & FAQ' ),
			alify_cf_field_def( 'arch_cnt_hero_eyebrow', 'Contact Hero Eyebrow', 'text', array( 'std' => 'Get in Touch' ) ),
			alify_cf_field_def( 'arch_cnt_hero_title', 'Contact Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Let’s Start a Conversation About <span class="inner-hero-title-accent">Your Next Move.</span>' ) ),
			alify_cf_field_def( 'arch_cnt_hero_desc', 'Contact Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_cnt_dir_title', 'Contact Sidebar Card Heading', 'text', array( 'std' => 'Direct Studio Channels' ) ),
			alify_cf_field_def( 'arch_cnt_dir_sub', 'Contact Sidebar Card Subtext', 'textarea' ),
			alify_cf_field_def( 'arch_sp_hero_eyebrow', 'Start Project Hero Eyebrow', 'text', array( 'std' => 'Project Scope Builder' ) ),
			alify_cf_field_def( 'arch_sp_hero_title', 'Start Project Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Share Your Vision — Let’s Build <span class="inner-hero-title-accent">Something Iconic.</span>' ) ),
			alify_cf_field_def( 'arch_sp_hero_desc', 'Start Project Hero Description', 'textarea' ),
			alify_cf_field_def( 'arch_tst_hero_title', 'Testimonials Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Real Words From Leaders Who <span class="inner-hero-title-accent">Scaled With ALIFY.</span>' ) ),
			alify_cf_field_def( 'arch_faq_hero_title', 'FAQ Hub Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Transparent Answers on <span class="inner-hero-title-accent">Scope, Craft & ROI.</span>' ) ),

			alify_cf_tab_def( 'ctab_legal_404', 'Thank You, Legal Docs & 404' ),
			alify_cf_field_def( 'arch_ty_title', 'Thank You Hero Heading (`<span>` allowed)', 'text', array( 'std' => 'Thank You — <span>Your Brief Is With Our Principals.</span>' ) ),
			alify_cf_field_def( 'arch_ty_steps_title', 'Thank You Next-Steps Heading (`<span>` allowed)', 'text', array( 'std' => 'Exactly What Happens <span>From Here.</span>' ) ),
			alify_cf_field_def(
				'thankyou_steps_rows',
				'Thank You Next-24-Hours Cards (Repeater — 3 Steps)',
				'repeater',
				array(
					'max_rows'   => 4,
					'sub_fields' => array(
						alify_cf_field_def( 'icon', 'Font Awesome Icon (e.g. fa-solid fa-paper-plane)', 'text' ),
						alify_cf_field_def( 'window', 'Time Window Badge (e.g. 0 – 2 Hours)', 'text' ),
						alify_cf_field_def( 'title', 'Step Title', 'text' ),
						alify_cf_field_def( 'desc', 'Step Description', 'textarea' ),
					),
				)
			),
			alify_cf_field_def( 'legal_privacy_hero_title', 'Privacy Policy Hero Heading (`<span>` allowed)', 'text' ),
			alify_cf_field_def( 'legal_privacy_updated', 'Privacy Policy Last Updated Label', 'text', array( 'std' => 'Last Updated: October 2026' ) ),
			alify_cf_field_def( 'legal_terms_hero_title', 'Terms of Service Hero Heading (`<span>` allowed)', 'text' ),
			alify_cf_field_def( 'legal_terms_updated', 'Terms of Service Last Updated Label', 'text', array( 'std' => 'Last Updated: October 2026' ) ),
			alify_cf_field_def( 'arch_404_code', '404 Big Display Code', 'text', array( 'std' => '404' ) ),
			alify_cf_field_def( 'arch_404_title', '404 Page Heading', 'text', array( 'std' => 'This Route Doesn’t Exist — Let’s Get You Back on Track.' ) ),
			alify_cf_field_def( 'arch_404_desc', '404 Page Description', 'textarea' ),
		),
	);

	// 13. Single Templates Global (`options:alify_singles`)
	$groups[] = array(
		'key'      => 'alify_opt_group_singles',
		'title'    => 'Single Templates Global Settings (CPT Singles & Blog Single)',
		'location' => 'options:alify_singles',
		'fields'   => array(
			alify_cf_tab_def( 'stab_srv', 'Service & Work Single Global' ),
			alify_cf_field_def( 'sgl_srv_overview_eyebrow', 'Service Overview Eyebrow', 'text', array( 'std' => 'What You Get' ) ),
			alify_cf_field_def( 'sgl_srv_overview_title', 'Service Overview Heading (`<span>` allowed)', 'text', array( 'std' => 'End-to-End Scope & <span>Production Deliverables.</span>' ) ),
			alify_cf_field_def( 'sgl_srv_process_title', 'Service Process Heading (`<span>` allowed)', 'text', array( 'std' => 'A Transparent 4-Sprint <span>Delivery Framework.</span>' ) ),
			alify_cf_field_def( 'sgl_srv_related_title', 'Service Related Projects Heading (`<span>` allowed)', 'text', array( 'std' => 'Featured Projects Shipped by <span>This Capability Squad.</span>' ) ),
			alify_cf_field_def( 'sgl_wrk_related_eyebrow', 'Work Single Related Eyebrow', 'text', array( 'std' => 'Continue Exploring' ) ),
			alify_cf_field_def( 'sgl_wrk_related_title', 'Work Single Related Heading (`<span>` allowed)', 'text', array( 'std' => 'More Selected Work From <span>ALIFY Studio.</span>' ) ),

			alify_cf_tab_def( 'stab_ind_cs', 'Industry & Case Study Single Global' ),
			alify_cf_field_def( 'sgl_ind_overview_title', 'Industry Overview Heading (`<span>` allowed)', 'text', array( 'std' => 'Turning Vertical Complexity Into <span>Competitive Advantage.</span>' ) ),
			alify_cf_field_def( 'sgl_ind_related_title', 'Industry Related Projects Heading (`<span>` allowed)', 'text', array( 'std' => 'Selected Client Launches in <span>Related Markets.</span>' ) ),
			alify_cf_field_def( 'sgl_cs_snapshot_heading', 'Case Study Sidebar Snapshot Heading', 'text', array( 'std' => 'Engagement Snapshot' ) ),
			alify_cf_field_def( 'sgl_cs_related_title', 'Case Study Related Launches Heading (`<span>` allowed)', 'text', array( 'std' => 'Explore Related Client <span>Product Launches.</span>' ) ),

			alify_cf_tab_def( 'stab_blog', 'Blog Single Global (single.php)' ),
			alify_cf_field_def( 'sgl_blog_default_hero_bg', 'Default Single Blog Hero Background Image', 'image' ),
			alify_cf_field_def( 'sgl_blog_toc_heading', 'Left Sticky TOC Heading', 'text', array( 'std' => 'Table of Contents' ) ),
			alify_cf_field_def( 'sgl_blog_sidebar_cta_title', 'Right Sidebar CTA Card Heading', 'text', array( 'std' => 'Have a Project in Mind?' ) ),
			alify_cf_field_def( 'sgl_blog_sidebar_cta_desc', 'Right Sidebar CTA Card Description', 'textarea', array( 'std' => 'Let’s architect a fast, conversion-led digital platform tailored to your brand.' ) ),
			alify_cf_field_def( 'sgl_blog_sidebar_cta_btn', 'Right Sidebar CTA Button Label', 'text', array( 'std' => 'Start a Project' ) ),
			alify_cf_field_def( 'sgl_blog_related_eyebrow', 'Related Articles Eyebrow', 'text', array( 'std' => 'Continue Reading' ) ),
			alify_cf_field_def( 'sgl_blog_related_title', 'Related Articles Heading (`<span>` allowed)', 'text', array( 'std' => 'Related Blogs & <span>Engineering Insights.</span>' ) ),
		),
	);

	// Page copy fields are managed by the paginated editor below; do not register thousands of inputs in Alify CF schemas.

	// Yield to published database Local JSON overrides if an admin synced & published a group in WP Admin
	$db_local_keys = array();
	$db_groups     = get_posts(
		array(
			'post_type'      => 'alify_field_group',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
		)
	);
	foreach ( $db_groups as $db_gid ) {
		$lk = get_post_meta( $db_gid, '_alify_cf_local_key', true );
		if ( is_string( $lk ) && '' !== $lk ) {
			$db_local_keys[ $lk ] = true;
		}
	}

	$syncing_key = ( is_admin() && isset( $_POST['action'], $_POST['key'] ) && 'alify_local_sync' === $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	foreach ( $groups as $group ) {
		if ( ! isset( $db_local_keys[ $group['key'] ] ) && $syncing_key !== $group['key'] ) {
			alify_register_field_group( $group );
		}
	}

	alify_seed_cf_default_option_repeaters_once();
}
add_action( 'init', 'alify_register_all_cf_field_groups', 15 );

/**
 * Return All Registered Group Definitions (Used by Local JSON Exporter / Validator).
 */
function alify_get_cf_group_definitions_for_json() {
	// Re-use the same schema definitions without side effects when exporting Local JSON
	return true;
}

/**
 * 3. Pre-Seed Initial Repeater Rows on Options Pages Once (So Repeaters Open Pre-Populated!).
 */
function alify_seed_cf_default_option_repeaters_once() {
	if ( 'done' === get_option( 'alify_cf_default_repeaters_seeded_v3' ) ) {
		return;
	}

	$home_opt = get_option( 'alify_cf_options_alify_home', array() );
	if ( ! is_array( $home_opt ) ) {
		$home_opt = array();
	}
	if ( empty( $home_opt['home_stats_rows'] ) ) {
		$home_opt['home_stats_rows'] = array(
			array( 'num' => '150', 'suffix' => '+', 'label' => 'Projects Delivered' ),
			array( 'num' => '98', 'suffix' => '%', 'label' => 'Client Retention' ),
			array( 'num' => '12', 'suffix' => '+', 'label' => 'Global Markets' ),
		);
	}
	if ( empty( $home_opt['home_why_pillars_rows'] ) ) {
		$home_opt['home_why_pillars_rows'] = array(
			array( 'icon' => 'fa-solid fa-users', 'title' => '100% Principal-Led', 'sub' => 'Direct access to senior product architects' ),
			array( 'icon' => 'fa-solid fa-gauge-high', 'title' => '99+ Speed Score', 'sub' => 'Zero bloated frameworks or laggy layouts' ),
			array( 'icon' => 'fa-solid fa-calendar-check', 'title' => 'Weekly Live Demos', 'sub' => 'Predictable sprints with staging links' ),
			array( 'icon' => 'fa-solid fa-chart-line', 'title' => 'Conversion-First', 'sub' => 'Every screen tied to pipeline & ROI' ),
		);
	}
	if ( empty( $home_opt['home_proc_rows'] ) ) {
		$home_opt['home_proc_rows'] = array(
			array(
				'badge'       => 'SPRINT 01',
				'title'       => 'Discovery & Blueprint',
				'desc'        => 'Stakeholder alignment, competitor teardown, funnel diagnostics, and technical architecture roadmap.',
				'deliverable' => 'Architecture & KPI Spec',
			),
			array(
				'badge'       => 'SPRINT 02',
				'title'       => 'UX Systems & Brand Craft',
				'desc'        => 'Interactive wireframes, Figma design tokens, signature UI direction, and conversion copywriting.',
				'deliverable' => 'Tokenized Figma System',
			),
			array(
				'badge'       => 'SPRINT 03',
				'title'       => 'Full-Stack & GSAP Build',
				'desc'        => 'Clean semantic code, 60fps GSAP scroll choreography, CMS/API integration, and Core Web Vitals tuning.',
				'deliverable' => 'Staging Flagship Build',
			),
			array(
				'badge'       => 'SPRINT 04',
				'title'       => 'QA, Launch & Growth',
				'desc'        => 'Cross-device QA, technical SEO schema verification, zero-downtime deployment, and conversion tracking.',
				'deliverable' => '99+ CWV Live Launch',
			),
		);
	}
	update_option( 'alify_cf_options_alify_home', $home_opt );

	$pages_opt = get_option( 'alify_cf_options_alify_pages', array() );
	if ( ! is_array( $pages_opt ) ) {
		$pages_opt = array();
	}
	if ( empty( $pages_opt['pricing_tiers_rows'] ) ) {
		$pages_opt['pricing_tiers_rows'] = array(
			array(
				'name'           => 'Launch Sprint',
				'sprint_price'   => '$4,500',
				'retainer_price' => '$3,800',
				'velocity'       => '3–4 Weeks',
				'summary'        => 'Ideal for seed-stage startups and growing brands launching a high-converting flagship marketing site and visual identity foundation.',
				'features'       => "Positioning Workshop & Conversion Wireframes\nCustom Responsive UI Design (Up to 8 Core Pages)\nClean Frontend & WordPress / Headless CMS Build\nGSAP Micro-Interactions & 99+ Core Web Vitals\nTechnical SEO Schema & Analytics Setup",
			),
			array(
				'name'           => 'Scale Ecosystem',
				'sprint_price'   => '$9,500',
				'retainer_price' => '$7,900',
				'velocity'       => '6–8 Weeks',
				'summary'        => 'Built for Series A–B companies scaling pipeline with a full tokenized Figma design system, custom web platform, and CRO architecture.',
				'features'       => "Full Brand System & Tokenized Figma UI Kit\nCustom Platform / Storefront (Up to 18 Templates)\nBespoke GSAP ScrollTrigger & Parallax Storytelling\nCRM, Booking & Lead Routing Integrations\n30-Day Post-Launch CRO & Speed Optimization",
			),
			array(
				'name'           => 'Enterprise Squad',
				'sprint_price'   => '$18,500',
				'retainer_price' => '$14,800',
				'velocity'       => '8–12 Weeks',
				'summary'        => 'Dedicated principal design and full-stack engineering pod for multi-region platforms, complex SaaS ecosystems, and enterprise transformations.',
				'features'       => "Dedicated Principal Designer + Lead Architect Pod\nMulti-Product Design System & Component Governance\nCustom Full-Stack Web App, APIs & Headless Commerce\nSecurity, Accessibility (WCAG AA) & Load Auditing\nPriority Slack Channel & Weekly Executive Demos",
			),
		);
	}
	if ( empty( $pages_opt['about_leaders_rows'] ) ) {
		$pages_opt['about_leaders_rows'] = array(
			array(
				'photo' => '',
				'name'  => 'Zayd Alvi',
				'role'  => 'Founder & Managing Partner',
				'bio'   => '12+ years leading enterprise digital transformations, SaaS positioning, and conversion architecture across global markets.',
			),
			array(
				'photo' => '',
				'name'  => 'Nadia Karim',
				'role'  => 'Head of Design Systems',
				'bio'   => 'Specializes in multi-brand Figma token governance, editorial typography, and tactile product interfaces.',
			),
			array(
				'photo' => '',
				'name'  => 'Tariq Mahmood',
				'role'  => 'Principal Full-Stack Architect',
				'bio'   => 'Obsessed with sub-second Core Web Vitals, clean semantic architecture, and 60fps GSAP motion choreography.',
			),
			array(
				'photo' => '',
				'name'  => 'Hira Siddiqui',
				'role'  => 'Director of Growth & CRO',
				'bio'   => 'Turns qualitative buyer research, funnel telemetry, and technical SEO into compounding pipeline revenue.',
			),
		);
	}
	if ( empty( $pages_opt['about_metrics_rows'] ) ) {
		$pages_opt['about_metrics_rows'] = array(
			array( 'prefix' => '', 'num' => '150', 'suffix' => '+', 'title' => 'Platforms Shipped', 'desc' => 'Web ecosystems, SaaS interfaces, and design systems launched on schedule.' ),
			array( 'prefix' => '$', 'num' => '420', 'suffix' => 'M+', 'title' => 'Client Pipeline Influenced', 'desc' => 'Verified enterprise demos, DTC revenue, and Series A–C fundraising growth.' ),
			array( 'prefix' => '', 'num' => '98', 'suffix' => '%', 'title' => 'Client Retention Rate', 'desc' => 'Long-term product partnerships extending well beyond initial launch.' ),
			array( 'prefix' => '', 'num' => '12', 'suffix' => '+', 'title' => 'Global Markets Served', 'desc' => 'Seamless async & live collaboration across North America, Europe & MENA.' ),
		);
	}
	if ( empty( $pages_opt['about_story_pillars_rows'] ) ) {
		$pages_opt['about_story_pillars_rows'] = array(
			array( 'icon' => 'fa-solid fa-user-check', 'title' => 'Zero Junior Handoffs', 'sub' => 'The principals you meet lead the build' ),
			array( 'icon' => 'fa-solid fa-cubes', 'title' => 'Tokenized Systems', 'sub' => '1:1 parity from Figma to production code' ),
			array( 'icon' => 'fa-solid fa-bolt', 'title' => '99+ Core Web Vitals', 'sub' => 'Engineered without bloated page builders' ),
			array( 'icon' => 'fa-solid fa-earth-americas', 'title' => '12+ Global Markets', 'sub' => 'Serving North America, Europe, UK & MENA' ),
		);
	}
	if ( empty( $pages_opt['about_principles_rows'] ) ) {
		$pages_opt['about_principles_rows'] = array(
			array(
				'icon'  => 'fa-solid fa-compass-drafting',
				'num'   => '01',
				'title' => 'Clarity Before Pixels',
				'desc'  => 'We diagnose buyer psychology, funnel bottlenecks, and competitive positioning before opening Figma — ensuring every layout solves a real commercial problem.',
			),
			array(
				'icon'  => 'fa-solid fa-layer-group',
				'num'   => '02',
				'title' => 'Systems Over One-Offs',
				'desc'  => 'Every project ships with documented typography, color tokens, and reusable component states so your internal team can launch new campaigns in hours.',
			),
			array(
				'icon'  => 'fa-solid fa-gauge-high',
				'num'   => '03',
				'title' => 'Speed Is a Brand Feature',
				'desc'  => 'Sub-second load times, clean semantic HTML, and GPU-accelerated 60fps GSAP motion build immediate buyer trust and compound organic rankings.',
			),
			array(
				'icon'  => 'fa-solid fa-handshake-simple',
				'num'   => '04',
				'title' => 'Radical Sprint Transparency',
				'desc'  => 'Live weekly staging links, async Loom walkthroughs, and direct Slack access to senior leads — never black-box agency silence.',
			),
		);
	}
	update_option( 'alify_cf_options_alify_pages', $pages_opt );

	$padv_opt = get_option( 'alify_cf_options_alify_pricing_adv', array() );
	if ( ! is_array( $padv_opt ) ) {
		$padv_opt = array();
	}
	if ( empty( $padv_opt['pricing_calc_modules_rows'] ) ) {
		$padv_opt['pricing_calc_modules_rows'] = array(
			array( 'icon' => 'fa-solid fa-code', 'title' => 'Custom Web Platform', 'cost' => 3200, 'weeks' => 2, 'price_label' => '+$3.2k', 'checked' => '1' ),
			array( 'icon' => 'fa-solid fa-pen-nib', 'title' => 'UI/UX & Brand System', 'cost' => 2200, 'weeks' => 2, 'price_label' => '+$2.2k', 'checked' => '1' ),
			array( 'icon' => 'fa-solid fa-cubes', 'title' => 'Tokenized Figma Kit', 'cost' => 1800, 'weeks' => 1, 'price_label' => '+$1.8k', 'checked' => '0' ),
			array( 'icon' => 'fa-solid fa-wand-magic-sparkles', 'title' => 'GSAP Motion & 3D Depth', 'cost' => 1500, 'weeks' => 1, 'price_label' => '+$1.5k', 'checked' => '0' ),
			array( 'icon' => 'fa-solid fa-cart-shopping', 'title' => 'E-Commerce / Checkout', 'cost' => 3600, 'weeks' => 2, 'price_label' => '+$3.6k', 'checked' => '0' ),
			array( 'icon' => 'fa-solid fa-magnifying-glass-chart', 'title' => 'Technical SEO & Schema', 'cost' => 1400, 'weeks' => 1, 'price_label' => '+$1.4k', 'checked' => '0' ),
			array( 'icon' => 'fa-solid fa-feather-pointed', 'title' => 'Conversion Copywriting', 'cost' => 1200, 'weeks' => 1, 'price_label' => '+$1.2k', 'checked' => '0' ),
			array( 'icon' => 'fa-solid fa-plug-circle-bolt', 'title' => 'CRM & API Integrations', 'cost' => 1900, 'weeks' => 1, 'price_label' => '+$1.9k', 'checked' => '0' ),
		);
	}
	if ( empty( $padv_opt['pricing_matrix_rows'] ) ) {
		$padv_opt['pricing_matrix_rows'] = array(
			array( 'feature' => '01 · Strategy, Brand & UX Systems', 'tier1' => 'GROUP', 'tier2' => '', 'tier3' => '' ),
			array( 'feature' => 'Stakeholder Positioning & Funnel Blueprint', 'tier1' => 'Included', 'tier2' => 'Included', 'tier3' => 'Included' ),
			array( 'feature' => 'Custom UI Screens & Templates', 'tier1' => 'Up to 8 Pages', 'tier2' => 'Up to 18 Pages', 'tier3' => 'Unlimited / Multi-App' ),
			array( 'feature' => 'Tokenized Figma Design System & UI Kit', 'tier1' => 'Core Style Guide', 'tier2' => 'Full Token Library', 'tier3' => 'Multi-Brand Governance' ),
			array( 'feature' => 'Conversion Copywriting & Messaging', 'tier1' => 'Hero & CTA Polish', 'tier2' => 'Full Funnel Copy', 'tier3' => 'Dedicated Senior Strategist' ),
			array( 'feature' => '02 · Full-Stack Engineering & Motion', 'tier1' => 'GROUP', 'tier2' => '', 'tier3' => '' ),
			array( 'feature' => 'Clean Semantic Frontend & Headless / WP CMS', 'tier1' => 'Included', 'tier2' => 'Included', 'tier3' => 'Custom Stack + APIs' ),
			array( 'feature' => 'GSAP ScrollTrigger & Parallax Storytelling', 'tier1' => 'Signature Reveals', 'tier2' => 'Bespoke Choreography', 'tier3' => 'Interactive Product Demos' ),
			array( 'feature' => 'Core Web Vitals Benchmark Guarantee', 'tier1' => '95+ Score', 'tier2' => '99+ Score', 'tier3' => '99+ + Edge CDN' ),
			array( 'feature' => 'CRM, Analytics & Lead Routing Integration', 'tier1' => 'GA4 + 1 CRM', 'tier2' => 'Multi-Step Attribution', 'tier3' => 'Custom Webhooks & SSO' ),
			array( 'feature' => '03 · Governance, SEO & Post-Launch', 'tier1' => 'GROUP', 'tier2' => '', 'tier3' => '' ),
			array( 'feature' => 'Technical SEO, Schema.org & OpenGraph', 'tier1' => 'Included', 'tier2' => 'Included', 'tier3' => 'Programmatic SEO' ),
			array( 'feature' => 'Post-Launch Hypercare & CRO Testing', 'tier1' => '14-Day Hypercare', 'tier2' => '30-Day CRO Sprint', 'tier3' => 'Continuous Dedicated Pod' ),
			array( 'feature' => 'Direct Slack Channel & Weekly Live Demos', 'tier1' => 'Included', 'tier2' => 'Included', 'tier3' => 'Priority SLA (< 2hr)' ),
		);
	}
	update_option( 'alify_cf_options_alify_pricing_adv', $padv_opt );

	$conv_opt = get_option( 'alify_cf_options_alify_conversion', array() );
	if ( ! is_array( $conv_opt ) ) {
		$conv_opt = array();
	}
	if ( empty( $conv_opt['book_outcomes_rows'] ) ) {
		$conv_opt['book_outcomes_rows'] = array(
			array(
				'icon'  => 'fa-solid fa-sitemap',
				'title' => '01. Architecture & UX Audit',
				'desc'  => 'Candid senior evaluation of your current website or product flow, highlighting immediate conversion and page-speed wins.',
			),
			array(
				'icon'  => 'fa-solid fa-route',
				'title' => '02. Phased Sprint Roadmap',
				'desc'  => 'Clear week-by-week execution plan covering UX design tokens, full-stack engineering, GSAP motion, and launch milestones.',
			),
			array(
				'icon'  => 'fa-solid fa-file-invoice-dollar',
				'title' => '03. Fixed Investment Proposal',
				'desc'  => 'Transparent sprint or retainer pricing delivered within 24 hours of our session — zero hidden hourly surprises.',
			),
		);
	}
	if ( empty( $conv_opt['thankyou_steps_rows'] ) ) {
		$conv_opt['thankyou_steps_rows'] = array(
			array(
				'icon'   => 'fa-solid fa-paper-plane',
				'window' => '0 – 2 Hours',
				'title'  => '01. Principal Triage',
				'desc'   => 'Your project brief is matched directly with the ALIFY practice heads specializing in your industry and technical stack.',
			),
			array(
				'icon'   => 'fa-solid fa-magnifying-glass-chart',
				'window' => 'Within 12 Hours',
				'title'  => '02. Diagnostic Prep',
				'desc'   => 'Our design director and lead architect audit your current digital footprint, speed benchmarks, and funnel opportunities.',
			),
			array(
				'icon'   => 'fa-solid fa-handshake',
				'window' => 'Within 24 Hours',
				'title'  => '03. Roadmap & Call Invite',
				'desc'   => 'You receive a tailored strategy response with clear timeline options, investment benchmarks, and direct calendar access.',
			),
		);
	}
	update_option( 'alify_cf_options_alify_conversion', $conv_opt );

	update_option( 'alify_cf_default_repeaters_seeded_v3', 'done' );
}

/**
 * 4. Auto-Populate `_alify_cf_*` Meta (Including Repeater Rows!) From Starter `_alify_*` Meta
 *    Whenever an Existing Post/CPT/Page Is Opened in WP Admin Before Its First CF Save.
 */
function alify_split_lines_clean( $raw ) {
	if ( empty( $raw ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $raw ) ) ) );
}

function alify_prepopulate_post_cf_from_starter( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || '1' === get_post_meta( $post_id, '_alify_cf_bridge_seeded', true ) ) {
		return;
	}
	$post = get_post( $post_id );
	if ( ! $post ) {
		return;
	}

	$pt  = $post->post_type;
	$set = function ( $cf_key, $val ) use ( $post_id ) {
		if ( ! metadata_exists( 'post', $post_id, '_alify_cf_' . $cf_key ) && '' !== $val && array() !== $val ) {
			update_post_meta( $post_id, '_alify_cf_' . $cf_key, $val );
		}
	};

	if ( 'alify_service' === $pt ) {
		$set( 'svc_notch', get_post_meta( $post_id, '_alify_svc_notch', true ) );
		$set( 'svc_tag', get_post_meta( $post_id, '_alify_svc_tag', true ) );
		$set( 'svc_filter', get_post_meta( $post_id, '_alify_svc_filter', true ) );
		$set( 'svc_icon', get_post_meta( $post_id, '_alify_svc_icon', true ) );
		$set( 'svc_bg_url', get_post_meta( $post_id, '_alify_svc_bg_image', true ) );

		$pts = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_svc_points', true ) ) as $ln ) {
			$pts[] = array( 'point' => $ln );
		}
		$set( 'svc_points_rows', $pts );

		$stats = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_svc_stats', true ) ) as $ln ) {
			$p       = array_map( 'trim', explode( '|', $ln, 2 ) );
			$stats[] = array(
				'val'   => isset( $p[0] ) ? $p[0] : '',
				'label' => isset( $p[1] ) ? $p[1] : '',
			);
		}
		$set( 'svc_stats_rows', $stats );

		$delivs = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_svc_deliverables', true ) ) as $ln ) {
			$p        = array_map( 'trim', explode( '|', $ln, 3 ) );
			$delivs[] = array(
				'icon'  => isset( $p[0] ) ? $p[0] : 'fa-solid fa-layer-group',
				'title' => isset( $p[1] ) ? $p[1] : '',
				'desc'  => isset( $p[2] ) ? $p[2] : '',
			);
		}
		$set( 'svc_deliv_rows', $delivs );
		$set( 'svc_stack_pills', get_post_meta( $post_id, '_alify_svc_stack', true ) );
	} elseif ( 'alify_project' === $pt ) {
		foreach ( array( 'num', 'short_title', 'client', 'sector', 'filter', 'year', 'sprint', 'metric', 'chips', 'case_slug', 'brief', 'craft' ) as $k ) {
			$set( 'proj_' . $k, get_post_meta( $post_id, '_alify_proj_' . $k, true ) );
		}
		$set( 'proj_bg_url', get_post_meta( $post_id, '_alify_proj_bg_image', true ) );

		$o_rows = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_proj_outcomes', true ) ) as $ln ) {
			$p        = array_map( 'trim', explode( '|', $ln, 2 ) );
			$o_rows[] = array(
				'val'   => isset( $p[0] ) ? $p[0] : '',
				'label' => isset( $p[1] ) ? $p[1] : '',
			);
		}
		$set( 'proj_outcomes_rows', $o_rows );

		$g_rows = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_proj_gallery', true ) ) as $ln ) {
			$p        = array_map( 'trim', explode( '|', $ln, 4 ) );
			$g_rows[] = array(
				'img_id'  => '',
				'url'     => isset( $p[0] ) ? $p[0] : '',
				'tag'     => isset( $p[1] ) ? $p[1] : '',
				'title'   => isset( $p[2] ) ? $p[2] : '',
				'caption' => isset( $p[3] ) ? $p[3] : '',
			);
		}
		$set( 'proj_gallery_rows', $g_rows );
	} elseif ( 'alify_industry' === $pt ) {
		foreach ( array( 'code', 'sector_group', 'icon', 'stat' ) as $k ) {
			$set( 'ind_' . $k, get_post_meta( $post_id, '_alify_ind_' . $k, true ) );
		}
		$set( 'ind_bg_url', get_post_meta( $post_id, '_alify_ind_bg_image', true ) );

		$pts = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_ind_points', true ) ) as $ln ) {
			$pts[] = array( 'point' => $ln );
		}
		$set( 'ind_points_rows', $pts );

		$stats = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_ind_stats', true ) ) as $ln ) {
			$p       = array_map( 'trim', explode( '|', $ln, 2 ) );
			$stats[] = array(
				'val'   => isset( $p[0] ) ? $p[0] : '',
				'label' => isset( $p[1] ) ? $p[1] : '',
			);
		}
		$set( 'ind_stats_rows', $stats );

		$chals = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_ind_challenges', true ) ) as $ln ) {
			$p       = array_map( 'trim', explode( '|', $ln, 3 ) );
			$chals[] = array(
				'badge' => isset( $p[0] ) ? $p[0] : '',
				'title' => isset( $p[1] ) ? $p[1] : '',
				'fix'   => isset( $p[2] ) ? $p[2] : '',
			);
		}
		$set( 'ind_challenges_rows', $chals );
	} elseif ( 'alify_case_study' === $pt ) {
		foreach ( array( 'filter', 'tab_index', 'client_short', 'client_kicker', 'sector_badge', 'duration_badge', 'bottleneck', 'architecture', 'chips', 'quote', 'quote_author' ) as $k ) {
			$set( 'cs_' . $k, get_post_meta( $post_id, '_alify_cs_' . $k, true ) );
		}
		$set( 'cs_bg_url', get_post_meta( $post_id, '_alify_cs_bg_image', true ) );

		$benches = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_cs_benchmarks', true ) ) as $ln ) {
			$p         = array_map( 'trim', explode( '|', $ln, 3 ) );
			$benches[] = array(
				'metric' => isset( $p[0] ) ? $p[0] : '',
				'before' => isset( $p[1] ) ? $p[1] : '',
				'after'  => isset( $p[2] ) ? $p[2] : '',
			);
		}
		$set( 'cs_bench_rows', $benches );

		$kpis = array();
		foreach ( alify_split_lines_clean( get_post_meta( $post_id, '_alify_cs_kpis', true ) ) as $ln ) {
			$p      = array_map( 'trim', explode( '|', $ln, 2 ) );
			$kpis[] = array(
				'val'   => isset( $p[0] ) ? $p[0] : '',
				'label' => isset( $p[1] ) ? $p[1] : '',
			);
		}
		$set( 'cs_kpi_rows', $kpis );
	} elseif ( 'alify_testimonial' === $pt ) {
		foreach ( array( 'role', 'initials', 'rating', 'outcome', 'tag', 'filter' ) as $k ) {
			$set( 'testi_' . $k, get_post_meta( $post_id, '_alify_testi_' . $k, true ) );
		}
	} elseif ( 'alify_faq' === $pt ) {
		$set( 'faq_pillar', get_post_meta( $post_id, '_alify_faq_pillar', true ) );
		$show = get_post_meta( $post_id, '_alify_faq_show_reusable', true );
		$set( 'faq_show_reusable', ( '1' === (string) $show || 'yes' === strtolower( (string) $show ) ) ? '1' : '0' );
	} elseif ( 'post' === $pt ) {
		$set( 'post_bg_url', get_post_meta( $post_id, '_alify_post_bg_image', true ) );
		$set( 'post_badge', get_post_meta( $post_id, '_alify_post_badge', true ) );
		$set( 'post_read_time', get_post_meta( $post_id, '_alify_post_read_time', true ) );
		$set( 'post_author_label', get_post_meta( $post_id, '_alify_post_author_label', true ) );
	} elseif ( 'page' === $pt ) {
		$set( 'page_hero_eyebrow', get_post_meta( $post_id, '_alify_page_hero_eyebrow', true ) );
		$set( 'page_hero_title', get_post_meta( $post_id, '_alify_page_hero_title', true ) );
		$set( 'page_hero_desc', get_post_meta( $post_id, '_alify_page_hero_desc', true ) );
		$set( 'page_hero_bg_url', get_post_meta( $post_id, '_alify_page_hero_bg', true ) );
		foreach ( array( 'hide_hero_form', 'hide_testimonials', 'hide_faq', 'hide_blog_slider', 'hide_contact' ) as $hk ) {
			$hv = get_post_meta( $post_id, '_alify_page_' . $hk, true );
			$set( 'page_' . $hk, ( '1' === (string) $hv ) ? '1' : '0' );
		}
	}

	update_post_meta( $post_id, '_alify_cf_bridge_seeded', '1' );
}

function alify_maybe_prepopulate_on_edit_screen() {
	if ( ! alify_has_cf_plugin() || ! isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	alify_prepopulate_post_cf_from_starter( absint( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
add_action( 'load-post.php', 'alify_maybe_prepopulate_on_edit_screen', 5 );

/**
 * Resolve Attachment ID or Fallback URL to Clean Image URL.
 */
function alify_cf_resolve_img( $att_id, $fallback_url = '' ) {
	if ( is_numeric( $att_id ) && (int) $att_id > 0 ) {
		$u = wp_get_attachment_image_url( (int) $att_id, 'full' );
		if ( $u ) {
			return $u;
		}
	}
	return trim( (string) $fallback_url );
}

/**
 * 5. Sync `Alify Custom Field` Post Saves (`save_post` Priority 25, After `Alify_CF::save` at 10)
 *    Back Into `_alify_*` Meta So All Card Renderers, Singles & Galleries Update Live!
 */
function alify_sync_cf_post_save_to_theme_meta( $post_id, $post ) {
	if ( ! alify_has_cf_plugin() || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! $post || ! isset( $_POST['alify_values'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$pt = $post->post_type;

	if ( 'alify_service' === $pt ) {
		foreach ( array( 'notch', 'tag', 'filter', 'icon' ) as $k ) {
			$v = alify_get_field( 'svc_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_svc_' . $k, $v );
			}
		}
		$bg = alify_cf_resolve_img( alify_get_field( 'svc_bg_img', $post_id ), alify_get_field( 'svc_bg_url', $post_id ) );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_svc_bg_image', $bg );
			update_post_meta( $post_id, '_alify_cf_svc_bg_url', $bg );
		}
		$pts = array();
		foreach ( alify_get_rows( 'svc_points_rows', $post_id ) as $r ) {
			if ( ! empty( $r['point'] ) ) {
				$pts[] = trim( (string) $r['point'] );
			}
		}
		update_post_meta( $post_id, '_alify_svc_points', implode( "\n", $pts ) );

		$sts = array();
		foreach ( alify_get_rows( 'svc_stats_rows', $post_id ) as $r ) {
			if ( ! empty( $r['val'] ) || ! empty( $r['label'] ) ) {
				$sts[] = trim( (string) ( $r['val'] ?? '' ) ) . ' | ' . trim( (string) ( $r['label'] ?? '' ) );
			}
		}
		update_post_meta( $post_id, '_alify_svc_stats', implode( "\n", $sts ) );

		$dlv = array();
		foreach ( alify_get_rows( 'svc_deliv_rows', $post_id ) as $r ) {
			if ( ! empty( $r['title'] ) ) {
				$dlv[] = trim( (string) ( $r['icon'] ?? 'fa-solid fa-layer-group' ) ) . ' | ' . trim( (string) $r['title'] ) . ' | ' . trim( (string) ( $r['desc'] ?? '' ) );
			}
		}
		update_post_meta( $post_id, '_alify_svc_deliverables', implode( "\n", $dlv ) );
		$stk = alify_get_field( 'svc_stack_pills', $post_id );
		if ( null !== $stk ) {
			update_post_meta( $post_id, '_alify_svc_stack', $stk );
		}
	} elseif ( 'alify_project' === $pt ) {
		foreach ( array( 'num', 'short_title', 'client', 'sector', 'filter', 'year', 'sprint', 'metric', 'chips', 'case_slug', 'brief', 'craft' ) as $k ) {
			$v = alify_get_field( 'proj_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_proj_' . $k, $v );
			}
		}
		$outc = array();
		foreach ( alify_get_rows( 'proj_outcomes_rows', $post_id ) as $r ) {
			if ( ! empty( $r['val'] ) || ! empty( $r['label'] ) ) {
				$outc[] = trim( (string) ( $r['val'] ?? '' ) ) . ' | ' . trim( (string) ( $r['label'] ?? '' ) );
			}
		}
		if ( ! empty( $outc ) ) {
			update_post_meta( $post_id, '_alify_proj_outcomes', implode( "\n", $outc ) );
		}
		$bg = alify_cf_resolve_img( alify_get_field( 'proj_bg_img', $post_id ), alify_get_field( 'proj_bg_url', $post_id ) );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_proj_bg_image', $bg );
			update_post_meta( $post_id, '_alify_cf_proj_bg_url', $bg );
		}

		// Merge Multi-Image Gallery IDs + Detailed Repeater Screens into `_alify_proj_gallery`
		$gal_lines = array();
		$seen_urls = array();
		foreach ( alify_get_rows( 'proj_gallery_rows', $post_id ) as $idx => $r ) {
			$u = alify_cf_resolve_img( $r['img_id'] ?? '', $r['url'] ?? '' );
			if ( '' !== $u ) {
				$seen_urls[ $u ] = true;
				$gal_lines[]     = implode(
					' | ',
					array(
						$u,
						! empty( $r['tag'] ) ? trim( (string) $r['tag'] ) : 'Product Showcase',
						! empty( $r['title'] ) ? trim( (string) $r['title'] ) : 'Screen 0' . ( $idx + 1 ),
						! empty( $r['caption'] ) ? trim( (string) $r['caption'] ) : 'Engineered by ALIFY Studio.',
					)
				);
			}
		}
		$gal_ids = alify_get_field( 'proj_gallery_media', $post_id );
		if ( is_array( $gal_ids ) ) {
			foreach ( $gal_ids as $gid ) {
				$gu = alify_cf_resolve_img( $gid, '' );
				if ( '' !== $gu && ! isset( $seen_urls[ $gu ] ) ) {
					$att_post        = get_post( (int) $gid );
					$att_title       = ( $att_post && ! empty( $att_post->post_title ) ) ? $att_post->post_title : 'Visual Screen';
					$att_cap         = ( $att_post && ! empty( $att_post->post_excerpt ) ) ? $att_post->post_excerpt : 'Interactive showcase frame.';
					$seen_urls[ $gu ] = true;
					$gal_lines[]     = implode( ' | ', array( $gu, 'Gallery Visual', $att_title, $att_cap ) );
				}
			}
		}
		if ( ! empty( $gal_lines ) ) {
			update_post_meta( $post_id, '_alify_proj_gallery', implode( "\n", $gal_lines ) );
		}
	} elseif ( 'alify_industry' === $pt ) {
		foreach ( array( 'code', 'sector_group', 'icon', 'stat' ) as $k ) {
			$v = alify_get_field( 'ind_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_ind_' . $k, $v );
			}
		}
		$bg = alify_cf_resolve_img( alify_get_field( 'ind_bg_img', $post_id ), alify_get_field( 'ind_bg_url', $post_id ) );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_ind_bg_image', $bg );
			update_post_meta( $post_id, '_alify_cf_ind_bg_url', $bg );
		}
		$pts = array();
		foreach ( alify_get_rows( 'ind_points_rows', $post_id ) as $r ) {
			if ( ! empty( $r['point'] ) ) {
				$pts[] = trim( (string) $r['point'] );
			}
		}
		update_post_meta( $post_id, '_alify_ind_points', implode( "\n", $pts ) );

		$sts = array();
		foreach ( alify_get_rows( 'ind_stats_rows', $post_id ) as $r ) {
			if ( ! empty( $r['val'] ) || ! empty( $r['label'] ) ) {
				$sts[] = trim( (string) ( $r['val'] ?? '' ) ) . ' | ' . trim( (string) ( $r['label'] ?? '' ) );
			}
		}
		update_post_meta( $post_id, '_alify_ind_stats', implode( "\n", $sts ) );

		$chals = array();
		foreach ( alify_get_rows( 'ind_challenges_rows', $post_id ) as $r ) {
			if ( ! empty( $r['title'] ) ) {
				$chals[] = trim( (string) ( $r['badge'] ?? 'Sector Challenge' ) ) . ' | ' . trim( (string) $r['title'] ) . ' | ' . trim( (string) ( $r['fix'] ?? '' ) );
			}
		}
		if ( ! empty( $chals ) ) {
			update_post_meta( $post_id, '_alify_ind_challenges', implode( "\n", $chals ) );
		}
	} elseif ( 'alify_case_study' === $pt ) {
		foreach ( array( 'filter', 'tab_index', 'client_short', 'client_kicker', 'sector_badge', 'duration_badge', 'bottleneck', 'architecture', 'chips', 'quote', 'quote_author' ) as $k ) {
			$v = alify_get_field( 'cs_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_cs_' . $k, $v );
			}
		}
		$bg = alify_cf_resolve_img( alify_get_field( 'cs_bg_img', $post_id ), alify_get_field( 'cs_bg_url', $post_id ) );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_cs_bg_image', $bg );
			update_post_meta( $post_id, '_alify_cf_cs_bg_url', $bg );
		}
		$benches = array();
		foreach ( alify_get_rows( 'cs_bench_rows', $post_id ) as $r ) {
			if ( ! empty( $r['metric'] ) ) {
				$benches[] = trim( (string) $r['metric'] ) . ' | ' . trim( (string) ( $r['before'] ?? '' ) ) . ' | ' . trim( (string) ( $r['after'] ?? '' ) );
			}
		}
		update_post_meta( $post_id, '_alify_cs_benchmarks', implode( "\n", $benches ) );

		$kpis = array();
		foreach ( alify_get_rows( 'cs_kpi_rows', $post_id ) as $r ) {
			if ( ! empty( $r['val'] ) || ! empty( $r['label'] ) ) {
				$kpis[] = trim( (string) ( $r['val'] ?? '' ) ) . ' | ' . trim( (string) ( $r['label'] ?? '' ) );
			}
		}
		update_post_meta( $post_id, '_alify_cs_kpis', implode( "\n", $kpis ) );
	} elseif ( 'alify_testimonial' === $pt ) {
		foreach ( array( 'role', 'initials', 'rating', 'outcome', 'tag', 'filter' ) as $k ) {
			$v = alify_get_field( 'testi_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_testi_' . $k, $v );
			}
		}
	} elseif ( 'alify_faq' === $pt ) {
		$pil = alify_get_field( 'faq_pillar', $post_id );
		if ( null !== $pil ) {
			update_post_meta( $post_id, '_alify_faq_pillar', $pil );
		}
		$sh = alify_get_field( 'faq_show_reusable', $post_id );
		if ( null !== $sh ) {
			update_post_meta( $post_id, '_alify_faq_show_reusable', ( '1' === (string) $sh || true === $sh ) ? '1' : '0' );
		}
	} elseif ( 'post' === $pt ) {
		$bg = alify_cf_resolve_img( alify_get_field( 'post_bg_img', $post_id ), alify_get_field( 'post_bg_url', $post_id ) );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_post_bg_image', $bg );
			update_post_meta( $post_id, '_alify_cf_post_bg_url', $bg );
		}
		foreach ( array( 'badge', 'read_time', 'author_label' ) as $k ) {
			$v = alify_get_field( 'post_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_post_' . $k, $v );
			}
		}
	} elseif ( 'page' === $pt ) {
		foreach ( array( 'hero_eyebrow', 'hero_title', 'hero_desc' ) as $k ) {
			$v = alify_get_field( 'page_' . $k, $post_id );
			if ( null !== $v ) {
				update_post_meta( $post_id, '_alify_page_' . $k, $v );
			}
		}
		$bg = alify_cf_resolve_img( alify_get_field( 'page_hero_bg_img', $post_id ), alify_get_field( 'page_hero_bg_url', $post_id ) );
		update_post_meta( $post_id, '_alify_page_hero_bg', $bg );
		if ( '' !== $bg ) {
			update_post_meta( $post_id, '_alify_cf_page_hero_bg_url', $bg );
		}
		foreach ( array( 'hide_hero_form', 'hide_testimonials', 'hide_faq', 'hide_blog_slider', 'hide_contact' ) as $hk ) {
			$hv = alify_get_field( 'page_' . $hk, $post_id );
			if ( null !== $hv ) {
				update_post_meta( $post_id, '_alify_page_' . $hk, ( '1' === (string) $hv || true === $hv ) ? '1' : '0' );
			}
		}
	}
}
add_action( 'save_post', 'alify_sync_cf_post_save_to_theme_meta', 25, 2 );

/**
 * Sync `Site Settings` (`alify_cf_options_site`) Saves Into Customizer `theme_mod`s Automatically.
 */
function alify_sync_cf_site_options_to_customizer( $old_value, $value = null ) {
	$data = is_array( $value ) ? $value : ( is_array( $old_value ) ? $old_value : array() );
	$map  = array(
		'opt_email'               => 'alify_email',
		'opt_whatsapp'            => 'alify_whatsapp',
		'opt_whatsapp_url'        => 'alify_whatsapp_url',
		'opt_hq'                  => 'alify_hq',
		'opt_header_contact_text' => 'alify_contact_btn_text',
		'opt_header_cta_text'     => 'alify_cta_text',
		'opt_footer_col1_title'   => 'alify_footer_col1_title',
		'opt_footer_col2_title'   => 'alify_footer_col2_title',
		'opt_social_twitter'      => 'alify_twitter',
		'opt_social_instagram'    => 'alify_instagram',
		'opt_social_facebook'     => 'alify_facebook',
		'opt_social_tiktok'       => 'alify_tiktok',
		'opt_brand_logo_height'   => 'alify_logo_height',
	);
	foreach ( $map as $opt_k => $mod_k ) {
		if ( isset( $data[ $opt_k ] ) && '' !== $data[ $opt_k ] ) {
			set_theme_mod( $mod_k, $data[ $opt_k ] );
		}
	}
}
add_action( 'update_option_alify_cf_options_site', 'alify_sync_cf_site_options_to_customizer', 10, 2 );
add_action( 'add_option_alify_cf_options_site', 'alify_sync_cf_site_options_to_customizer', 10, 2 );
