<?php
/**
 * ACF Free Frontend Render Bridge (`alify_opt()` + Section-Scoped HTML Overrides).
 * Connects all 4 `ALIFY Theme Settings` screens (`global`, `home`, `archives`, `singles`)
 * directly to Home, Archive/Hub Pages, Single Templates, Header, Footer & Brand Logo.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve a Theme Settings Field Value With Fallback (Used in Header, Footer, Blog & Single PHP Templates).
 *
 * @param string $screen_key `global`, `home`, `archives`, or `singles`.
 * @param string $field_name ACF field name.
 * @param mixed  $fallback   Default value when empty.
 * @return mixed
 */
function alify_opt( $screen_key, $field_name, $fallback = '' ) {
	if ( ! function_exists( 'alify_get_settings_post_id' ) ) {
		return $fallback;
	}
	$pid = alify_get_settings_post_id( $screen_key );
	if ( $pid <= 0 || ! metadata_exists( 'post', $pid, $field_name ) ) {
		return $fallback;
	}

	$raw = get_post_meta( $pid, $field_name, true );
	if ( '' === $raw || null === $raw || false === $raw ) {
		return $fallback;
	}

	// Resolve Attachment IDs for image fields automatically
	if ( is_numeric( $raw ) && (int) $raw > 0 && preg_match( '/(_bg|_image|_img|_logo|_avatar)$/', $field_name ) ) {
		$img_url = wp_get_attachment_image_url( (int) $raw, 'full' );
		return $img_url ? $img_url : $fallback;
	}

	if ( function_exists( 'get_field' ) ) {
		$formatted = get_field( $field_name, $pid );
		if ( is_array( $formatted ) && ! empty( $formatted['url'] ) ) {
			return $formatted['url'];
		}
		if ( null !== $formatted && '' !== $formatted && false !== $formatted ) {
			return $formatted;
		}
	}

	return $raw;
}

/**
 * Retrieve a Theme Settings Field ONLY When Explicitly Saved in WP Admin (`''` otherwise).
 */
function alify_opt_saved( $screen_key, $field_name ) {
	$val = alify_opt( $screen_key, $field_name, '' );
	return is_scalar( $val ) ? trim( (string) $val ) : '';
}

/**
 * Section-Scoped Callback Helper (Zero Nested `<section>` Tags Across All 22 Templates).
 */
function alify_acf_in_section( $html, $section_class, $callback ) {
	$pattern = '/(<section\b[^>]*class="[^"]*\b' . preg_quote( $section_class, '/' ) . '\b[^"]*"[^>]*>)([\s\S]*?)(<\/section>)/';
	return preg_replace_callback(
		$pattern,
		function ( $m ) use ( $callback ) {
			return $m[1] . call_user_func( $callback, $m[2] ) . $m[3];
		},
		$html,
		1
	);
}

/**
 * Replace Eyebrow Text Inside a Section Body (`<div class="...eyebrow...">...<span>TEXT</span></div>`).
 */
function alify_acf_set_eyebrow( $sec_body, $text ) {
	if ( '' === $text ) {
		return $sec_body;
	}
	return preg_replace(
		'/(<div\s+class="[^"]*eyebrow[^"]*"[^>]*>[\s\S]*?<span[^>]*>[\s\S]*?<\/span>\s*<span>)[\s\S]*?(<\/span>)/',
		'${1}' . esc_html( $text ) . '${2}',
		$sec_body,
		1
	);
}

/**
 * Replace First Matching Tag by Class Inside a Section Body (`<span>` Allowed for Accent Highlights).
 */
function alify_acf_set_tag_by_class( $sec_body, $tag, $class_fragment, $content, $allow_span = true ) {
	if ( '' === $content ) {
		return $sec_body;
	}
	$safe = $allow_span
		? wp_kses( $content, array( 'span' => array( 'class' => array() ), 'br' => array() ) )
		: esc_html( $content );

	return preg_replace(
		'/(<' . preg_quote( $tag, '/' ) . '\b[^>]*class="[^"]*' . preg_quote( $class_fragment, '/' ) . '[^"]*"[^>]*>)[\s\S]*?(<\/' . preg_quote( $tag, '/' ) . '>)/',
		'${1}' . $safe . '${2}',
		$sec_body,
		1
	);
}

/**
 * Apply Inner-Page Hero Defaults From `Archive & Hub Pages` Settings (Unless Overridden Per-Page!).
 */
function alify_acf_apply_inner_hero_defaults( $html, $page_id, $eyebrow, $title, $desc, $bg ) {
	if ( $page_id <= 0 || '' === (string) get_post_meta( $page_id, '_alify_page_hero_eyebrow', true ) ) {
		if ( '' !== $eyebrow ) {
			$html = preg_replace(
				'/(<div\s+class="inner-hero-eyebrow"[^>]*>[\s\S]*?<span\s+class="inner-hero-eyebrow-dot"[^>]*><\/span>\s*<span>)[\s\S]*?(<\/span>)/',
				'${1}' . esc_html( $eyebrow ) . '${2}',
				$html,
				1
			);
		}
	}

	if ( $page_id <= 0 || '' === (string) get_post_meta( $page_id, '_alify_page_hero_title', true ) ) {
		if ( '' !== $title ) {
			$safe_t = wp_kses( $title, array( 'span' => array( 'class' => array() ), 'br' => array() ) );
			$html   = preg_replace(
				'/(<h1\s+class="inner-hero-title"[^>]*>)[\s\S]*?(<\/h1>)/',
				'${1}' . $safe_t . '${2}',
				$html,
				1
			);
		}
	}

	if ( $page_id <= 0 || '' === (string) get_post_meta( $page_id, '_alify_page_hero_desc', true ) ) {
		if ( '' !== $desc ) {
			$html = preg_replace(
				'/(<p\s+class="inner-hero-desc"[^>]*>)[\s\S]*?(<\/p>)/',
				'${1}' . esc_html( $desc ) . '${2}',
				$html,
				1
			);
		}
	}

	if ( $page_id <= 0 || '' === (string) get_post_meta( $page_id, '_alify_page_hero_bg', true ) ) {
		if ( '' !== $bg ) {
			$html = preg_replace(
				'/(id="innerHeroBg"[^>]*style="background-image:\s*url\(\')[^\']*(\'\);")/',
				'${1}' . esc_url( $bg ) . '${2}',
				$html,
				1
			);
		}
	}

	return $html;
}

/**
 * Master ACF Theme Settings Override Pipeline Called Inside `alify_render_page_part()`.
 *
 * @param string $html      Rendered template markup.
 * @param string $part_slug Page part slug (`home`, `services`, `work`, `service-single`, etc.).
 * @return string
 */
function alify_apply_acf_theme_overrides( $html, $part_slug = '' ) {
	$page_id = is_singular( 'page' ) ? get_queried_object_id() : 0;

	// =========================================================================
	// 1. GLOBAL REUSABLE SECTIONS (Testimonials, FAQs, Blog Slider, Contact)
	// =========================================================================
	$g_testi_eb = alify_opt_saved( 'global', 'opt_testi_eyebrow' );
	$g_testi_tt = alify_opt_saved( 'global', 'opt_testi_title' );
	if ( '' !== $g_testi_eb || '' !== $g_testi_tt ) {
		$html = alify_acf_in_section(
			$html,
			'testimonials-slider-section',
			function ( $b ) use ( $g_testi_eb, $g_testi_tt ) {
				$b = alify_acf_set_eyebrow( $b, $g_testi_eb );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'testimonials-sec-title', $g_testi_tt );
				return $b;
			}
		);
	}

	$g_faq_eb = alify_opt_saved( 'global', 'opt_faq_eyebrow' );
	$g_faq_tt = alify_opt_saved( 'global', 'opt_faq_title' );
	$g_faq_ds = alify_opt_saved( 'global', 'opt_faq_desc' );
	if ( '' !== $g_faq_eb || '' !== $g_faq_tt || '' !== $g_faq_ds ) {
		$html = alify_acf_in_section(
			$html,
			'faq-section',
			function ( $b ) use ( $g_faq_eb, $g_faq_tt, $g_faq_ds ) {
				$b = alify_acf_set_eyebrow( $b, $g_faq_eb );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'faq-title', $g_faq_tt );
				$b = alify_acf_set_tag_by_class( $b, 'p', 'faq-desc', $g_faq_ds, false );
				return $b;
			}
		);
	}

	$g_blg_eb = alify_opt_saved( 'global', 'opt_blog_eyebrow' );
	$g_blg_tt = alify_opt_saved( 'global', 'opt_blog_title' );
	if ( '' !== $g_blg_eb || '' !== $g_blg_tt ) {
		$html = alify_acf_in_section(
			$html,
			'home-blog-section',
			function ( $b ) use ( $g_blg_eb, $g_blg_tt ) {
				$b = alify_acf_set_eyebrow( $b, $g_blg_eb );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'blog-section-title', $g_blg_tt );
				return $b;
			}
		);
	}

	$g_cnt_eb = alify_opt_saved( 'global', 'opt_rcontact_eyebrow' );
	$g_cnt_tt = alify_opt_saved( 'global', 'opt_rcontact_title' );
	$g_cnt_ds = alify_opt_saved( 'global', 'opt_rcontact_desc' );
	if ( '' !== $g_cnt_eb || '' !== $g_cnt_tt || '' !== $g_cnt_ds ) {
		$html = alify_acf_in_section(
			$html,
			'reusable-contact-section',
			function ( $b ) use ( $g_cnt_eb, $g_cnt_tt, $g_cnt_ds ) {
				$b = alify_acf_set_eyebrow( $b, $g_cnt_eb );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'contact-sec-title', $g_cnt_tt );
				$b = alify_acf_set_tag_by_class( $b, 'p', 'contact-sec-desc', $g_cnt_ds, false );
				return $b;
			}
		);
	}

	// =========================================================================
	// 2. HOME PAGE CONTENT (`home`)
	// =========================================================================
	if ( 'home' === $part_slug ) {
		$h_bg = alify_opt_saved( 'home', 'home_hero_bg' );
		if ( '' !== $h_bg ) {
			$html = preg_replace(
				'/(id="homeHeroBg"[^>]*style="background-image:\s*url\(\')[^\']*(\'\);")/',
				'${1}' . esc_url( $h_bg ) . '${2}',
				$html,
				1
			);
		}

		$h_pill = alify_opt_saved( 'home', 'home_status_pill' );
		if ( '' !== $h_pill ) {
			$html = preg_replace(
				'/(<div\s+class="hero-status-pill"[^>]*>[\s\S]*?<span\s+class="hero-status-dot"[^>]*><\/span>\s*<span>)[\s\S]*?(<\/span>)/',
				'${1}' . esc_html( $h_pill ) . '${2}',
				$html,
				1
			);
		}

		$tb = alify_opt_saved( 'home', 'home_title_before' );
		$ta = alify_opt_saved( 'home', 'home_title_accent' );
		$tf = alify_opt_saved( 'home', 'home_title_after' );
		if ( '' !== $tb || '' !== $ta || '' !== $tf ) {
			$new_h1 = esc_html( $tb ? $tb : 'We Craft Bold' ) . ' <span class="hero-title-accent">' . esc_html( $ta ? $ta : 'Digital Experiences' ) . '</span> ' . esc_html( $tf ? $tf : 'That Scale Brands.' );
			$html   = preg_replace( '/(<h1\s+class="hero-display-title"[^>]*>)[\s\S]*?(<\/h1>)/', '${1}' . $new_h1 . '${2}', $html, 1 );
		}

		$h_lead = alify_opt_saved( 'home', 'home_lead' );
		if ( '' !== $h_lead ) {
			$html = preg_replace( '/(<p\s+class="hero-lead-text"[^>]*>)[\s\S]*?(<\/p>)/', '${1}' . esc_html( $h_lead ) . '${2}', $html, 1 );
		}

		$h_pbtn = alify_opt_saved( 'home', 'home_primary_btn' );
		if ( '' !== $h_pbtn ) {
			$html = preg_replace(
				'/(<div\s+class="hero-cta-row">[\s\S]*?<span\s+class="btn-start-pill">)[\s\S]*?(<\/span>)/',
				'${1}' . esc_html( $h_pbtn ) . '${2}',
				$html,
				1
			);
		}

		$h_sbtn = alify_opt_saved( 'home', 'home_sec_btn' );
		if ( '' !== $h_sbtn ) {
			$html = preg_replace(
				'/(<a[^>]*class="btn-hero-secondary"[^>]*>\s*<span>)[\s\S]*?(<\/span>)/',
				'${1}' . esc_html( $h_sbtn ) . '${2}',
				$html,
				1
			);
		}

		// Home Hero Stats 1..3
		$stat_idx = 0;
		$html     = preg_replace_callback(
			'/<div\s+class="hero-metric-item">[\s\S]*?<\/div>\s*(?=<div\s+class="hero-metric-divider"|<\/div>)/',
			function ( $m ) use ( &$stat_idx ) {
				$stat_idx++;
				if ( $stat_idx > 3 ) {
					return $m[0];
				}
				$v = alify_opt_saved( 'home', 'home_stat' . $stat_idx . '_val' );
				$s = alify_opt_saved( 'home', 'home_stat' . $stat_idx . '_suf' );
				$l = alify_opt_saved( 'home', 'home_stat' . $stat_idx . '_lbl' );
				if ( '' === $v && '' === $s && '' === $l ) {
					return $m[0];
				}
				$num = '' !== $v ? $v : '150';
				$suf = '' !== $s ? $s : '+';
				$lbl = '' !== $l ? $l : 'Projects Delivered';
				return '<div class="hero-metric-item">'
					. '<span class="hero-metric-value" data-count="' . esc_attr( $num ) . '" data-suffix="' . esc_attr( $suf ) . '">' . esc_html( $num . $suf ) . '</span>'
					. '<span class="hero-metric-label">' . esc_html( $lbl ) . '</span>'
					. '</div>';
			},
			$html
		);

		// Why ALIFY Section
		$html = alify_acf_in_section(
			$html,
			'home-why-section',
			function ( $b ) {
				$b   = alify_acf_set_eyebrow( $b, alify_opt_saved( 'home', 'home_why_eyebrow' ) );
				$b   = alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'home', 'home_why_title' ) );
				$b   = alify_acf_set_tag_by_class( $b, 'p', 'about-story-p', alify_opt_saved( 'home', 'home_why_desc' ), false );
				$img = alify_opt_saved( 'home', 'home_why_image' );
				if ( '' !== $img ) {
					$b = preg_replace( '/(<img\b[^>]*src=")[^"]*(")/', '${1}' . esc_url( $img ) . '${2}', $b, 1 );
				}
				return $b;
			}
		);

		// Home Sliders & Showcase Section Headings
		$sec_pairs = array(
			'home-services-section'     => array( 'home_srv_eyebrow', 'home_srv_title', 'services-sec-title' ),
			'home-industries-section'   => array( 'home_ind_eyebrow', 'home_ind_title', 'industries-sec-title' ),
			'home-projects-section'     => array( 'home_prj_eyebrow', 'home_prj_title', 'projects-sec-title' ),
			'home-case-studies-section' => array( 'home_cs_eyebrow', 'home_cs_title', 'case-studies-sec-title' ),
		);
		foreach ( $sec_pairs as $s_cls => $keys ) {
			$eb = alify_opt_saved( 'home', $keys[0] );
			$tt = alify_opt_saved( 'home', $keys[1] );
			if ( '' !== $eb || '' !== $tt ) {
				$t_cls = $keys[2];
				$html  = alify_acf_in_section(
					$html,
					$s_cls,
					function ( $b ) use ( $eb, $tt, $t_cls ) {
						$b = alify_acf_set_eyebrow( $b, $eb );
						$b = alify_acf_set_tag_by_class( $b, 'h2', $t_cls, $tt );
						return $b;
					}
				);
			}
		}

		// 4-Sprint Process Section
		$html = alify_acf_in_section(
			$html,
			'studio-process-section',
			function ( $b ) {
				$b    = alify_acf_set_eyebrow( $b, alify_opt_saved( 'home', 'home_proc_eyebrow' ) );
				$b    = alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'home', 'home_proc_title' ) );
				$b    = alify_acf_set_tag_by_class( $b, 'p', 'studio-sec-desc', alify_opt_saved( 'home', 'home_proc_desc' ), false );
				$step = 0;
				$b    = preg_replace_callback(
					'/<article\s+class="studio-process-card">[\s\S]*?<\/article>/',
					function ( $cm ) use ( &$step ) {
						$step++;
						$st = alify_opt_saved( 'home', 'home_proc_s' . $step . '_title' );
						$sd = alify_opt_saved( 'home', 'home_proc_s' . $step . '_desc' );
						$c  = $cm[0];
						$c  = alify_acf_set_tag_by_class( $c, 'h3', 'studio-process-title', $st, false );
						$c  = alify_acf_set_tag_by_class( $c, 'p', 'studio-process-desc', $sd, false );
						return $c;
					},
					$b
				);
				return $b;
			}
		);
	}

	// =========================================================================
	// 3. ARCHIVE & HUB PAGES (`archives`)
	// =========================================================================
	$hero_archive_map = array(
		'services'      => array( 'arch_srv_hero_eyebrow', 'arch_srv_hero_title', 'arch_srv_hero_desc', 'arch_srv_hero_bg' ),
		'work'          => array( 'arch_wrk_hero_eyebrow', 'arch_wrk_hero_title', 'arch_wrk_hero_desc', 'arch_wrk_hero_bg' ),
		'industries'    => array( 'arch_ind_hero_eyebrow', 'arch_ind_hero_title', 'arch_ind_hero_desc', 'arch_ind_hero_bg' ),
		'case-studies'  => array( 'arch_cs_hero_eyebrow', 'arch_cs_hero_title', 'arch_cs_hero_desc', 'arch_cs_hero_bg' ),
		'blog'          => array( 'arch_blog_hero_eyebrow', 'arch_blog_hero_title', 'arch_blog_hero_desc', 'arch_blog_hero_bg' ),
		'pricing'       => array( 'arch_prc_hero_eyebrow', 'arch_prc_hero_title', 'arch_prc_hero_desc', 'arch_prc_hero_bg' ),
		'about'         => array( 'arch_abt_hero_eyebrow', 'arch_abt_hero_title', 'arch_abt_hero_desc', 'arch_abt_hero_bg' ),
		'testimonials'  => array( '', 'arch_tst_hero_title', '', '' ),
		'faq'           => array( '', 'arch_faq_hero_title', '', '' ),
		'contact'       => array( '', 'arch_cnt_hero_title', '', '' ),
		'start-project' => array( '', 'arch_sp_hero_title', '', '' ),
		'book-call'     => array( '', 'arch_bc_hero_title', '', '' ),
	);

	if ( isset( $hero_archive_map[ $part_slug ] ) ) {
		$hm   = $hero_archive_map[ $part_slug ];
		$html = alify_acf_apply_inner_hero_defaults(
			$html,
			$page_id,
			$hm[0] ? alify_opt_saved( 'archives', $hm[0] ) : '',
			$hm[1] ? alify_opt_saved( 'archives', $hm[1] ) : '',
			$hm[2] ? alify_opt_saved( 'archives', $hm[2] ) : '',
			$hm[3] ? alify_opt_saved( 'archives', $hm[3] ) : ''
		);
	}

	if ( 'services' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'services-grid-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'services-sec-title', alify_opt_saved( 'archives', 'arch_srv_sec_title' ) );
			}
		);
	} elseif ( 'work' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'projects-grid-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'projects-sec-title', alify_opt_saved( 'archives', 'arch_wrk_sec_title' ) );
			}
		);
	} elseif ( 'industries' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'industries-grid-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'industries-sec-title', alify_opt_saved( 'archives', 'arch_ind_sec_title' ) );
			}
		);
	} elseif ( 'pricing' === $part_slug ) {
		$save_pill = alify_opt_saved( 'archives', 'arch_prc_save_pill' );
		if ( '' !== $save_pill ) {
			$html = alify_acf_set_tag_by_class( $html, 'span', 'pricing-save-pill', $save_pill, false );
		}
		$plans = array(
			1 => 'launch-sprint',
			2 => 'scale-ecosystem',
			3 => 'enterprise-squad',
		);
		foreach ( $plans as $ti => $plan_slug ) {
			$p_name = alify_opt_saved( 'archives', 'prc_t' . $ti . '_name' );
			$p_sp   = alify_opt_saved( 'archives', 'prc_t' . $ti . '_sprint' );
			$p_rt   = alify_opt_saved( 'archives', 'prc_t' . $ti . '_retainer' );
			$p_vel  = alify_opt_saved( 'archives', 'prc_t' . $ti . '_velocity' );
			$p_sum  = alify_opt_saved( 'archives', 'prc_t' . $ti . '_summary' );
			$p_feat = alify_opt_saved( 'archives', 'prc_t' . $ti . '_features' );

			if ( '' !== $p_name || '' !== $p_sp || '' !== $p_rt || '' !== $p_vel || '' !== $p_sum || '' !== $p_feat ) {
				$html = preg_replace_callback(
					'/<article\b[^>]*data-plan="' . preg_quote( $plan_slug, '/' ) . '"[\s\S]*?<\/article>/',
					function ( $cm ) use ( $p_name, $p_sp, $p_rt, $p_vel, $p_sum, $p_feat ) {
						$card = $cm[0];
						if ( '' !== $p_sp ) {
							$card = preg_replace( '/data-sprint-price="[^"]*"/', 'data-sprint-price="' . esc_attr( $p_sp ) . '"', $card, 1 );
							$card = alify_acf_set_tag_by_class( $card, 'span', 'js-price-amount', $p_sp, false );
						}
						if ( '' !== $p_rt ) {
							$card = preg_replace( '/data-retainer-price="[^"]*"/', 'data-retainer-price="' . esc_attr( $p_rt ) . '"', $card, 1 );
						}
						if ( '' !== $p_vel ) {
							$card = preg_replace( '/data-sprint-velocity="[^"]*"/', 'data-sprint-velocity="' . esc_attr( $p_vel ) . '"', $card, 1 );
							$card = alify_acf_set_tag_by_class( $card, 'span', 'js-price-velocity', $p_vel, false );
						}
						$card = alify_acf_set_tag_by_class( $card, 'h3', 'pricing-tier-name', $p_name, false );
						$card = alify_acf_set_tag_by_class( $card, 'p', 'pricing-tier-summary', $p_sum, false );
						if ( '' !== $p_feat ) {
							$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $p_feat ) ) );
							if ( ! empty( $lines ) ) {
								$li_html = '';
								foreach ( $lines as $ln ) {
									$li_html .= '<li class="pricing-feature-item"><span class="pricing-feature-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span>' . esc_html( $ln ) . '</span></li>';
								}
								$card = preg_replace( '/(<ul\s+class="pricing-feature-list">)[\s\S]*?(<\/ul>)/', '${1}' . $li_html . '${2}', $card, 1 );
							}
						}
						return $card;
					},
					$html,
					1
				);
			}
		}
	} elseif ( 'about' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'about-story-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'archives', 'arch_abt_story_title' ) );
			}
		);
		$html = alify_acf_in_section(
			$html,
			'about-team-section',
			function ( $b ) {
				$b  = alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'archives', 'arch_abt_team_title' ) );
				$li = 0;
				$b  = preg_replace_callback(
					'/<article\s+class="about-leader-card">[\s\S]*?<\/article>/',
					function ( $cm ) use ( &$li ) {
						$li++;
						$c    = $cm[0];
						$img  = alify_opt_saved( 'archives', 'abt_leader_' . $li . '_img' );
						$name = alify_opt_saved( 'archives', 'abt_leader_' . $li . '_name' );
						$role = alify_opt_saved( 'archives', 'abt_leader_' . $li . '_role' );
						$bio  = alify_opt_saved( 'archives', 'abt_leader_' . $li . '_bio' );
						if ( '' !== $img ) {
							$c = preg_replace( '/(<img\b[^>]*src=")[^"]*(")/', '${1}' . esc_url( $img ) . '${2}', $c, 1 );
						}
						$c = alify_acf_set_tag_by_class( $c, 'h3', 'about-leader-name', $name, false );
						$c = alify_acf_set_tag_by_class( $c, 'span', 'about-leader-role', $role, false );
						$c = alify_acf_set_tag_by_class( $c, 'p', 'about-leader-bio', $bio, false );
						return $c;
					},
					$b
				);
				return $b;
			}
		);
	} elseif ( 'blog' === $part_slug ) {
		$html = alify_acf_set_tag_by_class( $html, 'div', 'inner-hero-card-metric', alify_opt_saved( 'archives', 'arch_blog_card_metric' ), false );
		$html = alify_acf_set_tag_by_class( $html, 'p', 'inner-hero-card-label', alify_opt_saved( 'archives', 'arch_blog_card_label' ), false );
		$b_cta = alify_opt_saved( 'archives', 'arch_blog_card_cta' );
		if ( '' !== $b_cta ) {
			$html = preg_replace( '/(<a\b[^>]*class="[^"]*inner-hero-card-cta[^"]*"[^>]*>\s*<span>)[\s\S]*?(<\/span>)/', '${1}' . esc_html( $b_cta ) . '${2}', $html, 1 );
		}
	} elseif ( 'book-call' === $part_slug ) {
		$html = alify_acf_set_tag_by_class( $html, 'h2', 'book-host-name', alify_opt_saved( 'archives', 'arch_bc_host_name' ), false );
		$html = alify_acf_set_tag_by_class( $html, 'span', 'book-host-role', alify_opt_saved( 'archives', 'arch_bc_host_role' ), false );
	} elseif ( 'thank-you' === $part_slug ) {
		$html = alify_acf_set_tag_by_class( $html, 'h1', 'thankyou-title', alify_opt_saved( 'archives', 'arch_ty_title' ) );
	} elseif ( '404' === $part_slug ) {
		$html = alify_acf_set_tag_by_class( $html, 'h1', 'error-stage-title', alify_opt_saved( 'archives', 'arch_404_title' ), false );
	}

	// =========================================================================
	// 4. SINGLE TEMPLATES GLOBAL (`singles`)
	// =========================================================================
	if ( 'service-single' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				$b = alify_acf_set_eyebrow( $b, alify_opt_saved( 'singles', 'sgl_srv_overview_eyebrow' ) );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_srv_overview_title' ) );
				return $b;
			}
		);
		$html = alify_acf_in_section(
			$html,
			'studio-process-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_srv_process_title' ) );
			}
		);
		$html = alify_acf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_srv_related_title' ) );
			}
		);
	} elseif ( 'work-single' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				$b = alify_acf_set_eyebrow( $b, alify_opt_saved( 'singles', 'sgl_wrk_related_eyebrow' ) );
				$b = alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_wrk_related_title' ) );
				return $b;
			}
		);
	} elseif ( 'industry-single' === $part_slug ) {
		$html = alify_acf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_ind_overview_title' ) );
			}
		);
		$html = alify_acf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_ind_related_title' ) );
			}
		);
	} elseif ( 'case-study-single' === $part_slug ) {
		$html = alify_acf_set_tag_by_class( $html, 'span', 'single-snapshot-heading', alify_opt_saved( 'singles', 'sgl_cs_snapshot_heading' ), false );
		$html = alify_acf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_acf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_opt_saved( 'singles', 'sgl_cs_related_title' ) );
			}
		);
	}

	return $html;
}
