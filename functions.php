<?php

$kreativ_includes = array(
    '/inc/setup.php',
    '/inc/assets.php',
    '/inc/cleanup.php',
    '/inc/login.php',
    '/inc/admin.php',
    '/inc/shortcodes.php',
    '/inc/template-helpers.php',
    '/inc/search.php',
    '/inc/seo.php',
);

foreach ( $kreativ_includes as $kreativ_include ) {
    $kreativ_include_path = get_template_directory() . $kreativ_include;

    if ( file_exists( $kreativ_include_path ) ) {
        require_once $kreativ_include_path;
    }
}
