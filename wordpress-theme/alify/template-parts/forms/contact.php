<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="contact-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('contact','success_message',get_field('form_success_message','option') ?: __('Thanks — your message has been received.','alify'))); ?></p><?php endif; ?>
  <form class="contact-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="contact"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_contact','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php echo esc_html(alify_form_setting('contact','name_label',__('Your name','alify'))); ?><input name="name" type="text" autocomplete="name"<?php echo alify_form_setting('contact','require_name',true) ? ' required' : ''; ?>></label>
    <label><?php echo esc_html(alify_form_setting('contact','email_label',__('Email address','alify'))); ?><input name="email" type="email" autocomplete="email" required></label>
    <?php if (alify_form_setting('contact','show_company',true)) : ?><label><?php echo esc_html(alify_form_setting('contact','company_label',__('Company','alify'))); ?><input name="company" type="text" autocomplete="organization"></label><?php endif; ?>
    <?php if (alify_form_setting('contact','show_phone',false)) : ?><label><?php echo esc_html(alify_form_setting('contact','phone_label',__('Phone','alify'))); ?><input name="phone" type="tel" autocomplete="tel"></label><?php endif; ?>
    <?php if (alify_form_setting('contact','show_message',true)) : ?><label><?php echo esc_html(alify_form_setting('contact','message_label',__('How can we help?','alify'))); ?><textarea name="message" rows="5"<?php echo alify_form_setting('contact','require_message',true) ? ' required' : ''; ?>></textarea></label><?php endif; ?>
    <?php alify_render_extra_form_fields('contact'); ?>
    <button class="btn btn-primary" type="submit"><?php echo esc_html(alify_form_setting('contact','submit_label',__('Send inquiry','alify'))); ?></button>
  </form>
</div>
