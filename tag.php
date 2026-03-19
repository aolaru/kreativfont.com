<?php
/*
 * Kreativ – Enhanced Tag Template
 */
get_header();

/* --------------------------------------------
   TAG CONTEXT
--------------------------------------------- */
$tag      = get_queried_object();
$tag_id   = $tag->term_id;
$tag_slug = $tag->slug;
$tag_name = single_tag_title('', false);
$tag_desc = tag_description();

/* --------------------------------------------
   SORTING
--------------------------------------------- */
$sort  = kreativ_get_archive_sort();
$paged = max(1, get_query_var('paged'));
$query = $GLOBALS['wp_query'];
?>

<!-- =====================================================
     TAG HEADER
===================================================== -->
<div class="container kreativ-category-bg">
    <div class="kreativ-category-header">
        <h1>
            <i class="fa-solid fa-tag"></i>
            Tag: <?php echo esc_html($tag_name); ?>
        </h1>

        <?php if ($tag_desc): ?>
            <p><?php echo wp_kses_post($tag_desc); ?></p>
        <?php endif; ?>
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

        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php
                kreativ_render_font_card(
                    array(
                        'post_id'        => get_the_ID(),
                        'badge_text'     => '#' . $tag_name,
                        'badge_slug'     => 'tag',
                        'column_classes' => 'col-md-4 col-lg-3 col-sm-6',
                    )
                );
                ?>

            <?php endwhile; ?>
        <?php else : ?>

            <div class="text-center my-5">
                <h2>No posts found for this tag.</h2>
                <p>Try changing the sorting or explore related categories.</p>
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
            'add_args'  => ['sort' => $sort],
        ]);
        ?>
    </div>

</div>

<?php get_footer(); ?>
