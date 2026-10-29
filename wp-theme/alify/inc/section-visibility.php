<?php
/**
 * Per-section responsive visibility controls for pages, archive templates and singles.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_section_visibility_template_map() {
	return array(
		'alify_service'     => 'service-single',
		'alify_project'     => 'work-single',
		'alify_industry'    => 'industry-single',
		'alify_case_study'  => 'case-study-single',
		'post'              => 'blog-single',
	);
}

function alify_section_visibility_page_slug( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	if ( 'page' === $post->post_type ) {
		if ( (int) get_option( 'page_for_posts' ) === (int) $post->ID ) {
			return '';
		}
		$template = get_page_template_slug( $post->ID );
		foreach ( alify_copy_editor_catalog() as $slug => $page_data ) {
			if ( ! empty( $page_data['template'] ) && $page_data['template'] === $template ) {
				return $slug;
			}
		}
		return '';
	}
	$map = alify_section_visibility_template_map();
	return isset( $map[ $post->post_type ] ) ? $map[ $post->post_type ] : '';
}

function alify_section_visibility_open_tag_key( $open_tag ) {
	if ( preg_match( '/\bid\s*=\s*(["\'])(.*?)\1/i', $open_tag, $match ) && '' !== $match[2] ) {
		return 'id-' . sanitize_title( $match[2] );
	}
	if ( preg_match( '/\bclass\s*=\s*(["\'])(.*?)\1/i', $open_tag, $match ) ) {
		$classes = preg_split( '/\s+/', trim( $match[2] ) );
		foreach ( $classes as $class ) {
			if ( '' !== $class && false === strpos( $class, 'alify-' ) ) {
				return sanitize_title( $class );
			}
		}
	}
	return '';
}

function alify_get_section_visibility_definitions( $page_slug ) {
	static $cache = array();
	if ( isset( $cache[ $page_slug ] ) ) {
		return $cache[ $page_slug ];
	}
	$path = ALIFY_THEME_DIR . '/template-parts/pages/' . sanitize_file_name( $page_slug ) . '.php';
	if ( ! is_file( $path ) ) {
		$cache[ $page_slug ] = array();
		return array();
	}
	$source = file_get_contents( $path );
	preg_match_all( '/<section\b[^>]*>/i', $source, $matches );
	$definitions = array();
	$seen = array();
	foreach ( $matches[0] as $open_tag ) {
		$key = alify_section_visibility_open_tag_key( $open_tag );
		if ( '' === $key ) {
			continue;
		}
		$seen[ $key ] = isset( $seen[ $key ] ) ? $seen[ $key ] + 1 : 1;
		if ( $seen[ $key ] > 1 ) {
			$key .= '-' . $seen[ $key ];
		}
		$label = ucwords( str_replace( '-', ' ', preg_replace( '/-section$/', '', $key ) ) );
		if ( preg_match( '/\baria-label\s*=\s*(["\'])(.*?)\1/i', $open_tag, $aria ) ) {
			$label = html_entity_decode( $aria[2], ENT_QUOTES, 'UTF-8' );
		}
		$definitions[ $key ] = $label;
	}
	$cache[ $page_slug ] = $definitions;
	return $definitions;
}

function alify_register_section_visibility_meta_boxes() {
	$post_types = array( 'page', 'post', 'alify_service', 'alify_project', 'alify_industry', 'alify_case_study' );
	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'alify_section_visibility',
			__( 'ALIFY Section Visibility — Desktop / Tablet / Mobile', 'alify' ),
			'alify_render_section_visibility_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'alify_register_section_visibility_meta_boxes' );

function alify_render_section_visibility_meta_box( $post ) {
	$page_slug = alify_section_visibility_page_slug( $post );
	$sections = alify_get_section_visibility_definitions( $page_slug );
	wp_nonce_field( 'alify_save_section_visibility_' . $post->ID, 'alify_section_visibility_nonce' );
	if ( $page_slug && function_exists( 'alify_copy_editor_resolve_target' ) ) {
		$target = 'p:' . (int) $post->ID;
		$resolved = alify_copy_editor_resolve_target( $target );
		if ( $resolved ) {
			$archive_slugs = array( 'blog', 'services', 'work', 'industries', 'case-studies', 'testimonials', 'faq' );
			$editor_page = 'page' === $post->post_type && in_array( $page_slug, $archive_slugs, true ) ? 'alify-archives' : 'alify-page-copy-editor';
			$edit_url = add_query_arg( array( 'page' => $editor_page, 'target' => rawurlencode( $target ) ), admin_url( 'admin.php' ) );
			echo '<p><a class="button" href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit all prefilled page and template copy', 'alify' ) . '</a></p>';
		}
	}
	echo '<p>' . esc_html__( 'Choose Show or Hide independently at desktop (1025px+), tablet (768–1024px), and mobile (up to 767px). Defaults are Show.', 'alify' ) . '</p>';
	if ( ! $sections ) {
		echo '<p class="description">' . esc_html__( 'No static sections were detected for this template.', 'alify' ) . '</p>';
		return;
	}
	$settings = get_post_meta( $post->ID, '_alify_section_visibility', true );
	if ( ! is_array( $settings ) ) { $settings = array(); }
	echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Section', 'alify' ) . '</th><th>' . esc_html__( 'Desktop', 'alify' ) . '</th><th>' . esc_html__( 'Tablet', 'alify' ) . '</th><th>' . esc_html__( 'Mobile', 'alify' ) . '</th></tr></thead><tbody>';
	foreach ( $sections as $key => $label ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th>';
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$value = isset( $settings[ $key ][ $device ] ) ? (string) $settings[ $key ][ $device ] : '1';
			$name = 'alify_section_visibility[' . $key . '][' . $device . ']';
			echo '<td><select name="' . esc_attr( $name ) . '"><option value="1" ' . selected( $value, '1', false ) . '>' . esc_html__( 'Show', 'alify' ) . '</option><option value="0" ' . selected( $value, '0', false ) . '>' . esc_html__( 'Hide', 'alify' ) . '</option></select></td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

function alify_save_section_visibility_meta( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || ! isset( $_POST['alify_section_visibility_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['alify_section_visibility_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'alify_save_section_visibility_' . $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$page_slug = alify_section_visibility_page_slug( $post );
	$allowed = alify_get_section_visibility_definitions( $page_slug );
	$posted = isset( $_POST['alify_section_visibility'] ) && is_array( $_POST['alify_section_visibility'] ) ? wp_unslash( $_POST['alify_section_visibility'] ) : array();
	$clean = array();
	foreach ( $allowed as $key => $label ) {
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$value = isset( $posted[ $key ][ $device ] ) ? (string) $posted[ $key ][ $device ] : '1';
			$clean[ $key ][ $device ] = '0' === $value ? '0' : '1';
		}
	}
	update_post_meta( $post_id, '_alify_section_visibility', $clean );
}
add_action( 'save_post', 'alify_save_section_visibility_meta', 20, 2 );

function alify_apply_section_visibility( $html, $post_id, $page_slug = '' ) {
	if ( ! is_string( $html ) ) { return $html; }
	$settings = array();
	if ( $post_id > 0 && is_singular() ) {
		$settings = get_post_meta( $post_id, '_alify_section_visibility', true );
	} else {
		$option_map = array(
			'404' => 'alify_conversion', 'blog' => 'alify_archives', 'services' => 'alify_archive_services',
			'work' => 'alify_archive_work', 'industries' => 'alify_archive_industries',
			'case-studies' => 'alify_archive_case_studies', 'testimonials' => 'alify_archive_testimonials',
			'faq' => 'alify_archive_faq',
		);
		if ( isset( $option_map[ $page_slug ] ) ) {
			$settings = get_option( 'alify_section_visibility_archive_' . $page_slug, array() );
		}
	}
	if ( ! is_array( $settings ) || ! $settings ) { return $html; }
	$seen = array();
	return preg_replace_callback(
		'/<section\b[^>]*>/i',
		function ( $match ) use ( $settings, &$seen ) {
			$key = alify_section_visibility_open_tag_key( $match[0] );
			if ( '' === $key ) { return $match[0]; }
			$seen[ $key ] = isset( $seen[ $key ] ) ? $seen[ $key ] + 1 : 1;
			if ( $seen[ $key ] > 1 ) { $key .= '-' . $seen[ $key ]; }
			if ( ! isset( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) { return $match[0]; }
			$attrs = '';
			foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
				$value = isset( $settings[ $key ][ $device ] ) && '0' === (string) $settings[ $key ][ $device ] ? '0' : '1';
				$attrs .= ' data-alify-show-' . $device . '="' . $value . '"';
			}
			return rtrim( substr( $match[0], 0, -1 ) ) . $attrs . '>';
		},
		$html
	);
}

function alify_section_visibility_css() {
	$css = '@media (min-width:1025px){[data-alify-show-desktop="0"]{display:none!important}}@media (min-width:768px) and (max-width:1024px){[data-alify-show-tablet="0"]{display:none!important}}@media (max-width:767px){[data-alify-show-mobile="0"]{display:none!important}}';
	wp_register_style( 'alify-section-visibility', false, array(), ALIFY_THEME_VERSION );
	wp_enqueue_style( 'alify-section-visibility' );
	wp_add_inline_style( 'alify-section-visibility', $css );
}
add_action( 'wp_enqueue_scripts', 'alify_section_visibility_css', 30 );
