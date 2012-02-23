<?php

// Check for AJAX requests
include_once(THEME_PATH . '/inc/ajax.php'); // Uncomment to enable AJAX functionality

// Start output buffering
ob_start();
ob_clean();

// Enqueue styles
wp_enqueue_style('normalize', THEME_URL . '/style.css');
wp_enqueue_style('default_stylesheet', THEME_URL . '/css/style.css');

// Enqueue scripts
//wp_enqueue_script('{handle-goes-here}', THEME_URL . '{path-to-file}', array(), false, true); // This is a sample usage of the wp_enqueue_script function

// Load the appropriate page template
ob_start();
ob_clean();
include(wpbp_get_template_file(THEME_PATH . '/inc/templates'));
define(PAGE, ob_get_contents());
ob_end_clean();

?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">

    <title></title>
    <meta name="description" content="">
    
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="imagetoolbar" content="false">
    
    <script src="<?php echo THEME_URL; ?>/js/modernizr-2.5.0.min.js"></script>

    <?php wp_head(); ?>
</head>
<body>
    <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
    
    <?php echo PAGE; ?>
    
    <script src="<?php echo THEME_URL; ?>/js/jquery-1.7.1.min.js"></script>
    
    <?php wp_footer(); ?>
    
    <script>
        var _gaq=[['_setAccount','UX-XXXXXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
</body>
</html>
<?php

$html = ob_get_contents();
ob_end_clean();

if (!isset($_REQUEST['__nominify'])) {
    $html = wpbp_minify_html($html);
}

echo($html);