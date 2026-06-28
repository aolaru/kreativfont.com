<?php
/*
Template Name: Best Condensed Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-compress',
        'title'          => 'Best Condensed Fonts',
        'summary'        => 'Condensed fonts for compact headlines, posters, packaging, editorial systems, and tight layouts.',
        'side_title'     => 'More impact in less width.',
        'side_copy'      => 'These picks focus on narrow type choices that keep layouts compact and direct.',
        'intro_title'    => 'Condensed fonts help when space and impact need to work together.',
        'intro_copy'     => 'Use this collection for posters, labels, editorial headlines, packaging systems, dashboards, and layouts where the font needs to fit tight spaces without feeling weak.',
        'intro_points'   => array(
            'Good for compact headlines, labels, posters, and editorial layouts.',
            'Focused on narrow, condensed, and compressed type.',
            'Check spacing carefully at small sizes.',
        ),
        'related_slugs'  => array( 'best-display-fonts', 'best-poster-fonts', 'best-logo-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-compress',
                'text' => 'Style: Condensed',
            ),
            array(
                'icon' => 'fa-solid fa-compress',
                'text' => 'Compact width',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Condensed',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Condensed style',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'condensed', 'narrow', 'compressed', 'narrow-type' ),
            ),
        ),
    )
);
