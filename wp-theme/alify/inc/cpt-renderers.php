<?php
/**
 * Dynamic `WP_Query` Renderers for All 6 ALIFY CPTs & Blog Slider
 * + Single CPT Payload Bridge (`window.ALIFY_CPT_SINGLE`) for Single Detail Templates
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely replace the inner HTML of a container element matched by `$open_marker` (e.g. `id="servicesGrid"`).
 * Uses deterministic tag-depth scanning so nested `<div>` / `<section>` blocks never misalign.
 */
function alify_replace_container_inner( $html, $open_marker, $tag_name, $replacement_inner ) {
	$marker_pos = strpos( $html, $open_marker );
	if ( false === $marker_pos ) {
		return $html;
	}

	$tag_open_pos = strrpos( substr( $html, 0, $marker_pos ), '<' . $tag_name );
	if ( false === $tag_open_pos ) {
		return $html;
	}

	$open_tag_end = strpos( $html, '>', $marker_pos );
	if ( false === $open_tag_end ) {
		return $html;
	}

	$inner_start = $open_tag_end + 1;
	$len         = strlen( $html );
	$depth       = 1;
	$cursor      = $inner_start;
	$open_pat    = '<' . $tag_name;
	$close_pat   = '</' . $tag_name . '>';
	$open_len    = strlen( $open_pat );
	$close_len   = strlen( $close_pat );

	while ( $cursor < $len && $depth > 0 ) {
		$next_open  = stripos( $html, $open_pat, $cursor );
		$next_close = stripos( $html, $close_pat, $cursor );

		if ( false === $next_close ) {
			return $html;
		}

		if ( false !== $next_open && $next_open < $next_close ) {
			// Verify word boundary after `<div` (space, `>`, `/`, newline)
			$after_ch = isset( $html[ $next_open + $open_len ] ) ? $html[ $next_open + $open_len ] : '';
			if ( '' === $after_ch || ctype_space( $after_ch ) || '>' === $after_ch || '/' === $after_ch ) {
				$depth++;
			}
			$cursor = $next_open + $open_len;
		} else {
			$depth--;
			if ( 0 === $depth ) {
				return substr( $html, 0, $inner_start ) . "\n" . $replacement_inner . "\n" . substr( $html, $next_close );
			}
			$cursor = $next_close + $close_len;
		}
	}

	return $html;
}

/**
 * Resolve CPT background image URL (Featured Image -> Custom Meta URL -> Fallback Theme Asset).
 */
function alify_get_cpt_bg_url( $post_id, $meta_key, $fallback_rel ) {
	$thumb = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( ! empty( $thumb ) ) {
		return $thumb;
	}
	$meta_img = get_post_meta( $post_id, $meta_key, true );
	if ( ! empty( $meta_img ) ) {
		if ( 0 === strpos( $meta_img, 'assets/' ) ) {
			return alify_asset_uri( $meta_img );
		}
		return $meta_img;
	}
	return alify_asset_uri( $fallback_rel );
}

/**
 * Parse newline-separated strings into clean non-empty array.
 */
function alify_parse_lines( $raw ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$out   = array();
	foreach ( $lines as $line ) {
		$trimmed = trim( $line );
		if ( '' !== $trimmed ) {
			$out[] = $trimmed;
		}
	}
	return $out;
}

/**
 * 1. Render Service Card HTML (`alify_service`)
 */
function alify_render_service_card_markup( $post, $index = 1 ) {
	$pid    = $post->ID;
	$num    = str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$notch  = get_post_meta( $pid, '_alify_svc_notch', true );
	$notch  = $notch ? $notch : ( $num . ' · SERVICE' );
	$tag    = get_post_meta( $pid, '_alify_svc_tag', true );
	$tag    = $tag ? $tag : 'Capability';
	$icon   = get_post_meta( $pid, '_alify_svc_icon', true );
	$icon   = $icon ? $icon : 'fa-solid fa-code';
	$bg_url = alify_get_cpt_bg_url( $pid, '_alify_svc_bg_image', 'assets/images/services/service-web-dev.jpg' );
	$points = alify_parse_lines( get_post_meta( $pid, '_alify_svc_points', true ) );
	if ( empty( $points ) ) {
		$points = array(
			'Senior Product Strategy & UX Direction',
			'Clean Custom Frontend & CMS Architecture',
			'99+ Core Web Vitals & Conversion QA',
		);
	}
	$desc = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 22 );
	$url  = get_permalink( $pid );

	ob_start();
	?>
	<article id="<?php echo esc_attr( $post->post_name ); ?>" class="service-card-wrap">
		<span class="service-notch-badge" aria-hidden="true"><?php echo esc_html( $notch ); ?></span>
		<div class="service-card">
			<div class="service-card-inner">
				<div class="service-card-bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>
				<div class="service-card-content">
					<div class="service-card-top">
						<span class="service-card-icon" aria-hidden="true">
							<i class="<?php echo esc_attr( $icon ); ?>"></i>
						</span>
						<span class="service-card-tag"><?php echo esc_html( $tag ); ?></span>
					</div>

					<h3 class="service-card-title">
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
					</h3>

					<p class="service-card-desc"><?php echo esc_html( $desc ); ?></p>

					<ul class="service-card-points">
						<?php foreach ( array_slice( $points, 0, 3 ) as $pt ) : ?>
							<li>
								<span class="service-point-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
								<span><?php echo esc_html( $pt ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="service-card-bottom">
					<a href="<?php echo esc_url( $url ); ?>" class="service-card-cta">
						<span class="service-card-cta-pill"><?php esc_html_e( 'View Service', 'alify' ); ?></span>
						<span class="service-card-cta-circle" aria-hidden="true">
							<i class="fa-solid fa-arrow-up service-card-cta-arrow"></i>
						</span>
					</a>
				</div>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * 2. Render Industry Card HTML (`alify_industry`)
 */
function alify_render_industry_card_markup( $post, $index = 1 ) {
	$pid    = $post->ID;
	$num    = str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$code   = get_post_meta( $pid, '_alify_ind_code', true );
	$code   = $code ? $code : ( $num . ' · SECTOR' );
	$icon   = get_post_meta( $pid, '_alify_ind_icon', true );
	$icon   = $icon ? $icon : 'fa-solid fa-landmark';
	$stat   = get_post_meta( $pid, '_alify_ind_stat', true );
	$stat   = $stat ? $stat : 'Avg. +140% Conversion Lift';
	$bg_url = alify_get_cpt_bg_url( $pid, '_alify_ind_bg_image', 'assets/images/industries/industry-fintech.jpg' );
	$points = alify_parse_lines( get_post_meta( $pid, '_alify_ind_points', true ) );
	if ( empty( $points ) ) {
		$points = array(
			'Domain-Specific UX & Information Architecture',
			'Compliance-Ready Custom Web Engineering',
			'Measurable Funnel & Pipeline Velocity',
		);
	}
	$desc = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 22 );
	$url  = get_permalink( $pid );

	ob_start();
	?>
	<article id="<?php echo esc_attr( $post->post_name ); ?>" class="industry-card">
		<div class="industry-card-bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>
		<div class="industry-card-content">
			<div class="industry-card-top">
				<span class="industry-card-icon" aria-hidden="true">
					<i class="<?php echo esc_attr( $icon ); ?>"></i>
				</span>
				<span class="industry-card-code"><?php echo esc_html( $code ); ?></span>
			</div>

			<h3 class="industry-card-title">
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
			</h3>

			<p class="industry-card-desc"><?php echo esc_html( $desc ); ?></p>

			<ul class="industry-card-deliverables">
				<?php foreach ( array_slice( $points, 0, 3 ) as $pt ) : ?>
					<li>
						<span class="industry-deliverable-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
						<span><?php echo esc_html( $pt ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="industry-card-footer">
			<span class="industry-card-stat"><?php echo esc_html( $stat ); ?></span>
			<a href="<?php echo esc_url( $url ); ?>" class="industry-card-cta">
				<span class="industry-card-cta-pill"><?php esc_html_e( 'Explore Sector', 'alify' ); ?></span>
				<span class="industry-card-cta-circle" aria-hidden="true">
					<i class="fa-solid fa-arrow-up industry-card-cta-arrow"></i>
				</span>
			</a>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * 3. Render Project Home Accordion Panel & Work 2-Col Card (`alify_project`)
 */
function alify_render_project_markup( $post, $index = 1, $mode = 'grid' ) {
	$pid         = $post->ID;
	$num         = get_post_meta( $pid, '_alify_proj_num', true );
	$num         = $num ? $num : str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$short_title = get_post_meta( $pid, '_alify_proj_short_title', true );
	$short_title = $short_title ? $short_title : get_the_title( $pid );
	$client      = get_post_meta( $pid, '_alify_proj_client', true );
	$client      = $client ? $client : strtoupper( $short_title );
	$sector      = get_post_meta( $pid, '_alify_proj_sector', true );
	$sector      = $sector ? $sector : 'Digital Product';
	$year        = get_post_meta( $pid, '_alify_proj_year', true );
	$year        = $year ? $year : '2026';
	$metric      = get_post_meta( $pid, '_alify_proj_metric', true );
	$metric      = $metric ? $metric : '+150% Conversion Growth';
	$chips_raw   = get_post_meta( $pid, '_alify_proj_chips', true );
	$chips       = array_filter( array_map( 'trim', explode( ',', $chips_raw ? $chips_raw : 'Web Architecture, UI/UX System, GSAP Motion' ) ) );
	$bg_url      = alify_get_cpt_bg_url( $pid, '_alify_proj_bg_image', 'assets/images/projects/project-finpulse.jpg' );
	$desc        = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 24 );
	$url         = get_permalink( $pid );

	ob_start();
	if ( 'accordion' === $mode ) :
		$is_first = ( 1 === $index );
		?>
		<article
			class="project-panel<?php echo $is_first ? ' is-active' : ''; ?>"
			tabindex="0"
			role="button"
			aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
			aria-label="<?php echo esc_attr( $short_title ); ?>"
		>
			<div class="project-panel-bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>

			<div class="project-panel-collapsed" aria-hidden="true">
				<span class="project-collapsed-num"><?php echo esc_html( $num ); ?></span>
				<div class="project-collapsed-vertical">
					<span class="project-collapsed-title"><?php echo esc_html( $short_title ); ?></span>
					<span class="project-collapsed-cat"><?php echo esc_html( $sector ); ?></span>
				</div>
				<span class="project-collapsed-plus"><i class="fa-solid fa-plus"></i></span>
			</div>

			<div class="project-panel-expanded">
				<div class="project-panel-top">
					<div class="project-panel-badges">
						<span class="project-badge-glass"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> <?php echo esc_html( $sector ); ?></span>
						<span class="project-badge-glass"><?php echo esc_html( $year ); ?></span>
					</div>
					<span class="project-metric-pill"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> <?php echo esc_html( $metric ); ?></span>
				</div>

				<div class="project-panel-bottom">
					<span class="project-client-label"><?php echo esc_html( $client ); ?></span>
					<h3 class="project-panel-title">
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
					</h3>
					<p class="project-panel-desc"><?php echo esc_html( $desc ); ?></p>

					<div class="project-panel-footer">
						<div class="project-deliverable-chips">
							<?php foreach ( array_slice( $chips, 0, 3 ) as $chip ) : ?>
								<span class="project-chip"><?php echo esc_html( $chip ); ?></span>
							<?php endforeach; ?>
						</div>
						<a href="<?php echo esc_url( $url ); ?>" class="project-cta-btn">
							<span class="project-cta-pill"><?php esc_html_e( 'View Project', 'alify' ); ?></span>
							<span class="project-cta-circle" aria-hidden="true"><i class="fa-solid fa-arrow-up project-cta-arrow"></i></span>
						</a>
					</div>
				</div>
			</div>
		</article>
	<?php else : ?>
		<article id="<?php echo esc_attr( $post->post_name ); ?>" class="project-grid-card">
			<div class="project-grid-card-bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>

			<div class="project-panel-top">
				<div class="project-panel-badges">
					<span class="project-badge-glass"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> <?php echo esc_html( $sector ); ?></span>
					<span class="project-badge-glass"><?php echo esc_html( $year ); ?></span>
				</div>
				<span class="project-metric-pill"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> <?php echo esc_html( $metric ); ?></span>
			</div>

			<div class="project-panel-bottom">
				<span class="project-client-label"><?php echo esc_html( $client ); ?></span>
				<h3 class="project-panel-title">
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
				</h3>
				<p class="project-panel-desc"><?php echo esc_html( $desc ); ?></p>

				<div class="project-panel-footer">
					<div class="project-deliverable-chips">
						<?php foreach ( array_slice( $chips, 0, 3 ) as $chip ) : ?>
							<span class="project-chip"><?php echo esc_html( $chip ); ?></span>
						<?php endforeach; ?>
					</div>
					<a href="<?php echo esc_url( $url ); ?>" class="project-cta-btn">
						<span class="project-cta-pill"><?php esc_html_e( 'View Project', 'alify' ); ?></span>
						<span class="project-cta-circle" aria-hidden="true"><i class="fa-solid fa-arrow-up project-cta-arrow"></i></span>
					</a>
				</div>
			</div>
		</article>
		<?php
	endif;
	return ob_get_clean();
}

/**
 * 4. Render Case Study Editorial Card (`alify_case_study`)
 */
function alify_render_case_study_card_markup( $post, $index = 1 ) {
	$pid         = $post->ID;
	$slug        = $post->post_name;
	$filter      = get_post_meta( $pid, '_alify_cs_filter', true );
	$filter      = $filter ? $filter : 'fintech-saas';
	$kicker      = get_post_meta( $pid, '_alify_cs_client_kicker', true );
	$kicker      = $kicker ? $kicker : 'ENTERPRISE CLIENT';
	$sec_badge   = get_post_meta( $pid, '_alify_cs_sector_badge', true );
	$sec_badge   = $sec_badge ? $sec_badge : 'Digital Platform';
	$dur_badge   = get_post_meta( $pid, '_alify_cs_duration_badge', true );
	$dur_badge   = $dur_badge ? $dur_badge : '10-Week Sprint';
	$bg_url      = alify_get_cpt_bg_url( $pid, '_alify_cs_bg_image', 'assets/images/projects/project-finpulse.jpg' );
	$bottleneck  = get_post_meta( $pid, '_alify_cs_bottleneck', true );
	$arch        = get_post_meta( $pid, '_alify_cs_architecture', true );
	$quote       = get_post_meta( $pid, '_alify_cs_quote', true );
	$quote_auth  = get_post_meta( $pid, '_alify_cs_quote_author', true );
	$chips_raw   = get_post_meta( $pid, '_alify_cs_chips', true );
	$chips       = array_filter( array_map( 'trim', explode( ',', $chips_raw ? $chips_raw : 'Design System, Custom Frontend, CRO Architecture' ) ) );

	$bench_lines = alify_parse_lines( get_post_meta( $pid, '_alify_cs_benchmarks', true ) );
	$kpi_lines   = alify_parse_lines( get_post_meta( $pid, '_alify_cs_kpis', true ) );
	$url         = get_permalink( $pid );
	$rev_cls     = ( 0 === $index % 2 ) ? ' is-reversed' : '';

	ob_start();
	?>
	<article id="<?php echo esc_attr( $slug ); ?>" class="cs-editorial-card<?php echo esc_attr( $rev_cls ); ?>" data-category="<?php echo esc_attr( $filter ); ?>">
		<div class="row cs-stage-row">
			<div class="col-12 col-lg-6">
				<div class="cs-visual-frame">
					<div class="cs-visual-bg" style="background-image: url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>
					<div class="cs-visual-top">
						<span class="cs-glass-tag"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> <?php echo esc_html( $sec_badge ); ?></span>
						<span class="cs-glass-tag"><i class="fa-solid fa-clock" aria-hidden="true"></i> <?php echo esc_html( $dur_badge ); ?></span>
					</div>

					<?php if ( ! empty( $bench_lines ) ) : ?>
						<div class="cs-benchmark-dock">
							<span class="cs-benchmark-label"><?php esc_html_e( 'Verified Before vs. After Benchmark', 'alify' ); ?></span>
							<div class="cs-benchmark-items">
								<?php foreach ( array_slice( $bench_lines, 0, 2 ) as $bl ) : ?>
									<?php $parts = array_map( 'trim', explode( '|', $bl ) ); ?>
									<?php if ( count( $parts ) >= 3 ) : ?>
										<div class="cs-benchmark-pair">
											<span class="cs-bench-name"><?php echo esc_html( $parts[0] ); ?>:</span>
											<span class="cs-bench-before"><?php echo esc_html( $parts[1] ); ?></span>
											<i class="fa-solid fa-arrow-right cs-bench-arrow" aria-hidden="true"></i>
											<span class="cs-bench-after"><?php echo esc_html( $parts[2] ); ?></span>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="col-12 col-lg-6">
				<div class="cs-narrative-col">
					<div>
						<span class="cs-client-kicker"><?php echo esc_html( $kicker ); ?></span>
						<h3 class="cs-case-title"><?php echo esc_html( get_the_title( $pid ) ); ?></h3>

						<div class="cs-diagnosis-list">
							<?php if ( ! empty( $bottleneck ) ) : ?>
								<div class="cs-diagnosis-box is-challenge">
									<span class="cs-diagnosis-head"><?php esc_html_e( '01 · The Bottleneck', 'alify' ); ?></span>
									<p class="cs-diagnosis-text"><?php echo esc_html( $bottleneck ); ?></p>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $arch ) ) : ?>
								<div class="cs-diagnosis-box">
									<span class="cs-diagnosis-head"><?php esc_html_e( '02 · ALIFY Architecture', 'alify' ); ?></span>
									<p class="cs-diagnosis-text"><?php echo esc_html( $arch ); ?></p>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $kpi_lines ) ) : ?>
							<div class="row cs-kpi-row">
								<?php foreach ( array_slice( $kpi_lines, 0, 3 ) as $k_idx => $kl ) : ?>
									<?php $kp = array_map( 'trim', explode( '|', $kl ) ); ?>
									<div class="col-12 col-md-4">
										<div class="cs-kpi-tile<?php echo 0 === $k_idx ? ' is-highlight' : ''; ?>">
											<span class="cs-kpi-value"><?php echo esc_html( isset( $kp[0] ) ? $kp[0] : '' ); ?></span>
											<span class="cs-kpi-label"><?php echo esc_html( isset( $kp[1] ) ? $kp[1] : '' ); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="cs-blueprint-wrap">
							<button type="button" class="cs-blueprint-toggle js-cs-blueprint-btn" aria-expanded="false" aria-controls="blueprint-<?php echo esc_attr( $slug ); ?>">
								<i class="fa-solid fa-sliders" aria-hidden="true"></i>
								<span><?php esc_html_e( 'Inspect Deliverables & Architecture', 'alify' ); ?></span>
								<i class="fa-solid fa-chevron-down cs-blueprint-chevron" aria-hidden="true"></i>
							</button>
							<div id="blueprint-<?php echo esc_attr( $slug ); ?>" class="cs-blueprint-panel">
								<div class="cs-blueprint-tags">
									<?php foreach ( $chips as $chip ) : ?>
										<span class="cs-blueprint-chip"><?php echo esc_html( $chip ); ?></span>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="cs-stage-footer">
						<?php if ( ! empty( $quote ) ) : ?>
							<div class="cs-mini-quote">
								<p class="cs-mini-quote-text">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
								<?php if ( ! empty( $quote_auth ) ) : ?>
									<span class="cs-mini-quote-author"><?php echo esc_html( $quote_auth ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<a href="<?php echo esc_url( $url ); ?>" class="btn-start-project">
							<span class="btn-start-pill"><?php esc_html_e( 'Read Case Study', 'alify' ); ?></span>
							<span class="btn-start-circle" aria-hidden="true"><i class="fa-solid fa-arrow-up btn-start-arrow"></i></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * 5. Render Testimonial Card (`alify_testimonial`)
 */
function alify_render_testimonial_card_markup( $post ) {
	$pid      = $post->ID;
	$name     = get_the_title( $pid );
	$role     = get_post_meta( $pid, '_alify_testi_role', true );
	$initials = get_post_meta( $pid, '_alify_testi_initials', true );
	if ( empty( $initials ) ) {
		$words    = preg_split( '/\s+/', trim( $name ) );
		$initials = strtoupper( substr( $words[0], 0, 1 ) . ( isset( $words[1] ) ? substr( $words[1], 0, 1 ) : '' ) );
	}
	$rating   = get_post_meta( $pid, '_alify_testi_rating', true );
	$rating   = $rating ? $rating : '5.0';
	$outcome  = get_post_meta( $pid, '_alify_testi_outcome', true );
	$outcome  = $outcome ? $outcome : 'Verified Launch';
	$tag      = get_post_meta( $pid, '_alify_testi_tag', true );
	$tag      = $tag ? $tag : 'Web & Engineering';
	$quote    = wp_strip_all_tags( $post->post_content );

	ob_start();
	?>
	<article class="testimonial-card">
		<div>
			<div class="testimonial-card-top">
				<div class="testimonial-rating" aria-label="<?php echo esc_attr( 'Rated ' . $rating . ' out of 5 stars' ); ?>">
					<i class="fa-solid fa-star" aria-hidden="true"></i>
					<i class="fa-solid fa-star" aria-hidden="true"></i>
					<i class="fa-solid fa-star" aria-hidden="true"></i>
					<i class="fa-solid fa-star" aria-hidden="true"></i>
					<i class="fa-solid fa-star" aria-hidden="true"></i>
					<span class="testimonial-rating-score"><?php echo esc_html( $rating ); ?></span>
				</div>
				<span class="testimonial-outcome-pill">
					<i class="fa-solid fa-chart-line" aria-hidden="true"></i>
					<?php echo esc_html( $outcome ); ?>
				</span>
			</div>
			<div class="testimonial-body">
				<i class="fa-solid fa-quote-left testimonial-quote-mark" aria-hidden="true"></i>
				<p class="testimonial-quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
			</div>
		</div>
		<div class="testimonial-footer">
			<div class="testimonial-author">
				<span class="testimonial-avatar" aria-hidden="true">
					<?php echo esc_html( $initials ); ?>
					<span class="testimonial-verified-badge" title="Verified Client">
						<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					</span>
				</span>
				<div class="testimonial-author-meta">
					<h3 class="testimonial-author-name"><?php echo esc_html( $name ); ?></h3>
					<p class="testimonial-author-role"><?php echo esc_html( $role ); ?></p>
				</div>
			</div>
			<span class="testimonial-service-tag"><?php echo esc_html( $tag ); ?></span>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * 6. Render FAQ Item (`alify_faq`)
 */
function alify_render_faq_item_markup( $post, $index = 1, $is_open = false ) {
	$num    = str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$answer = wpautop( wp_kses_post( $post->post_content ) );

	ob_start();
	?>
	<div class="faq-item<?php echo $is_open ? ' is-open' : ''; ?>">
		<button type="button" class="faq-question-btn" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
			<span class="faq-question-left">
				<span class="faq-num"><?php echo esc_html( $num ); ?></span>
				<span class="faq-question-text"><?php echo esc_html( get_the_title( $post->ID ) ); ?></span>
			</span>
			<span class="faq-toggle-icon" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
		</button>
		<div class="faq-answer-wrap">
			<div class="faq-answer-inner">
				<div class="faq-answer-body"><?php echo $answer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Inject Live `WP_Query` Output into Any Template HTML Whenever CPT Posts Exist in Database.
 */
function alify_inject_dynamic_cpts( $html ) {

	// A. Services Slider (`#servicesSliderTrack`) & Services Archive Grid (`#servicesGrid`)
	if ( false !== strpos( $html, 'servicesSliderTrack' ) || false !== strpos( $html, 'servicesGrid' ) ) {
		$services = get_posts(
			array(
				'post_type'      => 'alify_service',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $services ) ) {
			if ( false !== strpos( $html, 'id="servicesSliderTrack"' ) ) {
				$slides = '';
				foreach ( $services as $idx => $svc_post ) {
					$slides .= '<div class="service-slide">' . alify_render_service_card_markup( $svc_post, $idx + 1 ) . '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="servicesSliderTrack"', 'div', $slides );
			}
			if ( false !== strpos( $html, 'id="servicesGrid"' ) ) {
				$cols = '';
				foreach ( $services as $idx => $svc_post ) {
					$cat   = get_post_meta( $svc_post->ID, '_alify_svc_filter', true );
					$cat   = $cat ? $cat : 'engineering';
					$cols .= '<div class="col-12 col-md-6 col-lg-4 service-grid-col" data-category="' . esc_attr( $cat ) . '">';
					$cols .= alify_render_service_card_markup( $svc_post, $idx + 1 );
					$cols .= '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="servicesGrid"', 'div', $cols );
			}
		}
	}

	// B. Industries Slider (`#industriesSliderTrack`) & Industries Grid (`#industriesGrid`)
	if ( false !== strpos( $html, 'industriesSliderTrack' ) || false !== strpos( $html, 'industriesGrid' ) ) {
		$industries = get_posts(
			array(
				'post_type'      => 'alify_industry',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $industries ) ) {
			if ( false !== strpos( $html, 'id="industriesSliderTrack"' ) ) {
				$slides = '';
				foreach ( $industries as $idx => $ind_post ) {
					$slides .= '<div class="industry-slide">' . alify_render_industry_card_markup( $ind_post, $idx + 1 ) . '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="industriesSliderTrack"', 'div', $slides );
			}
			if ( false !== strpos( $html, 'id="industriesGrid"' ) ) {
				$cols = '';
				foreach ( $industries as $idx => $ind_post ) {
					$sec   = get_post_meta( $ind_post->ID, '_alify_ind_sector_group', true );
					$sec   = $sec ? $sec : 'finance-cloud';
					$cols .= '<div class="col-12 col-md-6 col-lg-4 industry-grid-col" data-sector="' . esc_attr( $sec ) . '">';
					$cols .= alify_render_industry_card_markup( $ind_post, $idx + 1 );
					$cols .= '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="industriesGrid"', 'div', $cols );
			}
		}
	}

	// C. Projects Accordion (`#projectsAccordionStage`) & Work 2-Col Grid (`#projectsGrid`)
	if ( false !== strpos( $html, 'projectsAccordionStage' ) || false !== strpos( $html, 'projectsGrid' ) ) {
		$projects = get_posts(
			array(
				'post_type'      => 'alify_project',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $projects ) ) {
			if ( false !== strpos( $html, 'id="projectsAccordionStage"' ) ) {
				$panels = '';
				foreach ( array_slice( $projects, 0, 6 ) as $idx => $proj_post ) {
					$panels .= alify_render_project_markup( $proj_post, $idx + 1, 'accordion' );
				}
				$html = alify_replace_container_inner( $html, 'id="projectsAccordionStage"', 'div', $panels );
			}
			if ( false !== strpos( $html, 'id="projectsGrid"' ) ) {
				$cols = '';
				foreach ( $projects as $idx => $proj_post ) {
					$cat   = get_post_meta( $proj_post->ID, '_alify_proj_filter', true );
					$cat   = $cat ? $cat : 'web-saas';
					$cols .= '<div class="col-12 col-md-6 project-grid-col" data-category="' . esc_attr( $cat ) . '">';
					$cols .= alify_render_project_markup( $proj_post, $idx + 1, 'grid' );
					$cols .= '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="projectsGrid"', 'div', $cols );
			}
		}
	}

	// D. Case Studies Stack (`#caseStudiesStack`)
	if ( false !== strpos( $html, 'id="caseStudiesStack"' ) ) {
		$cases = get_posts(
			array(
				'post_type'      => 'alify_case_study',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $cases ) ) {
			$stack = '';
			foreach ( $cases as $idx => $cs_post ) {
				$stack .= alify_render_case_study_card_markup( $cs_post, $idx + 1 );
			}
			$html = alify_replace_container_inner( $html, 'id="caseStudiesStack"', 'div', $stack );
		}
	}

	// E. Testimonials Dual-Row Carousel (`.testimonials-dual-stage`) & Grid (`#testimonialsGrid`)
	if ( false !== strpos( $html, 'testimonials-dual-stage' ) || false !== strpos( $html, 'testimonialsGrid' ) ) {
		$testis = get_posts(
			array(
				'post_type'      => 'alify_testimonial',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $testis ) ) {
			if ( false !== strpos( $html, 'class="testimonials-dual-stage"' ) ) {
				$half  = (int) ceil( count( $testis ) / 2 );
				$row1  = array_slice( $testis, 0, max( 1, $half ) );
				$row2  = array_slice( $testis, $half );
				if ( empty( $row2 ) ) {
					$row2 = $row1;
				}
				$dual  = '<div class="testimonials-row-viewport" data-row="1"><div class="testimonials-track">';
				foreach ( $row1 as $tp ) {
					$dual .= '<div class="testimonial-slide-item">' . alify_render_testimonial_card_markup( $tp ) . '</div>';
				}
				$dual .= '</div></div>';
				$dual .= '<div class="testimonials-row-viewport" data-row="2"><div class="testimonials-track">';
				foreach ( $row2 as $tp ) {
					$dual .= '<div class="testimonial-slide-item">' . alify_render_testimonial_card_markup( $tp ) . '</div>';
				}
				$dual .= '</div></div>';
				$html  = alify_replace_container_inner( $html, 'class="testimonials-dual-stage"', 'div', $dual );
			}
			if ( false !== strpos( $html, 'id="testimonialsGrid"' ) ) {
				$cols = '';
				foreach ( $testis as $tp ) {
					$cat   = get_post_meta( $tp->ID, '_alify_testi_filter', true );
					$cat   = $cat ? $cat : 'web';
					$cols .= '<div class="col-12 col-md-6 col-lg-4 testimonial-grid-col" data-category="' . esc_attr( $cat ) . '">';
					$cols .= alify_render_testimonial_card_markup( $tp );
					$cols .= '</div>';
				}
				$html = alify_replace_container_inner( $html, 'id="testimonialsGrid"', 'div', $cols );
			}
		}
	}

	// F. FAQs Hub (`#faqHubGroupsWrap`) & Reusable FAQ Section (`faq-section`)
	if ( false !== strpos( $html, 'faq-accordion-list' ) || false !== strpos( $html, 'faqHubGroupsWrap' ) ) {
		$faqs = get_posts(
			array(
				'post_type'      => 'alify_faq',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			)
		);
		if ( ! empty( $faqs ) ) {
			if ( false !== strpos( $html, 'id="faqHubGroupsWrap"' ) ) {
				$pillars = array(
					'scope-sprints'     => array( 'Scope, Sprints & Delivery Process', 'fa-solid fa-compass-drafting' ),
					'pricing-billing'   => array( 'Pricing, Milestones & Retainer Billing', 'fa-solid fa-Credit-card' ),
					'engineering-stack' => array( 'Engineering, Tech Stack & Performance', 'fa-solid fa-code' ),
					'design-ownership'  => array( 'Design Systems, Figma & IP Ownership', 'fa-solid fa-pen-ruler' ),
					'support-retainers' => array( 'Post-Launch Support, SEO & Growth', 'fa-solid fa-headset' ),
				);
				$pillars['pricing-billing'][1] = 'fa-solid fa-file-invoice-dollar';

				$grouped = array();
				foreach ( $faqs as $fp ) {
					$pil = get_post_meta( $fp->ID, '_alify_faq_pillar', true );
					if ( ! isset( $pillars[ $pil ] ) ) {
						$pil = 'scope-sprints';
					}
					$grouped[ $pil ][] = $fp;
				}

				$groups_html = '';
				$global_num  = 1;
				foreach ( $pillars as $pil_slug => $pil_info ) {
					if ( empty( $grouped[ $pil_slug ] ) ) {
						continue;
					}
					$cnt          = count( $grouped[ $pil_slug ] );
					$groups_html .= '<div class="faq-hub-group" id="cat-' . esc_attr( $pil_slug ) . '" data-faq-cat="' . esc_attr( $pil_slug ) . '">';
					$groups_html .= '<div class="faq-hub-group-head">';
					$groups_html .= '<h2 class="faq-hub-group-title"><span class="faq-hub-group-icon" aria-hidden="true"><i class="' . esc_attr( $pil_info[1] ) . '"></i></span><span>' . esc_html( $pil_info[0] ) . '</span></h2>';
					$groups_html .= '<span class="faq-hub-group-badge">' . esc_html( str_pad( (string) $cnt, 2, '0', STR_PAD_LEFT ) . ' Questions' ) . '</span>';
					$groups_html .= '</div><div class="faq-accordion-list">';
					foreach ( $grouped[ $pil_slug ] as $fp ) {
						$groups_html .= alify_render_faq_item_markup( $fp, $global_num, 1 === $global_num );
						$global_num++;
					}
					$groups_html .= '</div></div>';
				}
				$html = alify_replace_container_inner( $html, 'id="faqHubGroupsWrap"', 'div', $groups_html );
			} elseif ( false !== strpos( $html, 'class="faq-section"' ) ) {
				$reusable_faqs = array();
				foreach ( $faqs as $fp ) {
					if ( '1' === (string) get_post_meta( $fp->ID, '_alify_faq_show_reusable', true ) ) {
						$reusable_faqs[] = $fp;
					}
				}
				if ( empty( $reusable_faqs ) ) {
					$reusable_faqs = array_slice( $faqs, 0, 6 );
				}
				$acc_items = '';
				foreach ( array_slice( $reusable_faqs, 0, 6 ) as $idx => $fp ) {
					$acc_items .= alify_render_faq_item_markup( $fp, $idx + 1, 0 === $idx );
				}
				$html = alify_replace_container_inner( $html, 'class="faq-accordion-list"', 'div', $acc_items );
			}
		}
	}

	// G. Reusable Blog Slider (`#homeBlogSliderTrack`) Powered by Native WordPress `post` Entries
	if ( false !== strpos( $html, 'id="homeBlogSliderTrack"' ) ) {
		$blog_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 6,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		if ( ! empty( $blog_posts ) ) {
			$slides = '';
			foreach ( $blog_posts as $bp ) {
				$thumb = get_the_post_thumbnail_url( $bp->ID, 'large' );
				if ( empty( $thumb ) ) {
					$thumb = alify_get_cpt_bg_url( $bp->ID, '_alify_post_bg_image', 'assets/images/blog/blog-design-systems.jpg' );
				}
				$cats     = get_the_category( $bp->ID );
				$cat_name = ! empty( $cats ) ? $cats[0]->name : 'Engineering';
				$excerpt  = has_excerpt( $bp->ID ) ? get_the_excerpt( $bp->ID ) : wp_trim_words( wp_strip_all_tags( $bp->post_content ), 20 );
				$plink    = get_permalink( $bp->ID );
				$date_str = get_the_date( 'M j, Y', $bp->ID );

				$slides .= '<div class="blog-slide"><article class="blog-card">';
				$slides .= '<a href="' . esc_url( $plink ) . '" class="blog-card-media">';
				$slides .= '<span class="blog-card-category">' . esc_html( $cat_name ) . '</span>';
				$slides .= '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( get_the_title( $bp->ID ) ) . '" class="blog-card-img" loading="lazy" />';
				$slides .= '</a><div class="blog-card-body">';
				$slides .= '<div class="blog-card-meta"><span class="blog-card-meta-item"><i class="fa-solid fa-calendar" aria-hidden="true"></i> <span>' . esc_html( $date_str ) . '</span></span></div>';
				$slides .= '<h3 class="blog-card-title"><a href="' . esc_url( $plink ) . '">' . esc_html( get_the_title( $bp->ID ) ) . '</a></h3>';
				$slides .= '<p class="blog-card-excerpt">' . esc_html( $excerpt ) . '</p>';
				$slides .= '<div class="blog-card-footer"><a href="' . esc_url( $plink ) . '" class="blog-read-more"><span class="blog-read-more-pill">Read More</span><span class="blog-read-more-circle" aria-hidden="true"><i class="fa-solid fa-arrow-up blog-read-more-arrow"></i></span></a></div>';
				$slides .= '</div></article></div>';
			}
			$html = alify_replace_container_inner( $html, 'id="homeBlogSliderTrack"', 'div', $slides );
		}
	}

	return $html;
}

/**
 * Build Live `window.ALIFY_CPT_SINGLE` Data Payload for Single CPT Templates (`single-alify_*.php`)
 * so both edited starter items AND brand-new admin-created items hydrate 100% dynamically!
 */
function alify_build_single_cpt_override( $post ) {
	if ( ! $post || empty( $post->post_type ) ) {
		return null;
	}
	$pid  = $post->ID;
	$slug = $post->post_name;
	$desc = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 36 );

	if ( 'alify_service' === $post->post_type ) {
		$points     = alify_parse_lines( get_post_meta( $pid, '_alify_svc_points', true ) );
		$stat_lines = alify_parse_lines( get_post_meta( $pid, '_alify_svc_stats', true ) );
		$stats      = array();
		foreach ( $stat_lines as $sl ) {
			$sp = array_map( 'trim', explode( '|', $sl ) );
			if ( count( $sp ) >= 2 ) {
				$stats[] = array( $sp[0], $sp[1] );
			}
		}
		if ( empty( $stats ) ) {
			$stats = array( array( '99+', 'Core Web Vitals' ), array( '< 0.8s', 'Avg. LCP Speed' ), array( '+142%', 'Conversion Lift' ) );
		}

		$deliv_lines  = alify_parse_lines( get_post_meta( $pid, '_alify_svc_deliverables', true ) );
		$deliverables = array();
		foreach ( $deliv_lines as $dl ) {
			$dp = array_map( 'trim', explode( '|', $dl ) );
			if ( count( $dp ) >= 3 ) {
				$deliverables[] = array( $dp[0], $dp[1], $dp[2] );
			}
		}
		if ( empty( $deliverables ) && ! empty( $points ) ) {
			foreach ( $points as $pt ) {
				$deliverables[] = array( 'fa-solid fa-layer-group', $pt, 'Engineered directly by ALIFY senior specialists with measurable performance benchmarks.' );
			}
		}

		$data = array(
			'title' => get_the_title( $pid ),
			'code'  => get_post_meta( $pid, '_alify_svc_notch', true ) ? get_post_meta( $pid, '_alify_svc_notch', true ) : 'STUDIO CAPABILITY',
			'desc'  => $desc,
			'bg'    => alify_get_cpt_bg_url( $pid, '_alify_svc_bg_image', 'assets/images/services/service-web-dev.jpg' ),
			'tags'  => ! empty( $points ) ? array_slice( $points, 0, 3 ) : array( 'Senior Architecture', 'Custom Build', 'Conversion QA' ),
			'stats' => $stats,
		);
		if ( ! empty( $deliverables ) ) {
			$data['deliverables'] = $deliverables;
		}
		return array( 'type' => 'service', 'slug' => $slug, 'data' => $data );
	}

	if ( 'alify_project' === $post->post_type ) {
		$chips_raw = get_post_meta( $pid, '_alify_proj_chips', true );
		$chips     = array_values( array_filter( array_map( 'trim', explode( ',', $chips_raw ? $chips_raw : 'Web Architecture, Design System, GSAP Motion' ) ) ) );
		$bg_url    = alify_get_cpt_bg_url( $pid, '_alify_proj_bg_image', 'assets/images/projects/project-finpulse.jpg' );
		$short_t   = get_post_meta( $pid, '_alify_proj_short_title', true );
		$short_t   = $short_t ? $short_t : get_the_title( $pid );

		$gal_lines = alify_parse_lines( get_post_meta( $pid, '_alify_proj_gallery', true ) );
		$gallery   = array();
		foreach ( $gal_lines as $gl ) {
			$gp = array_map( 'trim', explode( '|', $gl ) );
			if ( ! empty( $gp[0] ) ) {
				$img_src   = ( 0 === strpos( $gp[0], 'assets/' ) ) ? alify_asset_uri( $gp[0] ) : $gp[0];
				$gallery[] = array(
					'src'     => $img_src,
					'tag'     => isset( $gp[1] ) ? $gp[1] : 'Interface Showcase',
					'title'   => isset( $gp[2] ) ? $gp[2] : $short_t,
					'caption' => isset( $gp[3] ) ? $gp[3] : $desc,
				);
			}
		}

		$brief = get_post_meta( $pid, '_alify_proj_brief', true );
		$craft = get_post_meta( $pid, '_alify_proj_craft', true );
		if ( empty( $brief ) && ! empty( $post->post_content ) ) {
			$brief = wp_strip_all_tags( $post->post_content );
		}

		$data = array(
			'slug'       => $slug,
			'shortTitle' => $short_t,
			'title'      => get_the_title( $pid ),
			'kicker'     => get_post_meta( $pid, '_alify_proj_client', true ) ? get_post_meta( $pid, '_alify_proj_client', true ) : strtoupper( $short_t ),
			'client'     => get_post_meta( $pid, '_alify_proj_client', true ) ? get_post_meta( $pid, '_alify_proj_client', true ) : $short_t,
			'category'   => get_post_meta( $pid, '_alify_proj_sector', true ) ? get_post_meta( $pid, '_alify_proj_sector', true ) : 'Digital Product',
			'year'       => get_post_meta( $pid, '_alify_proj_year', true ) ? get_post_meta( $pid, '_alify_proj_year', true ) : '2026',
			'sprint'     => get_post_meta( $pid, '_alify_proj_sprint', true ) ? get_post_meta( $pid, '_alify_proj_sprint', true ) : '10-Week Sprint',
			'metric'     => get_post_meta( $pid, '_alify_proj_metric', true ) ? get_post_meta( $pid, '_alify_proj_metric', true ) : '+150% Growth',
			'desc'       => $desc,
			'bg'         => $bg_url,
			'chips'      => $chips,
		);
		if ( ! empty( $brief ) ) {
			$data['brief'] = $brief;
		}
		if ( ! empty( $craft ) ) {
			$data['craft'] = $craft;
		}
		if ( ! empty( $gallery ) ) {
			$data['gallery'] = $gallery;
		}
		$case_slug = get_post_meta( $pid, '_alify_proj_case_slug', true );
		if ( ! empty( $case_slug ) ) {
			$data['caseId'] = $case_slug;
		}
		return array( 'type' => 'project', 'slug' => $slug, 'data' => $data );
	}

	if ( 'alify_industry' === $post->post_type ) {
		$points     = alify_parse_lines( get_post_meta( $pid, '_alify_ind_points', true ) );
		$stat_lines = alify_parse_lines( get_post_meta( $pid, '_alify_ind_stats', true ) );
		$stats      = array();
		foreach ( $stat_lines as $sl ) {
			$sp = array_map( 'trim', explode( '|', $sl ) );
			if ( count( $sp ) >= 2 ) {
				$stats[] = array( $sp[0], $sp[1] );
			}
		}
		$data = array(
			'title' => get_the_title( $pid ),
			'code'  => get_post_meta( $pid, '_alify_ind_code', true ) ? get_post_meta( $pid, '_alify_ind_code', true ) : 'SECTOR EXPERTISE',
			'desc'  => $desc,
			'bg'    => alify_get_cpt_bg_url( $pid, '_alify_ind_bg_image', 'assets/images/industries/industry-fintech.jpg' ),
			'tags'  => ! empty( $points ) ? array_slice( $points, 0, 3 ) : array( 'Compliance-Ready UX', 'Custom Web Platform', 'Conversion Velocity' ),
		);
		if ( ! empty( $stats ) ) {
			$data['stats'] = $stats;
		}
		return array( 'type' => 'industry', 'slug' => $slug, 'data' => $data );
	}

	if ( 'alify_case_study' === $post->post_type ) {
		$kpi_lines   = alify_parse_lines( get_post_meta( $pid, '_alify_cs_kpis', true ) );
		$kpis        = array();
		foreach ( $kpi_lines as $kl ) {
			$kp = array_map( 'trim', explode( '|', $kl ) );
			if ( count( $kp ) >= 2 ) {
				$kpis[] = array( $kp[0], $kp[1] );
			}
		}
		$bench_lines = alify_parse_lines( get_post_meta( $pid, '_alify_cs_benchmarks', true ) );
		$benchmarks  = array();
		foreach ( $bench_lines as $bl ) {
			$bp = array_map( 'trim', explode( '|', $bl ) );
			if ( count( $bp ) >= 3 ) {
				$benchmarks[] = array( $bp[0], $bp[1], $bp[2] );
			}
		}

		$data = array(
			'client'       => get_post_meta( $pid, '_alify_cs_client_short', true ) ? get_post_meta( $pid, '_alify_cs_client_short', true ) : get_the_title( $pid ),
			'hq'           => get_post_meta( $pid, '_alify_cs_client_kicker', true ),
			'sector'       => get_post_meta( $pid, '_alify_cs_sector_badge', true ) ? get_post_meta( $pid, '_alify_cs_sector_badge', true ) : 'Digital Product',
			'sprint'       => get_post_meta( $pid, '_alify_cs_duration_badge', true ) ? get_post_meta( $pid, '_alify_cs_duration_badge', true ) : '10-Week Sprint',
			'title'        => get_the_title( $pid ),
			'summary'      => $desc,
			'bg'           => alify_get_cpt_bg_url( $pid, '_alify_cs_bg_image', 'assets/images/projects/project-finpulse.jpg' ),
			'deliverables' => get_post_meta( $pid, '_alify_cs_chips', true ),
			'challenge'    => get_post_meta( $pid, '_alify_cs_bottleneck', true ),
			'architecture' => get_post_meta( $pid, '_alify_cs_architecture', true ),
			'quote'        => get_post_meta( $pid, '_alify_cs_quote', true ),
			'quoteAuthor'  => get_post_meta( $pid, '_alify_cs_quote_author', true ),
		);
		if ( ! empty( $kpis ) ) {
			$data['kpis'] = $kpis;
		}
		if ( ! empty( $benchmarks ) ) {
			$data['benchmarks'] = $benchmarks;
		}
		return array( 'type' => 'case', 'slug' => $slug, 'data' => $data );
	}

	return null;
}
