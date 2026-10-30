<?php
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) { return; }

class Alify_Static_Import_Command extends WP_CLI_Command {
    private $source;
    private $content_map=array();
    private $archive_map=array('home'=>'home','404'=>'not-found','services'=>'services','industries'=>'industries','case-studies'=>'case-studies','work'=>'work','blog'=>'blog','testimonials'=>'testimonials','faq'=>'faq','pricing'=>'pricing');

    /** Import the repository's existing standalone HTML content into ACF rows.
     *
     * ## OPTIONS
     * [--source=<path>]
     * : Directory containing the original static HTML files and assets. Defaults to the repository root.
     *
     * ## EXAMPLES
     *     wp alify import-static --source=/path/to/alify
     */
    public function __invoke($args,$assoc_args) {
        if (!class_exists('DOMDocument') || !function_exists('update_field')) { WP_CLI::error('DOM and ACF Pro are required for import.'); }
        $source=isset($assoc_args['source']) ? realpath($assoc_args['source']) : realpath(dirname(ALIFY_DIR,2));
        if (!$source || !is_dir($source) || !file_exists($source . '/index.html')) { WP_CLI::error('Cannot find index.html in source directory.'); }
        $this->source=$source;
        $this->create_pages();
        $this->create_content_items();
        $this->migrate_sections();
        $this->set_site_pages();
        WP_CLI::success('Static pages and original sections have been imported as ACF flexible-content rows. Review each page, SEO panel, menus, and internal links before going live.');
    }

    private function read_document($file) {
        $html=file_get_contents($file); if ($html===false) { return null; }
        $dom=new DOMDocument('1.0','UTF-8'); $previous=libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html,LIBXML_NONET|LIBXML_NOWARNING|LIBXML_NOERROR);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return $dom;
    }
    private function page_title($file) {
        $dom=$this->read_document($file); if (!$dom) { return basename($file,'.html'); }
        $nodes=$dom->getElementsByTagName('h1'); if ($nodes->length) { return trim($nodes->item(0)->textContent); }
        $nodes=$dom->getElementsByTagName('title'); return $nodes->length ? trim($nodes->item(0)->textContent) : basename($file,'.html');
    }
    private function migrate_seo($file,$post_id=0,$option_key='') {
        $dom=$this->read_document($file); if (!$dom) { return; }
        $title=''; $description=''; $canonical=''; $og_title=''; $og_description='';
        foreach ($dom->getElementsByTagName('title') as $node) { $title=trim($node->textContent); break; }
        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $name=strtolower($meta->getAttribute('name')); $property=strtolower($meta->getAttribute('property')); $content=trim($meta->getAttribute('content'));
            if ($name==='description') { $description=$content; }
            if ($property==='og:title') { $og_title=$content; }
            if ($property==='og:description') { $og_description=$content; }
        }
        foreach ($dom->getElementsByTagName('link') as $link) { if (strtolower($link->getAttribute('rel'))==='canonical') { $canonical=$link->getAttribute('href'); } }
        $prefix=$option_key ? str_replace('-','_',$option_key) . '_' : '';
        $target=$post_id ? $post_id : 'option';
        foreach (array('seo_title'=>$title,'seo_description'=>$description,'seo_canonical'=>$canonical,'seo_og_title'=>$og_title,'seo_og_description'=>$og_description) as $name=>$value) { if ($value!=='') { update_field($prefix . $name,$value,$target); } }
    }
    private function upsert($type,$title,$slug,$status='publish') {
        $existing=get_page_by_path($slug,OBJECT,$type);
        $data=array('post_type'=>$type,'post_title'=>wp_strip_all_tags($title),'post_name'=>$slug,'post_status'=>$status);
        if ($existing) { $data['ID']=$existing->ID; $id=wp_update_post($data,true); }
        else { $id=wp_insert_post($data,true); }
        if (is_wp_error($id)) { WP_CLI::warning($id->get_error_message()); return 0; }
        return (int)$id;
    }
    private function create_pages() {
        $files=array('index'=>'home','about'=>'about','services'=>'services','industries'=>'industries','case-studies'=>'case-studies','work'=>'work','blog'=>'blog','testimonials'=>'testimonials','faq'=>'faq','pricing'=>'pricing','contact'=>'contact','book-call'=>'book-call','start-project'=>'start-project','privacy-policy'=>'privacy-policy','terms-of-service'=>'terms-of-service','thank-you'=>'thank-you');
        foreach ($files as $file=>$slug) {
            $path=$this->source . '/' . $file . '.html'; if (!file_exists($path)) { continue; }
            $id=$this->upsert('page',$this->page_title($path),$slug);
            if ($id && in_array($slug,$this->archive_map,true)) { $this->migrate_seo($path,0,$slug); $this->content_map[$file . '.html']=home_url('/' . ($slug==='home'?'':$slug . '/') ); }
            elseif ($id) { $this->migrate_seo($path,$id); $this->content_map[$file . '.html']=get_permalink($id); }
        }
    }
    private function create_content_items() {
        $items=array(
            array('file'=>'service-single','type'=>'alify_service','slug'=>'web-design-development','term'=>'Design'),
            array('file'=>'industry-single','type'=>'alify_industry','slug'=>'fintech-digital-banking'),
            array('file'=>'case-study-single','type'=>'alify_case_study','slug'=>'finpulse-ai'),
            array('file'=>'work-single','type'=>'alify_project','slug'=>'finpulse-ai-platform'),
            array('file'=>'blog-single','type'=>'post','slug'=>'scalable-design-systems'),
        );
        foreach ($items as $item) {
            $path=$this->source . '/' . $item['file'] . '.html'; if (!file_exists($path)) { continue; }
            $id=$this->upsert($item['type'],$this->page_title($path),$item['slug']);
            if (!$id) { continue; }
            $this->migrate_seo($path,$id);
            if (!empty($item['term'])) {
                $term=term_exists($item['term'],'alify_service_category');
                if (!$term) { $term=wp_insert_term($item['term'],'alify_service_category',array('slug'=>'design')); }
                if (!is_wp_error($term)) { wp_set_object_terms($id,array((int)(is_array($term)?$term['term_id']:$term)),'alify_service_category'); }
            }
            $this->content_map[$item['file'] . '.html']=get_permalink($id);
        }
    }
    private function section_nodes($main) {
        $sections=array();
        foreach ($main->getElementsByTagName('section') as $section) {
            $parent=$section->parentNode; $nested=false;
            while ($parent && $parent!==$main) { if ($parent instanceof DOMElement && strtolower($parent->tagName)==='section') { $nested=true; break; } $parent=$parent->parentNode; }
            if (!$nested) { $sections[]=$section; }
        }
        return $sections;
    }
    private $element_counter=0;
    private function make_token() { $this->element_counter++; return '@@ALIFY_' . str_pad((string)$this->element_counter,5,'0',STR_PAD_LEFT) . '@@'; }
    private function asset_url($url) {
        if (!$url || preg_match('~^(?:https?:)?//|^(?:data:|blob:)~i',$url)) { return $url; }
        $path=parse_url($url,PHP_URL_PATH); $path=rawurldecode((string)$path); $path=preg_replace('~^(?:\\./|\\.\\./)+~','',$path); $path=ltrim($path,'/');
        if (strpos($path,'assets/')===0) { return ALIFY_URI . '/' . $path; }
        return $url;
    }
    private function internal_url($url) {
        if (!$url || preg_match('~^(?:https?:)?//|^(?:mailto:|tel:|sms:|data:)~i',$url) || $url[0]==='#') { return $url; }
        $parts=parse_url($url); $path=$parts['path'] ?? '';
        if (strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='html') { return $url; }
        $key=ltrim(preg_replace('~^(?:\\./|\\.\\./)+~','',$path),'/');
        if (isset($this->content_map[$key])) { $mapped=$this->content_map[$key]; }
        else { $slug=basename($key,'.html'); $mapped=home_url('/' . ($slug==='index'?'':$slug . '/')); }
        if (!empty($parts['query'])) { $mapped=add_query_arg(wp_parse_args($parts['query']),$mapped); }
        if (!empty($parts['fragment'])) { $mapped.='#' . $parts['fragment']; }
        return $mapped;
    }
    private function add_element(&$elements,$type,$label,$text='',$extra=array()) {
        $token=$this->make_token();
        $clean_label=wp_strip_all_tags($label); $clean_label=function_exists('mb_substr') ? mb_substr($clean_label,0,120) : substr($clean_label,0,120);
        $row=array('element_label'=>$clean_label,'element_token'=>$token,'secondary_token'=>'','element_type'=>$type,'text_value'=>$text,'link_value'=>array(),'image_value'=>0,'asset_url'=>'','alt_value'=>'');
        foreach ($extra as $key=>$value) { $row[$key]=$value; }
        $elements[]=$row;
        return $token;
    }
    private function node_label($node,$value='') {
        $tag=$node->parentNode && $node->parentNode instanceof DOMElement ? strtoupper($node->parentNode->tagName) : 'TEXT';
        $sample=trim(preg_replace('/\\s+/u',' ',wp_strip_all_tags($value ?: $node->nodeValue)));
        if (function_exists('mb_substr')) { $sample=mb_substr($sample,0,65); } else { $sample=substr($sample,0,65); }
        return $tag . ': ' . $sample;
    }
    private function extract_node_fields($node,$dom,&$elements) {
        if ($node instanceof DOMText) {
            $parent=strtolower($node->parentNode ? $node->parentNode->nodeName : '');
            $text=$node->nodeValue;
            if (in_array($parent,array('script','style'),true) || trim($text)==='' || strpos($text,'[alify_form ')!==false) { return; }
            $token=$this->add_element($elements,'text',$this->node_label($node,$text),$text);
            $node->nodeValue=$token;
            return;
        }
        if (!($node instanceof DOMElement)) { return; }
        $tag=strtolower($node->tagName);
        if ($tag==='a' && $node->hasAttribute('href')) {
            $old=$node->getAttribute('href'); $link_text=trim(preg_replace('/\\s+/u',' ',$node->textContent));
            $target=$node->getAttribute('target');
            $token=$this->add_element($elements,'link','Link URL: ' . ($link_text ?: $old),'',array('link_value'=>array('url'=>$this->internal_url($old),'title'=>$link_text,'target'=>$target)));
            $entry_index=count($elements)-1; $secondary=$this->make_token(); $elements[$entry_index]['secondary_token']=$secondary;
            $node->setAttribute('href',$token); if ($node->hasAttribute('target')) { $node->setAttribute('target',$secondary); }
        }
        if ($tag==='img' && $node->hasAttribute('src')) {
            $src=$node->getAttribute('src'); $alt=$node->getAttribute('alt');
            $token=$this->add_element($elements,'image','Image: ' . ($alt ?: basename(parse_url($src,PHP_URL_PATH) ?: $src)),'',array('asset_url'=>$this->asset_url($src),'alt_value'=>$alt));
            $entry_index=count($elements)-1; $secondary=$this->make_token(); $elements[$entry_index]['secondary_token']=$secondary;
            $node->setAttribute('src',$token); $node->setAttribute('alt',$secondary);
        }
        if ($node->hasAttribute('style')) {
            $style=$node->getAttribute('style');
            $style=preg_replace_callback('~url\\(\\s*(["\\\']?)([^)"\\\']+)\\1\\s*\\)~i',function($m) use (&$elements) {
                $url=trim($m[2]); if (preg_match('~^(?:https?:)?//|^(?:data:|blob:)~i',$url)) { return $m[0]; }
                $token=$this->add_element($elements,'image','Background image: ' . basename(parse_url($url,PHP_URL_PATH) ?: $url),'',array('asset_url'=>$this->asset_url($url)));
                return 'url("' . $token . '")';
            },$style);
            $node->setAttribute('style',$style);
        }
        $attribute_names=array('title','placeholder','aria-label','alt');
        foreach ($attribute_names as $attribute) {
            if ($tag==='img' && $attribute==='alt') { continue; }
            if (!$node->hasAttribute($attribute)) { continue; }
            $value=$node->getAttribute($attribute); $token=$this->add_element($elements,'attribute',strtoupper($tag) . ' ' . $attribute . ': ' . $value,$value);
            $node->setAttribute($attribute,$token);
        }
        $children=array(); foreach ($node->childNodes as $child) { $children[]=$child; }
        foreach ($children as $child) { $this->extract_node_fields($child,$dom,$elements); }
    }
    private function node_inner_html($node,$dom) { $html=''; foreach ($node->childNodes as $child) { $html.=$dom->saveHTML($child); } return $html; }
    private function replace_forms_with_shortcodes($section,$dom,$file) {
        $type=$file==='start-project' ? 'project' : ($file==='book-call' ? 'booking' : 'contact');
        $forms=array(); foreach ($section->getElementsByTagName('form') as $form) { $forms[]=$form; }
        foreach ($forms as $form) { $shortcode=$dom->createTextNode('[alify_form type="' . $type . '"]'); $form->parentNode->replaceChild($shortcode,$form); }
    }
    private function rewrite_urls($html) {
        $theme_assets=ALIFY_URI . '/assets/';
        $html=preg_replace('~(?:\.\./)?assets/~',$theme_assets,$html);
        $html=preg_replace_callback('~(href|src|action)=("|\')([^"\']+\.html(?:#[^"\']*)?)(\2)~i',function($m) {
            $target=$m[3];
            if (preg_match('~^(?:https?:)?//|^(?:mailto|tel):~i',$target)) { return $m[0]; }
            $hash=''; if (strpos($target,'#')!==false) { list($target,$hash)=explode('#',$target,2); $hash='#'.$hash; }
            if (isset($this->content_map[$target])) { $url=$this->content_map[$target]; }
            else { $slug=basename($target,'.html'); $url=home_url('/' . ($slug==='index'?'':$slug . '/')); }
            return $m[1] . '=' . $m[2] . esc_url($url . $hash) . $m[4];
        },$html);
        return $html;
    }
    private function migrate_sections() {
        $all=array('404'=>'404','index'=>'home','about'=>'about','services'=>'services','industries'=>'industries','case-studies'=>'case-studies','work'=>'work','blog'=>'blog','testimonials'=>'testimonials','faq'=>'faq','pricing'=>'pricing','contact'=>'contact','book-call'=>'book-call','start-project'=>'start-project','privacy-policy'=>'privacy-policy','terms-of-service'=>'terms-of-service','thank-you'=>'thank-you','service-single'=>'service-single','industry-single'=>'industry-single','case-study-single'=>'case-study-single','work-single'=>'work-single','blog-single'=>'blog-single');
        foreach ($all as $file=>$slug) {
            $path=$this->source . '/' . $file . '.html'; if (!file_exists($path)) { continue; }
            $dom=$this->read_document($path); if (!$dom) { continue; }
            $mains=$dom->getElementsByTagName('main'); if (!$mains->length) { continue; }
            $rows=array();
            foreach ($this->section_nodes($mains->item(0)) as $section) {
                $this->replace_forms_with_shortcodes($section,$dom,$file);
                $this->element_counter=0; $elements=array();
                $children=array(); foreach ($section->childNodes as $child) { $children[]=$child; }
                foreach ($children as $child) { $this->extract_node_fields($child,$dom,$elements); }
                $markup=$this->rewrite_urls($this->node_inner_html($section,$dom));
                $rows[]=array('acf_fc_layout'=>'legacy_elements','hide_section'=>0,'legacy_classes'=>$section->getAttribute('class'),'legacy_id'=>$section->getAttribute('id'),'legacy_aria_label'=>$section->getAttribute('aria-label'),'legacy_aria_labelledby'=>$section->getAttribute('aria-labelledby'),'legacy_markup'=>$markup,'legacy_elements'=>$elements);
            }
            if (!$rows) { continue; }
            if (isset($this->archive_map[$slug])) {
                $option_key=str_replace('-','_',$this->archive_map[$slug]);
                update_field($option_key . '_sections',$rows,'option');
                update_field($option_key . '_archive_title',$this->page_title($path),'option');
                if ($file==='404') { $this->migrate_seo($path,0,'not-found'); }
                $page=get_page_by_path($slug,OBJECT,'page'); if ($page) { $this->content_map[$file . '.html']=get_permalink($page); }
            } else {
                $target=$this->target_for_file($file);
                if ($target) { update_field('page_sections',$rows,$target); }
            }
            WP_CLI::log(sprintf('Imported %d sections from %s.html',$rows ? count($rows) : 0,$file));
        }
    }
    private function target_for_file($file) {
        $map=array('index'=>'home','service-single'=>'web-design-development','industry-single'=>'fintech-digital-banking','case-study-single'=>'finpulse-ai','work-single'=>'finpulse-ai-platform','blog-single'=>'scalable-design-systems');
        if (isset($map[$file])) {
            $type=array('service-single'=>'alify_service','industry-single'=>'alify_industry','case-study-single'=>'alify_case_study','work-single'=>'alify_project','blog-single'=>'post');
            $post=get_page_by_path($map[$file],OBJECT,$type[$file]); return $post ? (int)$post->ID : 0;
        }
        $page=get_page_by_path($file==='index'?'home':$file,OBJECT,'page'); return $page ? $page->ID : 0;
    }
    private function get_or_create_menu($name) {
        foreach (wp_get_nav_menus() as $menu) { if ($menu->name===$name) { return (int)$menu->term_id; } }
        $id=wp_create_nav_menu($name); return is_wp_error($id) ? 0 : (int)$id;
    }
    private function add_menu_page($menu_id,$slug,$label='',$parent=0) {
        if (!$menu_id) { return 0; }
        $page=get_page_by_path($slug,OBJECT,'page'); if (!$page) { return 0; }
        $item=wp_update_nav_menu_item($menu_id,0,array('menu-item-title'=>$label ?: $page->post_title,'menu-item-object'=>'page','menu-item-object-id'=>$page->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent));
        return is_wp_error($item) ? 0 : (int)$item;
    }
    private function add_menu_url($menu_id,$url,$label,$parent=0) {
        $item=wp_update_nav_menu_item($menu_id,0,array('menu-item-title'=>$label,'menu-item-url'=>$url,'menu-item-object'=>'custom','menu-item-type'=>'custom','menu-item-status'=>'publish','menu-item-parent-id'=>$parent));
        return is_wp_error($item) ? 0 : (int)$item;
    }
    private function populate_menu_if_empty($menu_id,$items) {
        if (!$menu_id || wp_get_nav_menu_items($menu_id)) { return; }
        foreach ($items as $item) { if ($item[0]==='page') { $this->add_menu_page($menu_id,$item[1],$item[2] ?? ''); } else { $this->add_menu_url($menu_id,$item[1],$item[2]); } }
    }
    private function set_site_menus() {
        $primary=$this->get_or_create_menu('ALIFY Primary Menu');
        $footer=$this->get_or_create_menu('ALIFY Footer Menu');
        $legal=$this->get_or_create_menu('ALIFY Legal Menu');
        if ($primary && !wp_get_nav_menu_items($primary)) {
            $about=$this->add_menu_page($primary,'about');
            $services=$this->add_menu_page($primary,'services');
            foreach (get_posts(array('post_type'=>'alify_service','numberposts'=>-1,'post_status'=>'publish','orderby'=>'menu_order title','order'=>'ASC')) as $service) { wp_update_nav_menu_item($primary,0,array('menu-item-title'=>$service->post_title,'menu-item-object'=>'alify_service','menu-item-object-id'=>$service->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$services)); }
            $industries=$this->add_menu_page($primary,'industries');
            foreach (get_posts(array('post_type'=>'alify_industry','numberposts'=>-1,'post_status'=>'publish','orderby'=>'menu_order title','order'=>'ASC')) as $industry) { wp_update_nav_menu_item($primary,0,array('menu-item-title'=>$industry->post_title,'menu-item-object'=>'alify_industry','menu-item-object-id'=>$industry->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$industries)); }
            $work=$this->add_menu_page($primary,'work'); $this->add_menu_url($primary,home_url('/case-studies/'),'Case Studies',$work);
            $this->add_menu_page($primary,'pricing'); $this->add_menu_page($primary,'blog','Insights'); $this->add_menu_page($primary,'contact');
            $about && $this->add_menu_page($primary,'testimonials','Testimonials',$about);
            $about && $this->add_menu_page($primary,'faq','FAQs',$about);
        }
        $this->populate_menu_if_empty($footer,array(array('page','about'),array('page','services'),array('page','case-studies','Case Studies'),array('page','contact')));
        $this->populate_menu_if_empty($legal,array(array('page','privacy-policy'),array('page','terms-of-service')));
        set_theme_mod('nav_menu_locations',array('primary'=>$primary,'footer'=>$footer,'legal'=>$legal));
    }
    private function set_site_pages() {
        $home=get_page_by_path('home',OBJECT,'page'); if ($home) { update_option('show_on_front','page'); update_option('page_on_front',$home->ID); }
        $blog=get_page_by_path('blog',OBJECT,'page'); if ($blog) { update_option('page_for_posts',$blog->ID); }
        $thank=get_page_by_path('thank-you',OBJECT,'page'); if ($thank && function_exists('update_field')) { update_field('form_thank_you_page',$thank->ID,'option'); }
        $this->set_site_menus();
    }
}
WP_CLI::add_command('alify import-static','Alify_Static_Import_Command');
