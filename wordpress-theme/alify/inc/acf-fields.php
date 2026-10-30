<?php
if (!defined('ABSPATH')) { exit; }

function alify_acf_collect_keys($value,$suffix,&$map) {
    if (!is_array($value)) { return; }
    if (isset($value['key']) && is_string($value['key'])) { $map[$value['key']]=$value['key'] . '_' . sanitize_key($suffix); }
    foreach ($value as $item) { if (is_array($item)) { alify_acf_collect_keys($item,$suffix,$map); } }
}
function alify_acf_rewrite_keys($value,$map) {
    if (!is_array($value)) { return is_string($value) && isset($map[$value]) ? $map[$value] : $value; }
    $result=array();
    foreach ($value as $key=>$item) {
        $new_key=is_string($key) && isset($map[$key]) ? $map[$key] : $key;
        $result[$new_key]=alify_acf_rewrite_keys($item,$map);
    }
    return $result;
}
function alify_acf_rekey($value, $suffix) {
    $map=array(); alify_acf_collect_keys($value,$suffix,$map);
    return alify_acf_rewrite_keys($value,$map);
}

function alify_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) { return; }
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array('page_title' => __('ALIFY Theme Options', 'alify'), 'menu_title' => __('ALIFY Options', 'alify'), 'menu_slug' => 'alify-options', 'capability' => 'manage_options', 'redirect' => true, 'icon_url' => 'dashicons-admin-customizer', 'position' => 3));
        $options = array(
            'theme' => 'Global Theme Settings', 'forms' => 'Forms & Email Settings', 'seo' => 'Global SEO Settings', 'not-found' => '404 Page',
            'home' => 'Home Page', 'about' => 'About Page', 'services' => 'Services Archive', 'industries' => 'Industries Archive',
            'case-studies' => 'Case Studies Archive', 'work' => 'Work Archive', 'blog' => 'Blog Archive',
            'testimonials' => 'Testimonials Archive', 'faq' => 'FAQ Archive', 'pricing' => 'Pricing Page',
            'service-category' => 'Service Category Archive', 'industry-category' => 'Industry Category Archive',
        );
        foreach ($options as $slug => $title) {
            acf_add_options_sub_page(array('page_title' => $title, 'menu_title' => $title, 'parent_slug' => 'alify-options', 'menu_slug' => 'alify-' . $slug));
        }
    }

    $section_layouts = array(
        'legacy_elements' => array('Imported Section — individually editable content','The original layout is preserved. Edit each text, link, image, alt text, placeholder, or accessible label in its own row below; do not edit the read-only markup field.', array(
            array('key'=>'field_alify_legacy_class','label'=>'Section CSS classes','name'=>'legacy_classes','type'=>'text'),
            array('key'=>'field_alify_legacy_id','label'=>'Section ID / anchor','name'=>'legacy_id','type'=>'text'),
            array('key'=>'field_alify_legacy_aria','label'=>'Accessible label','name'=>'legacy_aria_label','type'=>'text'),
            array('key'=>'field_alify_legacy_labelledby','label'=>'Accessible label reference','name'=>'legacy_aria_labelledby','type'=>'text'),
            array('key'=>'field_alify_legacy_markup','label'=>'Preserved layout markup (do not edit)','name'=>'legacy_markup','type'=>'textarea','rows'=>6,'readonly'=>1,'new_lines'=>'','instructions'=>'Layout and classes are retained from the original design. Use the fields below to change content.'),
            array('key'=>'field_alify_legacy_elements','label'=>'Individual content fields','name'=>'legacy_elements','type'=>'repeater','layout'=>'block','button_label'=>'Add content element','collapsed'=>'field_alify_element_label','sub_fields'=>array(
                array('key'=>'field_alify_element_label','label'=>'Element / original content preview','name'=>'element_label','type'=>'text'),
                array('key'=>'field_alify_element_token','label'=>'Internal element reference (do not change)','name'=>'element_token','type'=>'text','readonly'=>1),
                array('key'=>'field_alify_element_secondary_token','label'=>'Internal secondary reference (do not change)','name'=>'secondary_token','type'=>'text','readonly'=>1,'conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'link')),array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'image')))),
                array('key'=>'field_alify_element_type','label'=>'Element kind','name'=>'element_type','type'=>'select','choices'=>array('text'=>'Text','link'=>'Link URL','image'=>'Image','attribute'=>'Accessible label / placeholder / other text'),'ui'=>1),
                array('key'=>'field_alify_element_text','label'=>'Text / attribute value','name'=>'text_value','type'=>'textarea','rows'=>3,'conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'text')),array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'attribute')))),
                array('key'=>'field_alify_element_link','label'=>'Link destination (internal or external)','name'=>'link_value','type'=>'link','return_format'=>'array','conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'link')))),
                array('key'=>'field_alify_element_image','label'=>'Replace image (optional; leave empty to keep original)','name'=>'image_value','type'=>'image','return_format'=>'id','preview_size'=>'thumbnail','conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'image')))),
                array('key'=>'field_alify_element_asset_url','label'=>'Original / fallback asset URL','name'=>'asset_url','type'=>'url','conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'image')))),
                array('key'=>'field_alify_element_alt','label'=>'Image alt text','name'=>'alt_value','type'=>'text','conditional_logic'=>array(array(array('field'=>'field_alify_element_type','operator'=>'==','value'=>'image')))),
            )),
        )),

        'hero' => array('Hero Section', array(
            array('key'=>'field_alify_hero_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),
            array('key'=>'field_alify_hero_title','label'=>'Heading','name'=>'heading','type'=>'textarea','rows'=>2,'required'=>1),
            array('key'=>'field_alify_hero_copy','label'=>'Intro copy','name'=>'copy','type'=>'wysiwyg','tabs'=>'visual,text','toolbar'=>'basic','media_upload'=>0),
            array('key'=>'field_alify_hero_image','label'=>'Hero image','name'=>'image','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
            array('key'=>'field_alify_hero_primary','label'=>'Primary link','name'=>'primary_link','type'=>'link','return_format'=>'array'),
            array('key'=>'field_alify_hero_secondary','label'=>'Secondary link','name'=>'secondary_link','type'=>'link','return_format'=>'array'),
        )),
        'rich_text' => array('Rich Text / Content Section', array(
            array('key'=>'field_alify_text_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),
            array('key'=>'field_alify_text_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_text_content','label'=>'Content','name'=>'content','type'=>'wysiwyg','tabs'=>'all','toolbar'=>'full','media_upload'=>1),
        )),
        'image_text' => array('Image + Text Section', array(
            array('key'=>'field_alify_it_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),
            array('key'=>'field_alify_it_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_it_copy','label'=>'Copy','name'=>'copy','type'=>'wysiwyg','tabs'=>'all','toolbar'=>'full','media_upload'=>1),
            array('key'=>'field_alify_it_image','label'=>'Image','name'=>'image','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
            array('key'=>'field_alify_it_image_alt','label'=>'Image alt text','name'=>'image_alt','type'=>'text'),
            array('key'=>'field_alify_it_link','label'=>'Link','name'=>'link','type'=>'link','return_format'=>'array'),
            array('key'=>'field_alify_it_image_side','label'=>'Image position','name'=>'image_side','type'=>'button_group','choices'=>array('left'=>'Left','right'=>'Right'),'default_value'=>'right'),
        )),
        'card_grid' => array('Card Grid / Related Content', array(
            array('key'=>'field_alify_cards_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),
            array('key'=>'field_alify_cards_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_cards_intro','label'=>'Intro','name'=>'intro','type'=>'textarea','rows'=>3),
            array('key'=>'field_alify_cards_source','label'=>'Cards source','name'=>'source','type'=>'select','choices'=>array('manual'=>'Manual cards','services'=>'Services','industries'=>'Industries','case_studies'=>'Case studies','projects'=>'Work / Projects','posts'=>'Blog posts','testimonials'=>'Testimonials','faqs'=>'FAQs'),'default_value'=>'manual','ui'=>1),
            array('key'=>'field_alify_cards_items','label'=>'Cards','name'=>'items','type'=>'repeater','min'=>0,'layout'=>'block','button_label'=>'Add card','sub_fields'=>array(
                array('key'=>'field_alify_card_title','label'=>'Title','name'=>'title','type'=>'text','required'=>1),
                array('key'=>'field_alify_card_copy','label'=>'Summary','name'=>'summary','type'=>'textarea','rows'=>2),
                array('key'=>'field_alify_card_image','label'=>'Image','name'=>'image','type'=>'image','return_format'=>'array','preview_size'=>'thumbnail'),
                array('key'=>'field_alify_card_link','label'=>'Internal or external link','name'=>'link','type'=>'link','return_format'=>'array'),
                array('key'=>'field_alify_card_related','label'=>'Related content (preferred for internal links)','name'=>'related','type'=>'post_object','post_type'=>array('page','alify_service','alify_industry','alify_case_study','alify_project','post'),'return_format'=>'id','ui'=>1),
            )),
        )),
        'stats' => array('Statistics / Proof Points', array(
            array('key'=>'field_alify_stats_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_stats_items','label'=>'Stats','name'=>'items','type'=>'repeater','min'=>1,'layout'=>'table','button_label'=>'Add stat','sub_fields'=>array(
                array('key'=>'field_alify_stat_value','label'=>'Value','name'=>'value','type'=>'text'),array('key'=>'field_alify_stat_label','label'=>'Label','name'=>'label','type'=>'text'),array('key'=>'field_alify_stat_note','label'=>'Note','name'=>'note','type'=>'text'),
            )),
        )),
        'faq' => array('FAQ Section', array(
            array('key'=>'field_alify_faq_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_faq_items','label'=>'Questions','name'=>'items','type'=>'repeater','min'=>1,'layout'=>'block','button_label'=>'Add question','sub_fields'=>array(
                array('key'=>'field_alify_faq_question','label'=>'Question','name'=>'question','type'=>'text','required'=>1),array('key'=>'field_alify_faq_answer','label'=>'Answer','name'=>'answer','type'=>'wysiwyg','tabs'=>'all','toolbar'=>'basic','media_upload'=>0),
            )),
        )),
        'testimonial' => array('Testimonials / Reviews', array(
            array('key'=>'field_alify_testimonial_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_testimonial_items','label'=>'Testimonials','name'=>'items','type'=>'relationship','post_type'=>array('alify_testimonial'),'filters'=>array('search'),'return_format'=>'object'),
        )),
        'cta' => array('Call to Action', array(
            array('key'=>'field_alify_cta_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),array('key'=>'field_alify_cta_heading','label'=>'Heading','name'=>'heading','type'=>'text','required'=>1),
            array('key'=>'field_alify_cta_copy','label'=>'Copy','name'=>'copy','type'=>'textarea','rows'=>3),array('key'=>'field_alify_cta_link','label'=>'Button','name'=>'link','type'=>'link','return_format'=>'array'),
        )),
        'form' => array('Form Section', array(
            array('key'=>'field_alify_form_heading','label'=>'Heading','name'=>'heading','type'=>'text'),array('key'=>'field_alify_form_type','label'=>'Form template','name'=>'form_type','type'=>'select','choices'=>array('contact'=>'Contact','project'=>'Start a project','booking'=>'Book a call','newsletter'=>'Newsletter'),'default_value'=>'contact','ui'=>1),
        )),
        'pricing_table' => array('Pricing Comparison', array(
            array('key'=>'field_alify_price_heading','label'=>'Heading','name'=>'heading','type'=>'text'),
            array('key'=>'field_alify_price_plans','label'=>'Plans','name'=>'plans','type'=>'repeater','min'=>1,'layout'=>'block','button_label'=>'Add plan','sub_fields'=>array(
                array('key'=>'field_alify_plan_name','label'=>'Plan name','name'=>'name','type'=>'text'),array('key'=>'field_alify_plan_price','label'=>'Price / note','name'=>'price','type'=>'text'),array('key'=>'field_alify_plan_link','label'=>'CTA','name'=>'link','type'=>'link','return_format'=>'array'),
            )),
            array('key'=>'field_alify_price_rows','label'=>'Comparison rows','name'=>'rows','type'=>'repeater','layout'=>'table','button_label'=>'Add feature row','sub_fields'=>array(
                array('key'=>'field_alify_price_feature','label'=>'Feature','name'=>'feature','type'=>'text'),array('key'=>'field_alify_price_values','label'=>'Values (comma-separated or short text)','name'=>'values','type'=>'text'),
            )),
        )),
    );
    $layouts = array();
    foreach ($section_layouts as $name => $layout) {
        $layout_fields = array(array('key'=>'field_alify_hide_' . $name,'label'=>'Hide this section?','name'=>'hide_section','type'=>'true_false','ui'=>1,'default_value'=>0));
        $definition = isset($layout[2]) ? $layout[2] : $layout[1];
        foreach ($definition as $field) { $layout_fields[] = $field; }
        $layouts['layout_alify_' . $name] = array('key'=>'layout_alify_' . $name,'name'=>$name,'label'=>$layout[0],'display'=>'block','instructions'=>isset($layout[2]) ? $layout[1] : '','sub_fields'=>$layout_fields,'min'=>0);
    }
    acf_add_local_field_group(array(
        'key'=>'group_alify_page_sections','title'=>__('Page Sections & Internal Links','alify'),
        'fields'=>array(array('key'=>'field_alify_sections','label'=>__('Page sections (drag to reorder; each section can be hidden)','alify'),'name'=>'page_sections','type'=>'flexible_content','button_label'=>__('Add section','alify'),'layouts'=>$layouts,'min'=>0)),
        'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'page')),array(array('param'=>'post_type','operator'=>'==','value'=>'post')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_service')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_industry')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_case_study')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_project')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_testimonial')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_faq'))),
        'position'=>'acf_after_title','style'=>'seamless','active'=>true,
    ));

    $seo_fields = array(
        array('key'=>'field_alify_seo_title','label'=>'SEO title','name'=>'seo_title','type'=>'text','instructions'=>'Leave blank to use the WordPress title. Recommended maximum: about 60 characters.'),
        array('key'=>'field_alify_seo_desc','label'=>'Meta description','name'=>'seo_description','type'=>'textarea','rows'=>3,'instructions'=>'Write a unique, useful summary. Recommended maximum: about 160 characters.'),
        array('key'=>'field_alify_seo_canonical','label'=>'Canonical URL override','name'=>'seo_canonical','type'=>'url'),
        array('key'=>'field_alify_seo_robots','label'=>'Search engine indexing','name'=>'seo_noindex','type'=>'true_false','ui'=>1,'ui_on_text'=>'Noindex','ui_off_text'=>'Index','default_value'=>0),
        array('key'=>'field_alify_seo_og_title','label'=>'Social share title','name'=>'seo_og_title','type'=>'text'),
        array('key'=>'field_alify_seo_og_desc','label'=>'Social share description','name'=>'seo_og_description','type'=>'textarea','rows'=>2),
        array('key'=>'field_alify_seo_og_image','label'=>'Social share image','name'=>'seo_og_image','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
        array('key'=>'field_alify_seo_schema','label'=>'Schema type','name'=>'seo_schema_type','type'=>'select','choices'=>array('WebPage'=>'Web page','Article'=>'Article','Service'=>'Service','Product'=>'Product','FAQPage'=>'FAQ page','ContactPage'=>'Contact page'),'default_value'=>'WebPage','ui'=>1),
    );
    acf_add_local_field_group(array('key'=>'group_alify_seo','title'=>__('ALIFY SEO (no SEO plugin required)','alify'),'fields'=>$seo_fields,'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'page')),array(array('param'=>'post_type','operator'=>'==','value'=>'post')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_service')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_industry')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_case_study')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_project')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_testimonial')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_faq'))),'position'=>'side','active'=>true));
    acf_add_local_field_group(array('key'=>'group_alify_term_seo','title'=>__('ALIFY SEO','alify'),'fields'=>alify_acf_rekey($seo_fields,'service_taxonomy'),'location'=>array(array(array('param'=>'taxonomy','operator'=>'==','value'=>'alify_service_category')),array(array('param'=>'taxonomy','operator'=>'==','value'=>'alify_industry_category'))),'position'=>'normal','active'=>true));

    $content_fields = array(
        array('key'=>'field_alify_item_summary','label'=>'Listing summary / excerpt','name'=>'summary','type'=>'textarea','rows'=>3),
        array('key'=>'field_alify_item_eyebrow','label'=>'Eyebrow','name'=>'eyebrow','type'=>'text'),
        array('key'=>'field_alify_item_icon','label'=>'Icon class (optional)','name'=>'icon_class','type'=>'text'),
        array('key'=>'field_alify_item_related','label'=>'Related pages / internal links','name'=>'related_pages','type'=>'relationship','post_type'=>array('page','alify_service','alify_industry','alify_case_study','alify_project','post'),'return_format'=>'object','filters'=>array('search','post_type')),
        array('key'=>'field_alify_item_related_services','label'=>'Related services','name'=>'related_services','type'=>'relationship','post_type'=>array('alify_service'),'return_format'=>'object','filters'=>array('search')),
        array('key'=>'field_alify_item_related_industries','label'=>'Related industries','name'=>'related_industries','type'=>'relationship','post_type'=>array('alify_industry'),'return_format'=>'object','filters'=>array('search')),
        array('key'=>'field_alify_item_related_cases','label'=>'Related case studies / projects','name'=>'related_work','type'=>'relationship','post_type'=>array('alify_case_study','alify_project'),'return_format'=>'object','filters'=>array('search')),
        array('key'=>'field_alify_item_client','label'=>'Client / organization','name'=>'client_name','type'=>'text'),
        array('key'=>'field_alify_item_role','label'=>'Client role / company','name'=>'client_role','type'=>'text'),
        array('key'=>'field_alify_item_quote','label'=>'Testimonial quote','name'=>'quote','type'=>'textarea','rows'=>5),
        array('key'=>'field_alify_item_faq_answer','label'=>'Answer','name'=>'answer','type'=>'wysiwyg','tabs'=>'all','toolbar'=>'basic','media_upload'=>0),
        array('key'=>'field_alify_item_result','label'=>'Key result / outcome','name'=>'key_result','type'=>'text'),
        array('key'=>'field_alify_item_url','label'=>'External project URL (optional)','name'=>'project_url','type'=>'url'),
    );
    acf_add_local_field_group(array('key'=>'group_alify_cpt_content','title'=>__('Content Details & Internal Linking','alify'),'fields'=>$content_fields,'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'alify_service')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_industry')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_case_study')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_project')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_testimonial')),array(array('param'=>'post_type','operator'=>'==','value'=>'alify_faq'))),'active'=>true));

    $global_fields = array(
        array('key'=>'field_alify_logo','label'=>'Logo','name'=>'logo','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
        array('key'=>'field_alify_footer_text','label'=>'Footer description','name'=>'footer_description','type'=>'textarea','rows'=>3),
        array('key'=>'field_alify_email','label'=>'Contact email','name'=>'contact_email','type'=>'email'),array('key'=>'field_alify_phone','label'=>'Phone','name'=>'phone','type'=>'text'),
        array('key'=>'field_alify_address','label'=>'Address','name'=>'address','type'=>'textarea','rows'=>2),
        array('key'=>'field_alify_social','label'=>'Social links','name'=>'social_links','type'=>'repeater','layout'=>'table','button_label'=>'Add social link','sub_fields'=>array(array('key'=>'field_alify_social_label','label'=>'Label','name'=>'label','type'=>'text'),array('key'=>'field_alify_social_url','label'=>'URL','name'=>'url','type'=>'url'))),
        array('key'=>'field_alify_header_hide','label'=>'Hide header on selected page(s)','name'=>'header_hidden_pages','type'=>'relationship','post_type'=>array('page','post','alify_service','alify_industry','alify_case_study','alify_project','alify_testimonial','alify_faq'),'return_format'=>'id','filters'=>array('search','post_type')),
        array('key'=>'field_alify_footer_hide','label'=>'Hide footer on selected page(s)','name'=>'footer_hidden_pages','type'=>'relationship','post_type'=>array('page','post','alify_service','alify_industry','alify_case_study','alify_project','alify_testimonial','alify_faq'),'return_format'=>'id','filters'=>array('search','post_type')),
        array('key'=>'field_alify_logo_alt','label'=>'Brand text / accessible name','name'=>'brand_name','type'=>'text','default_value'=>'ALIFY'),
        array('key'=>'field_alify_header_cta_label','label'=>'Header CTA label','name'=>'header_cta_label','type'=>'text','default_value'=>'Let’s Talk'),
        array('key'=>'field_alify_header_cta_url','label'=>'Header CTA URL','name'=>'header_cta_url','type'=>'url'),
        array('key'=>'field_alify_project_cta_label','label'=>'Mobile project CTA label','name'=>'project_cta_label','type'=>'text','default_value'=>'Start a Project'),
        array('key'=>'field_alify_project_cta_url','label'=>'Mobile project CTA URL','name'=>'project_cta_url','type'=>'url'),
        array('key'=>'field_alify_copyright','label'=>'Copyright text override','name'=>'copyright_text','type'=>'text'),
    );
    acf_add_local_field_group(array('key'=>'group_alify_theme_options','title'=>__('Header, Footer & Brand','alify'),'fields'=>$global_fields,'location'=>array(array(array('param'=>'options_page','operator'=>'==','value'=>'alify-theme'))),'active'=>true));

    $form_fields = array(
        array('key'=>'field_alify_form_recipient','label'=>'Notification recipient','name'=>'form_recipient','type'=>'email'),
        array('key'=>'field_alify_form_from','label'=>'Notification from name','name'=>'form_from_name','type'=>'text','default_value'=>'ALIFY Website'),
        array('key'=>'field_alify_form_reply','label'=>'Reply-to email (optional)','name'=>'form_reply_to','type'=>'email'),
        array('key'=>'field_alify_form_success','label'=>'Default success message','name'=>'form_success_message','type'=>'text','default_value'=>'Thanks — your message has been received.'),
        array('key'=>'field_alify_form_redirect','label'=>'Thank-you page','name'=>'form_thank_you_page','type'=>'post_object','post_type'=>array('page'),'return_format'=>'id','ui'=>1),
        array('key'=>'field_alify_form_fields','label'=>'Per-form templates, fields & settings','name'=>'form_templates','type'=>'repeater','layout'=>'block','button_label'=>'Configure form','collapsed'=>'field_alify_form_tpl_type','sub_fields'=>array(
            array('key'=>'field_alify_form_tpl_type','label'=>'Form template','name'=>'form_type','type'=>'select','choices'=>array('contact'=>'Contact','project'=>'Start a project','booking'=>'Book a call','newsletter'=>'Newsletter'),'required'=>1),
            array('key'=>'field_alify_form_tpl_recipient','label'=>'Recipient override','name'=>'recipient','type'=>'email'),
            array('key'=>'field_alify_form_tpl_subject','label'=>'Notification subject','name'=>'subject','type'=>'text'),
            array('key'=>'field_alify_form_tpl_success','label'=>'Success message','name'=>'success_message','type'=>'text'),
            array('key'=>'field_alify_form_tpl_name_label','label'=>'Name field label','name'=>'name_label','type'=>'text'),array('key'=>'field_alify_form_tpl_email_label','label'=>'Email field label','name'=>'email_label','type'=>'text'),
            array('key'=>'field_alify_form_tpl_company_label','label'=>'Company field label','name'=>'company_label','type'=>'text'),array('key'=>'field_alify_form_tpl_phone_label','label'=>'Phone field label','name'=>'phone_label','type'=>'text'),
            array('key'=>'field_alify_form_tpl_message_label','label'=>'Message field label','name'=>'message_label','type'=>'text'),array('key'=>'field_alify_form_tpl_service_label','label'=>'Service field label','name'=>'service_interest_label','type'=>'text'),
            array('key'=>'field_alify_form_tpl_budget_label','label'=>'Budget field label','name'=>'budget_label','type'=>'text'),array('key'=>'field_alify_form_tpl_timeline_label','label'=>'Timeline field label','name'=>'timeline_label','type'=>'text'),
            array('key'=>'field_alify_form_tpl_date_label','label'=>'Meeting date field label','name'=>'meeting_date_label','type'=>'text'),array('key'=>'field_alify_form_tpl_submit_label','label'=>'Submit button label','name'=>'submit_label','type'=>'text'),
            array('key'=>'field_alify_form_tpl_budget_options','label'=>'Budget choices (one per line)','name'=>'budget_options','type'=>'textarea','rows'=>5),
            array('key'=>'field_alify_form_tpl_show_company','label'=>'Show company field','name'=>'show_company','type'=>'true_false','ui'=>1,'default_value'=>1),array('key'=>'field_alify_form_tpl_show_phone','label'=>'Show phone field','name'=>'show_phone','type'=>'true_false','ui'=>1,'default_value'=>0),
            array('key'=>'field_alify_form_tpl_show_message','label'=>'Show message field','name'=>'show_message','type'=>'true_false','ui'=>1,'default_value'=>1),array('key'=>'field_alify_form_tpl_show_service','label'=>'Show service interest field','name'=>'show_service_interest','type'=>'true_false','ui'=>1,'default_value'=>1),
            array('key'=>'field_alify_form_tpl_show_budget','label'=>'Show budget field','name'=>'show_budget','type'=>'true_false','ui'=>1,'default_value'=>1),array('key'=>'field_alify_form_tpl_show_timeline','label'=>'Show timeline field','name'=>'show_timeline','type'=>'true_false','ui'=>1,'default_value'=>1),
            array('key'=>'field_alify_form_tpl_show_date','label'=>'Show meeting date field','name'=>'show_meeting_date','type'=>'true_false','ui'=>1,'default_value'=>1),array('key'=>'field_alify_form_tpl_require_name','label'=>'Require name field','name'=>'require_name','type'=>'true_false','ui'=>1,'default_value'=>1),array('key'=>'field_alify_form_tpl_require_message','label'=>'Require message field','name'=>'require_message','type'=>'true_false','ui'=>1,'default_value'=>1),
            array('key'=>'field_alify_form_tpl_custom_fields','label'=>'Additional individual fields','name'=>'additional_fields','type'=>'repeater','layout'=>'row','button_label'=>'Add a form field','sub_fields'=>array(
                array('key'=>'field_alify_form_custom_key','label'=>'Field key (lowercase, no spaces)','name'=>'field_key','type'=>'text'),array('key'=>'field_alify_form_custom_label','label'=>'Field label','name'=>'label','type'=>'text'),
                array('key'=>'field_alify_form_custom_type','label'=>'Input type','name'=>'field_type','type'=>'select','choices'=>array('text'=>'Text','email'=>'Email','tel'=>'Phone','date'=>'Date','textarea'=>'Long text','select'=>'Select list'),'default_value'=>'text'),
                array('key'=>'field_alify_form_custom_placeholder','label'=>'Placeholder','name'=>'placeholder','type'=>'text'),array('key'=>'field_alify_form_custom_required','label'=>'Required field','name'=>'required','type'=>'true_false','ui'=>1),
                array('key'=>'field_alify_form_custom_options','label'=>'Select choices (one per line)','name'=>'options','type'=>'textarea','rows'=>4),
            )),
        )),

    );
    acf_add_local_field_group(array('key'=>'group_alify_form_options','title'=>__('Central Form Settings','alify'),'fields'=>$form_fields,'location'=>array(array(array('param'=>'options_page','operator'=>'==','value'=>'alify-forms'))),'active'=>true));

    $global_seo = array(
        array('key'=>'field_alify_org_name','label'=>'Organization name','name'=>'organization_name','type'=>'text','default_value'=>'ALIFY'),
        array('key'=>'field_alify_org_type','label'=>'Organization schema type','name'=>'organization_schema_type','type'=>'select','choices'=>array('Organization'=>'Organization','ProfessionalService'=>'Professional service','LocalBusiness'=>'Local business'),'default_value'=>'Organization','ui'=>1),
        array('key'=>'field_alify_org_logo','label'=>'Organization logo','name'=>'organization_logo','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
        array('key'=>'field_alify_default_desc','label'=>'Default meta description','name'=>'default_meta_description','type'=>'textarea','rows'=>3),
        array('key'=>'field_alify_default_social','label'=>'Default social share image','name'=>'default_social_image','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
        array('key'=>'field_alify_schema_phone','label'=>'Organization telephone','name'=>'organization_telephone','type'=>'text'),
        array('key'=>'field_alify_schema_sameas','label'=>'Social profile URLs','name'=>'organization_sameas','type'=>'repeater','layout'=>'table','button_label'=>'Add profile','sub_fields'=>array(array('key'=>'field_alify_sameas_url','label'=>'URL','name'=>'url','type'=>'url'))),
    );
    acf_add_local_field_group(array('key'=>'group_alify_global_seo','title'=>__('Global SEO & Organization Schema','alify'),'fields'=>$global_seo,'location'=>array(array(array('param'=>'options_page','operator'=>'==','value'=>'alify-seo'))),'active'=>true));

    foreach (array('home'=>'Home Page Sections','about'=>'About Page Sections','services'=>'Services Archive Sections','industries'=>'Industries Archive Sections','case-studies'=>'Case Studies Archive Sections','work'=>'Work Archive Sections','blog'=>'Blog Archive Sections','testimonials'=>'Testimonials Archive Sections','faq'=>'FAQ Archive Sections','pricing'=>'Pricing Page Sections','service-category'=>'Service Category Archive Sections','industry-category'=>'Industry Category Archive Sections','not-found'=>'404 Page Sections') as $slug => $title) {
        acf_add_local_field_group(array(
            'key'=>'group_alify_archive_' . str_replace('-', '_', $slug),'title'=>__($title, 'alify'),
            'fields'=>array(
                array('key'=>'field_alify_archive_title_' . str_replace('-', '_', $slug),'label'=>'Page title','name'=>str_replace('-','_',$slug) . '_archive_title','type'=>'text'),
                array('key'=>'field_alify_archive_intro_' . str_replace('-', '_', $slug),'label'=>'Intro','name'=>str_replace('-','_',$slug) . '_archive_intro','type'=>'wysiwyg','tabs'=>'all','toolbar'=>'basic','media_upload'=>0),
                array('key'=>'field_alify_archive_sections_' . str_replace('-', '_', $slug),'label'=>'Sections (hide any section or reorder)','name'=>str_replace('-','_',$slug) . '_sections','type'=>'flexible_content','button_label'=>'Add section','layouts'=>alify_acf_rekey($layouts, $slug)),
                array('key'=>'field_alify_archive_dynamic_' . str_replace('-', '_', $slug),'label'=>'Show automatic content-type listing (hide imported card section first)','name'=>str_replace('-','_',$slug) . '_show_dynamic_listing','type'=>'true_false','ui'=>1,'default_value'=>0),
                array('key'=>'field_alify_archive_hide_header_' . str_replace('-', '_', $slug),'label'=>'Hide site header on this archive','name'=>str_replace('-','_',$slug) . '_hide_header','type'=>'true_false','ui'=>1),
                array('key'=>'field_alify_archive_hide_footer_' . str_replace('-', '_', $slug),'label'=>'Hide site footer on this archive','name'=>str_replace('-','_',$slug) . '_hide_footer','type'=>'true_false','ui'=>1),
                array('key'=>'field_alify_archive_seo_title_' . str_replace('-', '_', $slug),'label'=>'SEO title','name'=>str_replace('-','_',$slug) . '_seo_title','type'=>'text'),
                array('key'=>'field_alify_archive_seo_desc_' . str_replace('-', '_', $slug),'label'=>'Meta description','name'=>str_replace('-','_',$slug) . '_seo_description','type'=>'textarea','rows'=>3),
                array('key'=>'field_alify_archive_seo_canonical_' . str_replace('-', '_', $slug),'label'=>'Canonical URL override','name'=>str_replace('-','_',$slug) . '_seo_canonical','type'=>'url'),
                array('key'=>'field_alify_archive_seo_noindex_' . str_replace('-', '_', $slug),'label'=>'Noindex this archive','name'=>str_replace('-','_',$slug) . '_seo_noindex','type'=>'true_false','ui'=>1),
                array('key'=>'field_alify_archive_seo_ogtitle_' . str_replace('-', '_', $slug),'label'=>'Social share title','name'=>str_replace('-','_',$slug) . '_seo_og_title','type'=>'text'),
                array('key'=>'field_alify_archive_seo_ogdesc_' . str_replace('-', '_', $slug),'label'=>'Social share description','name'=>str_replace('-','_',$slug) . '_seo_og_description','type'=>'textarea','rows'=>2),
                array('key'=>'field_alify_archive_seo_ogimage_' . str_replace('-', '_', $slug),'label'=>'Social share image','name'=>str_replace('-','_',$slug) . '_seo_og_image','type'=>'image','return_format'=>'array','preview_size'=>'medium'),
                array('key'=>'field_alify_archive_seo_schema_' . str_replace('-', '_', $slug),'label'=>'Schema type','name'=>str_replace('-','_',$slug) . '_seo_schema_type','type'=>'select','choices'=>array('WebPage'=>'Web page','Article'=>'Article','Service'=>'Service','FAQPage'=>'FAQ page','ContactPage'=>'Contact page'),'default_value'=>'WebPage','ui'=>1),
            ),
            'location'=>array(array(array('param'=>'options_page','operator'=>'==','value'=>'alify-' . $slug))),'active'=>true,
        ));
    }
}
add_action('acf/init', 'alify_acf_fields');
