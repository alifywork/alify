<?php
if (!defined('ABSPATH')) { exit; }

function alify_seo_archive_key() {
    if (is_404()) { return 'not-found'; }
    if (is_tax('alify_service_category')) { return 'service-category'; }
    if (is_tax('alify_industry_category')) { return 'industry-category'; }
    if (is_front_page()) { return 'home'; }
    if (is_home()) { return 'blog'; }
    if (is_post_type_archive()) {
        $type=get_query_var('post_type'); if (is_array($type)) { $type=reset($type); }
        $map=array('alify_service'=>'services','alify_industry'=>'industries','alify_case_study'=>'case-studies','alify_project'=>'work','alify_testimonial'=>'testimonials','alify_faq'=>'faq');
        if (isset($map[$type])) { return $map[$type]; }
    }
    if (is_page()) {
        $map=array('services'=>'services','industries'=>'industries','case-studies'=>'case-studies','work'=>'work','blog'=>'blog','testimonials'=>'testimonials','faq'=>'faq','pricing'=>'pricing');
        $slug=get_post_field('post_name',get_queried_object_id());
        if (isset($map[$slug])) { return $map[$slug]; }
    }
    return '';
}
function alify_seo_value($field, $id = 0) {
    if (!function_exists('get_field')) { return null; }
    if (is_tax(array('alify_service_category','alify_industry_category'))) {
        $term=get_queried_object();
        $value=get_field($field,$term);
        if ($value !== null && $value !== '') { return $value; }
    }
    if ($id) { $value=get_field($field,$id); if ($value !== null && $value !== '') { return $value; } }
    $archive=alify_seo_archive_key();
    if ($archive) {
        $value=get_field(str_replace('-','_',$archive) . '_' . $field,'option');
        if ($value !== null && $value !== '') { return $value; }
    }
    return null;
}
function alify_robots_policy($robots) {
    $noindex=is_404() || (bool)alify_seo_value('seo_noindex',(int)get_queried_object_id());
    if ($noindex) { $robots['noindex']=true; $robots['follow']=true; }
    return $robots;
}
add_filter('wp_robots','alify_robots_policy');

function alify_document_title($title) {
    $custom=alify_seo_value('seo_title',(int)get_queried_object_id());
    return $custom ? wp_strip_all_tags($custom) : $title;
}
add_filter('pre_get_document_title','alify_document_title',20);

function alify_seo_meta() {
    if (is_admin() || is_feed()) { return; }
    $id=(int)get_queried_object_id();
    $title=alify_seo_value('seo_title',$id);
    $description=alify_seo_value('seo_description',$id);
    $canonical=alify_seo_value('seo_canonical',$id);
    $og_title=alify_seo_value('seo_og_title',$id);
    $og_description=alify_seo_value('seo_og_description',$id);
    $og_image=alify_seo_value('seo_og_image',$id);
    if (!$title) { $title=wp_get_document_title(); }
    if (!$description) { $description=$id ? get_the_excerpt($id) : ''; }
    if (!$description) { $description=get_field('default_meta_description','option'); }
    if (!$canonical) {
        if (is_singular()) { $canonical=get_permalink($id); }
        elseif (is_tax()) { $canonical=get_term_link(get_queried_object()); if (is_wp_error($canonical)) { $canonical=''; } }
        elseif (is_post_type_archive()) { $type=get_query_var('post_type'); if (is_array($type)) { $type=reset($type); } $canonical=get_post_type_archive_link($type); }
        elseif (is_home() && get_option('page_for_posts')) { $canonical=get_permalink((int)get_option('page_for_posts')); }
        elseif (is_front_page()) { $canonical=home_url('/'); }
        elseif (is_404()) { $canonical=''; }
        else { $canonical=home_url(add_query_arg(array())); }
    }
    if (!$og_image) { $og_image=get_field('default_social_image','option'); }
    if (is_array($og_image)) { $og_image=!empty($og_image['ID']) ? wp_get_attachment_image_url($og_image['ID'],'full') : ($og_image['url'] ?? ''); }
    $schema_type=alify_seo_value('seo_schema_type',$id) ?: 'WebPage';
    echo "\n<!-- ALIFY SEO -->\n";
    if ($description) { printf("<meta name=\"description\" content=\"%s\">\n",esc_attr(wp_strip_all_tags($description))); }
    if ($canonical) { printf("<link rel=\"canonical\" href=\"%s\">\n",esc_url($canonical)); }
    printf('<meta property="og:type" content="%s">' . "\n",is_singular('post') ? 'article' : 'website');
    printf('<meta property="og:title" content="%s">' . "\n",esc_attr($og_title ?: $title));
    if ($og_description ?: $description) { printf('<meta property="og:description" content="%s">' . "\n",esc_attr(wp_strip_all_tags($og_description ?: $description))); }
    if ($og_image) { printf('<meta property="og:image" content="%s">' . "\n",esc_url($og_image)); }
    printf('<meta name="twitter:card" content="%s">' . "\n",$og_image ? 'summary_large_image' : 'summary');
    printf('<meta name="twitter:title" content="%s">' . "\n",esc_attr($og_title ?: $title));
    if ($og_description ?: $description) { printf('<meta name="twitter:description" content="%s">' . "\n",esc_attr(wp_strip_all_tags($og_description ?: $description))); }
    if ($og_image) { printf('<meta name="twitter:image" content="%s">' . "\n",esc_url($og_image)); }
    $org_name=get_field('organization_name','option') ?: get_bloginfo('name');
    $schema=array('@context'=>'https://schema.org','@type'=>$schema_type,'name'=>wp_strip_all_tags($title),'url'=>$canonical ?: home_url('/'),'isPartOf'=>array('@type'=>'WebSite','name'=>$org_name,'url'=>home_url('/')));
    if ($description) { $schema['description']=wp_strip_all_tags($description); }
    if (is_singular('post')) { $schema['datePublished']=get_the_date(DATE_W3C,$id); $schema['dateModified']=get_the_modified_date(DATE_W3C,$id); $schema['author']=array('@type'=>'Person','name'=>get_the_author_meta('display_name',(int)get_post_field('post_author',$id))); }
    echo '<script type="application/ld+json">' . wp_json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) . "</script>\n";
    $org_type=get_field('organization_schema_type','option') ?: 'Organization';
    $org=array('@context'=>'https://schema.org','@type'=>$org_type,'name'=>$org_name,'url'=>home_url('/'));
    $org_logo=get_field('organization_logo','option'); if (is_array($org_logo) && !empty($org_logo['url'])) { $org['logo']=esc_url_raw($org_logo['url']); }
    $phone=get_field('organization_telephone','option'); if ($phone) { $org['telephone']=$phone; }
    $address=get_field('address','option'); if ($address) { $org['address']=array('@type'=>'PostalAddress','streetAddress'=>wp_strip_all_tags($address)); }
    $sameas=get_field('organization_sameas','option'); if (is_array($sameas)) { $org['sameAs']=array_values(array_filter(array_map(function($row){ return !empty($row['url']) ? esc_url_raw($row['url']) : ''; },$sameas))); }
    echo '<script type="application/ld+json">' . wp_json_encode($org,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) . "</script>\n";
}
add_action('wp_head','alify_seo_meta',2);
