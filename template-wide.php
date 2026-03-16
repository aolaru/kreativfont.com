<?php
/*
Template Name: Wide page
*/
?>
<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php $page_summary = kreativ_get_page_summary( get_post() ); ?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i>
                Wide layout page
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Extra room for richer content and custom layouts.</h2>
                <p>This template keeps the same visual language as the rest of Kreativ Font while giving wide-format pages a more polished frame.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php edit_post_link( 'Edit', '<p>', '</p>' ); ?>
        <?php the_content(); ?>
    </section>
</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
