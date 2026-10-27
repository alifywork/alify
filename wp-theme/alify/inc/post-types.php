<?php
/**
 * Custom Post Types (Services, Projects, Case Studies, Industries, Testimonials, FAQs & Leads Inbox)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_register_post_types() {
	$cpts = array(
		'alify_service'     => array( 'Services', 'Service', 'services-item', 'dashicons-admin-customizer', true ),
		'alify_project'     => array( 'Projects', 'Project', 'project', 'dashicons-portfolio', true ),
		'alify_case_study'  => array( 'Case Studies', 'Case Study', 'case-study', 'dashicons-chart-area', true ),
		'alify_industry'    => array( 'Industries', 'Industry', 'industry', 'dashicons-networking', true ),
		'alify_testimonial' => array( 'Testimonials', 'Testimonial', 'testimonial-item', 'dashicons-format-quote', false ),
		'alify_faq'         => array( 'FAQs', 'FAQ', 'faq-item', 'dashicons-editor-help', false ),
	);

	foreach ( $cpts as $key => $cfg ) {
		list( $plural, $singular, $rewrite_slug, $icon, $public ) = $cfg;

		register_post_type(
			$key,
			array(
				'labels'       => array(
					'name'          => $plural,
					'singular_name' => $singular,
					'add_new_item'  => 'Add New ' . $singular,
					'edit_item'     => 'Edit ' . $singular,
				),
				'public'       => $public,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => $icon,
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => $rewrite_slug ),
			)
		);
	}

	// Private Admin CRM Inbox for Inquiries & Booked Strategy Calls
	register_post_type(
		'alify_lead',
		array(
			'labels'          => array(
				'name'          => __( 'ALIFY Leads & Calls', 'alify' ),
				'singular_name' => __( 'Lead', 'alify' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title', 'editor', 'custom-fields' ),
			'capability_type' => 'post',
		)
	);

	register_taxonomy(
		'faq_pillar',
		array( 'alify_faq' ),
		array(
			'label'        => __( 'FAQ Pillars', 'alify' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'alify_register_post_types' );
