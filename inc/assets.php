<?php

function kreativ_asset_version( $relative_path ) {
    $absolute_path = get_template_directory() . $relative_path;

    if ( file_exists( $absolute_path ) ) {
        return (string) filemtime( $absolute_path );
    }

    return null;
}

function kreativ_add_script_attributes( $handle, $attributes = array() ) {
    global $kreativ_script_attributes;

    if ( ! is_array( $kreativ_script_attributes ) ) {
        $kreativ_script_attributes = array();
    }

    $kreativ_script_attributes[ $handle ] = $attributes;
}

function kreativ_script_loader_tag( $tag, $handle, $src ) {
    global $kreativ_script_attributes;

    if ( empty( $kreativ_script_attributes[ $handle ] ) || empty( $src ) ) {
        return $tag;
    }

    $attributes = array_merge(
        array(
            'id'  => $handle . '-js',
            'src' => $src,
        ),
        $kreativ_script_attributes[ $handle ]
    );
    $output = array();

    foreach ( $attributes as $name => $value ) {
        if ( ! preg_match( '/^[a-zA-Z][a-zA-Z0-9:-]*$/', (string) $name ) || false === $value || null === $value ) {
            continue;
        }

        if ( true === $value ) {
            $output[] = esc_attr( $name );
            continue;
        }

        $output[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( $value ) );
    }

    return '<script ' . implode( ' ', $output ) . '></script>';
}
add_filter( 'script_loader_tag', 'kreativ_script_loader_tag', 10, 3 );

function kreativ_is_dynamic_collection_template() {
    // Virtual collection routes are resolved in template_include before the
    // header runs, so they do not have a WordPress page-template slug.
    if ( ! empty( $GLOBALS['kreativ_virtual_collection_url'] ) ) {
        return true;
    }

    $template_slug = (string) get_page_template_slug();

    return 'page-trending-commercial-fonts.php' === $template_slug || 0 === strpos( $template_slug, 'page-best-' );
}

function kreativ_should_load_lazyload() {
    return is_front_page()
        || is_home()
        || is_archive()
        || is_search()
        || is_404()
        || is_singular( 'post' )
        || kreativ_is_dynamic_collection_template()
        || is_page_template( array( 'template-filter-all.php', 'template-filter-market.php', 'template-filter-free.php' ) );
}

function kreativ_should_load_search_suggest() {
    if ( is_page( array( 'about', 'contact', 'privacy-policy', 'terms-of-use', 'updates' ) ) ) {
        return false;
    }

    return is_front_page()
        || is_home()
        || is_archive()
        || is_search()
        || is_404()
        || is_singular( 'post' )
        || is_page( array( 'fonts', 'tools', 'collections' ) )
        || kreativ_is_dynamic_collection_template()
        || is_page_template( array( 'template-filter-all.php', 'template-filter-market.php', 'template-filter-free.php', 'template-tools-page.php' ) );
}

function kreativ_should_load_page_styles() {
    if ( is_page_template( array( 'template-filter-all.php', 'template-filter-market.php' ) ) ) {
        return false;
    }

    return is_singular()
        || is_search()
        || is_404()
        || kreativ_is_dynamic_collection_template();
}

function kreativ_should_load_card_styles() {
    return is_front_page()
        || is_home()
        || is_archive()
        || is_search()
        || is_404()
        || kreativ_is_dynamic_collection_template()
        || is_page_template( array( 'template-filter-all.php', 'template-filter-market.php', 'template-filter-free.php' ) );
}

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

    // Keep the first rendered layout aligned with the enhanced header and cards.
    // This is deliberately attached to the base stylesheet: a cached or delayed
    // component stylesheet must not change the document's geometry after paint.
    wp_add_inline_style(
        'kreativ-styles',
        ':root{--kf-fixed-header-offset:72px}' .
        'body{padding-top:var(--kf-fixed-header-offset)}' .
        '.kreativ-header{box-sizing:border-box;min-height:var(--kf-fixed-header-offset);padding:0!important}' .
        '.kreativ-header .navbar{box-sizing:border-box;min-height:var(--kf-fixed-header-offset);height:var(--kf-fixed-header-offset)}' .
        '.kreativ-hdr-right{min-height:44px}' .
        '.kreativ-card-media{aspect-ratio:16/9;background:#f6f8ff}' .
        '.kreativ-card-media>img{display:block;width:100%;height:100%;object-fit:cover}' .
        '@media(max-width:991.98px){body{padding-top:0!important}.kreativ-header{position:sticky;top:0}.kreativ-content{padding-top:.85rem!important}}'
    );

    wp_enqueue_style(
        'kreativ-header',
        get_template_directory_uri() . '/css/kreativ-header.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-header.css' )
    );

    if ( kreativ_should_load_page_styles() ) {
        wp_enqueue_style(
            'kreativ-pages',
            get_template_directory_uri() . '/css/kreativ-pages.css',
            array( 'kreativ-styles' ),
            kreativ_asset_version( '/css/kreativ-pages.css' )
        );
    }

    wp_enqueue_style(
        'kreativ-footer',
        get_template_directory_uri() . '/css/kreativ-footer.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-footer.css' )
    );

    if ( kreativ_should_load_card_styles() ) {
        wp_enqueue_style(
            'kreativ-cards',
            get_template_directory_uri() . '/css/kreativ-cards.css',
            array( 'kreativ-styles' ),
            kreativ_asset_version( '/css/kreativ-cards.css' )
        );
    }

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
        array(),
        kreativ_asset_version( '/assets/assets/components/init.js' ),
        true
    );
    wp_enqueue_script( 'init' );
    kreativ_add_script_attributes( 'init', array( 'defer' => true ) );

    if ( is_singular( 'post' ) && 'font' === kreativ_get_single_content_kind( get_queried_object() ) ) {
        wp_enqueue_script(
            'kreativ-share',
            get_template_directory_uri() . '/js/kreativ-share.js',
            array(),
            kreativ_asset_version( '/js/kreativ-share.js' ),
            true
        );
        kreativ_add_script_attributes( 'kreativ-share', array( 'defer' => true ) );

        wp_enqueue_script(
            'kreativ-research-board',
            get_template_directory_uri() . '/js/kreativ-research-board.js',
            array(),
            kreativ_asset_version( '/js/kreativ-research-board.js' ),
            true
        );
        kreativ_add_script_attributes( 'kreativ-research-board', array( 'defer' => true ) );
    }

    if ( kreativ_should_load_lazyload() ) {
        wp_enqueue_script(
            'lazysizes',
            'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js',
            array(),
            '5.3.2',
            true
        );
        kreativ_add_script_attributes( 'lazysizes', array( 'async' => true ) );
    }

    wp_enqueue_script(
        'kreativ-theme-toggle',
        get_template_directory_uri() . '/js/kreativ-theme-toggle.js',
        array(),
        kreativ_asset_version( '/js/kreativ-theme-toggle.js' ),
        true
    );
    kreativ_add_script_attributes( 'kreativ-theme-toggle', array( 'defer' => true ) );

    if ( kreativ_should_load_search_suggest() ) {
        wp_enqueue_script(
            'kreativ-search-suggest',
            get_template_directory_uri() . '/js/kreativ-search-suggest.js',
            array(),
            kreativ_asset_version( '/js/kreativ-search-suggest.js' ),
            true
        );
        kreativ_add_script_attributes( 'kreativ-search-suggest', array( 'defer' => true ) );

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
    }

    if ( is_singular( 'post' ) || is_front_page() || is_tag() || is_category() ) {
        wp_dequeue_style( 'edd-styles' );
        wp_dequeue_script( 'edd-ajax' );
    }

    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_enqueue_scripts', 'kreativ_enqueue_theme_assets' );

function kreativ_is_probable_automated_request() {
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';

    if ( '' === $user_agent ) {
        return false;
    }

    return (bool) preg_match( '/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|pinterestbot|twitterbot|discordbot|whatsapp|telegrambot|headlesschrome|phantomjs|lighthouse/i', $user_agent );
}

function kreativ_enqueue_tracking_assets() {
    if ( kreativ_is_probable_automated_request() ) {
        return;
    }

    wp_enqueue_script(
        'kreativ-google-tag',
        'https://www.googletagmanager.com/gtag/js?id=G-4E74M6PB1Y',
        array(),
        null,
        false
    );
    kreativ_add_script_attributes( 'kreativ-google-tag', array( 'async' => true ) );

    wp_enqueue_script(
        'kreativ-cj-affiliate',
        'https://www.anrdoezrs.net/am/100743026/include/allCj/generate/onLoad/impressions/page/am.js',
        array(),
        null,
        false
    );
    kreativ_add_script_attributes( 'kreativ-cj-affiliate', array( 'async' => true ) );

    wp_enqueue_script(
        'kreativ-cloudflare-web-analytics',
        'https://static.cloudflareinsights.com/beacon.min.js',
        array(),
        null,
        true
    );
    kreativ_add_script_attributes(
        'kreativ-cloudflare-web-analytics',
        array(
            'type'           => 'module',
            'data-cf-beacon' => wp_json_encode( array( 'token' => 'ab7a9c1b54714400a0112acefa6e4479' ) ),
        )
    );

    wp_enqueue_script(
        'kreativ-analytics-events',
        get_template_directory_uri() . '/js/kreativ-analytics-events.js',
        array(),
        filemtime( get_template_directory() . '/js/kreativ-analytics-events.js' ),
        true
    );
    wp_localize_script(
        'kreativ-analytics-events',
        'kreativAnalyticsConfig',
        array(
            'contentType' => is_singular( 'post' ) && function_exists( 'kreativ_is_font_post' ) && kreativ_is_font_post( get_queried_object_id() ) ? 'font' : ( is_404() ? 'not_found' : 'site' ),
            'isNotFound'  => is_404(),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'kreativ_enqueue_tracking_assets', 20 );

function kreativ_print_google_analytics_config() {
    if ( kreativ_is_probable_automated_request() ) {
        return;
    }

    ?>
    <script id="kreativ-google-tag-config">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        if (!navigator.webdriver && !/headless|phantomjs/i.test(navigator.userAgent || '')) {
            gtag('js', new Date());
            gtag('config', 'G-4E74M6PB1Y');
        }
    </script>
    <?php
}
add_action( 'wp_head', 'kreativ_print_google_analytics_config', 5 );
