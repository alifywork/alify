<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post();
$archive_options = array('services'=>'services','industries'=>'industries','case-studies'=>'case-studies','work'=>'work','blog'=>'blog','testimonials'=>'testimonials','faq'=>'faq','pricing'=>'pricing');
$slug = get_post_field('post_name',get_the_ID());
if (isset($archive_options[$slug])) {
  $option_slug=$archive_options[$slug];
  $option_key=str_replace('-','_',$option_slug);
  $title=get_field($option_key . '_archive_title','option') ?: get_the_title();
  $intro=get_field($option_key . '_archive_intro','option');
  $archive_rows=get_field($option_key . '_sections','option');
  if (empty($archive_rows)) { echo '<section class="alify-section alify-archive-intro"><div class="container"><p class="alify-eyebrow">' . esc_html__('ALIFY Studio','alify') . '</p><h1>' . esc_html($title) . '</h1>' . wp_kses_post($intro) . '</div></section>'; }
  alify_render_sections($option_key . '_sections','option');
  $show_dynamic=(bool)get_field($option_key . '_show_dynamic_listing','option');
  $listing_types=array('services'=>'alify_service','industries'=>'alify_industry','case-studies'=>'alify_case_study','work'=>'alify_project','blog'=>'post','testimonials'=>'alify_testimonial','faq'=>'alify_faq');
  if ($show_dynamic && isset($listing_types[$slug])) { echo '<section class="alify-section"><div class="container">'; alify_render_archive_cards($listing_types[$slug]); echo '</div></section>'; }
  elseif ($slug==='pricing') { alify_render_post_content(get_the_ID()); }
} else {
  $sections=function_exists('have_rows') && have_rows('page_sections',get_the_ID());
  if ($sections) { alify_render_sections(); }
  else { echo '<section class="alify-section alify-section-hero"><div class="container"><p class="alify-eyebrow">' . esc_html__('ALIFY Studio','alify') . '</p><h1>' . esc_html(get_the_title()) . '</h1></div></section>'; }
  if (trim(get_the_content())) { echo '<div class="container alify-editor-content">' . apply_filters('the_content',get_the_content()) . '</div>'; }
}
endwhile; endif; ?>
<?php get_footer(); ?>
