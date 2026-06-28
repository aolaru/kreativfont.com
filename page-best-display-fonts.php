<?php
/*
Template Name: Best Display Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-heading',
        'title'          => 'Best Display Fonts',
        'summary'        => 'Display fonts for headlines, posters, covers, campaign visuals, and high-impact brand moments.',
        'side_title'     => 'Built for first impressions.',
        'side_copy'      => 'These picks focus on typefaces that can carry large, visible compositions.',
        'intro_title'    => 'Display fonts work when the type is part of the visual idea.',
        'intro_copy'     => 'Use this collection for headlines, posters, covers, social graphics, landing pages, and brand moments where the font needs to be noticed quickly.',
        'intro_points'   => array(
            'Good for headlines, covers, posters, and campaign graphics.',
            'Focused on display type with strong visual character.',
            'Best for short text, not long-form reading.',
        ),
        'related_slugs'  => array( 'best-poster-fonts', 'best-logo-fonts', 'best-condensed-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-heading',
                'text' => 'Style: Display',
            ),
            array(
                'icon' => 'fa-solid fa-bolt',
                'text' => 'High impact',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Display',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Display style',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'display', 'display-font-type' ),
            ),
        ),
    )
);
