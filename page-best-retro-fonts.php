<?php
/*
Template Name: Best Retro Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Mood collection',
        'eyebrow_icon'   => 'fa-solid fa-clock-rotate-left',
        'title'          => 'Best Retro Fonts',
        'summary'        => 'Retro fonts for nostalgic brands, posters, packaging, labels, and vintage-inspired display work.',
        'side_title'     => 'Nostalgia with a clear job.',
        'side_copy'      => 'These picks focus on fonts that bring a retro mood without losing practical use.',
        'intro_title'    => 'Retro fonts need the right reference point.',
        'intro_copy'     => 'Use this collection for throwback branding, posters, labels, packaging, editorial headlines, and campaign visuals where nostalgia is part of the concept.',
        'intro_points'   => array(
            'Good for labels, posters, packaging, and throwback brands.',
            'Focused on retro and vintage-inspired moods.',
            'Best when the era reference supports the project.',
        ),
        'related_slugs'  => array( 'best-vintage-script-fonts', 'best-display-fonts', 'best-packaging-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-clock-rotate-left',
                'text' => 'Mood: Retro',
            ),
            array(
                'icon' => 'fa-solid fa-clock-rotate-left',
                'text' => 'Nostalgic feel',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Retro',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Retro mood',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'retro', 'retro-font-mood', 'vintage' ),
            ),
        ),
    )
);
