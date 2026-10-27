<?php
/**
 * Search Results Template (`search.php`)
 * Searches across Pages, Blog Posts, Services, Projects, Industries & Case Studies in ALIFY Design.
 *
 * @package ALIFY
 */

get_header();

global $wp_query;
$alify_query_text  = get_search_query();
$alify_found_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$alify_hero_bg     = alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' );

$alify_type_labels = array(
	'page'              => __( 'Studio Page', 'alify' ),
	'post'              => __( 'Journal Article', 'alify' ),
	'alify_service'     => __( 'Service', 'alify' ),
	'alify_project'     => __( 'Client Project', 'alify' ),
	'alify_industry'    => __( 'Industry Sector', 'alify' ),
	'alify_case_study'  => __( 'Case Study', 'alify' ),
	'alify_faq'         => __( 'FAQ Answer', 'alify' ),
);
?>
<main>
	<section class="inner-hero" aria-label="<?php esc_attr_e( 'Search Results', 'alify' ); ?>">
		<div id="innerHeroBg" class="hero-bg-media" style="background-image: url('<?php echo esc_url( $alify_hero_bg ); ?>');" aria-hidden="true"></div>
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
						<span class="breadcrumb-current" aria-current="page"><?php esc_html_e( 'Search Results', 'alify' ); ?></span>
					</li>
				</ol>
			</nav>

			<div class="row inner-hero-row">
				<div class="col-12 col-lg-9">
					<div class="inner-hero-eyebrow">
						<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
						<span>
							<?php
							printf(
								/* translators: %d: number of matching search results */
								esc_html__( '%d Matching Results Across ALIFY Studio', 'alify' ),
								$alify_found_count
							);
							?>
						</span>
					</div>

					<h1 class="inner-hero-title">
						<?php if ( ! empty( $alify_query_text ) ) : ?>
							<?php esc_html_e( 'Results for', 'alify' ); ?>
							<span class="inner-hero-title-accent">&ldquo;<?php echo esc_html( $alify_query_text ); ?>&rdquo;</span>
						<?php else : ?>
							<?php esc_html_e( 'Explore', 'alify' ); ?>
							<span class="inner-hero-title-accent"><?php esc_html_e( 'ALIFY Studio', 'alify' ); ?></span>
						<?php endif; ?>
					</h1>

					<div class="alify-hero-search-wrap">
						<?php get_search_form(); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="alify-search-results-section" aria-label="<?php esc_attr_e( 'Matching Content', 'alify' ); ?>">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="row blog-archive-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						$pid        = get_the_ID();
						$ptype      = get_post_type( $pid );
						$type_badge = isset( $alify_type_labels[ $ptype ] ) ? $alify_type_labels[ $ptype ] : ucfirst( (string) $ptype );
						$thumb_url  = get_the_post_thumbnail_url( $pid, 'large' );
						if ( empty( $thumb_url ) && function_exists( 'alify_get_cpt_bg_url' ) ) {
							$meta_map = array(
								'alify_service'    => '_alify_svc_bg_image',
								'alify_project'    => '_alify_proj_bg_image',
								'alify_industry'   => '_alify_ind_bg_image',
								'alify_case_study' => '_alify_cs_bg_image',
								'post'             => '_alify_post_bg_image',
								'page'             => '_alify_page_hero_bg',
							);
							$mkey      = isset( $meta_map[ $ptype ] ) ? $meta_map[ $ptype ] : '_alify_post_bg_image';
							$thumb_url = alify_get_cpt_bg_url( $pid, $mkey, 'assets/images/blog/blog-design-systems.jpg' );
						}
						if ( empty( $thumb_url ) ) {
							$thumb_url = alify_asset_uri( 'assets/images/blog/blog-design-systems.jpg' );
						}
						$excerpt = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( get_the_content() ), 22 );
						?>
						<div class="col-12 col-md-6 col-lg-4 blog-grid-col">
							<article <?php post_class( 'blog-card' ); ?>>
								<a href="<?php the_permalink(); ?>" class="blog-card-media">
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
									<span class="blog-card-category"><?php echo esc_html( $type_badge ); ?></span>
								</a>
								<div class="blog-card-body">
									<div class="blog-card-meta">
										<span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
									</div>
									<h2 class="blog-card-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h2>
									<p class="blog-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
									<div class="blog-card-footer">
										<a href="<?php the_permalink(); ?>" class="blog-read-more">
											<span><?php esc_html_e( 'Explore Page', 'alify' ); ?></span>
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
			<?php else : ?>
				<div class="alify-search-empty-card">
					<span class="alify-search-empty-icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></span>
					<h2 class="alify-search-empty-title"><?php esc_html_e( 'No exact matches found for that search.', 'alify' ); ?></h2>
					<p class="alify-search-empty-desc">
						<?php esc_html_e( 'Try searching for a capability like "Web Design", "Design Systems", "E-Commerce", "SEO", or explore our core studio hubs below.', 'alify' ); ?>
					</p>
					<div class="alify-search-quick-links">
						<a href="<?php echo esc_url( alify_page_url( 'services' ) ); ?>" class="btn-start-project">
							<span class="btn-start-pill"><?php esc_html_e( 'Explore Services', 'alify' ); ?></span>
							<span class="btn-start-circle" aria-hidden="true"><i class="fa-solid fa-arrow-up btn-start-arrow"></i></span>
						</a>
						<a href="<?php echo esc_url( alify_page_url( 'work' ) ); ?>" class="alify-search-pill-link"><?php esc_html_e( 'Client Work', 'alify' ); ?></a>
						<a href="<?php echo esc_url( alify_page_url( 'faq' ) ); ?>" class="alify-search-pill-link"><?php esc_html_e( '20-FAQ Hub', 'alify' ); ?></a>
						<a href="<?php echo esc_url( alify_page_url( 'book-call' ) ); ?>" class="alify-search-pill-link"><?php esc_html_e( 'Book a Strategy Call', 'alify' ); ?></a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php alify_render_partial( 'template-parts/contact-reusable.php' ); ?>
</main>
<?php
get_footer();
