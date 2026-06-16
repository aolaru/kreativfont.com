<?php
/*
Template Name: Trending Commercial Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Commercial picks',
        'eyebrow_icon'   => 'fa-solid fa-chart-line',
        'title'          => 'Trending Commercial Fonts',
        'summary'        => 'Commercial fonts selected for branding, editorial layouts, packaging, and client-facing design work.',
        'side_title'     => 'Premium options first.',
        'side_copy'      => 'Free font posts are left out here so paid releases stay easier to compare.',
        'intro_title'    => 'Start here when you want premium options first.',
        'intro_copy'     => 'Commercial fonts often bring larger families, clearer licensing, and better production support. Use this list when quality and usage rights matter.',
        'intro_points'   => array(
            'Good for brand systems, editorial work, packaging, and campaigns.',
            'Keeps paid releases separate from free downloads.',
            'Useful when quality, licensing, and family depth matter.',
        ),
        'related_slugs'  => array( 'best-modern-sans-serif-fonts', 'best-elegant-serif-fonts', 'best-fonts-for-branding' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-layer-group',
                'text' => 'Commercial font posts',
            ),
            array(
                'icon' => 'fa-solid fa-bolt',
                'text' => 'Popular and recent',
            ),
            array(
                'icon' => 'fa-solid fa-compass',
                'text' => 'Updated regularly',
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
