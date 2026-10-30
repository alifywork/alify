<?php
if (!defined('ABSPATH')) { exit; }

function alify_hide_editor_for_managed_types() {
    remove_post_type_support('page','editor');
    remove_post_type_support('alify_service','editor');
    remove_post_type_support('alify_industry','editor');
    remove_post_type_support('alify_case_study','editor');
    remove_post_type_support('alify_project','editor');
    remove_post_type_support('alify_testimonial','editor');
    remove_post_type_support('alify_faq','editor');
    remove_post_type_support('post','editor');
    add_post_type_support('post','excerpt');
}
add_action('init','alify_hide_editor_for_managed_types',20);

function alify_admin_notices() {
    if (!function_exists('acf_add_options_page')) {
        echo '<div class="notice notice-warning"><p><strong>ALIFY theme:</strong> ACF Pro is required for editable page sections, theme options, archive content, and SEO controls. Install and activate ACF Pro to complete the theme setup.</p></div>';
    }
}
add_action('admin_notices','alify_admin_notices');

function alify_theme_setup_notice() {
    if (!get_option('alify_theme_setup_notice_dismissed')) {
        add_option('alify_theme_setup_notice_dismissed', 0);
    }
}
add_action('after_switch_theme','alify_theme_setup_notice');
