<?php get_header(); ?>

<?php
$search_query = get_search_query();
$result_count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                Search results
            </div>

            <h1 class="kreativ-page-title">
                <?php if ( have_posts() ) : ?>
                    Results for "<?php echo esc_html( $search_query ); ?>"
                <?php else : ?>
                    No results for "<?php echo esc_html( $search_query ); ?>"
                <?php endif; ?>
            </h1>

            <p class="kreativ-page-summary">
                <?php if ( have_posts() ) : ?>
                    <?php echo esc_html( sprintf( '%d result%s across the current library and content.', $result_count, 1 === $result_count ? '' : 's' ) ); ?>
                <?php else : ?>
                    We could not find matching fonts or content. Try a different keyword or browse the main font library instead.
                <?php endif; ?>
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Fonts and resources</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-filter"></i> Search-driven discovery</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Use search as a fast way into the library.</h2>
                <p>Search should feel like part of the same curated experience, not a generic WordPress fallback page.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php get_search_form(); ?>

        <?php if ( have_posts() ) : ?>
            <div class="row kreativ-results-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php
                    kreativ_render_font_card(
                        array(
                            'post_id'         => get_the_ID(),
                            'badge_text'      => 'Result',
                            'badge_slug'      => 'tag',
                            'column_classes'  => 'col-md-4 col-lg-3 col-sm-6',
                            'animation_class' => 'kreativ-card-animate',
                        )
                    );
                    ?>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="kreativ-empty-state">
                <p>Try another search or explore the <a href="<?php echo esc_url( home_url( '/category/fonts' ) ); ?>">Fonts archive</a>.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php get_footer(); ?>
