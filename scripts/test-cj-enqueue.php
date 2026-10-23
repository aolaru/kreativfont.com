<?php

// Exercise the real theme enqueue/filter code without a WordPress installation.
$scripts = array();
$inline  = array();
$data    = array();
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function get_template_directory() { return dirname( __DIR__ ); }
function wp_enqueue_script( $handle, $src, ...$args ) {
    $GLOBALS['scripts'][ $handle ] = $src;
}
function wp_add_inline_script( $handle, $code, $position = 'after' ) {
    $GLOBALS['inline'][ $handle ] = array( 'code' => $code, 'position' => $position );
}
function wp_script_add_data( $handle, $key, $value ) {
    $GLOBALS['data'][ $handle ][ $key ] = $value;
}
require dirname( __DIR__ ) . '/inc/assets.php';
kreativ_enqueue_affiliate_assets();
$handle = 'kreativ-cj-affiliate';
if ( ( $inline[ $handle ]['position'] ?? '' ) !== 'before'
    || false === strpos( $inline[ $handle ]['code'] ?? '', 'kreativCjImpressionsConfigured' )
    || ( $data[ $handle ]['strategy'] ?? '' ) !== 'async' ) {
    throw new RuntimeException( 'CJ must have compatibility code before its native async loader.' );
}
$tag = '<script id="kreativ-cj-affiliate-js-before">' . $inline[ $handle ]['code'] . '</script>'
    . '<script src="' . $scripts[ $handle ] . '" id="kreativ-cj-affiliate-js" async></script>';
if ( kreativ_script_loader_tag( $tag, $handle, $scripts[ $handle ] ) !== $tag ) {
    throw new RuntimeException( 'Theme tag filter must preserve CJ inline compatibility code.' );
}
echo "PASS: CJ setup precedes native async loader and survives the theme tag filter\n";
