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

	$flat_taxonomies = array(
		'service_category' => array( 'Services', 'Service Category', 'alify_service' ),
		'project_category' => array( 'Project Categories', 'Project Category', 'alify_project' ),
	);
	foreach ( $flat_taxonomies as $taxonomy => $labels ) {
		register_taxonomy(
			$taxonomy,
			array( $labels[2] ),
			array(
				'labels'       => array( 'name' => __( $labels[0], 'alify' ), 'singular_name' => __( $labels[1], 'alify' ) ),
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'hierarchical' => false,
				'rewrite'      => false,
				'query_var'    => true,
			)
		);
	}

	register_taxonomy(
		'faq_pillar',
		array( 'alify_faq' ),
		array(
			'label'        => __( 'FAQ Pillars', 'alify' ),
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
			'query_var'    => true,
		)
	);
}
add_action( 'init', 'alify_register_post_types' );

/**
 * Use clean, base-less category URLs while keeping real WordPress pages first priority.
 */
function alify_filter_bare_term_link( $url, $term, $taxonomy ) {
	$bare_taxonomies = array( 'service_category', 'project_category', 'faq_pillar' );
	if ( in_array( $taxonomy, $bare_taxonomies, true ) && ! is_wp_error( $term ) ) {
		return home_url( '/' . user_trailingslashit( $term->slug ) );
	}
	return $url;
}
add_filter( 'term_link', 'alify_filter_bare_term_link', 10, 3 );

/**
 * Resolve a root-level slug as a taxonomy term only when no WordPress page owns it.
 */
function alify_resolve_bare_term_request( $query_vars ) {
	if ( empty( $query_vars['pagename'] ) ) {
		return $query_vars;
	}
	$slug = sanitize_title( $query_vars['pagename'] );
	if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
		return $query_vars;
	}
	foreach ( array( 'service_category', 'project_category', 'faq_pillar' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			unset( $query_vars['pagename'] );
			$query_vars['taxonomy'] = $taxonomy;
			$query_vars['term'] = $term->slug;
			return $query_vars;
		}
	}
	return $query_vars;
}
add_filter( 'request', 'alify_resolve_bare_term_request' );

function alify_validate_bare_taxonomy_term_slug( $term, $taxonomy, $args ) {
	$bare_taxonomies = array( 'service_category', 'project_category', 'faq_pillar' );
	if ( ! in_array( $taxonomy, $bare_taxonomies, true ) ) {
		return $term;
	}
	$slug = ! empty( $args['slug'] ) ? sanitize_title( $args['slug'] ) : sanitize_title( $term );
	if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
		return new WP_Error( 'alify_term_slug_page_collision', __( 'This slug is already used by a page. Bare category URLs need a unique root-level slug.', 'alify' ) );
	}
	foreach ( $bare_taxonomies as $other_taxonomy ) {
		if ( ! taxonomy_exists( $other_taxonomy ) ) {
			continue;
		}
		if ( get_term_by( 'slug', $slug, $other_taxonomy ) ) {
			return new WP_Error( 'alify_term_slug_collision', __( 'This slug is already used by an ALIFY category. Bare category URLs must be unique across these categories.', 'alify' ) );
		}
	}
	return $term;
}
add_filter( 'pre_insert_term', 'alify_validate_bare_taxonomy_term_slug', 10, 3 );

/**
 * Reserve /blog/{post-slug}/ for native blog posts.
 */
function alify_register_clean_blog_permalink_rules() {
	add_rewrite_rule( '^blog/([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
}
add_action( 'init', 'alify_register_clean_blog_permalink_rules', 20 );

function alify_clean_blog_post_link( $permalink, $post ) {
	if ( 'post' !== $post->post_type ) {
		return $permalink;
	}
	return home_url( '/blog/' . user_trailingslashit( $post->post_name ) );
}
add_filter( 'post_link', 'alify_clean_blog_post_link', 10, 2 );

/**
 * Flush once after installing the clean permalink rules/taxonomies.
 */
function alify_flush_clean_permalink_rules_once() {
	if ( '2' === (string) get_option( 'alify_permalink_rules_version', '' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'alify_permalink_rules_version', '2' );
}
add_action( 'init', 'alify_flush_clean_permalink_rules_once', 99 );
