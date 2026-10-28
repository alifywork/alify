<?php
/**
 * Built-In SEO Meta Tags, OpenGraph, Twitter Cards & JSON-LD Schema.org Structured Data
 * Automatically yields to Yoast SEO, Rank Math, AIOSEO, or SEOPress when active!
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include public ALIFY Custom Post Types in native WordPress sitewide search (`/?s=...`).
 */
function alify_expand_site_search_post_types( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set(
			'post_type',
			array(
				'page',
				'post',
				'alify_service',
				'alify_project',
				'alify_industry',
				'alify_case_study',
			)
		);
	}
}
add_action( 'pre_get_posts', 'alify_expand_site_search_post_types' );

/**
 * Check if a dedicated third-party SEO plugin is handling meta/schema tags.
 */
function alify_has_external_seo_plugin() {
	return (
		defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' )
	);
}

/**
 * Output Meta Description, Canonical, OpenGraph, Twitter Card & JSON-LD Schema in `<head>`.
 */
function alify_output_seo_and_schema() {
	if ( alify_has_external_seo_plugin() ) {
		return;
	}

	$site_name = get_bloginfo( 'name' );
	if ( empty( $site_name ) ) {
		$site_name = 'ALIFY Studio';
	}

	$default_desc = 'ALIFY is a design-first creative digital agency engineering high-converting custom websites, UI/UX brand systems, e-commerce platforms, and digital products.';
	$meta_desc    = $default_desc;
	$og_type      = 'website';
	$og_image     = alify_asset_uri( 'assets/images/heroes/hero-home.jpg' );
	$canonical    = home_url( add_query_arg( array(), $GLOBALS['wp']->request ? '/' . ltrim( $GLOBALS['wp']->request, '/' ) . '/' : '/' ) );

	if ( is_singular() ) {
		$pid       = get_queried_object_id();
		$canonical = get_permalink( $pid );
		if ( is_singular( 'post' ) ) {
			$og_type = 'article';
		}

		$hero_desc = get_post_meta( $pid, '_alify_page_hero_desc', true );
		if ( ! empty( $hero_desc ) ) {
			$meta_desc = $hero_desc;
		} elseif ( has_excerpt( $pid ) ) {
			$meta_desc = get_the_excerpt( $pid );
		} else {
			$content_txt = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $pid ) ), 28 );
			if ( ! empty( $content_txt ) ) {
				$meta_desc = $content_txt;
			}
		}

		$thumb = get_the_post_thumbnail_url( $pid, 'full' );
		if ( ! empty( $thumb ) ) {
			$og_image = $thumb;
		}
	} elseif ( is_front_page() ) {
		$canonical = home_url( '/' );
		$lead_mod  = get_theme_mod( 'alify_home_lead', '' );
		if ( ! empty( $lead_mod ) ) {
			$meta_desc = $lead_mod;
		}
	}

	$doc_title = wp_get_document_title();

	echo '<meta name="description" content="' . esc_attr( $meta_desc ) . '" />' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $doc_title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $meta_desc ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical ) . '" />' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:site" content="@alifywork" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $doc_title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $meta_desc ) . '" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '" />' . "\n";

	// Build JSON-LD @graph
	$email    = get_theme_mod( 'alify_email', 'info@alify.site' );
	$whatsapp = get_theme_mod( 'alify_whatsapp', '+92 301 9328094' );
	$same_as  = array_values(
		array_filter(
			array(
				get_theme_mod( 'alify_twitter', 'https://x.com/alifywork' ),
				get_theme_mod( 'alify_instagram', 'https://www.instagram.com/alifywork/' ),
				get_theme_mod( 'alify_facebook', 'https://www.facebook.com/alifywork/' ),
				get_theme_mod( 'alify_tiktok', 'https://www.tiktok.com/@alifywork' ),
				get_theme_mod( 'alify_linkedin', '' ),
				get_theme_mod( 'alify_behance', '' ),
				get_theme_mod( 'alify_dribbble', '' ),
			)
		)
	);

	$graph = array(
		array(
			'@type'       => 'ProfessionalService',
			'@id'         => home_url( '/#organization' ),
			'name'        => $site_name,
			'url'         => home_url( '/' ),
			'email'       => $email,
			'telephone'   => $whatsapp,
			'image'       => $og_image,
			'description' => $default_desc,
			'sameAs'      => $same_as,
		),
		array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => $site_name,
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		),
	);

	if ( is_singular( 'post' ) ) {
		$pid     = get_queried_object_id();
		$graph[] = array(
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title( $pid ),
			'description'   => $meta_desc,
			'image'         => $og_image,
			'datePublished' => get_the_date( 'c', $pid ),
			'dateModified'  => get_the_modified_date( 'c', $pid ),
			'mainEntityOfPage' => $canonical,
			'publisher'     => array( '@id' => home_url( '/#organization' ) ),
		);
	}

	if ( 'faq' === alify_current_route_slug() ) {
		$faq_posts = get_posts(
			array(
				'post_type'      => 'alify_faq',
				'posts_per_page' => 25,
				'post_status'    => 'publish',
			)
		);
		if ( ! empty( $faq_posts ) ) {
			$qa_entities = array();
			foreach ( $faq_posts as $fp ) {
				$qa_entities[] = array(
					'@type'          => 'Question',
					'name'           => get_the_title( $fp->ID ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $fp->post_content ),
					),
				);
			}
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => $qa_entities,
			);
		}
	}

	$schema_payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema_payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'alify_output_seo_and_schema', 5 );
