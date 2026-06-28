<?php
/*
Template Name: Best Tech & Futuristic Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Mood collection',
        'eyebrow_icon'   => 'fa-solid fa-microchip',
        'title'          => 'Best Tech & Futuristic Fonts',
        'summary'        => 'Tech and futuristic fonts for digital products, sci-fi visuals, modern brands, and interface-led design.',
        'side_title'     => 'Sharp, digital, forward-looking.',
        'side_copy'      => 'These picks focus on fonts with a technical, futuristic, or high-contrast digital feel.',
        'intro_title'    => 'Tech fonts need to feel current without becoming hard to read.',
        'intro_copy'     => 'Use this collection for digital products, sci-fi posters, gaming visuals, software brands, interface-led campaigns, and future-facing identity work.',
        'intro_points'   => array(
            'Good for software, gaming, sci-fi, and digital product visuals.',
            'Focused on futuristic and technology-led moods.',
            'Balance sharp character with practical readability.',
        ),
        'related_slugs'  => array( 'best-modern-sans-serif-fonts', 'best-display-fonts', 'best-fonts-for-branding' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-microchip',
                'text' => 'Mood: Futuristic',
            ),
            array(
                'icon' => 'fa-solid fa-bolt',
                'text' => 'Digital tone',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Tech',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Tech mood',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'futuristic', 'tech', 'technology', 'sci-fi', 'scifi' ),
            ),
        ),
    )
);
