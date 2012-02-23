<?php

// Define constants
define('THEME_PATH', dirname(__FILE__));
define('THEME_URL', get_bloginfo('template_url'));
define('BLOG_URL', site_url());

// Flush the rewrite rules
if (isset($_REQUEST['__flush_rewrite'])) {
    update_option('rewrite_rules', '');
}

// Required files
require_once(THEME_PATH . '/inc/functions.php');

// Remove the meta generator tag
remove_action('wp_head', 'wp_generator');

// Hide the admin bar from template
add_filter('show_admin_bar', '__return_false');