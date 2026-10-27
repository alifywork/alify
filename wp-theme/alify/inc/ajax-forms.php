<?php
/**
 * WordPress AJAX Form, Strategy Call Booking & Newsletter Handlers
 * Powered by `ALIFY Forms Settings` & `ALIFY Form Templates` (`inc/forms-manager.php`):
 *   - Stores structured leads + form template ID in `alify_lead` CPT
 *   - Enforces Honeypot, IP Rate Limiting & Spam Domain/Keyword Blocking
 *   - Sends Admin Notification Emails (with To/CC/BCC/From + per-template recipient override)
 *   - Sends Optional Client Auto-Responder Confirmation Email
 *   - Fires Optional Outbound JSON Webhook (Zapier / Make / Slack / CRM)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_replace_mail_tokens( $template_str, $tokens ) {
	return strtr( (string) $template_str, $tokens );
}

function alify_ajax_submit_lead() {
	check_ajax_referer( 'alify_ajax_nonce', 'nonce' );

	$settings = function_exists( 'alify_get_forms_settings' ) ? alify_get_forms_settings() : array();

	// 1. Silent Honeypot Protection
	if ( ( ! isset( $settings['enable_honeypot'] ) || '1' === (string) $settings['enable_honeypot'] ) && ! empty( $_POST['alify_hp'] ) ) {
		wp_send_json_success( array( 'status' => 'ok' ) );
	}

	// 2. Per-IP Rate Limit Check (10-minute rolling window)
	$rate_max = isset( $settings['rate_limit_per_10m'] ) ? absint( $settings['rate_limit_per_10m'] ) : 15;
	if ( $rate_max > 0 ) {
		$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
		$rate_key = 'alify_rl_' . md5( $ip );
		$hits     = (int) get_transient( $rate_key );
		if ( $hits >= $rate_max ) {
			wp_send_json_error( array( 'message' => __( 'Too many submissions in a short period. Please try again in a few minutes.', 'alify' ) ), 429 );
		}
		set_transient( $rate_key, $hits + 1, 10 * MINUTE_IN_SECONDS );
	}

	$form_tpl_id = isset( $_POST['formTemplate'] ) ? sanitize_key( wp_unslash( $_POST['formTemplate'] ) ) : '';
	$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$company     = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$budget      = isset( $_POST['budget'] ) ? sanitize_text_field( wp_unslash( $_POST['budget'] ) ) : '';
	$timeline    = isset( $_POST['timeline'] ) ? sanitize_text_field( wp_unslash( $_POST['timeline'] ) ) : '';
	$subject_in  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$channel_in  = isset( $_POST['channel'] ) ? sanitize_text_field( wp_unslash( $_POST['channel'] ) ) : '';
	$capability  = isset( $_POST['capability'] ) ? sanitize_text_field( wp_unslash( $_POST['capability'] ) ) : 'Custom Digital Strategy & Build';
	$slot        = isset( $_POST['bookingSlot'] ) ? sanitize_text_field( wp_unslash( $_POST['bookingSlot'] ) ) : '';
	$lead_type   = isset( $_POST['leadType'] ) ? sanitize_key( wp_unslash( $_POST['leadType'] ) ) : '';
	$source_url  = isset( $_POST['sourceUrl'] ) ? sanitize_text_field( wp_unslash( $_POST['sourceUrl'] ) ) : '';
	$message     = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'alify' ) ), 400 );
	}

	// 3. Blocked Domains / Spam Keywords Filter
	if ( ! empty( $settings['blocked_terms'] ) ) {
		$raw_terms = preg_split( '/[\r\n,]+/', (string) $settings['blocked_terms'] );
		$haystack  = strtolower( $email . ' ' . $name . ' ' . $company . ' ' . $message );
		foreach ( $raw_terms as $term ) {
			$term = strtolower( trim( $term ) );
			if ( '' !== $term && false !== strpos( $haystack, $term ) ) {
				wp_send_json_success( array( 'status' => 'ok' ) );
			}
		}
	}

	$tpl_cfg = ( $form_tpl_id && function_exists( 'alify_get_form_template' ) ) ? alify_get_form_template( $form_tpl_id ) : null;

	if ( empty( $lead_type ) ) {
		if ( $tpl_cfg && ! empty( $tpl_cfg['lead_type'] ) ) {
			$lead_type = $tpl_cfg['lead_type'];
		} elseif ( ! empty( $slot ) ) {
			$lead_type = 'strategy_call';
		} elseif ( ! empty( $budget ) || false !== stripos( $source_url, 'start-project' ) ) {
			$lead_type = 'project_brief';
		} else {
			$lead_type = 'inquiry';
		}
	}

	$ref_prefix = ( 'strategy_call' === $lead_type )
		? ( ! empty( $settings['call_ref_prefix'] ) ? $settings['call_ref_prefix'] : 'CALL-' )
		: ( ! empty( $settings['lead_ref_prefix'] ) ? $settings['lead_ref_prefix'] : 'ALF-' );
	$ref        = isset( $_POST['ref'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_POST['ref'] ) ) )
		? sanitize_text_field( wp_unslash( $_POST['ref'] ) )
		: $ref_prefix . wp_rand( 1000, 9999 );

	// 4. Deduplicate Newsletter Signups by Email
	if ( 'newsletter' === $lead_type ) {
		$existing_nl = get_posts(
			array(
				'post_type'      => 'alify_lead',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'   => '_alify_lead_email',
						'value' => $email,
					),
					array(
						'key'   => '_alify_lead_type',
						'value' => 'newsletter',
					),
				),
			)
		);
		if ( ! empty( $existing_nl ) ) {
			wp_send_json_success( array( 'ref' => $ref, 'duplicate' => true ) );
		}
	}

	$type_labels = array(
		'strategy_call' => '[Strategy Call]',
		'project_brief' => '[Project Brief]',
		'newsletter'    => '[Newsletter]',
		'inquiry'       => '[Inquiry]',
	);
	$title_prefix = isset( $type_labels[ $lead_type ] ) ? $type_labels[ $lead_type ] : '[Inquiry]';
	$post_title   = sprintf( '%s %s (%s) — %s', $title_prefix, $name ? $name : $email, $capability, $ref );

	$body_lines = array(
		'Reference: ' . $ref,
		'Type: ' . $lead_type,
		'Form Template: ' . ( $tpl_cfg ? $tpl_cfg['label'] : ( $form_tpl_id ? $form_tpl_id : 'default' ) ),
		'Name: ' . $name,
		'Email: ' . $email,
	);
	if ( ! empty( $phone ) ) {
		$body_lines[] = 'Phone / WhatsApp: ' . $phone;
	}
	if ( ! empty( $company ) ) {
		$body_lines[] = 'Company: ' . $company;
	}
	if ( ! empty( $budget ) ) {
		$body_lines[] = 'Estimated Budget: ' . $budget;
	}
	if ( ! empty( $timeline ) ) {
		$body_lines[] = 'Target Timeline: ' . $timeline;
	}
	if ( ! empty( $subject_in ) ) {
		$body_lines[] = 'Subject: ' . $subject_in;
	}
	if ( ! empty( $channel_in ) ) {
		$body_lines[] = 'Preferred Channel: ' . $channel_in;
	}
	$body_lines[] = 'Capability / Track: ' . $capability;
	if ( ! empty( $slot ) ) {
		$body_lines[] = 'Reserved Slot: ' . $slot;
	}
	if ( ! empty( $source_url ) ) {
		$body_lines[] = 'Submitted From: ' . $source_url;
	}
	if ( ! empty( $message ) ) {
		$body_lines[] = "\nNotes / Brief:\n" . $message;
	}

	$lead_id = wp_insert_post(
		array(
			'post_type'    => 'alify_lead',
			'post_status'  => 'publish',
			'post_title'   => $post_title,
			'post_content' => implode( "\n", $body_lines ),
		)
	);

	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_alify_lead_ref', $ref );
		update_post_meta( $lead_id, '_alify_lead_type', $lead_type );
		update_post_meta( $lead_id, '_alify_lead_template', $form_tpl_id ? $form_tpl_id : $lead_type );
		update_post_meta( $lead_id, '_alify_lead_status', 'new' );
		update_post_meta( $lead_id, '_alify_lead_name', $name );
		update_post_meta( $lead_id, '_alify_lead_email', $email );
		update_post_meta( $lead_id, '_alify_lead_phone', $phone );
		update_post_meta( $lead_id, '_alify_lead_company', $company );
		update_post_meta( $lead_id, '_alify_lead_budget', $budget );
		update_post_meta( $lead_id, '_alify_lead_timeline', $timeline );
		update_post_meta( $lead_id, '_alify_lead_subject', $subject_in );
		update_post_meta( $lead_id, '_alify_lead_channel', $channel_in );
		update_post_meta( $lead_id, '_alify_lead_capability', $capability );
		update_post_meta( $lead_id, '_alify_lead_slot', $slot );
		update_post_meta( $lead_id, '_alify_lead_source_url', $source_url );
		update_post_meta( $lead_id, '_alify_lead_message', $message );
	}

	$studio_email = get_theme_mod( 'alify_email', 'info@alify.site' );
	$whatsapp_num = get_theme_mod( 'alify_whatsapp', '+90 301 9328094' );
	$tokens       = array(
		'{ref}'          => $ref,
		'{type_label}'   => $title_prefix,
		'{name}'         => $name ? $name : 'there',
		'{email}'        => $email,
		'{capability}'   => $capability,
		'{budget}'       => $budget ? $budget : 'TBD',
		'{slot}'         => $slot ? $slot : 'N/A',
		'{studio_email}' => $studio_email,
		'{whatsapp}'     => $whatsapp_num,
	);

	// 5. Studio Admin Notification Email
	if ( 'newsletter' !== $lead_type && ( ! isset( $settings['enable_admin_email'] ) || '1' === (string) $settings['enable_admin_email'] ) ) {
		$raw_to = ( $tpl_cfg && ! empty( $tpl_cfg['recipient_override'] ) )
			? $tpl_cfg['recipient_override']
			: ( ! empty( $settings['recipient_emails'] ) ? $settings['recipient_emails'] : $studio_email );

		$to_list = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $raw_to ) ) ) );
		if ( empty( $to_list ) ) {
			$to_list = array( $studio_email );
		}

		$subj_tpl = ! empty( $settings['email_subject'] ) ? $settings['email_subject'] : 'New ALIFY {type_label} {ref} — {capability}';
		$subject  = alify_replace_mail_tokens( $subj_tpl, $tokens );

		$headers = array();
		if ( ! empty( $settings['from_email'] ) && is_email( $settings['from_email'] ) ) {
			$from_name = ! empty( $settings['from_name'] ) ? $settings['from_name'] : 'ALIFY Studio';
			$headers[] = 'From: ' . $from_name . ' <' . $settings['from_email'] . '>';
		}
		$headers[] = 'Reply-To: ' . ( $name ? $name . ' <' . $email . '>' : $email );

		if ( ! empty( $settings['cc_emails'] ) ) {
			foreach ( array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $settings['cc_emails'] ) ) ) ) as $cc ) {
				$headers[] = 'Cc: ' . $cc;
			}
		}
		if ( ! empty( $settings['bcc_emails'] ) ) {
			foreach ( array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $settings['bcc_emails'] ) ) ) ) as $bcc ) {
				$headers[] = 'Bcc: ' . $bcc;
			}
		}

		wp_mail( $to_list, $subject, implode( "\n", $body_lines ), $headers );
	}

	// 6. Client Auto-Responder Confirmation Email
	if ( 'newsletter' !== $lead_type && isset( $settings['enable_autoresponder'] ) && '1' === (string) $settings['enable_autoresponder'] ) {
		$ar_subj = ! empty( $settings['autoresponder_subject'] ) ? alify_replace_mail_tokens( $settings['autoresponder_subject'], $tokens ) : 'We received your request (' . $ref . ') — ALIFY Studio';
		$ar_body = ! empty( $settings['autoresponder_body'] ) ? alify_replace_mail_tokens( $settings['autoresponder_body'], $tokens ) : '';
		if ( ! empty( $ar_body ) ) {
			$ar_headers = array( 'Reply-To: ALIFY Studio <' . $studio_email . '>' );
			if ( ! empty( $settings['from_email'] ) && is_email( $settings['from_email'] ) ) {
				$from_name    = ! empty( $settings['from_name'] ) ? $settings['from_name'] : 'ALIFY Studio';
				$ar_headers[] = 'From: ' . $from_name . ' <' . $settings['from_email'] . '>';
			}
			wp_mail( $email, $ar_subj, $ar_body, $ar_headers );
		}
	}

	// 7. Optional Outbound Webhook (Zapier / Make / Slack / CRM)
	if ( isset( $settings['enable_webhook'] ) && '1' === (string) $settings['enable_webhook'] && ! empty( $settings['webhook_url'] ) ) {
		wp_remote_post(
			esc_url_raw( $settings['webhook_url'] ),
			array(
				'timeout'  => 4,
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'     => wp_json_encode(
					array(
						'ref'          => $ref,
						'lead_id'      => $lead_id,
						'lead_type'    => $lead_type,
						'form_template'=> $form_tpl_id,
						'name'         => $name,
						'email'        => $email,
						'phone'        => $phone,
						'company'      => $company,
						'capability'   => $capability,
						'budget'       => $budget,
						'timeline'     => $timeline,
						'booking_slot' => $slot,
						'source_url'   => $source_url,
						'message'      => $message,
						'submitted_at' => gmdate( 'c' ),
					)
				),
			)
		);
	}

	$redirect_url = ( $tpl_cfg && ! empty( $tpl_cfg['redirect_url'] ) )
		? $tpl_cfg['redirect_url']
		: ( function_exists( 'alify_get_active_thank_you_url' ) ? alify_get_active_thank_you_url() : alify_page_url( 'thank-you' ) );

	wp_send_json_success(
		array(
			'ref'                => $ref,
			'submissionBehavior' => isset( $settings['submission_behavior'] ) ? $settings['submission_behavior'] : 'redirect',
			'redirectUrl'        => $redirect_url,
		)
	);
}
add_action( 'wp_ajax_alify_submit_lead', 'alify_ajax_submit_lead' );
add_action( 'wp_ajax_nopriv_alify_submit_lead', 'alify_ajax_submit_lead' );
