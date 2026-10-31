<?php get_header(); if (have_posts()) : while (have_posts()) : the_post(); ?>
<article <?php post_class('alify-single-content'); ?>><?php if (!alify_sections_contains_hero(get_the_ID())) : ?><header class="alify-section alify-single-hero"><div class="container"><p class="alify-eyebrow"><?php echo esc_html(get_the_date()); ?></p><h1><?php the_title(); ?></h1><?php if (has_post_thumbnail()) { the_post_thumbnail('full',array('loading'=>'eager')); } ?></div></header><?php endif; ?><?php alify_render_post_content(get_the_ID()); ?></article>
<?php endwhile; endif; get_footer(); ?>
