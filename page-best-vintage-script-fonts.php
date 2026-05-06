<?php
/*
Template Name: Best Vintage Script Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-pen-nib',
        'title'          => 'Best Vintage Script Fonts',
        'summary'        => 'A dynamic collection of script fonts with a vintage mood, built from the structured Font Style and Font Mood branches.',
        'side_title'     => 'Useful when style and mood matter together.',
        'side_copy'      => 'This page combines the Script style branch with the Vintage mood branch, so the results stay tighter than a broad tag archive.',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-pen-nib',
                'text' => 'Style: Script',
            ),
            array(
                'icon' => 'fa-solid fa-clock-rotate-left',
                'text' => 'Mood: Vintage',
            ),
            array(
                'icon' => 'fa-solid fa-tags',
                'text' => 'Structured taxonomy',
            ),
        ),
        'badge_text'     => 'Script',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Vintage script',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'script' ),
            ),
            array(
                'branch' => 'font_mood',
                'slugs'  => array( 'vintage' ),
            ),
        ),
    )
);
