<?php
/*
 * Kreativ – Enhanced Category Template (FINAL)
 */
get_header();

/* --------------------------------------------
   CATEGORY CONTEXT
--------------------------------------------- */
$category = get_queried_object();
$cat_id   = $category->term_id;
$cat_slug = $category->slug;
$cat_name = $category->name;
$cat_desc = category_description();
$is_tools_archive = 'tools' === $cat_slug;
$is_font_archive  = kreativ_is_font_archive_term( $category );

/* Homepage-style icons */
$kreativ_fa_icons = [
    'fonts'      => 'fa-solid fa-font',
    'free-fonts' => 'fa-solid fa-gift',
    'free'       => 'fa-solid fa-gift',
    'tools'      => 'fa-solid fa-screwdriver-wrench',
];
$cat_icon = $kreativ_fa_icons[$cat_slug] ?? 'fa-solid fa-folder-open';

/* --------------------------------------------
   SORTING + QUERY
--------------------------------------------- */
$sort  = kreativ_get_archive_sort();
$paged = max(1, get_query_var('paged'));
$font_filters = kreativ_get_font_filters();
$active_font_filter = kreativ_get_active_font_filter( $font_filters );

if ( ! $is_font_archive ) {
    $active_font_filter = 'latest';
}

$active_font_filter_config = $font_filters[ $active_font_filter ];

/* --------------------------------------------
   TAX QUERY (CORRECT)
--------------------------------------------- */
$tax_query = [
    [
        'taxonomy' => 'category',
        'field'    => 'term_id',
        'terms'    => [$cat_id],
    ]
];

if ( $is_font_archive && ! empty( $active_font_filter_config['tax_query'] ) ) {
    $tax_query = array_merge( $tax_query, $active_font_filter_config['tax_query'] );
}

$query = new WP_Query(
    kreativ_get_archive_query_args(
        array(
            'sort'           => in_array( $active_font_filter, array( 'latest', 'popular', 'free' ), true ) ? $active_font_filter : 'latest',
            'paged'          => $paged,
            'posts_per_page' => 24,
            'orderby'        => $active_font_filter_config['orderby'],
            'tax_query'      => $tax_query,
        )
    )
);
$archive_context = kreativ_get_archive_context_summary( $category, 'category', (int) $query->found_posts, $active_font_filter, $font_filters );
$related_groups  = $is_font_archive ? kreativ_get_category_archive_related_groups( $category, 6 ) : array();
$archive_count_label = sprintf(
    '%d %s',
    (int) $query->found_posts,
    $is_tools_archive
        ? ( 1 === (int) $query->found_posts ? 'tool' : 'tools' )
        : (
            $is_font_archive
                ? ( 1 === (int) $query->found_posts ? 'result' : 'results' )
                : ( 1 === (int) $query->found_posts ? 'article' : 'articles' )
        )
);
?>

<div class="container kreativ-category-bg">
    <div class="kreativ-category-header">
        <div class="kreativ-category-header-main">
            <div class="kreativ-category-eyebrow">
                <i class="<?php echo esc_attr($cat_icon); ?>"></i>
                <?php echo esc_html( $archive_context['eyebrow'] ); ?>
            </div>

            <h1><?php echo esc_html( $archive_context['title'] ); ?></h1>

            <p><?php echo wp_kses_post( $cat_desc ? $cat_desc : esc_html( $archive_context['summary'] ) ); ?></p>

            <div class="kreativ-category-meta-pills">
                <span class="kreativ-category-meta-pill"><i class="fa-solid fa-grid-2"></i> <?php echo esc_html( $archive_count_label ); ?></span>
                <?php if ( $is_tools_archive ) : ?>
                    <span class="kreativ-category-meta-pill"><i class="fa-solid fa-wand-magic-sparkles"></i> Identify, pair, name</span>
                <?php elseif ( $is_font_archive ) : ?>
                    <span class="kreativ-category-meta-pill"><i class="fa-solid fa-filter"></i> <?php echo esc_html( $active_font_filter_config['label'] ); ?> filter</span>
                <?php else : ?>
                    <span class="kreativ-category-meta-pill"><i class="fa-solid fa-newspaper"></i> Latest articles</span>
                <?php endif; ?>
            </div>
        </div>

        <aside class="kreativ-category-header-side">
            <div class="kreativ-category-side-card">
                <h2><?php echo esc_html( $archive_context['side_title'] ); ?></h2>
                <p><?php echo esc_html( $archive_context['side_copy'] ); ?></p>
            </div>
        </aside>
    </div>
</div>

<?php if ( $is_font_archive ) : ?>
    <div class="kreativ-font-filter-bar kreativ-archive-filter-bar">
        <?php foreach ( $font_filters as $filter_slug => $filter_config ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'font_filter', $filter_slug, get_term_link( $category ) ) ); ?>" class="kreativ-font-filter <?php echo $active_font_filter === $filter_slug ? 'active' : ''; ?>">
                <?php echo esc_html( $filter_config['label'] ); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ( ! empty( $related_groups ) ) : ?>
    <div class="container kreativ-archive-discovery">
        <div class="kreativ-archive-discovery-head">
            <h2>Related discovery</h2>
            <p>Move laterally through the library without losing the current archive context.</p>
        </div>

        <div class="kreativ-archive-discovery-groups">
            <?php foreach ( $related_groups as $group ) : ?>
                <div class="kreativ-archive-discovery-group">
                    <h3><?php echo esc_html( $group['label'] ); ?></h3>
                    <div class="kreativ-archive-discovery-pills">
                        <?php foreach ( $group['terms'] as $term ) : ?>
                            <a href="<?php echo esc_url( $term['url'] ); ?>" class="kreativ-archive-discovery-pill"><?php echo esc_html( $term['name'] ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<div class="container kreativ-category-grid kreativ-category-bg<?php echo $is_tools_archive ? ' kreativ-tools-grid' : ''; ?>">
    <div class="row">

        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                kreativ_render_font_card(
                    array(
                        'post_id'        => get_the_ID(),
                        'badge_text'     => $cat_name,
                        'badge_slug'     => $cat_slug,
                        'column_classes' => $is_tools_archive ? 'col-md-6 col-lg-3 col-sm-6' : 'col-md-4 col-lg-3 col-sm-6',
                    )
                );
                ?>

            <?php endwhile; ?>

        <?php else : ?>

            <div class="col-12">
                <div class="kreativ-archive-empty-state">
                    <?php if ( $is_font_archive ) : ?>
                        <h2>No fonts found in this category.</h2>
                        <p>Try another filter, browse the full font library, or jump into the latest additions instead.</p>
                    <?php elseif ( $is_tools_archive ) : ?>
                        <h2>No tools found in this category.</h2>
                        <p>Browse the tools hub or return to the homepage.</p>
                    <?php else : ?>
                        <h2>No articles found in this category.</h2>
                        <p>Browse the blog or return to the homepage.</p>
                    <?php endif; ?>
                    <div class="kreativ-archive-empty-actions">
                        <?php if ( $is_font_archive ) : ?>
                            <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-font-filter active">Browse Fonts</a>
                            <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="kreativ-font-filter">Clear filter</a>
                            <a href="<?php echo esc_url( add_query_arg( 'font_filter', 'latest', home_url( '/fonts' ) ) ); ?>" class="kreativ-font-filter">Explore latest fonts</a>
                        <?php elseif ( $is_tools_archive ) : ?>
                            <a href="<?php echo esc_url( home_url( '/tools' ) ); ?>" class="kreativ-font-filter active">Browse Tools</a>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kreativ-font-filter">Back to Home</a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="kreativ-font-filter active">Browse Blog</a>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kreativ-font-filter">Back to Home</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; wp_reset_postdata(); ?>

    </div>

    <?php
    $pagination = paginate_links([
        'total'     => $query->max_num_pages,
        'current'   => $paged,
        'mid_size'  => 2,
        'prev_text' => '&laquo; Previous',
        'next_text' => 'Next &raquo;',
        'add_args'  => $is_font_archive ? ['font_filter' => $active_font_filter] : array(),
    ]);
    ?>

    <?php if ( $pagination ) : ?>
        <div class="kreativ-pagination">
            <?php echo $pagination; ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
