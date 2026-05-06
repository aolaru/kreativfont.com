<?php
/*
Template Name: Best Modern Sans Serif Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-circle-half-stroke',
        'title'          => 'Best Modern Sans Serif Fonts',
        'summary'        => 'A dynamic collection of sans serif fonts with a modern mood, useful for interfaces, brands, editorial systems, and clean digital products.',
        'side_title'     => 'Clean structure, current visual tone.',
        'side_copy'      => 'This page combines the Sans Serif style branch with the Modern mood branch, keeping the collection focused around practical contemporary type.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-font',
                'text' => 'Style: Sans Serif',
            ),
            array(
                'icon' => 'fa-solid fa-circle-half-stroke',
                'text' => 'Mood: Modern',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updates automatically',
            ),
        ),
        'badge_text'     => 'Sans Serif',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Modern sans',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'sans-serif', 'sansserif' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'modern' ),
            ),
        ),
    )
);
