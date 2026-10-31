<?php
/**
 * Run with: wp eval-file wp-content/themes/alify/tools/verify-install.php
 * Prints a concise deployment/import health report without changing site data.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { exit(1); }

$errors=array();
$expected_pages=array('home','about','services','industries','case-studies','work','blog','testimonials','faq','pricing','contact','book-call','start-project','privacy-policy','terms-of-service','thank-you');
foreach ($expected_pages as $slug) {
    if (!get_page_by_path($slug,OBJECT,'page')) { $errors[]='Missing page: ' . $slug; }
}
$expected_types=array('alify_service'=>8,'alify_industry'=>9,'alify_project'=>6,'alify_case_study'=>6,'post'=>6,'alify_testimonial'=>12,'alify_faq'=>20);
foreach ($expected_types as $type=>$expected) {
    $actual=(int)(new WP_Query(array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids')))->found_posts;
    if ($actual<$expected) { $errors[]=sprintf('%s records: need at least %d, found %d',$type,$expected,$actual); }
}
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
