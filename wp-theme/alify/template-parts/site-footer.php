<?php
/**
 * Global Site Footer Partial
 * Powered dynamically by WordPress `wp_nav_menu()` (`footer_nav`, `footer_services`, `footer_legal`)
 * + Live Customizer Contact, Social & Brand Settings.
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alify_email         = get_theme_mod( 'alify_email', 'info@alify.site' );
$alify_whatsapp      = get_theme_mod( 'alify_whatsapp', '+92 301 9328094' );
$alify_whatsapp_url  = get_theme_mod( 'alify_whatsapp_url', 'https://wa.me/923019328094' );
$alify_hq            = get_theme_mod( 'alify_hq', 'Karachi, PK — Global Delivery' );
$alify_contact_label = get_theme_mod( 'alify_contact_btn_text', 'Contact Us' );
$alify_cta_label     = get_theme_mod( 'alify_cta_text', 'Start a Project' );
$alify_col1_heading  = get_theme_mod( 'alify_footer_col1_title', 'Navigation' );
$alify_col2_heading  = get_theme_mod( 'alify_footer_col2_title', 'Services' );

$alify_socials = array(
	array(
		'url'   => get_theme_mod( 'alify_twitter', 'https://x.com/alifywork' ),
		'icon'  => 'fa-brands fa-x-twitter',
		'label' => 'X Twitter',
	),
	array(
		'url'   => get_theme_mod( 'alify_instagram', 'https://www.instagram.com/alifywork/' ),
		'icon'  => 'fa-brands fa-instagram',
		'label' => 'Instagram',
	),
	array(
		'url'   => get_theme_mod( 'alify_facebook', 'https://www.facebook.com/alifywork/' ),
		'icon'  => 'fa-brands fa-facebook-f',
		'label' => 'Facebook',
	),
	array(
		'url'   => get_theme_mod( 'alify_tiktok', 'https://www.tiktok.com/@alifywork' ),
		'icon'  => 'fa-brands fa-tiktok',
		'label' => 'TikTok',
	),
	array(
		'url'   => get_theme_mod( 'alify_linkedin', '' ),
		'icon'  => 'fa-brands fa-linkedin-in',
		'label' => 'LinkedIn',
	),
	array(
		'url'   => get_theme_mod( 'alify_behance', '' ),
		'icon'  => 'fa-brands fa-behance',
		'label' => 'Behance',
	),
	array(
		'url'   => get_theme_mod( 'alify_dribbble', '' ),
		'icon'  => 'fa-brands fa-dribbble',
		'label' => 'Dribbble',
	),
);
?>
<footer class="site-footer">
	<div class="container">
		<!-- Pre-Footer Primary CTA Banner -->
		<div class="footer-cta-banner">
			<span class="footer-cta-watermark" aria-hidden="true">△</span>
			<div class="row footer-cta-row">
				<div class="col-12 col-lg-7">
					<span class="footer-cta-eyebrow">
						<span class="footer-cta-eyebrow-dot" aria-hidden="true">△</span>
						<?php esc_html_e( 'Ready to Scale Your Brand?', 'alify' ); ?>
					</span>
					<h2 class="footer-cta-title">
						<?php esc_html_e( 'Let’s Build Something Remarkable Together.', 'alify' ); ?>
					</h2>
					<p class="footer-cta-desc">
						<?php esc_html_e( 'Partner with ALIFY to craft high-converting digital products, iconic brand identities, and scalable web experiences.', 'alify' ); ?>
					</p>
				</div>
				<div class="col-12 col-lg-5 footer-cta-actions-col">
					<div class="footer-cta-actions">
						<a href="<?php echo esc_url( alify_page_url( 'start-project' ) ); ?>" class="btn-start-project btn-start-light">
							<span class="btn-start-pill"><?php echo esc_html( $alify_cta_label ); ?></span>
							<span class="btn-start-circle" aria-hidden="true">
								<i class="fa-solid fa-arrow-up btn-start-arrow"></i>
							</span>
						</a>
						<a href="<?php echo esc_url( alify_page_url( 'contact' ) ); ?>" class="btn-footer-outline"><?php echo esc_html( $alify_contact_label ); ?></a>
					</div>
				</div>
			</div>
		</div>

		<!-- Main Footer Navigation Columns -->
		<div class="row footer-main-row">
			<!-- Column 1: Brand & Newsletter -->
			<div class="col-12 col-lg-4">
				<?php alify_render_brand_logo( 'footer-brand-logo', 'footer-brand-mark' ); ?>
				<p class="footer-brand-desc">
					<?php esc_html_e( 'A design-first digital studio engineering bespoke websites, brand systems, and scalable products for ambitious companies worldwide.', 'alify' ); ?>
				</p>

				<?php
				if ( function_exists( 'alify_render_form_template_html' ) ) {
					echo alify_render_form_template_html( 'newsletter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					?>
					<label for="footerNewsletterInput" class="footer-newsletter-label">
						<?php esc_html_e( 'Subscribe to Studio Insights', 'alify' ); ?>
					</label>
					<form class="footer-newsletter-form" id="footerNewsletterForm" novalidate>
						<input
							type="email"
							id="footerNewsletterInput"
							class="footer-newsletter-input"
							placeholder="<?php esc_attr_e( 'Enter your work email', 'alify' ); ?>"
							aria-label="<?php esc_attr_e( 'Work email address', 'alify' ); ?>"
							required
						/>
						<button
							type="submit"
							class="footer-newsletter-btn"
							aria-label="<?php esc_attr_e( 'Subscribe', 'alify' ); ?>"
						>
							<i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
						</button>
					</form>
					<p class="footer-newsletter-msg" id="footerNewsletterMsg" aria-live="polite"></p>
					<?php
				}
				?>

				<div class="footer-socials" aria-label="<?php esc_attr_e( 'Social Media Links', 'alify' ); ?>">
					<?php foreach ( $alify_socials as $soc ) : ?>
						<?php if ( ! empty( $soc['url'] ) ) : ?>
							<a href="<?php echo esc_url( $soc['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-link" aria-label="<?php echo esc_attr( $soc['label'] ); ?>">
								<i class="<?php echo esc_attr( $soc['icon'] ); ?>" aria-hidden="true"></i>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Column 2: Quick Links (`footer_nav` Menu Location) -->
			<div class="col-6 col-md-4 col-lg-2">
				<h3 class="footer-col-title"><?php echo esc_html( $alify_col1_heading ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_nav',
						'container'      => false,
						'menu_class'     => 'footer-link-list',
						'depth'          => 1,
						'walker'         => new ALIFY_Footer_Link_Walker(),
						'fallback_cb'    => 'alify_fallback_footer_nav',
					)
				);
				?>
			</div>

			<!-- Column 3: Services (`footer_services` Menu Location) -->
			<div class="col-6 col-md-4 col-lg-3">
				<h3 class="footer-col-title"><?php echo esc_html( $alify_col2_heading ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_services',
						'container'      => false,
						'menu_class'     => 'footer-link-list',
						'depth'          => 1,
						'walker'         => new ALIFY_Footer_Link_Walker(),
						'fallback_cb'    => 'alify_fallback_footer_services',
					)
				);
				?>
			</div>

			<!-- Column 4: Contact Info -->
			<div class="col-12 col-md-4 col-lg-3">
				<h3 class="footer-col-title"><?php esc_html_e( 'Get in Touch', 'alify' ); ?></h3>
				<ul class="footer-contact-list">
					<li class="footer-contact-item">
						<span class="footer-contact-icon">
							<i class="fa-solid fa-envelope" aria-hidden="true"></i>
						</span>
						<div class="footer-contact-meta">
							<span class="footer-contact-label"><?php esc_html_e( 'Email Us', 'alify' ); ?></span>
							<a href="mailto:<?php echo esc_attr( sanitize_email( $alify_email ) ); ?>" class="footer-contact-value"><?php echo esc_html( $alify_email ); ?></a>
						</div>
					</li>
					<li class="footer-contact-item">
						<span class="footer-contact-icon">
							<i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
						</span>
						<div class="footer-contact-meta">
							<span class="footer-contact-label"><?php esc_html_e( 'WhatsApp', 'alify' ); ?></span>
							<a href="<?php echo esc_url( $alify_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="footer-contact-value"><?php echo esc_html( $alify_whatsapp ); ?></a>
						</div>
					</li>
					<li class="footer-contact-item">
						<span class="footer-contact-icon">
							<i class="fa-solid fa-location-dot" aria-hidden="true"></i>
						</span>
						<div class="footer-contact-meta">
							<span class="footer-contact-label"><?php esc_html_e( 'Headquarters', 'alify' ); ?></span>
							<span class="footer-contact-value"><?php echo esc_html( $alify_hq ); ?></span>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<!-- Bottom Bar -->
		<div class="footer-bottom">
			<div class="row footer-bottom-row">
				<div class="col-12 col-md-6">
					<p class="footer-copyright">
						&copy; <span id="footerYear"><?php echo esc_html( wp_date( 'Y' ) ); ?></span> ALIFY Studio. <?php esc_html_e( 'All rights reserved.', 'alify' ); ?>
					</p>
				</div>
				<div class="col-12 col-md-6">
					<div class="footer-bottom-right">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer_legal',
								'container'      => false,
								'items_wrap'     => '%3$s',
								'depth'          => 1,
								'walker'         => new ALIFY_Footer_Legal_Walker(),
								'fallback_cb'    => 'alify_fallback_footer_legal',
							)
						);
						?>
						<button
							type="button"
							id="backToTopBtn"
							class="back-to-top-btn"
							aria-label="<?php esc_attr_e( 'Scroll back to top', 'alify' ); ?>"
						>
							<i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>
