<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$page_summary = kreativ_get_page_summary( get_post() );
$categories   = get_the_category();
$primary_cat  = ! empty( $categories ) ? $categories[0]->name : 'Article';
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <?php echo esc_html( $primary_cat ); ?>
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-calendar"></i> <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-user"></i> <?php the_author(); ?></span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Browse the article, then continue exploring related type.</h2>
                <p>This template now matches the rest of the site instead of dropping into an older standalone article layout.</p>
            </div>

            <div class="kreativ-page-card-grid">
                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Category</span>
                    <span class="kreativ-page-mini-value"><?php echo esc_html( $primary_cat ); ?></span>
                    <p class="kreativ-page-mini-copy">Primary context for this article</p>
                </div>

                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Format</span>
                    <span class="kreativ-page-mini-value">Post</span>
                    <p class="kreativ-page-mini-copy">Editorial content inside the Kreativ system</p>
                </div>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <nav class="kreativ-post-nav" aria-label="Post navigation">
            <div><?php next_post_link( '%link', '<i class="fa-solid fa-arrow-left"></i> Newer' ); ?></div>
            <div><?php previous_post_link( '%link', 'Older <i class="fa-solid fa-arrow-right"></i>' ); ?></div>
        </nav>

        <p class="kreativ-post-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <span>/</span>
            <?php the_category( ' / ' ); ?>
        </p>

        <article class="kreativ-post-content">
            <?php the_content(); ?>
        </article>

        <?php if ( get_the_tags() ) : ?>
            <div class="kreativ-post-tags">
                <strong>Tags:</strong> <?php the_tags( '', ', ', '' ); ?>
            </div>
        <?php endif; ?>

        <div class="kreativ-post-meta">
            Published on <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
            in <?php the_category( ', ' ); ?> by <?php the_author(); ?>.
            <?php edit_post_link( 'Edit', ' | ', '' ); ?>
        </div>

        <section class="kreativ-related-posts">
            <div class="kreativ-section-header">
                <h2 class="kreativ-section-title">
                    <i class="fa-solid fa-compass" aria-hidden="true"></i>
                    You Might Also Like
                </h2>
            </div>

            <div class="row">
                <?php
                $related_query = new WP_Query(
                    array(
                        'post_type'           => 'post',
                        'posts_per_page'      => 4,
                        'post__not_in'        => array( get_the_ID() ),
                        'ignore_sticky_posts' => true,
                        'orderby'             => 'rand',
                        'category__in'        => wp_get_post_categories( get_the_ID() ),
                    )
                );

                if ( $related_query->have_posts() ) :
                    while ( $related_query->have_posts() ) :
                        $related_query->the_post();
                        list( $badge_slug, $badge_text ) = kreativ_get_primary_category_badge( get_the_ID() );
                        kreativ_render_font_card(
                            array(
                                'post_id'        => get_the_ID(),
                                'badge_text'     => $badge_text ? $badge_text : 'Related',
                                'badge_slug'     => $badge_slug ? $badge_slug : 'tag',
                                'column_classes' => 'col-md-3 col-sm-6',
                            )
                        );
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="col-12">
                        <p class="text-center">No related posts found.</p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
        </section>
    </section>
</div>

<?php endwhile; else : ?>
    <div class="kreativ-page-shell">
        <section class="kreativ-page-content">
            <div class="kreativ-empty-state">
                <h2>Sorry, no articles matched your criteria.</h2>
                <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">Back to Home</a></p>
            </div>
        </section>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
