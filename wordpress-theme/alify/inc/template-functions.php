<?php
if (!defined('ABSPATH')) { exit; }

function alify_option($name, $fallback = '') { $value = function_exists('get_field') ? get_field($name, 'option') : null; return ($value !== null && $value !== '') ? $value : $fallback; }
function alify_page_component_hidden($component) {
    $ids=alify_option($component . '_hidden_pages',array());
    if (is_array($ids) && is_singular() && in_array((int)get_queried_object_id(),array_map('intval',$ids),true)) { return true; }
    if (function_exists('alify_seo_archive_key')) {
        $archive=alify_seo_archive_key();
        if ($archive && function_exists('get_field')) { return (bool)get_field(str_replace('-','_',$archive) . '_hide_' . $component,'option'); }
    }
    return false;
}
function alify_link_markup($link, $class = 'btn btn-primary') {
    if (!$link || empty($link['url'])) { return ''; }
    $target = !empty($link['target']) ? $link['target'] : '_self';
    return '<a class="' . esc_attr($class) . '" href="' . esc_url($link['url']) . '" target="' . esc_attr($target) . '"' . ($target === '_blank' ? ' rel="noopener noreferrer"' : '') . '>' . esc_html($link['title'] ?: __('Learn more', 'alify')) . '</a>';
}
function alify_section_class($name) { return 'alify-section alify-section-' . sanitize_html_class($name); }

function alify_render_sections($field_name = 'page_sections', $post_id = false) {
    if (!function_exists('have_rows') || !have_rows($field_name, $post_id)) { return; }
    while (have_rows($field_name, $post_id)) {
        the_row();
        if (get_sub_field('hide_section')) { continue; }
        $layout = get_row_layout();
        if ($layout === 'legacy_html') {
            $legacy_class = trim((string)get_sub_field('legacy_classes'));
            $legacy_id = get_sub_field('legacy_id');
            $legacy_label = get_sub_field('legacy_aria_label');
            $legacy_labelledby = get_sub_field('legacy_aria_labelledby');
            echo '<section class="alify-imported-section ' . esc_attr($legacy_class) . '"' . ($legacy_id ? ' id="' . esc_attr($legacy_id) . '"' : '') . ($legacy_label ? ' aria-label="' . esc_attr($legacy_label) . '"' : '') . ($legacy_labelledby ? ' aria-labelledby="' . esc_attr($legacy_labelledby) . '"' : '') . '>' . do_shortcode(wp_kses_post(get_sub_field('html'))) . '</section>';
            continue;
        }
        $classes = alify_section_class($layout);
        echo '<section class="' . esc_attr($classes) . '"><div class="container">';
        switch ($layout) {
            case 'hero':
                $image = get_sub_field('image');
                echo '<div class="alify-hero-content">';
                if ($eyebrow = get_sub_field('eyebrow')) { echo '<p class="alify-eyebrow">' . esc_html($eyebrow) . '</p>'; }
                printf('<h1>%s</h1>', wp_kses_post(nl2br(esc_html(get_sub_field('heading')))));
                if ($copy = get_sub_field('copy')) { echo '<div class="alify-lead">' . wp_kses_post($copy) . '</div>'; }
                echo '<div class="alify-actions">' . alify_link_markup(get_sub_field('primary_link')) . alify_link_markup(get_sub_field('secondary_link'), 'btn btn-outline-primary') . '</div></div>';
                if ($image && !empty($image['ID'])) { echo wp_get_attachment_image($image['ID'], 'full', false, array('class'=>'alify-hero-image','loading'=>'eager')); }
                break;
            case 'rich_text':
                if ($eyebrow=get_sub_field('eyebrow')) { echo '<p class="alify-eyebrow">' . esc_html($eyebrow) . '</p>'; }
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                echo '<div class="alify-rich-content">' . wp_kses_post(get_sub_field('content')) . '</div>';
                break;
            case 'image_text':
                $image=get_sub_field('image'); $side=get_sub_field('image_side');
                echo '<div class="row align-items-center g-5 alify-image-text ' . ($side==='left'?'flex-row-reverse':'') . '"><div class="col-lg-6">';
                if ($eyebrow=get_sub_field('eyebrow')) { echo '<p class="alify-eyebrow">' . esc_html($eyebrow) . '</p>'; }
                echo '<h2>' . esc_html(get_sub_field('heading')) . '</h2><div>' . wp_kses_post(get_sub_field('copy')) . '</div>' . alify_link_markup(get_sub_field('link')) . '</div><div class="col-lg-6">';
                if ($image && !empty($image['ID'])) { echo wp_get_attachment_image($image['ID'],'large',false,array('class'=>'img-fluid','alt'=>get_sub_field('image_alt') ?: $image['alt'],'loading'=>'lazy')); }
                echo '</div></div>';
                break;
            case 'card_grid':
                if ($eyebrow=get_sub_field('eyebrow')) { echo '<p class="alify-eyebrow">' . esc_html($eyebrow) . '</p>'; }
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                if ($intro=get_sub_field('intro')) { echo '<p class="alify-section-intro">' . esc_html($intro) . '</p>'; }
                alify_render_cards(get_sub_field('source') ?: 'manual', get_sub_field('items'));
                break;
            case 'stats':
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                echo '<div class="row g-4 alify-stat-grid">';
                if (have_rows('items')) { while (have_rows('items')) { the_row(); echo '<div class="col-6 col-lg-3"><div class="alify-stat"><strong>' . esc_html(get_sub_field('value')) . '</strong><span>' . esc_html(get_sub_field('label')) . '</span><small>' . esc_html(get_sub_field('note')) . '</small></div></div>'; } }
                echo '</div>';
                break;
            case 'faq':
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                echo '<div class="alify-faq-list">';
                if (have_rows('items')) { while (have_rows('items')) { the_row(); echo '<details class="alify-faq-item"><summary>' . esc_html(get_sub_field('question')) . '</summary><div>' . wp_kses_post(get_sub_field('answer')) . '</div></details>'; } }
                echo '</div>';
                break;
            case 'testimonial':
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                $reviews=get_sub_field('items');
                if ($reviews) { echo '<div class="row g-4">'; foreach ($reviews as $review) { echo '<div class="col-md-6"><figure class="alify-quote"><blockquote>' . esc_html(get_field('quote',$review->ID) ?: get_the_excerpt($review)) . '</blockquote><figcaption><strong>' . esc_html(get_field('client_name',$review->ID) ?: get_the_title($review)) . '</strong> ' . esc_html(get_field('client_role',$review->ID)) . '</figcaption></figure></div>'; } echo '</div>'; }
                break;
            case 'cta':
                if ($eyebrow=get_sub_field('eyebrow')) { echo '<p class="alify-eyebrow">' . esc_html($eyebrow) . '</p>'; }
                echo '<div class="alify-cta-inner"><h2>' . esc_html(get_sub_field('heading')) . '</h2><p>' . esc_html(get_sub_field('copy')) . '</p>' . alify_link_markup(get_sub_field('link')) . '</div>';
                break;
            case 'form':
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                echo do_shortcode('[alify_form type="' . esc_attr(get_sub_field('form_type') ?: 'contact') . '"]');
                break;
            case 'pricing_table':
                if ($heading=get_sub_field('heading')) { echo '<h2>' . esc_html($heading) . '</h2>'; }
                $plans=get_sub_field('plans'); $rows=get_sub_field('rows');
                if ($plans) { echo '<div class="pricing-matrix-wrap" role="region" aria-label="' . esc_attr__('Pricing comparison table','alify') . '" tabindex="0"><table class="pricing-matrix-table"><thead><tr><th>' . esc_html__('Included','alify') . '</th>'; foreach ($plans as $plan) { echo '<th>' . esc_html($plan['name']) . '<br><small>' . esc_html($plan['price']) . '</small></th>'; } echo '</tr></thead><tbody>'; foreach ((array)$rows as $row) { echo '<tr><th>' . esc_html($row['feature']) . '</th>'; $values=array_map('trim',explode(',',(string)$row['values'])); foreach ($plans as $index=>$plan) { echo '<td>' . esc_html($values[$index] ?? '—') . '</td>'; } echo '</tr>'; } echo '</tbody></table></div>'; }
                break;
        }
        echo '</div></section>';
    }
}

function alify_render_archive_cards($post_type,$per_page=12) {
    $query=new WP_Query(array('post_type'=>$post_type,'post_status'=>'publish','posts_per_page'=>(int)$per_page,'paged'=>max(1,(int)get_query_var('paged')),'orderby'=>'menu_order date','order'=>'DESC'));
    echo '<div class="row g-4 alify-card-grid">';
    if ($query->have_posts()) { while ($query->have_posts()) { $query->the_post(); echo '<div class="col-md-6 col-lg-4"><article class="alify-card">'; if (has_post_thumbnail()) { the_post_thumbnail('medium',array('loading'=>'lazy')); } echo '<div class="alify-card-body"><h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2><p>' . esc_html(get_field('summary') ?: get_the_excerpt()) . '</p><a class="alify-card-link" href="' . esc_url(get_permalink()) . '">' . esc_html__('Explore','alify') . '</a></div></article></div>'; } }
    else { echo '<p>' . esc_html__('No content published yet.','alify') . '</p>'; }
    echo '</div>';
    echo '<div class="alify-pagination">' . wp_kses_post(paginate_links(array('total'=>$query->max_num_pages,'current'=>max(1,(int)get_query_var('paged')),'type'=>'list'))) . '</div>';
    wp_reset_postdata();
}

function alify_render_cards($source = 'manual', $manual = array()) {
    $post_types=array('services'=>'alify_service','industries'=>'alify_industry','case_studies'=>'alify_case_study','projects'=>'alify_project','posts'=>'post','testimonials'=>'alify_testimonial','faqs'=>'alify_faq');
    $items=array();
    if (isset($post_types[$source])) { $items=get_posts(array('post_type'=>$post_types[$source],'numberposts'=>-1,'orderby'=>'menu_order date','order'=>'ASC','post_status'=>'publish')); }
    echo '<div class="row g-4 alify-card-grid">';
    if ($source==='manual' && is_array($manual)) {
        foreach ($manual as $item) {
            $related=$item['related'] ?? 0; $url=$related ? get_permalink($related) : (!empty($item['link']['url']) ? $item['link']['url'] : '#');
            echo '<div class="col-md-6 col-lg-4"><article class="alify-card">';
            if (!empty($item['image']['ID'])) { echo wp_get_attachment_image($item['image']['ID'],'medium',false,array('loading'=>'lazy')); }
            echo '<div class="alify-card-body"><h3><a href="' . esc_url($url) . '">' . esc_html($item['title']) . '</a></h3><p>' . esc_html($item['summary']) . '</p><a href="' . esc_url($url) . '" class="alify-card-link">' . esc_html__('Explore','alify') . '</a></div></article></div>';
        }
    } else {
        foreach ($items as $item) { echo '<div class="col-md-6 col-lg-4"><article class="alify-card">'; if (has_post_thumbnail($item)) { echo get_the_post_thumbnail($item,'medium',array('loading'=>'lazy')); } echo '<div class="alify-card-body"><h3><a href="' . esc_url(get_permalink($item)) . '">' . esc_html(get_the_title($item)) . '</a></h3><p>' . esc_html(get_the_excerpt($item)) . '</p><a class="alify-card-link" href="' . esc_url(get_permalink($item)) . '">' . esc_html__('Explore','alify') . '</a></div></article></div>'; }
    }
    echo '</div>';
}

function alify_sections_contains_hero($post_id) {
    if (!function_exists('get_field')) { return false; }
    $rows=get_field('page_sections',$post_id);
    if (!is_array($rows)) { return false; }
    foreach ($rows as $row) {
        if (!empty($row['hide_section'])) { continue; }
        if (($row['acf_fc_layout'] ?? '')==='hero') { return true; }
        if (($row['acf_fc_layout'] ?? '')==='legacy_html' && preg_match('/(^|\\s)[^\\s]*hero[^\\s]*(\\s|$)/i',(string)($row['legacy_classes'] ?? ''))) { return true; }
    }
    return false;
}

function alify_render_post_content($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    alify_render_sections('page_sections', $post_id);
    $legacy = get_post_field('post_content', $post_id);
    if ($legacy) { echo '<div class="container alify-editor-content">' . apply_filters('the_content', $legacy) . '</div>'; }
}
