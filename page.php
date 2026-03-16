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
                <?php echo esc_html( $is_tool_page ? 'Kreativ font tool' : 'Kreativ page' ); ?>
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <?php if ( $is_tool_page ) : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-bolt"></i> Practical workflow</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-layer-group"></i> Built for font decisions</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Part of Kreativ Tools</span>
                <?php else : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Typography-focused content</span>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-compass"></i> Part of Kreativ Font</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <?php if ( $is_tool_page ) : ?>
                    <h2>Use the tool, then keep exploring the library.</h2>
                    <p>These pages are designed to help you move quickly from a font problem to a usable answer, without leaving the broader Kreativ font ecosystem.</p>
                <?php else : ?>
                    <h2>Focused pages with less clutter and clearer reading flow.</h2>
                    <p>Kreativ pages should feel like part of the same fonts-first product, even when they are not archive or homepage views.</p>
                <?php endif; ?>
            </div>

            <div class="kreativ-page-card-grid">
                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Type</span>
                    <span class="kreativ-page-mini-value"><?php echo esc_html( $is_tool_page ? 'Tool' : 'Page' ); ?></span>
                    <p class="kreativ-page-mini-copy"><?php echo esc_html( $is_tool_page ? 'Task-focused utility experience' : 'Editorial or informational content' ); ?></p>
                </div>

                <div class="kreativ-page-mini-card">
                    <span class="kreativ-page-mini-label">Context</span>
                    <span class="kreativ-page-mini-value"><?php echo esc_html( $is_tool_page ? 'Fonts' : 'Kreativ' ); ?></span>
                    <p class="kreativ-page-mini-copy"><?php echo esc_html( $is_tool_page ? 'Supports identification, pairing, and naming' : 'Part of the broader brand system' ); ?></p>
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
