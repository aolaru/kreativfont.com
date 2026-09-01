<?php
/*
Template Name: Best Modern Sans Serif Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Style collection',
        'eyebrow_icon'   => 'fa-solid fa-circle-half-stroke',
        'title'          => 'Best Modern Sans Serif Fonts',
        'summary'        => 'Browse modern sans serif fonts for interfaces, brands, editorial systems, and digital products, then select the right balance of personality, clarity, and family depth.',
        'side_title'     => 'Clean structure, current visual tone.',
        'side_copy'      => 'These picks favor clean sans serif type with a contemporary feel.',
        'intro_title'    => 'How to choose a modern sans serif font',
        'intro_copy'     => 'Modern sans serifs are versatile because they can make a product, identity, or editorial layout feel current without demanding attention from the message. The right choice depends on where the font will work hardest: interface text, a brand voice, headlines, or a complete system.',
        'intro_points'   => array(
            'Choose for the role: text, UI, headline, brand, or an all-purpose family.',
            'Look beyond a single weight when the project needs hierarchy or responsive layouts.',
            'Check clarity in compact navigation, small labels, and real body copy.',
        ),
        'related_slugs'  => array( 'best-minimal-fonts', 'best-tech-futuristic-fonts', 'best-fonts-for-branding' ),
        'guide_sections' => array(
            array(
                'title'      => 'Modern does not have to mean neutral',
                'paragraphs' => array(
                    'A modern sans serif can be quiet and functional, sharply geometric, warm and humanist, or more editorial in its proportions. Start by deciding whether the type should disappear into the interface, carry a recognizable brand voice, or create contrast in a headline. This prevents a “clean” choice from becoming interchangeable with every other clean option.',
                    'Open individual font pages to compare the available weights, specimen, mood, and intended use. For a product or content-heavy site, make sure the family remains clear in small text before using the boldest image as your reference.',
                ),
                'points'     => array(
                    'Use a family with several weights and italics when the interface or publication needs flexible hierarchy.',
                    'Check numerals, punctuation, and ambiguous characters such as I, l, and 1 for data-heavy layouts.',
                    'Test the font with real paragraphs as well as oversized headlines.',
                ),
            ),
            array(
                'title'      => 'Pair modern sans serifs by role',
                'paragraphs' => array(
                    'A modern sans serif often works as the foundation of a type system. It can support a more expressive serif or display face in campaigns, or take both headline and body roles when the family has enough contrast between weights. The goal is not to find two nearly identical sans serifs, but to give each typeface a purpose.',
                    'Continue to the branding, minimal, and tech collections when you need the same clean base with a more specific visual direction.',
                ),
            ),
        ),
        'faq_items'      => array(
            array(
                'question' => 'What is a modern sans serif font?',
                'answer'   => 'It is a sans serif typeface with a contemporary visual character. It may be geometric, humanist, neo-grotesque, or editorial, but usually prioritises clean forms and flexible use across digital and print design.',
            ),
            array(
                'question' => 'Are modern sans serif fonts good for websites?',
                'answer'   => 'They can be excellent for websites when the chosen family is clear at small sizes, supports the required languages, and is licensed for the planned web use. Test real interface and body copy before deciding.',
            ),
            array(
                'question' => 'How do I pair a modern sans serif?',
                'answer'   => 'Give the sans serif a clear role, such as interface and body text, then pair it with a contrasting display serif, script, or more expressive headline font when the project needs added personality.',
            ),
        ),
        'faq_title'      => 'Modern sans serif questions',
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
                'text' => 'Updated regularly',
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
        ),
        'title_exclude_patterns' => array(
            '/\b(script|handw[a-z-]*|handmade|hand-drawn|hand drawn|brush)\b/i',
        ),
    )
);
