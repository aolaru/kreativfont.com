<?php
$post         = get_post( $post ?? null );
$content_kind = isset( $content_kind ) ? $content_kind : 'article';

if ( ! $post instanceof WP_Post ) {
    return;
}

// The identifier owns its compact tool UI; never wrap it in the generic hero.
if ( kreativ_is_identifier_page( $post ) ) {
    kreativ_render_partial( 'partials/font-identifier.php' );
    return;
}

if ( function_exists( 'kreativ_get_creative_tool_config' ) && kreativ_get_creative_tool_config( $post ) ) {
    kreativ_render_partial( 'partials/creative-tool.php', array( 'post' => $post ) );
    return;
}

$config = array(
    'tool' => array(
        'eyebrow'          => 'Font tool',
        'icon'             => 'fa-solid fa-screwdriver-wrench',
        'shell_class'      => ' kreativ-tool-page',
        'breadcrumb_label' => 'Tools',
        'breadcrumb_url'   => home_url( '/tools' ),
        'badges'           => array(
            array( 'icon' => 'fa-solid fa-bolt', 'label' => 'Fast utility' ),
            array( 'icon' => 'fa-solid fa-wand-magic-sparkles', 'label' => 'Creative support' ),
        ),
    ),
    'information' => array(
        'eyebrow'          => 'Information',
        'icon'             => 'fa-solid fa-circle-info',
        'shell_class'      => '',
        'breadcrumb_label' => '',
        'breadcrumb_url'   => '',
        'badges'           => array(
            array( 'icon' => 'fa-solid fa-file-lines', 'label' => 'Site information' ),
        ),
    ),
    'article' => array(
        'eyebrow'          => 'Article',
        'icon'             => 'fa-solid fa-newspaper',
        'shell_class'      => '',
        'breadcrumb_label' => 'Blog',
        'breadcrumb_url'   => home_url( '/blog' ),
        'badges'           => array(
            array( 'icon' => 'fa-solid fa-calendar', 'label' => get_the_date( 'F j, Y', $post ) ),
        ),
    ),
);

$view         = $config[ $content_kind ] ?? $config['article'];
$page_summary = kreativ_get_content_summary( $post, 28, false );
$is_fancy_generator = 224093 === (int) ( $post->ID ?? 0 ) && has_shortcode( $post->post_content, 'kreativ_font_translator' );
?>

<div class="<?php echo esc_attr( 'kreativ-page-shell' . $view['shell_class'] . ( $is_fancy_generator ? ' kft-tool-page' : '' ) ); ?>">
    <?php if ( $is_fancy_generator ) : ?>
        <section class="kft-page-header" aria-labelledby="kft-page-title">
            <nav class="kft-page-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
                <a href="<?php echo esc_url( home_url( '/tools' ) ); ?>">Tools</a>
            </nav>
            <div class="kft-page-heading">
                <div class="kft-page-copy">
                    <h1 id="kft-page-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>
                    <p class="kft-page-summary">Create copyable text with 20 Unicode styles, or save 18 typography presets as PNG images.</p>
                    <ol class="kft-page-steps" role="list" aria-label="Create fancy text in three steps">
                        <li><span class="kft-step-number" aria-hidden="true">1</span><span>Enter text</span></li>
                        <li><span class="kft-step-number" aria-hidden="true">2</span><span>Choose a style</span></li>
                        <li><span class="kft-step-number" aria-hidden="true">3</span><span>Copy or download</span></li>
                    </ol>
                </div>
                <?php if ( has_post_thumbnail( $post ) ) : ?>
                    <figure class="kft-featured-artwork"><?php echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?></figure>
                <?php endif; ?>
            </div>
            <div class="kft-page-meta"><span>Free · No sign-up · Runs in your browser</span>
                <nav aria-label="Generator guide"><a href="#how-to-use">How to use</a><a href="#choose-your-format">Text or image?</a><a href="#generator-faq">FAQs</a></nav>
            </div>
        </section>
    <?php else : ?>
    <section class="kreativ-page-hero kreativ-page-hero-compact">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="<?php echo esc_attr( $view['icon'] ); ?>" aria-hidden="true"></i>
                <?php echo esc_html( $view['eyebrow'] ); ?>
            </div>

            <h1 class="kreativ-page-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>

            <?php if ( $page_summary ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <?php foreach ( $view['badges'] as $badge ) : ?>
                    <span class="kreativ-page-badge">
                        <i class="<?php echo esc_attr( $badge['icon'] ); ?>" aria-hidden="true"></i>
                        <?php echo esc_html( $badge['label'] ); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php endif; ?>

    <section class="kreativ-page-content">
        <?php if ( ! $is_fancy_generator ) : ?>
        <nav class="kreativ-post-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <?php if ( $view['breadcrumb_label'] && $view['breadcrumb_url'] ) : ?>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url( $view['breadcrumb_url'] ); ?>"><?php echo esc_html( $view['breadcrumb_label'] ); ?></a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <article class="kreativ-post-content">
            <?php the_content(); ?>
        </article>

        <?php edit_post_link( 'Edit', '<p class="kreativ-post-meta-item kreativ-post-meta-edit">', '</p>' ); ?>
    </section>
</div>
