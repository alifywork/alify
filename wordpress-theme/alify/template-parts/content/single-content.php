<?php
if (!defined('ABSPATH')) { exit; }
$type=get_post_type();
$summary=get_field('summary');
?>
<article <?php post_class('alify-single-content'); ?>><?php if (!alify_sections_contains_hero(get_the_ID())) : ?><header class="alify-section alify-single-hero"><div class="container">
  <?php if ($eyebrow=get_field('eyebrow')) : ?><p class="alify-eyebrow"><?php echo esc_html($eyebrow); ?></p><?php else : ?><p class="alify-eyebrow"><?php echo esc_html(get_post_type_object($type)->labels->singular_name ?? __('ALIFY','alify')); ?></p><?php endif; ?>
  <h1><?php the_title(); ?></h1><?php if ($summary) : ?><p class="alify-lead"><?php echo esc_html($summary); ?></p><?php endif; ?>
  <?php if (has_post_thumbnail()) : ?><div class="alify-featured-image"><?php the_post_thumbnail('full',array('loading'=>'eager')); ?></div><?php endif; ?>
  <?php if (get_field('key_result')) : ?><p class="alify-key-result"><strong><?php esc_html_e('Key outcome:','alify'); ?></strong> <?php echo esc_html(get_field('key_result')); ?></p><?php endif; ?>
</div></header><?php endif; ?>
<?php alify_render_post_content(get_the_ID()); ?>
<?php if (get_field('quote')) : ?><section class="alify-section"><div class="container"><figure class="alify-quote"><blockquote><?php echo esc_html(get_field('quote')); ?></blockquote><figcaption><?php echo esc_html(get_field('client_name')); ?><?php if (get_field('client_role')) : ?> — <?php echo esc_html(get_field('client_role')); ?><?php endif; ?></figcaption></figure></div></section><?php endif; ?>
<?php foreach (array('related_services'=>'services','related_industries'=>'industries','related_work'=>'case_studies','related_pages'=>'related') as $field=>$label) : $related=get_field($field); if ($related) : ?><section class="alify-section alify-related-section"><div class="container"><h2><?php echo esc_html(sprintf(__('Related %s','alify'),$label)); ?></h2><div class="row g-4"><?php foreach ($related as $item) : ?><div class="col-md-6 col-lg-4"><article class="alify-card"><div class="alify-card-body"><h3><a href="<?php echo esc_url(get_permalink($item)); ?>"><?php echo esc_html(get_the_title($item)); ?></a></h3><p><?php echo esc_html(get_field('summary',$item->ID) ?: get_the_excerpt($item)); ?></p></div></article></div><?php endforeach; ?></div></div></section><?php endif; endforeach; ?>
</article>
