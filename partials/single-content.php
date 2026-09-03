<?php
$post         = get_post( $post ?? null );
$content_kind = isset( $content_kind ) ? $content_kind : 'article';

if ( ! $post instanceof WP_Post ) {
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
$page_summary = kreativ_get_content_summary( $post, 28, 'tool' !== $content_kind );
?>

<div class="<?php echo esc_attr( 'kreativ-page-shell' . $view['shell_class'] ); ?>">
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

    <section class="kreativ-page-content">
        <nav class="kreativ-post-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <?php if ( $view['breadcrumb_label'] && $view['breadcrumb_url'] ) : ?>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url( $view['breadcrumb_url'] ); ?>"><?php echo esc_html( $view['breadcrumb_label'] ); ?></a>
            <?php endif; ?>
        </nav>

        <article class="kreativ-post-content">
            <?php the_content(); ?>
        </article>

        <?php edit_post_link( 'Edit', '<p class="kreativ-post-meta-item kreativ-post-meta-edit">', '</p>' ); ?>
    </section>
</div>
