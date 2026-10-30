<?php if (!defined('ABSPATH')) { exit; } ?>
</main>
<?php if (!alify_page_component_hidden('footer')) : $email=alify_option('contact_email'); ?>
<footer class="site-footer"><div class="container"><div class="row gy-4 footer-main">
  <div class="col-lg-5"><a href="<?php echo esc_url(home_url('/')); ?>" class="brand-logo"><span class="brand-logo-mark" aria-hidden="true">△</span><?php echo esc_html(alify_option('brand_name','ALIFY')); ?></a><p><?php echo esc_html(alify_option('footer_description')); ?></p><?php if ($email) : ?><a href="mailto:<?php echo esc_attr(antispambot($email)); ?>"><?php echo esc_html(antispambot($email)); ?></a><?php endif; ?></div>
  <div class="col-lg-4"><nav aria-label="<?php esc_attr_e('Footer Navigation','alify'); ?>"><?php wp_nav_menu(array('theme_location'=>'footer','container'=>false,'menu_class'=>'footer-nav-list','fallback_cb'=>false)); ?></nav></div>
  <div class="col-lg-3"><nav aria-label="<?php esc_attr_e('Legal Navigation','alify'); ?>"><?php wp_nav_menu(array('theme_location'=>'legal','container'=>false,'menu_class'=>'footer-legal-list','fallback_cb'=>false)); ?></nav></div>
</div><div class="footer-bottom"><span><?php echo esc_html(get_field('copyright_text','option') ?: sprintf(__('© %s ALIFY. All rights reserved.','alify'),gmdate('Y'))); ?></span><?php if (have_rows('social_links','option')) : ?><div class="footer-social-links"><?php while (have_rows('social_links','option')) : the_row(); ?><a href="<?php echo esc_url(get_sub_field('url')); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(get_sub_field('label')); ?></a><?php endwhile; ?></div><?php endif; ?></div></div></footer>
<?php endif; ?>
<?php wp_footer(); ?></body></html>
