<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="booking-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('booking','success_message',get_field('form_success_message','option') ?: __('Your call request has been received.','alify'))); ?></p><?php endif; ?>
  <form class="booking-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="booking"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_booking','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php echo esc_html(alify_form_setting('booking','name_label',__('Your name','alify'))); ?><input name="name" type="text" autocomplete="name"<?php echo alify_form_setting('booking','require_name',true) ? ' required' : ''; ?>></label>
    <label><?php echo esc_html(alify_form_setting('booking','email_label',__('Work email','alify'))); ?><input name="email" type="email" autocomplete="email" required></label>
    <?php if (alify_form_setting('booking','show_company',true)) : ?><label><?php echo esc_html(alify_form_setting('booking','company_label',__('Company','alify'))); ?><input name="company" type="text" autocomplete="organization"></label><?php endif; ?>
    <?php if (alify_form_setting('booking','show_phone',false)) : ?><label><?php echo esc_html(alify_form_setting('booking','phone_label',__('Phone','alify'))); ?><input name="phone" type="tel" autocomplete="tel"></label><?php endif; ?>
    <?php if (alify_form_setting('booking','show_meeting_date',true)) : ?><label><?php echo esc_html(alify_form_setting('booking','meeting_date_label',__('Preferred date','alify'))); ?><input name="meeting_date" type="date"></label><?php endif; ?>
    <?php if (alify_form_setting('booking','show_message',true)) : ?><label><?php echo esc_html(alify_form_setting('booking','message_label',__('What would you like to discuss?','alify'))); ?><textarea name="message" rows="4"<?php echo alify_form_setting('booking','require_message',false) ? ' required' : ''; ?>></textarea></label><?php endif; ?>
    <?php alify_render_extra_form_fields('booking'); ?>
    <button class="btn btn-primary" type="submit"><?php echo esc_html(alify_form_setting('booking','submit_label',__('Request a strategy call','alify'))); ?></button>
  </form>
</div>
