<?php
/*
Template Name: Best Modern Sans Serif Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-circle-half-stroke',
        'title'          => 'Best Modern Sans Serif Fonts',
        'summary'        => 'Modern sans serif fonts for interfaces, brands, editorial systems, and clean digital products.',
        'side_title'     => 'Clean structure, current visual tone.',
        'side_copy'      => 'These picks favor clean sans serif type with a contemporary feel.',
        'intro_title'    => 'Modern sans serif fonts are the safest starting point for clean systems.',
        'intro_copy'     => 'Use this collection when you need contemporary typography for interfaces, brands, editorial layouts, apps, dashboards, and product pages without making the design feel overly decorative.',
        'intro_points'   => array(
            'Good for UI, web, branding, and editorial systems.',
            'Focused on clean sans serif type with a modern feel.',
            'Useful when clarity and current visual tone matter.',
        ),
        'related_slugs'  => array( 'best-minimal-fonts', 'best-logo-fonts', 'best-fonts-for-branding' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-font',
                'text' => 'Style: Sans Serif',
            ),
            array(
                'icon' => 'fa-solid fa-circle-half-stroke',
                'text' => 'Mood: Modern',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Sans Serif',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Modern sans',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'sans-serif', 'sansserif' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'modern' ),
            ),
        ),
    )
);
