<?php
/**
 * Global Theme Footer
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ob_start();
include ALIFY_THEME_DIR . '/template-parts/site-footer.php';
$alify_footer_html = ob_get_clean();

// Apply Customizer social URLs
$alify_socials = array(
	'https://linkedin.com'  => get_theme_mod( 'alify_linkedin', 'https://linkedin.com' ),
	'https://instagram.com' => get_theme_mod( 'alify_instagram', 'https://instagram.com' ),
	'https://x.com'         => get_theme_mod( 'alify_twitter', 'https://x.com' ),
	'https://behance.net'   => get_theme_mod( 'alify_behance', 'https://behance.net' ),
	'https://dribbble.com'  => get_theme_mod( 'alify_dribbble', 'https://dribbble.com' ),
);
foreach ( $alify_socials as $default_url => $custom_url ) {
	if ( ! empty( $custom_url ) && $default_url !== $custom_url ) {
		$alify_footer_html = str_replace( 'href="' . $default_url . '"', 'href="' . esc_url( $custom_url ) . '"', $alify_footer_html );
	}
}

echo alify_transform_markup( $alify_footer_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
wp_footer();
?>
</body>
</html>
