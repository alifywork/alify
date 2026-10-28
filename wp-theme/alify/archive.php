<?php
/**
 * Archive Template (`archive.php`)
 * Routes Custom Post Type & FAQ Pillar Taxonomy archives to their authentic ALIFY hub templates,
 * and falls back to `home.php` for Blog Category, Tag, Author, and Date archives.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alify_cpt_archive_map = array(
	'alify_service'     => 'services',
	'alify_project'     => 'work',
	'alify_industry'    => 'industries',
	'alify_case_study'  => 'case-studies',
	'alify_testimonial' => 'testimonials',
	'alify_faq'         => 'faq',
);

foreach ( $alify_cpt_archive_map as $alify_pt => $alify_partial_slug ) {
	if ( is_post_type_archive( $alify_pt ) ) {
		get_header();
		alify_render_partial( 'template-parts/pages/' . $alify_partial_slug . '.php' );
		get_footer();
		return;
	}
}

if ( is_tax( 'faq_pillar' ) ) {
	get_header();
	alify_render_partial( 'template-parts/pages/faq.php' );
	get_footer();
	return;
}

require ALIFY_THEME_DIR . '/home.php';
