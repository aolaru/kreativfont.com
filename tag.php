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
$font_filters = kreativ_get_font_filters();
$active_font_filter = kreativ_get_active_font_filter( $font_filters );
$active_font_facets = kreativ_get_active_font_archive_facets();
$font_archive_facets = kreativ_get_font_archive_facets();
$active_font_filter_config = $font_filters[ $active_font_filter ];
$tax_query = array(
    array(
        'taxonomy' => 'post_tag',
        'field'    => 'term_id',
        'terms'    => array( $tag_id ),
    ),
);

if ( ! empty( $active_font_filter_config['tax_query'] ) ) {
    $tax_query = array_merge( $tax_query, $active_font_filter_config['tax_query'] );
}

$tax_query = array_merge( $tax_query, kreativ_get_font_eligibility_tax_query() );
$tax_query = array_merge( $tax_query, kreativ_get_font_archive_facet_tax_query( $active_font_facets ) );

$query = new WP_Query(
    kreativ_get_archive_query_args(
        array(
            'sort'           => in_array( $active_font_filter, array( 'latest', 'popular', 'free' ), true ) ? $active_font_filter : 'latest',
            'paged'          => $paged,
            'posts_per_page' => 24,
            'tax_query'      => $tax_query,
        )
    )
);
$archive_context = kreativ_get_archive_context_summary( $tag, 'tag', (int) $query->found_posts, $active_font_filter, $font_filters );
$related_groups  = kreativ_get_tag_archive_related_groups( $tag_name, 6 );
?>

<div class="container kreativ-category-bg">
    <div class="kreativ-category-header">
        <div class="kreativ-category-header-main">
            <div class="kreativ-category-eyebrow">
                <i class="fa-solid fa-tag"></i>
                <?php echo esc_html( $archive_context['eyebrow'] ); ?>
            </div>

            <h1><?php echo esc_html( $archive_context['title'] ); ?></h1>

            <p><?php echo wp_kses_post( $tag_desc ? $tag_desc : esc_html( $archive_context['summary'] ) ); ?></p>

            <div class="kreativ-category-meta-pills">
                <span class="kreativ-category-meta-pill"><i class="fa-solid fa-grid-2"></i> <?php echo esc_html( sprintf( '%d result%s', (int) $query->found_posts, 1 === (int) $query->found_posts ? '' : 's' ) ); ?></span>
                <span class="kreativ-category-meta-pill"><i class="fa-solid fa-filter"></i> <?php echo esc_html( $active_font_filter_config['label'] ); ?> filter</span>
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

<div class="kreativ-font-filter-bar kreativ-archive-filter-bar">
    <?php foreach ( $font_filters as $filter_slug => $filter_config ) : ?>
        <a href="<?php echo esc_url( add_query_arg( kreativ_get_font_archive_query_args( $filter_slug, $active_font_facets ), get_term_link( $tag ) ) ); ?>" class="kreativ-font-filter <?php echo $active_font_filter === $filter_slug ? 'active' : ''; ?>">
            <?php echo esc_html( $filter_config['label'] ); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ( ! empty( $font_archive_facets ) ) : ?>
    <form class="kreativ-font-facet-form" method="get" action="<?php echo esc_url( get_term_link( $tag ) ); ?>">
        <div class="kreativ-font-facet-grid">
            <?php foreach ( $font_archive_facets as $branch_key => $facet ) : ?>
                <label class="kreativ-font-facet-field">
                    <span><?php echo esc_html( $facet['label'] ); ?></span>
                    <select name="<?php echo esc_attr( $facet['param'] ); ?>">
                        <option value="">Any <?php echo esc_html( strtolower( $facet['label'] ) ); ?></option>
                        <?php foreach ( $facet['terms'] as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $active_font_facets[ $branch_key ] ?? '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
            <label class="kreativ-font-facet-field">
                <span>Availability</span>
                <select name="availability">
                    <option value="">Any availability</option>
                    <option value="commercial"<?php selected( $active_font_facets['availability'] ?? '', 'commercial' ); ?>>Commercial</option>
                    <option value="free"<?php selected( $active_font_facets['availability'] ?? '', 'free' ); ?>>Free</option>
                </select>
            </label>
        </div>
        <input type="hidden" name="font_filter" value="<?php echo esc_attr( $active_font_filter ); ?>">
        <div class="kreativ-font-facet-actions">
            <button type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i><span>Apply filters</span></button>
            <a href="<?php echo esc_url( add_query_arg( 'font_filter', $active_font_filter, get_term_link( $tag ) ) ); ?>">Clear filters</a>
        </div>
    </form>
<?php endif; ?>

<?php if ( ! empty( $related_groups ) ) : ?>
    <div class="container kreativ-archive-discovery">
        <div class="kreativ-archive-discovery-head">
            <h2>Related discovery</h2>
            <p>Use related matches to move from this tag into clearer browsing paths.</p>
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

<div class="container kreativ-category-grid kreativ-category-bg">
    <div class="row">

        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
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

            <div class="col-12">
                <div class="kreativ-archive-empty-state">
                <h2>No posts found for this tag.</h2>
                <p>Try another filter, browse the font library, or jump into the latest additions instead.</p>
                <div class="kreativ-archive-empty-actions">
                    <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-font-filter active">Browse Fonts</a>
                    <a href="<?php echo esc_url( get_term_link( $tag ) ); ?>" class="kreativ-font-filter">Try another filter</a>
                    <a href="<?php echo esc_url( add_query_arg( 'font_filter', 'latest', home_url( '/fonts' ) ) ); ?>" class="kreativ-font-filter">Explore latest fonts</a>
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
        'add_args'  => kreativ_get_font_archive_query_args( $active_font_filter, $active_font_facets ),
    ]);
    ?>

    <?php if ( $pagination ) : ?>
        <div class="kreativ-pagination">
            <?php echo $pagination; ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
