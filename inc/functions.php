<?php

/**
 * @package WordpressThemeBoilerplate
 * @subpackage CoreThemeFunctions
 */

/**
 * wpbp_convert_single_line_comments
 * 
 * This is a callback function for preg_replpace_callback to convert single-line
 * comments into multi-line comments
 * 
 * @param array $matches
 * @return string 
 */
function wpbp_convert_single_line_comments($matches) {
    return '<script'.$matches[1].'>'.preg_replace('/^[\s\t]*\/\/(.*)$/m', '/* $1 */', $matches[2]).'</script>';
}

/**
 * wpbp_file_exists_cascade
 * 
 * Checks if a given file exists and if so returns the file name or else returns
 * false. You may also pass an array containing several file paths and the first
 * one which exists will be returned. If none exist then the function will 
 * return false.
 * 
 * @param mixed $files A path to a single file or an array of file paths
 * @param string $prepend Optional. String to be prepended to the path 
 * @return string
 */
function wpbp_file_exists_cascade($files, $prepend = '') {
    if (is_array($files)) {
        foreach ($files as $file) {
            if (file_exists($prepend_path . $file)) {
                return $file;
            }
        }
    } else if (file_exists($prepend_path . $files)) {
        return $files;
    }
    return false;
}

/**
 * wpbp_get_template_file
 * 
 * Runs through the template file hierarchy and returns the appropriate template
 * file to use from a given path.
 * 
 * @param type $path The path to the directory in which to search for the template files.
 * @return string 
 */
function wpbp_get_template_file($path) {
    $path = preg_replace('|[\\/]+?$|', '', $path);
    
    $check_files = array();
    
    if (is_front_page()) {
        $check_files[] = 'front-page.php';
    }
    
    if (is_404()) {
        $check_files[] = '404.php';
    } else if (is_search()) {
        $check_files[] = 'search.php';
    } else if (is_archive()) {
        if (is_tax()) {
            $taxonomy = get_query_var('taxonomy');
            $term = get_query_var($taxonomy);

            $check_files[] = 'taxonomy-' . $taxonomy . '-' . $term . '.php';
            $check_files[] = 'taxonomy-' . $taxonomy . '.php';
            $check_files[] = 'taxonomy.php';
        } else if (is_category()) {
            $category = get_category(get_query_var('cat'));
            $id = $category->cat_ID;
            $slug = $category->slug;

            $check_files[] = 'category-' . $slug . '.php';
            $check_files[] = 'category-' . $id . '.php';
            $check_files[] = 'category.php';
        } else if (is_tag()) {
            $id = get_query_var('tag_id');
            $slug = get_query_var('tag');

            $check_files[] = 'tag-' . $slug . '.php';
            $check_files[] = 'tag-' . $id . '.php';
            $check_files[] = 'tag.php';
        } else if (is_author()) {
            $author = get_userdata(get_query_var('author'));
            $id = $author->ID;
            $nicename = $author->data->user_nicename;
                    
            $check_files[] = 'author-' . $nicename . '.php';
            $check_files[] = 'author-' . $id . '.php';
            $check_files[] = 'author.php';
        } else if (is_date()) {
            $check_files[] = 'date.php';
        } else if (is_post_type_archive()) {
            // TODO: This needs to be verified
            $post = get_post(get_the_ID());
            $post_type = $post->post_type;
            
            $check_files[] = 'archive-' . $post_type . '.php';
        }
        
        $check_files[] = 'archive.php';
    } else if (is_single()) {
        if (is_attachment()) {
            $mime_type = get_post_mime_type(get_the_ID());
            $mime_type_pieces = explode('/', $mime_type);
            
            foreach ($mime_type_pieces as $mime_type_piece) {
                $check_files[] = $mime_type_piece . '.php';
            }
            
            $check_files[] = str_replace('/', '_', $mime_type) . '.php';
            $check_files[] = 'attachment.php';
        }
        
        $post = get_post(get_the_ID());
        $post_type = $post->post_type;

        $check_files[] = 'single-' . $post_type . '.php';
        $check_files[] = 'single.php';
    } else if (is_page()) {
        $page = get_post(get_the_ID());
        $slug = $page->post_name;
        $id = $page->ID;
        
        $template_name = get_post_meta(get_the_ID(), '_wp_page_template', true);
        
        if (strpos('.php', $template_name) !== false) {
            $check_files[] = $template_name;
        }
        
        $check_files[] = 'page-' . $slug . '.php';
        $check_files[] = 'page-' . $id . '.php';
        $check_files[] = 'page.php';
    } else if (is_comments_popup()) {
        $check_files[] = 'comments-popup.php';
    }
    
    if (is_home()) {
        $check_files[] = 'home.php';
    }
    
    $check_files[] = 'index.php';
    
    $file = wpbp_file_exists_cascade($check_files, $path . '/');
    
    return $path . '/' . $file;
}

/**
 * wpbp_get_the_content_filtered
 * 
 * Retrieves the filtered post content.
 * 
 * @param string $template_path Optional. Content for when there is more text.
 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is false.
 * @return string 
 */
function wpbp_get_the_content_filtered($template_path = null, $stripteaser = false) {
    $content = get_the_content($template_path, $stripteaser);
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    return $content;
}

/**
 * wpbp_minify_html
 *
 * This function minifies HTML code by stripping new lines and excess
 * whitespace. Due to the fact that new lines are removed, single-line comments
 * within the &lt;script&gt; tag are converted to multi-line comments
 *
 * @param string $html The HTML to be minified.
 * @return string
 */
function wpbp_minify_html($html) {
    // Convert open-ended single-line comments to close-ended multi-line comments
    $html = preg_replace_callback('#<script(.*?)>(.*?)</script>#si', 'wpbp_convert_single_line_comments', $html);

    $patterns = array(
        '/[\r\n]/', // Remove new lines
        '/[\s\t]+/', // Remove extra whitespace
    );

    $replacements = array(
        '',
        ' ',
    );

    return preg_replace($patterns, $replacements, $html);
}

/**
 * wpbp_sluggify
 * 
 * Removes special characters from a string.
 * 
 * @param string $string The string to be sluggified.
 * @return string
 */
function wpbp_sluggify($string) {
    $string = preg_replace('/[\s]+/', '-', $string);
    $string = preg_replace('/[^A-Z0-9_-]/i', '', $string);
    return strtolower($string);
}