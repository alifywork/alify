<?php
/**
 * Built-In Lightweight SMTP Mailer & Live Email Diagnostics (`ALIFY Leads -> SMTP Mailer`)
 * Ensures reliable delivery of Lead Alerts, Strategy Call Confirmations & Client Auto-Responders
 * on shared/cloud hosts without requiring bloated third-party SMTP plugins.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encrypt / Decrypt SMTP Password Using WordPress `wp_salt( 'auth' )`.
 */
function alify_smtp_encrypt_secret( $plain ) {
	$plain = (string) $plain;
	if ( '' === $plain ) {
		return '';
	}
	if ( function_exists( 'openssl_encrypt' ) ) {
		$key = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );
		$enc = openssl_encrypt( $plain, 'AES-256-CBC', $key, 0, $iv );
		if ( false !== $enc ) {
			return 'enc1:' . $enc;
		}
	}
	return 'b64:' . base64_encode( $plain ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

function alify_smtp_decrypt_secret( $stored ) {
	$stored = (string) $stored;
	if ( '' === $stored ) {
		return '';
	}
	if ( 0 === strpos( $stored, 'enc1:' ) && function_exists( 'openssl_decrypt' ) ) {
		$key = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );
		$dec = openssl_decrypt( substr( $stored, 5 ), 'AES-256-CBC', $key, 0, $iv );
		return false !== $dec ? $dec : '';
	}
	if ( 0 === strpos( $stored, 'b64:' ) ) {
		$dec = base64_decode( substr( $stored, 4 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		return false !== $dec ? $dec : '';
	}
	return $stored;
}

/**
 * Retrieve Sanitized SMTP Settings With Defaults.
 */
function alify_get_smtp_settings() {
	$defaults = array(
		'enabled'     => '0',
		'from_email'  => 'info@alify.site',
		'from_name'   => 'ALIFY Studio',
		'force_from'  => '1',
		'host'        => '',
		'port'        => 587,
		'encryption'  => 'tls',
		'auth'        => '1',
		'username'    => '',
		'password'    => '',
	);
	$saved = get_option( 'alify_smtp_settings', array() );
	return is_array( $saved ) ? array_merge( $defaults, $saved ) : $defaults;
}

/**
 * Configure WordPress `PHPMailer` Instance When Built-In SMTP Is Enabled.
 */
function alify_configure_phpmailer_smtp( $phpmailer ) {
	$cfg = alify_get_smtp_settings();
	if ( '1' !== (string) $cfg['enabled'] || empty( $cfg['host'] ) ) {
		return;
	}

	// Respect external SMTP plugins only if host wasn't explicitly configured here
	$phpmailer->isSMTP();
	$phpmailer->Host       = (string) $cfg['host'];
	$phpmailer->Port       = max( 1, (int) $cfg['port'] );
	$phpmailer->SMTPAuth   = ( '1' === (string) $cfg['auth'] );
	$phpmailer->SMTPSecure = in_array( $cfg['encryption'], array( 'tls', 'ssl', '' ), true ) ? $cfg['encryption'] : 'tls';
	$phpmailer->SMTPAutoTLS = ( 'tls' === $phpmailer->SMTPSecure );

	if ( $phpmailer->SMTPAuth ) {
		$phpmailer->Username = (string) $cfg['username'];
		$phpmailer->Password = alify_smtp_decrypt_secret( $cfg['password'] );
	}

	if ( ! empty( $cfg['from_email'] ) && is_email( $cfg['from_email'] ) && '1' === (string) $cfg['force_from'] ) {
		try {
			$phpmailer->setFrom( $cfg['from_email'], ! empty( $cfg['from_name'] ) ? $cfg['from_name'] : 'ALIFY', false );
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Keep default sender if PHPMailer rejects header
		}
	}
}
add_action( 'phpmailer_init', 'alify_configure_phpmailer_smtp', 20 );

/**
 * Capture Last `wp_mail` Failure for Instant Admin Diagnostics.
 */
function alify_capture_wp_mail_error( $wp_error ) {
	if ( is_wp_error( $wp_error ) ) {
		update_option(
			'alify_smtp_last_error',
			array(
				'time'    => current_time( 'mysql' ),
				'message' => $wp_error->get_error_message(),
			),
			false
		);
	}
}
add_action( 'wp_mail_failed', 'alify_capture_wp_mail_error' );

/**
 * Register Submenu: `ALIFY Leads -> SMTP Mailer`
 */
function alify_register_smtp_mailer_submenu() {
	add_submenu_page(
		'edit.php?post_type=alify_lead',
		__( 'ALIFY SMTP Mailer & Email Diagnostics', 'alify' ),
		__( 'SMTP Mailer', 'alify' ),
		'manage_options',
		'alify-smtp-mailer',
		'alify_render_smtp_mailer_page'
	);
}
add_action( 'admin_menu', 'alify_register_smtp_mailer_submenu', 30 );

/**
 * Handle Settings Save & 1-Click Live Test Email Dispatch.
 */
function alify_handle_smtp_admin_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['alify_save_smtp_settings'] ) && check_admin_referer( 'alify_smtp_settings_nonce' ) ) {
		$existing = alify_get_smtp_settings();
		$raw_pass = isset( $_POST['smtp_password'] ) ? trim( (string) wp_unslash( $_POST['smtp_password'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$enc_pass = $existing['password'];
		if ( '1' === ( $_POST['smtp_clear_password'] ?? '0' ) ) {
			$enc_pass = '';
		} elseif ( '' !== $raw_pass && '••••••••••••' !== $raw_pass ) {
			$enc_pass = alify_smtp_encrypt_secret( $raw_pass );
		}

		$enc_mode = isset( $_POST['smtp_encryption'] ) ? sanitize_key( wp_unslash( $_POST['smtp_encryption'] ) ) : 'tls';
		if ( 'none' === $enc_mode ) {
			$enc_mode = '';
		}

		$updated = array(
			'enabled'    => isset( $_POST['smtp_enabled'] ) ? '1' : '0',
			'from_email' => isset( $_POST['smtp_from_email'] ) ? sanitize_email( wp_unslash( $_POST['smtp_from_email'] ) ) : 'info@alify.site',
			'from_name'  => isset( $_POST['smtp_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_from_name'] ) ) : 'ALIFY Studio',
			'force_from' => isset( $_POST['smtp_force_from'] ) ? '1' : '0',
			'host'       => isset( $_POST['smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_host'] ) ) : '',
			'port'       => isset( $_POST['smtp_port'] ) ? max( 1, absint( $_POST['smtp_port'] ) ) : 587,
			'encryption' => in_array( $enc_mode, array( 'tls', 'ssl', '' ), true ) ? $enc_mode : 'tls',
			'auth'       => isset( $_POST['smtp_auth'] ) ? '1' : '0',
			'username'   => isset( $_POST['smtp_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_username'] ) ) : '',
			'password'   => $enc_pass,
		);

		update_option( 'alify_smtp_settings', $updated );
		wp_safe_redirect( admin_url( 'edit.php?post_type=alify_lead&page=alify-smtp-mailer&saved=1' ) );
		exit;
	}

	if ( isset( $_POST['alify_send_smtp_test'] ) && check_admin_referer( 'alify_smtp_test_nonce' ) ) {
		delete_option( 'alify_smtp_last_error' );
		$to = isset( $_POST['test_recipient'] ) ? sanitize_email( wp_unslash( $_POST['test_recipient'] ) ) : '';
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		$subject = sprintf( '[ALIFY SMTP Verified] Test Delivery from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$body    = '<div style="font-family:Poppins,Arial,sans-serif;max-width:580px;margin:0 auto;padding:28px;background:#12151C;color:#F6F4F1;border-radius:14px;">'
			. '<p style="margin:0 0 8px;color:#E05A3A;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;font-weight:600;">ALIFY Mailer Diagnostic</p>'
			. '<h2 style="margin:0 0 12px;font-size:22px;font-weight:600;color:#FFFFFF;">Outbound Email Delivery Is Live</h2>'
			. '<p style="margin:0 0 16px;color:#C8CDD8;line-height:1.65;">Your WordPress installation successfully dispatched this test message. All ALIFY Lead Notifications, Strategy Call Confirmations, and Auto-Responder emails are ready for production.</p>'
			. '<p style="margin:0;font-size:12px;color:#8D95A5;">Timestamp: ' . esc_html( current_time( 'mysql' ) ) . ' &middot; Host: ' . esc_html( home_url() ) . '</p>'
			. '</div>';

		$sent   = wp_mail( $to, $subject, $body, $headers );
		$status = $sent ? 'sent' : 'failed';
		wp_safe_redirect( admin_url( 'edit.php?post_type=alify_lead&page=alify-smtp-mailer&test=' . $status . '&to=' . rawurlencode( $to ) ) );
		exit;
	}
}
add_action( 'admin_init', 'alify_handle_smtp_admin_actions' );

/**
 * Render `ALIFY Leads -> SMTP Mailer` Admin Screen.
 */
function alify_render_smtp_mailer_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$cfg        = alify_get_smtp_settings();
	$last_err   = get_option( 'alify_smtp_last_error', array() );
	$has_secret = ! empty( $cfg['password'] );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ALIFY Built-In SMTP Mailer & Live Delivery Diagnostics', 'alify' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Route all ALIFY Lead Alerts, Strategy Call Bookings, and Client Auto-Responders through authenticated SMTP — zero extra plugins required.', 'alify' ); ?></p>

		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'SMTP configuration saved.', 'alify' ); ?></strong></p></div>
		<?php endif; ?>

		<?php if ( isset( $_GET['test'] ) && 'sent' === $_GET['test'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Test Email Sent Successfully!', 'alify' ); ?></strong> <?php echo esc_html( sprintf( __( 'Delivered to %s.', 'alify' ), sanitize_email( wp_unslash( $_GET['to'] ?? '' ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?></p>
			</div>
		<?php elseif ( isset( $_GET['test'] ) && 'failed' === $_GET['test'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-error is-dismissible">
				<p><strong><?php esc_html_e( 'Test Email Failed.', 'alify' ); ?></strong> <?php echo ! empty( $last_err['message'] ) ? esc_html( $last_err['message'] ) : esc_html__( 'Check your SMTP host, port, and app password below.', 'alify' ); ?></p>
			</div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(360px,1fr));gap:22px;max-width:1120px;margin-top:18px;">
			<div class="card" style="max-width:none;padding:20px 24px;margin:0;">
				<h2 style="margin-top:0;"><?php esc_html_e( '1. Authenticated SMTP Server Configuration', 'alify' ); ?></h2>

				<p style="margin-bottom:14px;">
					<strong><?php esc_html_e( '1-Click Provider Presets:', 'alify' ); ?></strong>
					<button type="button" class="button button-small" onclick="document.getElementById('smtp_host').value='smtp.hostinger.com';document.getElementById('smtp_port').value='465';document.getElementById('smtp_encryption').value='ssl';">Hostinger / Titan (465 SSL)</button>
					<button type="button" class="button button-small" onclick="document.getElementById('smtp_host').value='smtp.gmail.com';document.getElementById('smtp_port').value='587';document.getElementById('smtp_encryption').value='tls';">Google Workspace / Gmail (587 TLS)</button>
					<button type="button" class="button button-small" onclick="document.getElementById('smtp_host').value='smtp.zoho.com';document.getElementById('smtp_port').value='587';document.getElementById('smtp_encryption').value='tls';">Zoho Mail (587 TLS)</button>
					<button type="button" class="button button-small" onclick="document.getElementById('smtp_host').value='smtp-relay.brevo.com';document.getElementById('smtp_port').value='587';document.getElementById('smtp_encryption').value='tls';">Brevo / SES (587 TLS)</button>
				</p>

				<form method="post" action="">
					<?php wp_nonce_field( 'alify_smtp_settings_nonce' ); ?>
					<input type="hidden" name="alify_save_smtp_settings" value="1" />
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable Built-In SMTP', 'alify' ); ?></th>
							<td>
								<label><input type="checkbox" name="smtp_enabled" value="1" <?php checked( '1', (string) $cfg['enabled'] ); ?> /> <?php esc_html_e( 'Route wp_mail() through authenticated SMTP', 'alify' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_from_email"><?php esc_html_e( 'From Email', 'alify' ); ?></label></th>
							<td>
								<input type="email" id="smtp_from_email" name="smtp_from_email" class="regular-text" value="<?php echo esc_attr( $cfg['from_email'] ); ?>" />
								<p class="description"><label><input type="checkbox" name="smtp_force_from" value="1" <?php checked( '1', (string) $cfg['force_from'] ); ?> /> <?php esc_html_e( 'Force this From address across all WordPress emails', 'alify' ); ?></label></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_from_name"><?php esc_html_e( 'From Name', 'alify' ); ?></label></th>
							<td><input type="text" id="smtp_from_name" name="smtp_from_name" class="regular-text" value="<?php echo esc_attr( $cfg['from_name'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'alify' ); ?></label></th>
							<td><input type="text" id="smtp_host" name="smtp_host" class="regular-text" placeholder="smtp.hostinger.com" value="<?php echo esc_attr( $cfg['host'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_port"><?php esc_html_e( 'SMTP Port & Encryption', 'alify' ); ?></label></th>
							<td>
								<input type="number" id="smtp_port" name="smtp_port" style="width:95px;" value="<?php echo esc_attr( (string) $cfg['port'] ); ?>" />
								<select id="smtp_encryption" name="smtp_encryption">
									<option value="tls" <?php selected( 'tls', $cfg['encryption'] ); ?>>STARTTLS (Port 587)</option>
									<option value="ssl" <?php selected( 'ssl', $cfg['encryption'] ); ?>>SSL / SMTPS (Port 465)</option>
									<option value="none" <?php selected( '', $cfg['encryption'] ); ?>>None (Port 25 / Local)</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'SMTP Authentication', 'alify' ); ?></th>
							<td><label><input type="checkbox" name="smtp_auth" value="1" <?php checked( '1', (string) $cfg['auth'] ); ?> /> <?php esc_html_e( 'Use SMTP Username & Password', 'alify' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_username"><?php esc_html_e( 'SMTP Username', 'alify' ); ?></label></th>
							<td><input type="text" id="smtp_username" name="smtp_username" class="regular-text" autocomplete="off" placeholder="info@alify.site" value="<?php echo esc_attr( $cfg['username'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="smtp_password"><?php esc_html_e( 'SMTP Password / App Key', 'alify' ); ?></label></th>
							<td>
								<input type="password" id="smtp_password" name="smtp_password" class="regular-text" autocomplete="new-password" value="<?php echo $has_secret ? '••••••••••••' : ''; ?>" />
								<p class="description"><?php esc_html_e( 'Encrypted at rest with AES-256 + WordPress auth salt. Leave masked to keep existing password.', 'alify' ); ?></p>
							</td>
						</tr>
					</table>
					<p class="submit"><button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save SMTP Configuration', 'alify' ); ?></button></p>
				</form>
			</div>

			<div class="card" style="max-width:none;padding:20px 24px;margin:0;">
				<h2 style="margin-top:0;"><?php esc_html_e( '2. Live Email Test & Error Log', 'alify' ); ?></h2>
				<p><?php esc_html_e( 'Send a live HTML diagnostic email right now to verify your inbox receives ALIFY form submissions without landing in spam.', 'alify' ); ?></p>

				<form method="post" action="" style="margin:18px 0 24px;">
					<?php wp_nonce_field( 'alify_smtp_test_nonce' ); ?>
					<input type="hidden" name="alify_send_smtp_test" value="1" />
					<p>
						<label for="test_recipient"><strong><?php esc_html_e( 'Send Test Email To:', 'alify' ); ?></strong></label><br />
						<input type="email" id="test_recipient" name="test_recipient" class="regular-text" style="margin-top:6px;" value="<?php echo esc_attr( $cfg['from_email'] ? $cfg['from_email'] : get_option( 'admin_email' ) ); ?>" required />
					</p>
					<button type="submit" class="button button-secondary button-hero"><?php esc_html_e( 'Send Live Test Email Now', 'alify' ); ?></button>
				</form>

				<hr />
				<h3><?php esc_html_e( 'Latest Mailer Diagnostic Trace', 'alify' ); ?></h3>
				<?php if ( ! empty( $last_err['message'] ) ) : ?>
					<div style="background:#fff5f5;border-left:4px solid #d63638;padding:12px 14px;border-radius:4px;">
						<p style="margin:0 0 4px;"><strong><?php echo esc_html( $last_err['time'] ?? '' ); ?></strong></p>
						<code style="display:block;white-space:pre-wrap;background:transparent;padding:0;"><?php echo esc_html( $last_err['message'] ); ?></code>
					</div>
				<?php else : ?>
					<p style="color:#0F8A73;font-weight:600;"><?php esc_html_e( '✓ Zero wp_mail() errors recorded.', 'alify' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
