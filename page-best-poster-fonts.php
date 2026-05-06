<?php
/*
Template Name: Best Poster Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-rectangle-ad',
        'title'          => 'Best Poster Fonts',
        'summary'        => 'A dynamic collection of poster fonts for campaigns, covers, announcements, display typography, and high-impact compositions.',
        'side_title'     => 'Built for expressive display decisions.',
        'side_copy'      => 'This page pulls from the Poster use-case branch, keeping the collection useful for loud, visual, and campaign-driven typography.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-rectangle-ad',
                'text' => 'Use Case: Poster',
            ),
            array(
                'icon' => 'fa-solid fa-bolt',
                'text' => 'Display impact',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Poster',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Poster use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'poster' ),
            ),
        ),
    )
);
