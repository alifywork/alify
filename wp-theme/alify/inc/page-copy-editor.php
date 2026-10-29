<?php
/**
 * Paginated dashboard editor for prefilled page, archive and template copy.
 *
 * Uses the same post meta and options arrays as Alify Custom Field while avoiding
 * oversized field-group schemas and admin forms.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_copy_editor_archive_options() {
	return array(
		'404'          => 'alify_conversion',
		'blog'         => 'alify_archives',
		'services'     => 'alify_archive_services',
		'work'         => 'alify_archive_work',
		'industries'   => 'alify_archive_industries',
		'case-studies' => 'alify_archive_case_studies',
		'testimonials' => 'alify_archive_testimonials',
		'faq'          => 'alify_archive_faq',
	);
}

function alify_copy_editor_cpt_templates() {
	return array(
		'alify_service'     => 'service-single',
		'alify_project'     => 'work-single',
		'alify_industry'    => 'industry-single',
		'alify_case_study'  => 'case-study-single',
		'alify_testimonial' => 'testimonial-single',
		'alify_faq'         => 'faq-single',
	);
}

function alify_copy_editor_catalog() {
	static $catalog = null;
	if ( null === $catalog ) {
		$catalog = require ALIFY_THEME_DIR . '/inc/page-content-catalog.php';
	}
	return $catalog;
}

function alify_copy_editor_targets() {
	$catalog = alify_copy_editor_catalog();
	$targets = array();
	$archive_options = alify_copy_editor_archive_options();
	$cpt_templates = array_flip( alify_copy_editor_cpt_templates() );

	foreach ( $catalog as $slug => $page_data ) {
		if ( isset( $archive_options[ $slug ] ) ) {
			$label = '404' === $slug ? __( '404 Page', 'alify' ) : sprintf( __( '%s Archive / Hub', 'alify' ), $page_data['title'] );
			$option_page = $archive_options[ $slug ];
			$targets[ 'o:' . $option_page . ':' . $slug ] = $label;
		}

		if ( ! empty( $page_data['template'] ) ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'meta_key'       => '_wp_page_template',
					'meta_value'     => $page_data['template'],
				)
			);
			foreach ( $pages as $page ) {
				if ( 'blog' === $slug && (int) get_option( 'page_for_posts' ) === (int) $page->ID ) {
					continue; // The Posts Index is rendered by home.php and saves copy to archive options.
				}
				$targets[ 'p:' . $page->ID ] = sprintf( __( '%1$s — Page: %2$s', 'alify' ), $page_data['title'], get_the_title( $page ) );
			}
		}

		if ( isset( $cpt_templates[ $slug ] ) ) {
			$post_type = $cpt_templates[ $slug ];
			$items = get_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			foreach ( $items as $item ) {
				$targets[ 'p:' . $item->ID ] = sprintf( __( '%1$s — %2$s', 'alify' ), $page_data['title'], get_the_title( $item ) );
			}
		}
	}
	return $targets;
}

function alify_copy_editor_resolve_target( $target ) {
	$catalog = alify_copy_editor_catalog();
	$target = sanitize_text_field( $target );
	if ( 0 === strpos( $target, 'o:' ) ) {
		$parts = explode( ':', $target );
		if ( 3 !== count( $parts ) ) {
			return false;
		}
		$option_page = sanitize_key( $parts[1] );
		$slug = sanitize_key( $parts[2] );
		$archive_options = alify_copy_editor_archive_options();
		if ( ! isset( $archive_options[ $slug ] ) || $archive_options[ $slug ] !== $option_page || ! isset( $catalog[ $slug ] ) ) {
			return false;
		}
		return array( 'kind' => 'option', 'slug' => $slug, 'option_page' => $option_page );
	}

	if ( 0 !== strpos( $target, 'p:' ) ) {
		return false;
	}
	$post_id_text = substr( $target, 2 );
	if ( '' === $post_id_text || ! ctype_digit( $post_id_text ) ) {
		return false;
	}
	$post_id = absint( $post_id_text );
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}
	$slug = '';
	if ( 'page' === $post->post_type ) {
		$template = get_page_template_slug( $post_id );
		foreach ( $catalog as $candidate_slug => $page_data ) {
			if ( ! empty( $page_data['template'] ) && $page_data['template'] === $template ) {
				$slug = $candidate_slug;
				break;
			}
		}
	} else {
		$cpt_templates = alify_copy_editor_cpt_templates();
		if ( isset( $cpt_templates[ $post->post_type ] ) ) {
			$slug = $cpt_templates[ $post->post_type ];
		}
	}
	if ( '' === $slug || ! isset( $catalog[ $slug ] ) || ( 'blog' === $slug && (int) get_option( 'page_for_posts' ) === $post_id ) ) {
		return false;
	}
	return array( 'kind' => 'post', 'slug' => $slug, 'post_id' => $post_id );
}

function alify_register_page_copy_editor_menu() {
	add_submenu_page(
		'alify-theme-settings-hub',
		__( 'Archives', 'alify' ),
		__( 'Archives', 'alify' ),
		'manage_options',
		'alify-archives',
		'alify_render_archive_copy_editor'
	);
	add_submenu_page(
		'alify-theme-settings-hub',
		__( 'All Page Content', 'alify' ),
		__( 'All Page Content', 'alify' ),
		'manage_options',
		'alify-page-copy-editor',
		'alify_render_page_copy_editor'
	);
}
add_action( 'admin_menu', 'alify_register_page_copy_editor_menu', 20 );

function alify_render_archive_copy_editor() {
	alify_render_page_copy_editor( 'archives' );
}

function alify_save_page_copy_editor() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to edit page copy.', 'alify' ) );
	}
	$target = isset( $_POST['target'] ) ? sanitize_text_field( wp_unslash( $_POST['target'] ) ) : '';
	$resolved = alify_copy_editor_resolve_target( $target );
	if ( ! $resolved ) {
		wp_die( esc_html__( 'The selected content target is invalid.', 'alify' ) );
	}
	check_admin_referer( 'alify_page_copy_' . $target );
	$catalog = alify_copy_editor_catalog();
	$allowed = array();
	foreach ( $catalog[ $resolved['slug'] ]['fields'] as $field ) {
		$allowed[ $field['name'] ] = isset( $field['attribute'] ) && '' !== $field['attribute'] ? 'attribute' : 'text';
	}
	$values = isset( $_POST['copy_values'] ) && is_array( $_POST['copy_values'] ) ? wp_unslash( $_POST['copy_values'] ) : array();
	if ( count( $values ) > 40 ) {
		wp_die( esc_html__( 'Too many fields were submitted at once.', 'alify' ) );
	}
	$option_name = '';
	$data = array();
	if ( 'option' === $resolved['kind'] ) {
		$option_name = 'alify_cf_options_' . $resolved['option_page'];
		$data = get_option( $option_name, array() );
		if ( ! is_array( $data ) ) { $data = array(); }
	}
	foreach ( $values as $name => $value ) {
		$name = sanitize_key( $name );
		if ( ! isset( $allowed[ $name ] ) || ! is_scalar( $value ) ) {
			continue;
		}
		$clean_value = sanitize_textarea_field( (string) $value );
		if ( 'post' === $resolved['kind'] ) {
			update_post_meta( $resolved['post_id'], '_alify_cf_' . $name, $clean_value );
		} else {
			$data[ $name ] = $clean_value;
		}
	}
	$archive_visibility = null;
	if ( $option_name && isset( $_POST['section_visibility'] ) && is_array( $_POST['section_visibility'] ) && function_exists( 'alify_get_section_visibility_definitions' ) ) {
		$sections = alify_get_section_visibility_definitions( $resolved['slug'] );
		$posted_sections = wp_unslash( $_POST['section_visibility'] );
		$visibility = array();
		foreach ( $sections as $section_key => $section_label ) {
			foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
				$choice = isset( $posted_sections[ $section_key ][ $device ] ) ? (string) $posted_sections[ $section_key ][ $device ] : '1';
				$visibility[ $section_key ][ $device ] = '0' === $choice ? '0' : '1';
			}
		}
		$archive_visibility = $visibility;
	}
	if ( $option_name ) {
		update_option( $option_name, $data );
	}
	if ( is_array( $archive_visibility ) ) {
		update_option( 'alify_section_visibility_archive_' . $resolved['slug'], $archive_visibility );
	}
	$paged = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;
	$scope = isset( $_POST['editor_scope'] ) && 'archives' === sanitize_key( wp_unslash( $_POST['editor_scope'] ) ) ? 'archives' : 'all';
	$url = add_query_arg(
		array( 'page' => 'archives' === $scope ? 'alify-archives' : 'alify-page-copy-editor', 'target' => rawurlencode( $target ), 'paged' => $paged, 'saved' => '1' ),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $url );
	exit;
}
add_action( 'admin_post_alify_page_copy_save', 'alify_save_page_copy_editor' );

function alify_render_page_copy_editor( $scope = 'all' ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to edit page copy.', 'alify' ) );
	}
	$catalog = alify_copy_editor_catalog();
	$targets = alify_copy_editor_targets();
	$archive_slugs = array( 'blog', 'services', 'work', 'industries', 'case-studies', 'testimonials', 'faq' );
	$single_template_slugs = array( 'service-single', 'work-single', 'industry-single', 'case-study-single', 'blog-single' );
	$targets = array_filter(
		$targets,
		function ( $label, $key ) use ( $scope, $archive_slugs, $single_template_slugs ) {
			if ( 0 === strpos( $key, 'o:' ) ) {
				$parts = explode( ':', $key );
				$is_archive = isset( $parts[2] ) && in_array( $parts[2], $archive_slugs, true );
				return 'archives' === $scope ? $is_archive : ( isset( $parts[2] ) && '404' === $parts[2] );
			}
			if ( 0 === strpos( $key, 'p:' ) ) {
				$resolved = alify_copy_editor_resolve_target( $key );
				$slug = $resolved && isset( $resolved['slug'] ) ? $resolved['slug'] : '';
				$is_archive = in_array( $slug, $archive_slugs, true );
				$is_single_page = in_array( $slug, $single_template_slugs, true );
				$post_id = absint( substr( $key, 2 ) );
				$template_page = $post_id && 'page' === get_post_type( $post_id );
				return 'archives' === $scope ? ( $template_page && $is_archive ) : ( ! $template_page || ( ! $is_archive && ! $is_single_page ) );
			}
			return false;
		},
		ARRAY_FILTER_USE_BOTH
	);
	$screen_title = 'archives' === $scope ? __( 'Archive Content', 'alify' ) : __( 'All Page Content', 'alify' );
	$target = isset( $_GET['target'] ) ? sanitize_text_field( wp_unslash( $_GET['target'] ) ) : '';
	if ( ! isset( $targets[ $target ] ) ) {
		$target = (string) key( $targets );
	}
	$resolved = $target ? alify_copy_editor_resolve_target( $target ) : false;
	if ( ! $resolved ) {
		echo '<div class="wrap"><h1>' . esc_html( $screen_title ) . '</h1><p>' . esc_html__( 'No matching page templates or archive targets were found.', 'alify' ) . '</p></div>';
		return;
	}
	$fields = $catalog[ $resolved['slug'] ]['fields'];
	$per_page = 40;
	$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$total_pages = max( 1, (int) ceil( count( $fields ) / $per_page ) );
	$paged = min( $paged, $total_pages );
	$visible_fields = array_slice( $fields, ( $paged - 1 ) * $per_page, $per_page );
	if ( 'post' === $resolved['kind'] ) {
		$stored = get_post_meta( $resolved['post_id'] );
	} else {
		$stored = get_option( 'alify_cf_options_' . $resolved['option_page'], array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
	}
	$saved = isset( $_GET['saved'] ) && '1' === $_GET['saved']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<div class="wrap alify-page-copy-editor"><h1>' . esc_html( $screen_title ) . '</h1>';
	echo '<p>' . esc_html__( 'Edit the prefilled headings, paragraphs, badges, buttons, labels, stats, placeholders and accessibility text. The layout and styling remain unchanged.', 'alify' ) . '</p>';
	if ( 'archives' === $scope && function_exists( 'alify_has_cf_plugin' ) && alify_has_cf_plugin() ) {
		echo '<p><a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=alify_field_group&page=alify-options-alify_archives' ) ) . '">' . esc_html__( 'Open archive hero & layout settings', 'alify' ) . '</a></p>';
	}
	if ( $saved ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Copy and visibility settings saved.', 'alify' ) . '</p></div>';
	}
	$admin_page = 'archives' === $scope ? 'alify-archives' : 'alify-page-copy-editor';
	$target_label = 'archives' === $scope ? __( 'Choose archive:', 'alify' ) : __( 'Choose page or template:', 'alify' );
	echo '<form method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '"><input type="hidden" name="page" value="' . esc_attr( $admin_page ) . '"><label for="alify-copy-target"><strong>' . esc_html( $target_label ) . '</strong> </label><select id="alify-copy-target" name="target">';
	foreach ( $targets as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( $target, $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select> <button class="button">' . esc_html__( 'Load content', 'alify' ) . '</button></form>';
	echo '<h2>' . esc_html( $targets[ $target ] ) . '</h2><p>' . esc_html( sprintf( __( '%1$d editable copy fields · page %2$d of %3$d', 'alify' ), count( $fields ), $paged, $total_pages ) ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="alify_page_copy_save"><input type="hidden" name="editor_scope" value="' . esc_attr( $scope ) . '"><input type="hidden" name="target" value="' . esc_attr( $target ) . '"><input type="hidden" name="paged" value="' . esc_attr( $paged ) . '">';
	wp_nonce_field( 'alify_page_copy_' . $target );
	if ( 'option' === $resolved['kind'] && function_exists( 'alify_get_section_visibility_definitions' ) ) {
		$section_definitions = alify_get_section_visibility_definitions( $resolved['slug'] );
		if ( $section_definitions ) {
			echo '<h3>' . esc_html__( 'Section visibility by device', 'alify' ) . '</h3><table class="widefat striped" style="max-width:1050px;margin-bottom:18px"><thead><tr><th>' . esc_html__( 'Section', 'alify' ) . '</th><th>' . esc_html__( 'Desktop', 'alify' ) . '</th><th>' . esc_html__( 'Tablet', 'alify' ) . '</th><th>' . esc_html__( 'Mobile', 'alify' ) . '</th></tr></thead><tbody>';
			$stored_visibility = get_option( 'alify_section_visibility_archive_' . $resolved['slug'], array() );
			if ( ! is_array( $stored_visibility ) ) { $stored_visibility = array(); }
			foreach ( $section_definitions as $section_key => $section_label ) {
				echo '<tr><th scope="row">' . esc_html( $section_label ) . '</th>';
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$setting = isset( $stored_visibility[ $section_key ][ $device ] ) ? (string) $stored_visibility[ $section_key ][ $device ] : '1';
					$name = 'section_visibility[' . $section_key . '][' . $device . ']';
					echo '<td><select name="' . esc_attr( $name ) . '"><option value="1" ' . selected( $setting, '1', false ) . '>' . esc_html__( 'Show', 'alify' ) . '</option><option value="0" ' . selected( $setting, '0', false ) . '>' . esc_html__( 'Hide', 'alify' ) . '</option></select></td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
	}
	echo '<div class="alify-copy-fields" style="max-width:1050px">';
	foreach ( $visible_fields as $field ) {
		$name = $field['name'];
		$meta_key = '_alify_cf_' . $name;
		$legacy_key = ! empty( $field['legacy_name'] ) ? '_alify_cf_' . $field['legacy_name'] : '';
		$value = isset( $stored[ $meta_key ][0] ) ? $stored[ $meta_key ][0] : ( $legacy_key && isset( $stored[ $legacy_key ][0] ) ? $stored[ $legacy_key ][0] : ( isset( $stored[ $name ] ) ? $stored[ $name ] : ( isset( $field['legacy_name'] ) && isset( $stored[ $field['legacy_name'] ] ) ? $stored[ $field['legacy_name'] ] : $field['default'] ) ) );
		$label = isset( $field['attribute'] ) && '' !== $field['attribute'] ? strtoupper( $field['attribute'] ) : strtoupper( $field['tag'] );
		$id = 'alify-copy-' . $name;
		echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:4px;margin:12px 0;padding:14px 16px"><label for="' . esc_attr( $id ) . '" style="display:block;font-weight:600;margin-bottom:6px">' . esc_html( $label . ' · ' . $field['label'] ) . '</label><textarea id="' . esc_attr( $id ) . '" name="copy_values[' . esc_attr( $name ) . ']" rows="2" class="large-text">' . esc_textarea( $value ) . '</textarea><p class="description">' . esc_html__( 'Prefilled with current design copy. Leave blank to show no text.', 'alify' ) . '</p></div>';
	}
	echo '</div><p><button type="submit" class="button button-primary">' . esc_html__( 'Save this page of copy', 'alify' ) . '</button></p></form>';
	if ( $total_pages > 1 ) {
		$links = paginate_links(
			array(
				'base'      => add_query_arg( array( 'page' => $admin_page, 'target' => rawurlencode( $target ), 'paged' => '%#%' ), admin_url( 'admin.php' ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => $total_pages,
				'prev_text' => __( 'Previous', 'alify' ),
				'next_text' => __( 'Next', 'alify' ),
			)
		);
		echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $links ) . '</div></div>';
	}
	echo '</div>';
}
