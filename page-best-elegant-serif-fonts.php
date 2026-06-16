<?php
/*
Template Name: Best Elegant Serif Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-feather-pointed',
        'title'          => 'Best Elegant Serif Fonts',
        'summary'        => 'Elegant serif fonts for luxury identities, editorial layouts, fashion systems, packaging, and polished brand work.',
        'side_title'     => 'Refined serifs for premium work.',
        'side_copy'      => 'These picks keep refined serif options together for premium visual work.',
        'intro_title'    => 'Elegant serifs are useful when the design needs authority and polish.',
        'intro_copy'     => 'This collection focuses on serif fonts that feel refined enough for premium brands, magazines, beauty, fashion, restaurants, packaging, and editorial identity work.',
        'intro_points'   => array(
            'Good for luxury, fashion, editorial, and packaging.',
            'Focused on refined serif options with an elegant tone.',
            'Best when tone matters as much as readability.',
        ),
        'related_slugs'  => array( 'trending-commercial-fonts', 'best-fonts-for-branding', 'best-wedding-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-font',
                'text' => 'Style: Serif',
            ),
            array(
                'icon' => 'fa-solid fa-gem',
                'text' => 'Mood: Elegant',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Serif',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Elegant serif',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'serif' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'elegant' ),
            ),
        ),
    )
);
