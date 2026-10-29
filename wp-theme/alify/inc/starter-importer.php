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
 * Idempotently Import a Local Theme Image (`assets/images/...`) Into WordPress Media Library (`wp_posts` attachment).
 * Never duplicates: tracks `_alify_source_asset` meta key on each imported attachment.
 */
function alify_import_theme_asset_to_media_library( $rel_or_url, $title = '', $parent_post_id = 0 ) {
	$clean = trim( (string) $rel_or_url );
	if ( '' === $clean ) {
		return 0;
	}
	if ( defined( 'ALIFY_THEME_URI' ) && 0 === strpos( $clean, ALIFY_THEME_URI ) ) {
		$clean = ltrim( substr( $clean, strlen( ALIFY_THEME_URI ) ), '/' );
	}
	$clean = ltrim( $clean, '/' );
	if ( 0 !== strpos( $clean, 'assets/images/' ) || false !== strpos( $clean, '..' ) ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_alify_source_asset',
			'meta_value'     => $clean,
		)
	);
	if ( ! empty( $existing[0] ) ) {
		return (int) $existing[0];
	}

	$src_file = ALIFY_THEME_DIR . '/' . $clean;
	if ( ! file_exists( $src_file ) || ! is_readable( $src_file ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['path'] ) ) {
		return 0;
	}

	$filename  = wp_unique_filename( $uploads['path'], basename( $src_file ) );
	$dest_file = trailingslashit( $uploads['path'] ) . $filename;
	if ( ! @copy( $src_file, $dest_file ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	$filetype = wp_check_filetype( $filename, null );
	$att_data = array(
		'guid'           => trailingslashit( $uploads['url'] ) . $filename,
		'post_mime_type' => ! empty( $filetype['type'] ) ? $filetype['type'] : 'image/jpeg',
		'post_title'     => '' !== $title ? sanitize_text_field( $title ) : preg_replace( '/\.[^.]+$/', '', basename( $src_file ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$att_id = wp_insert_attachment( $att_data, $dest_file, (int) $parent_post_id );
	if ( is_wp_error( $att_id ) || ! $att_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( $att_id, $dest_file );
	wp_update_attachment_metadata( $att_id, $meta );
	update_post_meta( $att_id, '_alify_source_asset', $clean );
	if ( '' !== $title ) {
		update_post_meta( $att_id, '_wp_attachment_image_alt', sanitize_text_field( $title ) );
	}

	return (int) $att_id;
}

/**
 * Sync All Starter Featured Images (`_thumbnail_id`) & `Alify Custom Field` Attachment IDs Across CPTs & Blog Posts.
 */
function alify_sync_all_starter_media_library() {
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( 'admin' );
	}

	$map = array(
		'alify_service'    => array( '_alify_svc_bg_image', '_alify_cf_svc_bg_img' ),
		'alify_project'    => array( '_alify_proj_bg_image', '_alify_cf_proj_bg_img' ),
		'alify_industry'   => array( '_alify_ind_bg_image', '_alify_cf_ind_bg_img' ),
		'alify_case_study' => array( '_alify_cs_bg_image', '_alify_cf_cs_bg_img' ),
		'post'             => array( '_alify_post_bg_image', '_alify_cf_post_bg_img' ),
	);

	$synced = 0;
	foreach ( $map as $pt => $keys ) {
		list( $src_meta, $cf_img_meta ) = $keys;
		$posts                          = get_posts(
			array(
				'post_type'      => $pt,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);
		foreach ( $posts as $p ) {
			$bg_path = get_post_meta( $p->ID, $src_meta, true );
			$att_id  = alify_import_theme_asset_to_media_library( $bg_path, $p->post_title, $p->ID );
			if ( $att_id > 0 ) {
				if ( ! has_post_thumbnail( $p->ID ) ) {
					set_post_thumbnail( $p->ID, $att_id );
				}
				if ( ! get_post_meta( $p->ID, $cf_img_meta, true ) ) {
					update_post_meta( $p->ID, $cf_img_meta, $att_id );
				}
				$synced++;
			}

			// Project Lightbox Gallery Attachment IDs (`proj_gallery_media` + `proj_gallery_rows`)
			if ( 'alify_project' === $pt ) {
				$gal_raw = get_post_meta( $p->ID, '_alify_proj_gallery', true );
				if ( ! empty( $gal_raw ) ) {
					$lines   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $gal_raw ) ) );
					$gal_ids = array();
					$cf_rows = array();
					foreach ( $lines as $ln ) {
						$parts = array_map( 'trim', explode( '|', $ln, 4 ) );
						$gpath = isset( $parts[0] ) ? $parts[0] : '';
						$gtag  = isset( $parts[1] ) ? $parts[1] : 'Product Showcase';
						$gttl  = isset( $parts[2] ) ? $parts[2] : $p->post_title;
						$gcap  = isset( $parts[3] ) ? $parts[3] : '';
						$gid   = alify_import_theme_asset_to_media_library( $gpath, $gttl, $p->ID );
						if ( $gid > 0 ) {
							$gal_ids[] = $gid;
						}
						$cf_rows[] = array(
							'img_id'  => $gid > 0 ? $gid : '',
							'url'     => $gpath,
							'tag'     => $gtag,
							'title'   => $gttl,
							'caption' => $gcap,
						);
					}
					if ( ! empty( $gal_ids ) && ! get_post_meta( $p->ID, '_alify_cf_proj_gallery_media', true ) ) {
						update_post_meta( $p->ID, '_alify_cf_proj_gallery_media', $gal_ids );
					}
					if ( ! empty( $cf_rows ) ) {
						update_post_meta( $p->ID, '_alify_cf_proj_gallery_rows', $cf_rows );
					}
				}
			}
		}
	}

	return $synced;
}

/**
 * Helper: Find existing post by slug & post_type or create a new one idempotently.
 */
function alify_upsert_starter_post( $post_type, $slug, $title, $content, $excerpt, $order = 0 ) {
	$existing = get_page_by_path( $slug, OBJECT, $post_type );
	if ( $existing ) {
		if ( false !== strpos( (string) $existing->post_content, 'undefined' ) ) {
			wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
				)
			);
		}
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

	if ( function_exists( 'alify_prepopulate_post_cf_from_starter' ) ) {
		$all_starter_ids = get_posts(
			array(
				'post_type'      => array( 'alify_service', 'alify_project', 'alify_industry', 'alify_case_study', 'alify_testimonial', 'alify_faq', 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);
		foreach ( $all_starter_ids as $sid ) {
			alify_prepopulate_post_cf_from_starter( (int) $sid );
		}
	}

	// Auto-import starter visuals into WP Media Library & set Featured Images + CF Attachment IDs!
	alify_sync_all_starter_media_library();

	// Upgrade any legacy `?service=` custom menu links to native `alify_service` CPT permalinks!
	delete_option( 'alify_menus_cpt_upgraded_v1' );
	if ( function_exists( 'alify_upgrade_legacy_menu_query_links' ) ) {
		alify_upgrade_legacy_menu_query_links();
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
	if ( isset( $_POST['alify_run_media_sync'] ) && check_admin_referer( 'alify_starter_import_nonce' ) ) {
		$media_cnt = alify_sync_all_starter_media_library();
		wp_safe_redirect( admin_url( 'themes.php?page=alify-starter-import&media_synced=' . (int) $media_cnt ) );
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
				<p><strong><?php esc_html_e( 'Import Complete!', 'alify' ); ?></strong> <?php esc_html_e( 'All 21 Pages, 5 Native CPT Menus, 8 Services, 6 Projects (+ Lightbox Galleries), 9 Industries, 6 Case Studies, 12 Testimonials, 20 FAQs, 6 Blog Posts, and WP Media Library Featured Images are now live and editable in WordPress Admin.', 'alify' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( isset( $_GET['media_synced'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Media Library Sync Complete!', 'alify' ); ?></strong> <?php echo esc_html( sprintf( __( '%d starter items verified with native WordPress Media Library Featured Images and Alify Custom Field attachment IDs.', 'alify' ), absint( $_GET['media_synced'] ) ) ); ?></p>
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

			<form method="post" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
				<?php wp_nonce_field( 'alify_starter_import_nonce' ); ?>
				<button type="submit" name="alify_run_starter_import" value="1" class="button button-primary button-hero">
					<?php esc_html_e( 'Import / Sync All ALIFY Starter Content + Media Library (67 Items + 21 Pages + 5 Menus)', 'alify' ); ?>
				</button>
				<button type="submit" name="alify_run_media_sync" value="1" class="button button-secondary button-hero">
					<?php esc_html_e( 'Re-Sync Featured Images & Media Library Only', 'alify' ); ?>
				</button>
			</form>
			<p class="description" style="margin-top:10px;">
				<?php esc_html_e( 'Safe & idempotent: running this again only adds missing items by slug and never duplicates existing posts.', 'alify' ); ?>
			</p>
		</div>
	</div>
	<?php
}
