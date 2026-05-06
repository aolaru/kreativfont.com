<?php
/*
Template Name: Kreativ Unified Home
*/
get_header();

/**
 * CATEGORY LABELS + ICONS
 */
$kreativ_category_labels = [
    'fonts' => 'Fonts',
];

$kreativ_fa_icons = [
    'fonts' => 'fa-solid fa-font',
];

$font_filters = kreativ_get_font_filters();
$active_font_filter = kreativ_get_active_font_filter( $font_filters );
$active_font_filter_config = $font_filters[ $active_font_filter ];
$free_fonts_slugs = function_exists( 'kreativ_get_free_fonts_category_slugs' ) ? kreativ_get_free_fonts_category_slugs() : array();

?>

<!-- =====================================================
     HERO SECTION WITH TOOLS
===================================================== -->
<div class="kreativ-hero container">
    <div class="kreativ-hero-main">
        <div class="kreativ-hero-eyebrow">
            <i class="fa-solid fa-font"></i>
            Curated fonts and practical tools
        </div>

        <h1 class="kreativ-hero-title">Curated fonts. Practical tools. Faster decisions.</h1>

        <p class="kreativ-hero-subtitle">
            Browse a growing library of curated type, then jump into the tools that help you identify, pair, and name fonts without wasting time.
        </p>

        <div class="kreativ-hero-actions">
            <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">
                <i class="fa-solid fa-compass"></i>
                Browse Fonts
            </a>
            <a href="/tools/kreativ-font-identifier" class="kreativ-hero-cta kreativ-hero-cta-secondary">
                <i class="fa-solid fa-magnifying-glass"></i>
                Identify a Font
            </a>
        </div>

        <div class="kreativ-hero-notes">
            <span class="kreativ-hero-note"><i class="fa-solid fa-layer-group"></i> 4000+ curated fonts</span>
        </div>
    </div>

    <div class="kreativ-hero-side">
        <div class="kreativ-hero-panel">
            <span class="kreativ-hero-panel-label">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Why Kreativ Works
            </span>
            <h2 class="kreativ-hero-panel-title">A fonts-first workflow built for browsing, identifying, and choosing faster.</h2>
            <p class="kreativ-hero-panel-copy">Kreativ brings curation and utility together, so discovery does not stop at inspiration and tools do not live in a separate silo.</p>
        </div>

    </div>

    <div class="kreativ-hero-tools">
        <span class="kreativ-hero-tools-label">Tools:</span>

        <a href="/tools/kreativ-font-pairing-tools" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-object-group"></i>
            <span>Font Pairing Tools</span>
        </a>

        <a href="/tools/kreativ-font-identifier" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-search"></i>
            <span>Font Identifier</span>
        </a>

        <a href="/tools/fancy-text-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Fancy Text Generator</span>
        </a>

        <a href="/tools/kreativ-font-name-generator" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Font Name Generator</span>
        </a>
    </div>
</div>


<!-- =====================================================
     CURATED COLLECTIONS
===================================================== -->
<?php
$kreativ_collection_links = array(
    array(
        'title' => 'Best Modern Sans Serif Fonts',
        'url'   => home_url( '/best-modern-sans-serif-fonts' ),
        'icon'  => 'fa-solid fa-circle-half-stroke',
        'copy'  => 'Clean sans serif picks for modern interfaces, brands, and editorial systems.',
    ),
    array(
        'title' => 'Best Elegant Serif Fonts',
        'url'   => home_url( '/best-elegant-serif-fonts' ),
        'icon'  => 'fa-solid fa-feather-pointed',
        'copy'  => 'Refined serif choices for luxury, fashion, publishing, and premium branding.',
    ),
    array(
        'title' => 'Best Logo Fonts',
        'url'   => home_url( '/best-logo-fonts' ),
        'icon'  => 'fa-solid fa-signature',
        'copy'  => 'Typeface shortcuts for marks, identities, wordmarks, and brand systems.',
    ),
    array(
        'title' => 'Best Fonts for Branding',
        'url'   => home_url( '/best-fonts-for-branding' ),
        'icon'  => 'fa-solid fa-bullseye',
        'copy'  => 'Fonts selected around identity work, client projects, and visual positioning.',
    ),
    array(
        'title' => 'Best Wedding Fonts',
        'url'   => home_url( '/best-wedding-fonts' ),
        'icon'  => 'fa-solid fa-ring',
        'copy'  => 'Elegant, romantic, and decorative fonts for invitations and event design.',
    ),
    array(
        'title' => 'Best Poster Fonts',
        'url'   => home_url( '/best-poster-fonts' ),
        'icon'  => 'fa-solid fa-rectangle-ad',
        'copy'  => 'Display-ready fonts for campaigns, posters, covers, and bold compositions.',
    ),
    array(
        'title' => 'Best Minimal Fonts',
        'url'   => home_url( '/best-minimal-fonts' ),
        'icon'  => 'fa-solid fa-minus',
        'copy'  => 'Quiet, reduced, and practical fonts for clean visual systems.',
    ),
);
?>

<section class="container kreativ-section kreativ-collections-section">
    <div class="kreativ-section-header">
        <div class="kreativ-section-heading">
            <span class="kreativ-section-eyebrow">Curated collections</span>
            <h2 class="kreativ-section-title">
                <i class="fa-solid fa-compass"></i>
                Explore fonts by intent
            </h2>
            <p class="kreativ-section-summary">Focused dynamic pages built from style, mood, and use-case branches.</p>
        </div>
    </div>

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



<!-- =====================================================
     LATEST BY CATEGORY
===================================================== -->
<?php
$home_sections = array();

if ( 'latest' === $active_font_filter && $free_fonts_slugs ) {
    $home_sections[] = array(
        'slug'       => 'fonts',
        'title'      => 'Latest Commercial Fonts',
        'eyebrow'    => 'Commercial picks',
        'summary'    => 'Fresh premium releases for branding, packaging, editorial work, and sharper client-facing font choices.',
        'view_all'   => home_url( '/fonts' ),
        'show_filters' => true,
        'posts_per_page' => 12,
        'tax_query'  => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array( 'fonts' ),
            ),
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $free_fonts_slugs,
                'operator' => 'NOT IN',
            ),
        ),
    );

    $home_sections[] = array(
        'slug'       => 'fonts',
        'title'      => 'Latest Free Fonts',
        'eyebrow'    => 'Free to use',
        'summary'    => 'New free fonts collected for quick experimentation, downloads, and lower-friction creative exploration.',
        'view_all'   => add_query_arg( 'font_filter', 'free', home_url( '/fonts' ) ),
        'show_filters' => false,
        'posts_per_page' => 12,
        'tax_query'  => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array( 'fonts' ),
            ),
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $free_fonts_slugs,
            ),
        ),
    );
} else {
    $home_sections[] = array(
        'slug'       => 'fonts',
        'title'      => $active_font_filter_config['title'],
        'eyebrow'    => 'Browse the library',
        'summary'    => 'Explore the current font stream by style, mood, and discovery intent without leaving the homepage.',
        'view_all'   => add_query_arg( 'font_filter', $active_font_filter, home_url( '/fonts' ) ),
        'show_filters' => true,
        'posts_per_page' => 24,
        'tax_query'  => array_merge(
            array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => array( 'fonts' ),
                ),
            ),
            $active_font_filter_config['tax_query']
        ),
    );
}

foreach ( $home_sections as $section ) :
    $slug = $section['slug'];
    $icon = $kreativ_fa_icons[ $slug ] ?? '';
    ?>
    <div class="container kreativ-section kreativ-section-<?php echo esc_attr( $slug ); ?>">

        <div class="kreativ-section-header">
            <div class="kreativ-section-heading">
                <?php if ( ! empty( $section['eyebrow'] ) ) : ?>
                    <span class="kreativ-section-eyebrow"><?php echo esc_html( $section['eyebrow'] ); ?></span>
                <?php endif; ?>
                <h2 class="kreativ-section-title">
                    <?php if ( $icon ) : ?>
                        <i class="<?php echo esc_attr( $icon ); ?>"></i>
                    <?php endif; ?>
                    <?php echo esc_html( $section['title'] ); ?>
                </h2>
                <?php if ( ! empty( $section['summary'] ) ) : ?>
                    <p class="kreativ-section-summary"><?php echo esc_html( $section['summary'] ); ?></p>
                <?php endif; ?>
            </div>
            <a href="<?php echo esc_url( $section['view_all'] ); ?>" class="kf-view-all">View All &rsaquo;</a>
        </div>

        <?php if ( ! empty( $section['show_filters'] ) ) : ?>
            <div class="kreativ-font-filter-bar">
                <?php foreach ( $font_filters as $filter_slug => $filter_config ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'font_filter', $filter_slug, get_permalink() ) ); ?>" class="kreativ-font-filter <?php echo $active_font_filter === $filter_slug ? 'active' : ''; ?>">
                        <?php echo esc_html( $filter_config['label'] ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php
            $query = new WP_Query( array(
                'posts_per_page'         => $section['posts_per_page'],
                'post_status'            => 'publish',
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby'                => 'latest' === $active_font_filter ? 'date' : $active_font_filter_config['orderby'],
                'order'                  => 'DESC',
                'tax_query'              => $section['tax_query'],
            ) );

            if ( $query->have_posts() ) :
                while ( $query->have_posts() ) :
                    $query->the_post();
                    kreativ_render_font_card(
                        array(
                            'post_id'    => get_the_ID(),
                            'badge_text' => $kreativ_category_labels[ $slug ],
                            'badge_slug' => $slug,
                        )
                    );
                endwhile;
            else :
                ?>
                <div class="col-12">
                    <p class="text-center">No fonts found for this filter yet.</p>
                </div>
                <?php
            endif;
            wp_reset_postdata();
            ?>
        </div>
    </div>
<?php endforeach; ?>

<?php get_footer(); ?>
