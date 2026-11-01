<?php
/**
 * Run with: wp eval-file wp-content/themes/alify/tools/verify-install.php
 * Prints a concise deployment/import health report without changing site data.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { exit(1); }

$errors=array();
$available_templates=wp_get_theme()->get_page_templates();
$expected_pages=array('home','about','services','industries','case-studies','work','blog','testimonials','faq','pricing','contact','book-call','start-project','privacy-policy','terms-of-service','thank-you');
foreach ($expected_pages as $slug) {
    $page=get_page_by_path($slug,OBJECT,'page');
    if (!$page) { $errors[]='Missing page: ' . $slug; continue; }
    $expected_template='templates/pages/' . $slug . '.php';
    if (get_page_template_slug($page->ID)!==$expected_template) { $errors[]='Wrong page template for ' . $slug . ': expected ' . $expected_template; }
    if (!in_array($expected_template,$available_templates,true)) { $errors[]='WordPress did not discover page template: ' . $expected_template; }
}
$expected_types=array('alify_service'=>8,'alify_industry'=>9,'alify_project'=>6,'alify_case_study'=>6,'post'=>6,'alify_testimonial'=>12,'alify_faq'=>20);
foreach ($expected_types as $type=>$expected) {
    $actual=(int)(new WP_Query(array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids')))->found_posts;
    if ($actual<$expected) { $errors[]=sprintf('%s records: need at least %d, found %d',$type,$expected,$actual); }
}
$field_requirements=array(
    'alify_service'=>array('desc','tags','stats','deliverables','stack'),
    'alify_industry'=>array('desc','tags','stats','challenges','solutions'),
    'alify_project'=>array('desc','chips','outcomes','gallery','case_id'),
    'alify_case_study'=>array('summary','kpis','benchmarks','quote'),
    'post'=>array('lead','sections','tags'),
    'alify_testimonial'=>array('client_name','quote','rating','testimonial_category'),
    'alify_faq'=>array('answer','faq_category'),
);
if (function_exists('get_field')) {
    foreach ($field_requirements as $type=>$required_fields) {
        $populated=false;
        foreach (get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>-1,'fields'=>'ids')) as $post_id) {
            $record_ok=true;
            foreach ($required_fields as $field) { if (!get_field($field,$post_id)) { $record_ok=false; break; } }
            if ($record_ok) { $populated=true; break; }
        }
        if (!$populated) { $errors[]='No ' . $type . ' record has all expected ACF detail fields.'; }
    }
    $about=get_page_by_path('about',OBJECT,'page');
    if ($about && !get_field('page_sections',$about->ID)) { $errors[]='About page ACF sections are empty.'; }
    if (!get_field('home_seo_title','option')) { $errors[]='Homepage SEO title is missing from ALIFY Options.'; }
    if (!get_field('home_sections','option')) { $errors[]='Home page ACF sections are missing.'; }
} else { $errors[]='ACF Pro is not active.'; }
if (!get_option('alify_static_import_completed')) { $errors[]='Automatic static import completion flag is missing.'; }
if (!function_exists('get_field')) { $errors[]='ACF Pro is not active.'; }
elseif (!get_field('home_sections','option')) { $errors[]='Home page ACF sections are missing.'; }
$locations=get_theme_mod('nav_menu_locations',array());
foreach (array('primary','footer','legal') as $location) {
    if (empty($locations[$location])) { $errors[]='Menu location is not assigned: ' . $location; }
}
$service_terms=get_terms(array('taxonomy'=>'alify_service_category','hide_empty'=>false,'fields'=>'slugs'));
if (!is_wp_error($service_terms)) {
    foreach (array('design','engineering','growth') as $slug) { if (!in_array($slug,$service_terms,true)) { $errors[]='Missing service category: ' . $slug; } }
}
if (!get_option('permalink_structure')) { $errors[]='Pretty permalinks are not enabled.'; }

WP_CLI::log('ALIFY installation verification');
WP_CLI::log(sprintf('Pages: %d expected; collections: %d expected.',count($expected_pages),array_sum($expected_types)));
if ($errors) {
    foreach ($errors as $error) { WP_CLI::warning($error); }
    WP_CLI::error(sprintf('Verification found %d issue(s).',count($errors)));
}
WP_CLI::success('All expected pages, content records, ACF options, menus, categories, and permalink settings are present.');
