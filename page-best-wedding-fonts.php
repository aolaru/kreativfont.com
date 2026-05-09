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
        'intro_title'    => 'Wedding fonts should feel personal, but still readable.',
        'intro_copy'     => 'Use this collection for invitations, stationery, menus, signage, and event branding where elegance, warmth, and a crafted mood matter.',
        'intro_points'   => array(
            'Good for invitations, stationery, and event branding.',
            'Pulls from the Wedding use-case branch.',
            'Pair decorative scripts with simpler supporting type.',
        ),
        'related_slugs'  => array( 'best-vintage-script-fonts', 'best-elegant-serif-fonts', 'best-logo-fonts' ),
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
