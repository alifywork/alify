<?php
if (!defined('ABSPATH')) { exit; }
// WordPress gives front-page.php priority over assigned page templates. Reuse the named Home template.
require ALIFY_DIR . '/templates/pages/home.php';
