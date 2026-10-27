<?php
/**
 * Default WordPress Page Template (`page.php`)
 * Automatically maps matching ALIFY blueprint slugs (`about`, `services`, `work`, `pricing`, `faq`, `book-call`, etc.)
 * AND renders any new custom Gutenberg/Classic page created in WP Admin with ALIFY Inner Hero + Reusable Sections.
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
	$hero_bg = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	if ( empty( $hero_bg ) ) {
		$hero_bg = alify_asset_uri( 'assets/images/heroes/hero-about.jpg' );
	}
	?>
	<main>
		<section class="inner-page-hero inner-page-hero--compact" aria-label="<?php the_title_attribute(); ?>">
			<div class="inner-hero-bg" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>')"></div>
			<div class="inner-hero-overlay"></div>
			<div class="inner-hero-grid" aria-hidden="true"></div>
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-9">
						<nav class="inner-breadcrumb" aria-label="Breadcrumb">
							<ol class="inner-breadcrumb-list">
								<li class="inner-breadcrumb-item">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span></a>
								</li>
								<li class="inner-breadcrumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
								<li class="inner-breadcrumb-item is-current" aria-current="page"><?php the_title(); ?></li>
							</ol>
						</nav>
						<div class="inner-hero-eyebrow">
							<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
							<span>ALIFY Digital Agency</span>
						</div>
						<h1 class="inner-hero-title"><?php the_title(); ?></h1>
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
		alify_render_partial( 'template-parts/faq-reusable.php' );
		alify_render_partial( 'template-parts/blog-slider.php' );
		alify_render_partial( 'template-parts/contact-reusable.php' );
		?>
	</main>
	<?php
endwhile;

get_footer();
