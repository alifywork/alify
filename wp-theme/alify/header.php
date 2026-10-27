<?php
/**
 * Global Theme Header (`header.php`)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
<?php
wp_body_open();
get_template_part( 'template-parts/site', 'header' );
?>
