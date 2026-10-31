<?php
/** Theme fallback template. */
get_header();
if (have_posts()) :
    echo '<section class="alify-section"><div class="container"><div class="row g-4">';
    while (have_posts()) : the_post();
        echo '<div class="col-md-6"><article class="alify-card"><div class="alify-card-body"><h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2><p>' . esc_html(get_the_excerpt()) . '</p></div></article></div>';
    endwhile;
    echo '</div>'; the_posts_pagination(); echo '</div></section>';
else :
    echo '<section class="alify-section"><div class="container"><p>' . esc_html__('No content found.','alify') . '</p></div></section>';
endif;
get_footer();
