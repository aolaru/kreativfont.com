<?php
/*
Template Name: Best Fonts for Branding
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-bullseye',
        'title'          => 'Best Fonts for Branding',
        'summary'        => 'Browse fonts for brand systems, packaging, campaigns, and client identities, then choose a family that can support the work beyond the first logo mockup.',
        'side_title'     => 'A practical collection for client-facing work.',
        'side_copy'      => 'Use it when a typeface needs identity and positioning value beyond one layout.',
        'intro_title'    => 'How to choose a font for a brand system',
        'intro_copy'     => 'Brand typography needs to carry a recognisable point of view while remaining practical across the entire customer experience. These fonts are starting points for identities that need to work in print, on screen, in campaigns, and in client handoff files.',
        'intro_points'   => array(
            'Prioritise family depth, hierarchy, and recognisability over a single striking preview.',
            'Test the type in the actual brand name, product names, and core messages.',
            'Check licensing, language support, and the formats required by the final system.',
        ),
        'related_slugs'  => array( 'best-logo-fonts', 'trending-commercial-fonts', 'best-modern-sans-serif-fonts' ),
        'guide_sections' => array(
            array(
                'title'      => 'Choose for the system, not only the logo',
                'paragraphs' => array(
                    'A brand font has more work to do than a logo font. It may need to create hierarchy in a campaign, make a product page feel consistent, support packaging information, and give presentations a recognisable voice. Look for options that have the range you need before choosing solely on personality.',
                    'A well-matched family can reduce the number of typefaces in the system. Useful weights, italics, numerals, punctuation, and language coverage give a brand more room to grow without losing its visual signature.',
                ),
                'points'     => array(
                    'Use a stronger weight or display companion for headlines and a readable style for supporting copy.',
                    'Check how the type behaves in all caps, small text, and dense information layouts.',
                    'Keep a record of the license and source so clients can use the brand correctly after handoff.',
                ),
            ),
            array(
                'title'      => 'Build contrast deliberately when pairing brand fonts',
                'paragraphs' => array(
                    'Pairing is usually about role, not finding two fonts with the same personality. A high-character serif or display face can lead the system, while a neutral sans serif handles interfaces and information. Two fonts can share a mood but still need enough contrast in proportion, structure, or weight to create clear hierarchy.',
                    'Use the logo and modern sans serif collections as companion routes when you need a separate wordmark direction or a reliable functional typeface.',
                ),
            ),
        ),
        'faq_items'      => array(
            array(
                'question' => 'How many fonts should a brand use?',
                'answer'   => 'Many effective brand systems use one flexible family or a display font paired with one practical text family. Add more only when each font has a clear role.',
            ),
            array(
                'question' => 'What should I check before licensing a brand font?',
                'answer'   => 'Confirm the license covers the planned logo, print, web, app, social, and client-handoff uses. Also check the available weights, language support, and file formats.',
            ),
            array(
                'question' => 'Can a free font work for professional branding?',
                'answer'   => 'Yes, when its license and technical coverage suit the project. A commercial family may be the better choice when the brand needs broader weights, languages, support, or exclusive visual positioning.',
            ),
        ),
        'faq_title'      => 'Brand font questions',
        'badges'         => array(
            array(
                'icon' => 'fa-solid fa-bullseye',
                'text' => 'Use Case: Branding',
            ),
            array(
                'icon' => 'fa-solid fa-briefcase',
                'text' => 'Client work',
            ),
            array(
                'icon' => 'fa-solid fa-rotate',
                'text' => 'Updated regularly',
            ),
        ),
        'badge_text'     => 'Branding',
        'badge_slug'     => 'fonts',
        'context_note'   => 'Branding use case',
        'posts_per_page' => 24,
        'orderby'        => array(
            'date' => 'DESC',
        ),
        'branch_filters' => array(
            array(
                'branch' => 'font_use_case',
                'slugs'  => array( 'branding' ),
            ),
        ),
    )
);
