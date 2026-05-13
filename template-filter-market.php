<?php
/*
Quarantined Template: Legacy Market Archive
Formerly exposed as a page template. Kept only for backward compatibility if an existing page is still assigned to this file.
*/
get_header();

$kreativ_category_labels = array(
    'fonts'            => 'Fonts',
    'templates-themes' => 'Templates',
    'graphics'         => 'Graphics',
    'photos'           => 'Photos',
    'videos'           => 'Videos',
    'sounds'           => 'Sounds',
    'free'             => 'Freebies',
);
$kreativ_fa_icons        = array(
    'fonts'            => 'fa-solid fa-font',
    'templates-themes' => 'fa-solid fa-layer-group',
    'graphics'         => 'fa-solid fa-pen-nib',
    'photos'           => 'fa-solid fa-camera',
    'videos'           => 'fa-solid fa-film',
    'sounds'           => 'fa-solid fa-music',
    'free'             => 'fa-solid fa-gift',
);
$market_sections         = array(
    'fonts'            => 'Latest Fonts',
    'templates-themes' => 'Latest Templates',
    'graphics'         => 'Latest Graphics',
    'photos'           => 'Latest Photos',
    'videos'           => 'Latest Videos',
    'sounds'           => 'Latest Sounds',
    'free'             => 'Latest Freebies',
);
?>

<div class="kreativ-hero container">
    <div class="kreativ-hero-main">
        <div class="kreativ-hero-eyebrow">
            <i class="fa-solid fa-store" aria-hidden="true"></i>
            Legacy archive
        </div>

        <h1 class="kreativ-hero-title">Legacy multi-resource archive preserved for reference only.</h1>

        <p class="kreativ-hero-subtitle">
            This template preserves the older broader-catalog view, but the active product is now focused on fonts and font tools.
        </p>
    </div>

    <div class="kreativ-hero-side">
        <div class="kreativ-hero-panel">
            <span class="kreativ-hero-panel-label">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                Legacy surface
            </span>
            <h2 class="kreativ-hero-panel-title">Kept available for backward compatibility, not as a primary product path.</h2>
            <p class="kreativ-hero-panel-copy">It reuses the shared visual system, but should be treated as a legacy archive rather than an active site direction.</p>
        </div>
    </div>

    <div class="kreativ-hero-tools">
        <span class="kreativ-hero-tools-label">Tools:</span>

        <a href="/tools/kreativ-font-pairing-tools" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-object-group" aria-hidden="true"></i>
            <span>Font Pairing Tools</span>
        </a>

        <a href="/tools/kreativ-font-identifier" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span>Font Identifier</span>
        </a>

        <a href="/tools/fancy-text-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
            <span>Fancy Text Generator</span>
        </a>

        <a href="/tools/kreativ-font-name-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
            <span>Font Name Generator</span>
        </a>
    </div>
</div>

<?php foreach ( $market_sections as $slug => $title ) : ?>
    <?php $icon = $kreativ_fa_icons[ $slug ] ?? ''; ?>
    <div class="container kreativ-section kreativ-section-<?php echo esc_attr( $slug ); ?>">
        <div class="kreativ-section-header">
            <h2 class="kreativ-section-title">
                <?php if ( $icon ) : ?>
                    <i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo esc_html( $title ); ?>
            </h2>
            <?php
            if ( 'fonts' === $slug ) {
                $section_url = home_url( '/fonts' );
            } elseif ( 'free' === $slug ) {
                $section_url = add_query_arg( 'font_filter', 'free', home_url( '/fonts' ) );
            } else {
                $section_url = home_url( '/category/' . $slug );
            }
            ?>
            <a href="<?php echo esc_url( $section_url ); ?>" class="kf-view-all">View All &rsaquo;</a>
        </div>

        <div class="row">
            <?php
            $market_query = new WP_Query(
                array(
                    'posts_per_page'      => 8,
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => true,
                    'no_found_rows'       => true,
                    'tax_query'           => array(
                        array(
                            'taxonomy' => 'category',
                            'field'    => 'slug',
                            'terms'    => array( $slug ),
                        ),
                    ),
                )
            );

            if ( $market_query->have_posts() ) :
                while ( $market_query->have_posts() ) :
                    $market_query->the_post();

                    kreativ_render_font_card(
                        array(
                            'post_id'      => get_the_ID(),
                            'badge_text'   => $kreativ_category_labels[ $slug ] ?? ucfirst( $slug ),
                            'badge_slug'   => in_array( $slug, array( 'fonts', 'free' ), true ) ? $slug : 'tag',
                        )
                    );
                endwhile;
            else :
                ?>
                <div class="col-12">
                    <p class="text-center">No resources found in this section yet.</p>
                </div>
                <?php
            endif;
            wp_reset_postdata();
            ?>
        </div>
    </div>
<?php endforeach; ?>

<?php get_footer(); ?>
