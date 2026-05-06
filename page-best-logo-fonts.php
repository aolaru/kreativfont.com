<?php
/*
Template Name: Best Logo Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-signature',
        'title'          => 'Best Logo Fonts',
        'summary'        => 'A dynamic collection of fonts suited for logos, wordmarks, identity systems, and distinctive brand marks.',
        'side_title'     => 'Fast entry point for identity work.',
        'side_copy'      => 'This page pulls from the Logo use-case branch, keeping the collection aligned with branding and mark-making intent.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-signature',
                'text' => 'Use Case: Logo',
            ),
            array(
                'icon' => 'fa-solid fa-bullseye',
                'text' => 'Identity focused',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Logo',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Logo use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'logo' ),
            ),
        ),
    )
);
