<?php
/*
Template Name: Best Wedding Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-ring',
        'title'          => 'Best Wedding Fonts',
        'summary'        => 'Wedding fonts for invitations, stationery, event branding, romantic layouts, and elegant print work.',
        'side_title'     => 'Romantic type without broad browsing.',
        'side_copy'      => 'These picks make decorative, elegant, and event-ready type easier to compare.',
        'intro_title'    => 'Wedding fonts should feel personal, but still readable.',
        'intro_copy'     => 'Use this collection for invitations, stationery, menus, signage, and event branding where elegance, warmth, and a crafted mood matter.',
        'intro_points'   => array(
            'Good for invitations, stationery, and event branding.',
            'Focused on invitations and event design.',
            'Pair decorative scripts with simpler supporting type.',
        ),
        'related_slugs'  => array( 'best-handwritten-fonts', 'best-vintage-script-fonts', 'best-elegant-serif-fonts' ),
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
                'text' => 'Updated regularly',
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
