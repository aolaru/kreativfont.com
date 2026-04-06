<?php
/*
Template Name: Updates Page
*/

$kreativ_updates = array(
    'April 2026' => array(
        array(
            'title'   => 'Search became much more useful for font discovery',
            'summary' => 'Search now behaves more like a real font-finding tool, with live suggestions, stronger ranking, and better explanations for why results appear.',
            'items'   => array(
                'A custom live suggestion dropdown was added to the header search.',
                'Suggestions now include font titles, designers, foundries, styles, moods, and use cases.',
                'Search result cards now explain what matched, and the full results page now follows the same structured matching logic as the live suggestions.',
            ),
        ),
        array(
            'title'   => 'Structured font taxonomy started powering the theme',
            'summary' => 'The theme began shifting away from loose generic tags toward a cleaner font taxonomy model built around designer, foundry, style, mood, and use case.',
            'items'   => array(
                'Single-page hero metadata now prefers structured designer and foundry information.',
                'Single-page classification now prefers font style branches instead of generic category guesses.',
                'Search ranking and live suggestions now understand structured font branches directly.',
            ),
        ),
        array(
            'title'   => 'Single font pages received another polish pass',
            'summary' => 'Single pages were tuned further so they read more clearly and feel more deliberate in day-to-day browsing.',
            'items'   => array(
                'Single-page navigation labels were corrected.',
                'Single hero title and summary widths were adjusted for a better balance between copy and imagery.',
                'The footer metadata and search-related presentation on single pages were refined further.',
            ),
        ),
    ),
    'March 2026' => array(
        array(
            'title'   => 'Site operations and publishing workflow improved',
            'summary' => 'The infrastructure behind the theme was upgraded so site changes can move faster from local work to GitHub and then to production.',
            'items'   => array(
                'GitHub repository and structured changelog were added for ongoing theme development.',
                'Automated deployment was set up through GitHub Actions and SFTP.',
                'The new public Updates page was added to make product progress visible on the site itself.',
            ),
        ),
        array(
            'title'   => 'Homepage refocused on fonts and tools',
            'summary' => 'The homepage now puts curated fonts and practical font tools first, with a clearer hero, stronger calls to action, and a tighter product direction.',
            'items'   => array(
                'Homepage now focuses on fonts and font tools instead of broader creative categories.',
                'Hero messaging updated around font discovery, utility, and faster decisions.',
                'Homepage now shows 24 fonts and a richer set of browsing filters.',
            ),
        ),
        array(
            'title'   => 'Browsing filters expanded and unified',
            'summary' => 'Browsing now feels more consistent across the site, with homepage-style filters also applied to category and tag archives.',
            'items'   => array(
                'Added mood and style filters like Modern, Vintage, and Elegant.',
                'Archive pages now use the same filter pattern as the homepage.',
                'Tag archive behavior was repaired so tag pages return results again.',
            ),
        ),
        array(
            'title'   => 'Tool pages and supporting templates were upgraded',
            'summary' => 'Pages across the site now share a stronger layout system, making tools and content pages feel more consistent with the core Kreativ Font experience.',
            'items'   => array(
                'Tool pages were moved onto a cleaner branded layout.',
                'Search, 404, wide pages, and supporting templates were normalized into the same design system.',
                'Legacy layout inconsistencies were reduced across key templates.',
            ),
        ),
        array(
            'title'   => 'Single font pages redesigned',
            'summary' => 'Single pages now feel more like product pages, with cleaner hero sections, better metadata, stronger sharing, and a tidier post footer.',
            'items'   => array(
                'Hero now uses smarter font-description summaries from the post content.',
                'Metadata now prefers designer and foundry context over the post author.',
                'Jetpack sharing buttons were replaced with a custom share bar.',
            ),
        ),
        array(
            'title'   => 'Header, footer, and mobile experience improved',
            'summary' => 'The global chrome is now cleaner and more usable across desktop and mobile, with better branding and more reliable navigation behavior.',
            'items'   => array(
                'Header branding updated to Kreativ Font with the tagline Curated Fonts and Tools.',
                'Search, navigation, and theme toggle were redesigned and tightened.',
                'Mobile off-canvas navigation and dark-mode behavior were fixed and hardened.',
            ),
        ),
        array(
            'title'   => 'Visual identity cleaned up',
            'summary' => 'The site now has a more coherent visual system across icons, favicons, cards, and supporting page layouts.',
            'items'   => array(
                'Favicon system rebuilt with a branded K mark.',
                'Font Awesome standardized for newer UI work.',
                'Footer refreshed and the single-post footer restyled to match the theme.',
            ),
        ),
        array(
            'title'   => 'Theme structure and deployment workflow improved',
            'summary' => 'Under the hood, the theme is now easier to maintain and safer to evolve without piling more legacy complexity on top of older code.',
            'items'   => array(
                'Theme code split into shared helpers, includes, and reusable partials.',
                'Unused assets and stale legacy references were removed.',
                'Shared components now drive cards, filters, layout patterns, and page structure more consistently.',
            ),
        ),
    ),
    'Since September 2025' => array(
        array(
            'title'   => 'Kreativ Font entered a broader modernization phase',
            'summary' => 'The site began shifting away from an older general creative-assets direction and toward a cleaner fonts-first product focused on discovery, utility, and a more coherent browsing experience.',
            'items'   => array(
                'The overall direction moved more decisively toward fonts, font reviews, and practical tools.',
                'Branding, UX, and structure started being treated as product work instead of isolated theme tweaks.',
                'This set the stage for the larger March and April 2026 improvements tracked above.',
            ),
        ),
    ),
);

get_header();
?>

<div class="kreativ-page-shell kreativ-updates-page">
    <section class="kreativ-page-hero kreativ-page-hero-compact">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-sparkles" aria-hidden="true"></i>
                Product updates
            </div>

            <h1 class="kreativ-page-title">Kreativ Font updates and product changes.</h1>

            <p class="kreativ-page-summary">
                A public log of the biggest improvements to browsing, font tools, single pages, mobile UX, and the overall Kreativ Font experience.
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Fonts-first improvements</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Product-focused updates</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-mobile-screen"></i> Desktop and mobile polish</span>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content kreativ-updates-content">
        <?php foreach ( $kreativ_updates as $month => $updates ) : ?>
            <div class="kreativ-updates-month">
                <h2 class="kreativ-updates-month-title"><?php echo esc_html( $month ); ?></h2>

                <div class="kreativ-updates-list">
                    <?php foreach ( $updates as $update ) : ?>
                        <article class="kreativ-update-card">
                            <div class="kreativ-update-date"><?php echo esc_html( $month ); ?></div>
                            <h3 class="kreativ-update-title"><?php echo esc_html( $update['title'] ); ?></h3>
                            <p class="kreativ-update-summary"><?php echo esc_html( $update['summary'] ); ?></p>

                            <ul class="kreativ-update-points">
                                <?php foreach ( $update['items'] as $item ) : ?>
                                    <li><?php echo esc_html( $item ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<?php get_footer(); ?>
