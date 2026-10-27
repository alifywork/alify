<?php
/**
 * Global Theme Header & Overlapping Navigation + Mobile Slide Drawer
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alify_route = alify_current_route_slug();
$alify_nav_map = array(
	'service-single'    => 'services',
	'industry-single'   => 'industries',
	'work-single'       => 'work',
	'case-study-single' => 'case-studies',
	'blog-single'       => 'blog',
	'testimonials'      => 'about',
	'faq'               => 'about',
	'book-call'         => 'about',
);
$alify_top_active = isset( $alify_nav_map[ $alify_route ] ) ? $alify_nav_map[ $alify_route ] : $alify_route;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="theme-color" content="#0F3473" />
	<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( alify_asset_uri( 'assets/images/favicon.svg' ) ); ?>" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
ob_start();
include ALIFY_THEME_DIR . '/template-parts/site-header.php';
$alify_header_html = ob_get_clean();

// Highlight active desktop nav item before URL transformation
if ( ! empty( $alify_top_active ) ) {
	$target_file = ( 'index' === $alify_top_active ) ? 'index.html' : $alify_top_active . '.html';
	$alify_header_html = str_replace(
		'href="' . $target_file . '" class="header-nav-link"',
		'href="' . $target_file . '" class="header-nav-link is-active"',
		$alify_header_html
	);
}
// Highlight active mobile drawer nav item
if ( ! empty( $alify_route ) ) {
	$drawer_file = ( 'index' === $alify_route ) ? 'index.html' : $alify_route . '.html';
	$alify_header_html = str_replace(
		'href="' . $drawer_file . '" class="drawer-nav-link"',
		'href="' . $drawer_file . '" class="drawer-nav-link is-active"',
		$alify_header_html
	);
}

// Apply Customizer CTA label if customized
$alify_cta_label = get_theme_mod( 'alify_cta_text', 'Start a Project' );
if ( 'Start a Project' !== $alify_cta_label && ! empty( $alify_cta_label ) ) {
	$alify_header_html = str_replace(
		'<span class="btn-start-pill">Start a Project</span>',
		'<span class="btn-start-pill">' . esc_html( $alify_cta_label ) . '</span>',
		$alify_header_html
	);
}

echo alify_transform_markup( $alify_header_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
