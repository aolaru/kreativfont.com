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
   SORTING LOGIC
--------------------------------------------- */
$sort = $_GET['sort'] ?? 'latest';

switch ($sort) {
    case 'popular':
        $orderby = 'comment_count';
        break;

    case 'free':
        $orderby = 'date';
        break;

    case 'ai':
        $orderby = 'meta_value_num';
        add_filter('posts_orderby', function($orderby) {
            return "comment_count DESC, post_date DESC";
        });
        break;

    default:
        $orderby = 'date';
}

/* Pagination */
$paged = max(1, get_query_var('paged'));

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

if ($sort === 'free') {
    $tax_query[] = [
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => ['free'],
    ];
}

/* --------------------------------------------
   FINAL QUERY (CORRECT PAGINATION)
--------------------------------------------- */
$args = [
    'post_type'          => 'post',
    'posts_per_page'     => 24,
    'paged'              => $paged,
    'orderby'            => $orderby,
    'order'              => 'DESC',
    'ignore_sticky_posts'=> true,
    'post_status'        => 'publish',
    'tax_query'          => $tax_query,
];

$query = new WP_Query($args);
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

		<?php
		// Suggest an update CTA (always available for this category)
		echo do_shortcode('[kcc_suggest_update]');
		?>
		
		
    </div>
</div>


<!-- =====================================================
     SORT BAR
===================================================== -->
<div class="kreativ-sort-bar">
    <a href="?sort=latest" class="kreativ-sort-btn <?php echo $sort==='latest'?'active':''; ?>">Latest</a>
    <a href="?sort=popular" class="kreativ-sort-btn <?php echo $sort==='popular'?'active':''; ?>">Popular</a>
    <a href="?sort=free" class="kreativ-sort-btn <?php echo $sort==='free'?'active':''; ?>">Free</a>
    <a href="?sort=ai" class="kreativ-sort-btn <?php echo $sort==='ai'?'active':''; ?>">AI Recommended</a>
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

            <h2 class="text-center my-5">No creatives found.</h2>

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
            'add_args'  => ['sort' => $sort],
        ]);
        ?>
    </div>

</div>

<?php get_footer(); ?>
