<?php
/*
Template Name: Tools Page
*/
get_header();
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$page_summary = kreativ_get_page_summary( get_post() );
$research_pair = 'kreativ-font-pairing-tools' === get_post_field( 'post_name', get_post() ) ? kreativ_get_requested_font_research_pair() : array();
?>

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

        <?php if ( ! empty( $research_pair ) ) : ?>
            <section class="kreativ-pairing-selection" aria-labelledby="kreativ-pairing-selection-title">
                <div>
                    <span class="kreativ-single-quick-label"><i class="fa-solid fa-object-group" aria-hidden="true"></i> Research board</span>
                    <h2 id="kreativ-pairing-selection-title">Selected fonts for comparison</h2>
                </div>
                <div class="kreativ-pairing-selection-grid">
                    <?php foreach ( $research_pair as $pair_font ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $pair_font ) ); ?>">
                            <?php if ( has_post_thumbnail( $pair_font ) ) : ?>
                                <?php echo get_the_post_thumbnail( $pair_font, 'thumbnail', array( 'loading' => 'lazy' ) ); ?>
                            <?php endif; ?>
                            <span><?php echo esc_html( get_the_title( $pair_font ) ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php the_content(); ?>
    </section>
</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
