<?php
/*
Template Name: Best Logo Fonts
*/

kreativ_render_dynamic_font_collection_page(
    array(
        'eyebrow'        => 'Use-case collection',
        'eyebrow_icon'   => 'fa-solid fa-signature',
        'title'          => 'Best Logo Fonts',
        'summary'        => 'Browse logo fonts for memorable wordmarks and identity systems, then evaluate their character, spacing, licensing, and flexibility before making a final choice.',
        'side_title'     => 'Fast entry point for identity work.',
        'side_copy'      => 'Use it when a wordmark or identity needs a distinctive type choice.',
        'intro_title'    => 'How to choose a font for a logo',
        'intro_copy'     => 'The best logo font is recognisable at a glance and dependable in every context where the brand will appear. Use this collection to find a direction, then test the strongest candidates with the real business name before committing.',
        'intro_points'   => array(
            'Test the exact brand name at large and small sizes before choosing.',
            'Look for character, spacing, and a silhouette people can remember.',
            'Confirm that the license covers commercial logo and client work.',
        ),
        'related_slugs'  => array( 'best-fonts-for-branding', 'best-modern-sans-serif-fonts', 'best-vintage-script-fonts' ),
        'guide_sections' => array(
            array(
                'title'      => 'A logo font has to work beyond the moodboard',
                'paragraphs' => array(
                    'A distinctive font can make a wordmark feel immediate, but the decision should hold up in the places a brand actually lives: a browser tab, social avatar, packaging label, presentation cover, email signature, and signage. Start with the personality you want to communicate, then remove choices that become unclear or generic at the sizes you need.',
                    'Evaluate the actual letters in the name, not a perfect specimen word. Pay particular attention to repeated characters, unusual joins, wide or narrow letters, and the visual balance between the first and last character.',
                ),
                'points'     => array(
                    'Check the wordmark in one colour before relying on effects, outlines, or gradients.',
                    'Test both horizontal and stacked lockups if the brand will need them.',
                    'Choose a family with useful weights when the wordmark may need a companion type system.',
                ),
            ),
            array(
                'title'      => 'Pair the logo font with a practical supporting typeface',
                'paragraphs' => array(
                    'The font used in a logo does not need to handle navigation, product copy, or long reading. A decorative display face often works best alongside a quieter sans serif or serif for the rest of the system. The contrast should feel intentional without making the brand look like two unrelated designs.',
                    'Open individual font pages to compare style, mood, and usage context, then continue to the branding and modern sans serif collections for supporting options.',
                ),
            ),
        ),
        'faq_items'      => array(
            array(
                'question' => 'What makes a font good for a logo?',
                'answer'   => 'A good logo font is recognisable, appropriate for the brand, and clear in the sizes and formats the business will actually use. Its license must also permit the intended commercial use.',
            ),
            array(
                'question' => 'Should a logo use a display font or a text font?',
                'answer'   => 'Either can work. Display fonts give more character to short names, while text-oriented families can create a more flexible and understated identity. Test the actual name before deciding.',
            ),
            array(
                'question' => 'Can I use one font for both a logo and a website?',
                'answer'   => 'You can when the family is readable and licensed for both uses. Many brands instead use a distinctive logo font with a simpler supporting font for interface and body text.',
            ),
        ),
        'faq_title'      => 'Logo font questions',
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
