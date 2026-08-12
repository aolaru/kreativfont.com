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

$kreativ_featured_font_query_args = array(
    'posts_per_page'         => 10,
    'post_status'            => 'publish',
    'ignore_sticky_posts'    => true,
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'meta_query'             => array(
        array(
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ),
    ),
);

$kreativ_commercial_tax_query = array(
    array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => array( 'fonts' ),
    ),
);

if ( $free_fonts_slugs ) {
    $kreativ_commercial_tax_query[] = array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => $free_fonts_slugs,
        'operator' => 'NOT IN',
    );
}

$kreativ_commercial_font_query = new WP_Query(
    array_merge(
        $kreativ_featured_font_query_args,
        array( 'tax_query' => $kreativ_commercial_tax_query )
    )
);

$kreativ_featured_font_candidates = $kreativ_commercial_font_query->posts;

if ( $free_fonts_slugs ) {
    $kreativ_free_font_query = new WP_Query(
        array_merge(
            $kreativ_featured_font_query_args,
            array(
                'tax_query' => array(
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
            )
        )
    );

    $kreativ_featured_font_candidates = array_merge( $kreativ_featured_font_candidates, $kreativ_free_font_query->posts );
}

$kreativ_featured_font = $kreativ_featured_font_candidates
    ? $kreativ_featured_font_candidates[ wp_rand( 0, count( $kreativ_featured_font_candidates ) - 1 ) ]
    : null;

?>

<!-- =====================================================
     HERO SECTION WITH TOOLS
===================================================== -->
<div class="kreativ-hero container">
    <div class="kreativ-hero-main">
        <div class="kreativ-hero-eyebrow">
            <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
            Curated fonts and practical tools
        </div>

        <h1 class="kreativ-hero-title">
            <span>Curated fonts.</span>
            <span>Practical tools.</span>
            <span>Faster decisions.</span>
        </h1>

        <p class="kreativ-hero-subtitle">
            Browse a growing library of curated type, then jump into the tools that help you identify, pair, and name fonts without wasting time.
        </p>

        <div class="kreativ-hero-actions">
            <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">
                <i class="fa-solid fa-compass" aria-hidden="true"></i>
                Browse Fonts
            </a>
            <a href="<?php echo esc_url( home_url( '/tools/kreativ-font-identifier' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-secondary">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                Identify a Font
            </a>
        </div>

        <div class="kreativ-hero-notes">
            <span class="kreativ-hero-note"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> 5000+ curated fonts</span>
        </div>
    </div>

    <div class="kreativ-hero-side">
        <?php if ( $kreativ_featured_font instanceof WP_Post ) : ?>
            <?php
            $kreativ_featured_font_id       = $kreativ_featured_font->ID;
            $kreativ_featured_font_title    = get_the_title( $kreativ_featured_font );
            $kreativ_featured_font_eyebrow = function_exists( 'kreativ_get_single_font_eyebrow' ) ? kreativ_get_single_font_eyebrow( $kreativ_featured_font ) : 'Font';
            ?>
            <a class="kreativ-hero-feature" href="<?php echo esc_url( get_permalink( $kreativ_featured_font ) ); ?>" aria-label="View <?php echo esc_attr( $kreativ_featured_font_title ); ?>">
                <?php
                echo wp_get_attachment_image(
                    get_post_thumbnail_id( $kreativ_featured_font_id ),
                    'large',
                    false,
                    array(
                        'class'         => 'kreativ-hero-feature-image',
                        'loading'       => 'eager',
                        'fetchpriority' => 'high',
                        'decoding'      => 'async',
                        'sizes'         => '(max-width: 767px) calc(100vw - 48px), (max-width: 1200px) 42vw, 480px',
                    )
                );
                ?>
                <span class="kreativ-hero-feature-kicker">New in the library</span>
                <span class="kreativ-hero-feature-meta">
                    <span>
                        <small><?php echo esc_html( $kreativ_featured_font_eyebrow ); ?></small>
                        <strong><?php echo esc_html( $kreativ_featured_font_title ); ?></strong>
                    </span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </span>
            </a>
        <?php else : ?>
            <div class="kreativ-hero-panel">
                <span class="kreativ-hero-panel-label">Font decisions</span>
                <h2 class="kreativ-hero-panel-title">Browse, identify, and choose fonts with less guesswork.</h2>
                <p class="kreativ-hero-panel-copy">Move from inspiration to practical choices, all in one place.</p>
            </div>
        <?php endif; ?>

    </div>

    <nav class="kreativ-hero-tools" aria-label="Quick font tools">
        <span class="kreativ-hero-tools-label">Quick tools</span>

        <a href="<?php echo esc_url( home_url( '/tools/kreativ-font-pairing-tools' ) ); ?>" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-object-group" aria-hidden="true"></i>
            <span>Font Pairing</span>
        </a>

        <a href="<?php echo esc_url( home_url( '/tools/kreativ-font-identifier' ) ); ?>" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-search" aria-hidden="true"></i>
            <span>Font Identifier</span>
        </a>

        <a href="<?php echo esc_url( home_url( '/tools/fancy-text-generator' ) ); ?>" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
            <span>Fancy Text</span>
        </a>

        <a href="<?php echo esc_url( home_url( '/tools/kreativ-font-name-generator' ) ); ?>" class="kreativ-hero-tool-card">
            <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
            <span>Font Names</span>
        </a>
    </nav>
</div>


<!-- =====================================================
     CURATED COLLECTIONS
===================================================== -->
<?php
$kreativ_collection_links = array();

foreach ( kreativ_get_font_collection_links() as $collection ) {
    if ( ! empty( $collection['featured'] ) ) {
        $kreativ_collection_links[] = $collection;
    }
}
?>

<section class="container kreativ-section kreativ-collections-section">
    <div class="kreativ-section-header">
        <div class="kreativ-section-heading">
            <span class="kreativ-section-eyebrow">Curated collections</span>
            <h2 class="kreativ-section-title">
                <i class="fa-solid fa-compass" aria-hidden="true"></i>
                Explore fonts by intent
            </h2>
            <p class="kreativ-section-summary">Start with a goal: commercial, free, modern, vintage, branding, packaging, and more.</p>
        </div>
        <a href="<?php echo esc_url( home_url( '/collections' ) ); ?>" class="kf-view-all">View all collections &rsaquo;</a>
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
        'badge_text' => 'Fonts',
        'badge_slug' => 'fonts',
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
        'badge_text' => 'Free',
        'badge_slug' => 'free',
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
                        <i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
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
            $section_post_limit = max( 1, absint( $section['posts_per_page'] ) );
            $query = new WP_Query( array(
                'posts_per_page'         => $section_post_limit * 2,
                'post_status'            => 'publish',
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby'                => 'latest' === $active_font_filter ? 'date' : $active_font_filter_config['orderby'],
                'order'                  => 'DESC',
                'tax_query'              => $section['tax_query'],
            ) );

            $rendered_post_ids    = array();
            $rendered_title_slugs = array();
            $badge_text           = $section['badge_text'] ?? ( $kreativ_category_labels[ $slug ] ?? '' );
            $badge_slug           = $section['badge_slug'] ?? $slug;

            if ( $query->have_posts() ) :
                while ( $query->have_posts() && count( $rendered_post_ids ) < $section_post_limit ) :
                    $query->the_post();
                    $post_id    = get_the_ID();
                    $title_slug = sanitize_title( get_the_title() );

                    if ( isset( $rendered_post_ids[ $post_id ] ) || ( $title_slug && isset( $rendered_title_slugs[ $title_slug ] ) ) ) {
                        continue;
                    }

                    $rendered_post_ids[ $post_id ] = true;

                    if ( $title_slug ) {
                        $rendered_title_slugs[ $title_slug ] = true;
                    }

                    kreativ_render_font_card(
                        array(
                            'post_id'    => $post_id,
                            'badge_text' => $badge_text,
                            'badge_slug' => $badge_slug,
                        )
                    );
                endwhile;
            endif;

            if ( empty( $rendered_post_ids ) ) :
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
