<?php

// Check if this is an AJAX request and if so handle appropriately
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $function_path = THEME_PATH . '/inc/ajax/functions/' . $_REQUEST['_function'] . '.php';
    if (file_exists($function_path)) {
        include($function_path);
    } else {            
        $template_path = THEME_PATH . '/inc/ajax/templates/' . get_post_type() . '.php';
        if (file_exists($template_path)) {
            include($template_path);
        } else {
            $return = array();
            
            while(have_posts()) {
                the_post();
                
                $p = get_post(get_the_ID());

                // Ensures that the filtered post content is populated
                $p->post_content_filtered = wpbp_get_the_content_filtered();
                
                $return[] = $p;
            }
            
            header('Content-type: application/json');
            echo(json_encode($return));
        }
    }
    exit;
}