<?php
/*
Template Name: Updates Page
*/

$kreativ_updates = array(
    array(
        'date'    => 'March 2026',
        'title'   => 'Homepage refocused on fonts and tools',
        'summary' => 'The homepage now puts curated fonts and practical font tools first, with a clearer hero, stronger calls to action, and a tighter product direction.',
        'items'   => array(
            'Homepage now focuses on fonts and font tools instead of broader creative categories.',
            'Hero messaging updated around font discovery, utility, and faster decisions.',
            'Homepage now shows 24 fonts and a richer set of browsing filters.',
        ),
    ),
    array(
        'date'    => 'March 2026',
        'title'   => 'Browsing filters expanded and unified',
        'summary' => 'Browsing now feels more consistent across the site, with homepage-style filters also applied to category and tag archives.',
        'items'   => array(
            'Added mood and style filters like Modern, Vintage, and Elegant.',
            'Archive pages now use the same filter pattern as the homepage.',
            'Tag archive behavior was repaired so tag pages return results again.',
        ),
    ),
    array(
        'date'    => 'March 2026',
        'title'   => 'Single font pages redesigned',
        'summary' => 'Single pages now feel more like product pages, with cleaner hero sections, better metadata, stronger sharing, and a tidier post footer.',
        'items'   => array(
            'Hero now uses smarter font-description summaries from the post content.',
            'Metadata now prefers designer and foundry context over the post author.',
            'Jetpack sharing buttons were replaced with a custom share bar.',
        ),
    ),
    array(
        'date'    => 'March 2026',
        'title'   => 'Header, footer, and mobile experience improved',
        'summary' => 'The global chrome is now cleaner and more usable across desktop and mobile, with better branding and more reliable navigation behavior.',
        'items'   => array(
            'Header branding updated to Kreativ Font with the tagline Curated Fonts and Tools.',
            'Search, navigation, and theme toggle were redesigned and tightened.',
            'Mobile off-canvas navigation and dark-mode behavior were fixed and hardened.',
        ),
    ),
    array(
        'date'    => 'March 2026',
        'title'   => 'Visual identity cleaned up',
        'summary' => 'The site now has a more coherent visual system across icons, favicons, cards, and supporting page layouts.',
        'items'   => array(
            'Favicon system rebuilt with a branded K mark.',
            'Font Awesome standardized for newer UI work.',
            'Footer refreshed and the single-post footer restyled to match the theme.',
        ),
    ),
    array(
        'date'    => 'March 2026',
        'title'   => 'Theme structure and deployment workflow improved',
        'summary' => 'Under the hood, the theme is now easier to maintain and faster to ship through GitHub and automated deployment.',
        'items'   => array(
            'Theme code split into shared helpers, includes, and reusable partials.',
            'Unused assets and stale legacy references were removed.',
            'GitHub-based deployment workflow was added for faster publishing.',
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
        <div class="kreativ-updates-list">
            <?php foreach ( $kreativ_updates as $update ) : ?>
                <article class="kreativ-update-card">
                    <div class="kreativ-update-date"><?php echo esc_html( $update['date'] ); ?></div>
                    <h2 class="kreativ-update-title"><?php echo esc_html( $update['title'] ); ?></h2>
                    <p class="kreativ-update-summary"><?php echo esc_html( $update['summary'] ); ?></p>

                    <ul class="kreativ-update-points">
                        <?php foreach ( $update['items'] as $item ) : ?>
                            <li><?php echo esc_html( $item ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>
