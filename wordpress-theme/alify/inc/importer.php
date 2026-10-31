<?php
if (!defined('ABSPATH')) { return; }

if (class_exists('WP_CLI_Command')) {
    abstract class Alify_Static_Import_Base extends WP_CLI_Command {}
} else {
    abstract class Alify_Static_Import_Base {}
}

class Alify_Static_Import_Command extends Alify_Static_Import_Base {
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
        $source=isset($assoc_args['source']) ? $assoc_args['source'] : realpath(dirname(ALIFY_DIR,2));
        if (!$this->run_import($source)) { WP_CLI::error($this->last_error); }
        WP_CLI::success('Static pages and original sections have been imported as ACF flexible-content rows. Review each page, SEO panel, menus, and internal links before going live.');
    }

    public $last_error = '';

    /** Run the same migration from WP-CLI or the one-time theme activation hook. */
    public function run_import($source) {
        if (!class_exists('DOMDocument') || !function_exists('update_field')) { $this->last_error='DOM and ACF Pro are required for import.'; return false; }
        $source=realpath($source);
        if (!$source || !is_dir($source) || !file_exists($source . '/index.html')) { $this->last_error='Cannot find index.html in source directory.'; return false; }
        $this->source=$source;
        $this->create_pages();
        $this->create_content_items();
        $this->migrate_sections();
        $this->set_site_pages();
        $required_pages=array('home','about','services','industries','case-studies','work','blog','testimonials','faq','pricing','contact','book-call','start-project','privacy-policy','terms-of-service','thank-you');
        $missing=array();
        foreach ($required_pages as $slug) { if (!get_page_by_path($slug,OBJECT,'page')) { $missing[]=$slug; } }
        if ($missing) { $this->last_error='Some pages could not be created: ' . implode(', ',$missing); return false; }
        if (count($this->catalog_items)+$this->extra_items_imported!==67) { $this->last_error='The full catalog import did not complete (expected 67 single records).'; return false; }
        return true;
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
        if (is_wp_error($id)) { if (defined('WP_CLI') && WP_CLI) { WP_CLI::warning($id->get_error_message()); } else { error_log('ALIFY import: ' . $id->get_error_message()); } return 0; }
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
    private $cpt_ids=array();

    private function element_has_class($node,$class) {
        if (!($node instanceof DOMElement)) { return false; }
        $classes=preg_split('/\\s+/',trim($node->getAttribute('class')));
        return in_array($class,$classes,true);
    }
    private function descendants_with_class($root,$class) {
        $matches=array(); if (!($root instanceof DOMElement)) { return $matches; }
        foreach ($root->getElementsByTagName('*') as $node) { if ($this->element_has_class($node,$class)) { $matches[]=$node; } }
        return $matches;
    }
    private function text_in_class($root,$class) {
        $nodes=$this->descendants_with_class($root,$class);
        return $nodes ? trim(preg_replace('/\\s+/u',' ',$nodes[0]->textContent)) : '';
    }
    private function service_categories_from_source() {
        $path=$this->source . '/services.html'; $dom=$this->read_document($path); if (!$dom) { return array(); }
        $map=array();
        foreach ($this->descendants_with_class($dom->getElementsByTagName('body')->item(0),'service-grid-col') as $card) {
            $category=sanitize_title($card->getAttribute('data-category')); if (!$category) { continue; }
            foreach ($card->getElementsByTagName('a') as $link) {
                $url=$link->getAttribute('href'); $parts=parse_url($url); $path_part=$parts['path'] ?? '';
                if (basename($path_part)!=='service-single.html') { continue; }
                $query=wp_parse_args($parts['query'] ?? ''); if (!empty($query['service'])) { $map[sanitize_title($query['service'])]=$category; break; }
            }
        }
        return $map;
    }
    private function import_testimonial_faq_items() {
        $testimonial_path=$this->source . '/testimonials.html'; $dom=$this->read_document($testimonial_path);
        if ($dom) {
            foreach ($this->descendants_with_class($dom->getElementsByTagName('body')->item(0),'testimonial-grid-col') as $card) {
                $client=$this->text_in_class($card,'testimonial-author-name'); $role=$this->text_in_class($card,'testimonial-author-role');
                $quote=$this->text_in_class($card,'testimonial-quote'); $slug=sanitize_title($client ?: $quote);
                if (!$slug || !$quote) { continue; }
                $id=$this->upsert('alify_testimonial',$client ?: $slug,$slug); if (!$id) { continue; }
                $this->extra_items_imported++;
                $summary=wp_trim_words($quote,28,''); wp_update_post(array('ID'=>$id,'post_excerpt'=>$summary));
                update_field('client_name',$client,$id); update_field('client_role',$role,$id); update_field('quote',$quote,$id); update_field('summary',$summary,$id);
                update_field('rating',$this->text_in_class($card,'testimonial-rating-score'),$id);
                update_field('key_result',$this->text_in_class($card,'testimonial-outcome-pill'),$id);
                update_field('service_label',$this->text_in_class($card,'testimonial-service-tag'),$id);
                update_field('testimonial_category',sanitize_title($card->getAttribute('data-category')),$id);
                $avatar=$this->text_in_class($card,'testimonial-avatar');
                preg_match('/[A-Z]{1,3}/',$avatar,$initials); update_field('avatar_initials',$initials[0] ?? '',$id);
                update_field('verified',(bool)$this->descendants_with_class($card,'testimonial-verified-badge'),$id);
                update_field('seo_title',($client ?: 'Client') . ' Testimonial | ALIFY',$id);
                if ($summary) { update_field('seo_description',wp_trim_words($summary,28,''),$id); }
                update_field('seo_schema_type','Article',$id);
            }
        }
        $faq_path=$this->source . '/faq.html'; $dom=$this->read_document($faq_path);
        if ($dom) {
            foreach ($this->descendants_with_class($dom->getElementsByTagName('body')->item(0),'faq-item') as $item) {
                $question=$this->text_in_class($item,'faq-question-text'); $answer_nodes=$this->descendants_with_class($item,'faq-answer-body');
                $answer=$answer_nodes ? $this->node_inner_html($answer_nodes[0],$dom) : '';
                if (!$question || !$answer) { continue; }
                $slug=sanitize_title($question); $id=$this->upsert('alify_faq',$question,$slug); if (!$id) { continue; }
                $this->extra_items_imported++;
                $category=''; $ancestor=$item->parentNode;
                while ($ancestor && $ancestor instanceof DOMElement) {
                    if ($this->element_has_class($ancestor,'faq-hub-group')) { $category=$this->text_in_class($ancestor,'faq-hub-group-title'); break; }
                    $ancestor=$ancestor->parentNode;
                }
                update_field('answer',$answer,$id); update_field('faq_category',$category,$id);
                update_field('faq_number',$this->text_in_class($item,'faq-num'),$id);
                $faq_summary=wp_trim_words(wp_strip_all_tags($answer),30,'');
                wp_update_post(array('ID'=>$id,'post_excerpt'=>$faq_summary));
                update_field('seo_title',$question . ' | ALIFY FAQ',$id);
                update_field('seo_description',wp_trim_words(wp_strip_all_tags($answer),28,''),$id);
                update_field('seo_schema_type','FAQPage',$id);
            }
        }
    }
    private function catalog_source_data() {
        $path=$this->source . '/catalog-content.json';
        if (!is_readable($path)) { $path=ALIFY_DIR . '/import-source/catalog-content.json'; }
        if (!is_readable($path)) { return array(); }
        $data=json_decode(file_get_contents($path),true);
        return is_array($data)?$data:array();
    }
    private function related_post_ids($slugs,$type) {
        $ids=array();
        foreach ((array)$slugs as $slug) { $post=get_page_by_path(sanitize_title($slug),OBJECT,$type); if ($post) { $ids[]=(int)$post->ID; } }
        return $ids;
    }
    private function catalog_asset_urls($value) {
        if (is_array($value)) { foreach ($value as $key=>$item) { $value[$key]=$this->catalog_asset_urls($item); } return $value; }
        if (is_string($value) && strpos($value,'assets/')===0) { return ALIFY_URI . '/' . $value; }
        return $value;
    }
    private function catalog_fields($family,$record) {
        $fields=$record;
        unset($fields['slug']);
        if ($family==='projects') { unset($fields['caseId'],$fields['shortTitle'],$fields['gallery2']); $fields['short_title']=$record['shortTitle'] ?? ''; $fields['gallery_2']=$record['gallery2'] ?? ''; }
        if ($family==='posts') { unset($fields['readTime']); $fields['read_time']=$record['readTime'] ?? ''; }
        if ($family==='services') {
            $fields['tags']=array_map(function($value){return array('tags_tag'=>$value);},$record['tags'] ?? array());
            $fields['stats']=array_map(function($row){return array('stats_value'=>$row[0] ?? '', 'stats_label'=>$row[1] ?? '');},$record['stats'] ?? array());
            $fields['deliverables']=array_map(function($row){return array('deliverables_icon'=>$row[0] ?? '', 'deliverables_title'=>$row[1] ?? '', 'deliverables_text'=>$row[2] ?? '');},$record['deliverables'] ?? array());
            $fields['stack']=array_map(function($value){return array('stack_item'=>$value);},$record['stack'] ?? array());
            $fields['related']=$this->related_post_ids($record['related'] ?? array(),'alify_project');
        } elseif ($family==='industries') {
            $fields['tags']=array_map(function($value){return array('tags_tag'=>$value);},$record['tags'] ?? array());
            $fields['stats']=array_map(function($row){return array('stats_value'=>$row[0] ?? '', 'stats_label'=>$row[1] ?? '');},$record['stats'] ?? array());
            $fields['challenges']=array_map(function($row){return array('challenges_issue'=>$row[0] ?? '', 'challenges_impact'=>$row[1] ?? '', 'challenges_solution'=>$row[2] ?? '');},$record['challenges'] ?? array());
            $fields['solutions']=array_map(function($value){return array('solutions_solution'=>$value);},$record['solutions'] ?? array());
            $fields['related']=$this->related_post_ids($record['related'] ?? array(),'alify_project');
        } elseif ($family==='projects') {
            $fields['chips']=array_map(function($value){return array('chips_chip'=>$value);},$record['chips'] ?? array());
            $fields['outcomes']=array_map(function($row){return array('outcomes_value'=>$row[0] ?? '', 'outcomes_label'=>$row[1] ?? '');},$record['outcomes'] ?? array());
            $fields['gallery']=array_map(function($row){return array('gallery_src'=>$row['src'] ?? '', 'gallery_tag'=>$row['tag'] ?? '', 'gallery_title'=>$row['title'] ?? '', 'gallery_caption'=>$row['caption'] ?? '');},$record['gallery'] ?? array());
            $case=get_page_by_path(sanitize_title($record['caseId'] ?? ''),OBJECT,'alify_case_study');
            $fields['case_id']=$case?(int)$case->ID:0;
        } elseif ($family==='case_studies') {
            $fields['kpis']=array_map(function($row){return array('kpis_value'=>$row[0] ?? '', 'kpis_label'=>$row[1] ?? '');},$record['kpis'] ?? array());
            $fields['benchmarks']=array_map(function($row){return array('benchmarks_label'=>$row[0] ?? '', 'benchmarks_before'=>$row[1] ?? '', 'benchmarks_after'=>$row[2] ?? '');},$record['benchmarks'] ?? array());
            $fields['related']=$this->related_post_ids($record['related'] ?? array(),'alify_project');
        } elseif ($family==='posts') {
            $fields['tags']=array_map(function($value){return array('tags_tag'=>$value);},$record['tags'] ?? array());
            $fields['sections']=array_map(function($section){
                return array('section_id'=>$section['id'] ?? '', 'section_heading'=>$section['heading'] ?? '',
                    'section_paragraphs'=>array_map(function($paragraph){return array('paragraph_text'=>$paragraph);},$section['paragraphs'] ?? array()),
                    'section_bullets'=>array_map(function($bullet){return array('bullet_text'=>$bullet);},$section['bullets'] ?? array()));
            },$record['sections'] ?? array());
        }
        return $this->catalog_asset_urls($fields);
    }
    private function create_content_items() {
        $catalogs=array(
            array('family'=>'services','file'=>'service-single','type'=>'alify_service','query'=>'service'),
            array('family'=>'industries','file'=>'industry-single','type'=>'alify_industry','query'=>'industry'),
            array('family'=>'projects','file'=>'work-single','type'=>'alify_project','query'=>'project'),
            array('family'=>'case_studies','file'=>'case-study-single','type'=>'alify_case_study','query'=>'case'),
            array('family'=>'posts','file'=>'blog-single','type'=>'post','query'=>'post'),
        );
        $data=$this->catalog_source_data(); $this->catalog_items=array(); $this->extra_items_imported=0;
        $service_categories=$this->service_categories_from_source();
        foreach ($catalogs as $catalog) {
            $template=$this->source . '/' . $catalog['file'] . '.html';
            if (!file_exists($template)) { continue; }
            foreach (($data[$catalog['family']] ?? array()) as $slug=>$record) {
                $record['slug']=$slug;
                $title=$record['title'] ?? ucwords(str_replace('-',' ',$slug));
                $id=$this->upsert($catalog['type'],$title,$slug);
                if (!$id) { continue; }
                $this->cpt_ids[$catalog['file']][]=$id;
                $this->catalog_items[]=array_merge($catalog,array('id'=>$id,'slug'=>$slug,'record'=>$record));
                if ($catalog['type']==='alify_service') {
                    $category_slug=$service_categories[$slug] ?? 'services';
                    $category_labels=array('engineering'=>'Engineering','design'=>'Design','growth'=>'Growth','services'=>'Services');
                    $category_name=$category_labels[$category_slug] ?? ucwords(str_replace('-',' ',$category_slug));
                    $service_term=term_exists($category_slug,'alify_service_category');
                    if (!$service_term) { $service_term=wp_insert_term($category_name,'alify_service_category',array('slug'=>$category_slug)); }
                    if (!is_wp_error($service_term)) {
                        $term_id=(int)(is_array($service_term)?$service_term['term_id']:$service_term);
                        wp_set_object_terms($id,array($term_id),'alify_service_category',false);
                        $term_object=get_term($term_id,'alify_service_category');
                        if ($term_object && !is_wp_error($term_object)) {
                            if (!get_field('seo_title',$term_object)) { update_field('seo_title',$category_name . ' Web Design & Development Services | ALIFY',$term_object); }
                            if (!get_field('seo_description',$term_object)) { update_field('seo_description','Explore ALIFY ' . strtolower($category_name) . ' services, capabilities, and delivery details.',$term_object); }
                        }
                    }
                }
                $permalink=get_permalink($id);
                $this->content_map[$catalog['file'] . '.html?' . $catalog['query'] . '=' . $slug]=$permalink;
                $this->content_map[$catalog['file'] . '.html']=$permalink;
            }
        }
        foreach ($this->catalog_items as $item) {
            $record=$item['record']; $id=$item['id'];
            foreach ($this->catalog_fields($item['family'],$record) as $name=>$value) { update_field($name,$value,$id); }
            $summary=$record['desc'] ?? ($record['summary'] ?? ($record['lead'] ?? ''));
            wp_update_post(array('ID'=>$id,'post_excerpt'=>wp_trim_words(wp_strip_all_tags($summary),45,'')));
            update_field('seo_title',($record['title'] ?? get_the_title($id)) . ' | ALIFY',$id);
            if ($summary) { update_field('seo_description',wp_trim_words(wp_strip_all_tags($summary),28,''),$id); }
            $schema=array('services'=>'Service','industries'=>'WebPage','projects'=>'Article','case_studies'=>'Article','posts'=>'Article');
            if (isset($schema[$item['family']])) { update_field('seo_schema_type',$schema[$item['family']],$id); }
        }
        $this->import_testimonial_faq_items();
    }
    private $catalog_items=array();
    private $extra_items_imported=0;
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
        $query_key=$key . (!empty($parts['query']) ? '?' . $parts['query'] : '');
        if (isset($this->content_map[$query_key])) { $mapped=$this->content_map[$query_key]; }
        elseif (isset($this->content_map[$key])) { $mapped=$this->content_map[$key]; }
        else { $slug=basename($key,'.html'); $mapped=home_url('/' . ($slug==='index'?'':$slug . '/')); }
        if (!empty($parts['query']) && !isset($this->content_map[$query_key])) { $mapped=add_query_arg(wp_parse_args($parts['query']),$mapped); }
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
                $targets=!empty($this->cpt_ids[$file]) ? $this->cpt_ids[$file] : array($this->target_for_file($file));
                foreach ($targets as $target) { if ($target) { update_field('page_sections',$rows,$target); } }
            }
            if (defined('WP_CLI') && WP_CLI) { WP_CLI::log(sprintf('Imported %d sections from %s.html',$rows ? count($rows) : 0,$file)); }
        }
    }
    private function target_for_file($file) {
        $map=array('index'=>'home','service-single'=>'web-development','industry-single'=>'fintech-digital-banking','case-study-single'=>'finpulse-ai','work-single'=>'finpulse-ai-platform','blog-single'=>'scalable-design-systems');
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

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('alify import-static','Alify_Static_Import_Command');
}

/** Import bundled source pages on first activation only; failed prerequisites remain retryable. */
function alify_import_bundled_source_on_activation() {
    if (get_option('alify_static_import_completed')) { return true; }
    // Never import until WordPress has registered the CPTs/taxonomies and ACF field groups.
    if (!did_action('init') || !post_type_exists('alify_service') || !did_action('acf/init')) { return false; }
    $source=ALIFY_DIR . '/import-source';
    if (!is_readable($source . '/index.html')) { return false; }
    $importer=new Alify_Static_Import_Command();
    if ($importer->run_import($source)) {
        update_option('alify_static_import_completed',time(),false);
        delete_transient('alify_import_last_error');
        return true;
    }
    set_transient('alify_import_last_error',$importer->last_error,DAY_IN_SECONDS);
    error_log('ALIFY automatic import skipped: ' . $importer->last_error);
    return false;
}
add_action('after_switch_theme','alify_import_bundled_source_on_activation');
// after_switch_theme may run before the new theme's init hooks; retry safely on the next admin request.
add_action('admin_init','alify_import_bundled_source_on_activation',20);

function alify_retry_bundled_import_action() {
    if (!current_user_can('manage_options')) { wp_die(esc_html__('You are not allowed to run this import.','alify')); }
    check_admin_referer('alify_retry_static_import');
    delete_transient('alify_import_last_error');
    $complete=alify_import_bundled_source_on_activation();
    $url=add_query_arg('alify_import_status',$complete?'complete':'failed',admin_url('edit.php?post_type=page'));
    wp_safe_redirect($url); exit;
}
add_action('admin_post_alify_retry_static_import','alify_retry_bundled_import_action');

function alify_import_admin_notice() {
    if (!current_user_can('manage_options')) { return; }
    $status=isset($_GET['alify_import_status']) ? sanitize_key(wp_unslash($_GET['alify_import_status'])) : '';
    if ($status==='complete') { echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('ALIFY static pages and content were imported. Review the Pages and content-type screens.','alify') . '</p></div>'; return; }
    if (get_option('alify_static_import_completed')) { return; }
    $error=get_transient('alify_import_last_error');
    $message=$error ? sprintf(__('ALIFY import is waiting: %s','alify'),$error) : __('ALIFY page import has not completed yet. Confirm ACF Pro and PHP DOM are active, then retry.','alify');
    $url=wp_nonce_url(admin_url('admin-post.php?action=alify_retry_static_import'),'alify_retry_static_import');
    echo '<div class="notice notice-warning"><p>' . esc_html($message) . ' <a class="button button-primary" href="' . esc_url($url) . '">' . esc_html__('Retry ALIFY import','alify') . '</a></p></div>';
}
add_action('admin_notices','alify_import_admin_notice');
