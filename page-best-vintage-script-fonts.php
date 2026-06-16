<?php
/*
Template Name: Best Vintage Script Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-pen-nib',
        'title'          => 'Best Vintage Script Fonts',
        'summary'        => 'Vintage script fonts for nostalgic branding, labels, invitations, and display work.',
        'side_title'     => 'Useful when style and mood matter together.',
        'side_copy'      => 'These picks keep hand-lettered, retro script options easier to compare.',
        'intro_title'    => 'Vintage script fonts work best when the mood is intentional.',
        'intro_copy'     => 'Use this collection for nostalgic marks, packaging, labels, wedding pieces, and display work where a hand-lettered or retro voice is part of the concept.',
        'intro_points'   => array(
            'Good for labels, invitations, retro brands, and packaging.',
            'Focused on script styles with a vintage feel.',
            'Best for display use, not long-form reading.',
        ),
        'related_slugs'  => array( 'best-wedding-fonts', 'best-logo-fonts', 'best-poster-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-pen-nib',
                'text' => 'Style: Script',
            ),
            array(
                'icon' => 'fa-solid fa-clock-rotate-left',
                'text' => 'Mood: Vintage',
            ),
            array(
                'icon' => 'fa-solid fa-tags',
                'text' => 'Focused results',
            ),
        ),
        'badge_text'     => 'Script',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Vintage script',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'script' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'vintage' ),
            ),
        ),
    )
);
