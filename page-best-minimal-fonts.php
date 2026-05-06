<?php
/*
Template Name: Best Minimal Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Mood collection',
        'eyebrow_icon'   => 'fa-solid fa-minus',
        'title'          => 'Best Minimal Fonts',
        'summary'        => 'A dynamic collection of minimal fonts for clean interfaces, restrained branding, editorial layouts, and quiet visual systems.',
        'side_title'     => 'Less noise, clearer choices.',
        'side_copy'      => 'This page pulls from the Minimal mood branch, making it a focused shortcut for clean and reduced typography.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-minus',
                'text' => 'Mood: Minimal',
            ),
            array(
                'icon' => 'fa-solid fa-table-cells-large',
                'text' => 'Clean systems',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Minimal',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Minimal mood',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'minimal' ),
            ),
        ),
    )
);
