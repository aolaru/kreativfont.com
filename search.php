<?php get_header(); ?>

<?php
$search_query = get_search_query();
$paged_query  = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
$paged        = max( 1, (int) $paged_query );
$active_refinements = kreativ_get_active_search_refinements();
$search_data  = kreativ_get_structured_search_results( $search_query, $paged, 24, $active_refinements );
$result_query = $search_data['query'];
$result_count = (int) $search_data['total'];
$refinement_groups = kreativ_get_search_refinement_groups_with_state( $search_query, 5, $active_refinements );
$pagination_base = add_query_arg( 'paged', '%#%', home_url( '/' ) );
$pagination_args = kreativ_get_search_refinement_base_args( $search_query, $active_refinements );
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                Search results
            </div>

            <h1 class="kreativ-page-title">
                <?php if ( $result_query->have_posts() ) : ?>
                    Results for "<?php echo esc_html( $search_query ); ?>"
                <?php else : ?>
                    No results for "<?php echo esc_html( $search_query ); ?>"
                <?php endif; ?>
            </h1>

            <p class="kreativ-page-summary">
                <?php if ( $result_query->have_posts() ) : ?>
                    <?php echo esc_html( sprintf( '%d matching result%s from font titles, designers, foundries, styles, moods, and related pages.', $result_count, 1 === $result_count ? '' : 's' ) ); ?>
                <?php else : ?>
                    We could not find matching fonts or content. Try a different keyword or browse the main font library instead.
                <?php endif; ?>
            </p>

            <div class="kreativ-page-badges">
                <span class="kreativ-page-badge"><i class="fa-solid fa-font"></i> Fonts and resources</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-filter"></i> Refinable results</span>
                <span class="kreativ-page-badge"><i class="fa-solid fa-tags"></i> Title, designer, foundry, style, mood, and use case</span>
            </div>
        </div>

        <div class="kreativ-page-hero-side">
            <div class="kreativ-page-side-card">
                <h2>Search across fonts, styles, and makers.</h2>
                <p>Use the filters below to narrow results by designer, foundry, style, mood, or use case.</p>
            </div>
        </div>
    </section>

    <section class="kreativ-page-content">
        <?php if ( $result_query->have_posts() ) : ?>
            <?php if ( ! empty( $refinement_groups ) ) : ?>
                <section class="kreativ-search-refinements">
                    <div class="kreativ-search-refinements-head">
                        <h2>Refine this search</h2>
                        <p>Narrow these results by designer, foundry, style, mood, or use case without leaving search.</p>
                    </div>

                    <?php if ( ! empty( $active_refinements ) ) : ?>
                        <div class="kreativ-search-refinement-active">
                            <span class="kreativ-search-refinement-active-label">Active filters</span>
                            <div class="kreativ-search-refinement-active-pills">
                                <?php foreach ( kreativ_get_search_refinement_query_map() as $branch_key => $config ) : ?>
                                    <?php if ( empty( $active_refinements[ $branch_key ] ) ) { continue; } ?>
                                    <?php
                                    $active_label = ucfirst( str_replace( '-', ' ', $active_refinements[ $branch_key ] ) );
                                    foreach ( $refinement_groups[ $branch_key ]['terms'] ?? array() as $term_data ) {
                                        if ( ! empty( $term_data['is_active'] ) ) {
                                            $active_label = $term_data['name'];
                                            break;
                                        }
                                    }
                                    $clear_args = $pagination_args;
                                    unset( $clear_args[ $config['param'] ] );
                                    ?>
                                    <a href="<?php echo esc_url( add_query_arg( $clear_args, home_url( '/' ) ) ); ?>" class="kreativ-search-refinement-pill is-active">
                                        <?php echo esc_html( $config['label'] . ': ' . $active_label ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?php echo esc_url( add_query_arg( 's', $search_query, home_url( '/' ) ) ); ?>" class="kreativ-search-refinement-clear">Clear filters</a>
                        </div>
                    <?php endif; ?>

                    <div class="kreativ-search-refinement-groups">
                        <?php foreach ( $refinement_groups as $group ) : ?>
                            <div class="kreativ-search-refinement-group">
                                <h3><?php echo esc_html( $group['label'] ); ?></h3>
                                <div class="kreativ-search-refinement-pills">
                                    <?php foreach ( $group['terms'] as $term ) : ?>
                                        <a href="<?php echo esc_url( $term['url'] ); ?>" class="kreativ-search-refinement-pill<?php echo ! empty( $term['is_active'] ) ? ' is-active' : ''; ?>">
                                            <span class="kreativ-search-refinement-pill-label"><?php echo esc_html( $term['name'] ); ?></span>
                                            <span class="kreativ-search-refinement-pill-count">(<?php echo esc_html( (string) $term['count'] ); ?>)</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="row kreativ-results-grid">
                <?php while ( $result_query->have_posts() ) : $result_query->the_post(); ?>
                    <?php
                    kreativ_render_font_card(
                        array(
                            'post_id'         => get_the_ID(),
                            'badge_text'      => 'Result',
                            'badge_slug'      => 'tag',
                            'context_note'    => kreativ_get_search_match_label( get_the_ID(), $search_query ),
                            'column_classes'  => 'col-md-4 col-lg-3 col-sm-6',
                            'animation_class' => 'kreativ-card-animate',
                        )
                    );
                    ?>
                <?php endwhile; ?>
            </div>

            <div class="kreativ-pagination">
                <?php
                echo paginate_links(
                    array(
                        'base'      => $pagination_base,
                        'format'    => '',
                        'total'     => $result_query->max_num_pages,
                        'current'   => $paged,
                        'add_args'  => $pagination_args,
                        'mid_size'  => 2,
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                    )
                );
                ?>
            </div>
        <?php else : ?>
            <div class="kreativ-empty-state">
                <h2>No matching fonts yet.</h2>
                <p>Try another keyword, adjust your search, or jump back into the main font library.</p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">Browse Fonts</a>
                    <a href="<?php echo esc_url( add_query_arg( 'font_filter', 'latest', home_url( '/fonts' ) ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-secondary">Explore latest fonts</a>
                </p>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </section>
</div>

<?php get_footer(); ?>
