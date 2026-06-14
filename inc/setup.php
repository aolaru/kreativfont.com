<?php

function kreativ_font_setup() {
    add_theme_support( 'menus' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );

    if ( function_exists( 'register_nav_menu' ) ) {
        register_nav_menu( 'primary', 'Primary Menu' );
    }
}
add_action( 'after_setup_theme', 'kreativ_font_setup' );

function kreativ_add_collections_to_primary_menu( $items, $args ) {
    if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
        return $items;
    }

    if ( preg_match( '#href=["\'][^"\']*/collections/?["\']#', $items ) ) {
        return $items;
    }

    $request_path = '';

    if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
        $request_path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
        $request_path = untrailingslashit( '/' . ltrim( (string) $request_path, '/' ) );
    }

    $is_current        = '/collections' === $request_path || 0 === strpos( $request_path, '/collections/' );
    $collections_class = 'menu-item menu-item-kreativ-collections';
    $aria_current      = '';

    if ( $is_current ) {
        $collections_class .= ' current-menu-item current_page_item';
        $aria_current       = ' aria-current="page"';
    }

    $collections_item = sprintf(
        '<li class="%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
        esc_attr( $collections_class ),
        esc_url( home_url( '/collections' ) ),
        $aria_current,
        esc_html__( 'Collections', 'kreativfontcom' )
    );

    $items_with_collections = preg_replace(
        '#(<li[^>]*>\s*<a[^>]+href=["\'][^"\']*/fonts/?["\'][^>]*>.*?</a>\s*</li>)#s',
        '$1' . $collections_item,
        $items,
        1,
        $replacement_count
    );

    if ( 0 < $replacement_count ) {
        return $items_with_collections;
    }

    return $items . $collections_item;
}
add_filter( 'wp_nav_menu_items', 'kreativ_add_collections_to_primary_menu', 10, 2 );

function kreativ_font_set_content_width() {
    $GLOBALS['content_width'] = 900;
}
add_action( 'after_setup_theme', 'kreativ_font_set_content_width', 0 );

if ( ! defined( 'EDD_SLUG' ) ) {
    define( 'EDD_SLUG', 'market' );
}
