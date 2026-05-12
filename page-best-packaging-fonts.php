<?php
/*
Template Name: Best Packaging Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-box-open',
        'title'          => 'Best Packaging Fonts',
        'summary'        => 'A dynamic collection of packaging fonts for labels, product systems, retail brands, food and beverage design, and shelf-ready visual identities.',
        'side_title'     => 'Packaging fonts have to work fast.',
        'side_copy'      => 'This page pulls from the Packaging use-case branch, so it stays focused on fonts that can carry labels, product names, claims, and brand personality.',
        'intro_title'    => 'Packaging fonts need shelf impact and production clarity.',
        'intro_copy'     => 'Use this collection for product labels, beverage brands, cosmetics, restaurants, retail packaging, and identity systems where the type has to read quickly and still feel distinctive.',
        'intro_points'   => array(
            'Good for labels, retail products, food, beverage, and cosmetics.',
            'Pulls from the Packaging use-case branch.',
            'Prioritize readability, personality, and strong hierarchy.',
        ),
        'related_slugs'  => array( 'best-logo-fonts', 'best-fonts-for-branding', 'best-luxury-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-box-open',
                'text' => 'Use Case: Packaging',
            ),
            array(
                'icon' => 'fa-solid fa-store',
                'text' => 'Retail ready',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Packaging',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Packaging use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'packaging' ),
            ),
        ),
    )
);
