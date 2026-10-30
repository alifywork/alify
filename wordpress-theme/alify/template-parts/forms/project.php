<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="project-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('project','success_message',get_field('form_success_message','option') ?: __('Thanks — your project brief is with our team.','alify'))); ?></p><?php endif; ?>
  <form class="project-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="project"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_project','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php esc_html_e('Your name','alify'); ?><input name="name" type="text" autocomplete="name" required></label>
    <label><?php esc_html_e('Work email','alify'); ?><input name="email" type="email" autocomplete="email" required></label>
    <label><?php esc_html_e('Company','alify'); ?><input name="company" type="text" autocomplete="organization"></label>
    <label><?php esc_html_e('Service you are interested in','alify'); ?><input name="service_interest" type="text"></label>
    <label><?php esc_html_e('Estimated budget','alify'); ?><select name="budget"><option value=""><?php esc_html_e('Select a range','alify'); ?></option><option>$5k–$10k</option><option>$10k–$25k</option><option>$25k–$50k</option><option>$50k+</option></select></label>
    <label><?php esc_html_e('Target timeline','alify'); ?><input name="timeline" type="text"></label>
    <label><?php esc_html_e('Project overview','alify'); ?><textarea name="message" rows="5" required></textarea></label>
    <button class="btn btn-primary" type="submit"><?php esc_html_e('Send project brief','alify'); ?></button>
  </form>
</div>
