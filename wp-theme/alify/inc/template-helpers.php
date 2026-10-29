<?php
/**
 * URL, Asset & Markup Rewriting Helpers for ALIFY WordPress Theme
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get full URI to a theme asset under /assets/.
 */
function alify_asset_uri( $rel_path ) {
	$rel_path = ltrim( $rel_path, '/' );
	return esc_url( ALIFY_THEME_URI . '/' . $rel_path );
}

/**
 * Resolve any ALIFY blueprint page slug to its WordPress permalink.
 */
function alify_page_url( $slug = '', $query_args = array() ) {
	if ( empty( $slug ) || 'index' === $slug || 'home' === $slug ) {
		$url = home_url( '/' );
	} else {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page->ID ) : home_url( '/' . trim( $slug, '/' ) . '/' );
	}

	if ( ! empty( $query_args ) ) {
		$url = add_query_arg( $query_args, $url );
	}
	return $url;
}

/**
 * Resolve Single CPT / Blog Permalink When Item Exists in DB, With Graceful Fallback to Blueprint Page URL.
 */
function alify_cpt_or_page_url( $page_slug, $query_args = array() ) {
	$cpt_param_map = array(
		'service-single'    => array( 'service', 'alify_service' ),
		'work-single'       => array( 'project', 'alify_project' ),
		'industry-single'   => array( 'industry', 'alify_industry' ),
		'case-study-single' => array( 'case', 'alify_case_study' ),
		'blog-single'       => array( 'post', 'post' ),
	);

	if ( isset( $cpt_param_map[ $page_slug ] ) ) {
		list( $param_key, $pt ) = $cpt_param_map[ $page_slug ];
		if ( ! empty( $query_args[ $param_key ] ) && post_type_exists( $pt ) ) {
			$item_slug = sanitize_title( (string) $query_args[ $param_key ] );
			$target    = get_page_by_path( $item_slug, OBJECT, $pt );
			if ( $target && 'publish' === $target->post_status ) {
				$extra = $query_args;
				unset( $extra[ $param_key ] );
				$plink = get_permalink( $target->ID );
				return ! empty( $extra ) ? add_query_arg( $extra, $plink ) : $plink;
			}
		}
	}

	return alify_page_url( $page_slug, $query_args );
}

/**
 * Detect current ALIFY route slug for active header/drawer menu highlighting.
 */
function alify_current_route_slug() {
	if ( is_front_page() || is_page_template( 'page-templates/template-home.php' ) ) {
		return 'index';
	}
	if ( is_404() ) {
		return '404';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( is_tax( 'faq_pillar' ) || is_post_type_archive( 'alify_faq' ) ) {
		return 'faq';
	}
	if ( is_post_type_archive( 'alify_service' ) ) {
		return 'services';
	}
	if ( is_post_type_archive( 'alify_project' ) ) {
		return 'work';
	}
	if ( is_post_type_archive( 'alify_industry' ) ) {
		return 'industries';
	}
	if ( is_post_type_archive( 'alify_case_study' ) ) {
		return 'case-studies';
	}
	if ( is_post_type_archive( 'alify_testimonial' ) ) {
		return 'testimonials';
	}
	if ( is_home() || ( is_archive() && ! is_post_type_archive() ) ) {
		return 'blog';
	}
	if ( is_singular( 'post' ) ) {
		return 'blog-single';
	}
	if ( is_singular( 'alify_service' ) ) {
		return 'service-single';
	}
	if ( is_singular( 'alify_project' ) ) {
		return 'work-single';
	}
	if ( is_singular( 'alify_industry' ) ) {
		return 'industry-single';
	}
	if ( is_singular( 'alify_case_study' ) ) {
		return 'case-study-single';
	}
	if ( is_page() ) {
		$post = get_post();
		if ( $post ) {
			return $post->post_name;
		}
	}
	return '';
}

/**
 * Rewrite static template HTML paths (`assets/...` & `*.html`) + Alify Custom Field & Customizer values dynamically.
 */
function alify_transform_markup( $html, $part_slug = '' ) {
	if ( function_exists( 'alify_apply_cf_options_overrides' ) ) {
		$html = alify_apply_cf_options_overrides( $html, $part_slug );
	}
	if ( function_exists( 'alify_apply_per_page_cf_overrides' ) ) {
		$html = alify_apply_per_page_cf_overrides( $html, $part_slug );
	}
	if ( function_exists( 'alify_apply_page_and_customizer_overrides' ) ) {
		$html = alify_apply_page_and_customizer_overrides( $html );
	}
	if ( function_exists( 'alify_apply_dynamic_form_templates' ) ) {
		$html = alify_apply_dynamic_form_templates( $html );
	}
	if ( function_exists( 'alify_inject_dynamic_cpts' ) ) {
		$html = alify_inject_dynamic_cpts( $html );
	}

	$theme_assets_uri = untrailingslashit( ALIFY_THEME_URI ) . '/assets/';

	// 1. Rewrite src="assets/...", href="assets/...", url('assets/...'), url("assets/...")
	$html = preg_replace(
		'/(src|href)=(["\'])assets\//i',
		'$1=$2' . esc_url( $theme_assets_uri ),
		$html
	);
	$html = preg_replace(
		'/url\((["\']?)assets\//i',
		'url($1' . esc_url( $theme_assets_uri ),
		$html
	);

	// 2. Rewrite internal .html links (including query strings & hash anchors) to WP permalinks
	$html = preg_replace_callback(
		'/href=(["\'])([a-z0-9-]+)\.html(\?[^"\'#]*)?(#[^"\']*)?\1/i',
		function ( $m ) {
			$quote = $m[1];
			$slug  = strtolower( $m[2] );
			$qs    = isset( $m[3] ) ? $m[3] : '';
			$hash  = isset( $m[4] ) ? $m[4] : '';

			$query_args = array();
			if ( ! empty( $qs ) ) {
				parse_str( ltrim( $qs, '?' ), $query_args );
			}
			$url = alify_cpt_or_page_url( $slug, $query_args ) . $hash;
			return 'href=' . $quote . esc_url( $url ) . $quote;
		},
		$html
	);

	// 3. Apply live Alify Custom Field `site` Options + Customizer Email, WhatsApp, Hours & Headquarters
	$email    = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'site', 'opt_email', get_theme_mod( 'alify_email', 'info@alify.site' ) ) : get_theme_mod( 'alify_email', 'info@alify.site' );
	$whatsapp = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'site', 'opt_whatsapp', get_theme_mod( 'alify_whatsapp', '+92 301 9328094' ) ) : get_theme_mod( 'alify_whatsapp', '+92 301 9328094' );
	$wa_url   = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'site', 'opt_whatsapp_url', get_theme_mod( 'alify_whatsapp_url', 'https://wa.me/923019328094' ) ) : get_theme_mod( 'alify_whatsapp_url', 'https://wa.me/923019328094' );
	$hq       = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'site', 'opt_hq', get_theme_mod( 'alify_hq', 'Karachi, PK — Global Delivery' ) ) : get_theme_mod( 'alify_hq', 'Karachi, PK — Global Delivery' );
	$hours    = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'site', 'opt_hours', 'Mon – Fri, 9:00 AM – 7:00 PM PKT' ) : 'Mon – Fri, 9:00 AM – 7:00 PM PKT';

	$html = str_replace( 'mailto:info@alify.site', 'mailto:' . sanitize_email( $email ), $html );
	$html = str_replace( 'info@alify.site', esc_html( $email ), $html );
	$html = str_replace( 'https://wa.me/923019328094', esc_url( $wa_url ), $html );
	$html = str_replace( '+92 301 9328094', esc_html( $whatsapp ), $html );
	$html = str_replace( 'Karachi, PK — Global Delivery', esc_html( $hq ), $html );
	$html = str_replace( 'Mon – Fri, 9:00 AM – 7:00 PM PKT', esc_html( $hours ), $html );

	return $html;
}

/**
 * Echo transformed theme partial safely.
 */
function alify_render_partial( $rel_file ) {
	$full = ALIFY_THEME_DIR . '/' . ltrim( $rel_file, '/' );
	if ( file_exists( $full ) ) {
		$part_slug = basename( $rel_file, '.php' );
		ob_start();
		include $full;
		$out = ob_get_clean();
		echo alify_transform_markup( $out, $part_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Render Brand Logo (Uses Alify Custom Field Logo or WordPress Custom Logo if uploaded, else signature △LIFY logo).
 */
function alify_render_brand_logo( $link_class = 'brand-logo', $mark_class = 'brand-logo-mark' ) {
	$home_url    = home_url( '/' );
	$cf_logo_src = function_exists( 'alify_cf_opt_img' ) ? alify_cf_opt_img( 'site', 'opt_brand_logo', '' ) : '';

	if ( ! empty( $cf_logo_src ) ) {
		echo '<a href="' . esc_url( $home_url ) . '" class="' . esc_attr( $link_class . ' has-custom-logo-img' ) . '" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
		echo '<img src="' . esc_url( $cf_logo_src ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="alify-custom-logo-img" />';
		echo '</a>';
		return;
	}

	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		$logo_id  = (int) get_theme_mod( 'custom_logo' );
		$logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( ! empty( $logo_src ) ) {
			echo '<a href="' . esc_url( $home_url ) . '" class="' . esc_attr( $link_class . ' has-custom-logo-img' ) . '" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
			echo '<img src="' . esc_url( $logo_src ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="alify-custom-logo-img" />';
			echo '</a>';
			return;
		}
	}

	echo '<a href="' . esc_url( $home_url ) . '" class="' . esc_attr( $link_class ) . '" aria-label="ALIFY Home">';
	echo '<span class="' . esc_attr( $mark_class ) . '" aria-hidden="true">△</span>LIFY';
	echo '</a>';
}
