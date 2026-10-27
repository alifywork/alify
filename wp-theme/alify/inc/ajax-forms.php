<?php
/**
 * WordPress AJAX Form & Strategy Call Booking Handlers
 * Stores submissions in `alify_lead` CPT and sends email to Studio Email (`info@alify.site`)
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function alify_ajax_submit_lead() {
	check_ajax_referer( 'alify_ajax_nonce', 'nonce' );

	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$capability = isset( $_POST['capability'] ) ? sanitize_text_field( wp_unslash( $_POST['capability'] ) ) : 'Custom Digital Strategy & Build';
	$slot       = isset( $_POST['bookingSlot'] ) ? sanitize_text_field( wp_unslash( $_POST['bookingSlot'] ) ) : '';
	$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$ref        = isset( $_POST['ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ref'] ) ) : 'ALF-' . wp_rand( 1000, 9999 );

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'alify' ) ), 400 );
	}

	$title_prefix = ! empty( $slot ) ? '[Strategy Call]' : '[Inquiry]';
	$post_title   = sprintf( '%s %s (%s) — %s', $title_prefix, $name ? $name : $email, $capability, $ref );

	$body_lines = array(
		'Reference: ' . $ref,
		'Name: ' . $name,
		'Email: ' . $email,
		'Phone / WhatsApp: ' . $phone,
		'Capability / Track: ' . $capability,
	);
	if ( ! empty( $slot ) ) {
		$body_lines[] = 'Reserved Slot: ' . $slot;
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
		update_post_meta( $lead_id, '_alify_lead_email', $email );
		update_post_meta( $lead_id, '_alify_lead_name', $name );
		update_post_meta( $lead_id, '_alify_lead_capability', $capability );
		update_post_meta( $lead_id, '_alify_lead_slot', $slot );
		update_post_meta( $lead_id, '_alify_lead_ref', $ref );
	}

	$to      = get_theme_mod( 'alify_email', 'info@alify.site' );
	$subject = sprintf( 'New ALIFY Lead %s — %s', $ref, $capability );
	$headers = array( 'Reply-To: ' . ( $name ? $name . ' <' . $email . '>' : $email ) );
	wp_mail( $to, $subject, implode( "\n", $body_lines ), $headers );

	wp_send_json_success(
		array(
			'ref'         => $ref,
			'redirectUrl' => alify_page_url( 'thank-you' ),
		)
	);
}
add_action( 'wp_ajax_alify_submit_lead', 'alify_ajax_submit_lead' );
add_action( 'wp_ajax_nopriv_alify_submit_lead', 'alify_ajax_submit_lead' );
