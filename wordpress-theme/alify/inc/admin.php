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

/** A single wp-admin landing page for import health, editable content, and ALIFY settings. */
function alify_register_setup_page() {
    add_theme_page(__('ALIFY Setup','alify'),__('ALIFY Setup','alify'),'manage_options','alify-setup','alify_render_setup_page');
}
add_action('admin_menu','alify_register_setup_page');

function alify_render_setup_page() {
    if (!current_user_can('manage_options')) { return; }
    $page_slugs=array('home','about','services','industries','case-studies','work','blog','testimonials','faq','pricing','contact','book-call','start-project','privacy-policy','terms-of-service','thank-you');
    $archive_fields=array('home'=>'home_sections','services'=>'services_sections','industries'=>'industries_sections','case-studies'=>'case_studies_sections','work'=>'work_sections','blog'=>'blog_sections','testimonials'=>'testimonials_sections','faq'=>'faq_sections','pricing'=>'pricing_sections');
    $missing=array();
    echo '<div class="wrap"><h1>' . esc_html__('ALIFY Setup & Content','alify') . '</h1>';
    echo '<p>' . esc_html__('Use this page to confirm the imported pages and collections exist, open their editors, and access the separate ALIFY ACF settings tabs.','alify') . '</p>';
    echo '<h2>' . esc_html__('Requirements and import status','alify') . '</h2><ul>';
    echo '<li>' . (function_exists('acf_add_options_page') ? '✅ ' . esc_html__('ACF Pro options pages active','alify') : '❌ ' . esc_html__('ACF Pro is missing or inactive','alify')) . '</li>';
    echo '<li>' . (class_exists('DOMDocument') ? '✅ ' . esc_html__('PHP DOM extension active','alify') : '❌ ' . esc_html__('PHP DOM extension is missing','alify')) . '</li>';
    echo '<li>' . (get_option('alify_static_import_completed') ? '✅ ' . esc_html__('Full content import completed','alify') : '⏳ ' . esc_html__('Full content import is pending','alify')) . '</li></ul>';
    $import_error=get_transient('alify_import_last_error');
    if ($import_error) { echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__('Import report:','alify') . '</strong> ' . esc_html($import_error) . '</p></div>'; }
    if (!get_option('alify_static_import_completed')) {
        $retry=wp_nonce_url(admin_url('admin-post.php?action=alify_retry_static_import'),'alify_retry_static_import');
        echo '<p><a class="button button-primary" href="' . esc_url($retry) . '">' . esc_html__('Create pages / Retry full import','alify') . '</a></p>';
    }
    echo '<h2>' . esc_html__('Imported Pages','alify') . '</h2><table class="widefat striped"><thead><tr><th>' . esc_html__('Page','alify') . '</th><th>' . esc_html__('Status','alify') . '</th><th>' . esc_html__('Assigned template','alify') . '</th><th>' . esc_html__('Content','alify') . '</th><th>' . esc_html__('Action','alify') . '</th></tr></thead><tbody>';
    foreach ($page_slugs as $slug) {
        $page=get_page_by_path($slug,OBJECT,'page');
        if (!$page) { $missing[]=$slug; echo '<tr><td>' . esc_html(ucwords(str_replace('-',' ',$slug))) . '</td><td>❌ ' . esc_html__('Missing','alify') . '</td><td>—</td><td>—</td><td>—</td></tr>'; continue; }
        $field=$archive_fields[$slug] ?? 'page_sections';
        $field_target=isset($archive_fields[$slug]) ? 'option' : $page->ID;
        $content_ready=function_exists('get_field') && (bool)get_field($field,$field_target);
        $status=$page->post_status==='publish' ? __('Published','alify') : ucfirst($page->post_status);
        $edit=admin_url('post.php?post=' . (int)$page->ID . '&action=edit');
        $template=get_page_template_slug($page->ID) ?: 'default';
        echo '<tr><td>' . esc_html($page->post_title) . ' <code>/' . esc_html($slug) . '/</code></td><td>' . esc_html($status) . '</td><td><code>' . esc_html($template) . '</code></td><td>' . ($content_ready ? '✅ ' . esc_html__('ACF sections present','alify') : '⚠️ ' . esc_html__('No ACF sections detected','alify')) . '</td><td><a href="' . esc_url($edit) . '">' . esc_html__('Edit','alify') . '</a></td></tr>';
    }
    echo '</tbody></table>';
    $collections=array('alify_service'=>8,'alify_industry'=>9,'alify_project'=>6,'alify_case_study'=>6,'post'=>6,'alify_testimonial'=>12,'alify_faq'=>20);
    echo '<h2>' . esc_html__('Content Collections','alify') . '</h2><table class="widefat striped"><thead><tr><th>' . esc_html__('Collection','alify') . '</th><th>' . esc_html__('Found / expected','alify') . '</th><th>' . esc_html__('Action','alify') . '</th></tr></thead><tbody>';
    foreach ($collections as $type=>$expected) {
        $count=(int)(new WP_Query(array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids')))->found_posts;
        $object=get_post_type_object($type); $label=$object ? $object->labels->name : $type;
        $url=admin_url('edit.php?post_type=' . $type);
        echo '<tr><td>' . esc_html($label) . '</td><td>' . esc_html((string)$count) . ' / ' . esc_html((string)$expected) . '</td><td><a href="' . esc_url($url) . '">' . esc_html__('View entries','alify') . '</a></td></tr>';
    }
    echo '</tbody></table>';
    $form_count=(int)(new WP_Query(array('post_type'=>'alify_submission','post_status'=>'private','posts_per_page'=>1,'fields'=>'ids')))->found_posts;
    echo '<h2>' . esc_html__('Form Entries','alify') . '</h2><p>' . esc_html(sprintf(__('%d saved submissions','alify'),$form_count)) . ' — <a href="' . esc_url(admin_url('edit.php?post_type=alify_submission')) . '">' . esc_html__('Open Form Entries','alify') . '</a></p>';
    if (function_exists('acf_add_options_page')) {
        echo '<h2>' . esc_html__('ALIFY Options & Content Tabs','alify') . '</h2><p>';
        $option_links=array('alify-theme'=>__('Brand & Header','alify'),'alify-forms'=>__('Forms','alify'),'alify-seo'=>__('Global SEO','alify'),'alify-not-found'=>__('404 Page','alify'),'alify-home'=>__('Home Page','alify'),'alify-about'=>__('About Page','alify'),'alify-services'=>__('Services Archive','alify'),'alify-industries'=>__('Industries Archive','alify'),'alify-case-studies'=>__('Case Studies Archive','alify'),'alify-work'=>__('Work Archive','alify'),'alify-blog'=>__('Blog Archive','alify'),'alify-testimonials'=>__('Testimonials Archive','alify'),'alify-faq'=>__('FAQ Archive','alify'),'alify-pricing'=>__('Pricing Page','alify'),'alify-service-category'=>__('Service Category','alify'),'alify-industry-category'=>__('Industry Category','alify'));
        foreach ($option_links as $slug=>$label) {
            echo '<a class="button" style="margin:0 6px 6px 0" href="' . esc_url(admin_url('admin.php?page=' . $slug)) . '">' . esc_html($label) . '</a> ';
        }
        echo '</p>';
    }
    if ($missing) { echo '<p class="description">' . esc_html(sprintf(__('Missing pages: %s','alify'),implode(', ',$missing))) . '</p>'; }
    echo '</div>';
}

function alify_theme_setup_notice() {
    if (!get_option('alify_theme_setup_notice_dismissed')) {
        add_option('alify_theme_setup_notice_dismissed', 0);
    }
}
add_action('after_switch_theme','alify_theme_setup_notice');
