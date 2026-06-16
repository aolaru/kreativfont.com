<?php
/*
Template Name: Best Logo Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-signature',
        'title'          => 'Best Logo Fonts',
        'summary'        => 'Fonts suited for logos, wordmarks, identity systems, and distinctive brand marks.',
        'side_title'     => 'Fast entry point for identity work.',
        'side_copy'      => 'Use it when a wordmark or identity needs a distinctive type choice.',
        'intro_title'    => 'Logo fonts need distinct shape, not just readability.',
        'intro_copy'     => 'Use this collection for wordmarks, brand marks, and identity concepts where the font needs to carry personality before the full visual system exists.',
        'intro_points'   => array(
            'Good for wordmarks, marks, and identity exploration.',
            'Focused on logo and wordmark use.',
            'Look for memorable shapes and strong spacing.',
        ),
        'related_slugs'  => array( 'best-fonts-for-branding', 'best-modern-sans-serif-fonts', 'best-vintage-script-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-signature',
                'text' => 'Use Case: Logo',
            ),
            array(
                'icon' => 'fa-solid fa-bullseye',
                'text' => 'Identity focused',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Logo',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Logo use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'logo' ),
            ),
        ),
    )
);
