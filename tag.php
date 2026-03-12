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
   SORTING LOGIC
--------------------------------------------- */
$sort = $_GET['sort'] ?? 'latest';

$orderby  = 'date';
$meta_key = '';

switch ($sort) {
    case 'popular':
        $orderby = 'comment_count';
        break;

    case 'ai':
        // Future-ready AI score
        $orderby  = 'meta_value_num';
        $meta_key = 'ai_score';
        break;

    case 'free':
        $orderby = 'date';
        break;
}

/* Pagination */
$paged = max(1, get_query_var('paged'));

/* --------------------------------------------
   TAX QUERY
--------------------------------------------- */
$tax_query = [
    [
        'taxonomy' => 'post_tag',
        'field'    => 'term_id',
        'terms'    => [$tag_id],
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
   FINAL QUERY
--------------------------------------------- */
$args = [
    'post_type'           => 'post',
    'posts_per_page'      => 24,
    'paged'               => $paged,
    'orderby'             => $orderby,
    'order'               => 'DESC',
    'meta_key'            => $meta_key ?: null,
    'ignore_sticky_posts' => true,
    'post_status'         => 'publish',
    'tax_query'           => $tax_query,
];

$query = new WP_Query($args);
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

        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>

                <?php
                $img   = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
                $thumb = $img[0] ?? get_template_directory_uri() . '/img/default-thumb.png';

                $is_new = (time() - get_the_time('U')) < 7 * DAY_IN_SECONDS;
                ?>

                <div class="col-md-4 col-lg-3 col-sm-6 kreativ-card-animate">
                    <div class="kreativ-font-card">

                        <a href="<?php the_permalink(); ?>">
                            <div class="kreativ-card-media">

                                <!-- Tag badge -->
                                <span class="kf-badge" style="background:#555;">
                                    #<?php echo esc_html($tag_name); ?>
                                </span>

                                <?php if ($is_new): ?>
                                    <span class="kf-badge kf-badge-new">NEW</span>
                                <?php endif; ?>

                                <img
                                    src="<?php echo esc_url($thumb); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    alt="<?php the_title_attribute(); ?>"
                                />
                            </div>

                            <h3><?php the_title(); ?></h3>
                        </a>

                    </div>
                </div>

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
