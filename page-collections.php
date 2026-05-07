<?php
/*
Template Name: Font Collections Hub
*/

get_header();

$kreativ_collection_links = kreativ_get_font_collection_links();
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
                Browse focused dynamic collections built from commercial status, style, mood, and use-case branches.
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Dynamic pages</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-tags" aria-hidden="true"></i> Structured taxonomy</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Updated from published fonts</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Use collections when intent is clearer than browsing.</h2>
                <p>Filters are useful for exploring. Collections are stronger landing pages for common design jobs and search intent.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <div class="kreativ-collections-grid">
            <?php foreach ( $kreativ_collection_links as $collection ) : ?>
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
</div>

<?php get_footer(); ?>
