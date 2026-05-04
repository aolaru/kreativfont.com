<?php
/*
Template Name: Updates Page
*/

$kreativ_updates = array(
    'May 2026' => array(
        'title'   => 'Homepage and single-page flow simplified',
        'summary' => 'May started with a simplification pass focused on making the site easier to scan, easier to maintain, and more consistent for future font posts.',
        'items'   => array(
            'The homepage was simplified with a cleaner hero, quieter tool links, tighter spacing, and fewer latest commercial font cards.',
            'A reusable font CTA component was added so commercial and free font posts can use consistent purchase and download blocks.',
            'Single font metadata was simplified so style, designer, and foundry stay primary while mood, use cases, and additional tags are presented with less visual weight.',
            'Legacy catalog styling and single-page breadcrumb behavior were cleaned up so the theme is more consistently focused on fonts and font tools.',
        ),
    ),
    'April 2026' => array(
        'title'   => 'Search, taxonomy, and single-page clarity improved together',
        'summary' => 'April focused on turning the site into a stronger font-discovery product, with better search behavior, stronger structured metadata, clearer browsing paths, and more deliberate single-page presentation.',
        'items'   => array(
            'A custom live suggestion dropdown was added to the header search.',
            'Suggestions now include font titles, designers, foundries, styles, moods, and use cases.',
            'Search result cards now explain what matched, and the full results page now follows the same structured matching logic as the live suggestions.',
            'Search refinements were added so results can be narrowed by structured font taxonomy without leaving the results page.',
            'The homepage now separates latest commercial fonts from latest free fonts, making browsing clearer as free-font publishing increases.',
            'Single-page hero metadata now prefers structured designer and foundry information.',
            'Single-page classification now prefers font style branches instead of generic category guesses.',
            'Single font pages now show cleaner structured metadata for style, designer, foundry, mood, use cases, and secondary tags.',
            'Mobile navigation was rebuilt and polished so the menu opens correctly above page content and the header no longer leaves extra spacing.',
            'Single-page navigation labels were corrected, and hero title and summary widths were rebalanced.',
        ),
    ),
    'March 2026' => array(
        'title'   => 'The theme was rebuilt into a more coherent fonts-first product',
        'summary' => 'March was the main rebuild month. The site moved away from an older general-assets direction and became a more consistent product centered on curated fonts, font tools, stronger browsing, and cleaner page structure.',
        'items'   => array(
            'GitHub repository, structured changelog, and automated deployment were set up for safer ongoing theme work.',
            'The homepage was refocused on fonts and font tools, with clearer hero messaging and stronger calls to action.',
            'Homepage and archive filters were unified, with mood and style filters like Modern, Vintage, and Elegant added.',
            'Single font pages were redesigned with stronger hero sections, smarter summaries, better metadata, and custom sharing.',
            'Header, footer, branding, mobile navigation, and dark mode were all rebuilt into a more consistent global UI.',
            'Tool pages, search, 404, wide pages, and supporting templates were normalized into one shared design system.',
            'Favicons, icons, cards, and supporting visual details were cleaned up to match the new direction.',
            'The public Updates page was added so product progress is visible on the site itself.',
        ),
    ),
    'Since September 2025' => array(
        'title'   => 'Kreativ Font entered a broader modernization phase',
        'summary' => 'The site began shifting away from an older general creative-assets direction and toward a cleaner fonts-first product focused on discovery, utility, and a more coherent browsing experience.',
        'items'   => array(
            'The overall direction moved more decisively toward fonts, font reviews, and practical tools.',
            'Branding, UX, and structure started being treated as product work instead of isolated theme tweaks.',
            'This set the stage for the larger March and April 2026 improvements tracked above.',
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
                    <article class="kreativ-update-card">
                        <div class="kreativ-update-date"><?php echo esc_html( $month ); ?></div>
                        <h3 class="kreativ-update-title"><?php echo esc_html( $updates['title'] ); ?></h3>
                        <p class="kreativ-update-summary"><?php echo esc_html( $updates['summary'] ); ?></p>

                        <ul class="kreativ-update-points">
                            <?php foreach ( $updates['items'] as $item ) : ?>
                                <li><?php echo esc_html( $item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<?php get_footer(); ?>
