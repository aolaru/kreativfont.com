<?php
/*
Template Name: Best Handwritten Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-pen',
        'title'          => 'Best Handwritten Fonts',
        'summary'        => 'Handwritten fonts for casual brands, notes, packaging, social graphics, and expressive display work.',
        'side_title'     => 'Personal without feeling random.',
        'side_copy'      => 'These picks make informal, hand-made typography easier to compare.',
        'intro_title'    => 'Handwritten fonts work best when the tone needs a human touch.',
        'intro_copy'     => 'Use this collection for casual branding, packaging details, social posts, invitations, notes, and display pieces where a more personal voice helps the design.',
        'intro_points'   => array(
            'Good for casual brands, notes, packaging, and invitations.',
            'Focused on handwritten and hand-drawn styles.',
            'Use carefully for short text, accents, and display moments.',
        ),
        'related_slugs'  => array( 'best-wedding-fonts', 'best-vintage-script-fonts', 'best-logo-fonts' ),
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-pen',
                'text' => 'Style: Handwritten',
            ),
            array(
                'icon' => 'fa-solid fa-pen',
                'text' => 'Human tone',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Handwritten',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Handwritten style',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_style',
                'slugs'  => array( 'handwritten', 'handwritten-2', 'handwriting', 'hand-drawn', 'handdrawn' ),
            ),
        ),
    )
);
