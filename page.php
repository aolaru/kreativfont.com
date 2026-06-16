<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$is_tool_page       = kreativ_is_tool_page( get_post() );
$page_summary       = kreativ_get_page_summary( get_post() );
$page_shell_classes = 'kreativ-page-shell';

if ( $is_tool_page ) {
    $page_shell_classes .= ' kreativ-tool-page';
}
?>

<div class="<?php echo esc_attr( $page_shell_classes ); ?>">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid <?php echo esc_attr( $is_tool_page ? 'fa-screwdriver-wrench' : 'fa-file-lines' ); ?>" aria-hidden="true"></i>
                <?php echo esc_html( $is_tool_page ? 'Font tool' : 'Page' ); ?>
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <?php if ( $is_tool_page ) : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-bolt"></i> Fast utility</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-layer-group"></i> Font decisions</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Creative support</span>
                <?php else : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Typography content</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-compass"></i> Easy reading</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <?php if ( $is_tool_page ) : ?>
                    <h2>Solve the font task, then keep exploring.</h2>
                    <p>Use the tool for identification, pairing, naming, or styling, then return to the library when you are ready to choose.</p>
                <?php else : ?>
                    <h2>Clear reading without extra clutter.</h2>
                    <p>Read the page, follow useful links, and get back to font browsing when needed.</p>
                <?php endif; ?>
            </div>

            <div class="kreativ-page-card-grid">
                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Type</span>
                    <span class="kreativ-page-mini-value"><?php echo esc_html( $is_tool_page ? 'Tool' : 'Page' ); ?></span>
                    <p class="kreativ-page-mini-copy"><?php echo esc_html( $is_tool_page ? 'Identify, pair, name, or style fonts' : 'Readable reference content' ); ?></p>
                </div>

                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Context</span>
                    <span class="kreativ-page-mini-value">Fonts</span>
                    <p class="kreativ-page-mini-copy"><?php echo esc_html( $is_tool_page ? 'Supports identification, pairing, and naming' : 'Supports browsing and font decisions' ); ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php edit_post_link( 'Edit', '<p>', '</p>' ); ?>
        <?php the_content( 'Read the rest of this page &larr;' ); ?>
    </section>
</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
