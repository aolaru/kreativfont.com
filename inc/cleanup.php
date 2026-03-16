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
