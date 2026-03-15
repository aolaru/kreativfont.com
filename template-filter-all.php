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

<style>
/* ---------------------------------------------------------
   GENERAL STYLES
--------------------------------------------------------- */
:root {
    --kf-gradient: linear-gradient(180deg, #fafbff 0%, #f4f6ff 100%);
}

.kreativ-hero {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.9fr);
    gap: 2rem;
    padding: 3.25rem;
    background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.92) 0%, rgba(246, 247, 255, 0.97) 44%, rgba(236, 239, 255, 1) 100%);
    border-radius: 30px;
    margin-bottom: 2.75rem;
    box-shadow: 0 26px 60px rgba(74, 74, 255, 0.08);
}

.kreativ-hero::before,
.kreativ-hero::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
}

.kreativ-hero::before {
    width: 340px;
    height: 340px;
    right: -90px;
    top: -120px;
    background: radial-gradient(circle, rgba(255, 51, 102, 0.22) 0%, rgba(255, 51, 102, 0) 72%);
}

.kreativ-hero::after {
    width: 360px;
    height: 360px;
    left: -160px;
    bottom: -200px;
    background: radial-gradient(circle, rgba(74, 74, 255, 0.18) 0%, rgba(74, 74, 255, 0) 70%);
}

.kreativ-hero-main,
.kreativ-hero-side {
    position: relative;
    z-index: 1;
}

.kreativ-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.74);
    color: #30378e;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    margin-bottom: 1.2rem;
}

.kreativ-hero-title {
    max-width: 10ch;
    font-size: clamp(3rem, 5.2vw, 5rem);
    line-height: 0.9;
    letter-spacing: -0.05em;
    color: #121622;
    margin-bottom: 1.1rem;
}

.kreativ-hero-subtitle {
    font-size: 1.08rem;
    margin-bottom: 1.5rem;
    color: #444d66;
    max-width: 560px;
    line-height: 1.6;
}

.kreativ-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.9rem;
    margin-bottom: 1.8rem;
}

.kreativ-hero-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    min-height: 50px;
    padding: 0.85rem 1.2rem;
    border-radius: 14px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kreativ-hero-cta:hover {
    text-decoration: none;
    transform: translateY(-2px);
}

.kreativ-hero-cta-primary {
    background: linear-gradient(135deg, #4A4AFF 0%, #FF3366 100%);
    color: #fff;
    box-shadow: 0 18px 34px rgba(74, 74, 255, 0.2);
}

.kreativ-hero-cta-secondary {
    background: rgba(255, 255, 255, 0.86);
    color: #24304e;
    border: 1px solid rgba(74, 74, 255, 0.14);
}

.kreativ-hero-notes {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.kreativ-hero-note {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 0.85rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.76);
    color: #3b4260;
    font-size: 0.88rem;
    font-weight: 700;
}

.kreativ-hero-note i {
    color: #4A4AFF;
}

.kreativ-hero-side {
    display: grid;
    gap: 1rem;
}

.kreativ-hero-panel {
    padding: 1.25rem;
    border-radius: 22px;
    background: rgba(18, 22, 34, 0.96);
    color: #fff;
    box-shadow: 0 24px 40px rgba(18, 22, 34, 0.18);
}

.kreativ-hero-panel-label {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 0.9rem;
}

.kreativ-hero-panel-title {
    font-size: 1.6rem;
    line-height: 1.08;
    margin-bottom: 0.8rem;
}

.kreativ-hero-panel-copy {
    margin: 0;
    color: rgba(255, 255, 255, 0.76);
    font-size: 0.96rem;
    line-height: 1.55;
}

.kreativ-hero-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.kreativ-hero-mini-card {
    padding: 1.05rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid rgba(74, 74, 255, 0.08);
}

.kreativ-hero-mini-label {
    display: block;
    color: #70789a;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    margin-bottom: 0.45rem;
}

.kreativ-hero-mini-value {
    display: block;
    color: #121622;
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.kreativ-hero-mini-copy {
    margin: 0;
    color: #535c7c;
    font-size: 0.93rem;
    line-height: 1.45;
}

/* ---------------------------------------------------------
   HERO TOOL SHORTCUTS
--------------------------------------------------------- */
.kreativ-hero-tools {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0;
    padding-top: 0.5rem;
}

.kreativ-hero-tools-label {
    font-weight: 700;
    font-size: 1rem;
    padding: 0.75rem 0.3rem;
    color: #4A4AFF;
}

.kreativ-hero-tool-card {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1.3rem;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e4ea;
    text-decoration: none;
    color: #1a1a1a;
    transition: 0.25s ease;
    font-weight: 600;
}

.kreativ-hero-tool-card i {
    font-size: 1.2rem;
    color: #4A4AFF;
}

.kreativ-hero-tool-card:hover {
    box-shadow: 0px 5px 18px rgba(0,0,0,0.12);
    transform: translateY(-3px);
    border-color: #4A4AFF;
}

.kreativ-tool-new {
    background: #ff3366;
    color: #fff;
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 0.65rem;
    margin-left: 4px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

@media (max-width: 767px) {
    .kreativ-hero {
        grid-template-columns: 1fr;
        padding: 1.6rem;
        border-radius: 24px;
    }

    .kreativ-hero-title {
        max-width: none;
        font-size: 2.5rem;
    }

    .kreativ-hero-actions,
    .kreativ-hero-notes {
        flex-direction: column;
        align-items: stretch;
    }

    .kreativ-hero-cta {
        width: 100%;
    }

    .kreativ-hero-mini-grid {
        grid-template-columns: 1fr;
    }

    .kreativ-hero-tool-card {
        width: 100%;
        justify-content: center;
    }

    .kreativ-font-filter-bar {
        gap: 0.5rem;
    }

    .kreativ-font-filter {
        flex: 1 1 calc(50% - 0.5rem);
    }
}

/* ---------------------------------------------------------
   SECTION WRAPPER
--------------------------------------------------------- */
.kreativ-section {
    padding: 1rem 0;
    background: var(--kf-gradient);
    border-radius: 22px;
    margin-bottom: 3rem;
}

/* ---------------------------------------------------------
   SECTION HEADERS
--------------------------------------------------------- */
.kreativ-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.2rem;
}

.kreativ-section-title {
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.kreativ-section-title i {
    font-size: 1.8rem;
    color: #4A4AFF;
}

.kf-view-all {
    font-size: 1.2rem;
    padding-right: 4px;
    color: #4A4AFF;
    text-decoration: none;
    transition: 0.2s ease;
}

.kf-view-all:hover {
    color: #2d2def;
    transform: translateX(3px);
}

.kreativ-font-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin: 0 0 1.5rem;
}

.kreativ-font-filter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1rem;
    border: 1px solid #d7dbeb;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.92);
    color: #3d43a4;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.kreativ-font-filter:hover {
    border-color: #4A4AFF;
    box-shadow: 0 8px 24px rgba(74, 74, 255, 0.12);
    transform: translateY(-1px);
    text-decoration: none;
}

.kreativ-font-filter.active {
    background: #4A4AFF;
    border-color: #4A4AFF;
    color: #fff;
    box-shadow: 0 10px 24px rgba(74, 74, 255, 0.2);
}

/* ---------------------------------------------------------
   SECTION GRADIENTS MATCHING BADGE COLORS
--------------------------------------------------------- */

/* Fonts — very light blue */
.kreativ-section-fonts {
    background: linear-gradient(135deg, #f8f9ff 0%, #fdfdff 100%) !important;
}

/* ---------------------------------------------------------
   CARDS
--------------------------------------------------------- */
.kreativ-font-card {
    background: #fff;
    padding: 1rem;
    border-radius: 14px;
    margin-bottom: 1rem;
    border: 1px solid #e2e4ea;
    transition: 0.25s ease;
}

.kreativ-font-card:hover {
    box-shadow: 0px 5px 22px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: #4A4AFF;
}

.kreativ-font-card a {
    text-decoration: none;
    color: inherit;
    display: block;
}

.kreativ-card-media {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

.kreativ-card-media img {
    width: 100%;
    display: block;
    transition: transform 0.3s ease;
}

.kreativ-card-media:hover img {
    transform: scale(1.06);
}

/* Grid spacing (gutters) */
.kreativ-section .row {
    margin-left: -10px;
    margin-right: -10px;
}

.kreativ-section .col-md-3,
.kreativ-section .col-sm-6 {
    padding: 10px;
}

/* Category badges */
.kf-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #fff;
    z-index: 2;
}

.kf-badge-fonts            { background: #4A4AFF; }

/* NEW badge */
.kf-badge-new {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    background: #ff3366;
    color: #fff;
    font-size: 0.7rem;
    font-weight: bold;
    letter-spacing: 0.03em;
    z-index: 2;
}

/* Card titles */
.kreativ-font-card h3 {
    font-size: 1rem;
    margin-top: 0.85rem;
    margin-bottom: 0;
    font-weight: 600;
}

/* ---------------------------------------------------------
   ENTRY ANIMATION
--------------------------------------------------------- */
.kreativ-card-animate {
    opacity: 0;
    transform: translateY(12px);
    animation: kfFadeUp 0.6s ease forwards;
}

@keyframes kfFadeUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Small stagger based on column order */
.kreativ-card-animate:nth-child(2) { animation-delay: 0.05s; }
.kreativ-card-animate:nth-child(3) { animation-delay: 0.1s; }
.kreativ-card-animate:nth-child(4) { animation-delay: 0.15s; }

@media (max-width: 767px) {
    .kreativ-section-title {
        font-size: 1.4rem;
    }
}
</style>



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

                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' );
                    $thumb_url = $thumb[0] ?? get_template_directory_uri() . '/img/default-thumb.png';
                    $is_new    = kf_is_new_post( get_the_ID() );
                    ?>
                    <div class="col-md-3 col-sm-6 kreativ-card-animate">
                        <div class="kreativ-font-card">
                            <a href="<?php the_permalink(); ?>">
                                <div class="kreativ-card-media">

                                    <span class="kf-badge kf-badge-<?php echo esc_attr( $slug ); ?>">
                                        <?php echo esc_html( $kreativ_category_labels[ $slug ] ); ?>
                                    </span>

                                    <?php if ( $is_new ) : ?>
                                        <span class="kf-badge-new">NEW</span>
                                    <?php endif; ?>

                                    <img class="lazyload"
                                         loading="lazy"
                                         decoding="async"
                                         alt="<?php the_title_attribute(); ?>"
                                         data-src="<?php echo esc_url( $thumb_url ); ?>"
                                         src="<?php echo esc_url( get_template_directory_uri() . '/img/loading.gif' ); ?>" />
                                </div>
                                <h3><?php the_title(); ?></h3>
                            </a>
                        </div>
                    </div>
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
