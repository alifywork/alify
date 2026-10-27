<?php
/**
 * WordPress AJAX Form, Strategy Call Booking & Newsletter Handlers
 * Stores structured leads in `alify_lead` CPT and emails Studio Email (`info@alify.site`)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_ajax_submit_lead() {
	check_ajax_referer( 'alify_ajax_nonce', 'nonce' );

	// Silent honeypot protection against bots
	if ( ! empty( $_POST['alify_hp'] ) ) {
		wp_send_json_success( array( 'status' => 'ok' ) );
	}

	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$company    = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$budget     = isset( $_POST['budget'] ) ? sanitize_text_field( wp_unslash( $_POST['budget'] ) ) : '';
	$capability = isset( $_POST['capability'] ) ? sanitize_text_field( wp_unslash( $_POST['capability'] ) ) : 'Custom Digital Strategy & Build';
	$slot       = isset( $_POST['bookingSlot'] ) ? sanitize_text_field( wp_unslash( $_POST['bookingSlot'] ) ) : '';
	$lead_type  = isset( $_POST['leadType'] ) ? sanitize_key( wp_unslash( $_POST['leadType'] ) ) : '';
	$source_url = isset( $_POST['sourceUrl'] ) ? sanitize_text_field( wp_unslash( $_POST['sourceUrl'] ) ) : '';
	$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$ref        = isset( $_POST['ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ref'] ) ) : 'ALF-' . wp_rand( 1000, 9999 );

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'alify' ) ), 400 );
	}

	if ( empty( $lead_type ) ) {
		if ( ! empty( $slot ) ) {
			$lead_type = 'strategy_call';
		} elseif ( ! empty( $budget ) || false !== stripos( $source_url, 'start-project' ) ) {
			$lead_type = 'project_brief';
		} else {
			$lead_type = 'inquiry';
		}
	}

	// Deduplicate newsletter signups by email
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
		update_post_meta( $lead_id, '_alify_lead_status', 'new' );
		update_post_meta( $lead_id, '_alify_lead_name', $name );
		update_post_meta( $lead_id, '_alify_lead_email', $email );
		update_post_meta( $lead_id, '_alify_lead_phone', $phone );
		update_post_meta( $lead_id, '_alify_lead_company', $company );
		update_post_meta( $lead_id, '_alify_lead_budget', $budget );
		update_post_meta( $lead_id, '_alify_lead_capability', $capability );
		update_post_meta( $lead_id, '_alify_lead_slot', $slot );
		update_post_meta( $lead_id, '_alify_lead_source_url', $source_url );
		update_post_meta( $lead_id, '_alify_lead_message', $message );
	}

	if ( 'newsletter' !== $lead_type ) {
		$to      = get_theme_mod( 'alify_email', 'info@alify.site' );
		$subject = sprintf( 'New ALIFY %s %s — %s', $title_prefix, $ref, $capability );
		$headers = array( 'Reply-To: ' . ( $name ? $name . ' <' . $email . '>' : $email ) );
		wp_mail( $to, $subject, implode( "\n", $body_lines ), $headers );
	}

	wp_send_json_success(
		array(
			'ref'         => $ref,
			'redirectUrl' => alify_page_url( 'thank-you' ),
		)
	);
}
add_action( 'wp_ajax_alify_submit_lead', 'alify_ajax_submit_lead' );
add_action( 'wp_ajax_nopriv_alify_submit_lead', 'alify_ajax_submit_lead' );
