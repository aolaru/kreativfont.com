<?php
/*
Template Name: Best Poster Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-rectangle-ad',
        'title'          => 'Best Poster Fonts',
        'summary'        => 'Poster fonts for campaigns, covers, announcements, display typography, and high-impact compositions.',
        'side_title'     => 'Expressive display choices.',
        'side_copy'      => 'These picks favor loud, visual, and campaign-driven typography.',
        'intro_title'    => 'Poster fonts need impact before detail.',
        'intro_copy'     => 'Use this collection for layouts where type has to carry the composition: posters, covers, announcements, social graphics, and campaign headlines.',
        'intro_points'   => array(
            'Good for display typography and campaign visuals.',
            'Focused on poster and campaign use.',
            'Prioritize scale, contrast, and immediate recognition.',
        ),
        'related_slugs'  => array( 'best-display-fonts', 'best-condensed-fonts', 'best-logo-fonts' ),
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
                'text' => 'Updated regularly',
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
