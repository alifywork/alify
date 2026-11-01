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
$home_page=get_page_by_path('home',OBJECT,'page');
$blog_page=get_page_by_path('blog',OBJECT,'page');
if ($home_page && (int)get_option('page_on_front')!== (int)$home_page->ID) { $errors[]='Home page is not configured as the static front page.'; }
if ($blog_page && (int)get_option('page_for_posts')!== (int)$blog_page->ID) { $errors[]='Blog page is not configured as the posts index.'; }
if (!is_file(get_template_directory() . '/front-page.php') || !is_file(get_template_directory() . '/home.php')) { $errors[]='WordPress front-page/posts-index hierarchy templates are missing.'; }
$expected_types=array('alify_service'=>8,'alify_industry'=>9,'alify_project'=>6,'alify_case_study'=>6,'post'=>6,'alify_testimonial'=>12,'alify_faq'=>20);
// These collection routes belong to named ACF page templates, not competing CPT archive rewrites.
foreach (array('alify_service','alify_industry','alify_project','alify_case_study','alify_testimonial','alify_faq') as $type) {
    $object=get_post_type_object($type);
    if ($object && $object->has_archive) { $errors[]='Disable the duplicate CPT archive for ' . $type . '; its collection is a named ACF page.'; }
}
$service_type=get_post_type_object('alify_service');
if ($service_type && $service_type->rewrite!==false) { $errors[]='Service CPT must disable generic rewrites and use category-based routes only.'; }
$service_sample=get_posts(array('post_type'=>'alify_service','post_status'=>'publish','numberposts'=>1));
if ($service_sample) {
    $service_terms=get_the_terms($service_sample[0]->ID,'alify_service_category');
    $service_url=get_permalink($service_sample[0]);
    $service_path=trim((string)parse_url($service_url,PHP_URL_PATH),'/');
    $service_segments=explode('/',$service_path);
    $has_category_path=false;
    if ($service_terms && !is_wp_error($service_terms)) {
        foreach ($service_terms as $term) { if (in_array($term->slug,$service_segments,true)) { $has_category_path=true; break; } }
    }
    if (!$has_category_path || parse_url($service_url,PHP_URL_QUERY) || strpos($service_path,'service-category')!==false) { $errors[]='Service permalinks must contain a service category path and omit the service-category segment.'; }
}
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
$locations=get_theme_mod('nav_menu_locations',array());
foreach (array('primary','footer','legal') as $location) {
    if (empty($locations[$location])) { $errors[]='Menu location is not assigned: ' . $location; }
}
$service_terms=get_terms(array('taxonomy'=>'alify_service_category','hide_empty'=>false,'fields'=>'slugs'));
if (is_wp_error($service_terms)) { $errors[]='Could not read service categories: ' . $service_terms->get_error_message(); }
else { foreach (array('design','engineering','growth') as $slug) { if (!in_array($slug,$service_terms,true)) { $errors[]='Missing service category: ' . $slug; } } }
if (!get_option('permalink_structure')) { $errors[]='Pretty permalinks are not enabled.'; }

WP_CLI::log('ALIFY installation verification');
WP_CLI::log(sprintf('Pages: %d expected; collections: %d expected.',count($expected_pages),array_sum($expected_types)));
if ($errors) {
    foreach ($errors as $error) { WP_CLI::warning($error); }
    WP_CLI::error(sprintf('Verification found %d issue(s).',count($errors)));
}
WP_CLI::success('All expected pages, content records, ACF options, menus, categories, and permalink settings are present.');
