<?php
/*
Template Name: Best Fonts for Branding
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-bullseye',
        'title'          => 'Best Fonts for Branding',
        'summary'        => 'A dynamic collection of fonts for brand systems, client identities, campaign work, packaging, and broader visual positioning.',
        'side_title'     => 'A practical collection for client-facing work.',
        'side_copy'      => 'This page pulls from the Branding use-case branch, so it stays focused on fonts with identity and positioning value.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-bullseye',
                'text' => 'Use Case: Branding',
            ),
            array(
                'icon' => 'fa-solid fa-briefcase',
                'text' => 'Client work',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Branding',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Branding use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'branding' ),
            ),
        ),
    )
);
