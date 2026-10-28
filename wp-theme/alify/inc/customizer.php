<?php
/**
 * WordPress Customizer Options:
 *   1. Site Identity — Custom Logo Height Control
 *   2. ALIFY Studio Contact & Brand
 *   3. ALIFY Home Page Hero (Copy, Image Upload & Animated Stat Counters)
 *   4. ALIFY Pricing Tiers & Billing (All 3 Plans — Sprint & Retainer Sync)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_customize_register( $wp_customize ) {

	// 1. Custom Logo Height inside Core `title_tagline` (Site Identity) Section
	$wp_customize->add_setting(
		'alify_logo_height',
		array(
			'default'           => 40,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'alify_logo_height',
		array(
			'label'       => __( 'Custom Logo Max Height (px)', 'alify' ),
			'description' => __( 'Applies when an image logo is uploaded above (24–72px). Leave empty logo to keep signature △LIFY typography.', 'alify' ),
			'section'     => 'title_tagline',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 24,
				'max'  => 72,
				'step' => 2,
			),
		)
	);

	// 2. ALIFY Studio Contact & Brand Section
	$wp_customize->add_section(
		'alify_studio_settings',
		array(
			'title'       => __( 'ALIFY Studio Contact & Brand', 'alify' ),
			'description' => __( 'Configure global agency email, WhatsApp, headquarters, header buttons, and social links.', 'alify' ),
			'priority'    => 30,
		)
	);

	$brand_fields = array(
		'alify_email'             => array( 'Studio Email Address', 'info@alify.site', 'sanitize_email', 'text' ),
		'alify_whatsapp'          => array( 'WhatsApp Display Number', '+92 301 9328094', 'sanitize_text_field', 'text' ),
		'alify_whatsapp_url'      => array( 'WhatsApp Direct Link (wa.me)', 'https://wa.me/923019328094', 'esc_url_raw', 'url' ),
		'alify_hq'                => array( 'Studio Headquarters Label', 'Karachi, PK — Global Delivery', 'sanitize_text_field', 'text' ),
		'alify_contact_btn_text'  => array( 'Header Secondary Button Label', 'Contact Us', 'sanitize_text_field', 'text' ),
		'alify_cta_text'          => array( 'Header Primary CTA Label', 'Start a Project', 'sanitize_text_field', 'text' ),
		'alify_footer_col1_title' => array( 'Footer Menu 1 Heading', 'Navigation', 'sanitize_text_field', 'text' ),
		'alify_footer_col2_title' => array( 'Footer Menu 2 Heading', 'Services', 'sanitize_text_field', 'text' ),
		'alify_twitter'           => array( 'X (Twitter) URL', 'https://x.com/alifywork', 'esc_url_raw', 'url' ),
		'alify_instagram'         => array( 'Instagram URL', 'https://www.instagram.com/alifywork/', 'esc_url_raw', 'url' ),
		'alify_facebook'          => array( 'Facebook URL', 'https://www.facebook.com/alifywork/', 'esc_url_raw', 'url' ),
		'alify_tiktok'            => array( 'TikTok URL', 'https://www.tiktok.com/@alifywork', 'esc_url_raw', 'url' ),
		'alify_linkedin'          => array( 'LinkedIn URL (Optional)', '', 'esc_url_raw', 'url' ),
		'alify_behance'           => array( 'Behance URL (Optional)', '', 'esc_url_raw', 'url' ),
		'alify_dribbble'          => array( 'Dribbble URL (Optional)', '', 'esc_url_raw', 'url' ),
	);

	foreach ( $brand_fields as $key => $cfg ) {
		list( $label, $default, $sanitizer, $ctrl_type ) = $cfg;
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitizer,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'alify_studio_settings',
				'type'    => $ctrl_type,
			)
		);
	}

	// 3. ALIFY Home Page Hero Section
	$wp_customize->add_section(
		'alify_home_hero_section',
		array(
			'title'       => __( 'ALIFY Home Page Hero', 'alify' ),
			'description' => __( 'Customize the Front Page centered hero headline, lead copy, background image, and animated KPI counters.', 'alify' ),
			'priority'    => 31,
		)
	);

	$wp_customize->add_setting(
		'alify_home_bg_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	if ( class_exists( 'WP_Customize_Image_Control' ) ) {
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'alify_home_bg_image',
				array(
					'label'   => __( 'Home Hero Background Image (Optional Override)', 'alify' ),
					'section' => 'alify_home_hero_section',
				)
			)
		);
	}

	$home_hero_fields = array(
		'alify_home_status_pill'   => array( 'Availability Status Pill', 'Creative Digital Studio — Available for New Projects', 'text' ),
		'alify_home_title_before'  => array( 'Hero Headline (Part 1)', 'We Craft Bold', 'text' ),
		'alify_home_title_accent'  => array( 'Hero Headline (Highlighted)', 'Digital Experiences', 'text' ),
		'alify_home_title_after'   => array( 'Hero Headline (Part 2)', 'That Scale Brands.', 'text' ),
		'alify_home_lead'          => array( 'Hero Lead Paragraph', 'From bespoke web platforms and iconic brand identities to high-converting digital products — ALIFY partners with ambitious founders and enterprises to turn bold ideas into measurable growth.', 'textarea' ),
		'alify_home_sec_btn_label' => array( 'Secondary CTA Button Label', 'Explore Our Work', 'text' ),
		'alify_home_stat1_val'     => array( 'Stat 1 Number (e.g. 150)', '150', 'text' ),
		'alify_home_stat1_suf'     => array( 'Stat 1 Suffix (e.g. +)', '+', 'text' ),
		'alify_home_stat1_lbl'     => array( 'Stat 1 Label', 'Projects Delivered', 'text' ),
		'alify_home_stat2_val'     => array( 'Stat 2 Number (e.g. 98)', '98', 'text' ),
		'alify_home_stat2_suf'     => array( 'Stat 2 Suffix (e.g. %)', '%', 'text' ),
		'alify_home_stat2_lbl'     => array( 'Stat 2 Label', 'Client Retention', 'text' ),
		'alify_home_stat3_val'     => array( 'Stat 3 Number (e.g. 12)', '12', 'text' ),
		'alify_home_stat3_suf'     => array( 'Stat 3 Suffix (e.g. +)', '+', 'text' ),
		'alify_home_stat3_lbl'     => array( 'Stat 3 Label', 'Global Markets', 'text' ),
	);

	foreach ( $home_hero_fields as $key => $cfg ) {
		list( $label, $default, $ctrl_type ) = $cfg;
		$sanitizer = ( 'textarea' === $ctrl_type ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitizer,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'alify_home_hero_section',
				'type'    => $ctrl_type,
			)
		);
	}

	// 4. ALIFY Pricing Tiers & Billing Section
	$wp_customize->add_section(
		'alify_pricing_section',
		array(
			'title'       => __( 'ALIFY Pricing Tiers & Billing', 'alify' ),
			'description' => __( 'Edit the 3 studio pricing tiers on /pricing/. Both Fixed-Scope Sprint and Monthly Retainer switcher values sync automatically.', 'alify' ),
			'priority'    => 32,
		)
	);

	$pricing_fields = array(
		'alify_pricing_save_pill' => array( 'Retainer Save Badge Text', 'Save 15%', 'text' ),

		// Tier 1: Launch Sprint
		'alify_plan1_name'        => array( 'Tier 1 — Plan Name', 'Launch Sprint', 'text' ),
		'alify_plan1_sprint'      => array( 'Tier 1 — Fixed Sprint Price', '$4,500', 'text' ),
		'alify_plan1_retainer'    => array( 'Tier 1 — Monthly Retainer Price', '$3,800', 'text' ),
		'alify_plan1_velocity'    => array( 'Tier 1 — Sprint Timeline', '3–4 Weeks', 'text' ),
		'alify_plan1_summary'     => array( 'Tier 1 — Summary Description', 'Ideal for seed-stage startups and growing brands launching a high-converting flagship marketing site and visual identity foundation.', 'textarea' ),
		'alify_plan1_features'    => array( 'Tier 1 — Custom Features Override (1 per line, leave blank for default)', '', 'textarea' ),

		// Tier 2: Scale Ecosystem
		'alify_plan2_name'        => array( 'Tier 2 — Plan Name', 'Scale Ecosystem', 'text' ),
		'alify_plan2_sprint'      => array( 'Tier 2 — Fixed Sprint Price', '$9,500', 'text' ),
		'alify_plan2_retainer'    => array( 'Tier 2 — Monthly Retainer Price', '$7,900', 'text' ),
		'alify_plan2_velocity'    => array( 'Tier 2 — Sprint Timeline', '6–8 Weeks', 'text' ),
		'alify_plan2_summary'     => array( 'Tier 2 — Summary Description', '', 'textarea' ),
		'alify_plan2_features'    => array( 'Tier 2 — Custom Features Override (1 per line, leave blank for default)', '', 'textarea' ),

		// Tier 3: Enterprise Squad
		'alify_plan3_name'        => array( 'Tier 3 — Plan Name', 'Enterprise Squad', 'text' ),
		'alify_plan3_sprint'      => array( 'Tier 3 — Fixed Sprint Price', '$18,500', 'text' ),
		'alify_plan3_retainer'    => array( 'Tier 3 — Monthly Retainer Price', '$14,800', 'text' ),
		'alify_plan3_velocity'    => array( 'Tier 3 — Sprint Timeline', '8–12 Weeks', 'text' ),
		'alify_plan3_summary'     => array( 'Tier 3 — Summary Description', '', 'textarea' ),
		'alify_plan3_features'    => array( 'Tier 3 — Custom Features Override (1 per line, leave blank for default)', '', 'textarea' ),
	);

	foreach ( $pricing_fields as $key => $cfg ) {
		list( $label, $default, $ctrl_type ) = $cfg;
		$sanitizer = ( 'textarea' === $ctrl_type ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitizer,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'alify_pricing_section',
				'type'    => $ctrl_type,
			)
		);
	}
}
add_action( 'customize_register', 'alify_customize_register' );

/**
 * Output Custom Logo Max-Height CSS Variable When Customized.
 */
function alify_customizer_head_css() {
	$logo_h = absint( get_theme_mod( 'alify_logo_height', 40 ) );
	if ( $logo_h > 0 && 40 !== $logo_h ) {
		$shrunk_h = max( 22, (int) round( $logo_h * 0.85 ) );
		echo '<style id="alify-custom-logo-size">.alify-custom-logo-img{max-height:' . esc_attr( (string) $logo_h ) . 'px;}.site-header.is-scrolled .alify-custom-logo-img{max-height:' . esc_attr( (string) $shrunk_h ) . 'px;}</style>' . "\n";
	}
}
add_action( 'wp_head', 'alify_customizer_head_css', 30 );

/**
 * One-Time Auto-Upgrade of Legacy Placeholder Contact & Social `theme_mod`s + Starter FAQ Copy.
 */
function alify_migrate_contact_socials_v2() {
	if ( 'done' === get_option( 'alify_contact_socials_v2_synced' ) ) {
		return;
	}

	$upgrades = array(
		'alify_whatsapp'     => array( array( '+90 301 9328094', '+903019328094' ), '+92 301 9328094' ),
		'alify_whatsapp_url' => array( array( 'https://wa.me/903019328094' ), 'https://wa.me/923019328094' ),
		'alify_twitter'      => array( array( 'https://x.com', 'https://x.com/', 'https://twitter.com' ), 'https://x.com/alifywork' ),
		'alify_instagram'    => array( array( 'https://instagram.com', 'https://instagram.com/', 'https://www.instagram.com' ), 'https://www.instagram.com/alifywork/' ),
		'alify_linkedin'     => array( array( 'https://linkedin.com', 'https://linkedin.com/' ), '' ),
		'alify_behance'      => array( array( 'https://behance.net', 'https://behance.net/' ), '' ),
		'alify_dribbble'     => array( array( 'https://dribbble.com', 'https://dribbble.com/' ), '' ),
	);

	foreach ( $upgrades as $mod_key => $pair ) {
		list( $old_list, $new_val ) = $pair;
		$cur                       = get_theme_mod( $mod_key, null );
		if ( null === $cur || in_array( trim( (string) $cur ), $old_list, true ) ) {
			set_theme_mod( $mod_key, $new_val );
		}
	}

	if ( null === get_theme_mod( 'alify_facebook', null ) ) {
		set_theme_mod( 'alify_facebook', 'https://www.facebook.com/alifywork/' );
	}
	if ( null === get_theme_mod( 'alify_tiktok', null ) ) {
		set_theme_mod( 'alify_tiktok', 'https://www.tiktok.com/@alifywork' );
	}

	// Update any already-imported FAQ posts containing the old +90 number
	$faqs = get_posts(
		array(
			'post_type'      => 'alify_faq',
			'posts_per_page' => 50,
			'post_status'    => 'any',
		)
	);
	foreach ( $faqs as $fq ) {
		if ( false !== strpos( $fq->post_content, '90 301 9328094' ) || false !== strpos( $fq->post_content, '903019328094' ) ) {
			$updated_body = str_replace(
				array( '+90 301 9328094', 'https://wa.me/903019328094' ),
				array( '+92 301 9328094', 'https://wa.me/923019328094' ),
				$fq->post_content
			);
			wp_update_post(
				array(
					'ID'           => $fq->ID,
					'post_content' => $updated_body,
				)
			);
		}
	}

	update_option( 'alify_contact_socials_v2_synced', 'done' );
}
add_action( 'init', 'alify_migrate_contact_socials_v2', 20 );
