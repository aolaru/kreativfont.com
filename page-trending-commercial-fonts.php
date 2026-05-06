<?php
/*
Template Name: Trending Commercial Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Commercial picks',
        'eyebrow_icon'   => 'fa-solid fa-chart-line',
        'title'          => 'Trending Commercial Fonts',
        'summary'        => 'A dynamic collection of commercial fonts, refreshed from the current Kreativ Font library and ranked by active site signals.',
        'side_title'     => 'Built for premium font discovery.',
        'side_copy'      => 'This page excludes the Free Fonts category and keeps the focus on commercial releases for branding, editorial, packaging, and client-facing work.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-layer-group',
                'text' => 'Commercial font posts',
            ),
            array(
                'icon' => 'fa-solid fa-bolt',
                'text' => 'Popular and recent signals',
            ),
            array(
                'icon' => 'fa-solid fa-compass',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Commercial',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Commercial pick',
        'posts_per_page' => 24,
        'orderby'        => array(
            'comment_count' => 'DESC',
            'date'          => 'DESC',
        ),
        'free_mode'      => 'exclude',
    )
);
