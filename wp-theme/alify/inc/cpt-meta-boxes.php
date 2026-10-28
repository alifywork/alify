<?php
/**
 * Native WordPress Custom Meta Boxes & Media Library Pickers for All 6 ALIFY CPTs
 * Zero third-party ACF dependency required.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return structured meta field schema per Custom Post Type.
 */
function alify_get_cpt_meta_schema() {
	return array(
		'alify_service'     => array(
			'title'  => __( 'ALIFY Service Card & Single Page Blueprint', 'alify' ),
			'fields' => array(
				'_alify_svc_notch'      => array( 'Notch Code Badge', 'text', 'e.g. 01 · WEB' ),
				'_alify_svc_tag'        => array( 'Discipline Pill Label', 'text', 'e.g. Engineering' ),
				'_alify_svc_filter'     => array( 'Archive Filter Tab (`design`, `engineering`, `growth`)', 'text', 'engineering' ),
				'_alify_svc_icon'       => array( 'Font Awesome Icon Class', 'text', 'e.g. fa-solid fa-code' ),
				'_alify_svc_bg_image'   => array( 'Card & Hero Background Image URL', 'image', '' ),
				'_alify_svc_points'     => array( 'Key Deliverables / Bullet Points (1 per line)', 'textarea', "Custom Semantic HTML, CSS & Next-Gen JS\n99+ Core Web Vitals & Edge Performance\nCMS & Enterprise API Integrations" ),
				'_alify_svc_stats'      => array( 'Single Page Banner Stats (`Value | Label`, 1 per line)', 'textarea', "99+ | Core Web Vitals\n< 0.8s | Avg. LCP Speed\n+142% | Conversion Lift" ),
				'_alify_svc_deliverables' => array( 'Single Page Bento Cards (`Icon | Title | Description`, 1 per line)', 'textarea', '' ),
			),
		),
		'alify_project'     => array(
			'title'  => __( 'ALIFY Project Showcase & 6-Screen Lightbox Gallery', 'alify' ),
			'fields' => array(
				'_alify_proj_num'         => array( 'Panel Number', 'text', 'e.g. 01' ),
				'_alify_proj_short_title' => array( 'Short Spine Title', 'text', 'e.g. FinPulse AI Platform' ),
				'_alify_proj_client'      => array( 'Client & Location Label', 'text', 'e.g. FINPULSE AI · SAN FRANCISCO' ),
				'_alify_proj_sector'      => array( 'Sector Badge Label', 'text', 'e.g. FinTech & SaaS' ),
				'_alify_proj_filter'      => array( 'Work Grid Filter (`web-saas`, `commerce-brand`, `ai-cloud`)', 'text', 'web-saas' ),
				'_alify_proj_year'        => array( 'Launch Year', 'text', '2026' ),
				'_alify_proj_sprint'      => array( 'Sprint Duration', 'text', 'e.g. 10-Week Sprint' ),
				'_alify_proj_metric'      => array( 'Headline Outcome Metric', 'text', 'e.g. +184% Demo Pipeline' ),
				'_alify_proj_chips'       => array( 'Deliverable Chips (comma-separated)', 'text', 'Web Architecture, Design System, GSAP Motion' ),
				'_alify_proj_case_slug'   => array( 'Linked Case Study Slug (optional)', 'text', 'e.g. cs-finpulse' ),
				'_alify_proj_bg_image'    => array( 'Primary Project Background Image URL', 'image', '' ),
				'_alify_proj_brief'       => array( 'Single Page — The Brief / Challenge', 'textarea', '' ),
				'_alify_proj_craft'       => array( 'Single Page — Engineering & Design Craft', 'textarea', '' ),
				'_alify_proj_gallery'     => array( 'Interactive Lightbox Gallery Screens (`Image URL | Tag | Title | Caption`, 1 per line)', 'gallery', '' ),
			),
		),
		'alify_industry'    => array(
			'title'  => __( 'ALIFY Industry Vertical Blueprint', 'alify' ),
			'fields' => array(
				'_alify_ind_code'         => array( 'Industry Code Badge', 'text', 'e.g. 01 · FINTECH' ),
				'_alify_ind_sector_group' => array( 'Filter Group (`finance-cloud`, `commerce-property`, `health-edu-mobility`)', 'text', 'finance-cloud' ),
				'_alify_ind_icon'         => array( 'Font Awesome Icon Class', 'text', 'e.g. fa-solid fa-landmark' ),
				'_alify_ind_stat'         => array( 'Footer Benchmark Stat', 'text', 'e.g. Avg. +148% Qualified Pipeline' ),
				'_alify_ind_bg_image'     => array( 'Card & Hero Background Image URL', 'image', '' ),
				'_alify_ind_points'       => array( 'Sector Deliverables (1 per line)', 'textarea', "PCI-Ready Checkout & Onboarding UX\nReal-Time Portfolio & Ledger UI\nInstitutional Brand & Web Systems" ),
				'_alify_ind_stats'        => array( 'Single Page Stats (`Value | Label`, 1 per line)', 'textarea', "+148% | Qualified Pipeline\n99.9% | Uptime & Compliance UX\n< 0.8s | Global Load Time" ),
			),
		),
		'alify_case_study'  => array(
			'title'  => __( 'ALIFY Case Study Benchmarks, KPIs & Executive Quote', 'alify' ),
			'fields' => array(
				'_alify_cs_filter'         => array( 'Filter Category (`fintech-saas`, `commerce-brand`, `health-proptech`)', 'text', 'fintech-saas' ),
				'_alify_cs_tab_index'      => array( 'Home Switcher Tab Label', 'text', 'e.g. 01 · FINTECH SAAS' ),
				'_alify_cs_client_short'   => array( 'Client Short Name', 'text', 'e.g. FinPulse AI' ),
				'_alify_cs_client_kicker'  => array( 'Client & HQ Kicker', 'text', 'e.g. FINPULSE AI · SAN FRANCISCO' ),
				'_alify_cs_sector_badge'   => array( 'Visual Glass Badge 1 (Sector)', 'text', 'e.g. Enterprise FinTech' ),
				'_alify_cs_duration_badge' => array( 'Visual Glass Badge 2 (Duration)', 'text', 'e.g. 10-Week Transformation' ),
				'_alify_cs_bg_image'       => array( 'Case Study Visual Image URL', 'image', '' ),
				'_alify_cs_benchmarks'     => array( 'Before vs. After Benchmarks (`Label | Before | After`, 1 per line)', 'textarea', "Mobile Speed | 41 | 99\nDemo CVR | 1.3% | 3.8%" ),
				'_alify_cs_bottleneck'     => array( '01 · The Bottleneck', 'textarea', '' ),
				'_alify_cs_architecture'   => array( '02 · ALIFY Architecture', 'textarea', '' ),
				'_alify_cs_kpis'           => array( '3 Verified KPI Tiles (`Value | Label`, 1 per line)', 'textarea', "+184% | Qualified Enterprise Demos\n0.7s | Global LCP Load Time\n3.4x | Pipeline ROI in 60 Days" ),
				'_alify_cs_chips'          => array( 'Architecture Blueprint Chips (comma-separated)', 'text', 'Multi-Brand Figma Token System, Custom Semantic Frontend & GSAP, Interactive ROI Simulator' ),
				'_alify_cs_quote'          => array( 'Executive Quote Text', 'textarea', '' ),
				'_alify_cs_quote_author'   => array( 'Executive Quote Author & Role', 'text', 'e.g. Sarah Jenkins — VP of Marketing, FinPulse AI' ),
			),
		),
		'alify_testimonial' => array(
			'title'  => __( 'ALIFY Client Testimonial Details (Post Title = Client Name, Editor = Quote)', 'alify' ),
			'fields' => array(
				'_alify_testi_role'     => array( 'Client Role & Company', 'text', 'e.g. VP of Marketing, FinPulse AI' ),
				'_alify_testi_initials' => array( 'Avatar Initials (2 letters)', 'text', 'e.g. SJ' ),
				'_alify_testi_rating'   => array( 'Star Rating Score', 'text', '5.0' ),
				'_alify_testi_outcome'  => array( 'Verified Outcome Pill', 'text', 'e.g. +184% Demo Pipeline' ),
				'_alify_testi_tag'      => array( 'Capability Tag Label', 'text', 'e.g. Web & Engineering' ),
				'_alify_testi_filter'   => array( 'Archive Filter (`web`, `branding`, `systems`, `growth`)', 'text', 'web' ),
			),
		),
		'alify_faq'         => array(
			'title'  => __( 'ALIFY FAQ Settings (Post Title = Question, Editor = Answer)', 'alify' ),
			'fields' => array(
				'_alify_faq_pillar'        => array( 'Knowledge Pillar (`scope-sprints`, `pricing-billing`, `engineering-stack`, `design-ownership`, `support-retainers`)', 'text', 'scope-sprints' ),
				'_alify_faq_show_reusable' => array( 'Include in Global 6-Item Reusable FAQ Section? (`1` = Yes, `0` = No)', 'text', '0' ),
			),
		),
	);
}

/**
 * Register Meta Boxes on CPT Edit Screens.
 */
function alify_register_cpt_meta_boxes() {
	$schema = alify_get_cpt_meta_schema();
	foreach ( $schema as $post_type => $box ) {
		add_meta_box(
			'alify_meta_' . $post_type,
			$box['title'],
			'alify_render_cpt_meta_box',
			$post_type,
			'normal',
			'high',
			array( 'fields' => $box['fields'] )
		);
	}
}
add_action( 'add_meta_boxes', 'alify_register_cpt_meta_boxes' );

/**
 * Render Meta Box Fields + Media Library Picker Buttons.
 */
function alify_render_cpt_meta_box( $post, $metabox ) {
	wp_nonce_field( 'alify_save_cpt_meta', 'alify_cpt_meta_nonce' );
	$fields = isset( $metabox['args']['fields'] ) ? $metabox['args']['fields'] : array();

	echo '<div class="alify-admin-meta-grid" style="display:grid;gap:16px;padding:8px 0;">';
	foreach ( $fields as $meta_key => $cfg ) {
		list( $label, $type, $placeholder ) = $cfg;
		$val = get_post_meta( $post->ID, $meta_key, true );

		echo '<div class="alify-admin-field">';
		echo '<label for="' . esc_attr( $meta_key ) . '" style="display:block;font-weight:600;margin-bottom:6px;">' . esc_html( $label ) . '</label>';

		if ( 'textarea' === $type ) {
			echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="3" class="large-text" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $val ) . '</textarea>';
		} elseif ( 'image' === $type ) {
			echo '<div style="display:flex;gap:8px;align-items:center;">';
			echo '<input type="text" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" style="flex:1;" placeholder="https://... or select via Media Library" />';
			echo '<button type="button" class="button alify-media-pick-btn" data-target="' . esc_attr( $meta_key ) . '">' . esc_html__( 'Choose Image', 'alify' ) . '</button>';
			echo '</div>';
		} elseif ( 'gallery' === $type ) {
			echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="6" class="large-text code" placeholder="https://.../screen1.jpg | Design System | Token Architecture | 60+ UI components">' . esc_textarea( $val ) . '</textarea>';
			echo '<p style="margin-top:6px;"><button type="button" class="button button-secondary alify-gallery-pick-btn" data-target="' . esc_attr( $meta_key ) . '">' . esc_html__( '+ Append Images from Media Library', 'alify' ) . '</button></p>';
		} else {
			echo '<input type="text" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $val ) . '" class="large-text" placeholder="' . esc_attr( $placeholder ) . '" />';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Enqueue `wp.media` Picker Script on CPT Edit Screens.
 */
function alify_admin_enqueue_cpt_media( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	$schema = alify_get_cpt_meta_schema();
	if ( ! $screen || ! isset( $schema[ $screen->post_type ] ) ) {
		return;
	}

	wp_enqueue_media();
	$inline_js = "
	jQuery(function($){
		$(document).on('click', '.alify-media-pick-btn', function(e){
			e.preventDefault();
			var targetId = $(this).data('target');
			var frame = wp.media({ title: 'Select Image', multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('#' + targetId).val(att.url);
			});
			frame.open();
		});
		$(document).on('click', '.alify-gallery-pick-btn', function(e){
			e.preventDefault();
			var targetId = $(this).data('target');
			var frame = wp.media({ title: 'Select Lightbox Gallery Screens', multiple: true, library: { type: 'image' } });
			frame.on('select', function(){
				var lines = [];
				frame.state().get('selection').each(function(model){
					var att = model.toJSON();
					var title = att.title || 'Project Interface';
					var cap = att.caption || att.alt || 'Engineered by ALIFY Studio.';
					lines.push(att.url + ' | Product Showcase | ' + title + ' | ' + cap);
				});
				var cur = $('#' + targetId).val().trim();
				$('#' + targetId).val(cur ? (cur + '\\n' + lines.join('\\n')) : lines.join('\\n'));
			});
			frame.open();
		});
	});
	";
	wp_add_inline_script( 'jquery-core', $inline_js );
}
add_action( 'admin_enqueue_scripts', 'alify_admin_enqueue_cpt_media' );

/**
 * Save Meta Box Fields Securely.
 */
function alify_save_cpt_meta_boxes( $post_id, $post ) {
	if ( ! isset( $_POST['alify_cpt_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_cpt_meta_nonce'] ) ), 'alify_save_cpt_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$schema = alify_get_cpt_meta_schema();
	if ( ! isset( $schema[ $post->post_type ] ) ) {
		return;
	}

	foreach ( $schema[ $post->post_type ]['fields'] as $meta_key => $cfg ) {
		if ( ! isset( $_POST[ $meta_key ] ) ) {
			continue;
		}
		$type = $cfg[1];
		$raw  = wp_unslash( $_POST[ $meta_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( 'textarea' === $type || 'gallery' === $type ) {
			$clean = sanitize_textarea_field( $raw );
		} elseif ( 'image' === $type ) {
			$clean = esc_url_raw( $raw );
		} else {
			$clean = sanitize_text_field( $raw );
		}

		update_post_meta( $post_id, $meta_key, $clean );
	}
}
add_action( 'save_post', 'alify_save_cpt_meta_boxes', 10, 2 );
