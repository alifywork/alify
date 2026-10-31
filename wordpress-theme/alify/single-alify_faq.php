<?php get_header(); if (have_posts()) : while (have_posts()) : the_post(); ?>
<article <?php post_class('alify-single-content'); ?>><?php if (!alify_sections_contains_hero(get_the_ID())) : ?><header class="alify-section alify-single-hero"><div class="container"><p class="alify-eyebrow"><?php esc_html_e('Frequently asked question','alify'); ?></p><h1><?php the_title(); ?></h1><div class="alify-rich-content"><?php echo wp_kses_post(get_field('answer')); ?></div></div></header><?php endif; ?><?php alify_render_post_content(get_the_ID()); ?></article>
<?php endwhile; endif; get_footer(); ?>
