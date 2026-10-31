<?php
if (!defined('ABSPATH')) { exit; }

/** Refresh dynamic service-category routes once after a theme version change. */
function alify_maybe_flush_rewrite_rules() {
    $stored=get_option('alify_rewrite_version');
    if ($stored!==ALIFY_VERSION) {
        flush_rewrite_rules(false);
        update_option('alify_rewrite_version',ALIFY_VERSION,false);
    }
}
add_action('init','alify_maybe_flush_rewrite_rules',99);
