<?php

function kreativ_handle_showcase_conversion() {
    if ( ! isset( $_GET['convert_showcases'] ) ) {
        return;
    }

    $query = new WP_Query(
        array(
            'post_type'      => 'kreativ_showcase',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        )
    );

    $count = 0;

    foreach ( $query->posts as $post ) {
        wp_update_post(
            array(
                'ID'        => $post->ID,
                'post_type' => 'post',
            )
        );
        $count++;
    }

    echo "<div class='notice notice-success'><p>Converted {$count} showcase posts to regular posts.</p></div>";
}
add_action( 'admin_init', 'kreativ_handle_showcase_conversion' );
