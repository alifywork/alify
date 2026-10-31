<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="newsletter-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('newsletter','success_message',get_field('form_success_message','option') ?: __('You are on the list. Thank you!','alify'))); ?></p><?php endif; ?>
  <form class="newsletter-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="newsletter"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_newsletter','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php echo esc_html(alify_form_setting('newsletter','email_label',__('Email address','alify'))); ?><input name="email" type="email" autocomplete="email" required></label>
    <?php alify_render_extra_form_fields('newsletter'); ?>
    <button class="btn btn-primary" type="submit"><?php echo esc_html(alify_form_setting('newsletter','submit_label',__('Subscribe','alify'))); ?></button>
  </form>
</div>
