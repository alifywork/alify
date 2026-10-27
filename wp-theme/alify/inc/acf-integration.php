<?php
/**
 * ACF Free Complete Integration:
 *   1. ACF Free "ALIFY Theme Settings" Options Engine (4 Tabbed Options Screens — No ACF Pro Required!)
 *   2. Custom ACF Location Rule (`alify_options_screen`) + `acf-json/` Local JSON Sync
 *   3. 12 ACF Free Field Groups (6 CPTs + Blog Posts + Pages + 4 Theme Settings Screens)
 *   4. Two-Way Sync (`acf/load_value` & `acf/save_post`) With Starter Data & CPT/Page Renderers
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Configure `acf-json` Save & Load Directory inside Theme.
 */
function alify_acf_json_save_point( $path ) {
	$dir = ALIFY_THEME_DIR . '/acf-json';
	if ( is_dir( $dir ) && is_writable( $dir ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		return $dir;
	}
	return $path;
}
add_filter( 'acf/settings/save_json', 'alify_acf_json_save_point' );

function alify_acf_json_load_point( $paths ) {
	$dir = ALIFY_THEME_DIR . '/acf-json';
	if ( is_dir( $dir ) ) {
		$paths[] = $dir;
	}
	return $paths;
}
add_filter( 'acf/settings/load_json', 'alify_acf_json_load_point' );

/**
 * 2. Define the 4 ALIFY Theme Settings / Options Screens (Works 100% in ACF Free!).
 */
function alify_get_settings_screens_config() {
	return array(
		'global'   => array(
			'slug'       => 'alify-settings-global',
			'menu_slug'  => 'alify-theme-settings',
			'menu_title' => __( 'Global, Header & Footer', 'alify' ),
			'page_title' => __( 'Global Brand, Header, Footer & Reusable Sections', 'alify' ),
		),
		'home'     => array(
			'slug'       => 'alify-settings-home',
			'menu_slug'  => 'alify-theme-settings-home',
			'menu_title' => __( 'Home Page Content', 'alify' ),
			'page_title' => __( 'Home Page — Hero, Sections & Process Settings', 'alify' ),
		),
		'archives' => array(
			'slug'       => 'alify-settings-archives',
			'menu_slug'  => 'alify-theme-settings-archives',
			'menu_title' => __( 'Archive & Hub Pages', 'alify' ),
			'page_title' => __( 'Archive & Inner Hub Pages Content (Services, Work, Blog, Pricing, About, etc.)', 'alify' ),
		),
		'singles'  => array(
			'slug'       => 'alify-settings-singles',
			'menu_slug'  => 'alify-theme-settings-singles',
			'menu_title' => __( 'Single Page Templates', 'alify' ),
			'page_title' => __( 'Single Templates Global Settings (Service, Work, Industry, Case Study & Blog Single)', 'alify' ),
		),
	);
}

/**
 * Register Private Singleton CPT `alify_settings_page` & Auto-Ensure the 4 Settings Posts Exist.
 */
function alify_register_settings_cpt_and_seed() {
	register_post_type(
		'alify_settings_page',
		array(
			'labels'              => array(
				'name'          => __( 'ALIFY Theme Settings', 'alify' ),
				'singular_name' => __( 'Theme Settings Screen', 'alify' ),
				'edit_item'     => __( 'ALIFY Theme Settings', 'alify' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'supports'            => array( 'title' ),
			'capability_type'     => 'page',
		)
	);
}
add_action( 'init', 'alify_register_settings_cpt_and_seed', 5 );

/**
 * Get (or Create) the Singleton Post ID for a Theme Settings Screen (`global`, `home`, `archives`, `singles`).
 */
function alify_get_settings_post_id( $screen_key ) {
	static $mem_cache = array();
	if ( isset( $mem_cache[ $screen_key ] ) ) {
		return $mem_cache[ $screen_key ];
	}

	$screens = alify_get_settings_screens_config();
	if ( ! isset( $screens[ $screen_key ] ) ) {
		return 0;
	}

	$cfg        = $screens[ $screen_key ];
	$stored_ids = get_option( 'alify_acf_settings_post_ids', array() );
	if ( ! is_array( $stored_ids ) ) {
		$stored_ids = array();
	}

	if ( ! empty( $stored_ids[ $screen_key ] ) ) {
		$p = get_post( (int) $stored_ids[ $screen_key ] );
		if ( $p && 'alify_settings_page' === $p->post_type && 'trash' !== $p->post_status ) {
			$mem_cache[ $screen_key ] = (int) $p->ID;
			return (int) $p->ID;
		}
	}

	$existing = get_page_by_path( $cfg['slug'], OBJECT, 'alify_settings_page' );
	if ( $existing && 'trash' !== $existing->post_status ) {
		$stored_ids[ $screen_key ] = (int) $existing->ID;
		update_option( 'alify_acf_settings_post_ids', $stored_ids );
		$mem_cache[ $screen_key ] = (int) $existing->ID;
		return (int) $existing->ID;
	}

	$new_id = wp_insert_post(
		array(
			'post_type'   => 'alify_settings_page',
			'post_status' => 'publish',
			'post_name'   => $cfg['slug'],
			'post_title'  => $cfg['page_title'],
		)
	);
	if ( $new_id && ! is_wp_error( $new_id ) ) {
		update_post_meta( $new_id, '_alify_settings_screen_key', $screen_key );
		$stored_ids[ $screen_key ] = (int) $new_id;
		update_option( 'alify_acf_settings_post_ids', $stored_ids );
		$mem_cache[ $screen_key ] = (int) $new_id;
		return (int) $new_id;
	}

	return 0;
}

/**
 * Resolve Screen Key (`global`, `home`, `archives`, `singles`) From a Post ID.
 */
function alify_get_screen_key_for_post( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}
	$meta_key = get_post_meta( $post_id, '_alify_settings_screen_key', true );
	if ( ! empty( $meta_key ) ) {
		return $meta_key;
	}
	$p = get_post( $post_id );
	if ( ! $p || 'alify_settings_page' !== $p->post_type ) {
		return '';
	}
	foreach ( alify_get_settings_screens_config() as $k => $cfg ) {
		if ( $cfg['slug'] === $p->post_name ) {
			update_post_meta( $post_id, '_alify_settings_screen_key', $k );
			return $k;
		}
	}
	return '';
}

/**
 * 3. Register WP Admin Sidebar Menu: `ALIFY Theme Settings` + 4 Submenus.
 */
function alify_register_theme_settings_admin_menu() {
	$screens = alify_get_settings_screens_config();

	add_menu_page(
		__( 'ALIFY Theme Settings', 'alify' ),
		__( 'ALIFY Theme Settings', 'alify' ),
		'manage_options',
		'alify-theme-settings',
		'alify_render_theme_settings_router',
		'dashicons-admin-settings',
		2
	);

	foreach ( $screens as $key => $cfg ) {
		add_submenu_page(
			'alify-theme-settings',
			$cfg['page_title'],
			$cfg['menu_title'],
			'manage_options',
			$cfg['menu_slug'],
			'alify_render_theme_settings_router'
		);
	}
}
add_action( 'admin_menu', 'alify_register_theme_settings_admin_menu' );

/**
 * Redirect `admin.php?page=alify-theme-settings*` Directly to the Matching ACF Settings Post Screen.
 */
function alify_redirect_theme_settings_menu_clicks() {
	if ( ! is_admin() || ! isset( $_GET['page'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$req_page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$screens  = alify_get_settings_screens_config();

	foreach ( $screens as $key => $cfg ) {
		if ( $cfg['menu_slug'] === $req_page ) {
			$pid = alify_get_settings_post_id( $key );
			if ( $pid > 0 ) {
				wp_safe_redirect( admin_url( 'post.php?post=' . $pid . '&action=edit' ) );
				exit;
			}
		}
	}
}
add_action( 'admin_init', 'alify_redirect_theme_settings_menu_clicks', 5 );

function alify_render_theme_settings_router() {
	echo '<div class="wrap"><h1>' . esc_html__( 'Loading ALIFY Theme Settings...', 'alify' ) . '</h1></div>';
}

/**
 * Keep `ALIFY Theme Settings` Highlighted in WP Admin Sidebar & Polish the Settings Edit Screen.
 */
function alify_highlight_settings_parent_menu( $parent_file ) {
	global $pagenow, $post;
	if ( 'post.php' === $pagenow && $post && 'alify_settings_page' === $post->post_type ) {
		return 'alify-theme-settings';
	}
	return $parent_file;
}
add_filter( 'parent_file', 'alify_highlight_settings_parent_menu' );

function alify_highlight_settings_submenu( $submenu_file ) {
	global $pagenow, $post;
	if ( 'post.php' === $pagenow && $post && 'alify_settings_page' === $post->post_type ) {
		$key     = alify_get_screen_key_for_post( $post->ID );
		$screens = alify_get_settings_screens_config();
		if ( $key && isset( $screens[ $key ] ) ) {
			return $screens[ $key ]['menu_slug'];
		}
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'alify_highlight_settings_submenu' );

function alify_polish_settings_screen_header() {
	$screen = get_current_screen();
	if ( ! $screen || 'alify_settings_page' !== $screen->post_type ) {
		return;
	}
	global $post;
	$active_key = $post ? alify_get_screen_key_for_post( $post->ID ) : 'global';
	$screens    = alify_get_settings_screens_config();
	?>
	<style>
		body.post-type-alify_settings_page .page-title-action,
		body.post-type-alify_settings_page #titlediv,
		body.post-type-alify_settings_page #minor-publishing,
		body.post-type-alify_settings_page #delete-action,
		body.post-type-alify_settings_page #slugdiv,
		body.post-type-alify_settings_page .wrap > h1.wp-heading-inline {
			display: none !important;
		}
		body.post-type-alify_settings_page #submitdiv .postbox-header h2 {
			font-size: 14px;
		}
		body.post-type-alify_settings_page #major-publishing-actions {
			background: #f8fafc;
			border-top: none;
			padding: 14px;
		}
		body.post-type-alify_settings_page #publish {
			width: 100%;
			height: 40px;
			font-size: 14px;
			font-weight: 600;
			background: #e05a3a;
			border-color: #e05a3a;
		}
		body.post-type-alify_settings_page #publish:hover {
			background: #c74a2c;
			border-color: #c74a2c;
		}
		.alify-acf-settings-banner {
			background: #12151c;
			color: #fff;
			padding: 20px 24px;
			border-radius: 10px;
			margin: 14px 0 18px;
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: space-between;
			gap: 14px;
		}
		.alify-acf-settings-nav {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
		}
		.alify-acf-settings-nav a {
			display: inline-block;
			padding: 8px 14px;
			border-radius: 999px;
			text-decoration: none;
			font-weight: 600;
			font-size: 12.5px;
			color: rgba(255,255,255,0.82);
			background: rgba(255,255,255,0.08);
			border: 1px solid rgba(255,255,255,0.14);
		}
		.alify-acf-settings-nav a.is-active {
			background: #e05a3a;
			border-color: #e05a3a;
			color: #fff;
		}
	</style>
	<script>
		jQuery(function($){
			$('#publish').val('Save ALIFY Settings');
			$('#submitdiv .hndle, #submitdiv .postbox-header h2').text('Publish Theme Changes');
		});
	</script>
	<?php
	add_action(
		'edit_form_top',
		function () use ( $screens, $active_key ) {
			$cur_title = isset( $screens[ $active_key ] ) ? $screens[ $active_key ]['page_title'] : 'ALIFY Theme Settings';
			echo '<div class="alify-acf-settings-banner">';
			echo '<div><span style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.08em;color:#e05a3a;text-transform:uppercase;">△ ALIFY STUDIO · ACF FREE OPTIONS</span>';
			echo '<h2 style="margin:4px 0 0;color:#fff;font-size:19px;">' . esc_html( $cur_title ) . '</h2></div>';
			echo '<nav class="alify-acf-settings-nav">';
			foreach ( $screens as $k => $cfg ) {
				$pid = alify_get_settings_post_id( $k );
				$url = $pid ? admin_url( 'post.php?post=' . $pid . '&action=edit' ) : admin_url( 'admin.php?page=' . $cfg['menu_slug'] );
				$cls = ( $k === $active_key ) ? ' class="is-active"' : '';
				echo '<a href="' . esc_url( $url ) . '"' . $cls . '>' . esc_html( $cfg['menu_title'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</nav></div>';

			if ( ! function_exists( 'acf_add_local_field_group' ) ) {
				echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Please activate the free Advanced Custom Fields (ACF) plugin to display all visual settings tabs on this screen.', 'alify' ) . '</strong></p></div>';
			}
		}
	);
}
add_action( 'admin_head', 'alify_polish_settings_screen_header' );

/**
 * 4. Register Custom ACF Free Location Rule: `alify_options_screen` (`global`, `home`, `archives`, `singles`).
 */
function alify_acf_location_types( $choices ) {
	$choices['ALIFY Theme']['alify_options_screen'] = __( 'ALIFY Theme Settings Screen', 'alify' );
	return $choices;
}
add_filter( 'acf/location/rule_types', 'alify_acf_location_types' );

function alify_acf_location_values( $choices ) {
	foreach ( alify_get_settings_screens_config() as $k => $cfg ) {
		$choices[ $k ] = $cfg['menu_title'];
	}
	return $choices;
}
add_filter( 'acf/location/rule_values/alify_options_screen', 'alify_acf_location_values' );

function alify_acf_location_match( $match, $rule, $options ) {
	$post_id = isset( $options['post_id'] ) ? (int) $options['post_id'] : 0;
	if ( $post_id <= 0 ) {
		return false;
	}
	$actual_key = alify_get_screen_key_for_post( $post_id );
	if ( '==' === $rule['operator'] ) {
		return ( $actual_key === $rule['value'] );
	}
	if ( '!=' === $rule['operator'] ) {
		return ( $actual_key !== $rule['value'] );
	}
	return $match;
}
add_filter( 'acf/location/rule_match/alify_options_screen', 'alify_acf_location_match', 10, 3 );

/**
 * 5. Compact Field Builder Helpers (100% ACF Free Field Types Only).
 */
function alify_acf_tab( $key_suffix, $label ) {
	return array(
		'key'       => 'field_alify_tab_' . $key_suffix,
		'label'     => $label,
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
		'endpoint'  => 0,
	);
}

function alify_acf_f( $name, $label, $type = 'text', $args = array() ) {
	$base = array(
		'key'           => 'field_' . $name,
		'label'         => $label,
		'name'          => $name,
		'type'          => $type,
		'instructions'  => isset( $args['desc'] ) ? $args['desc'] : '',
		'default_value' => isset( $args['std'] ) ? $args['std'] : '',
		'placeholder'   => isset( $args['ph'] ) ? $args['ph'] : '',
		'wrapper'       => array(
			'width' => isset( $args['w'] ) ? (string) $args['w'] : '',
		),
	);

	if ( 'textarea' === $type ) {
		$base['rows'] = isset( $args['rows'] ) ? (int) $args['rows'] : 3;
	} elseif ( 'image' === $type ) {
		$base['return_format'] = 'url';
		$base['preview_size']  = 'medium';
		$base['library']       = 'all';
	} elseif ( 'select' === $type ) {
		$base['choices'] = isset( $args['choices'] ) ? $args['choices'] : array();
		$base['ui']      = 0;
	} elseif ( 'true_false' === $type ) {
		$base['ui']            = 1;
		$base['default_value'] = isset( $args['std'] ) ? (int) $args['std'] : 0;
	}

	return $base;
}

/**
 * 6. Build All 12 ACF Free Field Groups (8 Post/CPT Groups + 4 Theme Settings Screens).
 */
function alify_get_acf_field_groups_definitions() {
	$groups = array();

	// -------------------------------------------------------------------------
	// GROUP 1: Services CPT (`alify_service`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_cpt_service',
		'title'    => 'ALIFY Service — Card & Single Page Content',
		'fields'   => array(
			alify_acf_tab( 'svc_card', 'Service Card & Archive' ),
			alify_acf_f( 'alify_svc_notch', 'Notch Code Badge', 'text', array( 'ph' => '01 · WEB', 'w' => 25 ) ),
			alify_acf_f( 'alify_svc_tag', 'Discipline Pill Label', 'text', array( 'ph' => 'Engineering', 'w' => 25 ) ),
			alify_acf_f(
				'alify_svc_filter',
				'Archive Filter Tab',
				'select',
				array(
					'w'       => 25,
					'std'     => 'engineering',
					'choices' => array(
						'design'      => 'Design & Brand',
						'engineering' => 'Web & Engineering',
						'growth'      => 'Growth, CRO & SEO',
					),
				)
			),
			alify_acf_f( 'alify_svc_icon', 'Font Awesome Icon Class', 'text', array( 'ph' => 'fa-solid fa-code', 'w' => 25 ) ),
			alify_acf_f( 'alify_svc_bg_image_upload', 'Upload Card & Hero Image (Media Library)', 'image', array( 'w' => 50, 'desc' => 'Select from Media Library to override the default theme image.' ) ),
			alify_acf_f( 'alify_svc_bg_image', 'Current / Fallback Image URL', 'text', array( 'w' => 50, 'desc' => 'Auto-filled from starter image or Media Library upload.' ) ),
			alify_acf_f( 'alify_svc_points', 'Card Key Deliverables / Bullet Points (1 per line)', 'textarea', array( 'rows' => 4 ) ),

			alify_acf_tab( 'svc_single', 'Single Page Stats & Deliverables' ),
			alify_acf_f( 'alify_svc_stats', 'Single Hero Stats (`Value | Label`, 1 per line)', 'textarea', array( 'rows' => 4, 'ph' => "99+ | Core Web Vitals\n< 0.8s | Avg. LCP Speed\n+142% | Conversion Lift" ) ),
			alify_acf_f( 'alify_svc_deliverables', 'Single Page Deliverable Bento Cards (`Icon Class | Title | Description`, 1 per line)', 'textarea', array( 'rows' => 5 ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_service' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 2: Projects / Work CPT (`alify_project`) + 6 Lightbox Gallery Pickers
	// -------------------------------------------------------------------------
	$proj_fields = array(
		alify_acf_tab( 'proj_card', 'Project Card & Metadata' ),
		alify_acf_f( 'alify_proj_num', 'Panel Number', 'text', array( 'ph' => '01', 'w' => 20 ) ),
		alify_acf_f( 'alify_proj_short_title', 'Short Spine Title', 'text', array( 'ph' => 'FinPulse AI Platform', 'w' => 40 ) ),
		alify_acf_f( 'alify_proj_client', 'Client & Location Label', 'text', array( 'ph' => 'FINPULSE AI · SAN FRANCISCO', 'w' => 40 ) ),
		alify_acf_f( 'alify_proj_sector', 'Sector Badge Label', 'text', array( 'ph' => 'FinTech & SaaS', 'w' => 25 ) ),
		alify_acf_f(
			'alify_proj_filter',
			'Work Grid Filter Category',
			'select',
			array(
				'w'       => 25,
				'std'     => 'web-saas',
				'choices' => array(
					'web-saas'       => 'Web & SaaS',
					'commerce-brand' => 'E-Commerce & Brand',
					'ai-cloud'       => 'AI, Health & PropTech',
				),
			)
		),
		alify_acf_f( 'alify_proj_year', 'Launch Year', 'text', array( 'ph' => '2026', 'w' => 25 ) ),
		alify_acf_f( 'alify_proj_sprint', 'Sprint Duration', 'text', array( 'ph' => '10-Week Sprint', 'w' => 25 ) ),
		alify_acf_f( 'alify_proj_metric', 'Headline Outcome Metric', 'text', array( 'ph' => '+184% Demo Pipeline', 'w' => 34 ) ),
		alify_acf_f( 'alify_proj_chips', 'Deliverable Chips (comma-separated)', 'text', array( 'ph' => 'Web Architecture, Design System, GSAP Motion', 'w' => 33 ) ),
		alify_acf_f( 'alify_proj_case_slug', 'Linked Case Study Slug (optional)', 'text', array( 'ph' => 'cs-finpulse', 'w' => 33 ) ),
		alify_acf_f( 'alify_proj_bg_image_upload', 'Upload Primary Project Image (Media Library)', 'image', array( 'w' => 50 ) ),
		alify_acf_f( 'alify_proj_bg_image', 'Current / Fallback Project Image URL', 'text', array( 'w' => 50 ) ),

		alify_acf_tab( 'proj_story', 'Single Page Story (Brief & Craft)' ),
		alify_acf_f( 'alify_proj_brief', 'The Brief / Challenge', 'textarea', array( 'rows' => 4 ) ),
		alify_acf_f( 'alify_proj_craft', 'Engineering & Design Craft', 'textarea', array( 'rows' => 4 ) ),

		alify_acf_tab( 'proj_gallery', 'Interactive Lightbox Gallery (6 Screens)' ),
	);

	for ( $gi = 1; $gi <= 6; $gi++ ) {
		$proj_fields[] = alify_acf_f( 'alify_proj_gal_' . $gi . '_upload', 'Screen 0' . $gi . ' — Upload Image', 'image', array( 'w' => 25 ) );
		$proj_fields[] = alify_acf_f( 'alify_proj_gal_' . $gi . '_url', 'Screen 0' . $gi . ' — Image URL', 'text', array( 'w' => 25 ) );
		$proj_fields[] = alify_acf_f( 'alify_proj_gal_' . $gi . '_tag', 'Screen 0' . $gi . ' — Tag Pill', 'text', array( 'w' => 20, 'ph' => 'UI Design' ) );
		$proj_fields[] = alify_acf_f( 'alify_proj_gal_' . $gi . '_title', 'Screen 0' . $gi . ' — Title', 'text', array( 'w' => 30 ) );
		$proj_fields[] = alify_acf_f( 'alify_proj_gal_' . $gi . '_cap', 'Screen 0' . $gi . ' — Lightbox Caption', 'text', array( 'w' => 100 ) );
	}

	$groups[] = array(
		'key'      => 'group_alify_cpt_project',
		'title'    => 'ALIFY Project — Showcase, Single Narrative & 6-Screen Lightbox',
		'fields'   => $proj_fields,
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_project' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 3: Industries CPT (`alify_industry`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_cpt_industry',
		'title'    => 'ALIFY Industry — Sector Card & Single Page Content',
		'fields'   => array(
			alify_acf_tab( 'ind_card', 'Industry Card & Archive' ),
			alify_acf_f( 'alify_ind_code', 'Industry Code Badge', 'text', array( 'ph' => '01 · FINTECH', 'w' => 25 ) ),
			alify_acf_f(
				'alify_ind_sector_group',
				'Archive Filter Group',
				'select',
				array(
					'w'       => 25,
					'std'     => 'finance-cloud',
					'choices' => array(
						'finance-cloud'       => 'FinTech, SaaS & AI',
						'commerce-property'   => 'Commerce, PropTech & Travel',
						'health-edu-mobility' => 'Health, EdTech & Mobility',
					),
				)
			),
			alify_acf_f( 'alify_ind_icon', 'Font Awesome Icon Class', 'text', array( 'ph' => 'fa-solid fa-landmark', 'w' => 25 ) ),
			alify_acf_f( 'alify_ind_stat', 'Card Footer Benchmark Stat', 'text', array( 'ph' => 'Avg. +148% Qualified Pipeline', 'w' => 25 ) ),
			alify_acf_f( 'alify_ind_bg_image_upload', 'Upload Industry Image (Media Library)', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_ind_bg_image', 'Current / Fallback Image URL', 'text', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_ind_points', 'Sector Deliverables (1 per line)', 'textarea', array( 'rows' => 4 ) ),

			alify_acf_tab( 'ind_single', 'Single Page Stats' ),
			alify_acf_f( 'alify_ind_stats', 'Single Page Stats (`Value | Label`, 1 per line)', 'textarea', array( 'rows' => 4 ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_industry' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 4: Case Studies CPT (`alify_case_study`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_cpt_case_study',
		'title'    => 'ALIFY Case Study — Benchmarks, KPIs & Executive Quote',
		'fields'   => array(
			alify_acf_tab( 'cs_meta', 'Card & Switcher Metadata' ),
			alify_acf_f(
				'alify_cs_filter',
				'Archive Filter Category',
				'select',
				array(
					'w'       => 33,
					'std'     => 'fintech-saas',
					'choices' => array(
						'fintech-saas'    => 'FinTech & SaaS',
						'commerce-brand'  => 'E-Commerce & Brand',
						'health-proptech' => 'Health & PropTech',
					),
				)
			),
			alify_acf_f( 'alify_cs_tab_index', 'Home Switcher Tab Label', 'text', array( 'ph' => '01 · FINTECH SAAS', 'w' => 33 ) ),
			alify_acf_f( 'alify_cs_client_short', 'Client Short Name', 'text', array( 'ph' => 'FinPulse AI', 'w' => 34 ) ),
			alify_acf_f( 'alify_cs_client_kicker', 'Client & HQ Kicker', 'text', array( 'ph' => 'FINPULSE AI · SAN FRANCISCO', 'w' => 34 ) ),
			alify_acf_f( 'alify_cs_sector_badge', 'Visual Badge 1 (Sector)', 'text', array( 'ph' => 'Enterprise FinTech', 'w' => 33 ) ),
			alify_acf_f( 'alify_cs_duration_badge', 'Visual Badge 2 (Duration)', 'text', array( 'ph' => '10-Week Transformation', 'w' => 33 ) ),
			alify_acf_f( 'alify_cs_bg_image_upload', 'Upload Case Study Image (Media Library)', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_cs_bg_image', 'Current / Fallback Image URL', 'text', array( 'w' => 50 ) ),

			alify_acf_tab( 'cs_metrics', 'Bottleneck, Architecture & KPIs' ),
			alify_acf_f( 'alify_cs_bottleneck', '01 · The Bottleneck', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'alify_cs_architecture', '02 · ALIFY Architecture', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'alify_cs_benchmarks', 'Before vs. After Benchmarks (`Label | Before | After`, 1 per line)', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'alify_cs_kpis', '3 Verified KPI Tiles (`Value | Label`, 1 per line)', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'alify_cs_chips', 'Architecture Blueprint Chips (comma-separated)', 'text', array( 'w' => 100 ) ),

			alify_acf_tab( 'cs_quote', 'Executive Client Quote' ),
			alify_acf_f( 'alify_cs_quote', 'Executive Quote Text', 'textarea', array( 'rows' => 3 ) ),
			alify_acf_f( 'alify_cs_quote_author', 'Executive Quote Author & Role', 'text' ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_case_study' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 5: Testimonials CPT (`alify_testimonial`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_cpt_testimonial',
		'title'    => 'ALIFY Testimonial — Client Role, Rating & Verified Outcome',
		'fields'   => array(
			alify_acf_f( 'alify_testi_role', 'Client Role & Company', 'text', array( 'ph' => 'VP of Marketing, FinPulse AI', 'w' => 40 ) ),
			alify_acf_f( 'alify_testi_initials', 'Avatar Initials (2 Letters)', 'text', array( 'ph' => 'SJ', 'w' => 20 ) ),
			alify_acf_f( 'alify_testi_rating', 'Star Rating Score', 'text', array( 'std' => '5.0', 'w' => 20 ) ),
			alify_acf_f(
				'alify_testi_filter',
				'Archive Filter Tab',
				'select',
				array(
					'w'       => 20,
					'std'     => 'web',
					'choices' => array(
						'web'      => 'Web & Engineering',
						'branding' => 'UI/UX & Branding',
						'systems'  => 'Design Systems',
						'growth'   => 'E-Commerce & SEO',
					),
				)
			),
			alify_acf_f( 'alify_testi_outcome', 'Verified Outcome Pill', 'text', array( 'ph' => '+184% Demo Pipeline', 'w' => 50 ) ),
			alify_acf_f( 'alify_testi_tag', 'Footer Capability Tag', 'text', array( 'ph' => 'Web & Engineering', 'w' => 50 ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_testimonial' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 6: FAQs CPT (`alify_faq`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_cpt_faq',
		'title'    => 'ALIFY FAQ — Knowledge Pillar & Global Reusable Toggle',
		'fields'   => array(
			alify_acf_f(
				'alify_faq_pillar',
				'Knowledge Hub Pillar',
				'select',
				array(
					'w'       => 50,
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
			alify_acf_f( 'alify_faq_show_reusable', 'Show in Global 6-Item Reusable FAQ Section?', 'true_false', array( 'w' => 50, 'std' => 0 ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'alify_faq' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 7: Native Blog Posts (`post`)
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_blog_post',
		'title'    => 'ALIFY Blog Post — Hero Banner & Reading Metadata',
		'fields'   => array(
			alify_acf_f( 'alify_post_bg_image_upload', 'Upload Single Hero / Card Image Override', 'image', array( 'w' => 50, 'desc' => 'Optional override if not using WordPress Featured Image.' ) ),
			alify_acf_f( 'alify_post_bg_image', 'Current / Fallback Image URL', 'text', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_post_badge', 'Custom Hero Category Badge Override', 'text', array( 'w' => 34, 'ph' => 'Leave blank to use Post Category' ) ),
			alify_acf_f( 'alify_post_read_time', 'Custom Reading Time Override', 'text', array( 'w' => 33, 'ph' => 'e.g. 6 Min Read (auto-calculated if blank)' ) ),
			alify_acf_f( 'alify_post_author_label', 'Custom Author Byline Override', 'text', array( 'w' => 33, 'ph' => 'e.g. ALIFY Editorial Team' ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 8: Pages (`page`) — Per-Page Hero & Section Visibility Switches
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_page_controls',
		'title'    => 'ALIFY Page Hero Override & Section Visibility Controls',
		'fields'   => array(
			alify_acf_tab( 'pg_hero', 'Page Hero Override' ),
			alify_acf_f( 'alify_page_hero_eyebrow', 'Hero Eyebrow Badge Text', 'text', array( 'w' => 35, 'desc' => 'Leave blank to use default / Theme Settings copy.' ) ),
			alify_acf_f( 'alify_page_hero_title', 'Hero Main H1 Heading (`<span>` allowed for accent)', 'text', array( 'w' => 65 ) ),
			alify_acf_f( 'alify_page_hero_desc', 'Hero Lead Description', 'textarea', array( 'rows' => 2 ) ),
			alify_acf_f( 'alify_page_hero_bg_upload', 'Upload Hero Background Image Override', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_page_hero_bg', 'Hero Background Image URL', 'text', array( 'w' => 50 ) ),

			alify_acf_tab( 'pg_vis', 'Section Visibility Switches' ),
			alify_acf_f( 'alify_page_hide_hero_form', 'Hide Right-Side Hero Lead Form Card', 'true_false', array( 'w' => 33 ) ),
			alify_acf_f( 'alify_page_hide_testimonials', 'Hide Reusable Testimonials Slider', 'true_false', array( 'w' => 33 ) ),
			alify_acf_f( 'alify_page_hide_faq', 'Hide Reusable FAQs Section', 'true_false', array( 'w' => 34 ) ),
			alify_acf_f( 'alify_page_hide_blog_slider', 'Hide Reusable Blog Slider', 'true_false', array( 'w' => 50 ) ),
			alify_acf_f( 'alify_page_hide_contact', 'Hide Bottom Reusable Contact Section', 'true_false', array( 'w' => 50 ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 9: THEME SETTINGS -> Global, Header, Footer & Reusable Sections
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_opt_global',
		'title'    => 'Global Brand, Header, Footer & Reusable Sections',
		'fields'   => array(
			alify_acf_tab( 'g_brand', 'Brand Identity & Contact' ),
			alify_acf_f( 'opt_brand_logo', 'Custom Brand Logo Image (Optional)', 'image', array( 'w' => 50, 'desc' => 'Leave empty to display the signature geometric △LIFY Garet logo.' ) ),
			alify_acf_f( 'opt_brand_logo_height', 'Custom Logo Max Height (px)', 'number', array( 'w' => 50, 'std' => 38 ) ),
			alify_acf_f( 'opt_email', 'Official Studio Email', 'email', array( 'w' => 33, 'std' => 'info@alify.site' ) ),
			alify_acf_f( 'opt_whatsapp', 'WhatsApp / Phone Display', 'text', array( 'w' => 33, 'std' => '+90 301 9328094' ) ),
			alify_acf_f( 'opt_whatsapp_url', 'WhatsApp Direct Link (`wa.me`)', 'url', array( 'w' => 34, 'std' => 'https://wa.me/903019328094' ) ),
			alify_acf_f( 'opt_hq', 'Studio Headquarters Label', 'text', array( 'w' => 50, 'std' => 'Karachi, PK — Global Delivery' ) ),
			alify_acf_f( 'opt_hours', 'Studio Operating Hours', 'text', array( 'w' => 50, 'std' => 'Mon – Fri, 9:00 AM – 7:00 PM PKT' ) ),

			alify_acf_tab( 'g_header', 'Header & CTA Buttons' ),
			alify_acf_f( 'opt_header_contact_text', 'Header Secondary Button Label', 'text', array( 'w' => 50, 'std' => 'Contact Us' ) ),
			alify_acf_f( 'opt_header_contact_url', 'Header Secondary Button Slug / URL', 'text', array( 'w' => 50, 'std' => 'contact' ) ),
			alify_acf_f( 'opt_header_cta_text', 'Header Primary Split-Pill CTA Label', 'text', array( 'w' => 50, 'std' => 'Start a Project' ) ),
			alify_acf_f( 'opt_header_cta_url', 'Header Primary Split-Pill Slug / URL', 'text', array( 'w' => 50, 'std' => 'start-project' ) ),

			alify_acf_tab( 'g_footer', 'Footer & Pre-Footer CTA' ),
			alify_acf_f( 'opt_footer_cta_eyebrow', 'Pre-Footer Banner Eyebrow', 'text', array( 'w' => 40, 'std' => 'Ready to Scale Your Brand?' ) ),
			alify_acf_f( 'opt_footer_cta_title', 'Pre-Footer Banner Heading', 'text', array( 'w' => 60, 'std' => 'Let’s Build Something Remarkable Together.' ) ),
			alify_acf_f( 'opt_footer_cta_desc', 'Pre-Footer Banner Description', 'textarea', array( 'rows' => 2, 'std' => 'Partner with ALIFY to craft high-converting digital products, iconic brand identities, and scalable web experiences.' ) ),
			alify_acf_f( 'opt_footer_brand_desc', 'Footer Brand Bio Paragraph', 'textarea', array( 'rows' => 2, 'std' => 'A design-first digital studio engineering bespoke websites, brand systems, and scalable products for ambitious companies worldwide.' ) ),
			alify_acf_f( 'opt_footer_col1_title', 'Footer Menu Column 1 Heading', 'text', array( 'w' => 25, 'std' => 'Navigation' ) ),
			alify_acf_f( 'opt_footer_col2_title', 'Footer Menu Column 2 Heading', 'text', array( 'w' => 25, 'std' => 'Services' ) ),
			alify_acf_f( 'opt_footer_col3_title', 'Footer Contact Column Heading', 'text', array( 'w' => 25, 'std' => 'Get in Touch' ) ),
			alify_acf_f( 'opt_footer_copyright', 'Footer Copyright Text', 'text', array( 'w' => 25, 'std' => 'ALIFY Studio. All rights reserved.' ) ),

			alify_acf_tab( 'g_socials', 'Social Media Profiles' ),
			alify_acf_f( 'opt_social_linkedin', 'LinkedIn URL', 'url', array( 'w' => 33, 'std' => 'https://linkedin.com' ) ),
			alify_acf_f( 'opt_social_instagram', 'Instagram URL', 'url', array( 'w' => 33, 'std' => 'https://instagram.com' ) ),
			alify_acf_f( 'opt_social_twitter', 'X (Twitter) URL', 'url', array( 'w' => 34, 'std' => 'https://x.com' ) ),
			alify_acf_f( 'opt_social_behance', 'Behance URL', 'url', array( 'w' => 50, 'std' => 'https://behance.net' ) ),
			alify_acf_f( 'opt_social_dribbble', 'Dribbble URL', 'url', array( 'w' => 50, 'std' => 'https://dribbble.com' ) ),

			alify_acf_tab( 'g_reusable', 'Global Reusable Sections (Testimonials, FAQ, Blog & Contact)' ),
			alify_acf_f( 'opt_testi_eyebrow', 'Testimonials Slider — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Client Stories & Proof' ) ),
			alify_acf_f( 'opt_testi_title', 'Testimonials Slider — Heading (`<span>` accent allowed)', 'text', array( 'w' => 65, 'std' => 'Trusted by Ambitious <span>Founders & Product Leaders.</span>' ) ),
			alify_acf_f( 'opt_faq_eyebrow', 'Reusable FAQs — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Frequently Asked Questions' ) ),
			alify_acf_f( 'opt_faq_title', 'Reusable FAQs — Heading (`<span>` accent allowed)', 'text', array( 'w' => 65, 'std' => 'Got Questions? We’ve Got <span>Clear Answers.</span>' ) ),
			alify_acf_f( 'opt_faq_desc', 'Reusable FAQs — Intro Description', 'textarea', array( 'rows' => 2, 'std' => 'Everything you need to know about our design sprints, custom engineering workflow, timelines, and ongoing studio partnerships.' ) ),
			alify_acf_f( 'opt_blog_eyebrow', 'Blog Slider — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Studio Journal & Insights' ) ),
			alify_acf_f( 'opt_blog_title', 'Blog Slider — Heading (`<span>` accent allowed)', 'text', array( 'w' => 65, 'std' => 'Latest Thinking on <span>Design, Motion & Growth.</span>' ) ),
			alify_acf_f( 'opt_rcontact_eyebrow', 'Bottom Contact Section — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Let’s Collaborate' ) ),
			alify_acf_f( 'opt_rcontact_title', 'Bottom Contact Section — Heading (`<span>` accent allowed)', 'text', array( 'w' => 65, 'std' => 'Ready to Elevate Your <span>Digital Presence?</span>' ) ),
			alify_acf_f( 'opt_rcontact_desc', 'Bottom Contact Section — Description', 'textarea', array( 'rows' => 2, 'std' => 'Tell us what you are building — our design directors and lead engineers will respond within one business day with clear next steps.' ) ),
		),
		'location' => array( array( array( 'param' => 'alify_options_screen', 'operator' => '==', 'value' => 'global' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 10: THEME SETTINGS -> Home Page Content
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_opt_home',
		'title'    => 'Home Page — Hero, Sections & 4-Sprint Process',
		'fields'   => array(
			alify_acf_tab( 'h_hero', 'Home Hero & Stat Counters' ),
			alify_acf_f( 'home_hero_bg', 'Home Hero Background Image', 'image', array( 'w' => 40, 'desc' => 'Upload custom Home Hero background image (leave empty for default ALIFY hero artwork).' ) ),
			alify_acf_f( 'home_status_pill', 'Availability Status Pill', 'text', array( 'w' => 60, 'std' => 'Creative Digital Studio — Available for New Projects' ) ),
			alify_acf_f( 'home_title_before', 'Hero H1 — Part 1', 'text', array( 'w' => 33, 'std' => 'We Craft Bold' ) ),
			alify_acf_f( 'home_title_accent', 'Hero H1 — Highlighted Accent', 'text', array( 'w' => 34, 'std' => 'Digital Experiences' ) ),
			alify_acf_f( 'home_title_after', 'Hero H1 — Part 2', 'text', array( 'w' => 33, 'std' => 'That Scale Brands.' ) ),
			alify_acf_f( 'home_lead', 'Hero Lead Paragraph', 'textarea', array( 'rows' => 2, 'std' => 'ALIFY partners with visionary startups and category-defining brands to design, engineer, and launch high-converting websites, design systems, and digital products.' ) ),
			alify_acf_f( 'home_primary_btn', 'Hero Primary CTA Button Label', 'text', array( 'w' => 50, 'std' => 'Start a Project' ) ),
			alify_acf_f( 'home_sec_btn', 'Hero Secondary CTA Button Label', 'text', array( 'w' => 50, 'std' => 'Explore Our Work' ) ),
			alify_acf_f( 'home_stat1_val', 'Stat 1 Number', 'text', array( 'w' => 11, 'std' => '150' ) ),
			alify_acf_f( 'home_stat1_suf', 'Stat 1 Suffix', 'text', array( 'w' => 11, 'std' => '+' ) ),
			alify_acf_f( 'home_stat1_lbl', 'Stat 1 Label', 'text', array( 'w' => 11, 'std' => 'Projects Delivered' ) ),
			alify_acf_f( 'home_stat2_val', 'Stat 2 Number', 'text', array( 'w' => 11, 'std' => '98' ) ),
			alify_acf_f( 'home_stat2_suf', 'Stat 2 Suffix', 'text', array( 'w' => 11, 'std' => '%' ) ),
			alify_acf_f( 'home_stat2_lbl', 'Stat 2 Label', 'text', array( 'w' => 11, 'std' => 'Client Retention' ) ),
			alify_acf_f( 'home_stat3_val', 'Stat 3 Number', 'text', array( 'w' => 11, 'std' => '12' ) ),
			alify_acf_f( 'home_stat3_suf', 'Stat 3 Suffix', 'text', array( 'w' => 11, 'std' => '+' ) ),
			alify_acf_f( 'home_stat3_lbl', 'Stat 3 Label', 'text', array( 'w' => 12, 'std' => 'Global Markets' ) ),

			alify_acf_tab( 'h_why', 'Why Partner With ALIFY' ),
			alify_acf_f( 'home_why_eyebrow', 'Section Eyebrow', 'text', array( 'w' => 35, 'std' => 'Why Partner With ALIFY' ) ),
			alify_acf_f( 'home_why_title', 'Section Heading (`<span>` accent allowed)', 'text', array( 'w' => 65, 'std' => 'Senior Design Craft Meets <span>Revenue-Grade Engineering.</span>' ) ),
			alify_acf_f( 'home_why_desc', 'Lead Paragraph', 'textarea', array( 'rows' => 3, 'std' => 'Most agencies hand off static slides or bloated themes. ALIFY embeds a dedicated senior squad of strategists, system designers, and engineers to ship sub-second digital flagships built to dominate your category.' ) ),
			alify_acf_f( 'home_why_image', 'Right-Column Studio Visual Image', 'image', array( 'w' => 50 ) ),

			alify_acf_tab( 'h_sliders', 'Services, Industries, Projects & Case Studies Headings' ),
			alify_acf_f( 'home_srv_eyebrow', 'Services Section — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Core Capabilities' ) ),
			alify_acf_f( 'home_srv_title', 'Services Section — Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'End-to-End Digital Craft Built to <span>Move Markets.</span>' ) ),
			alify_acf_f( 'home_ind_eyebrow', 'Industries Section — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Industries We Transform' ) ),
			alify_acf_f( 'home_ind_title', 'Industries Section — Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Tailored Digital Ecosystems for <span>High-Growth Verticals.</span>' ) ),
			alify_acf_f( 'home_prj_eyebrow', 'Featured Work — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Selected Portfolio' ) ),
			alify_acf_f( 'home_prj_title', 'Featured Work — Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Flagship Digital Products That <span>Redefined Their Category.</span>' ) ),
			alify_acf_f( 'home_cs_eyebrow', 'Case Studies — Eyebrow', 'text', array( 'w' => 35, 'std' => 'Verified Outcomes' ) ),
			alify_acf_f( 'home_cs_title', 'Case Studies — Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Where Design Craft Meets <span>Hard Revenue Metrics.</span>' ) ),

			alify_acf_tab( 'h_process', '4-Sprint Execution Process' ),
			alify_acf_f( 'home_proc_eyebrow', 'Process Eyebrow', 'text', array( 'w' => 35, 'std' => 'Execution Methodology' ) ),
			alify_acf_f( 'home_proc_title', 'Process Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'From Strategic Discovery to <span>Sub-Second Launch.</span>' ) ),
			alify_acf_f( 'home_proc_desc', 'Process Lead Description', 'textarea', array( 'rows' => 2, 'std' => 'Every ALIFY engagement runs on transparent weekly sprints with senior specialists — zero black-box handoffs, zero guesswork.' ) ),
			alify_acf_f( 'home_proc_s1_title', 'Sprint 01 Title', 'text', array( 'w' => 33, 'std' => 'Discovery & Blueprint' ) ),
			alify_acf_f( 'home_proc_s1_desc', 'Sprint 01 Description', 'text', array( 'w' => 67, 'std' => 'Stakeholder alignment, competitor teardown, funnel diagnostics, and technical architecture roadmap.' ) ),
			alify_acf_f( 'home_proc_s2_title', 'Sprint 02 Title', 'text', array( 'w' => 33, 'std' => 'UX Systems & Brand Craft' ) ),
			alify_acf_f( 'home_proc_s2_desc', 'Sprint 02 Description', 'text', array( 'w' => 67, 'std' => 'Interactive wireframes, Figma design tokens, signature UI direction, and conversion copywriting.' ) ),
			alify_acf_f( 'home_proc_s3_title', 'Sprint 03 Title', 'text', array( 'w' => 33, 'std' => 'Full-Stack & GSAP Build' ) ),
			alify_acf_f( 'home_proc_s3_desc', 'Sprint 03 Description', 'text', array( 'w' => 67, 'std' => 'Clean semantic code, 60fps GSAP scroll choreography, CMS/API integration, and Core Web Vitals tuning.' ) ),
			alify_acf_f( 'home_proc_s4_title', 'Sprint 04 Title', 'text', array( 'w' => 33, 'std' => 'QA, Launch & Growth' ) ),
			alify_acf_f( 'home_proc_s4_desc', 'Sprint 04 Description', 'text', array( 'w' => 67, 'std' => 'Cross-device QA, technical SEO schema verification, zero-downtime deployment, and conversion tracking.' ) ),
		),
		'location' => array( array( array( 'param' => 'alify_options_screen', 'operator' => '==', 'value' => 'home' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 11: THEME SETTINGS -> Archive & Hub Pages
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_opt_archives',
		'title'    => 'Archive & Hub Pages Content (All Inner Pages)',
		'fields'   => array(
			alify_acf_tab( 'a_srv', 'Services Archive (/services/)' ),
			alify_acf_f( 'arch_srv_hero_eyebrow', 'Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Our Capabilities' ) ),
			alify_acf_f( 'arch_srv_hero_title', 'Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'End-to-End <span class="inner-hero-title-accent">Digital Solutions</span> Engineered for Growth.' ) ),
			alify_acf_f( 'arch_srv_hero_desc', 'Hero Description', 'textarea', array( 'rows' => 2, 'std' => 'From brand positioning and tokenized UI/UX systems to custom full-stack platforms and technical SEO — explore our 8 core disciplines.' ) ),
			alify_acf_f( 'arch_srv_hero_bg', 'Services Hero Background Image', 'image', array( 'w' => 40 ) ),
			alify_acf_f( 'arch_srv_sec_title', 'Grid Section Heading (`<span>` allowed)', 'text', array( 'w' => 60, 'std' => 'Specialized Squads Across <span>Design, Code & Growth.</span>' ) ),

			alify_acf_tab( 'a_wrk', 'Work Archive (/work/)' ),
			alify_acf_f( 'arch_wrk_hero_eyebrow', 'Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Selected Portfolio' ) ),
			alify_acf_f( 'arch_wrk_hero_title', 'Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Crafted Platforms & Brands That <span class="inner-hero-title-accent">Move the Needle.</span>' ) ),
			alify_acf_f( 'arch_wrk_hero_desc', 'Hero Description', 'textarea', array( 'rows' => 2, 'std' => 'Explore flagship websites, SaaS platforms, headless storefronts, and design systems shipped by ALIFY squads.' ) ),
			alify_acf_f( 'arch_wrk_hero_bg', 'Work Hero Background Image', 'image', array( 'w' => 40 ) ),
			alify_acf_f( 'arch_wrk_sec_title', 'Grid Section Heading (`<span>` allowed)', 'text', array( 'w' => 60, 'std' => 'Engineered for Impact Across <span>Every Digital Touchpoint.</span>' ) ),

			alify_acf_tab( 'a_ind', 'Industries & Case Studies (/industries/ & /case-studies/)' ),
			alify_acf_f( 'arch_ind_hero_eyebrow', 'Industries Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Vertical Expertise' ) ),
			alify_acf_f( 'arch_ind_hero_title', 'Industries Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Domain-Focused Digital Craft Across <span class="inner-hero-title-accent">Global Industries.</span>' ) ),
			alify_acf_f( 'arch_ind_hero_desc', 'Industries Hero Description', 'textarea', array( 'rows' => 2 ) ),
			alify_acf_f( 'arch_ind_hero_bg', 'Industries Hero Background Image', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'arch_ind_sec_title', 'Industries Grid Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Digital Products Engineered for <span>Every Vertical’s Reality.</span>' ) ),
			alify_acf_f( 'arch_cs_hero_eyebrow', 'Case Studies Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Proven Impact' ) ),
			alify_acf_f( 'arch_cs_hero_title', 'Case Studies Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Deep Dives Into <span class="inner-hero-title-accent">Measurable Client</span> Transformations.' ) ),
			alify_acf_f( 'arch_cs_hero_desc', 'Case Studies Hero Description', 'textarea', array( 'rows' => 2 ) ),
			alify_acf_f( 'arch_cs_hero_bg', 'Case Studies Hero Background Image', 'image', array( 'w' => 50 ) ),

			alify_acf_tab( 'a_blog', 'Blog Index (/blog/)' ),
			alify_acf_f( 'arch_blog_hero_eyebrow', 'Blog Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Studio Journal' ) ),
			alify_acf_f( 'arch_blog_hero_title', 'Blog Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Perspectives on <span class="inner-hero-title-accent">Design, Code</span> & Digital Growth.' ) ),
			alify_acf_f( 'arch_blog_hero_desc', 'Blog Hero Description', 'textarea', array( 'rows' => 2, 'std' => 'Actionable guides, engineering deep dives, and brand strategy insights written by the product designers and developers at ALIFY.' ) ),
			alify_acf_f( 'arch_blog_hero_bg', 'Blog Hero Background Image', 'image', array( 'w' => 40 ) ),
			alify_acf_f( 'arch_blog_card_metric', 'Hero Right Card Metric', 'text', array( 'w' => 30, 'std' => 'Weekly' ) ),
			alify_acf_f( 'arch_blog_card_cta', 'Hero Right Card Link Label', 'text', array( 'w' => 30, 'std' => 'Work With Our Team' ) ),
			alify_acf_f( 'arch_blog_card_label', 'Hero Right Card Description', 'text', array( 'w' => 100, 'std' => 'Fresh perspectives on modern web craft, GSAP motion systems, and scalable UX.' ) ),

			alify_acf_tab( 'a_pricing', 'Pricing & 3 Tiers (/pricing/)' ),
			alify_acf_f( 'arch_prc_hero_eyebrow', 'Pricing Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Transparent Investment' ) ),
			alify_acf_f( 'arch_prc_hero_title', 'Pricing Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Predictable <span class="inner-hero-title-accent">Pricing Models.</span> Senior Studio Execution.' ) ),
			alify_acf_f( 'arch_prc_hero_desc', 'Pricing Hero Description', 'textarea', array( 'rows' => 2 ) ),
			alify_acf_f( 'arch_prc_hero_bg', 'Pricing Hero Background Image', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'arch_prc_save_pill', 'Retainer Toggle Discount Pill', 'text', array( 'w' => 50, 'std' => 'Save 15%' ) ),
			alify_acf_f( 'prc_t1_name', 'Tier 1 Name', 'text', array( 'w' => 25, 'std' => 'Launch Sprint' ) ),
			alify_acf_f( 'prc_t1_sprint', 'Tier 1 Sprint Price', 'text', array( 'w' => 25, 'std' => '$4,500' ) ),
			alify_acf_f( 'prc_t1_retainer', 'Tier 1 Retainer Price', 'text', array( 'w' => 25, 'std' => '$3,800' ) ),
			alify_acf_f( 'prc_t1_velocity', 'Tier 1 Timeline', 'text', array( 'w' => 25, 'std' => '3–4 Weeks' ) ),
			alify_acf_f( 'prc_t1_summary', 'Tier 1 Summary', 'text', array( 'w' => 50 ) ),
			alify_acf_f( 'prc_t1_features', 'Tier 1 Bullet Features (1 per line)', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'prc_t2_name', 'Tier 2 Name', 'text', array( 'w' => 25, 'std' => 'Scale Ecosystem' ) ),
			alify_acf_f( 'prc_t2_sprint', 'Tier 2 Sprint Price', 'text', array( 'w' => 25, 'std' => '$9,500' ) ),
			alify_acf_f( 'prc_t2_retainer', 'Tier 2 Retainer Price', 'text', array( 'w' => 25, 'std' => '$7,900' ) ),
			alify_acf_f( 'prc_t2_velocity', 'Tier 2 Timeline', 'text', array( 'w' => 25, 'std' => '6–8 Weeks' ) ),
			alify_acf_f( 'prc_t2_summary', 'Tier 2 Summary', 'text', array( 'w' => 50 ) ),
			alify_acf_f( 'prc_t2_features', 'Tier 2 Bullet Features (1 per line)', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),
			alify_acf_f( 'prc_t3_name', 'Tier 3 Name', 'text', array( 'w' => 25, 'std' => 'Enterprise Squad' ) ),
			alify_acf_f( 'prc_t3_sprint', 'Tier 3 Sprint Price', 'text', array( 'w' => 25, 'std' => '$18,500' ) ),
			alify_acf_f( 'prc_t3_retainer', 'Tier 3 Retainer Price', 'text', array( 'w' => 25, 'std' => '$14,800' ) ),
			alify_acf_f( 'prc_t3_velocity', 'Tier 3 Timeline', 'text', array( 'w' => 25, 'std' => '8–12 Weeks' ) ),
			alify_acf_f( 'prc_t3_summary', 'Tier 3 Summary', 'text', array( 'w' => 50 ) ),
			alify_acf_f( 'prc_t3_features', 'Tier 3 Bullet Features (1 per line)', 'textarea', array( 'rows' => 3, 'w' => 50 ) ),

			alify_acf_tab( 'a_about', 'About Us & Leadership (/about/)' ),
			alify_acf_f( 'arch_abt_hero_eyebrow', 'About Hero Eyebrow', 'text', array( 'w' => 35, 'std' => 'Who We Are' ) ),
			alify_acf_f( 'arch_abt_hero_title', 'About Hero Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'Design-Driven Minds Building <span class="inner-hero-title-accent">Iconic Digital</span> Brands.' ) ),
			alify_acf_f( 'arch_abt_hero_desc', 'About Hero Description', 'textarea', array( 'rows' => 2 ) ),
			alify_acf_f( 'arch_abt_hero_bg', 'About Hero Background Image', 'image', array( 'w' => 50 ) ),
			alify_acf_f( 'arch_abt_story_title', 'Studio Story Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Built for Founders Who Refuse <span>Average Digital Products.</span>' ) ),
			alify_acf_f( 'arch_abt_team_title', 'Leadership Section Heading (`<span>` allowed)', 'text', array( 'w' => 100, 'std' => 'Meet the Principals Leading <span>Every Engagement.</span>' ) ),
			alify_acf_f( 'abt_leader_1_img', 'Leader 01 Photo', 'image', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_1_name', 'Leader 01 Name', 'text', array( 'w' => 25, 'std' => 'Zayd Alvi' ) ),
			alify_acf_f( 'abt_leader_1_role', 'Leader 01 Role', 'text', array( 'w' => 25, 'std' => 'Founder & Managing Partner' ) ),
			alify_acf_f( 'abt_leader_1_bio', 'Leader 01 Bio', 'text', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_2_img', 'Leader 02 Photo', 'image', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_2_name', 'Leader 02 Name', 'text', array( 'w' => 25, 'std' => 'Nadia Karim' ) ),
			alify_acf_f( 'abt_leader_2_role', 'Leader 02 Role', 'text', array( 'w' => 25, 'std' => 'Head of Design Systems' ) ),
			alify_acf_f( 'abt_leader_2_bio', 'Leader 02 Bio', 'text', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_3_img', 'Leader 03 Photo', 'image', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_3_name', 'Leader 03 Name', 'text', array( 'w' => 25, 'std' => 'Tariq Mahmood' ) ),
			alify_acf_f( 'abt_leader_3_role', 'Leader 03 Role', 'text', array( 'w' => 25, 'std' => 'Principal Full-Stack Architect' ) ),
			alify_acf_f( 'abt_leader_3_bio', 'Leader 03 Bio', 'text', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_4_img', 'Leader 04 Photo', 'image', array( 'w' => 25 ) ),
			alify_acf_f( 'abt_leader_4_name', 'Leader 04 Name', 'text', array( 'w' => 25, 'std' => 'Hira Siddiqui' ) ),
			alify_acf_f( 'abt_leader_4_role', 'Leader 04 Role', 'text', array( 'w' => 25, 'std' => 'Director of Growth & CRO' ) ),
			alify_acf_f( 'abt_leader_4_bio', 'Leader 04 Bio', 'text', array( 'w' => 25 ) ),

			alify_acf_tab( 'a_other', 'Testimonials, FAQ, Contact, Start Project, Book Call, Thank You & 404' ),
			alify_acf_f( 'arch_tst_hero_title', 'Testimonials Page Hero Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Real Words From Leaders Who <span class="inner-hero-title-accent">Scaled With ALIFY.</span>' ) ),
			alify_acf_f( 'arch_faq_hero_title', 'FAQ Hub Page Hero Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Transparent Answers on <span class="inner-hero-title-accent">Scope, Craft & ROI.</span>' ) ),
			alify_acf_f( 'arch_cnt_hero_title', 'Contact Page Hero Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Let’s Start a Conversation About <span class="inner-hero-title-accent">Your Next Move.</span>' ) ),
			alify_acf_f( 'arch_sp_hero_title', 'Start Project Hero Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Share Your Vision — Let’s Build <span class="inner-hero-title-accent">Something Iconic.</span>' ) ),
			alify_acf_f( 'arch_bc_hero_title', 'Book a Call Hero Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Book a <span class="inner-hero-title-accent">Strategy Call</span> With Our Principals.' ) ),
			alify_acf_f( 'arch_bc_host_name', 'Book a Call Host Name', 'text', array( 'w' => 25, 'std' => 'ALIFY Principal Pod' ) ),
			alify_acf_f( 'arch_bc_host_role', 'Book a Call Host Role', 'text', array( 'w' => 25, 'std' => 'Managing Partner & Lead Architect · 30 Min' ) ),
			alify_acf_f( 'arch_ty_title', 'Thank You Page Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Thank You — <span>Your Brief Is With Our Principals.</span>' ) ),
			alify_acf_f( 'arch_404_title', '404 Page Heading', 'text', array( 'w' => 50, 'std' => 'This Route Doesn’t Exist — Let’s Get You Back on Track.' ) ),
		),
		'location' => array( array( array( 'param' => 'alify_options_screen', 'operator' => '==', 'value' => 'archives' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	// -------------------------------------------------------------------------
	// GROUP 12: THEME SETTINGS -> Single Page Templates Global
	// -------------------------------------------------------------------------
	$groups[] = array(
		'key'      => 'group_alify_opt_singles',
		'title'    => 'Single Templates Global Settings (CPT Singles & Blog Single)',
		'fields'   => array(
			alify_acf_tab( 's_srv', 'Service Single Global' ),
			alify_acf_f( 'sgl_srv_overview_eyebrow', 'Overview Section Eyebrow', 'text', array( 'w' => 35, 'std' => 'What You Get' ) ),
			alify_acf_f( 'sgl_srv_overview_title', 'Overview Section Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'End-to-End Scope & <span>Production Deliverables.</span>' ) ),
			alify_acf_f( 'sgl_srv_process_title', 'Process Section Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'A Transparent 4-Sprint <span>Delivery Framework.</span>' ) ),
			alify_acf_f( 'sgl_srv_related_title', 'Related Projects Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Featured Projects Shipped by <span>This Capability Squad.</span>' ) ),

			alify_acf_tab( 's_wrk', 'Work / Project Single Global' ),
			alify_acf_f( 'sgl_wrk_related_eyebrow', 'Related Work Eyebrow', 'text', array( 'w' => 35, 'std' => 'Continue Exploring' ) ),
			alify_acf_f( 'sgl_wrk_related_title', 'Related Work Heading (`<span>` allowed)', 'text', array( 'w' => 65, 'std' => 'More Selected Work From <span>ALIFY Studio.</span>' ) ),

			alify_acf_tab( 's_ind_cs', 'Industry & Case Study Single Global' ),
			alify_acf_f( 'sgl_ind_overview_title', 'Industry Single Overview Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Turning Vertical Complexity Into <span>Competitive Advantage.</span>' ) ),
			alify_acf_f( 'sgl_ind_related_title', 'Industry Single Related Projects Heading (`<span>` allowed)', 'text', array( 'w' => 50, 'std' => 'Selected Client Launches in <span>Related Markets.</span>' ) ),
			alify_acf_f( 'sgl_cs_snapshot_heading', 'Case Study Sidebar Snapshot Heading', 'text', array( 'w' => 40, 'std' => 'Engagement Snapshot' ) ),
			alify_acf_f( 'sgl_cs_related_title', 'Case Study Related Launches Heading (`<span>` allowed)', 'text', array( 'w' => 60, 'std' => 'Explore Related Client <span>Product Launches.</span>' ) ),

			alify_acf_tab( 's_blog', 'Blog Single Global (single.php)' ),
			alify_acf_f( 'sgl_blog_default_hero_bg', 'Default Single Blog Hero Background Image', 'image', array( 'w' => 50, 'desc' => 'Used when a blog post does not have a Featured Image or Post Hero Image set.' ) ),
			alify_acf_f( 'sgl_blog_toc_heading', 'Left Sticky TOC Heading', 'text', array( 'w' => 50, 'std' => 'Table of Contents' ) ),
			alify_acf_f( 'sgl_blog_sidebar_cta_title', 'Right Sidebar CTA Card Heading', 'text', array( 'w' => 40, 'std' => 'Have a Project in Mind?' ) ),
			alify_acf_f( 'sgl_blog_sidebar_cta_btn', 'Right Sidebar CTA Button Label', 'text', array( 'w' => 30, 'std' => 'Start a Project' ) ),
			alify_acf_f( 'sgl_blog_related_eyebrow', 'Related Articles Eyebrow', 'text', array( 'w' => 30, 'std' => 'Keep Reading' ) ),
			alify_acf_f( 'sgl_blog_sidebar_cta_desc', 'Right Sidebar CTA Card Description', 'textarea', array( 'rows' => 2, 'std' => 'Let’s architect a fast, conversion-led digital platform tailored to your brand.' ) ),
			alify_acf_f( 'sgl_blog_related_title', 'Related Articles Heading (`<span>` allowed)', 'text', array( 'w' => 100, 'std' => 'Related Blogs & <span>Engineering Insights.</span>' ) ),
		),
		'location' => array( array( array( 'param' => 'alify_options_screen', 'operator' => '==', 'value' => 'singles' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
	);

	return $groups;
}

/**
 * Register Local ACF Field Groups as Fallback (Skips Any Group Already Loaded From `acf-json/` or WP DB).
 */
function alify_register_acf_local_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	foreach ( alify_get_acf_field_groups_definitions() as $group ) {
		if ( function_exists( 'acf_get_field_group' ) && acf_get_field_group( $group['key'] ) ) {
			continue;
		}
		acf_add_local_field_group( $group );
	}
}
add_action( 'acf/include_fields', 'alify_register_acf_local_groups', 20 );

/**
 * 7. Two-Way Sync Between ACF Fields (`alify_*`) & Theme Post Meta (`_alify_*`).
 * Ensures all 67 Starter Items pre-fill ACF automatically AND saving in ACF updates live cards/singles!
 */
function alify_acf_preload_from_starter_meta( $value, $post_id, $field ) {
	if ( null !== $value && '' !== $value && false !== $value ) {
		return $value;
	}
	if ( ! is_numeric( $post_id ) || empty( $field['name'] ) ) {
		return $value;
	}

	$pid  = (int) $post_id;
	$name = $field['name'];

	// Pre-populate Project 6-Screen Lightbox fields from `_alify_proj_gallery`
	if ( preg_match( '/^alify_proj_gal_([1-6])_(url|tag|title|cap)$/', $name, $m ) ) {
		$idx     = (int) $m[1] - 1;
		$subkey  = $m[2];
		$raw_gal = get_post_meta( $pid, '_alify_proj_gallery', true );
		if ( ! empty( $raw_gal ) ) {
			$lines = preg_split( '/\r\n|\r|\n/', trim( (string) $raw_gal ) );
			if ( isset( $lines[ $idx ] ) ) {
				$parts = array_map( 'trim', explode( '|', $lines[ $idx ], 4 ) );
				$map   = array(
					'url'   => isset( $parts[0] ) ? $parts[0] : '',
					'tag'   => isset( $parts[1] ) ? $parts[1] : '',
					'title' => isset( $parts[2] ) ? $parts[2] : '',
					'cap'   => isset( $parts[3] ) ? $parts[3] : '',
				);
				if ( '' !== $map[ $subkey ] ) {
					return $map[ $subkey ];
				}
			}
		}
		return $value;
	}

	// Skip ACF `image` upload fields (they expect numeric Attachment IDs)
	if ( 'image' === $field['type'] ) {
		return $value;
	}

	// Direct 1-to-1 fallback from `_alify_*` post meta
	if ( 0 === strpos( $name, 'alify_' ) ) {
		$legacy = get_post_meta( $pid, '_' . $name, true );
		if ( '' !== $legacy && false !== $legacy ) {
			return $legacy;
		}
	}

	return $value;
}
add_filter( 'acf/load_value', 'alify_acf_preload_from_starter_meta', 10, 3 );

/**
 * Resolve ACF Image Field Value (Attachment ID, Image Array, or URL) to Clean Full URL.
 */
function alify_resolve_acf_image_url( $raw_val ) {
	if ( empty( $raw_val ) ) {
		return '';
	}
	if ( is_array( $raw_val ) && ! empty( $raw_val['url'] ) ) {
		return esc_url_raw( $raw_val['url'] );
	}
	if ( is_numeric( $raw_val ) && (int) $raw_val > 0 ) {
		$u = wp_get_attachment_image_url( (int) $raw_val, 'full' );
		return $u ? esc_url_raw( $u ) : '';
	}
	if ( is_string( $raw_val ) ) {
		return ( 0 === strpos( $raw_val, 'assets/' ) ) ? $raw_val : esc_url_raw( $raw_val );
	}
	return '';
}

/**
 * Sync ACF Saves (`acf/save_post`) Into `_alify_*` Meta & Customizer Theme Mods.
 */
function alify_sync_acf_save_to_theme( $post_id ) {
	if ( ! is_numeric( $post_id ) ) {
		return;
	}
	$pid  = (int) $post_id;
	$post = get_post( $pid );
	if ( ! $post ) {
		return;
	}

	// A. Sync Global Theme Settings -> Customizer `theme_mod`s so Customizer & ACF stay 100% unified
	if ( 'alify_settings_page' === $post->post_type ) {
		$screen_key = alify_get_screen_key_for_post( $pid );
		if ( 'global' === $screen_key && function_exists( 'get_field' ) ) {
			$mod_map = array(
				'opt_email'               => 'alify_email',
				'opt_whatsapp'            => 'alify_whatsapp',
				'opt_whatsapp_url'        => 'alify_whatsapp_url',
				'opt_hq'                  => 'alify_hq',
				'opt_header_contact_text' => 'alify_contact_btn_text',
				'opt_header_cta_text'     => 'alify_cta_text',
				'opt_footer_col1_title'   => 'alify_footer_col1_title',
				'opt_footer_col2_title'   => 'alify_footer_col2_title',
				'opt_social_linkedin'     => 'alify_linkedin',
				'opt_social_instagram'    => 'alify_instagram',
				'opt_social_twitter'      => 'alify_twitter',
				'opt_social_behance'      => 'alify_behance',
				'opt_social_dribbble'     => 'alify_dribbble',
				'opt_brand_logo_height'   => 'alify_logo_max_height',
			);
			foreach ( $mod_map as $acf_name => $mod_name ) {
				$v = get_field( $acf_name, $pid );
				if ( null !== $v && '' !== $v ) {
					set_theme_mod( $mod_name, $v );
				}
			}
		}
		return;
	}

	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	// B. Sync Direct CPT / Post / Page Text & Select & Toggle Fields -> `_alify_*`
	$direct_keys = array(
		'alify_service'     => array( 'alify_svc_notch', 'alify_svc_tag', 'alify_svc_filter', 'alify_svc_icon', 'alify_svc_points', 'alify_svc_stats', 'alify_svc_deliverables' ),
		'alify_project'     => array( 'alify_proj_num', 'alify_proj_short_title', 'alify_proj_client', 'alify_proj_sector', 'alify_proj_filter', 'alify_proj_year', 'alify_proj_sprint', 'alify_proj_metric', 'alify_proj_chips', 'alify_proj_case_slug', 'alify_proj_brief', 'alify_proj_craft' ),
		'alify_industry'    => array( 'alify_ind_code', 'alify_ind_sector_group', 'alify_ind_icon', 'alify_ind_stat', 'alify_ind_points', 'alify_ind_stats' ),
		'alify_case_study'  => array( 'alify_cs_filter', 'alify_cs_tab_index', 'alify_cs_client_short', 'alify_cs_client_kicker', 'alify_cs_sector_badge', 'alify_cs_duration_badge', 'alify_cs_benchmarks', 'alify_cs_bottleneck', 'alify_cs_architecture', 'alify_cs_kpis', 'alify_cs_chips', 'alify_cs_quote', 'alify_cs_quote_author' ),
		'alify_testimonial' => array( 'alify_testi_role', 'alify_testi_initials', 'alify_testi_rating', 'alify_testi_outcome', 'alify_testi_tag', 'alify_testi_filter' ),
		'alify_faq'         => array( 'alify_faq_pillar', 'alify_faq_show_reusable' ),
		'post'              => array( 'alify_post_badge', 'alify_post_read_time', 'alify_post_author_label' ),
		'page'              => array( 'alify_page_hero_eyebrow', 'alify_page_hero_title', 'alify_page_hero_desc', 'alify_page_hide_hero_form', 'alify_page_hide_testimonials', 'alify_page_hide_faq', 'alify_page_hide_blog_slider', 'alify_page_hide_contact' ),
	);

	if ( isset( $direct_keys[ $post->post_type ] ) ) {
		foreach ( $direct_keys[ $post->post_type ] as $f_name ) {
			$val = get_field( $f_name, $pid );
			if ( is_bool( $val ) ) {
				$val = $val ? '1' : '0';
			}
			if ( null !== $val ) {
				update_post_meta( $pid, '_' . $f_name, $val );
			}
		}
	}

	// C. Sync Paired Media Library Upload + Fallback URL Image Fields
	$img_pairs = array(
		'alify_service'    => array( 'alify_svc_bg_image_upload', 'alify_svc_bg_image', '_alify_svc_bg_image' ),
		'alify_project'    => array( 'alify_proj_bg_image_upload', 'alify_proj_bg_image', '_alify_proj_bg_image' ),
		'alify_industry'   => array( 'alify_ind_bg_image_upload', 'alify_ind_bg_image', '_alify_ind_bg_image' ),
		'alify_case_study' => array( 'alify_cs_bg_image_upload', 'alify_cs_bg_image', '_alify_cs_bg_image' ),
		'post'             => array( 'alify_post_bg_image_upload', 'alify_post_bg_image', '_alify_post_bg_image' ),
		'page'             => array( 'alify_page_hero_bg_upload', 'alify_page_hero_bg', '_alify_page_hero_bg' ),
	);

	if ( isset( $img_pairs[ $post->post_type ] ) ) {
		list( $upload_field, $url_field, $target_meta ) = $img_pairs[ $post->post_type ];
		$uploaded_url = alify_resolve_acf_image_url( get_field( $upload_field, $pid ) );
		$fallback_url = trim( (string) get_field( $url_field, $pid ) );
		$final_img    = ! empty( $uploaded_url ) ? $uploaded_url : $fallback_url;
		if ( '' !== $final_img ) {
			update_post_meta( $pid, $target_meta, $final_img );
			update_post_meta( $pid, $url_field, $final_img );
		}
	}

	// D. Sync Project 6-Screen Lightbox Gallery (`alify_proj_gal_1..6_*` -> `_alify_proj_gallery`)
	if ( 'alify_project' === $post->post_type ) {
		$gal_lines = array();
		for ( $gi = 1; $gi <= 6; $gi++ ) {
			$g_up    = alify_resolve_acf_image_url( get_field( 'alify_proj_gal_' . $gi . '_upload', $pid ) );
			$g_url   = trim( (string) get_field( 'alify_proj_gal_' . $gi . '_url', $pid ) );
			$g_final = ! empty( $g_up ) ? $g_up : $g_url;
			$g_tag   = trim( (string) get_field( 'alify_proj_gal_' . $gi . '_tag', $pid ) );
			$g_title = trim( (string) get_field( 'alify_proj_gal_' . $gi . '_title', $pid ) );
			$g_cap   = trim( (string) get_field( 'alify_proj_gal_' . $gi . '_cap', $pid ) );

			if ( ! empty( $g_final ) ) {
				update_post_meta( $pid, 'alify_proj_gal_' . $gi . '_url', $g_final );
				$gal_lines[] = implode(
					' | ',
					array(
						$g_final,
						$g_tag ? $g_tag : 'Product Showcase',
						$g_title ? $g_title : 'Interface Screen 0' . $gi,
						$g_cap ? $g_cap : 'Engineered by ALIFY Studio.',
					)
				);
			}
		}
		if ( ! empty( $gal_lines ) ) {
			update_post_meta( $pid, '_alify_proj_gallery', implode( "\n", $gal_lines ) );
		}
	}
}
add_action( 'acf/save_post', 'alify_sync_acf_save_to_theme', 20 );
