<?php
/*
Template Name: Filter Free
*/
get_header();
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-gift" aria-hidden="true"></i>
                Free font downloads
            </div>

            <h1 class="kreativ-page-title">Discover new free fonts every week.</h1>

            <p class="kreativ-page-summary">
                A growing collection of free fonts for designers who still need clear previews, cleaner browsing, and a faster path to download.
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-download"></i> Download-ready</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-tag"></i> Free resource stream</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Typography focused</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Free fonts deserve the same presentation quality as the paid library.</h2>
                <p>This template now uses the same card system and cleaner page framing as the rest of the theme, instead of the older ad hoc grid markup.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <div class="container kreativ-section kreativ-section-free">
            <div class="kreativ-section-header">
                <h2 class="kreativ-section-title">
                    <i class="fa-solid fa-gift" aria-hidden="true"></i>
                    Free Font Downloads
                </h2>
            </div>

            <div class="row">
                <?php
                $free_query = new WP_Query(
                    array(
                        'post_type'           => 'download',
                        'posts_per_page'      => 100,
                        'post_status'         => 'publish',
                        'ignore_sticky_posts' => true,
                        'tax_query'           => array(
                            array(
                                'taxonomy' => 'download_category',
                                'field'    => 'name',
                                'terms'    => array( 'Free' ),
                            ),
                        ),
                    )
                );

                if ( $free_query->have_posts() ) :
                    while ( $free_query->have_posts() ) :
                        $free_query->the_post();

                        $card = kreativ_get_font_card_args(
                            array(
                                'post_id'        => get_the_ID(),
                                'badge_text'     => 'Free',
                                'badge_slug'     => 'free',
                                'column_classes' => 'col-md-4 col-lg-3 col-sm-6',
                            )
                        );
                        ?>
                        <div class="<?php echo esc_attr( trim( $card['column_classes'] . ' kreativ-card-animate' ) ); ?>">
                            <div class="kreativ-font-card">
                                <a href="<?php echo esc_url( $card['permalink'] ); ?>">
                                    <div class="kreativ-card-media">
                                        <span class="kf-badge kf-badge-free">Free</span>

                                        <?php if ( ! empty( $card['show_new_badge'] ) ) : ?>
                                            <span class="kf-badge-new"><?php echo esc_html( $card['new_label'] ); ?></span>
                                        <?php endif; ?>

                                        <img class="lazyload"
                                            loading="lazy"
                                            decoding="async"
                                            alt="<?php echo esc_attr( $card['title_attr'] ); ?>"
                                            data-src="<?php echo esc_url( $card['thumb_url'] ); ?>"
                                            src="<?php echo esc_url( $card['loading_thumb_url'] ); ?>" />
                                    </div>
                                    <h3><?php echo esc_html( $card['title'] ); ?></h3>
                                </a>

                                <p class="kreativ-card-action">
                                    <a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'add_to_cart', 'download_id' => get_the_ID() ), home_url( '/checkout/' ) ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">
                                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                                        Download Free Font
                                    </a>
                                </p>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else :
                    ?>
                    <div class="col-12">
                        <h2 class="text-center my-5">Sorry, no free fonts found.</h2>
                    </div>
                    <?php
                endif;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
