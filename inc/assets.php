<?php

function kreativ_asset_version( $relative_path ) {
    $absolute_path = get_template_directory() . $relative_path;

    if ( file_exists( $absolute_path ) ) {
        return (string) filemtime( $absolute_path );
    }

    return null;
}

function kreativ_jquery_loading() {
    if ( ! is_admin() ) {
        wp_deregister_script( 'jquery' );
        wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js', array(), null, true );
        wp_enqueue_script( 'jquery' );
    }
}
add_action( 'wp_enqueue_scripts', 'kreativ_jquery_loading' );

function kreativ_enqueue_lazyload() {
    wp_enqueue_script(
        'lazysizes',
        'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js',
        array(),
        '5.3.2',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'kreativ_enqueue_lazyload' );

function kreativ_enqueue_theme_assets() {
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    wp_enqueue_style(
        'kreativ-styles',
        get_template_directory_uri() . '/assets/dist/main.min.css',
        array( 'font-awesome' ),
        kreativ_asset_version( '/assets/dist/main.min.css' )
    );

    wp_enqueue_style(
        'kreativ-header',
        get_template_directory_uri() . '/css/kreativ-header.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-header.css' )
    );

    wp_enqueue_style(
        'kreativ-pages',
        get_template_directory_uri() . '/css/kreativ-pages.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-pages.css' )
    );

    wp_enqueue_style(
        'kreativ-footer',
        get_template_directory_uri() . '/css/kreativ-footer.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-footer.css' )
    );

    wp_enqueue_style(
        'kreativ-cards',
        get_template_directory_uri() . '/css/kreativ-cards.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-cards.css' )
    );

    if ( is_page_template( 'template-filter-all.php' ) || is_page_template( 'template-filter-market.php' ) || is_page_template( 'template-filter-free.php' ) || is_page_template( 'page-collections.php' ) || is_page( 'collections' ) || is_404() ) {
        wp_enqueue_style(
            'kreativ-home',
            get_template_directory_uri() . '/css/kreativ-home.css',
            array( 'kreativ-styles' ),
            kreativ_asset_version( '/css/kreativ-home.css' )
        );
    }

    if ( is_category() || is_tag() ) {
        wp_enqueue_style(
            'kreativ-archive',
            get_template_directory_uri() . '/css/kreativ-archive.css',
            array( 'kreativ-styles' ),
            kreativ_asset_version( '/css/kreativ-archive.css' )
        );
    }

    wp_register_script(
        'init',
        get_template_directory_uri() . '/assets/assets/components/init.js',
        array( 'jquery' ),
        kreativ_asset_version( '/assets/assets/components/init.js' ),
        true
    );
    wp_enqueue_script( 'init' );

    wp_enqueue_script(
        'kreativ-search-suggest',
        get_template_directory_uri() . '/js/kreativ-search-suggest.js',
        array(),
        kreativ_asset_version( '/js/kreativ-search-suggest.js' ),
        true
    );

    wp_localize_script(
        'kreativ-search-suggest',
        'kreativSearchSuggest',
        array(
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'minChars'         => 2,
            'nonce'            => wp_create_nonce( 'kreativ_search_suggest' ),
            'searchResultsUrl' => home_url( '/' ),
            'labels'           => array(
                'fonts'    => 'Fonts',
                'designer' => 'Designers',
                'foundry'  => 'Foundries',
                'style'    => 'Styles',
                'mood'     => 'Moods',
                'useCase'  => 'Use Cases',
                'viewAll'  => 'View all results',
                'empty'    => 'No quick matches yet.',
            ),
        )
    );

    if ( is_singular( 'post' ) || is_front_page() || is_tag() || is_category() ) {
        wp_dequeue_style( 'edd-styles' );
        wp_dequeue_script( 'edd-ajax' );
    }

    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_enqueue_scripts', 'kreativ_enqueue_theme_assets' );
