<?php
if (!defined('ABSPATH')) { exit; }

function alify_form_setting($type, $key, $default = '') {
    if (function_exists('have_rows') && have_rows('form_templates', 'option')) {
        while (have_rows('form_templates', 'option')) { the_row(); if (get_sub_field('form_type') === $type) { $value = get_sub_field($key); return $value !== null && $value !== '' ? $value : $default; } }
    }
    return $default;
}

function alify_render_extra_form_fields($type) {
    $fields=alify_form_setting($type,'additional_fields',array());
    if (!is_array($fields)) { return; }
    $reserved=array('action','alify_form_type','alify_nonce','website_url','source_page','name','email','company','phone','message','budget','timeline','meeting_date','service_interest');
    $allowed_types=array('text','email','tel','date','textarea','select');
    foreach ($fields as $field) {
        $key=sanitize_key($field['field_key'] ?? ''); $kind=$field['field_type'] ?? 'text';
        if (!$key || in_array($key,$reserved,true) || !preg_match('/^[a-z][a-z0-9_]{0,39}$/',$key) || !in_array($kind,$allowed_types,true)) { continue; }
        $label=$field['label'] ?? ucfirst(str_replace('_',' ',$key)); $required=!empty($field['required']); $placeholder=$field['placeholder'] ?? '';
        echo '<label>' . esc_html($label);
        if ($kind==='textarea') { echo '<textarea name="' . esc_attr($key) . '" rows="4"' . ($placeholder?' placeholder="'.esc_attr($placeholder).'"':'') . ($required?' required':'') . '></textarea>'; }
        elseif ($kind==='select') {
            echo '<select name="' . esc_attr($key) . '"' . ($required?' required':'') . '><option value="">' . esc_html__('Choose an option','alify') . '</option>';
            $options=preg_split('/\r\n|\r|\n/',(string)($field['options'] ?? ''));
            foreach ($options as $option) { $option=trim($option); if ($option!=='') { echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>'; } }
            echo '</select>';
        } else { echo '<input name="' . esc_attr($key) . '" type="' . esc_attr($kind) . '"' . ($placeholder?' placeholder="'.esc_attr($placeholder).'"':'') . ($required?' required':'') . '>'; }
        echo '</label>';
    }
}

function alify_register_submission_type() {
    register_post_type('alify_submission', array(
        'labels'=>array('name'=>__('Form Entries','alify'),'singular_name'=>__('Form Entry','alify'),'edit_item'=>__('View Form Entry','alify')),
        'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'show_in_rest'=>false,'menu_icon'=>'dashicons-email-alt',
        'supports'=>array('title'),'capability_type'=>'post','map_meta_cap'=>true,
    ));
}
add_action('init','alify_register_submission_type');

function alify_form_shortcode($atts) {
    $atts = shortcode_atts(array('type'=>'contact'),$atts,'alify_form');
    $type = sanitize_key($atts['type']);
    if (!in_array($type,array('contact','project','booking','newsletter'),true)) { return ''; }
    ob_start();
    $form_type = $type;
    include ALIFY_DIR . '/template-parts/forms/' . $form_type . '.php';
    return ob_get_clean();
}
add_shortcode('alify_form','alify_form_shortcode');

function alify_handle_form_submission() {
    if (empty($_POST['alify_form_type'])) { wp_die(esc_html__('Invalid form submission.','alify'), '', array('response'=>400)); }
    $type = sanitize_key(wp_unslash($_POST['alify_form_type']));
    if (!in_array($type,array('contact','project','booking','newsletter'),true)) { wp_die(esc_html__('Invalid form type.','alify'), '', array('response'=>400)); }
    if (empty($_POST['alify_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['alify_nonce'])),'alify_form_' . $type)) { wp_die(esc_html__('Please refresh the page and try again.','alify'), '', array('response'=>403)); }
    if (!empty($_POST['website_url'])) { wp_safe_redirect(add_query_arg('form','success',wp_get_referer() ?: home_url('/'))); exit; }
    $fields = array();
    foreach (array('name','email','company','phone','message','budget','timeline','meeting_date','service_interest','source_page') as $key) {
        if (isset($_POST[$key]) && !is_array($_POST[$key])) {
            $raw=wp_unslash($_POST[$key]);
            $fields[$key]=in_array($key,array('message','source_page'),true) ? ($key==='source_page' ? esc_url_raw($raw) : sanitize_textarea_field($raw)) : sanitize_text_field($raw);
        }
    }
    $extra_fields=alify_form_setting($type,'additional_fields',array());
    $reserved=array('action','alify_form_type','alify_nonce','website_url','source_page','name','email','company','phone','message','budget','timeline','meeting_date','service_interest');
    if (is_array($extra_fields)) {
        foreach ($extra_fields as $extra) {
            $key=sanitize_key($extra['field_key'] ?? ''); $kind=$extra['field_type'] ?? 'text';
            if (!$key || in_array($key,$reserved,true)) { continue; }
            if (!isset($_POST[$key]) || is_array($_POST[$key])) {
                if (!empty($extra['required'])) { wp_die(esc_html(sprintf(__('%s is required.','alify'),$extra['label'] ?? $key)), '', array('response'=>400)); }
                continue;
            }
            $raw=wp_unslash($_POST[$key]);
            $value=$kind==='textarea' ? sanitize_textarea_field($raw) : ($kind==='email' ? sanitize_email($raw) : sanitize_text_field($raw));
            if (!empty($extra['required']) && $value==='') { wp_die(esc_html(sprintf(__('%s is required.','alify'),$extra['label'] ?? $key)), '', array('response'=>400)); }
            if ($kind==='email' && $value!=='' && !is_email($value)) { wp_die(esc_html(sprintf(__('Enter a valid %s.','alify'),$extra['label'] ?? $key)), '', array('response'=>400)); }
            if ($kind==='select' && $value!=='') { $choices=array_map('trim',preg_split('/\r\n|\r|\n/',(string)($extra['options'] ?? ''))); if (!in_array($value,$choices,true)) { wp_die(esc_html__('Invalid selection.','alify'), '', array('response'=>400)); } }
            $fields[$key]=$value;
        }
    }
    if (empty($fields['email'])) { wp_die(esc_html__('Email address is required.','alify'), '', array('response'=>400)); }
    if (!is_email($fields['email'])) { wp_die(esc_html__('Please enter a valid email address.','alify'), '', array('response'=>400)); }
    if ($type!=='newsletter' && alify_form_setting($type,'require_name',true) && empty($fields['name'])) { wp_die(esc_html__('Name is required.','alify'), '', array('response'=>400)); }
    if (alify_form_setting($type,'show_message',true) && alify_form_setting($type,'require_message',in_array($type,array('contact','project'),true)) && empty($fields['message'])) { wp_die(esc_html__('Please add a message.','alify'), '', array('response'=>400)); }
    $label = ucfirst($type) . ' — ' . ($fields['name'] ?? $fields['email'] ?? __('New inquiry','alify'));
    $entry_id = wp_insert_post(array('post_type'=>'alify_submission','post_status'=>'private','post_title'=>sanitize_text_field($label)),true);
    if (is_wp_error($entry_id)) { wp_die(esc_html__('We could not save your message. Please try again.','alify'), '', array('response'=>500)); }
    update_post_meta($entry_id,'_alify_form_type',$type);
    update_post_meta($entry_id,'_alify_submission_data',$fields);
    update_post_meta($entry_id,'_alify_source_url',esc_url_raw(wp_get_referer() ?: home_url('/')));
    $recipient = alify_form_setting($type, 'recipient', get_field('form_recipient','option') ?: get_option('admin_email'));
    $subject = alify_form_setting($type, 'subject', sprintf(__('New %s form submission','alify'),$type));
    $body = "Form: {$type}\n";
    foreach ($fields as $key=>$value) { $body .= ucfirst(str_replace('_',' ',$key)) . ': ' . $value . "\n"; }
    $from_name=get_field('form_from_name','option') ?: get_bloginfo('name');
    $from_email=sanitize_email(get_option('admin_email'));
    $headers=array('Content-Type: text/plain; charset=UTF-8');
    if ($from_email) { $headers[]='From: ' . sanitize_text_field($from_name) . ' <' . $from_email . '>'; }
    $reply_to=get_field('form_reply_to','option') ?: ($fields['email'] ?? '');
    if ($reply_to && is_email($reply_to)) { $headers[]='Reply-To: ' . sanitize_email($reply_to); }
    wp_mail($recipient,$subject,$body,$headers);
    $redirect_id = get_field('form_thank_you_page','option');
    $redirect = $redirect_id ? get_permalink($redirect_id) : (wp_get_referer() ?: home_url('/'));
    wp_safe_redirect(add_query_arg('form','success',$redirect));
    exit;
}
add_action('admin_post_nopriv_alify_submit','alify_handle_form_submission');
add_action('admin_post_alify_submit','alify_handle_form_submission');

function alify_submission_columns($columns) { return array('cb'=>$columns['cb'],'title'=>__('Entry','alify'),'alify_form'=>__('Form','alify'),'date'=>__('Received','alify')); }
add_filter('manage_alify_submission_posts_columns','alify_submission_columns');
function alify_submission_column_content($column,$post_id) {
    if ($column==='alify_form') { echo esc_html(ucfirst(get_post_meta($post_id,'_alify_form_type',true))); }
}
add_action('manage_alify_submission_posts_custom_column','alify_submission_column_content',10,2);
function alify_submission_meta_box() { add_meta_box('alify-submission-data',__('Submission Details','alify'),'alify_submission_meta_box_render','alify_submission','normal','high'); }
add_action('add_meta_boxes','alify_submission_meta_box');
function alify_submission_meta_box_render($post) {
    $data = get_post_meta($post->ID,'_alify_submission_data',true);
    if (!is_array($data)) { echo '<p>' . esc_html__('No form data.','alify') . '</p>'; return; }
    echo '<table class="widefat striped"><tbody>';
    foreach ($data as $key=>$value) { echo '<tr><th>' . esc_html(ucfirst(str_replace('_',' ',$key))) . '</th><td>' . nl2br(esc_html($value)) . '</td></tr>'; }
    echo '</tbody></table><p><strong>' . esc_html__('Form:','alify') . '</strong> ' . esc_html(get_post_meta($post->ID,'_alify_form_type',true)) . '</p>';
}
