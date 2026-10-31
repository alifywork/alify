<?php
if (!defined('ABSPATH')) { exit; }

class Alify_Desktop_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output,$depth=0,$args=null) { $output .= '<div class="dropdown-panel" role="menu"><ul class="dropdown-list">'; }
    public function end_lvl(&$output,$depth=0,$args=null) { $output .= '</ul></div>'; }
    public function start_el(&$output,$item,$depth=0,$args=null,$id=0) {
        $has=!empty($args->has_children);
        $classes=$depth===0 ? 'header-nav-item' : 'dropdown-list-item';
        if ($depth===0 && $has) { $classes .= ' has-dropdown'; }
        $output .= '<li class="' . esc_attr($classes) . '">';
        $class=$depth===0 ? 'header-nav-link' : 'dropdown-item-link';
        $current=!empty($item->current) || !empty($item->current_item_parent) || !empty($item->current_item_ancestor);
        if ($current) { $class .= ' is-active'; }
        $output .= '<a class="' . esc_attr($class) . '" href="' . esc_url($item->url) . '"' . ($current?' aria-current="page"':'') . ($has?' aria-expanded="false" aria-haspopup="true"':'') . '>';
        $output .= '<span>' . esc_html($item->title) . '</span>';
        if ($depth===0 && $has) { $output .= '<i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>'; }
        $output .= '</a>';
    }
    public function end_el(&$output,$item,$depth=0,$args=null) { $output .= '</li>'; }
}
class Alify_Drawer_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output,$depth=0,$args=null) { $output .= '<div class="drawer-submenu-wrap"><ul class="drawer-submenu">'; }
    public function end_lvl(&$output,$depth=0,$args=null) { $output .= '</ul></div>'; }
    public function start_el(&$output,$item,$depth=0,$args=null,$id=0) {
        $has=!empty($args->has_children);
        $link_class=$depth===0 ? 'drawer-nav-link' : 'drawer-submenu-link';
        $current=!empty($item->current) || !empty($item->current_item_parent) || !empty($item->current_item_ancestor);
        if ($current) { $link_class .= ' is-active'; }
        $output .= '<li class="drawer-nav-item' . ($has?' has-submenu':'') . '"><a class="' . esc_attr($link_class) . '" href="' . esc_url($item->url) . '"' . ($current?' aria-current="page"':'') . '>' . esc_html($item->title) . '</a>';
        if ($depth===0 && $has) { $output .= '<button type="button" class="drawer-dropdown-toggle" aria-label="' . esc_attr(sprintf(__('Toggle %s submenu','alify'),$item->title)) . '" aria-expanded="false"><i class="fa-solid fa-chevron-down drawer-chevron" aria-hidden="true"></i></button>'; }
    }
    public function end_el(&$output,$item,$depth=0,$args=null) { $output .= '</li>'; }
}

function alify_setup() {
    load_theme_textdomain('alify', ALIFY_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('custom-logo', array('height' => 72, 'width' => 240, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('align-wide');
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'alify'),
        'footer' => __('Footer Navigation', 'alify'),
        'legal' => __('Legal Navigation', 'alify'),
    ));
}
add_action('after_setup_theme', 'alify_setup');

/** Enqueue project-local assets; no third-party frontend dependencies are fetched remotely. */
function alify_enqueue_assets() {
    $base = ALIFY_URI . '/assets/';
    wp_enqueue_style('alify-bootstrap', $base . 'css/bootstrap-css/bootstrap.min.css', array(), ALIFY_VERSION);
    wp_enqueue_style('alify-icons', $base . 'icons/css/all.min.css', array(), ALIFY_VERSION);
    $styles = array(
        'global-component-css/header.css', 'global-component-css/footer.css',
        'global-component-css/ui-polish.css', 'global-component-css/page-loader.css',
        'reusable-component-css/about-studio.css', 'reusable-component-css/blog.css',
        'reusable-component-css/book-call.css', 'reusable-component-css/case-studies.css',
        'reusable-component-css/faq.css', 'reusable-component-css/forms.css',
        'reusable-component-css/industries.css', 'reusable-component-css/inner-hero.css',
        'reusable-component-css/pricing.css', 'reusable-component-css/projects.css',
        'reusable-component-css/services.css', 'reusable-component-css/single-pages.css',
        'reusable-component-css/testimonials.css', 'reusable-component-css/tool-stack.css', 'style.css'
    );
    foreach ($styles as $i => $file) {
        wp_enqueue_style('alify-style-' . $i, $base . 'css/' . $file, array('alify-bootstrap'), ALIFY_VERSION);
    }
    wp_enqueue_style('alify-theme', ALIFY_URI . '/style.css', array('alify-style-' . (count($styles) - 1)), ALIFY_VERSION);
    wp_enqueue_style('alify-wp-sections', ALIFY_URI . '/assets/css/alify-wordpress.css', array('alify-theme'), ALIFY_VERSION);
    wp_enqueue_script('alify-bootstrap', $base . 'js/bootstrap-js/bootstrap.bundle.min.js', array(), ALIFY_VERSION, true);
    wp_enqueue_script('alify-gsap', $base . 'gsap/gsap.min.js', array(), ALIFY_VERSION, true);
    wp_enqueue_script('alify-scrolltrigger', $base . 'gsap/ScrollTrigger.min.js', array('alify-gsap'), ALIFY_VERSION, true);
    $scripts = array(
        'global-component-js/header.js', 'global-component-js/footer.js', 'global-component-js/ui-polish.js',
        'reusable-component-js/blog.js', 'reusable-component-js/faq.js', 'reusable-component-js/forms.js',
        'reusable-component-js/testimonials.js', 'reusable-component-js/industries.js',
        'reusable-component-js/services.js', 'reusable-component-js/projects.js',
        'reusable-component-js/case-studies.js', 'reusable-component-js/about-studio.js',
        'main.js', 'global-component-js/page-loader.js'
    );
    foreach ($scripts as $i => $file) {
        wp_enqueue_script('alify-script-' . $i, $base . 'js/' . $file, array('alify-bootstrap', 'alify-gsap', 'alify-scrolltrigger'), ALIFY_VERSION, true);
    }
    $project_urls=array(); $project_catalog=array();
    foreach (get_posts(array('post_type'=>'alify_project','numberposts'=>-1,'post_status'=>'publish')) as $project) {
        $project_urls[$project->post_name]=get_permalink($project);
        if (function_exists('alify_catalog_frontend_data')) { $project_catalog[$project->post_name]=alify_catalog_frontend_data($project->ID); }
    }
    wp_localize_script('alify-script-0','AlifyProjectUrls',$project_urls);
    wp_localize_script('alify-script-0','AlifyProjectCatalog',$project_catalog);
    $case_urls=array();
    foreach (get_posts(array('post_type'=>'alify_case_study','numberposts'=>-1,'post_status'=>'publish')) as $case) { $case_urls[$case->post_name]=get_permalink($case); }
    wp_localize_script('alify-script-0','AlifyCaseStudyUrls',$case_urls);
    $blog_urls=array(); $blog_catalog=array();
    foreach (get_posts(array('post_type'=>'post','numberposts'=>-1,'post_status'=>'publish')) as $post) {
        $blog_urls[$post->post_name]=get_permalink($post);
        if (function_exists('alify_catalog_frontend_data')) { $blog_catalog[$post->post_name]=alify_catalog_frontend_data($post->ID); }
    }
    wp_localize_script('alify-script-0','AlifyBlogUrls',$blog_urls);
    wp_localize_script('alify-script-0','AlifyBlogCatalog',$blog_catalog);
    $detail_type=get_post_type();
    if (is_singular(array('alify_service','alify_industry','alify_project','alify_case_study','post'))) {
        $type_map=array('alify_service'=>'service','alify_industry'=>'industry','alify_project'=>'project','alify_case_study'=>'case','post'=>'post');
        $current_id=get_queried_object_id();
        wp_localize_script('alify-script-0','AlifyCurrentItem',array('type'=>$type_map[$detail_type] ?? '', 'slug'=>get_post_field('post_name',$current_id)));
        wp_localize_script('alify-script-0','AlifyCurrentItemData',function_exists('alify_catalog_frontend_data') ? alify_catalog_frontend_data($current_id) : array());
        wp_enqueue_script('alify-single-detail',$base . 'js/reusable-component-js/single-pages.js',array('alify-bootstrap','alify-gsap','alify-scrolltrigger'),ALIFY_VERSION,true);
    }
}
add_action('wp_enqueue_scripts', 'alify_enqueue_assets');

/** Gutenberg is intentionally disabled; page editing uses ACF Pro fields only. */
add_filter('use_block_editor_for_post', '__return_false', 100);
add_filter('use_block_editor_for_post_type', '__return_false', 100);
add_filter('gutenberg_can_edit_post_type', '__return_false', 100);
add_action('wp_enqueue_scripts', function () { wp_dequeue_style('wp-block-library'); wp_dequeue_style('wp-block-library-theme'); }, 100);

function alify_allow_safe_inline_svg($tags,$context) {
    if ($context!=='post') { return $tags; }
    $attrs=array('xmlns'=>true,'viewbox'=>true,'width'=>true,'height'=>true,'fill'=>true,'stroke'=>true,'stroke-width'=>true,'stroke-linecap'=>true,'stroke-linejoin'=>true,'d'=>true,'points'=>true,'cx'=>true,'cy'=>true,'r'=>true,'rx'=>true,'ry'=>true,'x'=>true,'y'=>true,'x1'=>true,'y1'=>true,'x2'=>true,'y2'=>true,'transform'=>true,'class'=>true,'id'=>true,'role'=>true,'aria-hidden'=>true,'focusable'=>true,'preserveaspectratio'=>true,'offset'=>true,'stop-color'=>true,'stop-opacity'=>true,'fill-opacity'=>true,'opacity'=>true,'href'=>true,'xlink:href'=>true);
    foreach (array('svg','g','path','circle','rect','ellipse','line','polyline','polygon','defs','lineargradient','radialgradient','stop','use','symbol','mask','clippath','text','tspan') as $tag) { $tags[$tag]=$attrs; }
    return $tags;
}
add_filter('wp_kses_allowed_html','alify_allow_safe_inline_svg',10,2);
