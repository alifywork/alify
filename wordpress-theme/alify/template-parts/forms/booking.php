<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="booking-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('booking','success_message',get_field('form_success_message','option') ?: __('Your call request has been received.','alify'))); ?></p><?php endif; ?>
  <form class="booking-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="booking"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_booking','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php esc_html_e('Your name','alify'); ?><input name="name" type="text" autocomplete="name" required></label>
    <label><?php esc_html_e('Work email','alify'); ?><input name="email" type="email" autocomplete="email" required></label>
    <label><?php esc_html_e('Company','alify'); ?><input name="company" type="text" autocomplete="organization"></label>
    <label><?php esc_html_e('Preferred date','alify'); ?><input name="meeting_date" type="date"></label>
    <label><?php esc_html_e('What would you like to discuss?','alify'); ?><textarea name="message" rows="4"></textarea></label>
    <button class="btn btn-primary" type="submit"><?php esc_html_e('Request a strategy call','alify'); ?></button>
  </form>
</div>
