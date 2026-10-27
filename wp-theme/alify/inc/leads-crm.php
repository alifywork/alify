<?php
/**
 * WP Admin "ALIFY Leads & Calls" CRM Upgrade:
 *   - Unread "New" Leads Counter Bubble in WP Admin Sidebar
 *   - Custom Admin List Columns (Ref ID, Type Badge, Status Pill, Client/WhatsApp, Track/Budget, Slot/Source, Date)
 *   - Filter Dropdowns by Lead Type & Pipeline Status
 *   - 1-Click UTF-8 CSV Export (`Export Leads CSV`)
 *   - Structured Lead Detail & Pipeline Status Meta Box
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available Lead Types & Pipeline Statuses.
 */
function alify_get_lead_types() {
	return array(
		'strategy_call' => __( 'Strategy Call', 'alify' ),
		'project_brief' => __( 'Project Brief', 'alify' ),
		'inquiry'       => __( 'General Inquiry', 'alify' ),
		'newsletter'    => __( 'Newsletter', 'alify' ),
	);
}

function alify_get_lead_statuses() {
	return array(
		'new'       => __( 'New', 'alify' ),
		'contacted' => __( 'Contacted', 'alify' ),
		'qualified' => __( 'Qualified', 'alify' ),
		'closed'    => __( 'Closed / Won', 'alify' ),
	);
}

/**
 * 1. Add Unread ("New" Status) Counter Badge Next to "ALIFY Leads & Calls" in WP Admin Menu.
 */
function alify_leads_admin_menu_badge() {
	global $menu;
	if ( ! is_array( $menu ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$new_leads = get_posts(
		array(
			'post_type'      => 'alify_lead',
			'post_status'    => 'publish',
			'posts_per_page' => 99,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_alify_lead_status',
					'value' => 'new',
				),
			),
		)
	);
	$count = is_array( $new_leads ) ? count( $new_leads ) : 0;
	if ( $count < 1 ) {
		return;
	}

	foreach ( $menu as $idx => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=alify_lead' === $item[2] ) {
			$menu[ $idx ][0] .= sprintf( ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>', $count ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			break;
		}
	}
}
add_action( 'admin_menu', 'alify_leads_admin_menu_badge', 99 );

/**
 * 2. Define Custom Columns on `edit.php?post_type=alify_lead`.
 */
function alify_lead_custom_columns( $columns ) {
	return array(
		'cb'           => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
		'alify_ref'    => __( 'Ref & Type', 'alify' ),
		'alify_status' => __( 'Status', 'alify' ),
		'alify_client' => __( 'Client & Direct Contact', 'alify' ),
		'alify_track'  => __( 'Capability & Budget', 'alify' ),
		'alify_slot'   => __( 'Booked Slot / Source', 'alify' ),
		'date'         => __( 'Received', 'alify' ),
	);
}
add_filter( 'manage_alify_lead_posts_columns', 'alify_lead_custom_columns' );

function alify_lead_render_custom_column( $column, $post_id ) {
	$ref     = get_post_meta( $post_id, '_alify_lead_ref', true );
	$type    = get_post_meta( $post_id, '_alify_lead_type', true );
	$status  = get_post_meta( $post_id, '_alify_lead_status', true );
	$name    = get_post_meta( $post_id, '_alify_lead_name', true );
	$email   = get_post_meta( $post_id, '_alify_lead_email', true );
	$phone   = get_post_meta( $post_id, '_alify_lead_phone', true );
	$company = get_post_meta( $post_id, '_alify_lead_company', true );
	$budget  = get_post_meta( $post_id, '_alify_lead_budget', true );
	$cap     = get_post_meta( $post_id, '_alify_lead_capability', true );
	$slot    = get_post_meta( $post_id, '_alify_lead_slot', true );
	$src     = get_post_meta( $post_id, '_alify_lead_source_url', true );

	if ( 'alify_ref' === $column ) {
		$types     = alify_get_lead_types();
		$t_label   = isset( $types[ $type ] ) ? $types[ $type ] : __( 'Inquiry', 'alify' );
		$t_colors  = array(
			'strategy_call' => '#0f8a73',
			'project_brief' => '#e05a3a',
			'newsletter'    => '#d98a2b',
			'inquiry'       => '#0f3473',
		);
		$bg_color  = isset( $t_colors[ $type ] ) ? $t_colors[ $type ] : '#0f3473';
		$edit_link = get_edit_post_link( $post_id );

		echo '<a href="' . esc_url( $edit_link ) . '" style="font-weight:700;font-family:monospace;font-size:12px;text-decoration:none;">' . esc_html( $ref ? $ref : '#' . $post_id ) . '</a><br />';
		echo '<span style="display:inline-block;margin-top:4px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;color:#fff;background:' . esc_attr( $bg_color ) . ';">' . esc_html( $t_label ) . '</span>';
	} elseif ( 'alify_status' === $column ) {
		$statuses = alify_get_lead_statuses();
		$status   = $status ? $status : 'new';
		$s_label  = isset( $statuses[ $status ] ) ? $statuses[ $status ] : ucfirst( $status );
		$s_styles = array(
			'new'       => 'background:#fff4ed;color:#c2410c;border:1px solid #fdba74;',
			'contacted' => 'background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;',
			'qualified' => 'background:#ecfdf5;color:#047857;border:1px solid #6ee7b7;',
			'closed'    => 'background:#f3f4f6;color:#374151;border:1px solid #d1d5db;',
		);
		$st_css = isset( $s_styles[ $status ] ) ? $s_styles[ $status ] : $s_styles['new'];
		echo '<span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;' . esc_attr( $st_css ) . '">' . esc_html( $s_label ) . '</span>';
	} elseif ( 'alify_client' === $column ) {
		echo '<strong>' . esc_html( $name ? $name : '—' ) . '</strong>';
		if ( ! empty( $company ) ) {
			echo ' <span style="color:#6b7280;">(' . esc_html( $company ) . ')</span>';
		}
		if ( ! empty( $email ) ) {
			echo '<br /><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
		}
		if ( ! empty( $phone ) ) {
			$digits = preg_replace( '/[^0-9]/', '', $phone );
			echo '<br /><span style="font-size:12px;">' . esc_html( $phone );
			if ( ! empty( $digits ) ) {
				echo ' &middot; <a href="' . esc_url( 'https://wa.me/' . $digits ) . '" target="_blank" rel="noopener noreferrer" style="color:#0f8a73;font-weight:600;">WhatsApp &nearr;</a>';
			}
			echo '</span>';
		}
	} elseif ( 'alify_track' === $column ) {
		echo '<span>' . esc_html( $cap ? $cap : '—' ) . '</span>';
		if ( ! empty( $budget ) ) {
			echo '<br /><strong style="color:#0f3473;font-size:12px;">Budget: ' . esc_html( $budget ) . '</strong>';
		}
	} elseif ( 'alify_slot' === $column ) {
		if ( ! empty( $slot ) ) {
			echo '<strong style="color:#0f8a73;">' . esc_html( $slot ) . '</strong><br />';
		}
		if ( ! empty( $src ) ) {
			echo '<code style="font-size:11px;">' . esc_html( $src ) . '</code>';
		} elseif ( empty( $slot ) ) {
			echo '—';
		}
	}
}
add_action( 'manage_alify_lead_posts_custom_column', 'alify_lead_render_custom_column', 10, 2 );

/**
 * 3. Filter Dropdowns (Lead Type + Pipeline Status) & 1-Click CSV Export Button on Leads Table.
 */
function alify_leads_filter_dropdowns( $post_type ) {
	if ( 'alify_lead' !== $post_type ) {
		return;
	}
	$cur_type   = isset( $_GET['alify_lead_type'] ) ? sanitize_key( wp_unslash( $_GET['alify_lead_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cur_status = isset( $_GET['alify_lead_status'] ) ? sanitize_key( wp_unslash( $_GET['alify_lead_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<select name="alify_lead_type">';
	echo '<option value="">' . esc_html__( 'All Lead Types', 'alify' ) . '</option>';
	foreach ( alify_get_lead_types() as $k => $lbl ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur_type, $k, false ) . '>' . esc_html( $lbl ) . '</option>';
	}
	echo '</select>';

	echo '<select name="alify_lead_status">';
	echo '<option value="">' . esc_html__( 'All Statuses', 'alify' ) . '</option>';
	foreach ( alify_get_lead_statuses() as $k => $lbl ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur_status, $k, false ) . '>' . esc_html( $lbl ) . '</option>';
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'alify_leads_filter_dropdowns' );

function alify_leads_apply_admin_filters( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'alify_lead' !== $query->get( 'post_type' ) ) {
		return;
	}

	$meta_query = array();
	if ( ! empty( $_GET['alify_lead_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$meta_query[] = array(
			'key'   => '_alify_lead_type',
			'value' => sanitize_key( wp_unslash( $_GET['alify_lead_type'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}
	if ( ! empty( $_GET['alify_lead_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$meta_query[] = array(
			'key'   => '_alify_lead_status',
			'value' => sanitize_key( wp_unslash( $_GET['alify_lead_status'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
	}
	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'alify_leads_apply_admin_filters' );

/**
 * Render 1-Click "Export Leads CSV" Button in Top Table Bar.
 */
function alify_leads_extra_tablenav( $which ) {
	global $typenow;
	if ( 'top' !== $which || 'alify_lead' !== $typenow || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$export_args = array(
		'alify_export_leads_csv' => '1',
		'alify_lead_type'        => isset( $_GET['alify_lead_type'] ) ? sanitize_key( wp_unslash( $_GET['alify_lead_type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'alify_lead_status'      => isset( $_GET['alify_lead_status'] ) ? sanitize_key( wp_unslash( $_GET['alify_lead_status'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
	$export_url  = wp_nonce_url( add_query_arg( $export_args, admin_url( 'edit.php?post_type=alify_lead' ) ), 'alify_export_leads_csv_action' );

	echo '<div class="alignleft actions"><a href="' . esc_url( $export_url ) . '" class="button button-primary">' . esc_html__( 'Export Leads CSV', 'alify' ) . '</a></div>';
}
add_action( 'manage_posts_extra_tablenav', 'alify_leads_extra_tablenav' );

/**
 * Handle Secure UTF-8 CSV Export Download.
 */
function alify_handle_leads_csv_export() {
	if ( ! is_admin() || ! isset( $_GET['alify_export_leads_csv'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'alify_export_leads_csv_action' );

	$meta_query = array();
	if ( ! empty( $_GET['alify_lead_type'] ) ) {
		$meta_query[] = array(
			'key'   => '_alify_lead_type',
			'value' => sanitize_key( wp_unslash( $_GET['alify_lead_type'] ) ),
		);
	}
	if ( ! empty( $_GET['alify_lead_status'] ) ) {
		$meta_query[] = array(
			'key'   => '_alify_lead_status',
			'value' => sanitize_key( wp_unslash( $_GET['alify_lead_status'] ) ),
		);
	}

	$args = array(
		'post_type'      => 'alify_lead',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( ! empty( $meta_query ) ) {
		$args['meta_query'] = $meta_query;
	}

	$leads    = get_posts( $args );
	$filename = 'alify-leads-' . gmdate( 'Y-m-d' ) . '.csv';

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$out = fopen( 'php://output', 'w' );
	if ( false === $out ) {
		exit;
	}

	// UTF-8 BOM for clean Excel opening
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv(
		$out,
		array( 'Ref ID', 'Submitted Date', 'Lead Type', 'Status', 'Client Name', 'Email', 'Phone / WhatsApp', 'Company', 'Budget', 'Capability / Track', 'Booked Slot', 'Source Page', 'Brief / Notes', 'Internal Team Notes' )
	);

	foreach ( $leads as $lead ) {
		$pid = $lead->ID;
		fputcsv(
			$out,
			array(
				get_post_meta( $pid, '_alify_lead_ref', true ),
				get_the_date( 'Y-m-d H:i:s', $pid ),
				get_post_meta( $pid, '_alify_lead_type', true ),
				get_post_meta( $pid, '_alify_lead_status', true ),
				get_post_meta( $pid, '_alify_lead_name', true ),
				get_post_meta( $pid, '_alify_lead_email', true ),
				get_post_meta( $pid, '_alify_lead_phone', true ),
				get_post_meta( $pid, '_alify_lead_company', true ),
				get_post_meta( $pid, '_alify_lead_budget', true ),
				get_post_meta( $pid, '_alify_lead_capability', true ),
				get_post_meta( $pid, '_alify_lead_slot', true ),
				get_post_meta( $pid, '_alify_lead_source_url', true ),
				get_post_meta( $pid, '_alify_lead_message', true ),
				get_post_meta( $pid, '_alify_lead_admin_notes', true ),
			)
		);
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_init', 'alify_handle_leads_csv_export' );

/**
 * 4. Structured Lead CRM Summary & Pipeline Status Meta Box on Single `alify_lead` Screen.
 */
function alify_register_lead_detail_meta_box() {
	add_meta_box(
		'alify_lead_crm_box',
		__( 'ALIFY Lead CRM Details, Quick Actions & Pipeline Status', 'alify' ),
		'alify_render_lead_detail_meta_box',
		'alify_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'alify_register_lead_detail_meta_box' );

function alify_render_lead_detail_meta_box( $post ) {
	wp_nonce_field( 'alify_save_lead_crm_meta', 'alify_lead_crm_nonce' );

	$pid     = $post->ID;
	$status  = get_post_meta( $pid, '_alify_lead_status', true );
	$status  = $status ? $status : 'new';
	$type    = get_post_meta( $pid, '_alify_lead_type', true );
	$email   = get_post_meta( $pid, '_alify_lead_email', true );
	$phone   = get_post_meta( $pid, '_alify_lead_phone', true );
	$notes   = get_post_meta( $pid, '_alify_lead_admin_notes', true );
	$digits  = preg_replace( '/[^0-9]/', '', (string) $phone );

	$details = array(
		'Reference ID'       => get_post_meta( $pid, '_alify_lead_ref', true ),
		'Lead Type'          => $type,
		'Client Name'        => get_post_meta( $pid, '_alify_lead_name', true ),
		'Email Address'      => $email,
		'Phone / WhatsApp'   => $phone,
		'Company'            => get_post_meta( $pid, '_alify_lead_company', true ),
		'Estimated Budget'   => get_post_meta( $pid, '_alify_lead_budget', true ),
		'Capability / Track' => get_post_meta( $pid, '_alify_lead_capability', true ),
		'Reserved Slot'      => get_post_meta( $pid, '_alify_lead_slot', true ),
		'Submitted From'     => get_post_meta( $pid, '_alify_lead_source_url', true ),
	);
	?>
	<div style="display:grid;gap:16px;padding:8px 0;">
		<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:8px;">
			<div>
				<label for="_alify_lead_status" style="font-weight:700;margin-right:8px;"><?php esc_html_e( 'Pipeline Status:', 'alify' ); ?></label>
				<select id="_alify_lead_status" name="_alify_lead_status">
					<?php foreach ( alify_get_lead_statuses() as $k => $lbl ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div style="display:flex;gap:8px;">
				<?php if ( ! empty( $email ) ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="button button-primary"><?php esc_html_e( 'Reply via Email', 'alify' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $digits ) ) : ?>
					<a href="<?php echo esc_url( 'https://wa.me/' . $digits ); ?>" target="_blank" rel="noopener noreferrer" class="button"><?php esc_html_e( 'Chat on WhatsApp', 'alify' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<table class="widefat striped">
			<tbody>
				<?php foreach ( $details as $label => $val ) : ?>
					<?php if ( '' !== (string) $val ) : ?>
						<tr>
							<th style="width:200px;font-weight:600;"><?php echo esc_html( $label ); ?></th>
							<td><?php echo esc_html( (string) $val ); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<label for="_alify_lead_admin_notes" style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e( 'Internal Studio Team Notes (Private)', 'alify' ); ?></label>
			<textarea id="_alify_lead_admin_notes" name="_alify_lead_admin_notes" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Add call summary, proposal link, or follow-up date...', 'alify' ); ?>"><?php echo esc_textarea( $notes ); ?></textarea>
		</div>
	</div>
	<?php
}

function alify_save_lead_crm_meta( $post_id, $post ) {
	if ( 'alify_lead' !== $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['alify_lead_crm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_lead_crm_nonce'] ) ), 'alify_save_lead_crm_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_alify_lead_status'] ) ) {
		update_post_meta( $post_id, '_alify_lead_status', sanitize_key( wp_unslash( $_POST['_alify_lead_status'] ) ) );
	}
	if ( isset( $_POST['_alify_lead_admin_notes'] ) ) {
		update_post_meta( $post_id, '_alify_lead_admin_notes', sanitize_textarea_field( wp_unslash( $_POST['_alify_lead_admin_notes'] ) ) );
	}
}
add_action( 'save_post', 'alify_save_lead_crm_meta', 10, 2 );
