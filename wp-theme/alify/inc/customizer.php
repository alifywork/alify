<?php
/**
 * WordPress Customizer Options (Email, WhatsApp, Headquarters, Socials & CTA)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'alify_studio_settings',
		array(
			'title'       => __( 'ALIFY Studio Contact & Brand', 'alify' ),
			'description' => __( 'Configure global agency email, WhatsApp, headquarters, and social links.', 'alify' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'alify_email'        => array( 'Studio Email Address', 'info@alify.site', 'sanitize_email' ),
		'alify_whatsapp'     => array( 'WhatsApp Display Number', '+90 301 9328094', 'sanitize_text_field' ),
		'alify_whatsapp_url' => array( 'WhatsApp Direct Link (wa.me)', 'https://wa.me/903019328094', 'esc_url_raw' ),
		'alify_hq'           => array( 'Studio Headquarters Label', 'Karachi, PK — Global Delivery', 'sanitize_text_field' ),
		'alify_cta_text'     => array( 'Header Primary CTA Label', 'Start a Project', 'sanitize_text_field' ),
		'alify_linkedin'     => array( 'LinkedIn URL', 'https://linkedin.com', 'esc_url_raw' ),
		'alify_instagram'    => array( 'Instagram URL', 'https://instagram.com', 'esc_url_raw' ),
		'alify_twitter'      => array( 'X / Twitter URL', 'https://x.com', 'esc_url_raw' ),
		'alify_behance'      => array( 'Behance URL', 'https://behance.net', 'esc_url_raw' ),
		'alify_dribbble'     => array( 'Dribbble URL', 'https://dribbble.com', 'esc_url_raw' ),
	);

	foreach ( $fields as $key => $cfg ) {
		list( $label, $default, $sanitizer ) = $cfg;

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
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'alify_customize_register' );
