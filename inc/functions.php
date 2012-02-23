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
 * @param mixed $files A path to a single file or 
 * @return string
 */
function wpbp_file_exists_cascade($files) {
    if (is_array($files)) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
    } else if (file_exists($files)) {
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
 * @param type $path
 * @return string 
 */
function wpbp_get_template_file($path) {
    $path = preg_replace('|[\\/]+?$|', '', $path);
    
    if (is_404()) {
        $file = wpbp_file_exists_cascade($path . '/404.php');
    } else if (is_search()) {
        $file = wpbp_file_exists_cascade($path . '/search.php');
    } else if (is_tax()) {
        $taxonomy = get_query_var('taxonomy');
        $term = get_query_var($taxonomy);
        
        $file = wpbp_file_exists_cascade(array(        
            $path . '/taxonomy-' . $taxonomy . '-' . $term . '.php',
            $path . '/taxonomy-' . $taxonomy . '.php',
            $path . '/taxonomy.php',
            $path . '/archive.php',
        ));
    } else if (is_category()) {
        $category = get_category(get_query_var('cat'));
        $id = $category->cat_ID;
        $slug = $category->slug;
        
        $file = wpbp_file_exists_cascade(array(
            $path . '/category-' . $slug . '.php',
            $path . '/category-' . $id . '.php',
            $path . '/category.php',
            $path . '/archive.php',
        ));
    }
    
    if (!$file) $file = $path . '/index.php';
    
    return $file;
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
 * @param string $html
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
 * @param string $string
 * @return string
 */
function wpbp_sluggify($string) {
    $string = preg_replace('/[\s]+/', '-', $string);
    $string = preg_replace('/[^A-Z0-9_-]/i', '', $string);
    return strtolower($string);
}