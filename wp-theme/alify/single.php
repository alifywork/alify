<?php
/**
 * Single Post Template (`single.php`)
 * Matches `blog-single.html` 100%:
 *   1. `.blog-single-hero` with `#innerHeroBg.hero-bg-media`, `.breadcrumb-nav`, `.blog-single-badge`, `.blog-single-title` & `.blog-single-meta-bar`
 *   2. 3-Column Sticky Layout (`col-12 col-lg-3` Sticky TOC | `col-12 col-lg-6` `.blog-prose` | `col-12 col-lg-3` Sticky Sidebar)
 *   3. `.blog-related-section` (`WP_Query` Related Articles) at the end of `<main>`
 *
 * @package ALIFY
 */

get_header();

while ( have_posts() ) :
	the_post();
	$pid     = get_the_ID();
	$hero_bg = get_the_post_thumbnail_url( $pid, 'full' );
	if ( empty( $hero_bg ) ) {
		$meta_img = get_post_meta( $pid, '_alify_post_bg_image', true );
		if ( ! empty( $meta_img ) ) {
			$hero_bg = ( 0 === strpos( $meta_img, 'assets/' ) ) ? alify_asset_uri( $meta_img ) : $meta_img;
		}
	}
	$featured_img = $hero_bg ? $hero_bg : alify_asset_uri( 'assets/images/blog/blog-design-systems.jpg' );
	if ( empty( $hero_bg ) ) {
		$hero_bg = function_exists( 'alify_cf_opt_img' ) ? alify_cf_opt_img( 'alify_singles', 'sgl_blog_default_hero_bg', alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' ) ) : alify_asset_uri( 'assets/images/heroes/hero-blog.jpg' );
	}

	$cats             = get_the_category( $pid );
	$custom_badge     = trim( (string) get_post_meta( $pid, '_alify_post_badge', true ) );
	$cat_name         = ! empty( $custom_badge ) ? $custom_badge : ( ! empty( $cats ) ? $cats[0]->name : 'Engineering & UX' );
	$word_count       = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $pid ) ) );
	$read_mins        = max( 3, (int) ceil( $word_count / 200 ) );
	$custom_read_time = trim( (string) get_post_meta( $pid, '_alify_post_read_time', true ) );
	$read_time_label  = ! empty( $custom_read_time ) ? $custom_read_time : ( $read_mins . ' Min Read' );
	$custom_author    = trim( (string) get_post_meta( $pid, '_alify_post_author_label', true ) );
	$author_display   = ! empty( $custom_author ) ? $custom_author : ( get_the_author() ? get_the_author() : 'ALIFY Editorial Team' );
	$toc_heading      = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_singles', 'sgl_blog_toc_heading', 'Table of Contents' ) : 'Table of Contents';
	$sbar_cta_title   = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_singles', 'sgl_blog_sidebar_cta_title', 'Have a Project in Mind?' ) : 'Have a Project in Mind?';
	$sbar_cta_desc    = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_singles', 'sgl_blog_sidebar_cta_desc', 'Let’s architect a fast, conversion-led digital platform tailored to your brand.' ) : 'Let’s architect a fast, conversion-led digital platform tailored to your brand.';
	$sbar_cta_btn     = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_singles', 'sgl_blog_sidebar_cta_btn', get_theme_mod( 'alify_cta_text', 'Start a Project' ) ) : get_theme_mod( 'alify_cta_text', 'Start a Project' );
	$rel_eyebrow      = function_exists( 'alify_cf_opt' ) ? alify_cf_opt( 'alify_singles', 'sgl_blog_related_eyebrow', 'Continue Reading' ) : 'Continue Reading';
	$rel_title_opt    = function_exists( 'alify_cf_opt_saved' ) ? alify_cf_opt_saved( 'alify_singles', 'sgl_blog_related_title' ) : '';
	$share_url        = rawurlencode( get_permalink( $pid ) );
	$share_ttl  = rawurlencode( get_the_title( $pid ) );
	$author_bio = get_the_author_meta( 'description' );
	if ( empty( $author_bio ) ) {
		$author_bio = __( 'Insights written by the senior designers and full-stack engineers at ALIFY Studio — helping ambitious brands ship faster, sharper digital products.', 'alify' );
	}
	?>
	<main>
		<!-- Single Post Hero & Breadcrumbs -->
		<section class="blog-single-hero" aria-label="<?php esc_attr_e( 'Article Header', 'alify' ); ?>">
			<div id="innerHeroBg" class="hero-bg-media" style="background-image: url('<?php echo esc_url( $hero_bg ); ?>');" aria-hidden="true"></div>
			<div class="container">
				<div class="blog-single-hero-inner">
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
								<a href="<?php echo esc_url( alify_page_url( 'blog' ) ); ?>" class="breadcrumb-link">
									<span><?php esc_html_e( 'Blog', 'alify' ); ?></span>
								</a>
							</li>
							<li class="breadcrumb-item-entry" aria-hidden="true">
								<i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
							</li>
							<li class="breadcrumb-item-entry">
								<span class="breadcrumb-current" id="blogSingleBreadcrumbCurrent" aria-current="page">
									<?php the_title(); ?>
								</span>
							</li>
						</ol>
					</nav>

					<span class="blog-single-badge" id="blogSingleCategory"><?php echo esc_html( $cat_name ); ?></span>

					<h1 class="blog-single-title" id="blogSingleTitle"><?php the_title(); ?></h1>

					<div class="blog-single-meta-bar">
						<span class="blog-single-meta-entry">
							<i class="fa-solid fa-user" aria-hidden="true"></i>
							<span><?php echo esc_html( $author_display ); ?></span>
						</span>
						<span class="blog-single-meta-entry">
							<i class="fa-solid fa-calendar" aria-hidden="true"></i>
							<span id="blogSingleDate"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
						</span>
						<span class="blog-single-meta-entry">
							<i class="fa-solid fa-clock" aria-hidden="true"></i>
							<span id="blogSingleReadTime"><?php echo esc_html( $read_time_label ); ?></span>
						</span>
					</div>
				</div>
			</div>
		</section>

		<!-- Single Blog 3-Column Sticky Layout: Left TOC | Center Content | Right Sidebar -->
		<section class="blog-single-section" aria-label="<?php esc_attr_e( 'Article Body', 'alify' ); ?>">
			<div class="container">
				<div class="row blog-single-row">
					<!-- LEFT COLUMN: Sticky Table of Contents -->
					<div class="col-12 col-lg-3">
						<aside class="blog-toc-sticky" aria-label="<?php esc_attr_e( 'Table of Contents', 'alify' ); ?>">
							<div class="blog-toc-card" id="blogTocCard">
								<div class="blog-toc-header">
									<h2 class="blog-toc-heading">
										<i class="fa-solid fa-list-ul" aria-hidden="true"></i>
										<span><?php echo esc_html( $toc_heading ); ?></span>
									</h2>
									<button
										type="button"
										class="blog-toc-mobile-btn"
										id="blogTocMobileBtn"
										aria-expanded="true"
									>
										<span><?php esc_html_e( 'Toggle', 'alify' ); ?></span>
										<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
									</button>
								</div>

								<div class="blog-reading-progress" aria-hidden="true">
									<div class="blog-reading-progress-fill" id="blogReadingProgressFill"></div>
								</div>

								<nav aria-label="<?php esc_attr_e( 'Article Sections', 'alify' ); ?>">
									<ol class="blog-toc-list" id="blogTocList">
										<li>
											<a href="#blogSingleProse" class="blog-toc-link is-active"><?php esc_html_e( 'Article Overview', 'alify' ); ?></a>
										</li>
									</ol>
								</nav>

								<div class="blog-toc-share">
									<span class="blog-toc-share-label"><?php esc_html_e( 'Share Article', 'alify' ); ?></span>
									<div class="blog-toc-share-btns">
										<button
											type="button"
											class="blog-share-btn"
											id="blogCopyLinkBtn"
											aria-label="<?php esc_attr_e( 'Copy article link', 'alify' ); ?>"
										>
											<i class="fa-solid fa-link" aria-hidden="true"></i>
										</button>
										<a
											href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="blog-share-btn"
											aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'alify' ); ?>"
										>
											<i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>
										</a>
										<a
											href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_ttl ); ?>"
											target="_blank"
											rel="noopener noreferrer"
											class="blog-share-btn"
											aria-label="<?php esc_attr_e( 'Share on X', 'alify' ); ?>"
										>
											<i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
										</a>
									</div>
									<div class="blog-share-status" id="blogShareStatus" aria-live="polite"></div>
								</div>
							</div>
						</aside>
					</div>

					<!-- CENTER COLUMN: Main WordPress Article Content -->
					<div class="col-12 col-lg-6">
						<article <?php post_class( 'blog-article-main' ); ?>>
							<figure class="blog-featured-figure">
								<img
									src="<?php echo esc_url( $featured_img ); ?>"
									alt="<?php the_title_attribute(); ?>"
									class="blog-featured-img"
									id="blogSingleFeaturedImg"
								/>
							</figure>

							<div class="blog-prose" id="blogSingleProse">
								<?php the_content(); ?>
							</div>

							<?php
							$tags = get_the_tags( $pid );
							if ( ! empty( $tags ) ) :
								?>
								<div class="blog-post-tags-bar" id="blogSingleTags">
									<?php foreach ( $tags as $tag_obj ) : ?>
										<a href="<?php echo esc_url( get_tag_link( $tag_obj->term_id ) ); ?>" class="blog-post-tag-pill">
											<i class="fa-solid fa-tag" aria-hidden="true"></i>
											<span><?php echo esc_html( $tag_obj->name ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="blog-author-card">
								<span class="blog-author-avatar" aria-hidden="true">△</span>
								<div>
									<h3 class="blog-author-name"><?php echo esc_html( get_the_author() ? get_the_author() : 'ALIFY Product & Design Team' ); ?></h3>
									<div class="blog-author-role"><?php esc_html_e( 'Design Systems & Frontend Engineering', 'alify' ); ?></div>
									<p class="blog-author-bio"><?php echo esc_html( $author_bio ); ?></p>
								</div>
							</div>

							<?php
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
							?>
						</article>
					</div>

					<!-- RIGHT COLUMN: Sticky Sidebar -->
					<div class="col-12 col-lg-3">
						<aside class="blog-sidebar-sticky" aria-label="<?php esc_attr_e( 'Blog Sidebar', 'alify' ); ?>">
							<!-- Search Widget -->
							<div class="blog-sidebar-widget">
								<h2 class="blog-widget-title"><?php esc_html_e( 'Search Journal', 'alify' ); ?></h2>
								<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="blog-sidebar-search">
									<input
										type="search"
										name="s"
										placeholder="<?php esc_attr_e( 'Search articles...', 'alify' ); ?>"
										aria-label="<?php esc_attr_e( 'Search blog articles', 'alify' ); ?>"
									/>
									<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'alify' ); ?>">
										<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
									</button>
								</form>
							</div>

							<!-- Recent Articles Widget -->
							<?php
							$recent_q = new WP_Query(
								array(
									'post_type'           => 'post',
									'posts_per_page'      => 4,
									'post_status'         => 'publish',
									'ignore_sticky_posts' => true,
								)
							);
							if ( $recent_q->have_posts() ) :
								?>
								<div class="blog-sidebar-widget">
									<h2 class="blog-widget-title"><?php esc_html_e( 'Recent Articles', 'alify' ); ?></h2>
									<ul class="blog-recent-list">
										<?php
										while ( $recent_q->have_posts() ) :
											$recent_q->the_post();
											$r_id    = get_the_ID();
											$r_thumb = get_the_post_thumbnail_url( $r_id, 'thumbnail' );
											if ( empty( $r_thumb ) && function_exists( 'alify_get_cpt_bg_url' ) ) {
												$r_thumb = alify_get_cpt_bg_url( $r_id, '_alify_post_bg_image', 'assets/images/blog/blog-design-systems.jpg' );
											}
											if ( empty( $r_thumb ) ) {
												$r_thumb = alify_asset_uri( 'assets/images/blog/blog-design-systems.jpg' );
											}
											?>
											<li class="blog-recent-item">
												<a href="<?php the_permalink(); ?>" class="blog-recent-thumb">
													<img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
												</a>
												<div class="blog-recent-info">
													<span class="blog-recent-date"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
													<a href="<?php the_permalink(); ?>" class="blog-recent-link"><?php the_title(); ?></a>
												</div>
											</li>
										<?php endwhile; ?>
									</ul>
								</div>
								<?php
								wp_reset_postdata();
							endif;
							?>

							<!-- Categories Widget -->
							<?php
							$all_cats = get_categories( array( 'hide_empty' => true ) );
							if ( ! empty( $all_cats ) ) :
								?>
								<div class="blog-sidebar-widget">
									<h2 class="blog-widget-title"><?php esc_html_e( 'Categories', 'alify' ); ?></h2>
									<ul class="blog-category-list">
										<?php foreach ( $all_cats as $cat_obj ) : ?>
											<li>
												<a href="<?php echo esc_url( get_category_link( $cat_obj->term_id ) ); ?>" class="blog-category-link">
													<span><?php echo esc_html( $cat_obj->name ); ?></span>
													<span class="blog-category-count"><?php echo esc_html( str_pad( (string) $cat_obj->count, 2, '0', STR_PAD_LEFT ) ); ?></span>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<!-- Sidebar Studio CTA Card -->
							<div class="blog-sidebar-cta">
								<h2 class="blog-sidebar-cta-title"><?php echo esc_html( $sbar_cta_title ); ?></h2>
								<p class="blog-sidebar-cta-desc">
									<?php echo esc_html( $sbar_cta_desc ); ?>
								</p>
								<a href="<?php echo esc_url( alify_page_url( 'start-project' ) ); ?>" class="btn-start-project btn-start-light">
									<span class="btn-start-pill"><?php echo esc_html( $sbar_cta_btn ); ?></span>
									<span class="btn-start-circle" aria-hidden="true">
										<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
									</span>
								</a>
							</div>
						</aside>
					</div>
				</div>
			</div>
		</section>

		<!-- Related Blogs Section (Placed at End of Single Blog Main Content) -->
		<?php
		$related_q = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( $pid ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			)
		);
		if ( $related_q->have_posts() ) :
			?>
			<section class="blog-related-section" aria-label="<?php esc_attr_e( 'Related Blog Articles', 'alify' ); ?>">
				<div class="container">
					<div class="row blog-related-head-row">
						<div class="col-12 col-lg-7">
							<div class="blog-related-eyebrow">
								<span class="blog-related-eyebrow-dot" aria-hidden="true">△</span>
								<span><?php echo esc_html( $rel_eyebrow ); ?></span>
							</div>
							<h2 class="blog-related-title">
								<?php if ( ! empty( $rel_title_opt ) ) : ?>
									<?php echo wp_kses( $rel_title_opt, array( 'span' => array( 'class' => array() ), 'br' => array() ) ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Related Blogs &', 'alify' ); ?> <span><?php esc_html_e( 'Engineering Insights.', 'alify' ); ?></span>
								<?php endif; ?>
							</h2>
							<p class="blog-related-desc">
								<?php esc_html_e( 'Explore more hands-on guides from ALIFY on design systems, 60fps GSAP motion, conversion UX, and high-speed frontend engineering.', 'alify' ); ?>
							</p>
						</div>
						<div class="col-12 col-lg-5">
							<div class="blog-related-actions">
								<a href="<?php echo esc_url( alify_page_url( 'blog' ) ); ?>" class="btn-start-project">
									<span class="btn-start-pill"><?php esc_html_e( 'Explore All Articles', 'alify' ); ?></span>
									<span class="btn-start-circle" aria-hidden="true">
										<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
									</span>
								</a>
							</div>
						</div>
					</div>

					<div class="row blog-grid-row">
						<?php
						while ( $related_q->have_posts() ) :
							$related_q->the_post();
							$rel_id    = get_the_ID();
							$rel_thumb = get_the_post_thumbnail_url( $rel_id, 'large' );
							if ( empty( $rel_thumb ) && function_exists( 'alify_get_cpt_bg_url' ) ) {
								$rel_thumb = alify_get_cpt_bg_url( $rel_id, '_alify_post_bg_image', 'assets/images/blog/blog-gsap-motion.jpg' );
							}
							if ( empty( $rel_thumb ) ) {
								$rel_thumb = alify_asset_uri( 'assets/images/blog/blog-gsap-motion.jpg' );
							}
							$rel_cats  = get_the_category( $rel_id );
							$rel_cat   = ! empty( $rel_cats ) ? $rel_cats[0]->name : 'Insights';
							$rel_words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $rel_id ) ) );
							$rel_mins  = max( 3, (int) ceil( $rel_words / 200 ) );
							$rel_exc   = has_excerpt( $rel_id ) ? get_the_excerpt( $rel_id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $rel_id ) ), 22 );
							?>
							<div class="col-12 col-md-6 col-lg-4 blog-grid-col">
								<article class="blog-card">
									<a href="<?php the_permalink(); ?>" class="blog-card-media">
										<span class="blog-card-category"><?php echo esc_html( $rel_cat ); ?></span>
										<img src="<?php echo esc_url( $rel_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img" loading="lazy" />
									</a>
									<div class="blog-card-body">
										<div class="blog-card-meta">
											<span class="blog-card-meta-item">
												<i class="fa-solid fa-calendar" aria-hidden="true"></i>
												<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
											</span>
											<span class="blog-card-meta-item">
												<i class="fa-solid fa-clock" aria-hidden="true"></i>
												<span><?php echo esc_html( $rel_mins . ' Min Read' ); ?></span>
											</span>
										</div>
										<h3 class="blog-card-title">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>
										<p class="blog-card-excerpt"><?php echo esc_html( $rel_exc ); ?></p>
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
