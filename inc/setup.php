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

function kreativ_font_set_content_width() {
    $GLOBALS['content_width'] = 900;
}
add_action( 'after_setup_theme', 'kreativ_font_set_content_width', 0 );

if ( ! defined( 'EDD_SLUG' ) ) {
    define( 'EDD_SLUG', 'market' );
}
