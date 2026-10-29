<?php
/**
 * WordPress Blog Posts Index & Archive Template (`home.php`)
 * Matches `blog.html` `.inner-hero` + `.blog-archive-section` + `.blog-card` 100%,
 * with automatic fallback to the 6-post ALIFY starter showcase when no posts exist yet.
 *
 * @package ALIFY
 */

get_header();

if ( have_posts() ) :
	$hero_bg       = function_exists( 'alify_cf_opt_img' ) ? alify_cf_opt_img( 'alify_archives', 'arch_blog_hero_bg', alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' ) ) : alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' );
	$archive_title = is_archive() ? get_the_archive_title() : '';
	$blog_eyebrow  = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_archives', 'arch_blog_hero_eyebrow', 'Studio Journal' ) : 'Studio Journal';
	$blog_h1_opt   = function_exists( 'alify_cf_opt_saved' ) ? alify_cf_opt_saved( 'alify_archives', 'arch_blog_hero_title' ) : '';
	$blog_desc_opt = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_archives', 'arch_blog_hero_desc', 'Actionable guides, engineering deep dives, and brand strategy insights written by the product designers and developers at ALIFY.' ) : 'Actionable guides, engineering deep dives, and brand strategy insights written by the product designers and developers at ALIFY.';
	$blog_c_metric = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_archives', 'arch_blog_card_metric', 'Weekly' ) : 'Weekly';
	$blog_c_label  = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_archives', 'arch_blog_card_label', 'Fresh perspectives on modern web craft, GSAP motion systems, and scalable UX.' ) : 'Fresh perspectives on modern web craft, GSAP motion systems, and scalable UX.';
	$blog_c_cta    = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_archives', 'arch_blog_card_cta', 'Work With Our Team' ) : 'Work With Our Team';
	ob_start();
	?>
	<main>
		<section class="inner-hero" aria-label="<?php esc_attr_e( 'Blog Hero', 'alify' ); ?>">
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
							<span class="breadcrumb-current" aria-current="page">
								<?php echo ! empty( $archive_title ) ? wp_kses_post( $archive_title ) : esc_html__( 'Blog', 'alify' ); ?>
							</span>
						</li>
					</ol>
				</nav>

				<div class="row inner-hero-row">
					<div class="col-12 col-lg-8">
						<div class="inner-hero-eyebrow">
							<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
							<span><?php echo esc_html( $blog_eyebrow ); ?></span>
						</div>

						<h1 class="inner-hero-title" data-alify-copy="copy_blog_001">
							<?php if ( ! empty( $archive_title ) ) : ?>
								<?php echo wp_kses_post( $archive_title ); ?>
							<?php elseif ( ! empty( $blog_h1_opt ) ) : ?>
								<?php echo wp_kses( $blog_h1_opt, array( 'span' => array( 'class' => array() ), 'br' => array() ) ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Perspectives on', 'alify' ); ?>
								<span class="inner-hero-title-accent"><?php esc_html_e( 'Design, Code', 'alify' ); ?></span>
								<?php esc_html_e( '& Digital Growth.', 'alify' ); ?>
							<?php endif; ?>
						</h1>

						<p class="inner-hero-desc" data-alify-copy="copy_blog_002">
							<?php echo esc_html( $blog_desc_opt ); ?>
						</p>

						<div class="inner-hero-tags">
							<span class="inner-hero-tag">
								<i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
								<span><?php esc_html_e( 'UI/UX Systems', 'alify' ); ?></span>
							</span>
							<span class="inner-hero-tag">
								<i class="fa-solid fa-code" aria-hidden="true"></i>
								<span><?php esc_html_e( 'Frontend Architecture', 'alify' ); ?></span>
							</span>
							<span class="inner-hero-tag">
								<i class="fa-solid fa-chart-line" aria-hidden="true"></i>
								<span><?php esc_html_e( 'Conversion & SEO', 'alify' ); ?></span>
							</span>
						</div>
					</div>

					<div class="col-12 col-lg-4">
						<div class="inner-hero-card">
							<div class="inner-hero-card-top">
								<span class="inner-hero-card-badge">
									<i class="fa-solid fa-bolt" aria-hidden="true"></i>
								</span>
								<span class="inner-hero-card-code">△ JOURNAL 04</span>
							</div>
							<div class="inner-hero-card-metric"><?php echo esc_html( $blog_c_metric ); ?></div>
							<p class="inner-hero-card-label"><?php echo esc_html( $blog_c_label ); ?></p>
							<a href="<?php echo esc_url( alify_page_url( 'start-project' ) ); ?>" class="inner-hero-card-cta">
								<span><?php echo esc_html( $blog_c_cta ); ?></span>
								<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="blog-archive-section" aria-label="<?php esc_attr_e( 'All Blog Articles', 'alify' ); ?>">
			<div class="container">
				<div class="row blog-grid-row">
					<?php
					while ( have_posts() ) :
						the_post();
						$pid       = get_the_ID();
						$thumb_url = get_the_post_thumbnail_url( $pid, 'large' );
						if ( empty( $thumb_url ) && function_exists( 'alify_get_cpt_bg_url' ) ) {
							$thumb_url = alify_get_cpt_bg_url( $pid, '_alify_post_bg_image', 'assets/images/blog/blog-design-systems.jpg' );
						}
						if ( empty( $thumb_url ) ) {
							$thumb_url = alify_asset_uri( 'assets/images/blog/blog-design-systems.jpg' );
						}
						$cats      = get_the_category( $pid );
						$cat_name  = ! empty( $cats ) ? $cats[0]->name : 'Engineering';
						$words     = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $pid ) ) );
						$read_mins = max( 3, (int) ceil( $words / 200 ) );
						$excerpt   = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $pid ) ), 22 );
						?>
						<div class="col-12 col-md-6 col-lg-4 blog-grid-col" data-category="<?php echo esc_attr( $cat_name ); ?>">
							<article <?php post_class( 'blog-card' ); ?>>
								<a href="<?php the_permalink(); ?>" class="blog-card-media">
									<span class="blog-card-category"><?php echo esc_html( $cat_name ); ?></span>
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img" loading="lazy" />
								</a>
								<div class="blog-card-body">
									<div class="blog-card-meta">
										<span class="blog-card-meta-item">
											<i class="fa-solid fa-calendar" aria-hidden="true"></i>
											<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
										</span>
										<span class="blog-card-meta-item">
											<i class="fa-solid fa-clock" aria-hidden="true"></i>
											<span><?php echo esc_html( $read_mins . ' Min Read' ); ?></span>
										</span>
									</div>
									<h2 class="blog-card-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h2>
									<p class="blog-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
									<div class="blog-card-footer">
										<a href="<?php the_permalink(); ?>" class="blog-read-more">
											<span class="blog-read-more-pill"><?php esc_html_e( 'Read More', 'alify' ); ?></span>
											<span class="blog-read-more-circle" aria-hidden="true">
												<i class="fa-solid fa-arrow-up blog-read-more-arrow"></i>
											</span>
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
	$blog_copy_page_id = (int) get_option( 'page_for_posts' );
	$blog_main_markup = ob_get_clean();
	echo function_exists( 'alify_apply_generated_page_copy_fields' )
		? alify_apply_generated_page_copy_fields( $blog_main_markup, $blog_copy_page_id, 'blog' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		: $blog_main_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
<?php
else :
	alify_render_partial( 'template-parts/pages/blog.php' );
endif;

get_footer();
