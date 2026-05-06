<?php
/*
Template Name: Best Wedding Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-ring',
        'title'          => 'Best Wedding Fonts',
        'summary'        => 'A dynamic collection of wedding fonts for invitations, stationery, event branding, romantic layouts, and elegant print work.',
        'side_title'     => 'Romantic type without broad browsing.',
        'side_copy'      => 'This page pulls from the Wedding use-case branch, making it easier to find decorative, elegant, and event-ready type.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-ring',
                'text' => 'Use Case: Wedding',
            ),
            array(
                'icon' => 'fa-solid fa-envelope-open-text',
                'text' => 'Invitations and stationery',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Wedding',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Wedding use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'wedding' ),
            ),
        ),
    )
);
