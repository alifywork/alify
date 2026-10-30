<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="project-form-wrap alify-form-wrap">
  <?php if (!empty($_GET['form']) && sanitize_key(wp_unslash($_GET['form'])) === 'success') : ?><p class="form-success" role="status"><?php echo esc_html(alify_form_setting('project','success_message',get_field('form_success_message','option') ?: __('Thanks — your project brief is with our team.','alify'))); ?></p><?php endif; ?>
  <form class="project-form alify-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="alify_submit"><input type="hidden" name="alify_form_type" value="project"><input type="hidden" name="source_page" value="<?php echo esc_url(get_permalink()); ?>">
    <?php wp_nonce_field('alify_form_project','alify_nonce'); ?>
    <p class="alify-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label></p>
    <label><?php echo esc_html(alify_form_setting('project','name_label',__('Your name','alify'))); ?><input name="name" type="text" autocomplete="name"<?php echo alify_form_setting('project','require_name',true) ? ' required' : ''; ?>></label>
    <label><?php echo esc_html(alify_form_setting('project','email_label',__('Work email','alify'))); ?><input name="email" type="email" autocomplete="email" required></label>
    <?php if (alify_form_setting('project','show_company',true)) : ?><label><?php echo esc_html(alify_form_setting('project','company_label',__('Company','alify'))); ?><input name="company" type="text" autocomplete="organization"></label><?php endif; ?>
    <?php if (alify_form_setting('project','show_phone',false)) : ?><label><?php echo esc_html(alify_form_setting('project','phone_label',__('Phone','alify'))); ?><input name="phone" type="tel" autocomplete="tel"></label><?php endif; ?>
    <?php if (alify_form_setting('project','show_service_interest',true)) : ?><label><?php echo esc_html(alify_form_setting('project','service_interest_label',__('Service you are interested in','alify'))); ?><input name="service_interest" type="text"></label><?php endif; ?>
    <?php if (alify_form_setting('project','show_budget',true)) : $budget_options=alify_form_setting('project','budget_options',"$5k–$10k\n$10k–$25k\n$25k–$50k\n$50k+"); ?><label><?php echo esc_html(alify_form_setting('project','budget_label',__('Estimated budget','alify'))); ?><select name="budget"><option value=""><?php esc_html_e('Select a range','alify'); ?></option><?php foreach (preg_split('/\r\n|\r|\n/',(string)$budget_options) as $option) : $option=trim($option); if ($option!=='') : ?><option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option><?php endif; endforeach; ?></select></label><?php endif; ?>
    <?php if (alify_form_setting('project','show_timeline',true)) : ?><label><?php echo esc_html(alify_form_setting('project','timeline_label',__('Target timeline','alify'))); ?><input name="timeline" type="text"></label><?php endif; ?>
    <?php if (alify_form_setting('project','show_message',true)) : ?><label><?php echo esc_html(alify_form_setting('project','message_label',__('Project overview','alify'))); ?><textarea name="message" rows="5"<?php echo alify_form_setting('project','require_message',true) ? ' required' : ''; ?>></textarea></label><?php endif; ?>
    <?php alify_render_extra_form_fields('project'); ?>
    <button class="btn btn-primary" type="submit"><?php echo esc_html(alify_form_setting('project','submit_label',__('Send project brief','alify'))); ?></button>
  </form>
</div>
