<?php
get_header();

$kreativ_404_collections = array();

foreach ( kreativ_get_font_collection_links() as $collection ) {
    if ( ! empty( $collection['featured'] ) ) {
        $kreativ_404_collections[] = $collection;
    }

    if ( 4 <= count( $kreativ_404_collections ) ) {
        break;
    }
}
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                404 error
            </div>

            <h1 class="kreativ-page-title">Page not found</h1>

            <p class="kreativ-page-summary">
                The page you were looking for may have moved. Search the font library or jump into one of the main browsing paths.
            </p>

            <div class="kreativ-page-badges">
                <a class="kreativ-page-badge" href="<?php echo esc_url( home_url( '/fonts' ) ); ?>"><i class="fa-solid fa-font" aria-hidden="true"></i> Browse Fonts</a>
                <a class="kreativ-page-badge" href="<?php echo esc_url( home_url( '/collections' ) ); ?>"><i class="fa-solid fa-compass" aria-hidden="true"></i> View Collections</a>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Let's find the right font instead.</h2>
                <p>Use search, browse the main library, or start from a focused collection.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php get_search_form(); ?>

        <?php if ( ! empty( $kreativ_404_collections ) ) : ?>
            <section class="kreativ-404-section">
                <div class="kreativ-section-header">
                    <div class="kreativ-section-heading">
                        <span class="kreativ-section-eyebrow">Recommended paths</span>
                        <h2 class="kreativ-section-title">
                            <i class="fa-solid fa-compass" aria-hidden="true"></i>
                            Browse by intent
                        </h2>
                        <p class="kreativ-section-summary">Start from one of the main dynamic collections instead of guessing the old URL.</p>
                    </div>
                </div>

                <div class="kreativ-collections-grid">
                    <?php foreach ( $kreativ_404_collections as $collection ) : ?>
                        <a href="<?php echo esc_url( $collection['url'] ); ?>" class="kreativ-collection-card">
                            <span class="kreativ-collection-icon">
                                <i class="<?php echo esc_attr( $collection['icon'] ); ?>" aria-hidden="true"></i>
                            </span>
                            <span class="kreativ-collection-content">
                                <strong><?php echo esc_html( $collection['title'] ); ?></strong>
                                <span><?php echo esc_html( $collection['copy'] ); ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="kreativ-related-posts">
            <div class="kreativ-section-header">
                <div class="kreativ-section-heading">
                    <span class="kreativ-section-eyebrow">Fresh from the library</span>
                    <h2 class="kreativ-section-title">
                        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                        Recently Added Fonts
                    </h2>
                    <p class="kreativ-section-summary">A few recent font posts to help you continue browsing.</p>
                </div>
            </div>

            <div class="row">
                <?php
                $suggested_query = new WP_Query(
                    array(
                        'post_type'           => 'post',
                        'posts_per_page'      => 4,
                        'ignore_sticky_posts' => true,
                    )
                );

                if ( $suggested_query->have_posts() ) :
                    while ( $suggested_query->have_posts() ) :
                        $suggested_query->the_post();
                        list( $badge_slug, $badge_text ) = kreativ_get_primary_category_badge( get_the_ID() );
                        kreativ_render_font_card(
                            array(
                                'post_id'        => get_the_ID(),
                                'badge_text'     => $badge_text ? $badge_text : 'Explore',
                                'badge_slug'     => $badge_slug ? $badge_slug : 'tag',
                                'column_classes' => 'col-md-3 col-sm-6',
                            )
                        );
                    endwhile;
                else :
                    ?>
                    <div class="col-12">
                        <p class="text-center">No posts available right now.</p>
                    </div>
                    <?php
                endif;
                wp_reset_postdata();
                ?>
            </div>
        </section>
    </section>
</div>

<?php get_footer(); ?>
