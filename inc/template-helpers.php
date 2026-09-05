<?php

function kreativ_add_category_body_class( $classes ) {
    if ( is_category() ) {
        $cat = get_queried_object();
        if ( isset( $cat->slug ) ) {
            $classes[] = 'kreativ-cat-' . sanitize_html_class( $cat->slug );
        }
    }

    return $classes;
}
add_filter( 'body_class', 'kreativ_add_category_body_class' );

function kf_is_new_post( $post_id ) {
    return ( time() - get_post_time( 'U', true, $post_id ) ) <= 7 * DAY_IN_SECONDS;
}

function kreativ_get_category_labels() {
    return array(
        'fonts'      => 'Fonts',
        'free-fonts' => 'Free Fonts',
        'free'       => 'Free Fonts',
    );
}

function kreativ_get_category_icons() {
    return array(
        'fonts'      => 'fa-solid fa-font',
        'free-fonts' => 'fa-solid fa-gift',
        'free'       => 'fa-solid fa-gift',
    );
}

function kreativ_get_primary_category_badge( $post_id, $labels = array() ) {
    $labels = empty( $labels ) ? kreativ_get_category_labels() : $labels;
    $terms  = get_the_terms( $post_id, 'category' );

    if ( ! $terms || is_wp_error( $terms ) ) {
        return array( null, null );
    }

    foreach ( $terms as $term ) {
        if ( isset( $labels[ $term->slug ] ) ) {
            return array( $term->slug, $labels[ $term->slug ] );
        }
    }

    return array( null, null );
}

function kreativ_get_archive_sort( $default = 'latest' ) {
    $sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : $default;

    return in_array( $sort, array( 'latest', 'popular', 'free', 'ai' ), true ) ? $sort : $default;
}

function kreativ_filter_has_terms( $taxonomy, $slugs ) {
    $term_ids = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'slug'       => (array) $slugs,
            'hide_empty' => true,
            'fields'     => 'ids',
        )
    );

    return ! is_wp_error( $term_ids ) && ! empty( $term_ids );
}

function kreativ_get_free_fonts_category_slugs() {
    $preferred_slugs = array( 'free-fonts', 'free' );
    $available_slugs = array();

    foreach ( $preferred_slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'category' );

        if ( $term && ! is_wp_error( $term ) ) {
            $available_slugs[] = $slug;
        }
    }

    return array_values( array_unique( $available_slugs ) );
}

function kreativ_get_font_content_category_slugs() {
    return array( 'fonts', 'free-fonts' );
}

function kreativ_get_non_font_content_category_slugs() {
    return array( 'tools', 'blog', 'templates-themes', 'graphics', 'photos', 'videos', 'sounds' );
}

function kreativ_get_font_eligibility_tax_clause() {
    $slugs = array();

    foreach ( kreativ_get_font_content_category_slugs() as $slug ) {
        $term = get_term_by( 'slug', $slug, 'category' );

        if ( $term && ! is_wp_error( $term ) ) {
            $slugs[] = $term->slug;
        }
    }

    if ( empty( $slugs ) ) {
        return array();
    }

    return array(
        'taxonomy'         => 'category',
        'field'            => 'slug',
        'terms'            => array_values( array_unique( $slugs ) ),
        'include_children' => true,
    );
}

function kreativ_get_font_exclusion_tax_clause() {
    $slugs = array();

    foreach ( kreativ_get_non_font_content_category_slugs() as $slug ) {
        $term = get_term_by( 'slug', $slug, 'category' );

        if ( $term && ! is_wp_error( $term ) ) {
            $slugs[] = $term->slug;
        }
    }

    if ( empty( $slugs ) ) {
        return array();
    }

    return array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => array_values( array_unique( $slugs ) ),
        'operator' => 'NOT IN',
    );
}

function kreativ_get_font_eligibility_tax_query() {
    $tax_query = array();
    $include   = kreativ_get_font_eligibility_tax_clause();
    $exclude   = kreativ_get_font_exclusion_tax_clause();

    if ( ! empty( $include ) ) {
        $tax_query[] = $include;
    }

    if ( ! empty( $exclude ) ) {
        $tax_query[] = $exclude;
    }

    return $tax_query;
}

function kreativ_is_font_post( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    return kreativ_post_has_category_slugs( $post, kreativ_get_font_content_category_slugs() )
        && ! kreativ_post_has_category_slugs( $post, kreativ_get_non_font_content_category_slugs() );
}

function kreativ_get_font_taxonomy_branch_definitions() {
    return array(
        'foundry' => array(
            'labels' => array( 'foundry' ),
            'slugs'  => array( 'foundry' ),
        ),
        'font_style' => array(
            'labels' => array( 'font style', 'style' ),
            'slugs'  => array( 'font-style', 'style' ),
        ),
        'designer' => array(
            'labels' => array( 'designer' ),
            'slugs'  => array( 'designer' ),
        ),
        'font_mood' => array(
            'labels' => array( 'font mood', 'mood' ),
            'slugs'  => array( 'font-mood', 'mood' ),
        ),
        'font_use_case' => array(
            'labels' => array( 'font use case', 'use case' ),
            'slugs'  => array( 'font-use-case', 'font-use-cases', 'use-case', 'use-cases' ),
        ),
    );
}

function kreativ_get_font_branch_parent_term_ids( $branch_key ) {
    static $cache = array();

    if ( isset( $cache[ $branch_key ] ) ) {
        return $cache[ $branch_key ];
    }

    $definitions = kreativ_get_font_taxonomy_branch_definitions();

    if ( empty( $definitions[ $branch_key ] ) ) {
        $cache[ $branch_key ] = array();
        return $cache[ $branch_key ];
    }

    $definition = $definitions[ $branch_key ];
    $parent_ids = array();

    foreach ( $definition['slugs'] as $slug ) {
        $term = get_term_by( 'slug', $slug, 'category' );

        if ( $term && ! is_wp_error( $term ) ) {
            $parent_ids[] = (int) $term->term_id;
        }
    }

    foreach ( $definition['labels'] as $label ) {
        $terms = get_terms(
            array(
                'taxonomy'   => 'category',
                'name'       => $label,
                'hide_empty' => false,
                'fields'     => 'ids',
            )
        );

        if ( ! is_wp_error( $terms ) ) {
            $parent_ids = array_merge( $parent_ids, array_map( 'intval', $terms ) );
        }
    }

    $cache[ $branch_key ] = array_values( array_unique( array_filter( $parent_ids ) ) );

    return $cache[ $branch_key ];
}

function kreativ_is_font_archive_term( $term ) {
    $term = get_term( $term, 'category' );

    if ( ! $term || is_wp_error( $term ) ) {
        return false;
    }

    if ( in_array( $term->slug, array( 'fonts', 'free', 'free-fonts' ), true ) ) {
        return true;
    }

    $term_ids = array_merge(
        array( (int) $term->term_id ),
        array_map( 'intval', get_ancestors( $term->term_id, 'category', 'taxonomy' ) )
    );

    foreach ( array_keys( kreativ_get_font_taxonomy_branch_definitions() ) as $branch_key ) {
        if ( array_intersect( $term_ids, kreativ_get_font_branch_parent_term_ids( $branch_key ) ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_get_post_category_branch_terms( $post = null, $branch_key = '' ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post || '' === $branch_key ) {
        return array();
    }

    $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

    if ( empty( $parent_ids ) ) {
        return array();
    }

    $terms = get_the_terms( $post->ID, 'category' );

    if ( ! $terms || is_wp_error( $terms ) ) {
        return array();
    }

    $matches = array();

    foreach ( $terms as $term ) {
        if ( in_array( (int) $term->term_id, $parent_ids, true ) ) {
            continue;
        }

        $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

        if ( array_intersect( array_map( 'intval', $ancestors ), $parent_ids ) ) {
            $matches[ $term->term_id ] = $term;
        }
    }

    return array_values( $matches );
}

function kreativ_get_primary_branch_term( $post = null, $branch_key = '' ) {
    $terms = kreativ_get_post_category_branch_terms( $post, $branch_key );

    if ( empty( $terms ) ) {
        return null;
    }

    return $terms[0];
}

function kreativ_get_valid_branch_term_slugs( $branch_key, $child_slugs ) {
    $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

    if ( empty( $parent_ids ) ) {
        return array();
    }

    $child_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', (array) $child_slugs ) ) ) );
    $valid_slugs = array();

    foreach ( $child_slugs as $child_slug ) {
        $term = get_term_by( 'slug', $child_slug, 'category' );

        if ( ! $term || is_wp_error( $term ) ) {
            continue;
        }

        $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

        if ( array_intersect( array_map( 'intval', $ancestors ), $parent_ids ) ) {
            $valid_slugs[] = $term->slug;
        }
    }

    return array_values( array_unique( $valid_slugs ) );
}

function kreativ_filter_has_branch_terms( $branch_key, $child_slugs ) {
    return ! empty( kreativ_get_valid_branch_term_slugs( $branch_key, $child_slugs ) );
}

function kreativ_get_font_filter_tax_clause( $branch_key, $child_slugs, $fallback_tag_slugs = array() ) {
    $child_slugs = kreativ_get_valid_branch_term_slugs( $branch_key, $child_slugs );

    if ( ! empty( $child_slugs ) ) {
        return array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $child_slugs,
        );
    }

    return array();
}

function kreativ_get_font_filters() {
    $serif_clause      = kreativ_get_font_filter_tax_clause( 'font_style', array( 'serif' ), array( 'serif' ) );
    $sans_serif_clause = kreativ_get_font_filter_tax_clause( 'font_style', array( 'sans-serif', 'sansserif' ), array( 'sans-serif', 'sansserif' ) );
    $script_clause     = kreativ_get_font_filter_tax_clause( 'font_style', array( 'script' ), array( 'script' ) );
    $display_clause    = kreativ_get_font_filter_tax_clause( 'font_style', array( 'display' ), array( 'display' ) );
    $modern_clause     = kreativ_get_font_filter_tax_clause( 'font_mood', array( 'modern' ), array( 'modern' ) );
    $vintage_clause    = kreativ_get_font_filter_tax_clause( 'font_mood', array( 'vintage' ), array( 'vintage' ) );
    $elegant_clause    = kreativ_get_font_filter_tax_clause( 'font_mood', array( 'elegant' ), array( 'elegant' ) );
    $free_fonts_slugs  = kreativ_get_free_fonts_category_slugs();

    $font_filters = array(
        'latest' => array(
            'label'     => 'Latest',
            'title'     => 'Latest Fonts',
            'orderby'   => 'date',
            'tax_query' => array(),
            'available' => true,
        ),
        'popular' => array(
            'label'     => 'Popular',
            'title'     => 'Popular Fonts',
            'orderby'   => 'comment_count',
            'tax_query' => array(),
            'available' => true,
        ),
        'free' => array(
            'label'     => 'Free',
            'title'     => 'Free Fonts',
            'orderby'   => 'date',
            'tax_query' => $free_fonts_slugs ? array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $free_fonts_slugs,
                ),
            ) : array(),
            'available' => ! empty( $free_fonts_slugs ),
        ),
        'serif' => array(
            'label'     => 'Serif',
            'title'     => 'Serif Fonts',
            'orderby'   => 'date',
            'tax_query' => $serif_clause ? array( $serif_clause ) : array(),
            'available' => ! empty( $serif_clause ),
        ),
        'sans-serif' => array(
            'label'     => 'Sans Serif',
            'title'     => 'Sans Serif Fonts',
            'orderby'   => 'date',
            'tax_query' => $sans_serif_clause ? array( $sans_serif_clause ) : array(),
            'available' => ! empty( $sans_serif_clause ),
        ),
        'script' => array(
            'label'     => 'Script',
            'title'     => 'Script Fonts',
            'orderby'   => 'date',
            'tax_query' => $script_clause ? array( $script_clause ) : array(),
            'available' => ! empty( $script_clause ),
        ),
        'display' => array(
            'label'     => 'Display',
            'title'     => 'Display Fonts',
            'orderby'   => 'date',
            'tax_query' => $display_clause ? array( $display_clause ) : array(),
            'available' => ! empty( $display_clause ),
        ),
        'modern' => array(
            'label'     => 'Modern',
            'title'     => 'Modern Fonts',
            'orderby'   => 'date',
            'tax_query' => $modern_clause ? array( $modern_clause ) : array(),
            'available' => ! empty( $modern_clause ),
        ),
        'vintage' => array(
            'label'     => 'Vintage',
            'title'     => 'Vintage Fonts',
            'orderby'   => 'date',
            'tax_query' => $vintage_clause ? array( $vintage_clause ) : array(),
            'available' => ! empty( $vintage_clause ),
        ),
        'elegant' => array(
            'label'     => 'Elegant',
            'title'     => 'Elegant Fonts',
            'orderby'   => 'date',
            'tax_query' => $elegant_clause ? array( $elegant_clause ) : array(),
            'available' => ! empty( $elegant_clause ),
        ),
    );

    return array_filter(
        $font_filters,
        static function ( $filter ) {
            return ! empty( $filter['available'] );
        }
    );
}

function kreativ_get_active_font_filter( $font_filters ) {
    $legacy_sort  = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : '';
    $active_filter = isset( $_GET['font_filter'] ) ? sanitize_key( wp_unslash( $_GET['font_filter'] ) ) : '';

    if ( '' === $active_filter && in_array( $legacy_sort, array( 'latest', 'popular', 'free' ), true ) ) {
        $active_filter = $legacy_sort;
    }

    if ( ! isset( $font_filters[ $active_filter ] ) ) {
        $active_filter = 'latest';
    }

    return $active_filter;
}

function kreativ_get_font_archive_facet_definitions() {
    return array(
        'font_style' => array(
            'label' => 'Style',
            'param' => 'style',
        ),
        'font_mood' => array(
            'label' => 'Mood',
            'param' => 'mood',
        ),
        'font_use_case' => array(
            'label' => 'Use case',
            'param' => 'use_case',
        ),
        'designer' => array(
            'label' => 'Designer',
            'param' => 'designer',
        ),
        'foundry' => array(
            'label' => 'Foundry',
            'param' => 'foundry',
        ),
    );
}

function kreativ_get_font_archive_facets( $limit = 80 ) {
    $facets = array();

    foreach ( kreativ_get_font_archive_facet_definitions() as $branch_key => $config ) {
        $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

        if ( empty( $parent_ids ) ) {
            continue;
        }

        $child_terms = array();

        foreach ( $parent_ids as $parent_id ) {
            $branch_terms = get_terms(
                array(
                    'taxonomy'   => 'category',
                    'parent'     => $parent_id,
                    'hide_empty' => true,
                    'number'     => max( 1, (int) $limit ),
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                )
            );

            if ( ! is_wp_error( $branch_terms ) ) {
                $child_terms = array_merge( $child_terms, $branch_terms );
            }
        }

        if ( empty( $child_terms ) ) {
            continue;
        }

        $unique_terms = array();

        foreach ( $child_terms as $term ) {
            $unique_terms[ $term->term_id ] = $term;
        }

        $facets[ $branch_key ] = array(
            'label' => $config['label'],
            'param' => $config['param'],
            'terms' => array_values( $unique_terms ),
        );
    }

    return $facets;
}

function kreativ_get_active_font_archive_facets() {
    $active = array();

    foreach ( kreativ_get_font_archive_facet_definitions() as $branch_key => $config ) {
        if ( empty( $_GET[ $config['param'] ] ) ) {
            continue;
        }

        $slug = sanitize_title( wp_unslash( $_GET[ $config['param'] ] ) );

        if ( '' !== $slug && in_array( $slug, kreativ_get_valid_branch_term_slugs( $branch_key, array( $slug ) ), true ) ) {
            $active[ $branch_key ] = $slug;
        }
    }

    $availability = isset( $_GET['availability'] ) ? sanitize_key( wp_unslash( $_GET['availability'] ) ) : '';

    if ( in_array( $availability, array( 'free', 'commercial' ), true ) ) {
        $active['availability'] = $availability;
    }

    return $active;
}

function kreativ_get_font_archive_facet_tax_query( $active_facets = array() ) {
    $tax_query = array();

    foreach ( kreativ_get_font_archive_facet_definitions() as $branch_key => $config ) {
        if ( empty( $active_facets[ $branch_key ] ) ) {
            continue;
        }

        $clause = kreativ_get_font_filter_tax_clause( $branch_key, array( $active_facets[ $branch_key ] ) );

        if ( ! empty( $clause ) ) {
            $tax_query[] = $clause;
        }
    }

    if ( empty( $active_facets['availability'] ) ) {
        return $tax_query;
    }

    $free_slugs = kreativ_get_free_fonts_category_slugs();

    if ( empty( $free_slugs ) ) {
        return $tax_query;
    }

    $tax_query[] = array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => $free_slugs,
        'operator' => 'free' === $active_facets['availability'] ? 'IN' : 'NOT IN',
    );

    return $tax_query;
}

function kreativ_get_font_archive_query_args( $font_filter = 'latest', $active_facets = array() ) {
    $args = array( 'font_filter' => $font_filter );

    foreach ( kreativ_get_font_archive_facet_definitions() as $branch_key => $config ) {
        if ( ! empty( $active_facets[ $branch_key ] ) ) {
            $args[ $config['param'] ] = $active_facets[ $branch_key ];
        }
    }

    if ( ! empty( $active_facets['availability'] ) ) {
        $args['availability'] = $active_facets['availability'];
    }

    return $args;
}

function kreativ_get_branch_key_for_category_term( $term ) {
    $term = get_term( $term, 'category' );

    if ( ! $term || is_wp_error( $term ) ) {
        return '';
    }

    foreach ( array_keys( kreativ_get_font_taxonomy_branch_definitions() ) as $branch_key ) {
        $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

        if ( empty( $parent_ids ) ) {
            continue;
        }

        if ( in_array( (int) $term->term_id, $parent_ids, true ) ) {
            return $branch_key;
        }

        $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

        if ( array_intersect( array_map( 'intval', $ancestors ), $parent_ids ) ) {
            return $branch_key;
        }
    }

    return '';
}

function kreativ_get_archive_branch_labels() {
    return array(
        'designer'      => 'Designers',
        'foundry'       => 'Foundries',
        'font_style'    => 'Styles',
        'font_mood'     => 'Moods',
        'font_use_case' => 'Use Cases',
    );
}

function kreativ_get_archive_context_summary( $term, $archive_type = 'category', $result_count = 0, $active_filter = 'latest', $font_filters = array() ) {
    $term = get_term( $term );

    if ( ! $term || is_wp_error( $term ) ) {
        return array(
            'eyebrow'    => 'Archive',
            'title'      => 'Explore the font library',
            'summary'    => 'Browse curated type, refine the list, and move quickly into better font decisions.',
            'side_title' => 'Discovery stays fast when the archive stays focused.',
            'side_copy'  => 'Use filters and related links to move through styles, moods, and use cases without falling back to generic browsing.',
        );
    }

    $branch_key    = 'category' === $archive_type ? kreativ_get_branch_key_for_category_term( $term ) : '';
    $branch_labels = kreativ_get_archive_branch_labels();
    $filter_label  = ! empty( $font_filters[ $active_filter ]['label'] ) ? $font_filters[ $active_filter ]['label'] : 'Latest';
    $count_phrase  = sprintf( '%d result%s', (int) $result_count, 1 === (int) $result_count ? '' : 's' );

    if ( 'category' === $archive_type && 'tools' === $term->slug ) {
        $tool_count_phrase = sprintf( '%d tool%s', (int) $result_count, 1 === (int) $result_count ? '' : 's' );

        return array(
            'eyebrow'    => 'Font tools',
            'title'      => 'Browse Font Tools',
            'summary'    => $tool_count_phrase . ' for faster font decisions. Identify type, generate names, test pairings, and create styled text in one place.',
            'side_title' => 'Pick the tool that matches the job.',
            'side_copy'  => 'Start with identification, pairing, naming, or text generation, then move back into the font library when you are ready to choose.',
        );
    }

    if ( 'category' === $archive_type && ! kreativ_is_font_archive_term( $term ) ) {
        return array(
            'eyebrow'    => 'Category archive',
            'title'      => 'Browse ' . $term->name,
            'summary'    => $count_phrase . ' published under this category.',
            'side_title' => 'Read the latest from ' . $term->name . '.',
            'side_copy'  => 'Open an article for the full details or return to the main site navigation to explore another section.',
        );
    }

    if ( 'tag' === $archive_type ) {
        return array(
            'eyebrow'    => 'Tag archive',
            'title'      => 'Explore fonts tagged ' . $term->name,
            'summary'    => $count_phrase . ' connected to this tag. Use the filter bar and related links to find matching styles, moods, and use cases.',
            'side_title' => 'Tags are a starting point.',
            'side_copy'  => 'From here you can narrow by style, mood, use case, designer, or foundry.',
        );
    }

    if ( isset( $branch_labels[ $branch_key ] ) ) {
        $singular = rtrim( $branch_labels[ $branch_key ], 's' );

        return array(
            'eyebrow'    => $branch_labels[ $branch_key ] . ' archive',
            'title'      => 'Explore ' . $term->name . ' across the font library',
            'summary'    => $count_phrase . ' currently surfaced here. Filter by ' . strtolower( $filter_label ) . ' order and move through related discovery paths without leaving the archive.',
            'side_title' => 'Browse more ' . strtolower( $singular ) . ' options like ' . $term->name . '.',
            'side_copy'  => 'Use related terms and filters to compare more fonts without browsing one post at a time.',
        );
    }

    return array(
        'eyebrow'    => 'Category archive',
        'title'      => 'Browse ' . $term->name,
        'summary'    => $count_phrase . ' available under this category. Use filters and related discovery links to tighten the view and keep browsing momentum.',
        'side_title' => 'Use this archive to keep browsing with more context.',
        'side_copy'  => 'Filter the list, follow related terms, or jump back into the main font library.',
    );
}

function kreativ_get_category_archive_related_groups( $term, $limit = 6 ) {
    $term = get_term( $term, 'category' );

    if ( ! $term || is_wp_error( $term ) ) {
        return array();
    }

    $groups      = array();
    $branch_key  = kreativ_get_branch_key_for_category_term( $term );
    $branch_map  = kreativ_get_archive_branch_labels();

    if ( $branch_key ) {
        $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );
        $parent_id  = 0;

        foreach ( array_map( 'intval', get_ancestors( $term->term_id, 'category', 'taxonomy' ) ) as $ancestor_id ) {
            if ( in_array( $ancestor_id, $parent_ids, true ) ) {
                $parent_id = $ancestor_id;
                break;
            }
        }

        if ( ! $parent_id && in_array( (int) $term->term_id, $parent_ids, true ) ) {
            $parent_id = (int) $term->term_id;
        }

        if ( $parent_id ) {
            $siblings = get_terms(
                array(
                    'taxonomy'   => 'category',
                    'parent'     => $parent_id,
                    'hide_empty' => true,
                    'number'     => $limit + 1,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                )
            );

            if ( ! is_wp_error( $siblings ) ) {
                $terms = array();

                foreach ( $siblings as $sibling ) {
                    if ( (int) $sibling->term_id === (int) $term->term_id ) {
                        continue;
                    }

                    $terms[] = array(
                        'name' => $sibling->name,
                        'url'  => get_term_link( $sibling ),
                    );

                    if ( count( $terms ) >= $limit ) {
                        break;
                    }
                }

                if ( ! empty( $terms ) ) {
                    $groups[] = array(
                        'label' => 'Related ' . strtolower( $branch_map[ $branch_key ] ?? 'terms' ),
                        'terms' => $terms,
                    );
                }
            }
        }
    }

    foreach ( kreativ_get_font_filters() as $filter_slug => $filter_config ) {
        if ( in_array( $filter_slug, array( 'latest', 'popular', 'free' ), true ) ) {
            continue;
        }

        if ( ! empty( $filter_config['tax_query'] ) ) {
            $term_names = array();
            foreach ( $filter_config['tax_query'] as $tax_clause ) {
                if ( empty( $tax_clause['taxonomy'] ) || 'category' !== $tax_clause['taxonomy'] || empty( $tax_clause['terms'] ) ) {
                    continue;
                }
                foreach ( (array) $tax_clause['terms'] as $clause_term_slug ) {
                    $term_names[] = $clause_term_slug;
                }
            }
        }
    }

    return $groups;
}

function kreativ_get_tag_archive_related_groups( $term_name, $limit = 6 ) {
    $groups       = array();
    $raw_matches  = kreativ_get_search_branch_term_matches( $term_name, $limit );
    $branch_labels = kreativ_get_archive_branch_labels();

    foreach ( $branch_labels as $branch_key => $label ) {
        if ( empty( $raw_matches[ $branch_key ] ) ) {
            continue;
        }

        $groups[] = array(
            'label' => $label,
            'terms' => array_map(
                static function ( $term ) {
                    return array(
                        'name' => $term->name,
                        'url'  => get_term_link( $term ),
                    );
                },
                $raw_matches[ $branch_key ]
            ),
        );
    }

    return $groups;
}

function kreativ_get_archive_query_args( $args = array() ) {
    $defaults = array(
        'sort'               => kreativ_get_archive_sort(),
        'tax_query'          => array(),
        'posts_per_page'     => 24,
        'paged'              => max( 1, get_query_var( 'paged' ) ),
        'post_type'          => 'post',
        'post_status'        => 'publish',
        'order'              => 'DESC',
        'ignore_sticky_posts'=> true,
        'meta_key'           => '',
        'tag_id'             => 0,
    );

    $args = wp_parse_args( $args, $defaults );

    switch ( $args['sort'] ) {
        case 'popular':
            $orderby = 'comment_count';
            break;

        case 'ai':
            $orderby         = 'meta_value_num';
            $args['meta_key'] = $args['meta_key'] ? $args['meta_key'] : 'ai_score';
            break;

        default:
            $orderby = 'date';
            break;
    }

    if ( 'free' === $args['sort'] ) {
        $free_fonts_slugs = kreativ_get_free_fonts_category_slugs();

        if ( $free_fonts_slugs ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $free_fonts_slugs,
            );
        }
    }

    return array(
        'post_type'           => $args['post_type'],
        'posts_per_page'      => $args['posts_per_page'],
        'paged'               => $args['paged'],
        'orderby'             => $orderby,
        'order'               => $args['order'],
        'meta_key'            => $args['meta_key'] ? $args['meta_key'] : null,
        'tag_id'              => $args['tag_id'] ? (int) $args['tag_id'] : null,
        'ignore_sticky_posts' => $args['ignore_sticky_posts'],
        'post_status'         => $args['post_status'],
        'tax_query'           => $args['tax_query'],
    );
}

function kreativ_get_page_summary( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return '';
    }

    if ( has_excerpt( $post ) ) {
        return get_the_excerpt( $post );
    }

    return wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '...' );
}

function kreativ_is_noise_summary_paragraph( $text ) {
    $normalized = strtolower( trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) ) );

    if ( '' === $normalized ) {
        return true;
    }

    $blocked_phrases = array(
        'view & purchase',
        'important notice',
        'premium commercial font',
        'not available for free download',
        'discover free fonts',
        'free tier',
        'official marketplace',
        'join the kreativ font free tier',
    );

    foreach ( $blocked_phrases as $phrase ) {
        if ( false !== strpos( $normalized, $phrase ) ) {
            return true;
        }
    }

    return str_word_count( $normalized ) < 6;
}

function kreativ_get_font_description_score( $text ) {
    $normalized = strtolower( trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) ) );

    if ( '' === $normalized ) {
        return 0;
    }

    $score = 0;

    $positive_patterns = array(
        '/\bis a\b.{0,80}\b(font|typeface|font family)\b/',
        '/\b(font|typeface|font family)\b.{0,80}\bdesigned by\b/',
        '/\b(font|typeface|font family)\b.{0,80}\bpublished by\b/',
        '/\b(display|serif|sans|sans-serif|script|handwritten|brush|calligraphy|modern|vintage|elegant)\b.{0,80}\b(font|typeface|family)\b/',
        '/\bdesigned by\b/',
        '/\bpublished by\b/',
    );

    foreach ( $positive_patterns as $pattern ) {
        if ( preg_match( $pattern, $normalized ) ) {
            $score += 2;
        }
    }

    if ( str_word_count( $normalized ) >= 10 ) {
        $score += 1;
    }

    if ( str_word_count( $normalized ) >= 18 ) {
        $score += 1;
    }

    return $score;
}

function kreativ_get_content_summary( $post = null, $max_words = 24, $apply_content_filters = true ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return '';
    }

    if ( has_excerpt( $post ) ) {
        return wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), $max_words, '...' );
    }

    // Tool shortcodes can be expensive to render. A page summary only needs the
    // authored copy, not interactive tool output, so callers can opt out of
    // running the full content-filter stack.
    $content = $apply_content_filters
        ? apply_filters( 'the_content', $post->post_content )
        : strip_shortcodes( $post->post_content );

    if ( preg_match_all( '/<p\b[^>]*>(.*?)<\/p>/is', $content, $paragraph_matches ) ) {
        $fallback_summary = '';

        foreach ( $paragraph_matches[1] as $paragraph_html ) {
            $paragraph_text = trim( wp_strip_all_tags( $paragraph_html ) );

            if ( kreativ_is_noise_summary_paragraph( $paragraph_text ) ) {
                continue;
            }

            if ( kreativ_get_font_description_score( $paragraph_text ) >= 3 ) {
                return wp_trim_words( $paragraph_text, $max_words, '...' );
            }

            if ( '' === $fallback_summary ) {
                $fallback_summary = $paragraph_text;
            }
        }

        if ( '' !== $fallback_summary ) {
            return wp_trim_words( $fallback_summary, $max_words, '...' );
        }
    }

    $fallback = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
    $fallback = preg_replace( '/\s+/', ' ', (string) $fallback );

    if ( kreativ_is_noise_summary_paragraph( $fallback ) ) {
        return '';
    }

    return wp_trim_words( trim( $fallback ), $max_words, '...' );
}

function kreativ_get_font_credit_data( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array(
            'designer' => '',
            'foundry'  => '',
        );
    }

    $format_names = static function ( $terms ) {
        $names = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ( $term ) {
                            return isset( $term->name ) ? trim( $term->name ) : '';
                        },
                        (array) $terms
                    )
                )
            )
        );

        if ( count( $names ) < 2 ) {
            return $names ? $names[0] : '';
        }

        $last_name = array_pop( $names );

        return implode( ', ', $names ) . ' and ' . $last_name;
    };

    $designer = $format_names( kreativ_get_post_category_branch_terms( $post, 'designer' ) );
    $foundry  = $format_names( kreativ_get_post_category_branch_terms( $post, 'foundry' ) );

    // Credits are extracted from authored copy. Rendering shortcodes here can
    // invoke expensive plugin output before the post body is rendered.
    $content = strip_shortcodes( $post->post_content );
    $text    = wp_strip_all_tags( $content );
    $text    = preg_replace( '/\s+/', ' ', (string) $text );

    if ( '' === $designer && preg_match( '/\bdesigned by\s+([^.,;]+?)(?:\s+and\s+(?:published|released)\s+by\b|[.,;]|$)/i', $text, $matches ) ) {
        $designer = trim( $matches[1] );
    }

    if ( '' === $foundry && preg_match( '/\b(?:published|released)\s+by\s+([^.,;]+?)(?:\s+[,;]|\s+and\b|[.,;]|$)/i', $text, $matches ) ) {
        $foundry = trim( $matches[1] );
    }

    $designer = preg_replace( '/\s+$/', '', (string) $designer );
    $foundry  = preg_replace( '/\s+$/', '', (string) $foundry );

    return array(
        'designer' => $designer,
        'foundry'  => $foundry,
    );
}

function kreativ_get_single_font_eyebrow( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return 'Font';
    }

    $style_map = array(
        'serif'       => 'Serif Font',
        'sans-serif'  => 'Sans Serif Font',
        'sans serif'  => 'Sans Serif Font',
        'script'      => 'Script Font',
        'display'     => 'Display Font',
        'slab-serif'  => 'Slab Serif Font',
        'slab serif'  => 'Slab Serif Font',
        'monospace'   => 'Monospace Font',
        'blackletter' => 'Blackletter Font',
        'symbol-dingbats' => 'Symbol & Dingbats Font',
        'symbol & dingbats' => 'Symbol & Dingbats Font',
        'variable'    => 'Variable Font',
        'handwritten' => 'Handwritten Font',
        'brush'       => 'Brush Font',
        'calligraphy' => 'Calligraphy Font',
        'modern'      => 'Modern Font',
        'vintage'     => 'Vintage Font',
        'elegant'     => 'Elegant Font',
    );

    $style_term = kreativ_get_primary_branch_term( $post, 'font_style' );

    if ( $style_term ) {
        $style_key = strtolower( sanitize_title( $style_term->slug ) );
        $style_name = strtolower( trim( $style_term->name ) );

        if ( isset( $style_map[ $style_key ] ) ) {
            return $style_map[ $style_key ];
        }

        if ( isset( $style_map[ $style_name ] ) ) {
            return $style_map[ $style_name ];
        }

        return $style_term->name . ' Font';
    }

    $summary = strtolower( kreativ_get_content_summary( $post, 28, false ) );

    foreach ( $style_map as $slug => $label ) {
        if ( false !== strpos( $summary, $slug ) ) {
            return $label;
        }
    }

    $categories = get_the_terms( $post->ID, 'category' );

    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            if ( 'free' === $category->slug ) {
                return 'Free Font';
            }
        }
    }

    if ( false !== strpos( strtolower( wp_strip_all_tags( $post->post_content ) ), 'premium commercial font' ) ) {
        return 'Premium Font';
    }

    return 'Font';
}

function kreativ_is_tool_page( $post = null ) {
    $post = get_post( $post );

    if ( $post instanceof WP_Post ) {
        $template_slug = get_page_template_slug( $post );

        if ( 'template-tools-page.php' === $template_slug ) {
            return true;
        }
    }

    $request_uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path  = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
    $path_segments = $request_path ? explode( '/', $request_path ) : array();

    return isset( $path_segments[0] ) && 'tools' === $path_segments[0];
}

function kreativ_get_single_content_kind( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return 'article';
    }

    if ( kreativ_is_tool_page( $post ) || kreativ_post_has_category_slugs( $post, array( 'tools' ) ) ) {
        return 'tool';
    }

    if ( in_array( $post->post_name, array( 'about', 'contact', 'privacy-policy', 'terms-of-use', 'terms-of-use-privacy-policy', 'updates' ), true ) ) {
        return 'information';
    }

    if ( kreativ_post_has_category_slugs( $post, array( 'blog' ) ) ) {
        return 'article';
    }

    if ( kreativ_is_font_post( $post ) ) {
        return 'font';
    }

    return 'article';
}

function kreativ_render_partial( $template, $args = array() ) {
    $template_path = locate_template( $template );

    if ( ! $template_path ) {
        return;
    }

    if ( ! empty( $args ) ) {
        extract( $args, EXTR_SKIP );
    }

    include $template_path;
}

function kreativ_get_font_card_args( $args = array() ) {
    $defaults = array(
        'post_id'          => get_the_ID(),
        'badge_text'       => '',
        'badge_slug'       => '',
        'column_classes'   => 'col-md-3 col-sm-6',
        'animation_class'  => 'kreativ-card-animate',
        'thumb_size'       => 'medium',
        'title_tag'        => 'h3',
        'new_label'        => 'NEW',
        'show_new_badge'   => null,
        'empty_thumb_url'  => get_template_directory_uri() . '/img/logo-512.png',
        'loading_thumb_url'=> get_template_directory_uri() . '/img/loading.gif',
    );

    $args = wp_parse_args( $args, $defaults );
    $post = get_post( $args['post_id'] );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $args['thumb_size'] );

    $args['post_id']        = $post->ID;
    $args['permalink']      = get_permalink( $post );
    $args['title']          = get_the_title( $post );
    $args['title_attr']     = the_title_attribute( array( 'post' => $post, 'echo' => false ) );
    $args['thumb_url']      = $thumb[0] ?? $args['empty_thumb_url'];
    $args['thumb_width']    = isset( $thumb[1] ) ? (int) $thumb[1] : 240;
    $args['thumb_height']   = isset( $thumb[2] ) ? (int) $thumb[2] : 140;
    $args['show_new_badge'] = null === $args['show_new_badge'] ? kf_is_new_post( $post->ID ) : (bool) $args['show_new_badge'];

    return $args;
}

function kreativ_render_font_card( $args = array() ) {
    $font_card_args = kreativ_get_font_card_args( $args );

    if ( empty( $font_card_args ) ) {
        return;
    }

    kreativ_render_partial( 'partials/font-card.php', $font_card_args );
}

function kreativ_fill_empty_attachment_alt_text( $attributes, $attachment, $size ) {
    if ( ! empty( $attributes['alt'] ) ) {
        return $attributes;
    }

    $attachment_alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
    $fallback_alt   = $attachment_alt ? $attachment_alt : get_the_title( $attachment );

    if ( ! $fallback_alt && ! empty( $attachment->post_parent ) ) {
        $fallback_alt = get_the_title( $attachment->post_parent );
    }

    if ( $fallback_alt ) {
        $attributes['alt'] = wp_strip_all_tags( $fallback_alt );
    }

    return $attributes;
}
add_filter( 'wp_get_attachment_image_attributes', 'kreativ_fill_empty_attachment_alt_text', 10, 3 );

function kreativ_demote_content_h1_headings( $content ) {
    if ( is_admin() || ! is_singular() || '' === trim( (string) $content ) ) {
        return $content;
    }

    $content = preg_replace( '/<h1(\s[^>]*)?>/i', '<h2$1>', $content );
    $content = preg_replace( '/<\/h1>/i', '</h2>', $content );

    return $content;
}
add_filter( 'the_content', 'kreativ_demote_content_h1_headings', 8 );

function kreativ_improve_tool_content_accessibility( $content ) {
    if ( is_admin() || ! is_singular() || ! is_main_query() || 'kreativ-font-identifier' !== get_post_field( 'post_name', get_queried_object_id() ) ) {
        return $content;
    }

    if ( false === strpos( $content, 'name="font_image"' ) || false !== strpos( $content, 'for="kreativ-font-identifier-upload"' ) ) {
        return $content;
    }

    $pattern = '/<input([^>]*type=["\']file["\'][^>]*name=["\']font_image["\'][^>]*)>/i';

    return preg_replace_callback(
        $pattern,
        static function ( $matches ) {
            $attributes = rtrim( trim( $matches[1] ), " \t\n\r\0\x0B/" );

            return '<label class="kfi-upload-label" for="kreativ-font-identifier-upload">Upload a font image</label><input id="kreativ-font-identifier-upload" ' . $attributes . ' aria-label="Upload a font image">';
        },
        $content,
        1
    );
}
add_filter( 'the_content', 'kreativ_improve_tool_content_accessibility', 12 );

function kreativ_redirect_legacy_legal_urls() {
    if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $request_path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
    $request_path = untrailingslashit( '/' . ltrim( (string) $request_path, '/' ) );
    $legacy_paths = array(
        '/privacy-policy',
        '/terms-of-use',
        '/blog/privacy-policy',
        '/blog/terms-of-use',
    );

    if ( in_array( $request_path, $legacy_paths, true ) ) {
        wp_safe_redirect( home_url( '/blog/terms-of-use-privacy-policy' ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'kreativ_redirect_legacy_legal_urls' );

function kreativ_post_has_category_slugs( $post = null, $slugs = array() ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    $slugs = array_filter( array_map( 'sanitize_title', (array) $slugs ) );

    if ( empty( $slugs ) ) {
        return false;
    }

    $terms = get_the_terms( $post->ID, 'category' );

    if ( ! $terms || is_wp_error( $terms ) ) {
        return false;
    }

    foreach ( $terms as $term ) {
        if ( in_array( $term->slug, $slugs, true ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_post_has_branch_term_slugs( $post = null, $branch_key = '', $slugs = array() ) {
    $terms = kreativ_get_post_category_branch_terms( $post, $branch_key );
    $slugs = array_filter( array_map( 'sanitize_title', (array) $slugs ) );

    if ( empty( $terms ) || empty( $slugs ) ) {
        return false;
    }

    foreach ( $terms as $term ) {
        if ( in_array( $term->slug, $slugs, true ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_get_single_font_quick_download_data( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post || ! kreativ_post_has_category_slugs( $post, kreativ_get_free_fonts_category_slugs() ) ) {
        return array();
    }

    $content = (string) $post->post_content;
    $download_url = '';
    $source_url   = '';

    if ( preg_match( '/\\[kreativ_font_download[^\\]]*\\surl=(["\\\'])(.*?)\\1/i', $content, $match ) ) {
        $download_url = esc_url_raw( html_entity_decode( $match[2], ENT_QUOTES, get_bloginfo( 'charset' ) ) );
    }

    if ( preg_match_all( '/<a\\s[^>]*href=(["\\\'])(.*?)\\1[^>]*>(.*?)<\\/a>/is', $content, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $href = esc_url_raw( html_entity_decode( wp_strip_all_tags( $match[2] ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
            $text = strtolower( wp_strip_all_tags( html_entity_decode( $match[3], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );

            if ( '' === $href ) {
                continue;
            }

            if ( ! $download_url && ( false !== strpos( strtolower( $href ), '.zip' ) || false !== strpos( $text, 'download' ) || false !== strpos( $text, 'zip' ) ) ) {
                $download_url = $href;
            }

            if ( ! $source_url && false !== strpos( strtolower( $href ), 'fonts.google.com' ) ) {
                $source_url = $href;
            }
        }
    }

    if ( ! $download_url && ! $source_url ) {
        return array();
    }

    return array(
        'download_url' => $download_url,
        'source_url'   => $source_url,
    );
}

function kreativ_get_single_font_primary_action_data( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $is_free = kreativ_post_has_category_slugs( $post, kreativ_get_free_fonts_category_slugs() );

    if ( $is_free ) {
        $download_data = kreativ_get_single_font_quick_download_data( $post );

        if ( empty( $download_data ) ) {
            return array();
        }

        $primary_url     = $download_data['download_url'] ?? '';
        $primary_label   = 'Download font ZIP';
        $primary_icon    = 'fa-solid fa-arrow-down';
        $action_title    = 'Download the font ZIP before reading the full details.';
        $secondary_url   = $download_data['source_url'] ?? '';
        $secondary_label = 'View source';

        if ( '' === $primary_url && '' !== $secondary_url ) {
            $primary_url     = $secondary_url;
            $primary_label   = 'View source';
            $primary_icon    = 'fa-solid fa-arrow-up-right-from-square';
            $action_title    = 'View the official font source before reading the full details.';
            $secondary_url   = '';
            $secondary_label = '';
        }

        return array(
            'type'            => 'free',
            'eyebrow'         => 'Free font package',
            'icon'            => 'fa-solid fa-download',
            'title'           => $action_title,
            'copy'            => 'Use the page below for specimen, license notes, and package details before production work.',
            'primary_url'     => $primary_url,
            'primary_label'   => $primary_label,
            'primary_icon'    => $primary_icon,
            'primary_rel'     => 'nofollow noopener',
            'secondary_url'   => $secondary_url,
            'secondary_label' => $secondary_label,
            'secondary_rel'   => 'nofollow noopener',
            'secondary_blank' => true,
        );
    }

    if ( ! preg_match_all( '/\\[kreativ_font_cta\\b([^\\]]*)\\]/i', (string) $post->post_content, $matches, PREG_SET_ORDER ) ) {
        return array();
    }

    foreach ( $matches as $match ) {
        $atts = shortcode_parse_atts( $match[1] );

        if ( ! is_array( $atts ) ) {
            continue;
        }

        $type = sanitize_key( $atts['type'] ?? 'commercial' );

        if ( 'free' === $type ) {
            continue;
        }

        $url = esc_url_raw( $atts['url'] ?? '' );

        if ( '' === $url ) {
            continue;
        }

        $secondary_url   = esc_url_raw( $atts['secondary_url'] ?? '' );
        $secondary_label = sanitize_text_field( $atts['secondary_label'] ?? '' );

        if ( '' === $secondary_url ) {
            $secondary_url   = add_query_arg( 'font_filter', 'free', home_url( '/fonts' ) );
            $secondary_label = 'Browse free fonts';
        }

        $site_host      = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
        $secondary_host = wp_parse_url( $secondary_url, PHP_URL_HOST );
        $secondary_blank = $secondary_host && $site_host !== $secondary_host;

        return array(
            'type'            => 'commercial',
            'eyebrow'         => 'License this font',
            'icon'            => 'fa-solid fa-cart-shopping',
            'title'           => sanitize_text_field( $atts['title'] ?? 'View pricing and licensing for this font.' ),
            'copy'            => sanitize_text_field( $atts['note'] ?? 'Use the official marketplace or foundry source for legal personal or commercial licensing.' ),
            'primary_url'     => $url,
            'primary_label'   => sanitize_text_field( $atts['label'] ?? 'View / Purchase Font' ),
            'primary_icon'    => 'fa-solid fa-arrow-up-right-from-square',
            'primary_rel'     => 'nofollow sponsored noopener',
            'secondary_url'   => $secondary_url,
            'secondary_label' => '' === $secondary_label ? 'Browse similar fonts' : $secondary_label,
            'secondary_rel'   => $secondary_blank ? 'nofollow noopener' : '',
            'secondary_blank' => $secondary_blank,
        );
    }

    return array();
}

function kreativ_get_single_font_facts( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post || ! kreativ_is_font_post( $post ) ) {
        return array();
    }

    $facts = array();

    foreach ( array( 'font_style' => 'Style', 'font_mood' => 'Mood', 'font_use_case' => 'Use case' ) as $branch_key => $label ) {
        $terms = kreativ_get_post_category_branch_terms( $post, $branch_key );

        if ( empty( $terms ) ) {
            continue;
        }

        $facts[] = array(
            'label' => $label,
            'value' => implode( ', ', wp_list_pluck( array_slice( $terms, 0, 2 ), 'name' ) ),
        );
    }

    $facts[] = array(
        'label' => 'Availability',
        'value' => kreativ_post_has_category_slugs( $post, kreativ_get_free_fonts_category_slugs() ) ? 'Free font' : 'Commercial font',
    );

    return $facts;
}

function kreativ_get_font_research_board_item( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post || ! kreativ_is_font_post( $post ) ) {
        return array();
    }

    $facts = kreativ_get_single_font_facts( $post );

    return array(
        'id'    => (int) $post->ID,
        'title' => get_the_title( $post ),
        'url'   => get_permalink( $post ),
        'image' => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
        'facts' => wp_list_pluck( $facts, 'value' ),
    );
}

function kreativ_get_requested_font_research_pair() {
    $first_id  = isset( $_GET['font_a'] ) ? absint( $_GET['font_a'] ) : 0;
    $second_id = isset( $_GET['font_b'] ) ? absint( $_GET['font_b'] ) : 0;

    if ( ! $first_id || ! $second_id || $first_id === $second_id ) {
        return array();
    }

    $posts = array();

    foreach ( array( $first_id, $second_id ) as $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status || ! kreativ_is_font_post( $post ) ) {
            return array();
        }

        $posts[] = $post;
    }

    return $posts;
}
