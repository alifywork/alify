<?php
if (!defined('ABSPATH')) { exit; }

function alify_catalog_field($type, $name, $label, $field_type='text', $extra=array()) {
    $key='field_alify_catalog_' . sanitize_key($type . '_' . $name);
    return array_merge(array('key'=>$key,'label'=>$label,'name'=>$name,'type'=>$field_type),$extra);
}
function alify_catalog_repeater($type,$name,$label,$subfields) {
    return alify_catalog_field($type,$name,$label,'repeater',array('layout'=>'block','button_label'=>sprintf(__('Add %s','alify'),strtolower($label)),'min'=>0,'sub_fields'=>$subfields));
}
function alify_catalog_row_fields($type,$prefix,$fields) {
    $result=array();
    foreach ($fields as $name=>$label) {
        $result[]=alify_catalog_field($type,$prefix . '_' . $name,$label,'text');
    }
    return $result;
}
function alify_catalog_schema($type) {
    $text=function($key,$label,$field_type='text') use ($type) { return alify_catalog_field($type,$key,$label,$field_type); };
    $repeat=function($key,$label,$children) use ($type) {
        $sub=array(); foreach ($children as $name=>$child_label) { $sub[]=alify_catalog_field($type,$key . '_' . $name,$child_label,'text'); }
        return alify_catalog_repeater($type,$key,$label,$sub);
    };
    $related=function($key,$label,$post_types,$multiple=true) use ($type) {
        return alify_catalog_field($type,$key,$label,$multiple?'relationship':'post_object',array('post_type'=>$post_types,'return_format'=>'object','filters'=>array('search','post_type'),'ui'=>1,'multiple'=>$multiple));
    };
    $fields=array(alify_catalog_field($type,'content_tab',__('Detail Content','alify'),'tab',array('placement'=>'top','endpoint'=>0)));
    if ($type==='alify_service') {
        $fields=array_merge($fields,array($text('code','Hero eyebrow'),$text('title','Detail title'),$text('desc','Description','textarea'),$text('bg','Hero image URL','url'),alify_catalog_field($type,'bg_image','Choose hero image','image',array('return_format'=>'array','preview_size'=>'medium')),$repeat('tags','Hero tags',array('tag'=>'Tag')),$repeat('stats','Statistics',array('value'=>'Value','label'=>'Label')),$repeat('deliverables','Deliverables',array('icon'=>'Icon class','title'=>'Title','text'=>'Description')),$repeat('stack','Technology / capability stack',array('item'=>'Item')),$related('related','Related projects',array('alify_project'))));
    } elseif ($type==='alify_industry') {
        $fields=array_merge($fields,array($text('code','Hero eyebrow'),$text('title','Detail title'),$text('desc','Description','textarea'),$text('bg','Hero image URL','url'),alify_catalog_field($type,'bg_image','Choose hero image','image',array('return_format'=>'array','preview_size'=>'medium')),$repeat('tags','Hero tags',array('tag'=>'Tag')),$repeat('stats','Statistics',array('value'=>'Value','label'=>'Label')),$repeat('challenges','Industry challenges',array('issue'=>'Challenge','impact'=>'Current impact','solution'=>'ALIFY solution')),$repeat('solutions','Solutions',array('solution'=>'Solution')),$related('related','Related projects',array('alify_project'))));
    } elseif ($type==='alify_project') {
        $fields=array_merge($fields,array($text('title','Project title'),$text('short_title','Short title'),$text('desc','Summary','textarea'),$text('bg','Cover image URL','url'),alify_catalog_field($type,'cover_image','Choose cover image','image',array('return_format'=>'array','preview_size'=>'medium')),$text('gallery_2','Secondary gallery image URL','url'),alify_catalog_field($type,'secondary_image','Choose secondary image','image',array('return_format'=>'array','preview_size'=>'medium')),$text('category','Project category'),$text('year','Year'),$text('sprint','Delivery sprint'),$text('metric','Headline metric'),$text('client','Client'),$text('hq','Client location'),$text('kicker','Client eyebrow'),$text('brief','Project brief','textarea'),$text('craft','Our work','textarea'),$repeat('chips','Project tags',array('chip'=>'Tag')),$repeat('outcomes','Outcomes',array('value'=>'Value','label'=>'Label')),alify_catalog_repeater($type,'gallery','Gallery',array_merge(array(alify_catalog_field($type,'gallery_image','Choose image','image',array('return_format'=>'array','preview_size'=>'thumbnail'))),alify_catalog_row_fields($type,'gallery',array('src'=>'Image URL','tag'=>'Image category','title'=>'Image title','caption'=>'Image description')))),$related('case_id','Related case study',array('alify_case_study'),false)));
    } elseif ($type==='alify_case_study') {
        $fields=array_merge($fields,array($text('client','Client'),$text('hq','Client location'),$text('sector','Sector'),$text('sprint','Delivery sprint'),$text('bg','Cover image URL','url'),alify_catalog_field($type,'cover_image','Choose cover image','image',array('return_format'=>'array','preview_size'=>'medium')),$text('title','Case study title'),$text('summary','Summary','textarea'),$repeat('kpis','Key outcomes',array('value'=>'Value','label'=>'Label')),$repeat('benchmarks','Before and after benchmarks',array('label'=>'Metric','before'=>'Before','after'=>'After')),$text('deliverables','Deliverables','textarea'),$text('ch1','Chapter one','textarea'),$text('ch2','Chapter two','textarea'),$text('ch3','Chapter three','textarea'),$text('ch4','Chapter four','textarea'),$text('quote','Client quote','textarea'),$text('author','Quote attribution'),$related('related','Related projects',array('alify_project'))));
    } elseif ($type==='alify_testimonial') {
        $fields=array_merge($fields,array($text('rating','Rating out of 5'),$text('service_label','Service label'),$text('testimonial_category','Testimonial category'),$text('avatar_initials','Avatar initials'),alify_catalog_field($type,'verified','Verified client','true_false',array('ui'=>1,'default_value'=>1))));
    } elseif ($type==='alify_faq') {
        $fields=array_merge($fields,array($text('faq_category','FAQ category'),$text('faq_number','Original number')));
    } elseif ($type==='post') {
        $section_sub=array(alify_catalog_field($type,'section_id','Section anchor','text'),alify_catalog_field($type,'section_heading','Section heading','text'),alify_catalog_repeater($type,'section_paragraphs','Paragraphs',array(alify_catalog_field($type,'paragraph_text','Paragraph','textarea'))),alify_catalog_repeater($type,'section_bullets','Bullet points',array(alify_catalog_field($type,'bullet_text','Bullet','textarea'))));
        $fields=array_merge($fields,array($text('title','Article title'),$text('category','Category'),$text('date','Display date'),$text('read_time','Reading time'),$text('image','Featured image URL','url'),alify_catalog_field($type,'featured_image','Choose featured image','image',array('return_format'=>'array','preview_size'=>'medium')),$text('lead','Article introduction','textarea'),$text('quote','Highlighted quotation','textarea'),alify_catalog_repeater($type,'sections','Article sections',$section_sub),$repeat('tags','Article tags',array('tag'=>'Tag'))));
    }
    return $fields;
}
function alify_register_catalog_fields() {
    if (!function_exists('acf_add_local_field_group')) { return; }
    $types=array('alify_service'=>'Service Single Content','alify_industry'=>'Industry Single Content','alify_project'=>'Project Single Content','alify_case_study'=>'Case Study Single Content','alify_testimonial'=>'Testimonial Details','alify_faq'=>'FAQ Details','post'=>'Blog Article Content');
    foreach ($types as $type=>$title) {
        acf_add_local_field_group(array('key'=>'group_alify_catalog_' . sanitize_key($type),'title'=>__($title,'alify'),'fields'=>alify_catalog_schema($type),'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>$type))),'position'=>'normal','active'=>true));
    }
}
add_action('acf/init','alify_register_catalog_fields',20);

function alify_catalog_related_slugs($value) {
    if (!$value) { return array(); }
    if (!is_array($value)) { $value=array($value); }
    $slugs=array();
    foreach ($value as $post) { if ($post instanceof WP_Post) { $slugs[]=$post->post_name; } elseif (is_numeric($post)) { $slug=get_post_field('post_name',(int)$post); if ($slug) { $slugs[]=$slug; } } }
    return $slugs;
}
function alify_catalog_asset_urls($value) {
    if (is_array($value)) { foreach ($value as $key=>$item) { $value[$key]=alify_catalog_asset_urls($item); } return $value; }
    if (is_string($value) && strpos($value,'assets/')===0) { return ALIFY_URI . '/' . $value; }
    return $value;
}
function alify_catalog_image_url($field,$post_id) {
    $image=get_field($field,$post_id);
    if (is_array($image) && !empty($image['url'])) { return $image['url']; }
    if (is_numeric($image) && $image) { $url=wp_get_attachment_image_url((int)$image,'full'); return $url ?: ''; }
    return '';
}
function alify_catalog_frontend_data($post_id) {
    if (!function_exists('get_field')) { return array(); }
    $type=get_post_type($post_id); $keys=array();
    if ($type==='alify_service') { $keys=array('code','title','desc','bg'); }
    elseif ($type==='alify_industry') { $keys=array('code','title','desc','bg'); }
    elseif ($type==='alify_project') { $keys=array('case_id','bg','gallery_2','category','year','sprint','metric','client','hq','kicker','short_title','title','desc','brief','craft'); }
    elseif ($type==='alify_case_study') { $keys=array('client','hq','sector','sprint','bg','title','summary','deliverables','ch1','ch2','ch3','ch4','quote','author'); }
    elseif ($type==='post') { $keys=array('title','category','date','read_time','image','lead','quote'); }
    $data=array(); foreach ($keys as $key) { $value=get_field($key,$post_id); if ($value!==null && $value!=='') { $data[$key]=$value; } }
    if (in_array($type,array('alify_service','alify_industry'),true)) { $image=alify_catalog_image_url('bg_image',$post_id); if ($image) { $data['bg']=$image; } }
    if ($type==='alify_project') {
        $image=alify_catalog_image_url('cover_image',$post_id); if ($image) { $data['bg']=$image; }
        $image=alify_catalog_image_url('secondary_image',$post_id); if ($image) { $data['gallery2']=$image; }
    }
    if ($type==='alify_case_study') { $image=alify_catalog_image_url('cover_image',$post_id); if ($image) { $data['bg']=$image; } }
    if ($type==='post') { $image=alify_catalog_image_url('featured_image',$post_id); if ($image) { $data['image']=$image; } }
    $rows=function($key) use ($post_id) { $value=get_field($key,$post_id); return is_array($value)?$value:array(); };
    $simple=function($key,$subkey) use ($rows) { return array_values(array_map(function($row) use ($subkey) { return $row[$subkey] ?? ''; },$rows($key))); };
    $pairs=function($key,$first,$second) use ($rows) { return array_values(array_map(function($row) use ($first,$second) { return array($row[$first] ?? '',$row[$second] ?? ''); },$rows($key))); };
    $triples=function($key,$first,$second,$third) use ($rows) { return array_values(array_map(function($row) use ($first,$second,$third) { return array($row[$first] ?? '',$row[$second] ?? '',$row[$third] ?? ''); },$rows($key))); };
    if ($type==='alify_project') {
        $case=get_field('case_id',$post_id); $data['caseId']=$case instanceof WP_Post?$case->post_name:'';
        $data['slug']=get_post_field('post_name',$post_id);
        $data['gallery2']=$data['gallery_2'] ?? ''; unset($data['gallery_2']);
        $data['shortTitle']=$data['short_title'] ?? ''; unset($data['short_title']);
    }
    if ($type==='post') { $data['readTime']=$data['read_time'] ?? ''; unset($data['read_time']); }
    if ($type==='alify_service') { $data['tags']=$simple('tags','tags_tag'); $data['stats']=$pairs('stats','stats_value','stats_label'); $data['deliverables']=$triples('deliverables','deliverables_icon','deliverables_title','deliverables_text'); $data['stack']=$simple('stack','stack_item'); $data['related']=alify_catalog_related_slugs(get_field('related',$post_id)); }
    elseif ($type==='alify_industry') { $data['tags']=$simple('tags','tags_tag'); $data['stats']=$pairs('stats','stats_value','stats_label'); $data['challenges']=$triples('challenges','challenges_issue','challenges_impact','challenges_solution'); $data['solutions']=$simple('solutions','solutions_solution'); $data['related']=alify_catalog_related_slugs(get_field('related',$post_id)); }
    elseif ($type==='alify_project') { $data['chips']=$simple('chips','chips_chip'); $data['outcomes']=$pairs('outcomes','outcomes_value','outcomes_label'); $data['gallery']=array_map(function($row){ $image=$row['gallery_image'] ?? ''; if (is_array($image)) { $image=$image['url'] ?? ''; } elseif (is_numeric($image) && $image) { $image=wp_get_attachment_image_url((int)$image,'full') ?: ''; } return array('src'=>$image ?: ($row['gallery_src'] ?? ''), 'tag'=>$row['gallery_tag'] ?? '', 'title'=>$row['gallery_title'] ?? '', 'caption'=>$row['gallery_caption'] ?? ''); },$rows('gallery')); }
    elseif ($type==='alify_case_study') { $data['kpis']=$pairs('kpis','kpis_value','kpis_label'); $data['benchmarks']=$triples('benchmarks','benchmarks_label','benchmarks_before','benchmarks_after'); $data['related']=alify_catalog_related_slugs(get_field('related',$post_id)); }
    elseif ($type==='post') { $data['tags']=$simple('tags','tags_tag'); $data['sections']=array_map(function($section) { return array('id'=>$section['section_id'] ?? '', 'heading'=>$section['section_heading'] ?? '', 'paragraphs'=>array_values(array_map(function($row){return $row['paragraph_text'] ?? '';},$section['section_paragraphs'] ?? array())),'bullets'=>array_values(array_map(function($row){return $row['bullet_text'] ?? '';},$section['section_bullets'] ?? array()))); },$rows('sections')); }
    return alify_catalog_asset_urls($data);
}
