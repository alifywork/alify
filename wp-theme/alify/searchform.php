<?php
/**
 * Custom ALIFY Search Form Template (`searchform.php`)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alify_unique_id = wp_unique_id( 'alify-search-input-' );
?>
<form role="search" method="get" class="alify-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $alify_unique_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Search ALIFY Studio:', 'alify' ); ?>
	</label>
	<div class="alify-search-bar">
		<span class="alify-search-icon" aria-hidden="true">
			<i class="fa-solid fa-magnifying-glass"></i>
		</span>
		<input
			type="search"
			id="<?php echo esc_attr( $alify_unique_id ); ?>"
			class="alify-search-field"
			placeholder="<?php esc_attr_e( 'Search services, projects, case studies, FAQs or articles...', 'alify' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
		/>
		<button type="submit" class="alify-search-submit">
			<span><?php esc_html_e( 'Search', 'alify' ); ?></span>
			<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
		</button>
	</div>
</form>
