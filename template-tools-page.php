<?php
/*
Template Name: Tools Page
*/
get_header();
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php $page_summary = kreativ_get_page_summary( get_post() ); ?>

<div class="kreativ-page-shell kreativ-tool-page">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                Font tool
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-bolt"></i> Fast utility</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-layer-group"></i> Font decisions</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Creative support</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Solve the font task, then keep exploring.</h2>
                <p>Use the tool for identification, pairing, naming, or styling, then return to the library when you are ready to choose.</p>
            </div>

            <div class="kreativ-page-card-grid">
                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Type</span>
                    <span class="kreativ-page-mini-value">Tool</span>
                    <p class="kreativ-page-mini-copy">Identify, pair, name, or style fonts</p>
                </div>

                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Context</span>
                    <span class="kreativ-page-mini-value">Fonts</span>
                    <p class="kreativ-page-mini-copy">Supports identification, pairing, and naming</p>
                </div>
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
