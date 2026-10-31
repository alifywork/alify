<?php
if (!defined('ABSPATH')) { exit; }

function alify_register_content_types() {
    register_post_type('alify_service', array(
        'labels' => array('name' => __('Services', 'alify'), 'singular_name' => __('Service', 'alify'), 'add_new_item' => __('Add Service', 'alify')),
        'public' => true, 'has_archive' => false, 'hierarchical' => true,
        'rewrite' => array('slug' => '', 'with_front' => false, 'hierarchical' => true),
        'menu_icon' => 'dashicons-admin-tools', 'show_in_rest' => false,
        'supports' => array('title', 'thumbnail', 'page-attributes'),
    ));
    register_taxonomy('alify_service_category', array('alify_service'), array(
        'labels' => array('name' => __('Service Categories', 'alify'), 'singular_name' => __('Service Category', 'alify')),
        'public' => true, 'hierarchical' => true, 'show_admin_column' => true, 'show_in_rest' => false,
        'rewrite' => array('slug' => '', 'with_front' => false, 'hierarchical' => true),
    ));
    register_post_type('alify_industry', array(
        'labels' => array('name' => __('Industries', 'alify'), 'singular_name' => __('Industry', 'alify')),
        'public' => true, 'has_archive' => false, 'hierarchical' => true,
        'rewrite' => array('slug' => 'industries', 'with_front' => false, 'hierarchical' => true),
        'menu_icon' => 'dashicons-building', 'show_in_rest' => false,
        'supports' => array('title', 'thumbnail', 'page-attributes'),
    ));
    register_taxonomy('alify_industry_category', array('alify_industry'), array(
        'labels' => array('name' => __('Industry Categories', 'alify'), 'singular_name' => __('Industry Category', 'alify')),
        'public' => true, 'hierarchical' => true, 'show_admin_column' => true, 'show_in_rest' => false,
        'rewrite' => array('slug' => 'industry', 'with_front' => false, 'hierarchical' => true),
    ));
    $types = array(
        'alify_case_study' => array('Case Studies', 'Case Study', 'case-studies', 'dashicons-chart-area'),
        'alify_project' => array('Projects', 'Project', 'work', 'dashicons-portfolio'),
        'alify_testimonial' => array('Testimonials', 'Testimonial', 'testimonials', 'dashicons-format-quote'),
        'alify_faq' => array('FAQs', 'FAQ', 'faqs', 'dashicons-editor-help'),
    );
    foreach ($types as $type => $config) {
        register_post_type($type, array(
            'labels' => array('name' => __($config[0], 'alify'), 'singular_name' => __($config[1], 'alify'), 'add_new_item' => sprintf(__('Add %s', 'alify'), $config[1])),
            'public' => true, 'has_archive' => $config[2], 'hierarchical' => false,
            'rewrite' => array('slug' => $config[2], 'with_front' => false),
            'menu_icon' => $config[3], 'show_in_rest' => false,
            'supports' => array('title', 'thumbnail', 'page-attributes'),
        ));
    }
}
add_action('init', 'alify_register_content_types');

/** Service URLs omit the implementation taxonomy name and use term / parent / service slugs. */
function alify_service_permalink($url, $post, $leavename, $sample) {
    if ($post->post_type !== 'alify_service') { return $url; }
    $segments = array();
    $terms = get_the_terms($post, 'alify_service_category');
    if ($terms && !is_wp_error($terms)) {
        usort($terms, function ($a, $b) { return count(get_ancestors($b->term_id, 'alify_service_category')) - count(get_ancestors($a->term_id, 'alify_service_category')); });
        $term = reset($terms);
        $term_path = array_reverse(get_ancestors($term->term_id, 'alify_service_category'));
        foreach ($term_path as $ancestor_id) { $ancestor = get_term($ancestor_id, 'alify_service_category'); if ($ancestor && !is_wp_error($ancestor)) { $segments[] = $ancestor->slug; } }
        $segments[] = $term->slug;
    }
    $parents = array_reverse(get_post_ancestors($post));
    foreach ($parents as $parent_id) { $segments[] = get_post_field('post_name', $parent_id); }
    $segments[] = $leavename ? '%postname%' : $post->post_name;
    return home_url('/' . implode('/', array_filter($segments)) . '/');
}
add_filter('post_type_link', 'alify_service_permalink', 10, 4);

/** Service routes are generated from real category paths to avoid hijacking unrelated pages. */
function alify_service_rewrite_rules($rules) {
    $service_rules = array();
    $terms = get_terms(array('taxonomy'=>'alify_service_category','hide_empty'=>false));
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $ancestors = array_reverse(get_ancestors($term->term_id,'alify_service_category'));
            $parts = array();
            foreach ($ancestors as $ancestor_id) { $ancestor=get_term($ancestor_id,'alify_service_category'); if ($ancestor && !is_wp_error($ancestor)) { $parts[]=$ancestor->slug; } }
            $parts[] = $term->slug;
            $prefix = implode('/',array_map('preg_quote',$parts));
            $service_rules['^' . $prefix . '/(.+?)/?$'] = 'index.php?alify_service_path=$matches[1]';
        }
    }
    return $service_rules + $rules;
}
add_filter('rewrite_rules_array','alify_service_rewrite_rules');
function alify_service_query_vars($vars) { $vars[]='alify_service_path'; return $vars; }
add_filter('query_vars','alify_service_query_vars');
function alify_resolve_service_path($query_vars) {
    if (empty($query_vars['alify_service_path'])) { return $query_vars; }
    $service_path = trim(sanitize_text_field(wp_unslash($query_vars['alify_service_path'])),'/');
    $post = get_page_by_path($service_path,OBJECT,'alify_service');
    unset($query_vars['alify_service_path']);
    if ($post) { $query_vars['post_type']='alify_service'; $query_vars['p']=$post->ID; }
    return $query_vars;
}
add_filter('request','alify_resolve_service_path');
function alify_flush_service_rules() { flush_rewrite_rules(false); }
add_action('created_alify_service_category','alify_flush_service_rules');
add_action('edited_alify_service_category','alify_flush_service_rules');
add_action('delete_alify_service_category','alify_flush_service_rules');
