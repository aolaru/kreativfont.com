<?php
/*
Template Name: Best Elegant Serif Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-feather-pointed',
        'title'          => 'Best Elegant Serif Fonts',
        'summary'        => 'A dynamic collection of elegant serif fonts for luxury identities, editorial layouts, fashion systems, packaging, and polished brand work.',
        'side_title'     => 'Refined serifs for premium work.',
        'side_copy'      => 'This page combines the Serif style branch with the Elegant mood branch, so the results stay useful for high-end visual decisions.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-font',
                'text' => 'Style: Serif',
            ),
            array(
                'icon' => 'fa-solid fa-gem',
                'text' => 'Mood: Elegant',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Serif',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Elegant serif',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'serif' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'elegant' ),
            ),
        ),
    )
);
