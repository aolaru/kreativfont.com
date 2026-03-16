<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$page_summary = kreativ_get_page_summary( get_post() );
$categories   = get_the_category();
$primary_cat  = ! empty( $categories ) ? $categories[0]->name : 'Article';
$page_summary = wp_trim_words( wp_strip_all_tags( $page_summary ), 24, '...' );
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero kreativ-page-hero-compact">
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
