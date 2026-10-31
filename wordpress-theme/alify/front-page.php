<?php get_header(); ?>
<?php
$home_id = get_option('page_on_front');
if (function_exists('have_rows') && have_rows('home_sections','option')) {
  alify_render_sections('home_sections','option');
} elseif ($home_id && function_exists('have_rows') && have_rows('page_sections',$home_id)) {
  alify_render_sections('page_sections',$home_id);
} else { ?><section class="alify-section alify-section-hero"><div class="container"><p class="alify-eyebrow">ALIFY Studio</p><h1><?php echo esc_html(get_bloginfo('description') ?: get_bloginfo('name')); ?></h1><p><?php esc_html_e('Add your homepage sections in ALIFY Options → Home Page.','alify'); ?></p></div></section><?php }
?>
<?php get_footer(); ?>
