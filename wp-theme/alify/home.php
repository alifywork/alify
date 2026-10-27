<?php
/**
 * WordPress Blog Posts Index Template (`home.php`)
 * Dynamically renders published WordPress posts in the ALIFY 3-column `.blog-card` grid,
 * with automatic fallback to the 6-post ALIFY starter showcase when no posts exist yet.
 *
 * @package ALIFY
 */

get_header();

if ( have_posts() ) :
	$hero_bg = alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' );
	?>
	<main>
		<section class="inner-page-hero inner-page-hero--compact" aria-label="ALIFY Journal & Insights">
			<div class="inner-hero-bg" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>')"></div>
			<div class="inner-hero-overlay"></div>
			<div class="inner-hero-grid" aria-hidden="true"></div>
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-8">
						<nav class="inner-breadcrumb" aria-label="Breadcrumb">
							<ol class="inner-breadcrumb-list">
								<li class="inner-breadcrumb-item">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span></a>
								</li>
								<li class="inner-breadcrumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
								<li class="inner-breadcrumb-item is-current" aria-current="page">Blog &amp; Insights</li>
							</ol>
						</nav>
						<div class="inner-hero-eyebrow">
							<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
							<span>Engineering, UX &amp; Growth Playbooks</span>
						</div>
						<h1 class="inner-hero-title">Insights Built From <span class="text-highlight">Real Client Launches.</span></h1>
						<p class="inner-hero-desc">Deep-dive articles on design systems, GSAP motion performance, technical SEO, and conversion engineering from the ALIFY studio.</p>
					</div>
				</div>
			</div>
		</section>

		<section class="blog-archive-section" aria-label="Published Articles">
			<div class="container">
				<div class="row blog-archive-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
						if ( empty( $thumb_url ) ) {
							$thumb_url = alify_asset_uri( 'assets/images/blog/blog-design-systems.jpg' );
						}
						$cats     = get_the_category();
						$cat_name = ! empty( $cats ) ? $cats[0]->name : 'Engineering';
						?>
						<div class="col-12 col-md-6 col-lg-4 blog-grid-col" data-category="<?php echo esc_attr( strtolower( $cat_name ) ); ?>">
							<article <?php post_class( 'blog-card' ); ?>>
								<a href="<?php the_permalink(); ?>" class="blog-card-media">
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
									<span class="blog-card-category"><?php echo esc_html( $cat_name ); ?></span>
								</a>
								<div class="blog-card-body">
									<div class="blog-card-meta">
										<span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
										<span class="blog-card-meta-dot" aria-hidden="true"></span>
										<span><i class="fa-regular fa-user" aria-hidden="true"></i> <?php the_author(); ?></span>
									</div>
									<h2 class="blog-card-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h2>
									<p class="blog-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
									<div class="blog-card-footer">
										<a href="<?php the_permalink(); ?>" class="blog-read-more">
											<span>Read More</span>
											<span class="blog-read-more-icon" aria-hidden="true"><i class="fa-solid fa-arrow-up"></i></span>
										</a>
									</div>
								</div>
							</article>
						</div>
					<?php endwhile; ?>
				</div>

				<div class="blog-wp-pagination">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => '&larr; Previous',
							'next_text' => 'Next &rarr;',
						)
					);
					?>
				</div>
			</div>
		</section>
	</main>
	<?php
else :
	alify_render_partial( 'template-parts/pages/blog.php' );
endif;

get_footer();
