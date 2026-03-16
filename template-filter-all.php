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

function kf_home_filter_has_terms( $taxonomy, $slugs ) {
    $term_ids = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'slug'       => (array) $slugs,
            'hide_empty' => true,
            'fields'     => 'ids',
        ]
    );

    return ! is_wp_error( $term_ids ) && ! empty( $term_ids );
}

$font_filters = [
    'latest' => [
        'label' => 'Latest',
        'title' => 'Latest Fonts',
        'orderby' => 'date',
        'tax_query' => [],
        'available' => true,
    ],
    'popular' => [
        'label' => 'Popular',
        'title' => 'Popular Fonts',
        'orderby' => 'comment_count',
        'tax_query' => [],
        'available' => true,
    ],
    'free' => [
        'label' => 'Free',
        'title' => 'Free Fonts',
        'orderby' => 'date',
        'tax_query' => [
            [
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => [ 'free' ],
            ],
        ],
        'available' => kf_home_filter_has_terms( 'category', [ 'free' ] ),
    ],
    'serif' => [
        'label' => 'Serif',
        'title' => 'Serif Fonts',
        'orderby' => 'date',
        'tax_query' => [
            [
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => [ 'serif' ],
            ],
        ],
        'available' => kf_home_filter_has_terms( 'post_tag', [ 'serif' ] ),
    ],
    'sans-serif' => [
        'label' => 'Sans Serif',
        'title' => 'Sans Serif Fonts',
        'orderby' => 'date',
        'tax_query' => [
            [
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => [ 'sans-serif', 'sansserif' ],
            ],
        ],
        'available' => kf_home_filter_has_terms( 'post_tag', [ 'sans-serif', 'sansserif' ] ),
    ],
    'script' => [
        'label' => 'Script',
        'title' => 'Script Fonts',
        'orderby' => 'date',
        'tax_query' => [
            [
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => [ 'script' ],
            ],
        ],
        'available' => kf_home_filter_has_terms( 'post_tag', [ 'script' ] ),
    ],
    'display' => [
        'label' => 'Display',
        'title' => 'Display Fonts',
        'orderby' => 'date',
        'tax_query' => [
            [
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => [ 'display' ],
            ],
        ],
        'available' => kf_home_filter_has_terms( 'post_tag', [ 'display' ] ),
    ],
];

$font_filters = array_filter(
    $font_filters,
    static function ( $filter ) {
        return ! empty( $filter['available'] );
    }
);

$active_font_filter = isset( $_GET['font_filter'] ) ? sanitize_key( wp_unslash( $_GET['font_filter'] ) ) : 'latest';

if ( ! isset( $font_filters[ $active_font_filter ] ) ) {
    $active_font_filter = 'latest';
}

$active_font_filter_config = $font_filters[ $active_font_filter ];

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
            <a href="/category/fonts" class="kreativ-hero-cta kreativ-hero-cta-primary">
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
            <span class="kreativ-hero-note"><i class="fa-solid fa-bolt"></i> Practical tools, not filler</span>
            <span class="kreativ-hero-note"><i class="fa-solid fa-sparkles"></i> Discovery and utility in one place</span>
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

        <div class="kreativ-hero-mini-grid">
            <div class="kreativ-hero-mini-card">
                <span class="kreativ-hero-mini-label">Browse</span>
                <span class="kreativ-hero-mini-value">4000+</span>
                <p class="kreativ-hero-mini-copy">curated fonts collected for discovery, comparison, and inspiration</p>
            </div>

            <div class="kreativ-hero-mini-card">
                <span class="kreativ-hero-mini-label">Use</span>
                <span class="kreativ-hero-mini-value">4</span>
                <p class="kreativ-hero-mini-copy">core tools available right from the homepage</p>
            </div>
        </div>
    </div>

    <div class="kreativ-hero-tools">
        <span class="kreativ-hero-tools-label">Tools:</span>

        <a href="/tools/kreativ-font-pairing-tools" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-object-group"></i>
            <span>Font Pairing Tools</span>
            <small class="kreativ-tool-new">NEW</small>
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
     LATEST BY CATEGORY
===================================================== -->
<?php
$home_sections = [
    'fonts' => $active_font_filter_config['title'],
];

foreach ( $home_sections as $slug => $title ) :
    $icon = $kreativ_fa_icons[ $slug ] ?? '';
    $font_tax_query = array_merge(
        [
            [
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => [ $slug ],
            ],
        ],
        $active_font_filter_config['tax_query']
    );
    ?>
    <div class="container kreativ-section kreativ-section-<?php echo esc_attr( $slug ); ?>">

        <div class="kreativ-section-header">
            <h2 class="kreativ-section-title">
                <?php if ( $icon ) : ?>
                    <i class="<?php echo esc_attr( $icon ); ?>"></i>
                <?php endif; ?>
                <?php echo esc_html( $title ); ?>
            </h2>
            <a href="<?php echo esc_url( '/category/' . $slug ); ?>" class="kf-view-all">View All &rsaquo;</a>
        </div>

        <div class="kreativ-font-filter-bar">
            <?php foreach ( $font_filters as $filter_slug => $filter_config ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'font_filter', $filter_slug, get_permalink() ) ); ?>" class="kreativ-font-filter <?php echo $active_font_filter === $filter_slug ? 'active' : ''; ?>">
                    <?php echo esc_html( $filter_config['label'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <?php
            $query = new WP_Query( [
                'posts_per_page'         => 24,
                'post_status'            => 'publish',
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true, // perf
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby'                => $active_font_filter_config['orderby'],
                'order'                  => 'DESC',
                'tax_query'              => $font_tax_query,
            ] );

            if ( $query->have_posts() ) :
                while ( $query->have_posts() ) :
                    $query->the_post();
                    ?>
                    <?php
                    kreativ_render_font_card(
                        array(
                            'post_id'      => get_the_ID(),
                            'badge_text'   => $kreativ_category_labels[ $slug ],
                            'badge_slug'   => $slug,
                        )
                    );
                    ?>
                <?php
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
