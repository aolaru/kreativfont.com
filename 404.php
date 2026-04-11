<?php get_header(); ?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                404 error
            </div>

            <h1 class="kreativ-page-title">Nothing found</h1>

            <p class="kreativ-page-summary">
                The page you were looking for does not exist or may have moved. Search again or jump back into the main font library.
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-magnifying-glass"></i> Search the site</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-house"></i> Return to browsing</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Keep the dead end useful.</h2>
                <p>The 404 page should route people back into search and discovery instead of feeling like a generic fallback.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php get_search_form(); ?>

        <div class="kreativ-empty-state">
            <p><a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>">Browse Fonts</a> or return to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>">homepage</a>.</p>
        </div>

        <section class="kreativ-related-posts">
            <div class="kreativ-section-header">
                <h2 class="kreativ-section-title">
                    <i class="fa-solid fa-compass" aria-hidden="true"></i>
                    Explore More
                </h2>
            </div>

            <div class="row">
                <?php
                $suggested_query = new WP_Query(
                    array(
                        'post_type'           => 'post',
                        'posts_per_page'      => 8,
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
