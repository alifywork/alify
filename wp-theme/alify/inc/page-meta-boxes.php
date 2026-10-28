<?php
/**
 * Per-Page Hero & Section Visibility Meta Boxes + Dynamic Customizer/Page HTML Overrides
 * Allows editing any Page's Hero Eyebrow, Title, Subtitle, Background Image (or Featured Image),
 * Lead Form toggle, and Reusable Sections visibility directly from WP Admin -> Pages!
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register "ALIFY Page Hero & Section Controls" Meta Box on `page` Post Type.
 */
function alify_register_page_hero_meta_box() {
	if ( function_exists( 'alify_has_cf_plugin' ) && alify_has_cf_plugin() ) {
		return;
	}
	add_meta_box(
		'alify_page_hero_controls',
		__( 'ALIFY Page Hero & Section Controls', 'alify' ),
		'alify_render_page_hero_meta_box',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'alify_register_page_hero_meta_box' );

function alify_render_page_hero_meta_box( $post ) {
	wp_nonce_field( 'alify_save_page_hero_meta', 'alify_page_hero_nonce' );

	$eyebrow = get_post_meta( $post->ID, '_alify_page_hero_eyebrow', true );
	$title   = get_post_meta( $post->ID, '_alify_page_hero_title', true );
	$desc    = get_post_meta( $post->ID, '_alify_page_hero_desc', true );
	$bg_img  = get_post_meta( $post->ID, '_alify_page_hero_bg', true );

	$toggles = array(
		'_alify_page_hide_hero_form'    => __( 'Hide Right-Side Hero Lead Form Card on this page', 'alify' ),
		'_alify_page_hide_testimonials' => __( 'Hide Reusable Testimonials Slider Section on this page', 'alify' ),
		'_alify_page_hide_faq'          => __( 'Hide Reusable FAQs Section on this page', 'alify' ),
		'_alify_page_hide_blog_slider'  => __( 'Hide Reusable Blog Slider Section on this page', 'alify' ),
		'_alify_page_hide_contact'      => __( 'Hide Bottom Reusable Contact Section on this page', 'alify' ),
	);
	?>
	<div style="display:grid;gap:14px;padding:8px 0;">
		<p class="description" style="margin:0;">
			<?php esc_html_e( 'Leave any text/image field blank to keep the default ALIFY studio copy & artwork for this page. Setting a WordPress Featured Image in the right sidebar also overrides the page hero background.', 'alify' ); ?>
		</p>

		<div>
			<label for="_alify_page_hero_eyebrow" style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Hero Eyebrow Badge Text', 'alify' ); ?></label>
			<input type="text" id="_alify_page_hero_eyebrow" name="_alify_page_hero_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'e.g. Who We Are', 'alify' ); ?>" />
		</div>

		<div>
			<label for="_alify_page_hero_title" style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Hero Main H1 Heading (HTML <span> allowed for accent)', 'alify' ); ?></label>
			<input type="text" id="_alify_page_hero_title" name="_alify_page_hero_title" value="<?php echo esc_attr( $title ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'e.g. Design-Driven Minds Building <span class="inner-hero-title-accent">Iconic Digital</span> Brands.', 'alify' ); ?>" />
		</div>

		<div>
			<label for="_alify_page_hero_desc" style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Hero Subtitle / Description', 'alify' ); ?></label>
			<textarea id="_alify_page_hero_desc" name="_alify_page_hero_desc" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Optional custom hero lead paragraph...', 'alify' ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		</div>

		<div>
			<label for="_alify_page_hero_bg" style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Hero Background Image URL Override', 'alify' ); ?></label>
			<div style="display:flex;gap:8px;align-items:center;">
				<input type="text" id="_alify_page_hero_bg" name="_alify_page_hero_bg" value="<?php echo esc_attr( $bg_img ); ?>" class="regular-text" style="flex:1;" placeholder="https://..." />
				<button type="button" class="button alify-page-bg-pick-btn" data-target="_alify_page_hero_bg"><?php esc_html_e( 'Choose Image', 'alify' ); ?></button>
			</div>
		</div>

		<fieldset style="border-top:1px solid #ddd;padding-top:12px;margin-top:4px;">
			<legend style="font-weight:600;margin-bottom:8px;"><?php esc_html_e( 'Section Visibility Controls', 'alify' ); ?></legend>
			<?php foreach ( $toggles as $t_key => $t_label ) : ?>
				<?php $checked = ( '1' === (string) get_post_meta( $post->ID, $t_key, true ) ); ?>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="<?php echo esc_attr( $t_key ); ?>" value="1" <?php checked( $checked ); ?> />
					<?php echo esc_html( $t_label ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
	</div>
	<?php
}

/**
 * Enqueue Media Picker on `page` Edit Screens.
 */
function alify_admin_enqueue_page_media( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	$js = "
	jQuery(function($){
		$(document).on('click', '.alify-page-bg-pick-btn', function(e){
			e.preventDefault();
			var targetId = $(this).data('target');
			var frame = wp.media({ title: 'Select Hero Background Image', multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('#' + targetId).val(att.url);
			});
			frame.open();
		});
	});
	";
	wp_add_inline_script( 'jquery-core', $js );
}
add_action( 'admin_enqueue_scripts', 'alify_admin_enqueue_page_media' );

/**
 * Save Page Hero & Visibility Meta Fields.
 */
function alify_save_page_hero_meta( $post_id, $post ) {
	if ( 'page' !== $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['alify_page_hero_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_page_hero_nonce'] ) ), 'alify_save_page_hero_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_alify_page_hero_eyebrow'] ) ) {
		update_post_meta( $post_id, '_alify_page_hero_eyebrow', sanitize_text_field( wp_unslash( $_POST['_alify_page_hero_eyebrow'] ) ) );
	}
	if ( isset( $_POST['_alify_page_hero_title'] ) ) {
		update_post_meta( $post_id, '_alify_page_hero_title', wp_kses_post( wp_unslash( $_POST['_alify_page_hero_title'] ) ) );
	}
	if ( isset( $_POST['_alify_page_hero_desc'] ) ) {
		update_post_meta( $post_id, '_alify_page_hero_desc', sanitize_textarea_field( wp_unslash( $_POST['_alify_page_hero_desc'] ) ) );
	}
	if ( isset( $_POST['_alify_page_hero_bg'] ) ) {
		update_post_meta( $post_id, '_alify_page_hero_bg', esc_url_raw( wp_unslash( $_POST['_alify_page_hero_bg'] ) ) );
	}

	$checkbox_keys = array(
		'_alify_page_hide_hero_form',
		'_alify_page_hide_testimonials',
		'_alify_page_hide_faq',
		'_alify_page_hide_blog_slider',
		'_alify_page_hide_contact',
	);
	foreach ( $checkbox_keys as $ck ) {
		$val = isset( $_POST[ $ck ] ) ? '1' : '0';
		update_post_meta( $post_id, $ck, $val );
	}
}
add_action( 'save_post', 'alify_save_page_hero_meta', 10, 2 );

/**
 * Remove an entire HTML element (`<section ...>...</section>` or `<div ...>...</div>`) containing `$marker`.
 */
function alify_remove_container_element( $html, $marker, $tag_name ) {
	$marker_pos = strpos( $html, $marker );
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

	$len       = strlen( $html );
	$depth     = 1;
	$cursor    = $open_tag_end + 1;
	$open_pat  = '<' . $tag_name;
	$close_pat = '</' . $tag_name . '>';
	$open_len  = strlen( $open_pat );
	$close_len = strlen( $close_pat );

	while ( $cursor < $len && $depth > 0 ) {
		$next_open  = stripos( $html, $open_pat, $cursor );
		$next_close = stripos( $html, $close_pat, $cursor );

		if ( false === $next_close ) {
			return $html;
		}

		if ( false !== $next_open && $next_open < $next_close ) {
			$after_ch = isset( $html[ $next_open + $open_len ] ) ? $html[ $next_open + $open_len ] : '';
			if ( '' === $after_ch || ctype_space( $after_ch ) || '>' === $after_ch || '/' === $after_ch ) {
				$depth++;
			}
			$cursor = $next_open + $open_len;
		} else {
			$depth--;
			if ( 0 === $depth ) {
				return substr( $html, 0, $tag_open_pos ) . substr( $html, $next_close + $close_len );
			}
			$cursor = $next_close + $close_len;
		}
	}

	return $html;
}

/**
 * Apply Home Hero Customizer, Pricing Customizer, and Per-Page Meta Box Overrides at Render Time.
 */
function alify_apply_page_and_customizer_overrides( $html ) {

	// 1. Front Page Hero Customizer Overrides (`class="home-hero"`)
	if ( false !== strpos( $html, 'class="home-hero"' ) ) {
		$home_bg = get_theme_mod( 'alify_home_bg_image', '' );
		if ( empty( $home_bg ) && is_page() ) {
			$qid     = get_queried_object_id();
			$home_bg = $qid ? get_the_post_thumbnail_url( $qid, 'full' ) : '';
			if ( empty( $home_bg ) && $qid ) {
				$home_bg = get_post_meta( $qid, '_alify_page_hero_bg', true );
			}
		}
		if ( ! empty( $home_bg ) ) {
			$html = preg_replace(
				'/(id="homeHeroBg"[^>]*style="background-image:\s*url\([\'"]?)[^\'")]+([\'"]?\);?")/i',
				'$1' . esc_url( $home_bg ) . '$2',
				$html,
				1
			);
		}

		$status_pill = get_theme_mod( 'alify_home_status_pill', 'Creative Digital Studio — Available for New Projects' );
		$html        = preg_replace(
			'/(<div class="hero-status-pill">\s*<span class="hero-status-dot"[^>]*><\/span>\s*<span>).*?(<\/span>)/s',
			'$1' . esc_html( $status_pill ) . '$2',
			$html,
			1
		);

		$t_before = get_theme_mod( 'alify_home_title_before', 'We Craft Bold' );
		$t_accent = get_theme_mod( 'alify_home_title_accent', 'Digital Experiences' );
		$t_after  = get_theme_mod( 'alify_home_title_after', 'That Scale Brands.' );
		$new_h1   = esc_html( $t_before ) . ' <span class="hero-title-highlight">' . esc_html( $t_accent ) . '</span> ' . esc_html( $t_after );
		$html     = preg_replace(
			'/<h1 class="hero-display-title">.*?<\/h1>/s',
			'<h1 class="hero-display-title">' . $new_h1 . '</h1>',
			$html,
			1
		);

		$lead = get_theme_mod( 'alify_home_lead', '' );
		if ( ! empty( $lead ) ) {
			$html = preg_replace(
				'/<p class="hero-lead">.*?<\/p>/s',
				'<p class="hero-lead">' . esc_html( $lead ) . '</p>',
				$html,
				1
			);
		}

		$cta_label = get_theme_mod( 'alify_cta_text', 'Start a Project' );
		$sec_label = get_theme_mod( 'alify_home_sec_btn_label', 'Explore Our Work' );
		$html      = preg_replace(
			'/(<div class="hero-cta-group">.*?<span class="btn-start-pill">).*?(<\/span>)/s',
			'$1' . esc_html( $cta_label ) . '$2',
			$html,
			1
		);
		$html      = preg_replace(
			'/(class="btn-hero-secondary">\s*<i[^>]*><\/i>\s*<span>).*?(<\/span>)/s',
			'$1' . esc_html( $sec_label ) . '$2',
			$html,
			1
		);

		// 3 Animated Stat Counters
		$s1_v = get_theme_mod( 'alify_home_stat1_val', '150' );
		$s1_s = get_theme_mod( 'alify_home_stat1_suf', '+' );
		$s1_l = get_theme_mod( 'alify_home_stat1_lbl', 'Projects Delivered' );
		$s2_v = get_theme_mod( 'alify_home_stat2_val', '98' );
		$s2_s = get_theme_mod( 'alify_home_stat2_suf', '%' );
		$s2_l = get_theme_mod( 'alify_home_stat2_lbl', 'Client Retention' );
		$s3_v = get_theme_mod( 'alify_home_stat3_val', '12' );
		$s3_s = get_theme_mod( 'alify_home_stat3_suf', '+' );
		$s3_l = get_theme_mod( 'alify_home_stat3_lbl', 'Global Markets' );

		$stats_html  = '<div class="hero-stat-item"><span class="hero-stat-number" data-count="' . esc_attr( $s1_v ) . '" data-suffix="' . esc_attr( $s1_s ) . '">' . esc_html( $s1_v . $s1_s ) . '</span><span class="hero-stat-label">' . esc_html( $s1_l ) . '</span></div>';
		$stats_html .= '<span class="hero-stat-divider" aria-hidden="true"></span>';
		$stats_html .= '<div class="hero-stat-item"><span class="hero-stat-number" data-count="' . esc_attr( $s2_v ) . '" data-suffix="' . esc_attr( $s2_s ) . '">' . esc_html( $s2_v . $s2_s ) . '</span><span class="hero-stat-label">' . esc_html( $s2_l ) . '</span></div>';
		$stats_html .= '<span class="hero-stat-divider" aria-hidden="true"></span>';
		$stats_html .= '<div class="hero-stat-item"><span class="hero-stat-number" data-count="' . esc_attr( $s3_v ) . '" data-suffix="' . esc_attr( $s3_s ) . '">' . esc_html( $s3_v . $s3_s ) . '</span><span class="hero-stat-label">' . esc_html( $s3_l ) . '</span></div>';

		if ( function_exists( 'alify_replace_container_inner' ) ) {
			$html = alify_replace_container_inner( $html, 'class="hero-stats-bar"', 'div', $stats_html );
		}
	}

	// 2. Per-Page Hero Meta Box & Section Visibility Overrides (`is_page()`)
	if ( is_page() ) {
		$page_id = get_queried_object_id();
		if ( $page_id > 0 ) {
			$p_bg = get_post_meta( $page_id, '_alify_page_hero_bg', true );
			if ( empty( $p_bg ) ) {
				$p_bg = get_the_post_thumbnail_url( $page_id, 'full' );
			}
			if ( ! empty( $p_bg ) ) {
				$html = preg_replace(
					'/(id="innerHeroBg"[^>]*style="background-image:\s*url\([\'"]?)[^\'")]+([\'"]?\);?")/i',
					'$1' . esc_url( $p_bg ) . '$2',
					$html,
					1
				);
			}

			$p_eyebrow = get_post_meta( $page_id, '_alify_page_hero_eyebrow', true );
			if ( ! empty( $p_eyebrow ) ) {
				$html = preg_replace(
					'/(<div class="inner-hero-eyebrow">\s*<span[^>]*><\/span>\s*<span>).*?(<\/span>)/s',
					'$1' . esc_html( $p_eyebrow ) . '$2',
					$html,
					1
				);
			}

			$p_title = get_post_meta( $page_id, '_alify_page_hero_title', true );
			if ( ! empty( $p_title ) ) {
				$html = preg_replace(
					'/<h1([^>]*class="[^"]*inner-hero-title[^"]*"[^>]*)>.*?<\/h1>/s',
					'<h1$1>' . wp_kses_post( $p_title ) . '</h1>',
					$html,
					1
				);
			}

			$p_desc = get_post_meta( $page_id, '_alify_page_hero_desc', true );
			if ( ! empty( $p_desc ) ) {
				$html = preg_replace(
					'/<p([^>]*class="[^"]*inner-hero-desc[^"]*"[^>]*)>.*?<\/p>/s',
					'<p$1>' . esc_html( $p_desc ) . '</p>',
					$html,
					1
				);
			}

			// Section Visibility Toggles
			if ( '1' === (string) get_post_meta( $page_id, '_alify_page_hide_hero_form', true ) ) {
				$html = alify_remove_container_element( $html, 'class="hero-lead-form-card"', 'div' );
				$html = preg_replace( '/<div class="col-12 col-lg-5">\s*<\/div>/s', '', $html, 1 );
				$html = preg_replace(
					'/(<div class="row inner-hero-row">\s*<div class=")col-12 col-lg-7(")/s',
					'$1col-12 col-lg-9$2',
					$html,
					1
				);
			}
			if ( '1' === (string) get_post_meta( $page_id, '_alify_page_hide_testimonials', true ) ) {
				$html = alify_remove_container_element( $html, 'class="testimonials-slider-section', 'section' );
			}
			if ( '1' === (string) get_post_meta( $page_id, '_alify_page_hide_faq', true ) ) {
				$html = alify_remove_container_element( $html, 'class="faq-section"', 'section' );
			}
			if ( '1' === (string) get_post_meta( $page_id, '_alify_page_hide_blog_slider', true ) ) {
				$html = alify_remove_container_element( $html, 'class="home-blog-section"', 'section' );
			}
			if ( '1' === (string) get_post_meta( $page_id, '_alify_page_hide_contact', true ) ) {
				$html = alify_remove_container_element( $html, 'class="reusable-contact-section"', 'section' );
			}
		}
	}

	// 3. Pricing Tiers Customizer Sync (`pricing-tiers-section`)
	if ( false !== strpos( $html, 'pricing-tiers-section' ) ) {
		$save_pill = get_theme_mod( 'alify_pricing_save_pill', 'Save 15%' );
		$html      = preg_replace(
			'/(<span class="pricing-save-pill">).*?(<\/span>)/s',
			'$1' . esc_html( $save_pill ) . '$2',
			$html,
			1
		);

		$plans_map = array(
			'launch-sprint'    => array( 'alify_plan1', 'Launch Sprint', '$4,500', '$3,800', '3–4 Weeks' ),
			'scale-ecosystem'  => array( 'alify_plan2', 'Scale Ecosystem', '$9,500', '$7,900', '6–8 Weeks' ),
			'enterprise-squad' => array( 'alify_plan3', 'Enterprise Squad', '$18,500', '$14,800', '8–12 Weeks' ),
		);

		foreach ( $plans_map as $plan_slug => $defaults ) {
			list( $prefix, $def_name, $def_sprint, $def_retainer, $def_vel ) = $defaults;

			$p_name     = get_theme_mod( $prefix . '_name', $def_name );
			$p_sprint   = get_theme_mod( $prefix . '_sprint', $def_sprint );
			$p_retainer = get_theme_mod( $prefix . '_retainer', $def_retainer );
			$p_vel      = get_theme_mod( $prefix . '_velocity', $def_vel );
			$p_sum      = get_theme_mod( $prefix . '_summary', '' );
			$p_feats    = get_theme_mod( $prefix . '_features', '' );

			$html = preg_replace_callback(
				'/<article[^>]*data-plan="' . preg_quote( $plan_slug, '/' ) . '".*?<\/article>/s',
				function ( $m ) use ( $p_name, $p_sprint, $p_retainer, $p_vel, $p_sum, $p_feats ) {
					$card = $m[0];
					$card = preg_replace( '/data-sprint-price="[^"]*"/', 'data-sprint-price="' . esc_attr( $p_sprint ) . '"', $card, 1 );
					$card = preg_replace( '/data-retainer-price="[^"]*"/', 'data-retainer-price="' . esc_attr( $p_retainer ) . '"', $card, 1 );
					$card = preg_replace( '/data-sprint-velocity="[^"]*"/', 'data-sprint-velocity="' . esc_attr( $p_vel ) . '"', $card, 1 );

					$card = preg_replace( '/(<span class="js-price-velocity">).*?(<\/span>)/s', '$1' . esc_html( $p_vel ) . '$2', $card, 1 );
					$card = preg_replace( '/(<h3 class="pricing-tier-name">).*?(<\/h3>)/s', '$1' . esc_html( $p_name ) . '$2', $card, 1 );
					$card = preg_replace( '/(<span class="pricing-price-amount js-price-amount">).*?(<\/span>)/s', '$1' . esc_html( $p_sprint ) . '$2', $card, 1 );

					if ( ! empty( $p_sum ) ) {
						$card = preg_replace( '/(<p class="pricing-tier-summary">).*?(<\/p>)/s', '$1' . esc_html( $p_sum ) . '$2', $card, 1 );
					}
					if ( ! empty( $p_feats ) && function_exists( 'alify_parse_lines' ) ) {
						$lines = alify_parse_lines( $p_feats );
						if ( ! empty( $lines ) ) {
							$lis = '';
							foreach ( $lines as $ln ) {
								$lis .= '<li class="pricing-feature-item"><span class="pricing-feature-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span>' . esc_html( $ln ) . '</span></li>';
							}
							$card = preg_replace( '/(<ul class="pricing-feature-list">).*?(<\/ul>)/s', '$1' . $lis . '$2', $card, 1 );
						}
					}
					return $card;
				},
				$html,
				1
			);
		}
	}

	return $html;
}
