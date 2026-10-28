<?php
/**
 * Frontend Render Bridge for `Alify Custom Field` Options Pages & Post Fields.
 * Connects `site`, `alify_home`, `alify_archives`, `alify_pages`, and `alify_singles`
 * directly to Home, Archive/Hub Pages, Single Templates, Header, Footer & Brand Logo.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read Raw Option Data Array for an `Alify Custom Field` Options Page (`site`, `alify_home`, etc.).
 */
function alify_cf_page_data( $page = 'site' ) {
	if ( function_exists( 'alify_get_option' ) && class_exists( 'Alify_CF_Options' ) ) {
		return Alify_CF_Options::data( $page );
	}
	$raw = get_option( 'alify_cf_options_' . $page, array() );
	return is_array( $raw ) ? $raw : array();
}

/**
 * Retrieve a Scalar Option Value With Fallback.
 */
function alify_cf_opt( $page, $name, $fallback = '' ) {
	$data = alify_cf_page_data( $page );
	if ( ! array_key_exists( $name, $data ) || '' === $data[ $name ] || null === $data[ $name ] ) {
		return $fallback;
	}
	return is_scalar( $data[ $name ] ) ? (string) $data[ $name ] : $fallback;
}

/**
 * Retrieve a Scalar Option ONLY When Explicitly Saved (`''` Otherwise).
 */
function alify_cf_opt_saved( $page, $name ) {
	return trim( (string) alify_cf_opt( $page, $name, '' ) );
}

/**
 * Retrieve an Image Option URL (Resolves Numeric Attachment IDs From `Alify Custom Field` Image Picker).
 */
function alify_cf_opt_img( $page, $name, $fallback = '' ) {
	$data = alify_cf_page_data( $page );
	if ( ! isset( $data[ $name ] ) || '' === $data[ $name ] ) {
		return $fallback;
	}
	$val = $data[ $name ];
	if ( is_numeric( $val ) && (int) $val > 0 ) {
		$u = wp_get_attachment_image_url( (int) $val, 'full' );
		return $u ? $u : $fallback;
	}
	return is_string( $val ) ? $val : $fallback;
}

/**
 * Retrieve Saved Repeater Rows From an Options Page.
 */
function alify_cf_opt_rows( $page, $name ) {
	$data = alify_cf_page_data( $page );
	if ( empty( $data[ $name ] ) || ! is_array( $data[ $name ] ) ) {
		return array();
	}
	return array_values( array_filter( $data[ $name ], 'is_array' ) );
}

/**
 * Section-Scoped HTML Replacement Helpers.
 */
function alify_cf_in_section( $html, $section_class, $callback ) {
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

function alify_cf_set_eyebrow( $sec_body, $text ) {
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

function alify_cf_set_tag_by_class( $sec_body, $tag, $class_fragment, $content, $allow_span = true ) {
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

function alify_cf_apply_inner_hero_defaults( $html, $page_id, $eyebrow, $title, $desc, $bg ) {
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
			$html   = preg_replace( '/(<h1\s+class="inner-hero-title"[^>]*>)[\s\S]*?(<\/h1>)/', '${1}' . $safe_t . '${2}', $html, 1 );
		}
	}
	if ( $page_id <= 0 || '' === (string) get_post_meta( $page_id, '_alify_page_hero_desc', true ) ) {
		if ( '' !== $desc ) {
			$html = preg_replace( '/(<p\s+class="inner-hero-desc"[^>]*>)[\s\S]*?(<\/p>)/', '${1}' . esc_html( $desc ) . '${2}', $html, 1 );
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
 * Master Render Pipeline Applying All `Alify Custom Field` Options Pages to Template Markup.
 */
function alify_apply_cf_options_overrides( $html, $part_slug = '' ) {
	$page_id = is_singular( 'page' ) ? get_queried_object_id() : 0;

	// =========================================================================
	// 1. GLOBAL REUSABLE SECTIONS (`site`)
	// =========================================================================
	$g_testi_eb = alify_cf_opt_saved( 'site', 'opt_testi_eyebrow' );
	$g_testi_tt = alify_cf_opt_saved( 'site', 'opt_testi_title' );
	if ( '' !== $g_testi_eb || '' !== $g_testi_tt ) {
		$html = alify_cf_in_section(
			$html,
			'testimonials-slider-section',
			function ( $b ) use ( $g_testi_eb, $g_testi_tt ) {
				$b = alify_cf_set_eyebrow( $b, $g_testi_eb );
				return alify_cf_set_tag_by_class( $b, 'h2', 'testimonials-sec-title', $g_testi_tt );
			}
		);
	}

	$g_faq_eb = alify_cf_opt_saved( 'site', 'opt_faq_eyebrow' );
	$g_faq_tt = alify_cf_opt_saved( 'site', 'opt_faq_title' );
	$g_faq_ds = alify_cf_opt_saved( 'site', 'opt_faq_desc' );
	if ( '' !== $g_faq_eb || '' !== $g_faq_tt || '' !== $g_faq_ds ) {
		$html = alify_cf_in_section(
			$html,
			'faq-section',
			function ( $b ) use ( $g_faq_eb, $g_faq_tt, $g_faq_ds ) {
				$b = alify_cf_set_eyebrow( $b, $g_faq_eb );
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'faq-title', $g_faq_tt );
				return alify_cf_set_tag_by_class( $b, 'p', 'faq-desc', $g_faq_ds, false );
			}
		);
	}

	$g_blg_eb = alify_cf_opt_saved( 'site', 'opt_blog_eyebrow' );
	$g_blg_tt = alify_cf_opt_saved( 'site', 'opt_blog_title' );
	if ( '' !== $g_blg_eb || '' !== $g_blg_tt ) {
		$html = alify_cf_in_section(
			$html,
			'home-blog-section',
			function ( $b ) use ( $g_blg_eb, $g_blg_tt ) {
				$b = alify_cf_set_eyebrow( $b, $g_blg_eb );
				return alify_cf_set_tag_by_class( $b, 'h2', 'blog-section-title', $g_blg_tt );
			}
		);
	}

	$g_cnt_eb = alify_cf_opt_saved( 'site', 'opt_rcontact_eyebrow' );
	$g_cnt_tt = alify_cf_opt_saved( 'site', 'opt_rcontact_title' );
	$g_cnt_ds = alify_cf_opt_saved( 'site', 'opt_rcontact_desc' );
	if ( '' !== $g_cnt_eb || '' !== $g_cnt_tt || '' !== $g_cnt_ds ) {
		$html = alify_cf_in_section(
			$html,
			'reusable-contact-section',
			function ( $b ) use ( $g_cnt_eb, $g_cnt_tt, $g_cnt_ds ) {
				$b = alify_cf_set_eyebrow( $b, $g_cnt_eb );
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'contact-sec-title', $g_cnt_tt );
				return alify_cf_set_tag_by_class( $b, 'p', 'contact-sec-desc', $g_cnt_ds, false );
			}
		);
	}

	// =========================================================================
	// 2. HOME PAGE CONTENT (`alify_home`)
	// =========================================================================
	if ( 'home' === $part_slug ) {
		$h_bg = alify_cf_opt_img( 'alify_home', 'home_hero_bg', '' );
		if ( '' !== $h_bg ) {
			$html = preg_replace(
				'/(id="homeHeroBg"[^>]*style="background-image:\s*url\(\')[^\']*(\'\);")/',
				'${1}' . esc_url( $h_bg ) . '${2}',
				$html,
				1
			);
		}

		$h_pill = alify_cf_opt_saved( 'alify_home', 'home_status_pill' );
		if ( '' !== $h_pill ) {
			$html = preg_replace(
				'/(<div\s+class="hero-status-pill"[^>]*>[\s\S]*?<span\s+class="hero-status-dot"[^>]*><\/span>\s*<span>)[\s\S]*?(<\/span>)/',
				'${1}' . esc_html( $h_pill ) . '${2}',
				$html,
				1
			);
		}

		$tb = alify_cf_opt_saved( 'alify_home', 'home_title_before' );
		$ta = alify_cf_opt_saved( 'alify_home', 'home_title_accent' );
		$tf = alify_cf_opt_saved( 'alify_home', 'home_title_after' );
		if ( '' !== $tb || '' !== $ta || '' !== $tf ) {
			$new_h1 = esc_html( $tb ? $tb : 'We Craft Bold' ) . ' <span class="hero-title-accent">' . esc_html( $ta ? $ta : 'Digital Experiences' ) . '</span> ' . esc_html( $tf ? $tf : 'That Scale Brands.' );
			$html   = preg_replace( '/(<h1\s+class="hero-display-title"[^>]*>)[\s\S]*?(<\/h1>)/', '${1}' . $new_h1 . '${2}', $html, 1 );
		}

		$h_lead = alify_cf_opt_saved( 'alify_home', 'home_lead' );
		if ( '' !== $h_lead ) {
			$html = preg_replace( '/(<p\s+class="hero-lead-text"[^>]*>)[\s\S]*?(<\/p>)/', '${1}' . esc_html( $h_lead ) . '${2}', $html, 1 );
		}

		$h_pbtn = alify_cf_opt_saved( 'alify_home', 'home_primary_btn' );
		if ( '' !== $h_pbtn ) {
			$html = preg_replace( '/(<div\s+class="hero-cta-row">[\s\S]*?<span\s+class="btn-start-pill">)[\s\S]*?(<\/span>)/', '${1}' . esc_html( $h_pbtn ) . '${2}', $html, 1 );
		}

		$h_sbtn = alify_cf_opt_saved( 'alify_home', 'home_sec_btn' );
		if ( '' !== $h_sbtn ) {
			$html = preg_replace( '/(<a[^>]*class="btn-hero-secondary"[^>]*>\s*<span>)[\s\S]*?(<\/span>)/', '${1}' . esc_html( $h_sbtn ) . '${2}', $html, 1 );
		}

		// Home Hero Stats Repeater (`home_stats_rows`)
		$stat_rows = alify_cf_opt_rows( 'alify_home', 'home_stats_rows' );
		if ( ! empty( $stat_rows ) ) {
			$si   = 0;
			$html = preg_replace_callback(
				'/<div\s+class="hero-metric-item">[\s\S]*?<\/div>\s*(?=<div\s+class="hero-metric-divider"|<\/div>)/',
				function ( $m ) use ( $stat_rows, &$si ) {
					if ( ! isset( $stat_rows[ $si ] ) ) {
						$si++;
						return $m[0];
					}
					$r = $stat_rows[ $si ];
					$si++;
					$num = trim( (string) ( $r['num'] ?? '150' ) );
					$suf = trim( (string) ( $r['suffix'] ?? '+' ) );
					$lbl = trim( (string) ( $r['label'] ?? '' ) );
					return '<div class="hero-metric-item">'
						. '<span class="hero-metric-value" data-count="' . esc_attr( $num ) . '" data-suffix="' . esc_attr( $suf ) . '">' . esc_html( $num . $suf ) . '</span>'
						. '<span class="hero-metric-label">' . esc_html( $lbl ) . '</span>'
						. '</div>';
				},
				$html
			);
		}

		// Why Partner With ALIFY
		$html = alify_cf_in_section(
			$html,
			'home-why-section',
			function ( $b ) {
				$b   = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_home', 'home_why_eyebrow' ) );
				$b   = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_home', 'home_why_title' ) );
				$b   = alify_cf_set_tag_by_class( $b, 'p', 'about-story-p', alify_cf_opt_saved( 'alify_home', 'home_why_desc' ), false );
				$img = alify_cf_opt_img( 'alify_home', 'home_why_image', '' );
				if ( '' !== $img ) {
					$b = preg_replace( '/(<img\b[^>]*src=")[^"]*(")/', '${1}' . esc_url( $img ) . '${2}', $b, 1 );
				}
				return $b;
			}
		);

		// Home Sliders & Showcase Headings
		$sec_pairs = array(
			'home-services-section'     => array( 'home_srv_eyebrow', 'home_srv_title', 'services-sec-title' ),
			'home-industries-section'   => array( 'home_ind_eyebrow', 'home_ind_title', 'industries-sec-title' ),
			'home-projects-section'     => array( 'home_prj_eyebrow', 'home_prj_title', 'projects-sec-title' ),
			'home-case-studies-section' => array( 'home_cs_eyebrow', 'home_cs_title', 'case-studies-sec-title' ),
		);
		foreach ( $sec_pairs as $s_cls => $keys ) {
			$eb = alify_cf_opt_saved( 'alify_home', $keys[0] );
			$tt = alify_cf_opt_saved( 'alify_home', $keys[1] );
			if ( '' !== $eb || '' !== $tt ) {
				$t_cls = $keys[2];
				$html  = alify_cf_in_section(
					$html,
					$s_cls,
					function ( $b ) use ( $eb, $tt, $t_cls ) {
						$b = alify_cf_set_eyebrow( $b, $eb );
						return alify_cf_set_tag_by_class( $b, 'h2', $t_cls, $tt );
					}
				);
			}
		}

		// 4-Sprint Process Section + Repeater (`home_proc_rows`)
		$proc_rows = alify_cf_opt_rows( 'alify_home', 'home_proc_rows' );
		$html      = alify_cf_in_section(
			$html,
			'studio-process-section',
			function ( $b ) use ( $proc_rows ) {
				$b = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_home', 'home_proc_eyebrow' ) );
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_home', 'home_proc_title' ) );
				$b = alify_cf_set_tag_by_class( $b, 'p', 'studio-sec-desc', alify_cf_opt_saved( 'alify_home', 'home_proc_desc' ), false );
				if ( ! empty( $proc_rows ) ) {
					$pi = 0;
					$b  = preg_replace_callback(
						'/<article\s+class="studio-process-card">[\s\S]*?<\/article>/',
						function ( $cm ) use ( $proc_rows, &$pi ) {
							if ( ! isset( $proc_rows[ $pi ] ) ) {
								$pi++;
								return $cm[0];
							}
							$r = $proc_rows[ $pi ];
							$pi++;
							$c = $cm[0];
							$c = alify_cf_set_tag_by_class( $c, 'span', 'studio-process-badge', trim( (string) ( $r['badge'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'h3', 'studio-process-title', trim( (string) ( $r['title'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'p', 'studio-process-desc', trim( (string) ( $r['desc'] ?? '' ) ), false );
							if ( ! empty( $r['deliverable'] ) ) {
								$c = preg_replace(
									'/(<span\s+class="studio-process-deliverable">[\s\S]*?<i[^>]*><\/i>\s*<span>)[\s\S]*?(<\/span>)/',
									'${1}' . esc_html( trim( (string) $r['deliverable'] ) ) . '${2}',
									$c,
									1
								);
							}
							return $c;
						},
						$b
					);
				}
				return $b;
			}
		);
	}

	// =========================================================================
	// 3. ARCHIVE HUBS (`alify_archives`) & INNER PAGES (`alify_pages`)
	// =========================================================================
	$hero_map = array(
		'services'      => array( 'alify_archives', 'arch_srv_hero_eyebrow', 'arch_srv_hero_title', 'arch_srv_hero_desc', 'arch_srv_hero_bg' ),
		'work'          => array( 'alify_archives', 'arch_wrk_hero_eyebrow', 'arch_wrk_hero_title', 'arch_wrk_hero_desc', 'arch_wrk_hero_bg' ),
		'industries'    => array( 'alify_archives', 'arch_ind_hero_eyebrow', 'arch_ind_hero_title', 'arch_ind_hero_desc', 'arch_ind_hero_bg' ),
		'case-studies'  => array( 'alify_archives', 'arch_cs_hero_eyebrow', 'arch_cs_hero_title', 'arch_cs_hero_desc', 'arch_cs_hero_bg' ),
		'blog'          => array( 'alify_archives', 'arch_blog_hero_eyebrow', 'arch_blog_hero_title', 'arch_blog_hero_desc', 'arch_blog_hero_bg' ),
		'pricing'       => array( 'alify_pages', 'arch_prc_hero_eyebrow', 'arch_prc_hero_title', 'arch_prc_hero_desc', 'arch_prc_hero_bg' ),
		'about'         => array( 'alify_pages', 'arch_abt_hero_eyebrow', 'arch_abt_hero_title', 'arch_abt_hero_desc', 'arch_abt_hero_bg' ),
		'testimonials'  => array( 'alify_pages', '', 'arch_tst_hero_title', '', '' ),
		'faq'           => array( 'alify_pages', '', 'arch_faq_hero_title', '', '' ),
		'contact'       => array( 'alify_pages', '', 'arch_cnt_hero_title', '', '' ),
		'start-project' => array( 'alify_pages', '', 'arch_sp_hero_title', '', '' ),
		'book-call'     => array( 'alify_pages', '', 'arch_bc_hero_title', '', '' ),
	);

	if ( isset( $hero_map[ $part_slug ] ) ) {
		list( $opg, $keb, $ktt, $kds, $kbg ) = $hero_map[ $part_slug ];
		$html                                = alify_cf_apply_inner_hero_defaults(
			$html,
			$page_id,
			$keb ? alify_cf_opt_saved( $opg, $keb ) : '',
			$ktt ? alify_cf_opt_saved( $opg, $ktt ) : '',
			$kds ? alify_cf_opt_saved( $opg, $kds ) : '',
			$kbg ? alify_cf_opt_img( $opg, $kbg, '' ) : ''
		);
	}

	if ( 'services' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'services-grid-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'services-sec-title', alify_cf_opt_saved( 'alify_archives', 'arch_srv_sec_title' ) );
			}
		);
	} elseif ( 'work' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'projects-grid-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'projects-sec-title', alify_cf_opt_saved( 'alify_archives', 'arch_wrk_sec_title' ) );
			}
		);
	} elseif ( 'industries' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'industries-grid-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'industries-sec-title', alify_cf_opt_saved( 'alify_archives', 'arch_ind_sec_title' ) );
			}
		);
	} elseif ( 'blog' === $part_slug ) {
		$html  = alify_cf_set_tag_by_class( $html, 'div', 'inner-hero-card-metric', alify_cf_opt_saved( 'alify_archives', 'arch_blog_card_metric' ), false );
		$html  = alify_cf_set_tag_by_class( $html, 'p', 'inner-hero-card-label', alify_cf_opt_saved( 'alify_archives', 'arch_blog_card_label' ), false );
		$b_cta = alify_cf_opt_saved( 'alify_archives', 'arch_blog_card_cta' );
		if ( '' !== $b_cta ) {
			$html = preg_replace( '/(<a\b[^>]*class="[^"]*inner-hero-card-cta[^"]*"[^>]*>\s*<span>)[\s\S]*?(<\/span>)/', '${1}' . esc_html( $b_cta ) . '${2}', $html, 1 );
		}
	} elseif ( 'pricing' === $part_slug ) {
		$save_pill = alify_cf_opt_saved( 'alify_pages', 'arch_prc_save_pill' );
		if ( '' !== $save_pill ) {
			$html = alify_cf_set_tag_by_class( $html, 'span', 'pricing-save-pill', $save_pill, false );
		}
		$tier_rows = alify_cf_opt_rows( 'alify_pages', 'pricing_tiers_rows' );
		$plans     = array( 'launch-sprint', 'scale-ecosystem', 'enterprise-squad' );
		foreach ( $plans as $idx => $plan_slug ) {
			if ( ! isset( $tier_rows[ $idx ] ) ) {
				continue;
			}
			$tr   = $tier_rows[ $idx ];
			$html = preg_replace_callback(
				'/<article\b[^>]*data-plan="' . preg_quote( $plan_slug, '/' ) . '"[\s\S]*?<\/article>/',
				function ( $cm ) use ( $tr ) {
					$card   = $cm[0];
					$p_name = trim( (string) ( $tr['name'] ?? '' ) );
					$p_sp   = trim( (string) ( $tr['sprint_price'] ?? '' ) );
					$p_rt   = trim( (string) ( $tr['retainer_price'] ?? '' ) );
					$p_vel  = trim( (string) ( $tr['velocity'] ?? '' ) );
					$p_sum  = trim( (string) ( $tr['summary'] ?? '' ) );
					$p_feat = trim( (string) ( $tr['features'] ?? '' ) );

					if ( '' !== $p_sp ) {
						$card = preg_replace( '/data-sprint-price="[^"]*"/', 'data-sprint-price="' . esc_attr( $p_sp ) . '"', $card, 1 );
						$card = alify_cf_set_tag_by_class( $card, 'span', 'js-price-amount', $p_sp, false );
					}
					if ( '' !== $p_rt ) {
						$card = preg_replace( '/data-retainer-price="[^"]*"/', 'data-retainer-price="' . esc_attr( $p_rt ) . '"', $card, 1 );
					}
					if ( '' !== $p_vel ) {
						$card = preg_replace( '/data-sprint-velocity="[^"]*"/', 'data-sprint-velocity="' . esc_attr( $p_vel ) . '"', $card, 1 );
						$card = alify_cf_set_tag_by_class( $card, 'span', 'js-price-velocity', $p_vel, false );
					}
					$card = alify_cf_set_tag_by_class( $card, 'h3', 'pricing-tier-name', $p_name, false );
					$card = alify_cf_set_tag_by_class( $card, 'p', 'pricing-tier-summary', $p_sum, false );
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
	} elseif ( 'about' === $part_slug ) {
		$html        = alify_cf_in_section(
			$html,
			'about-story-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_title' ) );
			}
		);
		$leader_rows = alify_cf_opt_rows( 'alify_pages', 'about_leaders_rows' );
		$html        = alify_cf_in_section(
			$html,
			'about-team-section',
			function ( $b ) use ( $leader_rows ) {
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_pages', 'arch_abt_team_title' ) );
				if ( ! empty( $leader_rows ) ) {
					$li = 0;
					$b  = preg_replace_callback(
						'/<article\s+class="about-leader-card">[\s\S]*?<\/article>/',
						function ( $cm ) use ( $leader_rows, &$li ) {
							if ( ! isset( $leader_rows[ $li ] ) ) {
								$li++;
								return $cm[0];
							}
							$r = $leader_rows[ $li ];
							$li++;
							$c   = $cm[0];
							$img = alify_cf_resolve_img( $r['photo'] ?? '', '' );
							if ( '' !== $img ) {
								$c = preg_replace( '/(<img\b[^>]*src=")[^"]*(")/', '${1}' . esc_url( $img ) . '${2}', $c, 1 );
							}
							$c = alify_cf_set_tag_by_class( $c, 'h3', 'about-leader-name', trim( (string) ( $r['name'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'span', 'about-leader-role', trim( (string) ( $r['role'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'p', 'about-leader-bio', trim( (string) ( $r['bio'] ?? '' ) ), false );
							return $c;
						},
						$b
					);
				}
				return $b;
			}
		);
	} elseif ( 'book-call' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'h2', 'book-host-name', alify_cf_opt_saved( 'alify_pages', 'arch_bc_host_name' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'span', 'book-host-role', alify_cf_opt_saved( 'alify_pages', 'arch_bc_host_role' ), false );
	} elseif ( 'thank-you' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'h1', 'thankyou-title', alify_cf_opt_saved( 'alify_pages', 'arch_ty_title' ) );
	} elseif ( '404' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'h1', 'error-stage-title', alify_cf_opt_saved( 'alify_pages', 'arch_404_title' ), false );
	}

	// =========================================================================
	// 4. SINGLE TEMPLATES GLOBAL (`alify_singles`)
	// =========================================================================
	if ( 'service-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				$b = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_singles', 'sgl_srv_overview_eyebrow' ) );
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_overview_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'studio-process-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_process_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_related_title' ) );
			}
		);
	} elseif ( 'work-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				$b = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_singles', 'sgl_wrk_related_eyebrow' ) );
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_wrk_related_title' ) );
			}
		);
	} elseif ( 'industry-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_ind_overview_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_ind_related_title' ) );
			}
		);
	} elseif ( 'case-study-single' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'span', 'single-snapshot-heading', alify_cf_opt_saved( 'alify_singles', 'sgl_cs_snapshot_heading' ), false );
		$html = alify_cf_in_section(
			$html,
			'single-related-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_cs_related_title' ) );
			}
		);
	}

	return $html;
}
