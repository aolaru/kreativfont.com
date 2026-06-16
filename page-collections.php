<?php
/*
Template Name: Font Collections Hub
*/

get_header();

$kreativ_collection_links = kreativ_get_font_collection_links();
$kreativ_collection_groups = array(
    'popular'   => array(
        'label' => 'Popular',
        'icon'  => 'fa-solid fa-star',
        'title' => 'Popular starting points',
        'copy'  => 'Fast routes into the collections most visitors need first.',
    ),
    'licensing' => array(
        'label' => 'Free & Licensing',
        'icon'  => 'fa-solid fa-shield-halved',
        'title' => 'Free and license-aware options',
        'copy'  => 'Start here when budget and commercial-use clarity matter.',
    ),
    'style'     => array(
        'label' => 'Style & Mood',
        'icon'  => 'fa-solid fa-wand-magic-sparkles',
        'title' => 'Style and mood shortcuts',
        'copy'  => 'Browse by the visual tone you want the type to carry.',
    ),
    'use_case'  => array(
        'label' => 'Use Case',
        'icon'  => 'fa-solid fa-briefcase',
        'title' => 'Project-specific collections',
        'copy'  => 'Pick fonts around the design job instead of starting from a blank library.',
    ),
);
$kreativ_grouped_collections = array_fill_keys( array_keys( $kreativ_collection_groups ), array() );

foreach ( $kreativ_collection_links as $collection ) {
    $group_key = $collection['group'] ?? 'popular';

    if ( ! isset( $kreativ_grouped_collections[ $group_key ] ) ) {
        $group_key = 'popular';
    }

    $kreativ_grouped_collections[ $group_key ][] = $collection;
}
?>

<div class="kreativ-page-shell kreativ-collections-hub">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-compass" aria-hidden="true"></i>
                Font collections
            </div>

            <h1 class="kreativ-page-title">Curated Font Collections</h1>

            <p class="kreativ-page-summary">
                Find better font options by project goal, licensing need, visual style, or use case.
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Commercial</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-gift" aria-hidden="true"></i> Free</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Style & Mood</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Use Case</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Start with the job, then pick the font.</h2>
                <p>Compare useful font options without starting from scratch.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content kreativ-collections-content" aria-label="Font collection groups">
        <div class="kreativ-collection-groups">
            <?php foreach ( $kreativ_collection_groups as $group_key => $group ) : ?>
                <?php if ( empty( $kreativ_grouped_collections[ $group_key ] ) ) { continue; } ?>
                <section class="kreativ-collection-group" aria-labelledby="kreativ-collection-group-<?php echo esc_attr( sanitize_html_class( $group_key ) ); ?>">
                    <div class="kreativ-collection-group-head">
                        <span class="kreativ-collection-group-label">
                            <i class="<?php echo esc_attr( $group['icon'] ); ?>" aria-hidden="true"></i>
                            <?php echo esc_html( $group['label'] ); ?>
                        </span>
                        <h2 id="kreativ-collection-group-<?php echo esc_attr( sanitize_html_class( $group_key ) ); ?>"><?php echo esc_html( $group['title'] ); ?></h2>
                        <p><?php echo esc_html( $group['copy'] ); ?></p>
                    </div>

                    <div class="kreativ-collections-grid">
                        <?php foreach ( $kreativ_grouped_collections[ $group_key ] as $collection ) : ?>
                            <a href="<?php echo esc_url( $collection['url'] ); ?>" class="kreativ-collection-card">
                                <span class="kreativ-collection-icon">
                                    <i class="<?php echo esc_attr( $collection['icon'] ); ?>" aria-hidden="true"></i>
                                </span>
                                <span class="kreativ-collection-content">
                                    <strong><?php echo esc_html( $collection['title'] ); ?></strong>
                                    <span><?php echo esc_html( $collection['copy'] ); ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>
