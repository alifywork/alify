<?php
/**
 * Single Post Template (`single.php`)
 * Signature ALIFY 3-Column Sticky Layout (Left Sticky TOC, Center Post Content, Right Sticky Sidebar)
 * + Dynamic Related Blogs `WP_Query` at the end of `<main>`.
 *
 * @package ALIFY
 */

get_header();

while ( have_posts() ) :
	the_post();
	$hero_bg = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	if ( empty( $hero_bg ) ) {
		$hero_bg = alify_asset_uri( 'assets/images/heroes/hero-blog-single.jpg' );
	}
	$cats     = get_the_category();
	$cat_name = ! empty( $cats ) ? $cats[0]->name : 'Engineering & UX';
	$email    = get_theme_mod( 'alify_email', 'info@alify.site' );
	?>
	<main>
		<section class="inner-page-hero inner-page-hero--compact" aria-label="<?php the_title_attribute(); ?>">
			<div class="inner-hero-bg" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>')"></div>
			<div class="inner-hero-overlay"></div>
			<div class="inner-hero-grid" aria-hidden="true"></div>
			<div class="container">
				<div class="row">
					<div class="col-12 col-lg-10">
						<nav class="inner-breadcrumb" aria-label="Breadcrumb">
							<ol class="inner-breadcrumb-list">
								<li class="inner-breadcrumb-item">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span></a>
								</li>
								<li class="inner-breadcrumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
								<li class="inner-breadcrumb-item">
									<a href="<?php echo esc_url( alify_page_url( 'blog' ) ); ?>">Blog</a>
								</li>
								<li class="inner-breadcrumb-sep" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
								<li class="inner-breadcrumb-item is-current" aria-current="page"><?php the_title(); ?></li>
							</ol>
						</nav>
						<div class="inner-hero-eyebrow">
							<span class="inner-hero-eyebrow-dot" aria-hidden="true"></span>
							<span><?php echo esc_html( $cat_name ); ?> &middot; <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
						</div>
						<h1 class="inner-hero-title"><?php the_title(); ?></h1>
					</div>
				</div>
			</div>
		</section>

		<section class="blog-single-section" aria-label="Article Content">
			<div class="container">
				<div class="row blog-single-row">
					<!-- Left Column: Sticky Table of Contents -->
					<div class="col-12 col-lg-3">
						<aside class="blog-toc-sticky" aria-label="Table of Contents">
							<div class="blog-toc-card">
								<div class="blog-toc-header">
									<span class="blog-toc-title"><i class="fa-solid fa-list-ul" aria-hidden="true"></i> On This Page</span>
								</div>
								<nav class="blog-toc-nav" id="wpSingleTocNav">
									<p class="blog-toc-hint"><?php esc_html_e( 'Scroll through the article sections.', 'alify' ); ?></p>
								</nav>
							</div>
						</aside>
					</div>

					<!-- Center Column: Main WordPress Post Content -->
					<div class="col-12 col-lg-6">
						<article <?php post_class( 'blog-article-content' ); ?> id="blogArticleBody">
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="blog-featured-figure">
									<?php the_post_thumbnail( 'large' ); ?>
								</figure>
							<?php endif; ?>

							<div class="blog-wp-entry-content">
								<?php the_content(); ?>
							</div>

							<?php
							$tags = get_the_tags();
							if ( ! empty( $tags ) ) :
								?>
								<div class="blog-post-tags">
									<?php foreach ( $tags as $tag_obj ) : ?>
										<a href="<?php echo esc_url( get_tag_link( $tag_obj->term_id ) ); ?>" class="blog-post-tag-pill">
											<i class="fa-solid fa-tag" aria-hidden="true"></i>
											<span><?php echo esc_html( $tag_obj->name ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
							?>
						</article>
					</div>

					<!-- Right Column: Sticky Sidebar -->
					<div class="col-12 col-lg-3">
						<aside class="blog-sidebar-sticky" aria-label="Article Sidebar">
							<div class="blog-sidebar-card">
								<span class="blog-sidebar-label">Written By</span>
								<h3 class="blog-sidebar-author"><?php the_author(); ?></h3>
								<p class="blog-sidebar-bio"><?php echo esc_html( get_the_author_meta( 'description' ) ? get_the_author_meta( 'description' ) : 'Senior Product, Design Systems & Growth Engineering Team at ALIFY.' ); ?></p>
							</div>

							<div class="blog-sidebar-card blog-sidebar-cta">
								<span class="blog-sidebar-label">Need Senior Execution?</span>
								<h3 class="blog-sidebar-cta-title">Turn These Playbooks Into Revenue.</h3>
								<p class="blog-sidebar-bio">Book a 30-minute architecture &amp; growth call directly with ALIFY principals.</p>
								<a href="<?php echo esc_url( alify_page_url( 'book-call' ) ); ?>" class="btn-hero-primary" data-book-call-open>
									<span>Book a Strategy Call</span>
									<i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
								</a>
								<p class="blog-sidebar-direct">Or email <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
							</div>
						</aside>
					</div>
				</div>
			</div>
		</section>

		<?php
		// Related Posts Section at the end of <main>
		$related_q = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( get_the_ID() ),
				'ignore_sticky_posts' => true,
			)
		);
		if ( $related_q->have_posts() ) :
			?>
			<section class="related-blogs-section" aria-labelledby="wpRelatedBlogsHeading">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<div class="section-eyebrow">
								<span class="section-eyebrow-mark" aria-hidden="true">&#9651;</span>
								<span>Keep Reading</span>
							</div>
							<h2 id="wpRelatedBlogsHeading" class="section-heading">Related <span class="text-highlight">Insights &amp; Articles</span></h2>
						</div>
					</div>
					<div class="row blog-archive-grid">
						<?php
						while ( $related_q->have_posts() ) :
							$related_q->the_post();
							$rel_thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
							if ( empty( $rel_thumb ) ) {
								$rel_thumb = alify_asset_uri( 'assets/images/blog/blog-gsap-motion.jpg' );
							}
							$rel_cats = get_the_category();
							$rel_cat  = ! empty( $rel_cats ) ? $rel_cats[0]->name : 'Insights';
							?>
							<div class="col-12 col-md-6 col-lg-4">
								<article class="blog-card">
									<a href="<?php the_permalink(); ?>" class="blog-card-media">
										<img src="<?php echo esc_url( $rel_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
										<span class="blog-card-category"><?php echo esc_html( $rel_cat ); ?></span>
									</a>
									<div class="blog-card-body">
										<h3 class="blog-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<p class="blog-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
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
				</div>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>
	</main>
	<?php
endwhile;

get_footer();
