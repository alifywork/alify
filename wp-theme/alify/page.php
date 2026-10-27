<?php
/**
 * Default WordPress Page Template (`page.php`)
 * Automatically maps matching ALIFY blueprint slugs (`about`, `services`, `work`, `pricing`, `faq`, `book-call`, etc.)
 * AND renders any new custom Gutenberg/Classic page created in WP Admin with authentic ALIFY `.inner-hero` + Reusable Sections.
 *
 * @package ALIFY
 */

$alify_post_obj  = get_queried_object();
$alify_page_slug = ( $alify_post_obj && ! empty( $alify_post_obj->post_name ) ) ? $alify_post_obj->post_name : '';
$alify_part_file = ALIFY_THEME_DIR . '/template-parts/pages/' . $alify_page_slug . '.php';

if ( ! empty( $alify_page_slug ) && file_exists( $alify_part_file ) ) {
	get_header();
	alify_render_partial( 'template-parts/pages/' . $alify_page_slug . '.php' );
	if ( 'work-single' === $alify_page_slug ) {
		alify_render_partial( 'template-parts/work-lightbox.php' );
	}
	get_footer();
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
	$pid     = get_the_ID();
	$hero_bg = get_post_meta( $pid, '_alify_page_hero_bg', true );
	if ( empty( $hero_bg ) ) {
		$hero_bg = get_the_post_thumbnail_url( $pid, 'full' );
	}
	if ( empty( $hero_bg ) ) {
		$hero_bg = alify_asset_uri( 'assets/images/heroes/hero-about.jpg' );
	}
	$eyebrow = get_post_meta( $pid, '_alify_page_hero_eyebrow', true );
	$eyebrow = $eyebrow ? $eyebrow : 'ALIFY Digital Studio';
	$h_title = get_post_meta( $pid, '_alify_page_hero_title', true );
	$h_desc  = get_post_meta( $pid, '_alify_page_hero_desc', true );
	if ( empty( $h_desc ) && has_excerpt( $pid ) ) {
		$h_desc = get_the_excerpt( $pid );
	}
	?>
	<main>
		<section class="inner-hero" aria-label="<?php the_title_attribute(); ?>">
			<div id="innerHeroBg" class="hero-bg-media" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>');" aria-hidden="true"></div>
			<span class="inner-hero-watermark" aria-hidden="true">△</span>
			<div class="container">
				<nav class="breadcrumb-nav" aria-label="<?php esc_attr_e( 'Breadcrumb', 'alify' ); ?>">
					<ol class="breadcrumb-list">
						<li class="breadcrumb-item-entry">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="breadcrumb-link">
								<i class="fa-solid fa-house breadcrumb-home-icon" aria-hidden="true"></i>
								<span><?php esc_html_e( 'Home', 'alify' ); ?></span>
							</a>
						</li>
						<li class="breadcrumb-item-entry" aria-hidden="true">
							<i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
						</li>
						<li class="breadcrumb-item-entry">
							<span class="breadcrumb-current" aria-current="page"><?php the_title(); ?></span>
						</li>
					</ol>
				</nav>

				<div class="row inner-hero-row">
					<div class="col-12 col-lg-9">
						<div class="inner-hero-eyebrow">
							<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
							<span><?php echo esc_html( $eyebrow ); ?></span>
						</div>

						<h1 class="inner-hero-title">
							<?php echo ! empty( $h_title ) ? wp_kses_post( $h_title ) : esc_html( get_the_title() ); ?>
						</h1>

						<?php if ( ! empty( $h_desc ) ) : ?>
							<p class="inner-hero-desc"><?php echo esc_html( $h_desc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<section class="wp-custom-page-section">
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-10">
						<article <?php post_class( 'wp-page-entry-content' ); ?>>
							<?php the_content(); ?>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php
		if ( '1' !== (string) get_post_meta( $pid, '_alify_page_hide_faq', true ) ) {
			alify_render_partial( 'template-parts/faq-reusable.php' );
		}
		if ( '1' !== (string) get_post_meta( $pid, '_alify_page_hide_blog_slider', true ) ) {
			alify_render_partial( 'template-parts/blog-slider.php' );
		}
		if ( '1' !== (string) get_post_meta( $pid, '_alify_page_hide_contact', true ) ) {
			alify_render_partial( 'template-parts/contact-reusable.php' );
		}
		?>
	</main>
	<?php
endwhile;

get_footer();
