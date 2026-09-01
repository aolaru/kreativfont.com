<?php
/*
Template Name: Best Wedding Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-ring',
        'title'          => 'Best Wedding Fonts',
        'summary'        => 'Browse wedding fonts for invitations, signage, stationery, and event branding, then choose a pairing that feels personal without sacrificing readability.',
        'side_title'     => 'Romantic type without broad browsing.',
        'side_copy'      => 'These picks make decorative, elegant, and event-ready type easier to compare.',
        'intro_title'    => 'How to choose wedding fonts that guests can actually read',
        'intro_copy'     => 'Wedding typography should set the tone before the event begins, from save-the-dates to menus and welcome signs. Choose a decorative voice for the moments that need emotion, then support it with a clear text face for the details people need to find quickly.',
        'intro_points'   => array(
            'Use expressive fonts for names and short headings, not every line of information.',
            'Pair scripts and decorative faces with a simple serif or sans serif for details.',
            'Test names, dates, venue information, and print size before finalising the design.',
        ),
        'related_slugs'  => array( 'best-handwritten-fonts', 'best-vintage-script-fonts', 'best-elegant-serif-fonts' ),
        'guide_sections' => array(
            array(
                'title'      => 'Give each typeface a clear role',
                'paragraphs' => array(
                    'The most successful wedding suites usually use contrast: a script, handwritten, or elegant display font for names and a supporting face for dates, directions, menus, and RSVP details. That balance keeps the suite romantic while making it easy for guests to read at a glance.',
                    'Avoid choosing from a specimen image alone. Typeset the couple’s names, the venue name, and a complete line of practical information. Long names, repeated letters, and tiny punctuation reveal problems that a short preview can hide.',
                ),
                'points'     => array(
                    'Keep the smallest print size readable before approving a decorative font.',
                    'Check whether the font includes the accents, ampersand, numerals, and punctuation you need.',
                    'Use one visual focal point per layout instead of competing decorative styles.',
                ),
            ),
            array(
                'title'      => 'Plan for print, signage, and digital sharing',
                'paragraphs' => array(
                    'Wedding typography often moves across several formats: printed invitations, large welcome signs, place cards, websites, and social graphics. A good choice feels consistent in each, while the supporting typeface protects legibility where the information becomes denser.',
                    'Before sending files to print or sharing editable templates, check the font license and whether the recipient needs their own license to edit the final design.',
                ),
            ),
        ),
        'faq_items'      => array(
            array(
                'question' => 'What fonts work best for wedding invitations?',
                'answer'   => 'A decorative script, handwritten, or elegant serif can work well for names and headings when paired with a clean supporting font for dates, venues, and other details.',
            ),
            array(
                'question' => 'Should I use two fonts on a wedding invitation?',
                'answer'   => 'Usually, yes. A decorative font gives the invitation personality, while a simpler text font makes the practical information clear and easier to scan.',
            ),
            array(
                'question' => 'What should I check before printing a wedding font?',
                'answer'   => 'Test the smallest size, real names and dates, punctuation, accents, and the exact print stock or proof. Also confirm the font license covers the intended design and template use.',
            ),
        ),
        'faq_title'      => 'Wedding font questions',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-ring',
                'text' => 'Use Case: Wedding',
            ),
            array(
                'icon' => 'fa-solid fa-envelope-open-text',
                'text' => 'Invitations and stationery',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Wedding',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Wedding use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'wedding' ),
            ),
        ),
    )
);
