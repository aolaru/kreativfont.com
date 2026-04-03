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

/* Homepage-style icons */
$kreativ_fa_icons = [
    'fonts'            => 'fa-solid fa-font',
    'templates-themes' => 'fa-solid fa-layer-group',
    'graphics'         => 'fa-solid fa-pen-nib',
    'photos'           => 'fa-solid fa-camera',
    'videos'           => 'fa-solid fa-film',
    'sounds'           => 'fa-solid fa-music',
    'free'             => 'fa-solid fa-gift',
];
$cat_icon = $kreativ_fa_icons[$cat_slug] ?? 'fa-solid fa-folder-open';

/* --------------------------------------------
   SORTING + QUERY
--------------------------------------------- */
$sort  = kreativ_get_archive_sort();
$paged = max(1, get_query_var('paged'));
$font_filters = kreativ_get_font_filters();
$active_font_filter = kreativ_get_active_font_filter( $font_filters );
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

if ( ! empty( $active_font_filter_config['tax_query'] ) ) {
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
?>

<!-- =====================================================
     GRADIENT HEADER
===================================================== -->
<div class="container kreativ-category-bg">
    <div class="kreativ-category-header">
        <h1>
            <i class="<?php echo esc_attr($cat_icon); ?>"></i>
            Browse <?php echo esc_html($cat_name); ?>
        </h1>

		<?php if ($cat_desc): ?>
			<p><?php echo wp_kses_post($cat_desc); ?></p>
		<?php endif; ?>
    </div>
</div>


<div class="kreativ-font-filter-bar kreativ-archive-filter-bar">
    <?php foreach ( $font_filters as $filter_slug => $filter_config ) : ?>
        <a href="<?php echo esc_url( add_query_arg( 'font_filter', $filter_slug, get_term_link( $category ) ) ); ?>" class="kreativ-font-filter <?php echo $active_font_filter === $filter_slug ? 'active' : ''; ?>">
            <?php echo esc_html( $filter_config['label'] ); ?>
        </a>
    <?php endforeach; ?>
</div>



<!-- =====================================================
     POSTS GRID
===================================================== -->
<div class="container kreativ-category-grid kreativ-category-bg">
    <div class="row">

        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                kreativ_render_font_card(
                    array(
                        'post_id'        => get_the_ID(),
                        'badge_text'     => $cat_name,
                        'badge_slug'     => $cat_slug,
                        'column_classes' => 'col-md-4 col-lg-3 col-sm-6',
                    )
                );
                ?>

            <?php endwhile; ?>

        <?php else : ?>

            <div class="col-12">
                <div class="kreativ-archive-empty-state">
                    <h2>No fonts found in this category.</h2>
                    <p>Try another filter, browse the full font library, or jump into the latest additions instead.</p>
                    <div class="kreativ-archive-empty-actions">
                        <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-font-filter active">Browse Fonts</a>
                        <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="kreativ-font-filter">Try another filter</a>
                        <a href="<?php echo esc_url( home_url( '/category/fonts?font_filter=latest' ) ); ?>" class="kreativ-font-filter">Explore latest fonts</a>
                    </div>
                </div>
            </div>

        <?php endif; wp_reset_postdata(); ?>

    </div>


    <!-- Pagination -->
    <div class="kreativ-pagination">
        <?php
        echo paginate_links([
            'total'     => $query->max_num_pages,
            'current'   => $paged,
            'mid_size'  => 2,
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
            'add_args'  => ['font_filter' => $active_font_filter],
        ]);
        ?>
    </div>

</div>

<?php get_footer(); ?>
