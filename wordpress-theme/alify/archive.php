<?php get_header();
$post_type=get_query_var('post_type'); if (is_array($post_type)) { $post_type=reset($post_type); }
$map=array('alify_case_study'=>array('case_studies','case-studies','case_studies'),'alify_project'=>array('work','work','projects'),'alify_testimonial'=>array('testimonials','testimonials','testimonials'),'alify_faq'=>array('faq','faqs','faqs'));
$config=$map[$post_type] ?? array('blog','blog','posts'); $option_key=str_replace('-','_',$config[0]);
$title=get_field($option_key . '_archive_title','option') ?: post_type_archive_title('',false);
$intro=get_field($option_key . '_archive_intro','option');
$archive_rows=get_field($option_key . '_sections','option');
?>
<?php if (empty($archive_rows)) : ?><section class="alify-section alify-archive-intro"><div class="container"><p class="alify-eyebrow">ALIFY Studio</p><h1><?php echo esc_html($title ?: __('Insights','alify')); ?></h1><?php echo wp_kses_post($intro); ?></div></section><?php endif; ?>
<?php alify_render_sections($option_key . '_sections','option'); if (get_field($option_key . '_show_dynamic_listing','option')) : ?>
<section class="alify-section"><div class="container"><div class="row g-4 alify-card-grid">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?><div class="col-md-6 col-lg-4"><article class="alify-card"><?php if (has_post_thumbnail()) { the_post_thumbnail('medium',array('loading'=>'lazy')); } ?><div class="alify-card-body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(get_field('summary') ?: get_the_excerpt()); ?></p><a class="alify-card-link" href="<?php the_permalink(); ?>"><?php esc_html_e('Explore','alify'); ?></a></div></article></div><?php endwhile; else : ?><p><?php esc_html_e('No content published yet.','alify'); ?></p><?php endif; ?>
</div><?php the_posts_pagination(); ?></div></section><?php endif; ?>
<?php get_footer(); ?>
