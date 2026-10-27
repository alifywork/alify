<?php
/**
 * ALIFY Forms Settings & Form Templates Studio
 * Adds two WP Admin submenus under `ALIFY Leads & Calls`:
 *   1. Form Templates (`edit.php?post_type=alify_lead&page=alify-form-templates`)
 *   2. Forms Settings (`edit.php?post_type=alify_lead&page=alify-forms-settings`)
 * Provides `[alify_form id="..."]` shortcode and live dynamic rendering across all theme forms.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Global Forms Settings Defaults & Getter.
 */
function alify_get_forms_settings_defaults() {
	$default_email = get_theme_mod( 'alify_email', 'info@alify.site' );
	return array(
		'enable_admin_email'    => '1',
		'recipient_emails'      => $default_email,
		'cc_emails'             => '',
		'bcc_emails'            => '',
		'from_name'             => 'ALIFY Studio',
		'from_email'            => $default_email,
		'email_subject'         => 'New ALIFY {type_label} {ref} — {capability}',
		'enable_autoresponder'  => '1',
		'autoresponder_subject' => 'We received your request ({ref}) — ALIFY Studio',
		'autoresponder_body'    => "Hi {name},\n\nThank you for reaching out to ALIFY Studio! Your request ({ref}) regarding \"{capability}\" has been received by our senior project directors.\n\nWe will review your details and reply within 1 business day.\n\nDirect Studio Email: {studio_email}\nWhatsApp Direct: {whatsapp}\n\nBest regards,\nALIFY Studio Team",
		'submission_behavior'   => 'redirect',
		'thank_you_url'         => '',
		'lead_ref_prefix'       => 'ALF-',
		'call_ref_prefix'       => 'CALL-',
		'enable_honeypot'       => '1',
		'rate_limit_per_10m'    => 15,
		'blocked_terms'         => '',
		'enable_webhook'        => '0',
		'webhook_url'           => '',
	);
}

function alify_get_forms_settings() {
	$saved = get_option( 'alify_forms_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, alify_get_forms_settings_defaults() );
}

/**
 * Resolve Active Global Thank-You URL.
 */
function alify_get_active_thank_you_url() {
	$settings = alify_get_forms_settings();
	if ( ! empty( $settings['thank_you_url'] ) ) {
		return esc_url_raw( $settings['thank_you_url'] );
	}
	return alify_page_url( 'thank-you' );
}

/**
 * 2. Built-In & Custom Form Templates Registry.
 */
function alify_get_builtin_form_templates() {
	return array(
		'reusable_contact' => array(
			'id'                 => 'reusable_contact',
			'is_builtin'         => true,
			'layout'             => 'reusable_contact',
			'label'              => __( 'Global Reusable Contact Form', 'alify' ),
			'location'           => __( 'Above Footer across Home & 16 Inner/Single Pages', 'alify' ),
			'lead_type'          => 'inquiry',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'badge'              => '',
			'title'              => 'Send Us a Message',
			'subtitle'           => 'Fill out the details below and we will get back to you promptly.',
			'button_text'        => 'Send Message',
			'footer_note'        => 'Your details remain strictly confidential.',
			'nda_label'          => '',
			'success_msg'        => 'Thank you! Your message has been sent — our studio team will reply within 24 hours.',
			'redirect_url'       => '',
			'recipient_override' => '',
			'fields'             => array( 'phone', 'service', 'message' ),
			'services_list'      => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions\nScalable Design Systems\nConversion Rate Optimization (CRO)\nInteractive Motion & GSAP\nAI Product & SaaS UX",
			'budgets_list'       => '',
		),
		'contact_page'     => array(
			'id'                 => 'contact_page',
			'is_builtin'         => true,
			'layout'             => 'contact_page',
			'label'              => __( 'Contact Us Page — Complete Inquiry Form', 'alify' ),
			'location'           => __( 'Contact Us Page (/contact/)', 'alify' ),
			'lead_type'          => 'inquiry',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'badge'              => 'Complete Inquiry Form',
			'title'              => 'Tell Us How We Can Help',
			'subtitle'           => 'Complete the fields below and our team will prepare a tailored response within one business day.',
			'button_text'        => 'Submit Complete Inquiry',
			'footer_note'        => 'Guaranteed response within 24 business hours.',
			'nda_label'          => 'Please keep my project details confidential under ALIFY’s standard mutual NDA policy.',
			'success_msg'        => 'Thank you for contacting ALIFY! Your detailed inquiry has been logged and a project director will reach out within 24 hours.',
			'redirect_url'       => '',
			'recipient_override' => '',
			'fields'             => array( 'phone', 'company', 'service', 'budget', 'subject', 'channel', 'message', 'nda' ),
			'services_list'      => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions\nScalable Design Systems\nGeneral / Agency Partnership",
			'budgets_list'       => "$3,000 – $5,000\n$5,000 – $15,000\n$15,000 – $35,000\n$35,000+",
		),
		'start_project'    => array(
			'id'                 => 'start_project',
			'is_builtin'         => true,
			'layout'             => 'start_project',
			'label'              => __( 'Start a Project — 4-Step Scope Builder', 'alify' ),
			'location'           => __( 'Start a Project Page (/start-project/)', 'alify' ),
			'lead_type'          => 'project_brief',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'badge'              => 'STEP 01 – 04',
			'title'              => 'Which capabilities do you need?',
			'subtitle'           => '',
			'button_text'        => 'Submit Project Brief',
			'footer_note'        => 'Proposal & sprint plan delivered within 48 hours.',
			'nda_label'          => 'Send me a signed mutual NDA before our discovery call.',
			'success_msg'        => 'Awesome! Your project brief is in our hands — our strategy & engineering leads will send your roadmap within 48 hours.',
			'redirect_url'       => '',
			'recipient_override' => '',
			'fields'             => array( 'phone', 'company', 'service', 'budget', 'timeline', 'message', 'nda' ),
			'services_list'      => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions\nScalable Design System\nConversion Rate Optimization\nInteractive Motion & GSAP\nAI Product & SaaS UX",
			'budgets_list'       => "$3k – $5k\n$5k – $15k\n$15k – $35k\n$35k+ Enterprise",
			'timelines_list'     => "Urgent (2–4 Weeks)\n1 – 2 Months\n3+ Months\nFlexible",
		),
		'hero_lead'        => array(
			'id'                 => 'hero_lead',
			'is_builtin'         => true,
			'layout'             => 'hero_lead',
			'label'              => __( 'Inner Hero — Quick Lead Form Card', 'alify' ),
			'location'           => __( 'Right Column of Inner Page Hero Banners', 'alify' ),
			'lead_type'          => 'inquiry',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'preserve_page_copy' => '1',
			'badge'              => 'Fast-Track Written Brief',
			'response_pill'      => 'Replies in 24h',
			'title'              => 'Get a Tailored Scope & Estimate',
			'subtitle'           => 'Share your project goals below for a tailored response within 24 hours.',
			'button_text'        => 'Get Itemized Quote',
			'footer_note'        => '',
			'success_msg'        => 'Thank you! Our team received your request and will reply within 24 hours.',
			'redirect_url'       => '',
			'recipient_override' => '',
			'fields'             => array( 'phone', 'service', 'message' ),
			'services_list'      => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions\nScalable Design Systems",
			'budgets_list'       => '',
		),
		'strategy_call'    => array(
			'id'                 => 'strategy_call',
			'is_builtin'         => true,
			'layout'             => 'strategy_call',
			'label'              => __( 'Book a Strategy Call — Scheduler & Global Modal', 'alify' ),
			'location'           => __( 'Book a Call Page (/book-call/) + Global Popup Modal', 'alify' ),
			'lead_type'          => 'strategy_call',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'badge'              => '30-Min Executive Discovery',
			'title'              => 'Book a Strategy Call With ALIFY.',
			'subtitle'           => 'Pick a business day and time slot to map your architecture, UX priorities, and timeline directly with our principals.',
			'button_text'        => 'Confirm 30-Minute Strategy Call',
			'footer_note'        => '',
			'success_msg'        => 'Locking your calendar slot & preparing confirmation...',
			'redirect_url'       => '',
			'recipient_override' => '',
			'tracks_list'        => "New Web / SaaS Build | Custom architecture, UI & full-stack build\nUI/UX & Design System | Brand identity, Figma tokens & UX overhaul\nHeadless E-Commerce & CRO | Shopify Plus, checkout lift & speed\nDedicated Growth Retainer | Ongoing velocity pod, SEO & experimentation",
			'slots_list'         => "10:00 AM\n11:30 AM\n02:00 PM\n03:30 PM\n05:00 PM\n06:30 PM",
			'channels_list'      => "Google Meet\nZoom Video\nWhatsApp Call",
		),
		'newsletter'       => array(
			'id'                 => 'newsletter',
			'is_builtin'         => true,
			'layout'             => 'newsletter',
			'label'              => __( 'Footer — Studio Insights Newsletter', 'alify' ),
			'location'           => __( 'Global Site Footer (All Pages)', 'alify' ),
			'lead_type'          => 'newsletter',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'title'              => 'Subscribe to Studio Insights',
			'placeholder'        => 'Enter your work email',
			'button_text'        => 'Subscribe',
			'success_msg'        => 'Thank you for subscribing to ALIFY insights!',
			'redirect_url'       => '',
			'recipient_override' => '',
		),
	);
}

function alify_get_all_form_templates() {
	$builtins        = alify_get_builtin_form_templates();
	$saved_overrides = get_option( 'alify_form_templates', array() );
	$custom_list     = get_option( 'alify_custom_form_templates', array() );

	if ( ! is_array( $saved_overrides ) ) {
		$saved_overrides = array();
	}
	if ( ! is_array( $custom_list ) ) {
		$custom_list = array();
	}

	$all = array();
	foreach ( $builtins as $id => $defaults ) {
		$ov          = isset( $saved_overrides[ $id ] ) && is_array( $saved_overrides[ $id ] ) ? $saved_overrides[ $id ] : array();
		$merged      = wp_parse_args( $ov, $defaults );
		$merged['id'] = $id;
		$merged['is_builtin'] = true;
		$all[ $id ]  = $merged;
	}

	foreach ( $custom_list as $id => $cfg ) {
		if ( ! is_array( $cfg ) || isset( $all[ $id ] ) ) {
			continue;
		}
		$custom_defaults = array(
			'id'                 => $id,
			'is_builtin'         => false,
			'layout'             => 'reusable_contact',
			'label'              => ucwords( str_replace( '_', ' ', $id ) ),
			'location'           => __( 'Custom Shortcode Form', 'alify' ),
			'lead_type'          => 'inquiry',
			'mode'               => 'native',
			'custom_shortcode'   => '',
			'badge'              => '',
			'title'              => 'Request Information',
			'subtitle'           => 'Fill out the form below and our team will respond within 24 hours.',
			'button_text'        => 'Submit Request',
			'footer_note'        => 'Your details remain strictly confidential.',
			'nda_label'          => 'Keep my details confidential under mutual NDA.',
			'success_msg'        => 'Thank you! Your submission has been received.',
			'redirect_url'       => '',
			'recipient_override' => '',
			'fields'             => array( 'phone', 'company', 'service', 'message' ),
			'services_list'      => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions",
			'budgets_list'       => "$3,000 – $5,000\n$5,000 – $15,000\n$15,000 – $35,000\n$35,000+",
		);
		$merged               = wp_parse_args( $cfg, $custom_defaults );
		$merged['id']         = $id;
		$merged['is_builtin'] = false;
		$all[ $id ]           = $merged;
	}

	return $all;
}

function alify_get_form_template( $template_id ) {
	$all = alify_get_all_form_templates();
	return isset( $all[ $template_id ] ) ? $all[ $template_id ] : null;
}

function alify_parse_lines_list( $raw_text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw_text );
	$clean = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line && false === stripos( $line, 'mobile app' ) ) {
			$clean[] = $line;
		}
	}
	return $clean;
}

/**
 * Build Localized JS Config for Strategy Call Modal & Form Settings (`window.ALIFY_WP`).
 */
function alify_get_frontend_forms_js_config() {
	$settings = alify_get_forms_settings();
	$sc       = alify_get_form_template( 'strategy_call' );

	$slots = $sc ? alify_parse_lines_list( $sc['slots_list'] ) : array( '10:00 AM', '11:30 AM', '02:00 PM', '03:30 PM', '05:00 PM', '06:30 PM' );
	$chans = $sc ? alify_parse_lines_list( $sc['channels_list'] ) : array( 'Google Meet', 'Zoom Video', 'WhatsApp Call' );

	return array(
		'submissionBehavior' => $settings['submission_behavior'],
		'thankYouUrl'        => alify_get_active_thank_you_url(),
		'leadRefPrefix'      => ! empty( $settings['lead_ref_prefix'] ) ? $settings['lead_ref_prefix'] : 'ALF-',
		'callRefPrefix'      => ! empty( $settings['call_ref_prefix'] ) ? $settings['call_ref_prefix'] : 'CALL-',
		'enableHoneypot'     => '1' === (string) $settings['enable_honeypot'],
		'strategyCall'       => array(
			'mode'        => $sc ? $sc['mode'] : 'native',
			'badge'       => $sc ? $sc['badge'] : '30-Min Executive Discovery',
			'title'       => $sc ? $sc['title'] : 'Book a Strategy Call With ALIFY.',
			'subtitle'    => $sc ? $sc['subtitle'] : '',
			'buttonText'  => $sc ? $sc['button_text'] : 'Confirm 30-Minute Strategy Call',
			'redirectUrl' => $sc ? $sc['redirect_url'] : '',
			'slots'       => $slots,
			'channels'    => $chans,
		),
	);
}

/**
 * 3. Frontend Form HTML Builders (Strict Custom CSS + Bootstrap Grid-Only Classes).
 */
function alify_render_honeypot_input() {
	$settings = alify_get_forms_settings();
	if ( '1' !== (string) $settings['enable_honeypot'] ) {
		return '';
	}
	return '<input type="text" name="alify_hp" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;" aria-hidden="true" />';
}

function alify_render_form_template_html( $template_id, $context = array() ) {
	$tpl = alify_get_form_template( $template_id );
	if ( ! $tpl ) {
		return '';
	}

	if ( 'disabled' === $tpl['mode'] ) {
		return '';
	}

	$settings      = alify_get_forms_settings();
	$redirect_mode = ( 'inline' === $settings['submission_behavior'] ) ? 'inline' : 'redirect';
	$redirect_url  = ! empty( $tpl['redirect_url'] ) ? $tpl['redirect_url'] : '';
	$uid           = 'alify_' . sanitize_key( $template_id ) . '_' . wp_rand( 100, 999 );
	$layout        = ! empty( $tpl['layout'] ) ? $tpl['layout'] : 'reusable_contact';

	// Shortcode / External Form Plugin Override Mode
	if ( 'shortcode' === $tpl['mode'] && ! empty( $tpl['custom_shortcode'] ) ) {
		$wrap_class = 'reusable-contact-card';
		if ( 'hero_lead' === $layout ) {
			$wrap_class = 'hero-lead-form-card';
		} elseif ( 'contact_page' === $layout ) {
			$wrap_class = 'contact-complete-card';
		} elseif ( 'start_project' === $layout ) {
			$wrap_class = 'scope-builder-card';
		} elseif ( 'strategy_call' === $layout ) {
			$wrap_class = 'book-studio-card';
		} elseif ( 'newsletter' === $layout ) {
			return '<div class="alify-newsletter-override">' . do_shortcode( wp_kses_post( $tpl['custom_shortcode'] ) ) . '</div>';
		}

		$out = '<div class="' . esc_attr( $wrap_class ) . '">';
		if ( ! empty( $tpl['title'] ) ) {
			$h_cls = ( 'hero_lead' === $layout ) ? 'hero-lead-title' : 'reusable-contact-card-title';
			$out  .= '<h3 class="' . esc_attr( $h_cls ) . '">' . esc_html( $tpl['title'] ) . '</h3>';
		}
		if ( ! empty( $tpl['subtitle'] ) ) {
			$p_cls = ( 'hero_lead' === $layout ) ? 'hero-lead-subtitle' : 'reusable-contact-card-sub';
			$out  .= '<p class="' . esc_attr( $p_cls ) . '">' . esc_html( $tpl['subtitle'] ) . '</p>';
		}
		$out .= do_shortcode( $tpl['custom_shortcode'] );
		$out .= '</div>';
		return $out;
	}

	// Layout A: Footer Newsletter Form
	if ( 'newsletter' === $layout ) {
		ob_start();
		?>
		<label for="footerNewsletterInput" class="footer-newsletter-label">
			<?php echo esc_html( $tpl['title'] ); ?>
		</label>
		<form class="footer-newsletter-form" id="footerNewsletterForm" data-form-template="newsletter" data-success-msg="<?php echo esc_attr( $tpl['success_msg'] ); ?>" novalidate>
			<?php echo alify_render_honeypot_input(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input
				type="email"
				id="footerNewsletterInput"
				class="footer-newsletter-input"
				placeholder="<?php echo esc_attr( ! empty( $tpl['placeholder'] ) ? $tpl['placeholder'] : 'Enter your work email' ); ?>"
				aria-label="<?php echo esc_attr( $tpl['title'] ); ?>"
				required
			/>
			<button type="submit" class="footer-newsletter-btn" aria-label="<?php echo esc_attr( ! empty( $tpl['button_text'] ) ? $tpl['button_text'] : 'Subscribe' ); ?>">
				<i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
			</button>
		</form>
		<p class="footer-newsletter-msg" id="footerNewsletterMsg" aria-live="polite"></p>
		<?php
		return ob_get_clean();
	}

	// Layout B: Inner Hero Quick Lead Card
	if ( 'hero_lead' === $layout ) {
		$badge    = ! empty( $context['badge'] ) ? $context['badge'] : $tpl['badge'];
		$resp     = ! empty( $context['response_pill'] ) ? $context['response_pill'] : ( ! empty( $tpl['response_pill'] ) ? $tpl['response_pill'] : 'Replies in 24h' );
		$title    = ! empty( $context['title'] ) ? $context['title'] : $tpl['title'];
		$subtitle = ! empty( $context['subtitle'] ) ? $context['subtitle'] : $tpl['subtitle'];
		$btn_txt  = ! empty( $tpl['button_text'] ) ? $tpl['button_text'] : 'Get Itemized Quote';
		$services = alify_parse_lines_list( $tpl['services_list'] );

		ob_start();
		?>
		<div class="hero-lead-form-card">
			<div class="hero-lead-form-top">
				<?php if ( ! empty( $badge ) ) : ?>
					<span class="hero-lead-badge">
						<i class="fa-solid fa-bolt" aria-hidden="true"></i>
						<?php echo esc_html( $badge ); ?>
					</span>
				<?php endif; ?>
				<?php if ( ! empty( $resp ) ) : ?>
					<span class="hero-lead-response"><?php echo esc_html( $resp ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $title ) ) : ?>
				<h2 class="hero-lead-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="hero-lead-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<form
				class="js-alify-form"
				data-form-template="<?php echo esc_attr( $tpl['id'] ); ?>"
				data-lead-type="<?php echo esc_attr( $tpl['lead_type'] ); ?>"
				data-redirect-mode="<?php echo esc_attr( $redirect_mode ); ?>"
				data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>"
				data-success-msg="<?php echo esc_attr( $tpl['success_msg'] ); ?>"
				novalidate
			>
				<?php echo alify_render_honeypot_input(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="row alify-form-row">
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<input type="text" class="alify-input" placeholder="Full Name *" aria-label="Full Name" data-required="true" />
							<span class="alify-error-msg">Please enter your name.</span>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<input type="email" class="alify-input" placeholder="Work Email *" aria-label="Work Email" data-required="true" />
							<span class="alify-error-msg">Enter a valid email.</span>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<input type="tel" class="alify-input" placeholder="Phone / WhatsApp *" aria-label="Phone or WhatsApp" data-required="true" />
							<span class="alify-error-msg">Enter phone number.</span>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<select class="alify-select" aria-label="Select Service" data-required="true">
								<option value="">Select Service *</option>
								<?php foreach ( $services as $srv ) : ?>
									<option value="<?php echo esc_attr( sanitize_title( $srv ) ); ?>"><?php echo esc_html( $srv ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="alify-error-msg">Select a service.</span>
						</div>
					</div>
					<div class="col-12">
						<div class="alify-field">
							<textarea class="alify-textarea" placeholder="Briefly describe your target scope &amp; launch timeline *" aria-label="Project Summary" data-required="true"></textarea>
							<span class="alify-error-msg">Please add a short project note.</span>
						</div>
					</div>
					<div class="col-12">
						<button type="submit" class="btn-start-project">
							<span class="btn-start-pill"><?php echo esc_html( $btn_txt ); ?></span>
							<span class="btn-start-circle" aria-hidden="true">
								<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
							</span>
						</button>
					</div>
				</div>

				<div class="alify-form-feedback" aria-live="polite">
					<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					<span class="alify-feedback-text"></span>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	// Layout C: Start a Project 4-Step Scope Builder
	if ( 'start_project' === $layout ) {
		$services  = alify_parse_lines_list( $tpl['services_list'] );
		$budgets   = alify_parse_lines_list( isset( $tpl['budgets_list'] ) ? $tpl['budgets_list'] : '' );
		$timelines = alify_parse_lines_list( isset( $tpl['timelines_list'] ) ? $tpl['timelines_list'] : '' );
		$icons     = array( 'fa-code', 'fa-pen-nib', 'fa-chart-line', 'fa-cart-shopping', 'fa-layer-group', 'fa-bullseye', 'fa-wand-magic-sparkles', 'fa-microchip' );

		ob_start();
		?>
		<div class="scope-builder-card">
			<form
				class="js-alify-form"
				data-form-template="<?php echo esc_attr( $tpl['id'] ); ?>"
				data-lead-type="project_brief"
				data-redirect-mode="<?php echo esc_attr( $redirect_mode ); ?>"
				data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>"
				data-success-msg="<?php echo esc_attr( $tpl['success_msg'] ); ?>"
				novalidate
			>
				<?php echo alify_render_honeypot_input(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<div class="scope-step-block">
					<div class="scope-step-head">
						<span class="scope-step-num">STEP 01</span>
						<h2 class="scope-step-title"><?php echo esc_html( ! empty( $tpl['title'] ) ? $tpl['title'] : 'Which capabilities do you need?' ); ?></h2>
					</div>
					<div class="alify-choice-grid">
						<?php foreach ( $services as $idx => $srv ) : ?>
							<label class="alify-choice-pill">
								<input type="checkbox" name="scope_services" value="<?php echo esc_attr( $srv ); ?>" <?php checked( 0, $idx ); ?> />
								<span class="alify-choice-label">
									<i class="fa-solid <?php echo esc_attr( $icons[ $idx % count( $icons ) ] ); ?>" aria-hidden="true"></i>
									<?php echo esc_html( $srv ); ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( ! empty( $budgets ) ) : ?>
					<div class="scope-step-block">
						<div class="scope-step-head">
							<span class="scope-step-num">STEP 02</span>
							<h2 class="scope-step-title">Estimated Project Investment</h2>
						</div>
						<div class="alify-choice-grid">
							<?php foreach ( $budgets as $b_idx => $b_lbl ) : ?>
								<label class="alify-choice-pill">
									<input type="radio" name="project_budget" value="<?php echo esc_attr( $b_lbl ); ?>" <?php checked( 1, $b_idx ); ?> />
									<span class="alify-choice-label"><?php echo esc_html( $b_lbl ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $timelines ) ) : ?>
					<div class="scope-step-block">
						<div class="scope-step-head">
							<span class="scope-step-num">STEP 03</span>
							<h2 class="scope-step-title">Target Launch Timeline</h2>
						</div>
						<div class="alify-choice-grid">
							<?php foreach ( $timelines as $t_idx => $t_lbl ) : ?>
								<label class="alify-choice-pill">
									<input type="radio" name="project_timeline" value="<?php echo esc_attr( $t_lbl ); ?>" <?php checked( 1, $t_idx ); ?> />
									<span class="alify-choice-label"><?php echo esc_html( $t_lbl ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="scope-step-block">
					<div class="scope-step-head">
						<span class="scope-step-num">STEP 04</span>
						<h2 class="scope-step-title">Your Details &amp; Project Brief</h2>
					</div>
					<div class="row alify-form-row">
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_name">Full Name <span class="alify-req">*</span></label>
								<input type="text" id="<?php echo esc_attr( $uid ); ?>_name" class="alify-input" placeholder="Your full name" data-required="true" />
								<span class="alify-error-msg">Please enter your full name.</span>
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_email">Work Email <span class="alify-req">*</span></label>
								<input type="email" id="<?php echo esc_attr( $uid ); ?>_email" class="alify-input" placeholder="founder@company.com" data-required="true" />
								<span class="alify-error-msg">Please enter a valid work email.</span>
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_phone">Phone / WhatsApp <span class="alify-req">*</span></label>
								<input type="tel" id="<?php echo esc_attr( $uid ); ?>_phone" class="alify-input" placeholder="+90 301 9328094" data-required="true" />
								<span class="alify-error-msg">Please enter your phone number.</span>
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_company">Company / Current Website</label>
								<input type="text" id="<?php echo esc_attr( $uid ); ?>_company" name="company" class="alify-input" placeholder="https://yourbrand.com" />
							</div>
						</div>
						<div class="col-12">
							<div class="alify-field">
								<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_msg">Project Overview, Goals &amp; Key Deliverables <span class="alify-req">*</span></label>
								<textarea id="<?php echo esc_attr( $uid ); ?>_msg" class="alify-textarea" placeholder="Describe what you want to build, target audience, inspiration links, or business metrics you want to move..." data-required="true"></textarea>
								<span class="alify-error-msg">Please describe your project goals.</span>
							</div>
						</div>
						<?php if ( ! empty( $tpl['nda_label'] ) ) : ?>
							<div class="col-12">
								<label class="alify-check-label">
									<input type="checkbox" class="alify-check-input" checked />
									<span><?php echo esc_html( $tpl['nda_label'] ); ?></span>
								</label>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="alify-submit-bar">
					<span class="alify-nda-note">
						<i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
						<span><?php echo esc_html( $tpl['footer_note'] ); ?></span>
					</span>
					<button type="submit" class="btn-start-project">
						<span class="btn-start-pill"><?php echo esc_html( $tpl['button_text'] ); ?></span>
						<span class="btn-start-circle" aria-hidden="true">
							<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
						</span>
					</button>
				</div>

				<div class="alify-form-feedback" aria-live="polite">
					<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					<span class="alify-feedback-text"></span>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	// Layout D: Strategy Call Scheduler (`book-studio-card`)
	if ( 'strategy_call' === $layout ) {
		$raw_tracks = alify_parse_lines_list( $tpl['tracks_list'] );
		$slots      = alify_parse_lines_list( $tpl['slots_list'] );
		$channels   = alify_parse_lines_list( $tpl['channels_list'] );
		$t_icons    = array( 'fa-code', 'fa-pen-nib', 'fa-cart-shopping', 'fa-chart-line' );

		ob_start();
		?>
		<div class="book-studio-card">
			<form
				id="bookPageForm"
				data-form-template="strategy_call"
				data-redirect-mode="<?php echo esc_attr( $redirect_mode ); ?>"
				data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>"
				data-success-msg="<?php echo esc_attr( $tpl['success_msg'] ); ?>"
				novalidate
			>
				<?php echo alify_render_honeypot_input(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="book-step-block">
					<div class="book-step-head">
						<h3 class="book-step-title"><span class="book-step-num">01</span><span>Select Your Primary Focus</span></h3>
					</div>
					<div class="book-track-grid" id="bookPageTracks">
						<?php foreach ( $raw_tracks as $t_idx => $t_line ) : ?>
							<?php
							$parts  = array_map( 'trim', explode( '|', $t_line, 2 ) );
							$t_name = $parts[0];
							$t_meta = isset( $parts[1] ) ? $parts[1] : '';
							?>
							<button type="button" class="book-track-btn<?php echo 0 === $t_idx ? ' is-active' : ''; ?>" data-track="<?php echo esc_attr( $t_name ); ?>">
								<i class="fa-solid <?php echo esc_attr( $t_icons[ $t_idx % count( $t_icons ) ] ); ?>" aria-hidden="true"></i>
								<span>
									<span class="book-track-name"><?php echo esc_html( $t_name ); ?></span>
									<?php if ( ! empty( $t_meta ) ) : ?>
										<span class="book-track-meta"><?php echo esc_html( $t_meta ); ?></span>
									<?php endif; ?>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="book-step-block">
					<div class="book-step-head">
						<h3 class="book-step-title"><span class="book-step-num">02</span><span>Choose Date &amp; Time Slot</span></h3>
						<select class="book-tz-select" id="bookPageTz" aria-label="Select Timezone">
							<option value="PKT (UTC+5)">PKT — Karachi (UTC+5)</option>
							<option value="TRT (UTC+3)">TRT — Istanbul (UTC+3)</option>
							<option value="GST (UTC+4)">GST — Dubai (UTC+4)</option>
							<option value="GMT / UK">GMT / BST — London</option>
							<option value="EST (UTC-5)">EST — New York (UTC-5)</option>
						</select>
					</div>
					<div class="book-dates-grid" id="bookPageDates"></div>
					<div class="book-slots-grid" id="bookPageSlots">
						<?php foreach ( $slots as $s_idx => $slot_txt ) : ?>
							<button type="button" class="book-slot-pill<?php echo 1 === $s_idx ? ' is-active' : ''; ?>" data-slot="<?php echo esc_attr( $slot_txt ); ?>"><?php echo esc_html( $slot_txt ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="book-step-block">
					<div class="book-step-head">
						<h3 class="book-step-title"><span class="book-step-num">03</span><span>Meeting Channel &amp; Your Details</span></h3>
					</div>
					<div class="book-channels-grid" id="bookPageChannels">
						<?php foreach ( $channels as $c_idx => $chan_txt ) : ?>
							<?php
							$c_icon = 'fa-solid fa-video';
							if ( false !== stripos( $chan_txt, 'zoom' ) ) {
								$c_icon = 'fa-solid fa-desktop';
							} elseif ( false !== stripos( $chan_txt, 'whatsapp' ) ) {
								$c_icon = 'fa-brands fa-whatsapp';
							}
							?>
							<button type="button" class="book-channel-pill<?php echo 0 === $c_idx ? ' is-active' : ''; ?>" data-channel="<?php echo esc_attr( $chan_txt ); ?>">
								<i class="<?php echo esc_attr( $c_icon ); ?>" aria-hidden="true"></i>
								<span><?php echo esc_html( $chan_txt ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="row alify-form-row">
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="bookVisitorName">Full Name *</label>
								<input type="text" id="bookVisitorName" class="alify-input" placeholder="Your full name" data-required="true" />
								<span class="alify-error-msg">Please enter your full name.</span>
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="bookVisitorEmail">Work Email *</label>
								<input type="email" id="bookVisitorEmail" class="alify-input" placeholder="you@company.com" data-required="true" />
								<span class="alify-error-msg">Please enter a valid email address.</span>
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="bookVisitorWhatsapp">WhatsApp / Phone</label>
								<input type="tel" id="bookVisitorWhatsapp" class="alify-input" placeholder="+90 301 9328094" />
							</div>
						</div>
						<div class="col-12 col-md-6">
							<div class="alify-field">
								<label class="alify-label" for="bookVisitorCompany">Company / Website URL</label>
								<input type="text" id="bookVisitorCompany" class="alify-input" placeholder="https://yourcompany.com" />
							</div>
						</div>
						<div class="col-12">
							<div class="alify-field">
								<label class="alify-label" for="bookVisitorAgenda">Key Goals or Questions for This Call</label>
								<textarea id="bookVisitorAgenda" class="alify-textarea" rows="3" placeholder="Tell us briefly what you are building, redesigning, or scaling..."></textarea>
							</div>
						</div>
					</div>
				</div>

				<button type="submit" class="book-submit-btn">
					<i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
					<span><?php echo esc_html( $tpl['button_text'] ); ?></span>
				</button>

				<div class="alify-form-feedback" aria-live="polite">
					<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
					<span class="alify-feedback-text"><?php echo esc_html( $tpl['success_msg'] ); ?></span>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	// Layout E: Reusable Contact (`reusable_contact`) & Contact Page (`contact_page`) & Custom Templates
	$is_complete = ( 'contact_page' === $layout );
	$card_class  = $is_complete ? 'contact-complete-card' : 'reusable-contact-card';
	$fields      = isset( $tpl['fields'] ) && is_array( $tpl['fields'] ) ? $tpl['fields'] : array( 'phone', 'service', 'message' );
	$services    = alify_parse_lines_list( isset( $tpl['services_list'] ) ? $tpl['services_list'] : '' );
	$budgets     = alify_parse_lines_list( isset( $tpl['budgets_list'] ) ? $tpl['budgets_list'] : '' );

	ob_start();
	?>
	<div class="<?php echo esc_attr( $card_class ); ?>">
		<?php if ( ! empty( $tpl['badge'] ) ) : ?>
			<div class="contact-sec-eyebrow">
				<span class="contact-sec-eyebrow-dot" aria-hidden="true">△</span>
				<span><?php echo esc_html( $tpl['badge'] ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $tpl['title'] ) ) : ?>
			<h3 class="reusable-contact-card-title"><?php echo esc_html( $tpl['title'] ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $tpl['subtitle'] ) ) : ?>
			<p class="reusable-contact-card-sub"><?php echo esc_html( $tpl['subtitle'] ); ?></p>
		<?php endif; ?>

		<form
			class="js-alify-form"
			data-form-template="<?php echo esc_attr( $tpl['id'] ); ?>"
			data-lead-type="<?php echo esc_attr( $tpl['lead_type'] ); ?>"
			data-redirect-mode="<?php echo esc_attr( $redirect_mode ); ?>"
			data-redirect-url="<?php echo esc_attr( $redirect_url ); ?>"
			data-success-msg="<?php echo esc_attr( $tpl['success_msg'] ); ?>"
			novalidate
		>
			<?php echo alify_render_honeypot_input(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="row alify-form-row">
				<div class="col-12 col-md-6">
					<div class="alify-field">
						<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_name">Full Name <span class="alify-req">*</span></label>
						<input type="text" id="<?php echo esc_attr( $uid ); ?>_name" class="alify-input" placeholder="Enter your full name" data-required="true" />
						<span class="alify-error-msg">Please enter your full name.</span>
					</div>
				</div>
				<div class="col-12 col-md-6">
					<div class="alify-field">
						<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_email">Work Email <span class="alify-req">*</span></label>
						<input type="email" id="<?php echo esc_attr( $uid ); ?>_email" class="alify-input" placeholder="you@company.com" data-required="true" />
						<span class="alify-error-msg">Please enter a valid email address.</span>
					</div>
				</div>

				<?php if ( in_array( 'phone', $fields, true ) ) : ?>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_phone">Phone / WhatsApp <span class="alify-req">*</span></label>
							<input type="tel" id="<?php echo esc_attr( $uid ); ?>_phone" class="alify-input" placeholder="+90 301 9328094" data-required="true" />
							<span class="alify-error-msg">Please enter your phone number.</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'company', $fields, true ) ) : ?>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_company">Company / Organization</label>
							<input type="text" id="<?php echo esc_attr( $uid ); ?>_company" name="company" class="alify-input" placeholder="Company or brand name" />
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'service', $fields, true ) && ! empty( $services ) ) : ?>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_service">Service Interested In <span class="alify-req">*</span></label>
							<select id="<?php echo esc_attr( $uid ); ?>_service" class="alify-select" data-required="true">
								<option value="">Choose a service</option>
								<?php foreach ( $services as $srv ) : ?>
									<option value="<?php echo esc_attr( sanitize_title( $srv ) ); ?>"><?php echo esc_html( $srv ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="alify-error-msg">Please select a service.</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'budget', $fields, true ) && ! empty( $budgets ) ) : ?>
					<div class="col-12 col-md-6">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_budget">Estimated Budget (Optional)</label>
							<select id="<?php echo esc_attr( $uid ); ?>_budget" name="budget" class="alify-select">
								<option value="">Select estimated range</option>
								<?php foreach ( $budgets as $b_lbl ) : ?>
									<option value="<?php echo esc_attr( $b_lbl ); ?>"><?php echo esc_html( $b_lbl ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'subject', $fields, true ) ) : ?>
					<div class="col-12">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_subject">Subject <span class="alify-req">*</span></label>
							<input type="text" id="<?php echo esc_attr( $uid ); ?>_subject" name="subject" class="alify-input" placeholder="How can our studio help you?" data-required="true" />
							<span class="alify-error-msg">Please enter a subject.</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'channel', $fields, true ) ) : ?>
					<div class="col-12">
						<div class="alify-field">
							<span class="alify-label">Preferred Contact Channel</span>
							<div class="alify-choice-grid">
								<label class="alify-choice-pill">
									<input type="radio" name="preferred_channel" value="Email" checked />
									<span class="alify-choice-label"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email</span>
								</label>
								<label class="alify-choice-pill">
									<input type="radio" name="preferred_channel" value="Phone / WhatsApp" />
									<span class="alify-choice-label"><i class="fa-solid fa-phone" aria-hidden="true"></i> Phone / WhatsApp</span>
								</label>
								<label class="alify-choice-pill">
									<input type="radio" name="preferred_channel" value="Discovery Video Call" />
									<span class="alify-choice-label"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Discovery Video Call</span>
								</label>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'message', $fields, true ) ) : ?>
					<div class="col-12">
						<div class="alify-field">
							<label class="alify-label" for="<?php echo esc_attr( $uid ); ?>_msg">Project / Inquiry Details <span class="alify-req">*</span></label>
							<textarea id="<?php echo esc_attr( $uid ); ?>_msg" class="alify-textarea" placeholder="Tell us about your project, goals, and desired timeline..." data-required="true"></textarea>
							<span class="alify-error-msg">Please share a few details about your inquiry.</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( in_array( 'nda', $fields, true ) && ! empty( $tpl['nda_label'] ) ) : ?>
					<div class="col-12">
						<label class="alify-check-label">
							<input type="checkbox" class="alify-check-input" checked />
							<span><?php echo esc_html( $tpl['nda_label'] ); ?></span>
						</label>
					</div>
				<?php endif; ?>

				<div class="col-12">
					<div class="alify-submit-bar">
						<span class="alify-nda-note">
							<i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
							<span><?php echo esc_html( $tpl['footer_note'] ); ?></span>
						</span>
						<button type="submit" class="btn-start-project">
							<span class="btn-start-pill"><?php echo esc_html( $tpl['button_text'] ); ?></span>
							<span class="btn-start-circle" aria-hidden="true">
								<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
							</span>
						</button>
					</div>
				</div>
			</div>

			<div class="alify-form-feedback" aria-live="polite">
				<i class="fa-solid fa-circle-check" aria-hidden="true"></i>
				<span class="alify-feedback-text"></span>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * 4. Balanced `<div>` Replacer — Injects Live Form Templates Into All Page Partials Safely.
 */
function alify_replace_div_by_class( $html, $target_class, $callback ) {
	$needle = 'class="' . $target_class . '"';
	$offset = 0;

	while ( false !== ( $cls_pos = strpos( $html, $needle, $offset ) ) ) {
		$div_start = strrpos( substr( $html, 0, $cls_pos ), '<div' );
		if ( false === $div_start ) {
			$offset = $cls_pos + strlen( $needle );
			continue;
		}

		// Scan forward counting nested `<div` and `</div>` tags to find exact matching close tag
		$len   = strlen( $html );
		$depth = 0;
		$pos   = $div_start;
		$end   = false;

		while ( $pos < $len ) {
			$next_open  = stripos( $html, '<div', $pos );
			$next_close = stripos( $html, '</div', $pos );

			if ( false === $next_close ) {
				break;
			}

			if ( false !== $next_open && $next_open < $next_close ) {
				$after = substr( $html, $next_open + 4, 1 );
				if ( '' === $after || ctype_space( $after ) || '>' === $after ) {
					$depth++;
				}
				$pos = $next_open + 4;
			} else {
				$close_gt = strpos( $html, '>', $next_close );
				if ( false === $close_gt ) {
					break;
				}
				$depth--;
				$pos = $close_gt + 1;
				if ( 0 === $depth ) {
					$end = $pos;
					break;
				}
			}
		}

		if ( false === $end ) {
			$offset = $cls_pos + strlen( $needle );
			continue;
		}

		$original_block = substr( $html, $div_start, $end - $div_start );
		$replacement    = call_user_func( $callback, $original_block );
		$html           = substr( $html, 0, $div_start ) . $replacement . substr( $html, $end );
		$offset         = $div_start + strlen( $replacement );
	}

	return $html;
}

function alify_apply_dynamic_form_templates( $html ) {
	if ( false !== strpos( $html, 'class="reusable-contact-card"' ) ) {
		$html = alify_replace_div_by_class(
			$html,
			'reusable-contact-card',
			function () {
				return alify_render_form_template_html( 'reusable_contact' );
			}
		);
	}

	if ( false !== strpos( $html, 'class="contact-complete-card"' ) ) {
		$html = alify_replace_div_by_class(
			$html,
			'contact-complete-card',
			function () {
				return alify_render_form_template_html( 'contact_page' );
			}
		);
	}

	if ( false !== strpos( $html, 'class="scope-builder-card"' ) ) {
		$html = alify_replace_div_by_class(
			$html,
			'scope-builder-card',
			function () {
				return alify_render_form_template_html( 'start_project' );
			}
		);
	}

	if ( false !== strpos( $html, 'class="book-studio-card"' ) ) {
		$html = alify_replace_div_by_class(
			$html,
			'book-studio-card',
			function () {
				return alify_render_form_template_html( 'strategy_call' );
			}
		);
	}

	if ( false !== strpos( $html, 'class="hero-lead-form-card"' ) ) {
		$hero_tpl = alify_get_form_template( 'hero_lead' );
		$html     = alify_replace_div_by_class(
			$html,
			'hero-lead-form-card',
			function ( $orig ) use ( $hero_tpl ) {
				$ctx = array();
				if ( $hero_tpl && '1' === (string) $hero_tpl['preserve_page_copy'] ) {
					if ( preg_match( '/<h2 class="hero-lead-title">(.*?)<\/h2>/s', $orig, $m ) ) {
						$ctx['title'] = trim( wp_strip_all_tags( $m[1] ) );
					}
					if ( preg_match( '/<p class="hero-lead-subtitle">(.*?)<\/p>/s', $orig, $m ) ) {
						$ctx['subtitle'] = trim( wp_strip_all_tags( $m[1] ) );
					}
					if ( preg_match( '/<span class="hero-lead-response">(.*?)<\/span>/s', $orig, $m ) ) {
						$ctx['response_pill'] = trim( wp_strip_all_tags( $m[1] ) );
					}
					if ( preg_match( '/<span class="hero-lead-badge">.*?<\/i>\s*(.*?)<\/span>/s', $orig, $m ) ) {
						$ctx['badge'] = trim( wp_strip_all_tags( $m[1] ) );
					}
				}
				return alify_render_form_template_html( 'hero_lead', $ctx );
			}
		);
	}

	return $html;
}

/**
 * 5. `[alify_form id="..."]` WordPress Shortcode (Embed Any Form Template on Any Page/Post).
 */
function alify_shortcode_render_form( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'       => 'reusable_contact',
			'template' => '',
			'title'    => '',
			'subtitle' => '',
		),
		$atts,
		'alify_form'
	);

	$tpl_id = ! empty( $atts['template'] ) ? sanitize_key( $atts['template'] ) : sanitize_key( $atts['id'] );

	// Ensure form styles & scripts are loaded even on pages where forms weren't conditionally enqueued
	wp_enqueue_style( 'alify-forms', alify_asset_uri( 'assets/css/reusable-component-css/forms.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/reusable-component-css/forms.css' ) );
	wp_enqueue_script( 'alify-forms-js', alify_asset_uri( 'assets/js/reusable-component-js/forms.js' ), array( 'alify-ui-polish-js' ), alify_file_ver( 'assets/js/reusable-component-js/forms.js' ), true );

	if ( 'strategy_call' === $tpl_id ) {
		wp_enqueue_style( 'alify-book-call', alify_asset_uri( 'assets/css/reusable-component-css/book-call.css' ), array( 'alify-bootstrap-grid' ), alify_file_ver( 'assets/css/reusable-component-css/book-call.css' ) );
		wp_enqueue_script( 'alify-book-call-js', alify_asset_uri( 'assets/js/reusable-component-js/book-call.js' ), array( 'alify-ui-polish-js' ), alify_file_ver( 'assets/js/reusable-component-js/book-call.js' ), true );
	}

	$ctx = array();
	if ( ! empty( $atts['title'] ) ) {
		$ctx['title'] = $atts['title'];
	}
	if ( ! empty( $atts['subtitle'] ) ) {
		$ctx['subtitle'] = $atts['subtitle'];
	}

	return alify_render_form_template_html( $tpl_id, $ctx );
}
add_shortcode( 'alify_form', 'alify_shortcode_render_form' );

/**
 * 6. Register WP Admin Submenus Under `ALIFY Leads & Calls` (`edit.php?post_type=alify_lead`).
 */
function alify_register_forms_admin_menus() {
	// Remove default "Add New Lead" item to keep CRM menu clean
	remove_submenu_page( 'edit.php?post_type=alify_lead', 'post-new.php?post_type=alify_lead' );

	add_submenu_page(
		'edit.php?post_type=alify_lead',
		__( 'ALIFY Form Templates', 'alify' ),
		__( 'Form Templates', 'alify' ),
		'manage_options',
		'alify-form-templates',
		'alify_render_form_templates_admin_page'
	);

	add_submenu_page(
		'edit.php?post_type=alify_lead',
		__( 'ALIFY Forms Settings', 'alify' ),
		__( 'Forms Settings', 'alify' ),
		'manage_options',
		'alify-forms-settings',
		'alify_render_forms_settings_admin_page'
	);
}
add_action( 'admin_menu', 'alify_register_forms_admin_menus', 20 );

/**
 * Count Stored Submissions Per Form Template ID.
 */
function alify_count_leads_by_template( $template_id, $fallback_lead_type = '' ) {
	$by_tpl = get_posts(
		array(
			'post_type'      => 'alify_lead',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_alify_lead_template',
					'value' => $template_id,
				),
			),
		)
	);
	$count = is_array( $by_tpl ) ? count( $by_tpl ) : 0;

	if ( 0 === $count && in_array( $fallback_lead_type, array( 'strategy_call', 'project_brief', 'newsletter' ), true ) ) {
		$by_type = get_posts(
			array(
				'post_type'      => 'alify_lead',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => '_alify_lead_type',
						'value' => $fallback_lead_type,
					),
				),
			)
		);
		$count = is_array( $by_type ) ? count( $by_type ) : 0;
	}

	return $count;
}

/**
 * 7. Handle Admin POST Saves for Forms Settings, Test Email & Form Templates.
 */
function alify_handle_forms_admin_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Action A: Save Global Forms Settings
	if ( isset( $_POST['alify_save_forms_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_save_forms_settings_nonce'] ) ), 'alify_save_forms_settings' ) ) {
		$new_settings = array(
			'enable_admin_email'    => isset( $_POST['enable_admin_email'] ) ? '1' : '0',
			'recipient_emails'      => isset( $_POST['recipient_emails'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_emails'] ) ) : 'info@alify.site',
			'cc_emails'             => isset( $_POST['cc_emails'] ) ? sanitize_text_field( wp_unslash( $_POST['cc_emails'] ) ) : '',
			'bcc_emails'            => isset( $_POST['bcc_emails'] ) ? sanitize_text_field( wp_unslash( $_POST['bcc_emails'] ) ) : '',
			'from_name'             => isset( $_POST['from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['from_name'] ) ) : 'ALIFY Studio',
			'from_email'            => isset( $_POST['from_email'] ) ? sanitize_email( wp_unslash( $_POST['from_email'] ) ) : 'info@alify.site',
			'email_subject'         => isset( $_POST['email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['email_subject'] ) ) : 'New ALIFY {type_label} {ref} — {capability}',
			'enable_autoresponder'  => isset( $_POST['enable_autoresponder'] ) ? '1' : '0',
			'autoresponder_subject' => isset( $_POST['autoresponder_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['autoresponder_subject'] ) ) : '',
			'autoresponder_body'    => isset( $_POST['autoresponder_body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['autoresponder_body'] ) ) : '',
			'submission_behavior'   => isset( $_POST['submission_behavior'] ) && 'inline' === $_POST['submission_behavior'] ? 'inline' : 'redirect',
			'thank_you_url'         => isset( $_POST['thank_you_url'] ) ? esc_url_raw( wp_unslash( $_POST['thank_you_url'] ) ) : '',
			'lead_ref_prefix'       => isset( $_POST['lead_ref_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_ref_prefix'] ) ) : 'ALF-',
			'call_ref_prefix'       => isset( $_POST['call_ref_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['call_ref_prefix'] ) ) : 'CALL-',
			'enable_honeypot'       => isset( $_POST['enable_honeypot'] ) ? '1' : '0',
			'rate_limit_per_10m'    => isset( $_POST['rate_limit_per_10m'] ) ? max( 0, absint( $_POST['rate_limit_per_10m'] ) ) : 15,
			'blocked_terms'         => isset( $_POST['blocked_terms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['blocked_terms'] ) ) : '',
			'enable_webhook'        => isset( $_POST['enable_webhook'] ) ? '1' : '0',
			'webhook_url'           => isset( $_POST['webhook_url'] ) ? esc_url_raw( wp_unslash( $_POST['webhook_url'] ) ) : '',
		);

		update_option( 'alify_forms_settings', $new_settings );

		if ( ! empty( $_POST['alify_send_test_email'] ) ) {
			$recipients = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $new_settings['recipient_emails'] ) ) ) );
			$to         = ! empty( $recipients ) ? $recipients : array( get_option( 'admin_email' ) );
			$headers    = array();
			if ( ! empty( $new_settings['from_email'] ) && is_email( $new_settings['from_email'] ) ) {
				$headers[] = 'From: ' . $new_settings['from_name'] . ' <' . $new_settings['from_email'] . '>';
			}
			$sent = wp_mail(
				$to,
				'[Test] ALIFY Forms Notification Delivery Check',
				"Success! Your ALIFY WordPress Theme Forms Settings email configuration is active.\n\nRecipient(s): " . implode( ', ', $to ) . "\nTimestamp: " . gmdate( 'Y-m-d H:i:s' ) . ' UTC',
				$headers
			);
			wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-forms-settings', 'test_mail' => $sent ? 'sent' : 'failed' ), admin_url( 'edit.php' ) ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-forms-settings', 'updated' => '1' ), admin_url( 'edit.php' ) ) );
		exit;
	}

	// Action B: Save Single Form Template Configuration
	if ( isset( $_POST['alify_save_form_tpl_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_save_form_tpl_nonce'] ) ), 'alify_save_form_tpl' ) ) {
		$tpl_id   = isset( $_POST['tpl_id'] ) ? sanitize_key( wp_unslash( $_POST['tpl_id'] ) ) : '';
		$builtins = alify_get_builtin_form_templates();

		if ( ! empty( $_POST['alify_reset_template'] ) && isset( $builtins[ $tpl_id ] ) ) {
			$saved = get_option( 'alify_form_templates', array() );
			unset( $saved[ $tpl_id ] );
			update_option( 'alify_form_templates', $saved );
			wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates', 'edit_tpl' => $tpl_id, 'reset' => '1' ), admin_url( 'edit.php' ) ) );
			exit;
		}

		if ( ! empty( $_POST['alify_delete_custom_template'] ) && ! isset( $builtins[ $tpl_id ] ) ) {
			$customs = get_option( 'alify_custom_form_templates', array() );
			unset( $customs[ $tpl_id ] );
			update_option( 'alify_custom_form_templates', $customs );
			wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates', 'deleted' => '1' ), admin_url( 'edit.php' ) ) );
			exit;
		}

		$allowed_modes = array( 'native', 'shortcode', 'disabled' );
		$mode          = isset( $_POST['mode'] ) && in_array( $_POST['mode'], $allowed_modes, true ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'native';

		$fields_clean = array();
		if ( isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ) {
			foreach ( wp_unslash( $_POST['fields'] ) as $f_key ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$fields_clean[] = sanitize_key( $f_key );
			}
		}

		$payload = array(
			'mode'               => $mode,
			'custom_shortcode'   => isset( $_POST['custom_shortcode'] ) ? wp_kses_post( wp_unslash( $_POST['custom_shortcode'] ) ) : '',
			'preserve_page_copy' => isset( $_POST['preserve_page_copy'] ) ? '1' : '0',
			'badge'              => isset( $_POST['badge'] ) ? sanitize_text_field( wp_unslash( $_POST['badge'] ) ) : '',
			'response_pill'      => isset( $_POST['response_pill'] ) ? sanitize_text_field( wp_unslash( $_POST['response_pill'] ) ) : '',
			'title'              => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'subtitle'           => isset( $_POST['subtitle'] ) ? sanitize_textarea_field( wp_unslash( $_POST['subtitle'] ) ) : '',
			'placeholder'        => isset( $_POST['placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['placeholder'] ) ) : '',
			'button_text'        => isset( $_POST['button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) : '',
			'footer_note'        => isset( $_POST['footer_note'] ) ? sanitize_text_field( wp_unslash( $_POST['footer_note'] ) ) : '',
			'nda_label'          => isset( $_POST['nda_label'] ) ? sanitize_text_field( wp_unslash( $_POST['nda_label'] ) ) : '',
			'success_msg'        => isset( $_POST['success_msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['success_msg'] ) ) : '',
			'redirect_url'       => isset( $_POST['redirect_url'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_url'] ) ) : '',
			'recipient_override' => isset( $_POST['recipient_override'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_override'] ) ) : '',
			'services_list'      => isset( $_POST['services_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['services_list'] ) ) : '',
			'budgets_list'       => isset( $_POST['budgets_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['budgets_list'] ) ) : '',
			'timelines_list'     => isset( $_POST['timelines_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['timelines_list'] ) ) : '',
			'tracks_list'        => isset( $_POST['tracks_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tracks_list'] ) ) : '',
			'slots_list'         => isset( $_POST['slots_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['slots_list'] ) ) : '',
			'channels_list'      => isset( $_POST['channels_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['channels_list'] ) ) : '',
		);

		if ( isset( $builtins[ $tpl_id ] ) ) {
			$saved = get_option( 'alify_form_templates', array() );
			if ( ! is_array( $saved ) ) {
				$saved = array();
			}
			$saved[ $tpl_id ] = $payload;
			update_option( 'alify_form_templates', $saved );
		} elseif ( ! empty( $tpl_id ) ) {
			$customs = get_option( 'alify_custom_form_templates', array() );
			if ( ! is_array( $customs ) ) {
				$customs = array();
			}
			$existing_custom         = isset( $customs[ $tpl_id ] ) ? $customs[ $tpl_id ] : array();
			$payload['label']        = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : ( isset( $existing_custom['label'] ) ? $existing_custom['label'] : $tpl_id );
			$payload['layout']       = isset( $_POST['layout'] ) && in_array( $_POST['layout'], array( 'reusable_contact', 'contact_page', 'hero_lead' ), true ) ? sanitize_key( wp_unslash( $_POST['layout'] ) ) : 'reusable_contact';
			$payload['lead_type']    = isset( $_POST['lead_type'] ) && in_array( $_POST['lead_type'], array( 'inquiry', 'project_brief', 'strategy_call' ), true ) ? sanitize_key( wp_unslash( $_POST['lead_type'] ) ) : 'inquiry';
			$payload['fields']       = ! empty( $fields_clean ) ? $fields_clean : array( 'phone', 'service', 'message' );
			$customs[ $tpl_id ]      = wp_parse_args( $payload, $existing_custom );
			update_option( 'alify_custom_form_templates', $customs );
		}

		wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates', 'edit_tpl' => $tpl_id, 'saved' => '1' ), admin_url( 'edit.php' ) ) );
		exit;
	}

	// Action C: Create Brand-New Custom Form Template
	if ( isset( $_POST['alify_create_custom_tpl_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alify_create_custom_tpl_nonce'] ) ), 'alify_create_custom_tpl' ) ) {
		$raw_name = isset( $_POST['new_tpl_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_tpl_name'] ) ) : '';
		$slug     = sanitize_key( str_replace( '-', '_', sanitize_title( $raw_name ) ) );
		if ( empty( $slug ) ) {
			$slug = 'custom_form_' . wp_rand( 100, 999 );
		}
		$builtins = alify_get_builtin_form_templates();
		if ( isset( $builtins[ $slug ] ) ) {
			$slug .= '_custom';
		}

		$layout  = isset( $_POST['new_tpl_layout'] ) && in_array( $_POST['new_tpl_layout'], array( 'reusable_contact', 'contact_page', 'hero_lead' ), true ) ? sanitize_key( wp_unslash( $_POST['new_tpl_layout'] ) ) : 'reusable_contact';
		$customs = get_option( 'alify_custom_form_templates', array() );
		if ( ! is_array( $customs ) ) {
			$customs = array();
		}

		$customs[ $slug ] = array(
			'id'            => $slug,
			'is_builtin'    => false,
			'label'         => $raw_name ? $raw_name : ucwords( str_replace( '_', ' ', $slug ) ),
			'location'      => __( 'Shortcode Embed Form', 'alify' ),
			'layout'        => $layout,
			'lead_type'     => 'inquiry',
			'mode'          => 'native',
			'title'         => $raw_name ? $raw_name : 'Send Us a Message',
			'subtitle'      => 'Complete the form below and our team will reply within 24 hours.',
			'button_text'   => 'Submit Request',
			'footer_note'   => 'Your details remain strictly confidential.',
			'nda_label'     => 'Keep my project details confidential under mutual NDA.',
			'success_msg'   => 'Thank you! Your request has been received.',
			'fields'        => array( 'phone', 'company', 'service', 'message' ),
			'services_list' => "Web Design & Development\nUI/UX & Brand Identity\nDigital Marketing & SEO\nE-Commerce Solutions\nScalable Design Systems",
			'budgets_list'  => "$3,000 – $5,000\n$5,000 – $15,000\n$15,000 – $35,000\n$35,000+",
		);
		update_option( 'alify_custom_form_templates', $customs );

		wp_safe_redirect( add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates', 'edit_tpl' => $slug, 'created' => '1' ), admin_url( 'edit.php' ) ) );
		exit;
	}
}
add_action( 'admin_init', 'alify_handle_forms_admin_actions' );

/**
 * 8. Render WP Admin Screen: `ALIFY Leads & Calls → Form Templates`.
 */
function alify_render_form_templates_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$templates = alify_get_all_form_templates();
	$edit_key  = isset( $_GET['edit_tpl'] ) ? sanitize_key( wp_unslash( $_GET['edit_tpl'] ) ) : 'reusable_contact'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $templates[ $edit_key ] ) ) {
		$keys     = array_keys( $templates );
		$edit_key = reset( $keys );
	}
	$active_tpl   = $templates[ $edit_key ];
	$settings_url = add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-forms-settings' ), admin_url( 'edit.php' ) );
	$leads_url    = admin_url( 'edit.php?post_type=alify_lead' );
	?>
	<div class="wrap">
		<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
			<div>
				<h1 style="margin:0 0 4px;font-weight:700;"><?php esc_html_e( 'ALIFY Form Templates & Shortcode Studio', 'alify' ); ?></h1>
				<p style="margin:0;color:#4b5563;">
					<?php esc_html_e( 'Customize all 6 built-in website forms, copy `[alify_form id="..."]` shortcodes for any page/post, or create custom forms.', 'alify' ); ?>
				</p>
			</div>
			<div style="display:flex;gap:8px;">
				<a href="<?php echo esc_url( $leads_url ); ?>" class="button"><?php esc_html_e( '← Leads Inbox', 'alify' ); ?></a>
				<a href="<?php echo esc_url( $settings_url ); ?>" class="button button-primary"><?php esc_html_e( 'Global Forms Settings ⚙', 'alify' ); ?></a>
			</div>
		</div>

		<?php if ( ! empty( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Form template updated! Live website forms now reflect your changes immediately.', 'alify' ); ?></strong></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['created'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Custom form template created! Copy its shortcode below to embed it on any WordPress page or post.', 'alify' ); ?></strong></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'Form template restored to ALIFY factory defaults.', 'alify' ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'Custom form template deleted.', 'alify' ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:minmax(300px, 360px) 1fr;gap:22px;align-items:start;">
			<!-- Left Directory: All Built-In + Custom Form Templates -->
			<div style="display:grid;gap:14px;">
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;overflow:hidden;">
					<div style="padding:12px 16px;background:#12151c;color:#fff;font-weight:600;font-size:13px;">
						<?php esc_html_e( 'Active Form Templates', 'alify' ); ?> (<?php echo count( $templates ); ?>)
					</div>
					<div>
						<?php foreach ( $templates as $id => $t ) : ?>
							<?php
							$is_cur    = ( $id === $edit_key );
							$edit_link = add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates', 'edit_tpl' => $id ), admin_url( 'edit.php' ) );
							$sub_count = alify_count_leads_by_template( $id, isset( $t['lead_type'] ) ? $t['lead_type'] : '' );
							$mode_lbl  = 'native' === $t['mode'] ? 'Native' : ( 'shortcode' === $t['mode'] ? 'Shortcode' : 'Hidden' );
							$mode_bg   = 'native' === $t['mode'] ? '#ecfdf5;color:#047857' : ( 'shortcode' === $t['mode'] ? '#eff6ff;color:#1d4ed8' : '#fef2f2;color:#b91c1c' );
							?>
							<a href="<?php echo esc_url( $edit_link ); ?>" style="display:block;padding:13px 16px;text-decoration:none;border-bottom:1px solid #f0f0f1;background:<?php echo $is_cur ? '#f0f6fc' : '#fff'; ?>;border-left:4px solid <?php echo $is_cur ? '#e05a3a' : 'transparent'; ?>;">
								<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
									<strong style="color:#111827;font-size:13.5px;"><?php echo esc_html( $t['label'] ); ?></strong>
									<span style="padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:<?php echo esc_attr( $mode_bg ); ?>;"><?php echo esc_html( $mode_lbl ); ?></span>
								</div>
								<div style="color:#6b7280;font-size:12px;margin-top:4px;"><?php echo esc_html( $t['location'] ); ?></div>
								<div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:11.5px;">
									<code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;color:#1f2937;">[alify_form id="<?php echo esc_html( $id ); ?>"]</code>
									<span style="color:#0f8a73;font-weight:600;"><?php echo esc_html( (string) $sub_count ); ?> <?php esc_html_e( 'leads', 'alify' ); ?></span>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Create Custom Form Template Box -->
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:16px;">
					<h3 style="margin:0 0 8px;font-size:14px;"><?php esc_html_e( '+ Create New Form Template', 'alify' ); ?></h3>
					<p style="margin:0 0 12px;font-size:12px;color:#6b7280;">
						<?php esc_html_e( 'Create a reusable form template and embed it on any page or blog post via shortcode.', 'alify' ); ?>
					</p>
					<form method="post">
						<?php wp_nonce_field( 'alify_create_custom_tpl', 'alify_create_custom_tpl_nonce' ); ?>
						<p style="margin:0 0 10px;">
							<label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;"><?php esc_html_e( 'Template Name', 'alify' ); ?></label>
							<input type="text" name="new_tpl_name" class="widefat" placeholder="e.g. Free UX Audit Form" required />
						</p>
						<p style="margin:0 0 12px;">
							<label style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;"><?php esc_html_e( 'Card Design Style', 'alify' ); ?></label>
							<select name="new_tpl_layout" class="widefat">
								<option value="reusable_contact"><?php esc_html_e( 'Standard Studio Contact Card', 'alify' ); ?></option>
								<option value="contact_page"><?php esc_html_e( 'Complete Multi-Field Inquiry Card', 'alify' ); ?></option>
								<option value="hero_lead"><?php esc_html_e( 'Compact Quick-Lead Card', 'alify' ); ?></option>
							</select>
						</p>
						<button type="submit" class="button button-secondary" style="width:100%;"><?php esc_html_e( 'Create Form Template', 'alify' ); ?></button>
					</form>
				</div>
			</div>

			<!-- Right Editor Panel: Customize Active Form Template -->
			<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:22px;">
				<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;margin-bottom:18px;">
					<div>
						<span style="display:inline-block;padding:3px 10px;border-radius:999px;background:#fff4ed;color:#e05a3a;font-weight:700;font-size:11px;margin-bottom:4px;">
							<?php echo ! empty( $active_tpl['is_builtin'] ) ? esc_html__( 'BUILT-IN THEME FORM', 'alify' ) : esc_html__( 'CUSTOM SHORTCODE FORM', 'alify' ); ?>
						</span>
						<h2 style="margin:0;font-size:19px;"><?php echo esc_html( $active_tpl['label'] ); ?></h2>
						<p style="margin:4px 0 0;color:#6b7280;font-size:13px;"><?php echo esc_html( $active_tpl['location'] ); ?></p>
					</div>
					<div style="background:#f8fafc;border:1px solid #cbd5e1;padding:8px 12px;border-radius:8px;">
						<span style="display:block;font-size:11px;color:#64748b;font-weight:600;margin-bottom:2px;"><?php esc_html_e( 'Embed Shortcode (Any Page / Post):', 'alify' ); ?></span>
						<code style="font-size:13px;font-weight:700;color:#0f3473;user-select:all;">[alify_form id="<?php echo esc_html( $active_tpl['id'] ); ?>"]</code>
					</div>
				</div>

				<form method="post">
					<?php wp_nonce_field( 'alify_save_form_tpl', 'alify_save_form_tpl_nonce' ); ?>
					<input type="hidden" name="tpl_id" value="<?php echo esc_attr( $active_tpl['id'] ); ?>" />

					<table class="form-table" role="presentation">
						<tbody>
							<?php if ( empty( $active_tpl['is_builtin'] ) ) : ?>
								<tr>
									<th scope="row"><label for="tpl_label"><?php esc_html_e( 'Template Admin Name', 'alify' ); ?></label></th>
									<td>
										<input type="text" id="tpl_label" name="label" class="regular-text" value="<?php echo esc_attr( $active_tpl['label'] ); ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="tpl_layout"><?php esc_html_e( 'Visual Card Layout', 'alify' ); ?></label></th>
									<td>
										<select id="tpl_layout" name="layout">
											<option value="reusable_contact" <?php selected( $active_tpl['layout'], 'reusable_contact' ); ?>><?php esc_html_e( 'Standard Studio Contact Card', 'alify' ); ?></option>
											<option value="contact_page" <?php selected( $active_tpl['layout'], 'contact_page' ); ?>><?php esc_html_e( 'Complete Multi-Field Inquiry Card', 'alify' ); ?></option>
											<option value="hero_lead" <?php selected( $active_tpl['layout'], 'hero_lead' ); ?>><?php esc_html_e( 'Compact Quick-Lead Card', 'alify' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="tpl_lead_type"><?php esc_html_e( 'CRM Lead Tag', 'alify' ); ?></label></th>
									<td>
										<select id="tpl_lead_type" name="lead_type">
											<option value="inquiry" <?php selected( $active_tpl['lead_type'], 'inquiry' ); ?>><?php esc_html_e( 'General Inquiry', 'alify' ); ?></option>
											<option value="project_brief" <?php selected( $active_tpl['lead_type'], 'project_brief' ); ?>><?php esc_html_e( 'Project Brief', 'alify' ); ?></option>
											<option value="strategy_call" <?php selected( $active_tpl['lead_type'], 'strategy_call' ); ?>><?php esc_html_e( 'Strategy Call', 'alify' ); ?></option>
										</select>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row"><label for="tpl_mode"><?php esc_html_e( 'Form Rendering Mode', 'alify' ); ?></label></th>
								<td>
									<select id="tpl_mode" name="mode">
										<option value="native" <?php selected( $active_tpl['mode'], 'native' ); ?>><?php esc_html_e( 'ALIFY Native Form (Saves to Leads CRM + Sends Email)', 'alify' ); ?></option>
										<option value="shortcode" <?php selected( $active_tpl['mode'], 'shortcode' ); ?>><?php esc_html_e( 'External Plugin Shortcode / Custom HTML (CF7, WPForms, Fluent Forms)', 'alify' ); ?></option>
										<option value="disabled" <?php selected( $active_tpl['mode'], 'disabled' ); ?>><?php esc_html_e( 'Disabled / Hidden', 'alify' ); ?></option>
									</select>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="tpl_custom_shortcode"><?php esc_html_e( 'External Shortcode / HTML (If Shortcode Mode)', 'alify' ); ?></label></th>
								<td>
									<textarea id="tpl_custom_shortcode" name="custom_shortcode" rows="2" class="large-text code" placeholder='e.g. [contact-form-7 id="123" title="Contact"]'><?php echo esc_textarea( isset( $active_tpl['custom_shortcode'] ) ? $active_tpl['custom_shortcode'] : '' ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Used only when Form Rendering Mode above is set to "External Plugin Shortcode / Custom HTML".', 'alify' ); ?></p>
								</td>
							</tr>

							<?php if ( 'hero_lead' === $active_tpl['id'] ) : ?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Contextual Page Headings', 'alify' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="preserve_page_copy" value="1" <?php checked( isset( $active_tpl['preserve_page_copy'] ) ? $active_tpl['preserve_page_copy'] : '1', '1' ); ?> />
											<?php esc_html_e( 'Keep each inner page’s contextual card title/subtitle (uncheck to force the custom Title & Subtitle below on all pages).', 'alify' ); ?>
										</label>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( 'newsletter' !== $active_tpl['layout'] ) : ?>
								<tr>
									<th scope="row"><label for="tpl_badge"><?php esc_html_e( 'Badge / Eyebrow Text', 'alify' ); ?></label></th>
									<td>
										<input type="text" id="tpl_badge" name="badge" class="regular-text" value="<?php echo esc_attr( isset( $active_tpl['badge'] ) ? $active_tpl['badge'] : '' ); ?>" />
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row"><label for="tpl_title"><?php esc_html_e( 'Form Heading / Title', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="tpl_title" name="title" class="large-text" value="<?php echo esc_attr( isset( $active_tpl['title'] ) ? $active_tpl['title'] : '' ); ?>" />
								</td>
							</tr>

							<?php if ( 'newsletter' === $active_tpl['layout'] ) : ?>
								<tr>
									<th scope="row"><label for="tpl_placeholder"><?php esc_html_e( 'Email Input Placeholder', 'alify' ); ?></label></th>
									<td>
										<input type="text" id="tpl_placeholder" name="placeholder" class="regular-text" value="<?php echo esc_attr( isset( $active_tpl['placeholder'] ) ? $active_tpl['placeholder'] : '' ); ?>" />
									</td>
								</tr>
							<?php else : ?>
								<tr>
									<th scope="row"><label for="tpl_subtitle"><?php esc_html_e( 'Form Subtitle / Description', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_subtitle" name="subtitle" rows="2" class="large-text"><?php echo esc_textarea( isset( $active_tpl['subtitle'] ) ? $active_tpl['subtitle'] : '' ); ?></textarea>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( empty( $active_tpl['is_builtin'] ) ) : ?>
								<?php
								$cur_fields = isset( $active_tpl['fields'] ) && is_array( $active_tpl['fields'] ) ? $active_tpl['fields'] : array();
								$avail_f    = array(
									'phone'   => __( 'Phone / WhatsApp Number', 'alify' ),
									'company' => __( 'Company / Organization', 'alify' ),
									'service' => __( 'Service / Capability Dropdown', 'alify' ),
									'budget'  => __( 'Estimated Budget Dropdown', 'alify' ),
									'subject' => __( 'Subject Line', 'alify' ),
									'channel' => __( 'Preferred Contact Channel Radios', 'alify' ),
									'message' => __( 'Project / Inquiry Details Textarea', 'alify' ),
									'nda'     => __( 'Mutual NDA Consent Checkbox', 'alify' ),
								);
								?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Fields to Display', 'alify' ); ?></th>
									<td>
										<p class="description" style="margin-bottom:8px;"><?php esc_html_e( 'Full Name & Work Email are always required. Choose additional fields below:', 'alify' ); ?></p>
										<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));gap:6px;">
											<?php foreach ( $avail_f as $fk => $flabel ) : ?>
												<label>
													<input type="checkbox" name="fields[]" value="<?php echo esc_attr( $fk ); ?>" <?php checked( in_array( $fk, $cur_fields, true ) ); ?> />
													<?php echo esc_html( $flabel ); ?>
												</label>
											<?php endforeach; ?>
										</div>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row"><label for="tpl_button_text"><?php esc_html_e( 'Submit Button Label', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="tpl_button_text" name="button_text" class="regular-text" value="<?php echo esc_attr( isset( $active_tpl['button_text'] ) ? $active_tpl['button_text'] : '' ); ?>" />
								</td>
							</tr>

							<?php if ( in_array( $active_tpl['layout'], array( 'reusable_contact', 'contact_page', 'start_project' ), true ) ) : ?>
								<tr>
									<th scope="row"><label for="tpl_footer_note"><?php esc_html_e( 'Confidentiality / Footer Note', 'alify' ); ?></label></th>
									<td>
										<input type="text" id="tpl_footer_note" name="footer_note" class="large-text" value="<?php echo esc_attr( isset( $active_tpl['footer_note'] ) ? $active_tpl['footer_note'] : '' ); ?>" />
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( in_array( $active_tpl['layout'], array( 'contact_page', 'start_project' ), true ) || empty( $active_tpl['is_builtin'] ) ) : ?>
								<tr>
									<th scope="row"><label for="tpl_nda_label"><?php esc_html_e( 'NDA Checkbox Text', 'alify' ); ?></label></th>
									<td>
										<input type="text" id="tpl_nda_label" name="nda_label" class="large-text" value="<?php echo esc_attr( isset( $active_tpl['nda_label'] ) ? $active_tpl['nda_label'] : '' ); ?>" />
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( in_array( $active_tpl['layout'], array( 'reusable_contact', 'contact_page', 'start_project', 'hero_lead' ), true ) ) : ?>
								<tr>
									<th scope="row"><label for="tpl_services_list"><?php esc_html_e( 'Services / Capabilities Options (1 per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_services_list" name="services_list" rows="6" class="large-text"><?php echo esc_textarea( isset( $active_tpl['services_list'] ) ? $active_tpl['services_list'] : '' ); ?></textarea>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( in_array( $active_tpl['layout'], array( 'contact_page', 'start_project' ), true ) || empty( $active_tpl['is_builtin'] ) ) : ?>
								<tr>
									<th scope="row"><label for="tpl_budgets_list"><?php esc_html_e( 'Budget Ranges (1 per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_budgets_list" name="budgets_list" rows="4" class="large-text"><?php echo esc_textarea( isset( $active_tpl['budgets_list'] ) ? $active_tpl['budgets_list'] : '' ); ?></textarea>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( 'start_project' === $active_tpl['layout'] ) : ?>
								<tr>
									<th scope="row"><label for="tpl_timelines_list"><?php esc_html_e( 'Timeline Options (1 per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_timelines_list" name="timelines_list" rows="4" class="large-text"><?php echo esc_textarea( isset( $active_tpl['timelines_list'] ) ? $active_tpl['timelines_list'] : '' ); ?></textarea>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( 'strategy_call' === $active_tpl['layout'] ) : ?>
								<tr>
									<th scope="row"><label for="tpl_tracks_list"><?php esc_html_e( 'Discussion Tracks (`Title | Description` per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_tracks_list" name="tracks_list" rows="4" class="large-text"><?php echo esc_textarea( isset( $active_tpl['tracks_list'] ) ? $active_tpl['tracks_list'] : '' ); ?></textarea>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="tpl_slots_list"><?php esc_html_e( 'Available Time Slots (1 per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_slots_list" name="slots_list" rows="5" class="large-text"><?php echo esc_textarea( isset( $active_tpl['slots_list'] ) ? $active_tpl['slots_list'] : '' ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Updates both the `/book-call/` page and the global Book a Strategy Call modal.', 'alify' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="tpl_channels_list"><?php esc_html_e( 'Meeting Channels (1 per line)', 'alify' ); ?></label></th>
									<td>
										<textarea id="tpl_channels_list" name="channels_list" rows="3" class="large-text"><?php echo esc_textarea( isset( $active_tpl['channels_list'] ) ? $active_tpl['channels_list'] : '' ); ?></textarea>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row"><label for="tpl_success_msg"><?php esc_html_e( 'Success Confirmation Message', 'alify' ); ?></label></th>
								<td>
									<textarea id="tpl_success_msg" name="success_msg" rows="2" class="large-text"><?php echo esc_textarea( isset( $active_tpl['success_msg'] ) ? $active_tpl['success_msg'] : '' ); ?></textarea>
								</td>
							</tr>

							<?php if ( 'newsletter' !== $active_tpl['layout'] ) : ?>
								<tr>
									<th scope="row"><label for="tpl_redirect_url"><?php esc_html_e( 'Custom Redirect URL Override (Optional)', 'alify' ); ?></label></th>
									<td>
										<input type="url" id="tpl_redirect_url" name="redirect_url" class="large-text" placeholder="<?php echo esc_attr( alify_get_active_thank_you_url() ); ?>" value="<?php echo esc_attr( isset( $active_tpl['redirect_url'] ) ? $active_tpl['redirect_url'] : '' ); ?>" />
										<p class="description"><?php esc_html_e( 'Leave blank to use the Global Thank-You Page URL from Forms Settings.', 'alify' ); ?></p>
									</td>
								</tr>
							<?php endif; ?>

							<tr>
								<th scope="row"><label for="tpl_recipient_override"><?php esc_html_e( 'Notification Email Override (Optional)', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="tpl_recipient_override" name="recipient_override" class="regular-text" placeholder="e.g. info@alify.site" value="<?php echo esc_attr( isset( $active_tpl['recipient_override'] ) ? $active_tpl['recipient_override'] : '' ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave blank to use Global Recipient Email(s) from Forms Settings. Supports comma-separated emails.', 'alify' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid #e5e7eb;margin-top:12px;">
						<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Form Template', 'alify' ); ?></button>
						<?php if ( ! empty( $active_tpl['is_builtin'] ) ) : ?>
							<button type="submit" name="alify_reset_template" value="1" class="button" onclick="return confirm('Reset this form template back to ALIFY defaults?');"><?php esc_html_e( 'Reset to Default', 'alify' ); ?></button>
						<?php else : ?>
							<button type="submit" name="alify_delete_custom_template" value="1" class="button button-link-delete" onclick="return confirm('Delete this custom form template permanently?');"><?php esc_html_e( 'Delete Custom Template', 'alify' ); ?></button>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 9. Render WP Admin Screen: `ALIFY Leads & Calls → Forms Settings`.
 */
function alify_render_forms_settings_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s         = alify_get_forms_settings();
	$tpls_url  = add_query_arg( array( 'post_type' => 'alify_lead', 'page' => 'alify-form-templates' ), admin_url( 'edit.php' ) );
	$leads_url = admin_url( 'edit.php?post_type=alify_lead' );
	?>
	<div class="wrap">
		<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
			<div>
				<h1 style="margin:0 0 4px;font-weight:700;"><?php esc_html_e( 'ALIFY Global Forms Settings', 'alify' ); ?></h1>
				<p style="margin:0;color:#4b5563;">
					<?php esc_html_e( 'Configure email notifications, visitor auto-responders, thank-you redirects, anti-spam rules, and CRM webhooks.', 'alify' ); ?>
				</p>
			</div>
			<div style="display:flex;gap:8px;">
				<a href="<?php echo esc_url( $leads_url ); ?>" class="button"><?php esc_html_e( '← Leads Inbox', 'alify' ); ?></a>
				<a href="<?php echo esc_url( $tpls_url ); ?>" class="button button-primary"><?php esc_html_e( 'Edit Form Templates & Shortcodes →', 'alify' ); ?></a>
			</div>
		</div>

		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Forms Settings saved successfully.', 'alify' ); ?></strong></p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['test_mail'] ) && 'sent' === $_GET['test_mail'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Test email handed off to WordPress `wp_mail()` successfully! Check your recipient inbox (or SMTP plugin logs).', 'alify' ); ?></strong></p></div>
		<?php elseif ( isset( $_GET['test_mail'] ) && 'failed' === $_GET['test_mail'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( '`wp_mail()` returned false on this server. Configure an SMTP plugin (e.g. WP Mail SMTP / FluentSMTP) if running on localhost or restricted mail hosting.', 'alify' ); ?></strong></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'alify_save_forms_settings', 'alify_save_forms_settings_nonce' ); ?>

			<div style="display:grid;gap:20px;max-width:980px;">
				<!-- Card 1: Admin Email Notifications -->
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px 24px;">
					<h2 style="margin:0 0 4px;font-size:17px;"><?php esc_html_e( '1. Studio Admin Email Notifications', 'alify' ); ?></h2>
					<p style="margin:0 0 14px;color:#6b7280;font-size:13px;"><?php esc_html_e( 'Every form submission is always saved in ALIFY Leads & Calls. Configure instant email alerts below.', 'alify' ); ?></p>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Admin Email Alerts', 'alify' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="enable_admin_email" value="1" <?php checked( $s['enable_admin_email'], '1' ); ?> />
										<?php esc_html_e( 'Send email notification to studio team on every new inquiry, project brief, or strategy call', 'alify' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="recipient_emails"><?php esc_html_e( 'Primary Recipient Email(s)', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="recipient_emails" name="recipient_emails" class="large-text" value="<?php echo esc_attr( $s['recipient_emails'] ); ?>" />
									<p class="description"><?php esc_html_e( 'Default: info@alify.site — separate multiple email addresses with commas.', 'alify' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="cc_emails"><?php esc_html_e( 'CC / BCC Emails (Optional)', 'alify' ); ?></label></th>
								<td>
									<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
										<input type="text" id="cc_emails" name="cc_emails" class="regular-text" placeholder="CC: team@alify.site" value="<?php echo esc_attr( $s['cc_emails'] ); ?>" />
										<input type="text" id="bcc_emails" name="bcc_emails" class="regular-text" placeholder="BCC: archive@alify.site" value="<?php echo esc_attr( $s['bcc_emails'] ); ?>" />
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="from_name"><?php esc_html_e( 'Sender Name & From Email', 'alify' ); ?></label></th>
								<td>
									<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
										<input type="text" id="from_name" name="from_name" class="regular-text" placeholder="ALIFY Studio" value="<?php echo esc_attr( $s['from_name'] ); ?>" />
										<input type="email" id="from_email" name="from_email" class="regular-text" placeholder="info@alify.site" value="<?php echo esc_attr( $s['from_email'] ); ?>" />
									</div>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="email_subject"><?php esc_html_e( 'Admin Subject Line Template', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="email_subject" name="email_subject" class="large-text" value="<?php echo esc_attr( $s['email_subject'] ); ?>" />
									<p class="description"><?php esc_html_e( 'Available tags: `{ref}`, `{type_label}`, `{name}`, `{email}`, `{capability}`, `{budget}`, `{slot}`', 'alify' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Email Delivery Test', 'alify' ); ?></th>
								<td>
									<button type="submit" name="alify_send_test_email" value="1" class="button button-secondary"><?php esc_html_e( 'Save & Send Test Email Now', 'alify' ); ?></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Card 2: Client Auto-Responder -->
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px 24px;">
					<h2 style="margin:0 0 4px;font-size:17px;"><?php esc_html_e( '2. Client Auto-Responder (Visitor Confirmation Email)', 'alify' ); ?></h2>
					<p style="margin:0 0 14px;color:#6b7280;font-size:13px;"><?php esc_html_e( 'Automatically email a branded confirmation receipt with Reference ID to the client.', 'alify' ); ?></p>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Auto-Responder', 'alify' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="enable_autoresponder" value="1" <?php checked( $s['enable_autoresponder'], '1' ); ?> />
										<?php esc_html_e( 'Send automatic confirmation email to visitors when they submit an inquiry, brief, or strategy call', 'alify' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="autoresponder_subject"><?php esc_html_e( 'Auto-Responder Subject', 'alify' ); ?></label></th>
								<td>
									<input type="text" id="autoresponder_subject" name="autoresponder_subject" class="large-text" value="<?php echo esc_attr( $s['autoresponder_subject'] ); ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="autoresponder_body"><?php esc_html_e( 'Auto-Responder Message Body', 'alify' ); ?></label></th>
								<td>
									<textarea id="autoresponder_body" name="autoresponder_body" rows="7" class="large-text"><?php echo esc_textarea( $s['autoresponder_body'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Available tags: `{name}`, `{email}`, `{ref}`, `{capability}`, `{budget}`, `{slot}`, `{studio_email}`, `{whatsapp}`', 'alify' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Card 3: Submission Redirects & Reference IDs -->
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px 24px;">
					<h2 style="margin:0 0 4px;font-size:17px;"><?php esc_html_e( '3. Confirmation Behavior & Thank-You Redirect', 'alify' ); ?></h2>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="submission_behavior"><?php esc_html_e( 'After Form Submission', 'alify' ); ?></label></th>
								<td>
									<select id="submission_behavior" name="submission_behavior">
										<option value="redirect" <?php selected( $s['submission_behavior'], 'redirect' ); ?>><?php esc_html_e( 'Redirect Visitor to Personalized Thank-You Page (Recommended)', 'alify' ); ?></option>
										<option value="inline" <?php selected( $s['submission_behavior'], 'inline' ); ?>><?php esc_html_e( 'Stay on Same Page & Show Inline Confirmation Banner', 'alify' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="thank_you_url"><?php esc_html_e( 'Global Thank-You Page URL', 'alify' ); ?></label></th>
								<td>
									<input type="url" id="thank_you_url" name="thank_you_url" class="large-text" placeholder="<?php echo esc_attr( alify_page_url( 'thank-you' ) ); ?>" value="<?php echo esc_attr( $s['thank_you_url'] ); ?>" />
									<p class="description"><?php esc_html_e( 'Leave blank to automatically use your WordPress `/thank-you/` page permalink.', 'alify' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Reference Code Prefixes', 'alify' ); ?></th>
								<td>
									<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;max-width:440px;">
										<div>
											<label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;"><?php esc_html_e( 'Inquiry / Project Prefix', 'alify' ); ?></label>
											<input type="text" name="lead_ref_prefix" class="regular-text" value="<?php echo esc_attr( $s['lead_ref_prefix'] ); ?>" />
										</div>
										<div>
											<label style="font-size:12px;font-weight:600;display:block;margin-bottom:3px;"><?php esc_html_e( 'Strategy Call Prefix', 'alify' ); ?></label>
											<input type="text" name="call_ref_prefix" class="regular-text" value="<?php echo esc_attr( $s['call_ref_prefix'] ); ?>" />
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Card 4: Spam Protection & Webhook -->
				<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px 24px;">
					<h2 style="margin:0 0 4px;font-size:17px;"><?php esc_html_e( '4. Anti-Spam Security & External CRM Webhook', 'alify' ); ?></h2>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Honeypot Bot Trap', 'alify' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="enable_honeypot" value="1" <?php checked( $s['enable_honeypot'], '1' ); ?> />
										<?php esc_html_e( 'Enable invisible honeypot spam protection on all ALIFY forms', 'alify' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="rate_limit_per_10m"><?php esc_html_e( 'IP Rate Limit (Per 10 Mins)', 'alify' ); ?></label></th>
								<td>
									<input type="number" id="rate_limit_per_10m" name="rate_limit_per_10m" min="0" max="200" value="<?php echo esc_attr( (string) $s['rate_limit_per_10m'] ); ?>" />
									<span class="description"><?php esc_html_e( 'Max submissions per IP in 10 minutes (`0` = unlimited).', 'alify' ); ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="blocked_terms"><?php esc_html_e( 'Blocked Domains / Spam Words', 'alify' ); ?></label></th>
								<td>
									<textarea id="blocked_terms" name="blocked_terms" rows="2" class="large-text" placeholder="e.g. mailinator.com, crypto-spam"><?php echo esc_textarea( $s['blocked_terms'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Comma-separated or one per line. Matching submissions are silently dropped.', 'alify' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'CRM / Zapier / Slack Webhook', 'alify' ); ?></th>
								<td>
									<label style="display:block;margin-bottom:8px;">
										<input type="checkbox" name="enable_webhook" value="1" <?php checked( $s['enable_webhook'], '1' ); ?> />
										<?php esc_html_e( 'Send real-time JSON POST payload to external Webhook URL on every new lead', 'alify' ); ?>
									</label>
									<input type="url" id="webhook_url" name="webhook_url" class="large-text" placeholder="https://hooks.zapier.com/hooks/catch/..." value="<?php echo esc_attr( $s['webhook_url'] ); ?>" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save All Forms Settings', 'alify' ); ?></button>
				</div>
			</div>
		</form>
	</div>
	<?php
}
