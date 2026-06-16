<?php
/*
Template Name: Best Luxury Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Mood collection',
        'eyebrow_icon'   => 'fa-solid fa-gem',
        'title'          => 'Best Luxury Fonts',
        'summary'        => 'Luxury fonts for premium brands, beauty systems, fashion campaigns, packaging, hospitality, and high-end editorial work.',
        'side_title'     => 'Use luxury fonts when perception matters.',
        'side_copy'      => 'These picks focus on typefaces that feel premium, polished, and brand-forward.',
        'intro_title'    => 'Luxury fonts should feel intentional, refined, and memorable.',
        'intro_copy'     => 'Use this collection when the typeface needs to communicate premium value before the rest of the brand system does. These fonts are useful for beauty, fashion, restaurants, hospitality, editorial, and upscale packaging.',
        'intro_points'   => array(
            'Good for premium brands, beauty, fashion, and hospitality.',
            'Focused on premium and high-end visual tone.',
            'Look for refined contrast, strong spacing, and high-end tone.',
        ),
        'related_slugs'  => array( 'best-elegant-serif-fonts', 'best-fonts-for-branding', 'best-packaging-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-gem',
                'text' => 'Mood: Luxury',
            ),
            array(
                'icon' => 'fa-solid fa-crown',
                'text' => 'Premium tone',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Luxury',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Luxury mood',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'luxury' ),
            ),
        ),
    )
);
