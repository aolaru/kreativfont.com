<?php
/*
Template Name: Best Free Fonts for Commercial Use
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Free to use',
        'eyebrow_icon'   => 'fa-solid fa-gift',
        'title'          => 'Best Free Fonts for Commercial Use',
        'summary'        => 'A dynamic shortcut to free font posts that are useful for commercial projects, experiments, and lower-friction creative work.',
        'side_title'     => 'Free fonts still need license checks.',
        'side_copy'      => 'This page pulls from the Free Fonts category. Always verify the included license before using a font in client or commercial work.',
        'intro_title'    => 'Free fonts are useful when licensing is clear.',
        'intro_copy'     => 'This collection is designed for quick testing, mockups, and production cases where a free font can work. Always confirm the included license before using any typeface in paid or public-facing work.',
        'intro_points'   => array(
            'Good for experiments, prototypes, and lower-budget projects.',
            'Prioritizes posts from the Free Fonts category.',
            'Check the license before commercial launch.',
        ),
        'related_slugs'  => array( 'best-modern-sans-serif-fonts', 'best-logo-fonts', 'best-fonts-for-branding' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-gift',
                'text' => 'Free Fonts category',
            ),
            array(
                'icon' => 'fa-solid fa-briefcase',
                'text' => 'Commercial-use focused',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates as new fonts publish',
            ),
        ),
        'badge_text'     => 'Free',
        'badge_slug'     => 'free-fonts',
        'context_note'   => 'Free font',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'free_mode'      => 'include',
    )
);
