<?php

function kreativ_remove_comments_rss() {
    return;
}
add_filter( 'post_comments_feed_link', 'kreativ_remove_comments_rss' );

remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

function kreativ_disable_jetpack_sharing_on_single( $show, $post ) {
    if ( is_singular( 'post' ) ) {
        return false;
    }

    return $show;
}
add_filter( 'sharing_show', 'kreativ_disable_jetpack_sharing_on_single', 10, 2 );
