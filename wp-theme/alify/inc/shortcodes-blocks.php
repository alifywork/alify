<?php
/**
 * ALIFY Reusable Section Shortcodes & Dynamic Gutenberg Blocks (`alify_register_block_type`)
 * Lets editors compose custom landing pages in WordPress Classic or Block Editor using live ALIFY sections,
 * while automatically enqueuing only the required section CSS & JS on demand!
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow `background-image` in `safe_style_css` so Gutenberg Dynamic Blocks (`wp_kses_post`)
 * preserve inline card & section background-image styles cleanly.
 */
function alify_allow_bg_image_in_safe_style_css( $styles ) {
	if ( is_array( $styles ) ) {
		foreach ( array( 'background-image', 'background-size', 'background-position', 'background-repeat' ) as $prop ) {
			if ( ! in_array( $prop, $styles, true ) ) {
				$styles[] = $prop;
			}
		}
	}
	return $styles;
}
add_filter( 'safe_style_css', 'alify_allow_bg_image_in_safe_style_css' );

/**
 * Extract a Fully Transformed `<section class="...$section_class...">...</section>` From a Theme Template Part,
 * With Optional Per-Instance Eyebrow & Heading Overrides!
 */
function alify_extract_live_section_markup( $part_slug, $section_class, $eyebrow = '', $title = '' ) {
	$part_file = ALIFY_THEME_DIR . '/template-parts/pages/' . $part_slug . '.php';
	if ( ! file_exists( $part_file ) ) {
		return '';
	}

	ob_start();
	include $part_file;
	$raw_page = (string) ob_get_clean();

	$pattern = '/<section\b[^>]*class="[^"]*\b' . preg_quote( $section_class, '/' ) . '\b[^"]*"[^>]*>[\s\S]*?<\/section>/i';
	if ( ! preg_match( $pattern, $raw_page, $m ) ) {
		return '';
	}

	$sec = alify_transform_markup( $m[0], $part_slug );

	if ( '' !== trim( (string) $eyebrow ) && function_exists( 'alify_cf_set_eyebrow' ) ) {
		$sec = alify_cf_set_eyebrow( $sec, trim( (string) $eyebrow ) );
	}
	if ( '' !== trim( (string) $title ) ) {
		$sec = preg_replace( '/(<h2\b[^>]*>)[\s\S]*?(<\/h2>)/i', '${1}' . esc_html( trim( (string) $title ) ) . '${2}', $sec, 1 );
	}

	return $sec;
}

/**
 * Register All 11 `[alify_*]` Shortcodes for Classic, Gutenberg Shortcode Block, or Custom Templates.
 */
function alify_register_reusable_shortcodes() {
	add_shortcode(
		'alify_services',
		function ( $atts ) {
			$a    = shortcode_atts( array( 'layout' => 'slider', 'eyebrow' => '', 'title' => '' ), $atts, 'alify_services' );
			$grid = ( 'grid' === strtolower( (string) $a['layout'] ) );
			return alify_extract_live_section_markup( $grid ? 'services' : 'home', $grid ? 'services-grid-section' : 'home-services-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_projects',
		function ( $atts ) {
			$a    = shortcode_atts( array( 'layout' => 'panels', 'eyebrow' => '', 'title' => '' ), $atts, 'alify_projects' );
			$grid = ( 'grid' === strtolower( (string) $a['layout'] ) );
			return alify_extract_live_section_markup( $grid ? 'work' : 'home', $grid ? 'projects-grid-section' : 'home-projects-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_industries',
		function ( $atts ) {
			$a    = shortcode_atts( array( 'layout' => 'slider', 'eyebrow' => '', 'title' => '' ), $atts, 'alify_industries' );
			$grid = ( 'grid' === strtolower( (string) $a['layout'] ) );
			return alify_extract_live_section_markup( $grid ? 'industries' : 'home', $grid ? 'industries-grid-section' : 'home-industries-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_case_studies',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_case_studies' );
			return alify_extract_live_section_markup( 'home', 'home-case-studies-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_process',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_process' );
			return alify_extract_live_section_markup( 'home', 'studio-process-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_pricing',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_pricing' );
			return alify_extract_live_section_markup( 'pricing', 'pricing-tiers-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_scope_calculator',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_scope_calculator' );
			return alify_extract_live_section_markup( 'pricing', 'pricing-calc-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_testimonials',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_testimonials' );
			return alify_extract_live_section_markup( 'home', 'testimonials-slider-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_faqs',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_faqs' );
			return alify_extract_live_section_markup( 'home', 'faq-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_blog_slider',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_blog_slider' );
			return alify_extract_live_section_markup( 'home', 'home-blog-section', $a['eyebrow'], $a['title'] );
		}
	);

	add_shortcode(
		'alify_contact_section',
		function ( $atts ) {
			$a = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'alify_contact_section' );
			return alify_extract_live_section_markup( 'home', 'reusable-contact-section', $a['eyebrow'], $a['title'] );
		}
	);
}
add_action( 'init', 'alify_register_reusable_shortcodes', 14 );

/**
 * Register 8 Dynamic Gutenberg Blocks Powered by `Alify Custom Field` (`alify_register_block_type`).
 */
function alify_register_cf_gutenberg_blocks() {
	if ( ! function_exists( 'alify_register_block_type' ) ) {
		return;
	}

	alify_register_block_type(
		'alify/services-showcase',
		array(
			'title'           => 'ALIFY Services Showcase',
			'description'     => 'Live WordPress Services CPT slider or filterable grid with custom eyebrow & heading.',
			'fields'          => array(
				array(
					'name'    => 'layout',
					'label'   => 'Display Mode',
					'type'    => 'select',
					'default' => 'slider',
					'choices' => array(
						'slider' => 'Signature Clipped Slider',
						'grid'   => 'Filterable Services Grid',
					),
				),
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				$grid = ( 'grid' === ( $values['layout'] ?? 'slider' ) );
				return alify_extract_live_section_markup( $grid ? 'services' : 'home', $grid ? 'services-grid-section' : 'home-services-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/projects-showcase',
		array(
			'title'           => 'ALIFY Projects Showcase',
			'description'     => 'Hover-expanding flagship panels or 2-per-row portfolio grid from Projects CPT.',
			'fields'          => array(
				array(
					'name'    => 'layout',
					'label'   => 'Display Mode',
					'type'    => 'select',
					'default' => 'panels',
					'choices' => array(
						'panels' => 'Interactive Expanding Panels',
						'grid'   => '2-Column Work Grid',
					),
				),
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				$grid = ( 'grid' === ( $values['layout'] ?? 'panels' ) );
				return alify_extract_live_section_markup( $grid ? 'work' : 'home', $grid ? 'projects-grid-section' : 'home-projects-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/industries-showcase',
		array(
			'title'           => 'ALIFY Industries Showcase',
			'description'     => 'Sector expertise slider or filterable grid powered by Industries CPT.',
			'fields'          => array(
				array(
					'name'    => 'layout',
					'label'   => 'Display Mode',
					'type'    => 'select',
					'default' => 'slider',
					'choices' => array(
						'slider' => 'Horizontal Sector Slider',
						'grid'   => 'Filterable Sector Grid',
					),
				),
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				$grid = ( 'grid' === ( $values['layout'] ?? 'slider' ) );
				return alify_extract_live_section_markup( $grid ? 'industries' : 'home', $grid ? 'industries-grid-section' : 'home-industries-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/case-studies-stage',
		array(
			'title'           => 'ALIFY Case Studies Stage',
			'description'     => 'Interactive Case Studies benchmark switcher powered by Case Studies CPT.',
			'fields'          => array(
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				return alify_extract_live_section_markup( 'home', 'home-case-studies-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/sprint-process',
		array(
			'title'           => 'ALIFY 4-Sprint Process',
			'description'     => '4-sprint agency delivery methodology cards synced with ALIFY Theme Settings.',
			'fields'          => array(
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				return alify_extract_live_section_markup( 'home', 'studio-process-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/pricing-tiers',
		array(
			'title'           => 'ALIFY Pricing Tiers',
			'description'     => '3-tier Sprint vs. Retainer pricing cards synced with Pricing & About Us Settings.',
			'fields'          => array(
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				return alify_extract_live_section_markup( 'pricing', 'pricing-tiers-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/testimonials-slider',
		array(
			'title'           => 'ALIFY Testimonials Slider',
			'description'     => '2-row client reviews slider powered by Testimonials CPT.',
			'fields'          => array(
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				return alify_extract_live_section_markup( 'home', 'testimonials-slider-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);

	alify_register_block_type(
		'alify/faq-accordion',
		array(
			'title'           => 'ALIFY FAQs Accordion',
			'description'     => 'Interactive FAQ accordion powered by FAQs CPT.',
			'fields'          => array(
				array( 'name' => 'eyebrow', 'label' => 'Custom Eyebrow (optional)', 'type' => 'text' ),
				array( 'name' => 'title', 'label' => 'Custom Heading (optional)', 'type' => 'text' ),
			),
			'render_callback' => function ( $values ) {
				return alify_extract_live_section_markup( 'home', 'faq-section', $values['eyebrow'] ?? '', $values['title'] ?? '' );
			},
		)
	);
}
add_action( 'init', 'alify_register_cf_gutenberg_blocks', 18 );

/**
 * Auto-Detect Any `[alify_*]` Shortcodes or `wp:alify/*` Blocks in Current Page Content
 * and Enqueue Matching Component CSS & JS Automatically!
 */
function alify_auto_enqueue_shortcode_and_block_assets( $components ) {
	if ( ! is_singular() ) {
		return $components;
	}
	$post = get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return $components;
	}

	$content   = (string) $post->post_content;
	$detectors = array(
		'services'     => array( 'alify_services', 'wp:alify/services-showcase' ),
		'projects'     => array( 'alify_projects', 'wp:alify/projects-showcase' ),
		'industries'   => array( 'alify_industries', 'wp:alify/industries-showcase' ),
		'case-studies' => array( 'alify_case_studies', 'wp:alify/case-studies-stage' ),
		'about-studio' => array( 'alify_process', 'wp:alify/sprint-process' ),
		'pricing'      => array( 'alify_pricing', 'alify_scope_calculator', 'wp:alify/pricing-tiers' ),
		'testimonials' => array( 'alify_testimonials', 'wp:alify/testimonials-slider' ),
		'faq'          => array( 'alify_faqs', 'wp:alify/faq-accordion' ),
		'blog'         => array( 'alify_blog_slider' ),
		'forms'        => array( 'alify_contact_section' ),
	);

	foreach ( $detectors as $handle => $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $content, $needle ) ) {
				if ( ! in_array( $handle, $components['css'], true ) ) {
					$components['css'][] = $handle;
				}
				if ( ! in_array( $handle, $components['js'], true ) ) {
					$components['js'][] = $handle;
				}
				break;
			}
		}
	}

	return $components;
}
add_filter( 'alify_route_components', 'alify_auto_enqueue_shortcode_and_block_assets' );
