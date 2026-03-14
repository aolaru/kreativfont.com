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

$font_filters = [
    'latest' => [
        'label' => 'Latest',
        'title' => 'Latest Fonts',
        'orderby' => 'date',
        'tax_query' => [],
    ],
    'popular' => [
        'label' => 'Popular',
        'title' => 'Popular Fonts',
        'orderby' => 'comment_count',
        'tax_query' => [],
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
    ],
];

$active_font_filter = isset( $_GET['font_filter'] ) ? sanitize_key( wp_unslash( $_GET['font_filter'] ) ) : 'latest';

if ( ! isset( $font_filters[ $active_font_filter ] ) ) {
    $active_font_filter = 'latest';
}

$active_font_filter_config = $font_filters[ $active_font_filter ];

?>

<!-- Load Font Awesome 6 -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      crossorigin="anonymous"
      referrerpolicy="no-referrer" />

<style>
/* ---------------------------------------------------------
   GENERAL STYLES
--------------------------------------------------------- */
:root {
    --kf-gradient: linear-gradient(180deg, #fafbff 0%, #f4f6ff 100%);
}

.kreativ-hero {
    padding: 3rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #eef1ff 0%, #fafbff 100%);
    border-radius: 22px;
    margin-bottom: 2.5rem;
}

/* Hero subtitle */
.kreativ-hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 2.2rem;
    color: #444;
    max-width: 650px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.5;
}

/* ---------------------------------------------------------
   HERO TOOL SHORTCUTS
--------------------------------------------------------- */
.kreativ-hero-tools {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
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

    <p class="kreativ-hero-subtitle">
        Discover curated fonts and practical tools for pairing, identifying, and naming type.
    </p>

    <div class="kreativ-hero-tools">
        <span class="kreativ-hero-tools-label">Tools:</span>

        <a href="/tools/kreativ-font-pairing-tools" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-search"></i>
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
