<?php
/**
 * Frontend Render Bridge for `Alify Custom Field` Options Pages & Post Fields.
 * Connects `site`, `alify_home`, `alify_archives`, `alify_pages`, `alify_conversion`,
 * and `alify_singles` directly to Home, Archive/Hub Pages, Pricing, About, Book Call,
 * Contact, Start Project, Thank You, Legal Docs (`the_content()`), 404, Single Templates,
 * Header, Footer & Brand Logo.
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

function alify_cf_set_tag_by_class( $sec_body, $tag, $class_fragment, $content, $allow_span = true, $occurrence = 0 ) {
	if ( '' === $content ) {
		return $sec_body;
	}
	$safe = $allow_span
		? wp_kses( $content, array( 'span' => array( 'class' => array() ), 'br' => array() ) )
		: esc_html( $content );

	$pattern = '/(<' . preg_quote( $tag, '/' ) . '\\b[^>]*class="[^"]*' . preg_quote( $class_fragment, '/' ) . '[^"]*"[^>]*>)[\\s\\S]*?(<\\/' . preg_quote( $tag, '/' ) . '>)/';
	$seen = 0;
	return preg_replace_callback( $pattern, function ( $match ) use ( $safe, $occurrence, &$seen ) {
		if ( $seen++ !== (int) $occurrence ) { return $match[0]; }
		return $match[1] . $safe . $match[2];
	}, $sec_body );
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
 * Shared 4-Sprint Process Cards Renderer (`home_proc_rows`) Used on Home & About Pages.
 */
function alify_cf_apply_process_rows( $sec_body, $proc_rows ) {
	if ( empty( $proc_rows ) ) {
		return $sec_body;
	}
	$pi = 0;
	return preg_replace_callback(
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
		$sec_body
	);
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

		// Why Partner With ALIFY (`.about-visual-bg` + `.about-visual-quote`)
		$html = alify_cf_in_section(
			$html,
			'home-why-section',
			function ( $b ) {
				$b     = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_home', 'home_why_eyebrow' ) );
				$b     = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_home', 'home_why_title' ) );
				$b     = alify_cf_set_tag_by_class( $b, 'p', 'about-story-p', alify_cf_opt_saved( 'alify_home', 'home_why_desc' ), false );
				$img   = alify_cf_opt_img( 'alify_home', 'home_why_image', '' );
				$quote = alify_cf_opt_saved( 'alify_home', 'home_why_quote' );
				if ( '' !== $img ) {
					$b = preg_replace( '/(class="about-visual-bg"[\s\S]*?style="background-image:\s*url\(\')[^\']*(\'\);")/', '${1}' . esc_url( $img ) . '${2}', $b, 1 );
				}
				if ( '' !== $quote ) {
					$b = alify_cf_set_tag_by_class( $b, 'p', 'about-visual-quote', '“' . $quote . '”', false );
				}
				$h_pillars = alify_cf_opt_rows( 'alify_home', 'home_why_pillars_rows' );
				if ( ! empty( $h_pillars ) && function_exists( 'alify_replace_container_inner' ) ) {
					$p_html = '';
					foreach ( $h_pillars as $pr ) {
						$ic  = ! empty( $pr['icon'] ) ? trim( (string) $pr['icon'] ) : 'fa-solid fa-circle-check';
						$ttl = trim( (string) ( $pr['title'] ?? '' ) );
						$sub = trim( (string) ( $pr['sub'] ?? '' ) );
						if ( '' === $ttl ) {
							continue;
						}
						$p_html .= '<div class="about-pillar-item">'
							. '<span class="about-pillar-icon" aria-hidden="true"><i class="' . esc_attr( $ic ) . '"></i></span>'
							. '<div><span class="about-pillar-title">' . esc_html( $ttl ) . '</span>'
							. '<span class="about-pillar-sub">' . esc_html( $sub ) . '</span></div>'
							. '</div>';
					}
					if ( '' !== $p_html ) {
						$b = alify_replace_container_inner( $b, 'class="about-pillars-grid"', 'div', $p_html );
					}
				}
				return $b;
			}
		);

		// Home Sliders & Showcase Headings (`cs-sec-title` on Case Studies!)
		$sec_pairs = array(
			'home-services-section'     => array( 'home_srv_eyebrow', 'home_srv_title', 'services-sec-title' ),
			'home-industries-section'   => array( 'home_ind_eyebrow', 'home_ind_title', 'industries-sec-title' ),
			'home-projects-section'     => array( 'home_prj_eyebrow', 'home_prj_title', 'projects-sec-title' ),
			'home-case-studies-section' => array( 'home_cs_eyebrow', 'home_cs_title', 'cs-sec-title' ),
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
		$cs_desc = alify_cf_opt_saved( 'alify_home', 'home_cs_desc' );
		if ( '' !== $cs_desc ) {
			$html = alify_cf_in_section(
				$html,
				'home-case-studies-section',
				function ( $b ) use ( $cs_desc ) {
					return alify_cf_set_tag_by_class( $b, 'p', 'cs-sec-desc', $cs_desc, false );
				}
			);
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
				return alify_cf_apply_process_rows( $b, $proc_rows );
			}
		);
	}

	// =========================================================================
	// 3. ARCHIVE HUBS (`alify_archives`), PRICING/ABOUT (`alify_pages`) & CONVERSION (`alify_conversion`)
	// =========================================================================
	$hero_map = array(
		'services'         => array( 'alify_archives', 'arch_srv_hero_eyebrow', 'arch_srv_hero_title', 'arch_srv_hero_desc', 'arch_srv_hero_bg' ),
		'work'             => array( 'alify_archives', 'arch_wrk_hero_eyebrow', 'arch_wrk_hero_title', 'arch_wrk_hero_desc', 'arch_wrk_hero_bg' ),
		'industries'       => array( 'alify_archives', 'arch_ind_hero_eyebrow', 'arch_ind_hero_title', 'arch_ind_hero_desc', 'arch_ind_hero_bg' ),
		'case-studies'     => array( 'alify_archives', 'arch_cs_hero_eyebrow', 'arch_cs_hero_title', 'arch_cs_hero_desc', 'arch_cs_hero_bg' ),
		'blog'             => array( 'alify_archives', 'arch_blog_hero_eyebrow', 'arch_blog_hero_title', 'arch_blog_hero_desc', 'arch_blog_hero_bg' ),
		'pricing'          => array( 'alify_pages', 'arch_prc_hero_eyebrow', 'arch_prc_hero_title', 'arch_prc_hero_desc', 'arch_prc_hero_bg' ),
		'about'            => array( 'alify_pages', 'arch_abt_hero_eyebrow', 'arch_abt_hero_title', 'arch_abt_hero_desc', 'arch_abt_hero_bg' ),
		'book-call'        => array( 'alify_conversion', 'arch_bc_hero_eyebrow', 'arch_bc_hero_title', 'arch_bc_hero_desc', 'arch_bc_hero_bg' ),
		'contact'          => array( 'alify_conversion', 'arch_cnt_hero_eyebrow', 'arch_cnt_hero_title', 'arch_cnt_hero_desc', '' ),
		'start-project'    => array( 'alify_conversion', 'arch_sp_hero_eyebrow', 'arch_sp_hero_title', 'arch_sp_hero_desc', '' ),
		'testimonials'     => array( 'alify_conversion', '', 'arch_tst_hero_title', '', '' ),
		'faq'              => array( 'alify_conversion', '', 'arch_faq_hero_title', '', '' ),
		'privacy-policy'   => array( 'alify_conversion', '', 'legal_privacy_hero_title', '', '' ),
		'terms-of-service' => array( 'alify_conversion', '', 'legal_terms_hero_title', '', '' ),
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

		// Pricing Scope Calculator & Comparison Matrix Sections (+ Dynamic Repeater Modules & Matrix Rows!)
		$html = alify_cf_in_section(
			$html,
			'pricing-calc-section',
			function ( $b ) {
				$b        = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_pages', 'prc_calc_eyebrow' ) );
				$b        = alify_cf_set_tag_by_class( $b, 'h2', 'pricing-sec-title', alify_cf_opt_saved( 'alify_pages', 'prc_calc_title' ) );
				$b        = alify_cf_set_tag_by_class( $b, 'p', 'pricing-sec-desc', alify_cf_opt_saved( 'alify_pages', 'prc_calc_desc' ), false );
				$mod_rows = alify_cf_opt_rows( 'alify_pricing_adv', 'pricing_calc_modules_rows' );
				if ( ! empty( $mod_rows ) && function_exists( 'alify_replace_container_inner' ) ) {
					$m_html = '';
					foreach ( $mod_rows as $mr ) {
						$ttl = trim( (string) ( $mr['title'] ?? '' ) );
						if ( '' === $ttl ) {
							continue;
						}
						$ic    = ! empty( $mr['icon'] ) ? trim( (string) $mr['icon'] ) : 'fa-solid fa-layer-group';
						$cost  = max( 0, (int) ( $mr['cost'] ?? 0 ) );
						$weeks = max( 1, (int) ( $mr['weeks'] ?? 1 ) );
						$plbl  = ! empty( $mr['price_label'] ) ? trim( (string) $mr['price_label'] ) : '+$' . number_format_i18n( $cost );
						$chk   = ( ! empty( $mr['checked'] ) && ( '1' === (string) $mr['checked'] || true === $mr['checked'] ) ) ? ' checked' : '';
						$m_html .= '<label class="pricing-module-chip">'
							. '<input type="checkbox" name="calc_module" value="' . esc_attr( $ttl ) . '" data-cost="' . esc_attr( (string) $cost ) . '" data-weeks="' . esc_attr( (string) $weeks ) . '"' . $chk . '>'
							. '<span class="pricing-module-box">'
							. '<span class="pricing-module-icon" aria-hidden="true"><i class="' . esc_attr( $ic ) . '"></i></span>'
							. '<span class="pricing-module-info">'
							. '<span class="pricing-module-name">' . esc_html( $ttl ) . '</span>'
							. '<span class="pricing-module-price">' . esc_html( $plbl ) . '</span>'
							. '</span>'
							. '<span class="pricing-module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>'
							. '</span>'
							. '</label>';
					}
					if ( '' !== $m_html ) {
						$b = alify_replace_container_inner( $b, 'class="pricing-module-grid"', 'div', $m_html );
					}
				}
				return $b;
			}
		);
		$html = alify_cf_in_section(
			$html,
			'pricing-matrix-section',
			function ( $b ) {
				$b        = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_pages', 'prc_matrix_eyebrow' ) );
				$b        = alify_cf_set_tag_by_class( $b, 'h2', 'pricing-sec-title', alify_cf_opt_saved( 'alify_pages', 'prc_matrix_title' ) );
				$b        = alify_cf_set_tag_by_class( $b, 'p', 'pricing-sec-desc', alify_cf_opt_saved( 'alify_pages', 'prc_matrix_desc' ), false );
				$mat_rows = alify_cf_opt_rows( 'alify_pricing_adv', 'pricing_matrix_rows' );
				if ( ! empty( $mat_rows ) ) {
					$render_cell = function ( $v, $featured = false ) {
						$val = trim( (string) $v );
						$cls = $featured ? ' class="is-featured-col"' : '';
						if ( 'included' === strtolower( $val ) || 'yes' === strtolower( $val ) ) {
							return '<td' . $cls . '><span class="matrix-yes"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Included</span></td>';
						}
						if ( '' === $val || '—' === $val || '-' === $val || 'no' === strtolower( $val ) ) {
							return '<td' . $cls . '><span class="matrix-dash" aria-label="Not included">—</span></td>';
						}
						return '<td' . $cls . '>' . esc_html( $val ) . '</td>';
					};
					$tb_html = '';
					foreach ( $mat_rows as $rx ) {
						$feat = trim( (string) ( $rx['feature'] ?? '' ) );
						if ( '' === $feat ) {
							continue;
						}
						$t1 = trim( (string) ( $rx['tier1'] ?? '' ) );
						$t2 = trim( (string) ( $rx['tier2'] ?? '' ) );
						$t3 = trim( (string) ( $rx['tier3'] ?? '' ) );
						if ( 'GROUP' === strtoupper( $t1 ) ) {
							$tb_html .= '<tr class="matrix-group-row"><td colspan="4">' . esc_html( $feat ) . '</td></tr>';
						} else {
							$tb_html .= '<tr><th scope="row">' . esc_html( $feat ) . '</th>'
								. $render_cell( $t1, false )
								. $render_cell( $t2, true )
								. $render_cell( $t3, false )
								. '</tr>';
						}
					}
					if ( '' !== $tb_html ) {
						$b = preg_replace( '/(<table\s+class="pricing-matrix-table"[\s\S]*?<tbody>)[\s\S]*?(<\/tbody>)/', '${1}' . $tb_html . '${2}', $b, 1 );
					}
				}
				return $b;
			}
		);
	} elseif ( 'about' === $part_slug ) {
		// 1. About Story Section (Eyebrow, Heading, 2 Paragraphs, Visual BG & Quote)
		$html = alify_cf_in_section(
			$html,
			'about-story-section',
			function ( $b ) {
				$b   = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_eyebrow' ) );
				$b   = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_title' ) );
				$p1  = alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_p1' );
				$p2  = alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_p2' );
				$pi  = 0;
				if ( '' !== $p1 || '' !== $p2 ) {
					$b = preg_replace_callback(
						'/<p\s+class="about-story-p">[\s\S]*?<\/p>/',
						function ( $pm ) use ( $p1, $p2, &$pi ) {
							$pi++;
							if ( 1 === $pi && '' !== $p1 ) {
								return '<p class="about-story-p">' . esc_html( $p1 ) . '</p>';
							}
							if ( 2 === $pi && '' !== $p2 ) {
								return '<p class="about-story-p">' . esc_html( $p2 ) . '</p>';
							}
							return $pm[0];
						},
						$b
					);
				}
				$s_img = alify_cf_opt_img( 'alify_pages', 'arch_abt_story_img', '' );
				if ( '' !== $s_img ) {
					$b = preg_replace( '/(class="about-visual-bg"[\s\S]*?style="background-image:\s*url\(\')[^\']*(\'\);")/', '${1}' . esc_url( $s_img ) . '${2}', $b, 1 );
				}
				$s_qt = alify_cf_opt_saved( 'alify_pages', 'arch_abt_story_quote' );
				if ( '' !== $s_qt ) {
					$b = alify_cf_set_tag_by_class( $b, 'p', 'about-visual-quote', '“' . $s_qt . '”', false );
				}
				$b = alify_cf_set_tag_by_class( $b, 'span', 'about-founder-name', alify_cf_opt_saved( 'alify_pages', 'arch_abt_founder_name' ), false );
				$b = alify_cf_set_tag_by_class( $b, 'span', 'about-founder-role', alify_cf_opt_saved( 'alify_pages', 'arch_abt_founder_role' ), false );

				$a_pillars = alify_cf_opt_rows( 'alify_pages', 'about_story_pillars_rows' );
				if ( ! empty( $a_pillars ) && function_exists( 'alify_replace_container_inner' ) ) {
					$ap_html = '';
					foreach ( $a_pillars as $pr ) {
						$ic  = ! empty( $pr['icon'] ) ? trim( (string) $pr['icon'] ) : 'fa-solid fa-circle-check';
						$ttl = trim( (string) ( $pr['title'] ?? '' ) );
						$sub = trim( (string) ( $pr['sub'] ?? '' ) );
						if ( '' === $ttl ) {
							continue;
						}
						$ap_html .= '<div class="about-pillar-item">'
							. '<span class="about-pillar-icon" aria-hidden="true"><i class="' . esc_attr( $ic ) . '"></i></span>'
							. '<div><span class="about-pillar-title">' . esc_html( $ttl ) . '</span>'
							. '<span class="about-pillar-sub">' . esc_html( $sub ) . '</span></div>'
							. '</div>';
					}
					if ( '' !== $ap_html ) {
						$b = alify_replace_container_inner( $b, 'class="about-pillars-grid"', 'div', $ap_html );
					}
				}
				return $b;
			}
		);

		// 2. About Milestones Counters Repeater (`about_metrics_rows`)
		$metric_rows = alify_cf_opt_rows( 'alify_pages', 'about_metrics_rows' );
		if ( ! empty( $metric_rows ) ) {
			$html = alify_cf_in_section(
				$html,
				'about-metrics-section',
				function ( $b ) use ( $metric_rows ) {
					$mi = 0;
					return preg_replace_callback(
						'/<div\s+class="about-metric-box">[\s\S]*?<\/div>/',
						function ( $mm ) use ( $metric_rows, &$mi ) {
							if ( ! isset( $metric_rows[ $mi ] ) ) {
								$mi++;
								return $mm[0];
							}
							$r = $metric_rows[ $mi ];
							$mi++;
							$pre = trim( (string) ( $r['prefix'] ?? '' ) );
							$num = trim( (string) ( $r['num'] ?? '150' ) );
							$suf = trim( (string) ( $r['suffix'] ?? '+' ) );
							$ttl = trim( (string) ( $r['title'] ?? '' ) );
							$dsc = trim( (string) ( $r['desc'] ?? '' ) );
							$pre_attr = '' !== $pre ? ' data-prefix="' . esc_attr( $pre ) . '"' : '';
							return '<div class="about-metric-box">'
								. '<span class="about-metric-num js-studio-counter"' . $pre_attr . ' data-target="' . esc_attr( $num ) . '" data-suffix="' . esc_attr( $suf ) . '">' . esc_html( $pre . $num . $suf ) . '</span>'
								. '<span class="about-metric-title">' . esc_html( $ttl ) . '</span>'
								. '<p class="about-metric-desc">' . esc_html( $dsc ) . '</p>'
								. '</div>';
						},
						$b
					);
				}
			);
		}

		// 3. About Mission & Vision Bento Cards (`about-values-section`)
		$html = alify_cf_in_section(
			$html,
			'about-values-section',
			function ( $b ) {
				$mt = alify_cf_opt_saved( 'alify_pages', 'arch_abt_mission_title' );
				$mx = alify_cf_opt_saved( 'alify_pages', 'arch_abt_mission_text' );
				$vt = alify_cf_opt_saved( 'alify_pages', 'arch_abt_vision_title' );
				$vx = alify_cf_opt_saved( 'alify_pages', 'arch_abt_vision_text' );
				$vi = 0;
				if ( '' !== $mt || '' !== $mx || '' !== $vt || '' !== $vx ) {
					$b = preg_replace_callback(
						'/<article\s+class="about-mv-card[^"]*">[\s\S]*?<\/article>/',
						function ( $vm ) use ( $mt, $mx, $vt, $vx, &$vi ) {
							$vi++;
							$card = $vm[0];
							if ( 1 === $vi ) {
								$card = alify_cf_set_tag_by_class( $card, 'h3', 'about-mv-title', $mt, false );
								$card = alify_cf_set_tag_by_class( $card, 'p', 'about-mv-text', $mx, false );
							} elseif ( 2 === $vi ) {
								$card = alify_cf_set_tag_by_class( $card, 'h3', 'about-mv-title', $vt, false );
								$card = alify_cf_set_tag_by_class( $card, 'p', 'about-mv-text', $vx, false );
							}
							return $card;
						},
						$b
					);
				}
				$prin_rows = alify_cf_opt_rows( 'alify_pages', 'about_principles_rows' );
				if ( ! empty( $prin_rows ) && function_exists( 'alify_replace_container_inner' ) ) {
					$pr_html = '';
					foreach ( $prin_rows as $idx_p => $pr ) {
						$ttl = trim( (string) ( $pr['title'] ?? '' ) );
						if ( '' === $ttl ) {
							continue;
						}
						$ic  = ! empty( $pr['icon'] ) ? trim( (string) $pr['icon'] ) : 'fa-solid fa-gem';
						$num = ! empty( $pr['num'] ) ? trim( (string) $pr['num'] ) : sprintf( '%02d', $idx_p + 1 );
						$dsc = trim( (string) ( $pr['desc'] ?? '' ) );
						$pr_html .= '<div class="col-12 col-md-6 col-lg-3">'
							. '<article class="about-principle-card">'
							. '<div class="about-principle-top">'
							. '<span class="about-principle-icon" aria-hidden="true"><i class="' . esc_attr( $ic ) . '"></i></span>'
							. '<span class="about-principle-num">' . esc_html( $num ) . '</span>'
							. '</div>'
							. '<h3 class="about-principle-title">' . esc_html( $ttl ) . '</h3>'
							. '<p class="about-principle-desc">' . esc_html( $dsc ) . '</p>'
							. '</article>'
							. '</div>';
					}
					if ( '' !== $pr_html ) {
						$b = alify_replace_container_inner( $b, 'class="row about-principles-row"', 'div', $pr_html );
					}
				}
				return $b;
			}
		);

		// 4. Sync 4-Sprint Process on About Page Too (`home_proc_rows`)
		$proc_rows = alify_cf_opt_rows( 'alify_home', 'home_proc_rows' );
		if ( ! empty( $proc_rows ) ) {
			$html = alify_cf_in_section(
				$html,
				'studio-process-section',
				function ( $b ) use ( $proc_rows ) {
					return alify_cf_apply_process_rows( $b, $proc_rows );
				}
			);
		}

		// 5. About Leadership Principals Repeater (`about_leaders_rows`)
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
		$h_av = alify_cf_opt_img( 'alify_conversion', 'arch_bc_host_avatar', '' );
		if ( '' !== $h_av ) {
			$html = preg_replace( '/(<img\b[^>]*src=")[^"]*("[^>]*class="book-host-avatar")/', '${1}' . esc_url( $h_av ) . '${2}', $html, 1 );
		}
		$html = alify_cf_set_tag_by_class( $html, 'span', 'book-host-kicker', alify_cf_opt_saved( 'alify_conversion', 'arch_bc_host_kicker' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'h2', 'book-host-name', alify_cf_opt_saved( 'alify_conversion', 'arch_bc_host_name' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'span', 'book-host-role', alify_cf_opt_saved( 'alify_conversion', 'arch_bc_host_role' ), false );

		$out_rows = alify_cf_opt_rows( 'alify_conversion', 'book_outcomes_rows' );
		$html     = alify_cf_in_section(
			$html,
			'book-outcomes-section',
			function ( $b ) use ( $out_rows ) {
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'book-outcomes-title', alify_cf_opt_saved( 'alify_conversion', 'arch_bc_outcomes_title' ) );
				if ( ! empty( $out_rows ) ) {
					$oi = 0;
					$b  = preg_replace_callback(
						'/<article\s+class="book-outcome-card">[\s\S]*?<\/article>/',
						function ( $om ) use ( $out_rows, &$oi ) {
							if ( ! isset( $out_rows[ $oi ] ) ) {
								$oi++;
								return $om[0];
							}
							$r = $out_rows[ $oi ];
							$oi++;
							$ico = trim( (string) ( $r['icon'] ?? 'fa-solid fa-sitemap' ) );
							$ttl = trim( (string) ( $r['title'] ?? '' ) );
							$dsc = trim( (string) ( $r['desc'] ?? '' ) );
							return '<article class="book-outcome-card">'
								. '<span class="book-outcome-icon" aria-hidden="true"><i class="' . esc_attr( $ico ) . '"></i></span>'
								. '<h3 class="book-outcome-card-title">' . esc_html( $ttl ) . '</h3>'
								. '<p class="book-outcome-card-desc">' . esc_html( $dsc ) . '</p>'
								. '</article>';
						},
						$b
					);
				}
				return $b;
			}
		);
	} elseif ( 'contact' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'h2', 'contact-directory-title', alify_cf_opt_saved( 'alify_conversion', 'arch_cnt_dir_title' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'p', 'contact-directory-sub', alify_cf_opt_saved( 'alify_conversion', 'arch_cnt_dir_sub' ), false );
	} elseif ( 'thank-you' === $part_slug ) {
		$html    = alify_cf_set_tag_by_class( $html, 'h1', 'thankyou-title', alify_cf_opt_saved( 'alify_conversion', 'arch_ty_title' ) );
		$ty_rows = alify_cf_opt_rows( 'alify_conversion', 'thankyou_steps_rows' );
		$html    = alify_cf_in_section(
			$html,
			'thankyou-steps-section',
			function ( $b ) use ( $ty_rows ) {
				$b = alify_cf_set_tag_by_class( $b, 'h2', 'studio-sec-title', alify_cf_opt_saved( 'alify_conversion', 'arch_ty_steps_title' ) );
				if ( ! empty( $ty_rows ) ) {
					$ti = 0;
					$b  = preg_replace_callback(
						'/<article\s+class="thankyou-step-card">[\s\S]*?<\/article>/',
						function ( $tm ) use ( $ty_rows, &$ti ) {
							if ( ! isset( $ty_rows[ $ti ] ) ) {
								$ti++;
								return $tm[0];
							}
							$r = $ty_rows[ $ti ];
							$ti++;
							$c   = $tm[0];
							$ico = trim( (string) ( $r['icon'] ?? '' ) );
							if ( '' !== $ico ) {
								$c = preg_replace( '/(<span\s+class="thankyou-step-icon"[^>]*>\s*<i\s+class=")[^"]*(")/', '${1}' . esc_attr( $ico ) . '${2}', $c, 1 );
							}
							$c = alify_cf_set_tag_by_class( $c, 'span', 'thankyou-step-window', trim( (string) ( $r['window'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'h3', 'thankyou-step-title', trim( (string) ( $r['title'] ?? '' ) ), false );
							$c = alify_cf_set_tag_by_class( $c, 'p', 'thankyou-step-desc', trim( (string) ( $r['desc'] ?? '' ) ), false );
							return $c;
						},
						$b
					);
				}
				return $b;
			}
		);
	} elseif ( 'privacy-policy' === $part_slug || 'terms-of-service' === $part_slug ) {
		$upd_key = ( 'privacy-policy' === $part_slug ) ? 'legal_privacy_updated' : 'legal_terms_updated';
		$html    = alify_cf_set_tag_by_class( $html, 'span', 'legal-toc-updated', alify_cf_opt_saved( 'alify_conversion', $upd_key ), false );

		// Native WordPress Block/Classic Editor `the_content()` Support for Legal Pages!
		if ( $page_id > 0 ) {
			$raw_legal = trim( (string) get_post_field( 'post_content', $page_id ) );
			if ( '' !== $raw_legal && function_exists( 'alify_replace_container_inner' ) ) {
				$rendered_legal = '<article class="legal-prose-card">' . apply_filters( 'the_content', $raw_legal ) . '</article>';
				$html           = alify_replace_container_inner( $html, 'class="legal-prose-stack"', 'div', $rendered_legal );
			}
		}
	} elseif ( '404' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'span', 'error-code-display', alify_cf_opt_saved( 'alify_conversion', 'arch_404_code' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'h1', 'error-stage-title', alify_cf_opt_saved( 'alify_conversion', 'arch_404_title' ), false );
		$html = alify_cf_set_tag_by_class( $html, 'p', 'error-stage-desc', alify_cf_opt_saved( 'alify_conversion', 'arch_404_desc' ), false );
	}

	// =========================================================================
	// 4. SINGLE TEMPLATES GLOBAL (`alify_singles` — Exact `single-sec-title` & Section Selectors!)
	// =========================================================================
	if ( 'service-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				$b = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_singles', 'sgl_srv_overview_eyebrow' ) );
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_overview_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'single-process-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_process_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'single-related-projects-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_srv_related_title' ) );
			}
		);
	} elseif ( 'work-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-related-projects-section',
			function ( $b ) {
				$b = alify_cf_set_eyebrow( $b, alify_cf_opt_saved( 'alify_singles', 'sgl_wrk_related_eyebrow' ) );
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_wrk_related_title' ) );
			}
		);
	} elseif ( 'industry-single' === $part_slug ) {
		$html = alify_cf_in_section(
			$html,
			'single-overview-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_ind_overview_title' ) );
			}
		);
		$html = alify_cf_in_section(
			$html,
			'single-related-projects-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_ind_related_title' ) );
			}
		);
	} elseif ( 'case-study-single' === $part_slug ) {
		$html = alify_cf_set_tag_by_class( $html, 'span', 'single-snapshot-heading', alify_cf_opt_saved( 'alify_singles', 'sgl_cs_snapshot_heading' ), false );
		$html = alify_cf_in_section(
			$html,
			'single-related-projects-section',
			function ( $b ) {
				return alify_cf_set_tag_by_class( $b, 'h2', 'single-sec-title', alify_cf_opt_saved( 'alify_singles', 'sgl_cs_related_title' ) );
			}
		);
	}

	return $html;
}


/**
 * Apply per-page dashboard content overrides. Page repeater rows use semantic section keys;
 * heading and copy match only inside the named static section to preserve its curated markup/design.
 */
if ( ! function_exists( 'alify_apply_per_page_cf_overrides' ) ) {
	function alify_apply_per_page_cf_overrides( $html, $part_slug = '' ) {
		if ( ! is_string( $html ) || ( ! is_page() && ! is_singular() ) || ! function_exists( 'alify_get_field' ) ) {
			return $html;
		}
		$post_id = get_queried_object_id();
		if ( ! $post_id ) { return $html; }
		$eyebrow = (string) alify_get_field( 'pg_content_hero_eyebrow', $post_id );
		if ( '' !== trim( $eyebrow ) ) {
			$html = preg_replace_callback( '~(<div[^>]*class="[^"]*\\binner-hero-eyebrow\b[^"]*"[^>]*>)(.*?)(</div>)~si', function( $m ) use ( $eyebrow ) {
				$inside = preg_replace_callback( '~(<span[^>]*class="[^"]*inner-hero-eyebrow-dot[^"]*"[^>]*>)(.*?)(</span>)~si', function( $dot ) { return $dot[1] . '△' . $dot[3]; }, $m[2], 1 );
				$inside = preg_replace_callback( '~(<span[^>]*>)(.*?)(</span>)~si', function( $label ) use ( $eyebrow ) { return $label[1] . esc_html( $eyebrow ) . $label[3]; }, $inside, 1 );
				return $m[1] . $inside . $m[3];
			}, $html, 1 );
		}
		$hero_map = array(
			'pg_content_hero_title' => array( 'inner-hero-title', 'hero-display-title' ),
			'pg_content_hero_desc' => array( 'inner-hero-desc', 'hero-lead' ),
		);
		foreach ( $hero_map as $field => $classes ) {
			$value = alify_get_field( $field, $post_id );
			if ( ! is_string( $value ) || '' === trim( $value ) ) { continue; }
			foreach ( $classes as $class ) {
				$pattern = '~(<(?:h1|p|div)[^>]*class="[^"]*\b' . preg_quote( $class, '~' ) . '\b[^"]*"[^>]*>)(.*?)(</(?:h1|p|div)>)~si';
				$html = preg_replace_callback( $pattern, function( $m ) use ( $value, $class ) {
					$safe = ( 'inner-hero-title' === $class || 'hero-display-title' === $class ) ? wp_kses( $value, array( 'span' => array( 'class' => true ) ) ) : esc_html( $value );
					return $m[1] . $safe . $m[3];
				}, $html, 1 );
				if ( false !== strpos( $html, $class ) ) { break; }
			}
		}
		$direct_map = array(
			'pg_content_hero_lead_title' => array( 'hero-lead-title' ),
			'pg_content_hero_lead_desc' => array( 'hero-lead-subtitle' ),
			'pg_content_hero_lead_button' => array( 'btn-start-pill' ),
			'pg_content_hero_lead_badge' => array( 'hero-lead-badge' ),
			'pg_content_hero_lead_sla' => array( 'hero-lead-response' ),
			'pg_content_hero_card_metric' => array( 'inner-hero-card-metric' ),
			'pg_content_hero_card_copy' => array( 'inner-hero-card-label' ),
			'pg_content_hero_cta' => array( 'inner-hero-card-cta' ),
		);
		foreach ( $direct_map as $field => $classes ) {
			$value = alify_get_field( $field, $post_id );
			if ( ! is_string( $value ) || '' === trim( $value ) ) { continue; }
			foreach ( $classes as $class ) {
				$html = alify_cf_set_tag_by_class( $html, 'span', $class, $value, false );
				$html = alify_cf_set_tag_by_class( $html, 'h2', $class, $value );
				$html = alify_cf_set_tag_by_class( $html, 'p', $class, $value );
				$html = alify_cf_set_tag_by_class( $html, 'a', $class, $value );
				$html = alify_cf_set_tag_by_class( $html, 'button', $class, $value );
			}
		}
		$hero_tags = (string) alify_get_field( 'pg_content_hero_tags', $post_id );
		if ( '' !== trim( $hero_tags ) ) {
			$tags_html = '';
			foreach ( preg_split( '/\\r?\\n/', $hero_tags ) as $tag_label ) {
				$tag_label = trim( $tag_label );
				if ( '' !== $tag_label ) { $tags_html .= '<span class="inner-hero-tag">' . esc_html( $tag_label ) . '</span>'; }
			}
			$html = preg_replace_callback( '~(<div[^>]*class="[^"]*\\binner-hero-tags\\b[^"]*"[^>]*>)(.*?)(</div>)~si', function( $m ) use ( $tags_html ) { return $m[1] . $tags_html . $m[3]; }, $html, 1 );
		}
		$hero_image_id = (int) alify_get_field( 'pg_content_hero_image', $post_id );
		$hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
		if ( ! $hero_image_url ) { $hero_image_url = (string) alify_get_field( 'pg_content_hero_image_url', $post_id ); }
		if ( $hero_image_url ) {
			$html = preg_replace_callback( '~(<[^>]+id="(?:innerHeroBg|homeHeroBg)"[^>]*style=")(.*?)("[^>]*>)~si', function( $m ) use ( $hero_image_url ) {
				return $m[1] . 'background-image: url(' . esc_url( $hero_image_url ) . ');' . $m[3];
			}, $html, 1 );
		}
		$hide_map = array(
			'page_hide_hero_form' => array( 'hero-lead-form-card', 'inner-hero-lead-card' ),
			'page_hide_testimonials' => array( 'testimonials-slider-section' ),
			'page_hide_faq' => array( 'faq-section' ),
			'page_hide_blog_slider' => array( 'home-blog-section' ),
			'page_hide_contact' => array( 'reusable-contact-section' ),
		);
		foreach ( $hide_map as $field => $classes ) {
			if ( ! alify_get_field( $field, $post_id ) ) { continue; }
			foreach ( $classes as $class ) {
				$html = preg_replace( '~<section\b(?=[^>]*class="[^"]*\b' . preg_quote( $class, '~' ) . '\b[^"]*")[^>]*>.*?</section>~si', '', $html, 1 );
			}
		}
		$rows = alify_get_field( 'pg_content_sections', $post_id );
		if ( ! is_array( $rows ) ) { return $html; }
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['section_key'] ) ) { continue; }
			$key = sanitize_html_class( $row['section_key'] );
			$pattern = '~(<section\b[^>]*class="[^"]*\b' . preg_quote( $key, '~' ) . '\b[^"]*"[^>]*>)(.*?)(</section>)~si';
			$html = preg_replace_callback( $pattern, function( $m ) use ( $row ) {
				$inner = $m[2];
				if ( isset( $row['section_eyebrow'] ) && '' !== trim( (string) $row['section_eyebrow'] ) ) {
					$inner = preg_replace( '~(<(?:span|p)[^>]*class="[^"]*(?:eyebrow|kicker)[^"]*"[^>]*>)(.*?)(</(?:span|p)>)~si', '$1' . esc_html( $row['section_eyebrow'] ) . '$3', $inner, 1 );
				}
				if ( isset( $row['section_title'] ) && '' !== trim( (string) $row['section_title'] ) ) {
					$inner = preg_replace_callback( '~(<h[1-6][^>]*>)(.*?)(</h[1-6]>)~si', function ( $h ) use ( $row ) { return $h[1] . wp_kses( $row['section_title'], array( 'span' => array( 'class' => true ), 'br' => array() ) ) . $h[3]; }, $inner, 1 );
				}
				if ( isset( $row['section_description'] ) && '' !== trim( (string) $row['section_description'] ) ) {
					$inner = preg_replace_callback( '~(<p[^>]*>)(.*?)(</p>)~si', function ( $p ) use ( $row ) { return $p[1] . esc_html( $row['section_description'] ) . $p[3]; }, $inner, 1 );
				}
				if ( ! empty( $row['section_body'] ) ) {
					$body = wp_kses_post( $row['section_body'] );
					$inner = preg_replace_callback( '~(<div[^>]*class="[^"]*(?:legal-prose-stack|legal-doc-content)[^"]*"[^>]*>)(.*?)(</div>)~si', function( $content ) use ( $body ) { return $content[1] . $body . $content[3]; }, $inner, 1 );
					if ( false === strpos( $inner, 'legal-prose-stack' ) && false === strpos( $inner, 'legal-doc-content' ) ) { $inner .= '<div class="alify-editor-section-body">' . $body . '</div>'; }
				}
				$section_image_id = isset( $row['section_image'] ) ? (int) $row['section_image'] : 0;
				$section_image_url = $section_image_id ? wp_get_attachment_image_url( $section_image_id, 'full' ) : '';
				if ( ! $section_image_url && ! empty( $row['section_image_url'] ) ) { $section_image_url = esc_url( $row['section_image_url'] ); }
				if ( $section_image_url ) { $inner .= '<figure class="alify-editor-section-image">' . ( $section_image_id ? wp_get_attachment_image( $section_image_id, 'large', false, array( 'loading' => 'lazy' ) ) : '<img src="' . esc_url( $section_image_url ) . '" alt="" loading="lazy">' ) . '</figure>'; }
				$items = isset( $row['section_items'] ) && is_array( $row['section_items'] ) ? $row['section_items'] : array();
				foreach ( $items as $item_index => $item ) {
					if ( ! is_array( $item ) ) { continue; }
					$title_class = ! empty( $item['item_target_class'] ) ? sanitize_html_class( $item['item_target_class'] ) : '';
					$desc_class = ! empty( $item['item_description_target_class'] ) ? sanitize_html_class( $item['item_description_target_class'] ) : '';
					if ( $title_class && ! empty( $item['item_title'] ) ) {
						foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p' ) as $target_tag ) { $inner = alify_cf_set_tag_by_class( $inner, $target_tag, $title_class, (string) $item['item_title'], true, (int) $item_index ); }
					}
					if ( $desc_class && ! empty( $item['item_description'] ) ) {
						foreach ( array( 'p', 'span', 'div' ) as $target_tag ) { $inner = alify_cf_set_tag_by_class( $inner, $target_tag, $desc_class, (string) $item['item_description'], false, (int) $item_index ); }
					}
				}
				if ( $items ) {
					$cards = '<div class="alify-editor-content-grid">';
					foreach ( $items as $item ) {
						if ( ! is_array( $item ) || ( array_key_exists( 'item_enabled', $item ) && ! $item['item_enabled'] ) ) { continue; }
						$title = isset( $item['item_title'] ) ? trim( (string) $item['item_title'] ) : '';
						$description = isset( $item['item_description'] ) ? trim( (string) $item['item_description'] ) : '';
						$body = isset( $item['item_body'] ) ? wp_kses_post( $item['item_body'] ) : '';
						$image_id = isset( $item['item_image'] ) ? (int) $item['item_image'] : 0;
						$link = isset( $item['item_link'] ) ? esc_url( $item['item_link'] ) : '';
						$cards .= '<article class="alify-editor-content-card">';
						$icon_class = ! empty( $item['item_icon'] ) ? trim( preg_replace( '/[^a-zA-Z0-9_ -]/', '', (string) $item['item_icon'] ) ) : '';
						if ( $icon_class ) { $cards .= '<i class="' . esc_attr( $icon_class ) . ' alify-editor-content-icon" aria-hidden="true"></i>'; }
						if ( $image_id ) { $cards .= wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'alify-editor-content-image', 'loading' => 'lazy' ) ); }
						if ( ! empty( $item['item_kicker'] ) ) { $cards .= '<span class="alify-editor-content-kicker">' . esc_html( $item['item_kicker'] ) . '</span>'; }
						if ( ! empty( $item['item_value'] ) ) { $cards .= '<strong class="alify-editor-content-value">' . esc_html( $item['item_value'] ) . '</strong>'; }
						if ( $title ) { $cards .= '<h3>' . esc_html( $title ) . '</h3>'; }
						if ( ! empty( $item['item_label'] ) ) { $cards .= '<span class="alify-editor-content-label">' . esc_html( $item['item_label'] ) . '</span>'; }
						if ( $description ) { $cards .= '<p>' . esc_html( $description ) . '</p>'; }
						if ( $body ) { $cards .= '<div class="alify-editor-content-body">' . $body . '</div>'; }
						if ( $link ) { $cards .= '<a href="' . $link . '" class="alify-editor-content-link">Learn More <span aria-hidden="true">↗</span></a>'; }
						$cards .= '</article>';
					}
					$cards .= '</div>';
					$inner .= $cards;
				}
				if ( array_key_exists( 'section_enabled', $row ) && ! $row['section_enabled'] ) { return ''; }
				$opening = $m[1];
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$field = 'section_show_' . $device;
					if ( array_key_exists( $field, $row ) ) {
						$visible = ! empty( $row[ $field ] ) ? '1' : '0';
						$opening = rtrim( substr( $opening, 0, -1 ) ) . ' data-alify-show-' . $device . '="' . $visible . '">';
					}
				}
				return $opening . $inner . $m[3];
			}, $html, 1 );
		}
		return $html;
	}
}

/**
 * Apply the generated, pre-filled per-page title/body fields to their marked template elements.
 */
function alify_apply_generated_page_copy_fields( $html, $post_id, $part_slug = '' ) {
	if ( ! is_string( $html ) ) { return $html; }
	$page_slug = '' !== $part_slug ? $part_slug : ( $post_id ? get_post_field( 'post_name', $post_id ) : '' );
	static $catalog = null;
	if ( null === $catalog ) { $catalog = require ALIFY_THEME_DIR . '/inc/page-content-catalog.php'; }
	if ( empty( $catalog[ $page_slug ]['fields'] ) ) { return preg_replace( '/\\sdata-alify-copy(?:-[a-z-]+)?="[^"]*"/', '', $html ); }
	$option_page = '';
	if ( is_404() && '404' === $page_slug ) { $option_page = 'alify_conversion'; }
	elseif ( 'blog' === $page_slug && ( is_home() || is_archive() ) ) { $option_page = 'alify_archives'; }
	elseif ( is_post_type_archive() || is_tax() ) {
		$archive_option_pages = array(
			'services' => 'alify_archive_services', 'work' => 'alify_archive_work',
			'industries' => 'alify_archive_industries', 'case-studies' => 'alify_archive_case_studies',
			'testimonials' => 'alify_archive_testimonials', 'faq' => 'alify_archive_faq',
		);
		$option_page = $archive_option_pages[ $page_slug ] ?? 'alify_archives';
	}
	$options = $option_page ? get_option( 'alify_cf_options_' . $option_page, array() ) : array();
	if ( ! is_array( $options ) ) { $options = array(); }
	foreach ( $catalog[ $page_slug ]['fields'] as $field ) {
		if ( $option_page ) {
			$option_key = array_key_exists( $field['name'], $options ) ? $field['name'] : ( ! empty( $field['legacy_name'] ) && array_key_exists( $field['legacy_name'], $options ) ? $field['legacy_name'] : '' );
			$value = $option_key && is_scalar( $options[ $option_key ] ) ? (string) $options[ $option_key ] : null;
		}
		else {
			$meta_key = '_alify_cf_' . $field['name'];
			$has_value = $post_id && metadata_exists( 'post', $post_id, $meta_key );
			$value = $post_id ? get_post_meta( $post_id, $meta_key, true ) : null;
			if ( ! $has_value && $post_id && ! empty( $field['legacy_name'] ) ) {
				$legacy_key = '_alify_cf_' . $field['legacy_name'];
				$has_value = metadata_exists( 'post', $post_id, $legacy_key );
				if ( $has_value ) { $value = get_post_meta( $post_id, $legacy_key, true ); }
			}
			if ( ! $has_value && ( ! is_string( $value ) || '' === $value ) ) {
				$value = ( $post_id && function_exists( 'alify_get_field' ) ) ? alify_get_field( $field['name'], $post_id ) : $value;
			}
		}
		if ( ! is_string( $value ) || $value === $field['default'] ) { continue; }
		$name = preg_quote( $field['name'], '~' );
		if ( ! empty( $field['attribute'] ) ) {
			$attribute = preg_quote( $field['attribute'], '~' );
			$pattern = '~<[^>]*\\bdata-alify-copy-' . $attribute . '="' . $name . '"[^>]*>~i';
			$html = preg_replace_callback( $pattern, function ( $match ) use ( $value, $attribute ) {
				$clean = in_array( $attribute, array( 'href', 'src' ), true ) ? esc_url( $value ) : esc_attr( $value );
				$attribute_pattern = '~(\\b' . preg_quote( $attribute, '~' ) . '=")[^"]*(")~i';
				return preg_replace_callback( $attribute_pattern, function ( $attribute_match ) use ( $clean ) {
					return $attribute_match[1] . $clean . $attribute_match[2];
				}, $match[0], 1 );
			}, $html, 1 );
		} else {
			$tag = preg_quote( $field['tag'], '~' );
			$pattern = '~(<(' . $tag . ')\\b[^>]*\\bdata-alify-copy="' . $name . '"[^>]*>)(.*?)(</\\2>)~si';
			$html = preg_replace_callback( $pattern, function ( $match ) use ( $value ) { return $match[1] . nl2br( esc_html( $value ) ) . $match[4]; }, $html, 1 );
		}
	}
	return preg_replace( '/\\sdata-alify-copy(?:-[a-z-]+)?="[^"]*"/', '', $html );
}
