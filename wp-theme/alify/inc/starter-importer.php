<?php
/**
 * 1-Click Starter Content Importer (`Appearance -> ALIFY Starter Import`)
 * Imports all 8 Services, 6 Projects (+ 36 Lightbox Screens), 9 Industries,
 * 6 Case Studies, 12 Testimonials, 20 FAQs, and 6 Blog Posts into WordPress DB.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper: Find existing post by slug & post_type or create a new one idempotently.
 */
function alify_upsert_starter_post( $post_type, $slug, $title, $content, $excerpt, $order = 0 ) {
	$existing = get_page_by_path( $slug, OBJECT, $post_type );
	if ( $existing ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => $post_type,
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
			'menu_order'   => (int) $order,
		)
	);

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Run Full Starter Import (Pages + Menus + 6 CPTs + 6 Blog Posts).
 */
function alify_import_all_starter_content() {
	if ( function_exists( 'alify_seed_default_pages' ) ) {
		alify_seed_default_pages();
	}

	$json_path = ALIFY_THEME_DIR . '/inc/starter-data.json';
	if ( ! file_exists( $json_path ) ) {
		return array();
	}

	$raw  = file_get_contents( $json_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$data = json_decode( (string) $raw, true );
	if ( ! is_array( $data ) ) {
		return array();
	}

	$imported_counts = array(
		'services'     => 0,
		'projects'     => 0,
		'industries'   => 0,
		'case_studies' => 0,
		'testimonials' => 0,
		'faqs'         => 0,
		'blog_posts'   => 0,
	);

	// 1. Services (8)
	if ( ! empty( $data['services'] ) ) {
		foreach ( $data['services'] as $i => $s ) {
			$pid = alify_upsert_starter_post( 'alify_service', $s['slug'], $s['title'], $s['excerpt'], $s['excerpt'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_svc_notch', $s['notch'] );
				update_post_meta( $pid, '_alify_svc_tag', $s['tag'] );
				update_post_meta( $pid, '_alify_svc_filter', $s['filter'] );
				update_post_meta( $pid, '_alify_svc_icon', $s['icon'] );
				update_post_meta( $pid, '_alify_svc_bg_image', $s['bg'] );
				update_post_meta( $pid, '_alify_svc_points', implode( "\n", $s['points'] ) );
				$imported_counts['services']++;
			}
		}
	}

	// 2. Projects (6 + 6-Screen Lightbox Galleries)
	if ( ! empty( $data['projects'] ) ) {
		foreach ( $data['projects'] as $i => $p ) {
			$pid = alify_upsert_starter_post( 'alify_project', $p['slug'], $p['title'], $p['brief'], $p['excerpt'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_proj_num', $p['num'] );
				update_post_meta( $pid, '_alify_proj_short_title', $p['short_title'] );
				update_post_meta( $pid, '_alify_proj_client', $p['client'] );
				update_post_meta( $pid, '_alify_proj_sector', $p['sector'] );
				update_post_meta( $pid, '_alify_proj_filter', $p['filter'] );
				update_post_meta( $pid, '_alify_proj_year', $p['year'] );
				update_post_meta( $pid, '_alify_proj_sprint', $p['sprint'] );
				update_post_meta( $pid, '_alify_proj_metric', $p['metric'] );
				update_post_meta( $pid, '_alify_proj_chips', $p['chips'] );
				update_post_meta( $pid, '_alify_proj_case_slug', $p['case_slug'] );
				update_post_meta( $pid, '_alify_proj_bg_image', $p['bg'] );
				update_post_meta( $pid, '_alify_proj_brief', $p['brief'] );
				update_post_meta( $pid, '_alify_proj_craft', $p['craft'] );
				update_post_meta( $pid, '_alify_proj_gallery', implode( "\n", $p['gallery'] ) );
				$imported_counts['projects']++;
			}
		}
	}

	// 3. Industries (9)
	if ( ! empty( $data['industries'] ) ) {
		foreach ( $data['industries'] as $i => $ind ) {
			$pid = alify_upsert_starter_post( 'alify_industry', $ind['slug'], $ind['title'], $ind['excerpt'], $ind['excerpt'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_ind_code', $ind['code'] );
				update_post_meta( $pid, '_alify_ind_sector_group', $ind['sector_group'] );
				update_post_meta( $pid, '_alify_ind_icon', $ind['icon'] );
				update_post_meta( $pid, '_alify_ind_stat', $ind['stat'] );
				update_post_meta( $pid, '_alify_ind_bg_image', $ind['bg'] );
				update_post_meta( $pid, '_alify_ind_points', implode( "\n", $ind['points'] ) );
				$imported_counts['industries']++;
			}
		}
	}

	// 4. Case Studies (6)
	if ( ! empty( $data['case_studies'] ) ) {
		foreach ( $data['case_studies'] as $i => $cs ) {
			$pid = alify_upsert_starter_post( 'alify_case_study', $cs['slug'], $cs['title'], $cs['architecture'], $cs['bottleneck'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_cs_filter', $cs['filter'] );
				update_post_meta( $pid, '_alify_cs_client_short', $cs['client_short'] );
				update_post_meta( $pid, '_alify_cs_client_kicker', $cs['kicker'] );
				update_post_meta( $pid, '_alify_cs_sector_badge', $cs['sector_badge'] );
				update_post_meta( $pid, '_alify_cs_duration_badge', $cs['duration_badge'] );
				update_post_meta( $pid, '_alify_cs_bg_image', $cs['bg'] );
				update_post_meta( $pid, '_alify_cs_benchmarks', implode( "\n", $cs['benchmarks'] ) );
				update_post_meta( $pid, '_alify_cs_bottleneck', $cs['bottleneck'] );
				update_post_meta( $pid, '_alify_cs_architecture', $cs['architecture'] );
				update_post_meta( $pid, '_alify_cs_kpis', implode( "\n", $cs['kpis'] ) );
				update_post_meta( $pid, '_alify_cs_chips', $cs['chips'] );
				update_post_meta( $pid, '_alify_cs_quote', $cs['quote'] );
				update_post_meta( $pid, '_alify_cs_quote_author', $cs['quote_author'] );
				$imported_counts['case_studies']++;
			}
		}
	}

	// 5. Testimonials (12)
	if ( ! empty( $data['testimonials'] ) ) {
		foreach ( $data['testimonials'] as $i => $t ) {
			$pid = alify_upsert_starter_post( 'alify_testimonial', $t['slug'], $t['name'], $t['quote'], $t['quote'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_testi_role', $t['role'] );
				update_post_meta( $pid, '_alify_testi_initials', $t['initials'] );
				update_post_meta( $pid, '_alify_testi_rating', $t['rating'] );
				update_post_meta( $pid, '_alify_testi_outcome', $t['outcome'] );
				update_post_meta( $pid, '_alify_testi_tag', $t['tag'] );
				update_post_meta( $pid, '_alify_testi_filter', $t['filter'] );
				$imported_counts['testimonials']++;
			}
		}
	}

	// 6. FAQs (20 + `faq_pillar` taxonomy)
	if ( ! empty( $data['faqs'] ) ) {
		foreach ( $data['faqs'] as $i => $f ) {
			$pid = alify_upsert_starter_post( 'alify_faq', $f['slug'], $f['question'], $f['answer'], $f['answer'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_faq_pillar', $f['pillar'] );
				update_post_meta( $pid, '_alify_faq_show_reusable', $f['show_reusable'] );
				if ( taxonomy_exists( 'faq_pillar' ) ) {
					wp_set_object_terms( $pid, array( $f['pillar'] ), 'faq_pillar', false );
				}
				$imported_counts['faqs']++;
			}
		}
	}

	// 7. Native WordPress Blog Posts (6)
	if ( ! empty( $data['blog_posts'] ) ) {
		foreach ( $data['blog_posts'] as $i => $bp ) {
			$pid = alify_upsert_starter_post( 'post', $bp['slug'], $bp['title'], $bp['content'], $bp['excerpt'], $i + 1 );
			if ( $pid > 0 ) {
				update_post_meta( $pid, '_alify_post_bg_image', $bp['image'] );
				if ( ! empty( $bp['category'] ) ) {
					$term = term_exists( $bp['category'], 'category' );
					if ( ! $term ) {
						$term = wp_insert_term( $bp['category'], 'category' );
					}
					if ( ! is_wp_error( $term ) ) {
						$cat_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
						wp_set_post_categories( $pid, array( $cat_id ) );
					}
				}
				if ( ! empty( $bp['tags'] ) && is_array( $bp['tags'] ) ) {
					wp_set_post_tags( $pid, $bp['tags'], false );
				}
				$imported_counts['blog_posts']++;
			}
		}
	}

	flush_rewrite_rules();
	return $imported_counts;
}

/**
 * Register WP Admin Submenu: Appearance -> ALIFY Starter Import
 */
function alify_register_starter_import_page() {
	add_theme_page(
		__( 'ALIFY Starter Content Importer', 'alify' ),
		__( 'ALIFY Starter Import', 'alify' ),
		'manage_options',
		'alify-starter-import',
		'alify_render_starter_import_page'
	);
}
add_action( 'admin_menu', 'alify_register_starter_import_page' );

/**
 * Handle 1-Click Starter Import Form Submission
 */
function alify_handle_starter_import_submit() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['alify_run_starter_import'] ) && check_admin_referer( 'alify_starter_import_nonce' ) ) {
		alify_import_all_starter_content();
		wp_safe_redirect( admin_url( 'themes.php?page=alify-starter-import&imported=1' ) );
		exit;
	}
}
add_action( 'admin_init', 'alify_handle_starter_import_submit' );

/**
 * Render Appearance -> ALIFY Starter Import Admin Dashboard
 */
function alify_render_starter_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$stats = array(
		'Studio Pages (`page`)'               => (int) wp_count_posts( 'page' )->publish,
		'Services (`alify_service`)'          => (int) wp_count_posts( 'alify_service' )->publish,
		'Projects (`alify_project`)'          => (int) wp_count_posts( 'alify_project' )->publish,
		'Industries (`alify_industry`)'       => (int) wp_count_posts( 'alify_industry' )->publish,
		'Case Studies (`alify_case_study`)'   => (int) wp_count_posts( 'alify_case_study' )->publish,
		'Testimonials (`alify_testimonial`)'  => (int) wp_count_posts( 'alify_testimonial' )->publish,
		'FAQs (`alify_faq`)'                  => (int) wp_count_posts( 'alify_faq' )->publish,
		'Blog Articles (`post`)'              => (int) wp_count_posts( 'post' )->publish,
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ALIFY Studio — 1-Click Starter Content & Dynamic CMS Importer', 'alify' ); ?></h1>

		<?php if ( isset( $_GET['imported'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Import Complete!', 'alify' ); ?></strong> <?php esc_html_e( 'All 21 Pages, 5 Menus, 8 Services, 6 Projects (+ Lightbox Galleries), 9 Industries, 6 Case Studies, 12 Testimonials, 20 FAQs, and 6 Blog Posts are now live and editable in WordPress Admin.', 'alify' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width:780px;padding:20px 24px;margin-top:18px;">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Live WordPress Database Inventory', 'alify' ); ?></h2>
			<p><?php esc_html_e( 'Import all ALIFY starter content into native WordPress Custom Post Types so you can add, edit, reorder, or delete any Service, Project, Lightbox Gallery, Industry, Case Study, Review, FAQ, or Blog Post directly from WP Admin.', 'alify' ); ?></p>

			<table class="widefat striped" style="margin:16px 0 22px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Content Type', 'alify' ); ?></th>
						<th><?php esc_html_e( 'Published in Database', 'alify' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $stats as $label => $cnt ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td><?php echo esc_html( (string) $cnt ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" action="">
				<?php wp_nonce_field( 'alify_starter_import_nonce' ); ?>
				<input type="hidden" name="alify_run_starter_import" value="1" />
				<button type="submit" class="button button-primary button-hero">
					<?php esc_html_e( 'Import / Sync All ALIFY Starter Content (67 Items + 21 Pages + 5 Menus)', 'alify' ); ?>
				</button>
			</form>
			<p class="description" style="margin-top:10px;">
				<?php esc_html_e( 'Safe & idempotent: running this again only adds missing items by slug and never duplicates existing posts.', 'alify' ); ?>
			</p>
		</div>
	</div>
	<?php
}
