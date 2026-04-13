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
        'fonts'            => 'Fonts',
        'templates-themes' => 'Templates',
        'graphics'         => 'Graphics',
        'photos'           => 'Photos',
        'videos'           => 'Videos',
        'sounds'           => 'Sounds',
        'free'             => 'Freebies',
    );
}

function kreativ_get_category_icons() {
    return array(
        'fonts'            => 'fa-solid fa-font',
        'templates-themes' => 'fa-solid fa-layer-group',
        'graphics'         => 'fa-solid fa-pen-nib',
        'photos'           => 'fa-solid fa-camera',
        'videos'           => 'fa-solid fa-film',
        'sounds'           => 'fa-solid fa-music',
        'free'             => 'fa-solid fa-gift',
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

function kreativ_filter_has_branch_terms( $branch_key, $child_slugs ) {
    $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

    if ( empty( $parent_ids ) ) {
        return false;
    }

    $child_slugs = array_map( 'sanitize_title', (array) $child_slugs );

    foreach ( $child_slugs as $child_slug ) {
        $term = get_term_by( 'slug', $child_slug, 'category' );

        if ( ! $term || is_wp_error( $term ) ) {
            continue;
        }

        $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

        if ( array_intersect( array_map( 'intval', $ancestors ), $parent_ids ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_get_font_filter_tax_clause( $branch_key, $child_slugs, $fallback_tag_slugs = array() ) {
    $child_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', (array) $child_slugs ) ) ) );

    if ( ! empty( $child_slugs ) && kreativ_filter_has_branch_terms( $branch_key, $child_slugs ) ) {
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
            'tax_query' => array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => array( 'free' ),
                ),
            ),
            'available' => kreativ_filter_has_terms( 'category', array( 'free' ) ),
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
            'summary'    => 'Browse curated type, refine your filters, and move quickly into better font decisions.',
            'side_title' => 'Discovery stays fast when the archive stays focused.',
            'side_copy'  => 'Use filters and related links to move through styles, moods, and use cases without falling back to generic browsing.',
        );
    }

    $branch_key    = 'category' === $archive_type ? kreativ_get_branch_key_for_category_term( $term ) : '';
    $branch_labels = kreativ_get_archive_branch_labels();
    $filter_label  = ! empty( $font_filters[ $active_filter ]['label'] ) ? $font_filters[ $active_filter ]['label'] : 'Latest';
    $count_phrase  = sprintf( '%d result%s', (int) $result_count, 1 === (int) $result_count ? '' : 's' );

    if ( 'tag' === $archive_type ) {
        return array(
            'eyebrow'    => 'Tag archive',
            'title'      => 'Explore fonts tagged ' . $term->name,
            'summary'    => $count_phrase . ' connected to this tag. Use the filter bar and related discovery links to branch into more structured font paths.',
            'side_title' => 'Tags are a loose entry point, not the whole discovery model.',
            'side_copy'  => 'From here you can pivot into more structured filters like style, mood, use case, designer, or foundry.',
        );
    }

    if ( isset( $branch_labels[ $branch_key ] ) ) {
        $singular = rtrim( $branch_labels[ $branch_key ], 's' );

        return array(
            'eyebrow'    => $branch_labels[ $branch_key ] . ' archive',
            'title'      => 'Explore ' . $term->name . ' across the font library',
            'summary'    => $count_phrase . ' currently surfaced here. Filter by ' . strtolower( $filter_label ) . ' order and move through related discovery paths without leaving the archive.',
            'side_title' => $term->name . ' sits inside the ' . strtolower( $singular ) . ' branch.',
            'side_copy'  => 'Use sibling terms and homepage-style filters to move laterally through the library instead of browsing one post at a time.',
        );
    }

    return array(
        'eyebrow'    => 'Category archive',
        'title'      => 'Browse ' . $term->name,
        'summary'    => $count_phrase . ' available under this category. Use filters and related discovery links to tighten the view and keep browsing momentum.',
        'side_title' => 'Use this archive as a discovery hub, not just a list.',
        'side_copy'  => 'The archive now shares the same visual system and filter language as the homepage, search, and single pages.',
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

function kreativ_get_search_terms( $search_string ) {
    $search_string = trim( (string) $search_string );

    if ( '' === $search_string ) {
        return array();
    }

    $terms = preg_split( '/\s+/', $search_string );
    $terms = array_filter(
        array_map( 'trim', (array) $terms ),
        static function ( $term ) {
            return '' !== $term;
        }
    );

    return array_values( array_unique( $terms ) );
}

function kreativ_get_search_branch_match_map() {
    return array(
        'designer' => array(
            'label' => 'Matched designer',
            'boost' => 85,
        ),
        'foundry' => array(
            'label' => 'Matched foundry',
            'boost' => 80,
        ),
        'font_style' => array(
            'label' => 'Matched style',
            'boost' => 75,
        ),
        'font_mood' => array(
            'label' => 'Matched mood',
            'boost' => 65,
        ),
        'font_use_case' => array(
            'label' => 'Matched use case',
            'boost' => 65,
        ),
    );
}

function kreativ_get_search_suggestion_group_map() {
    return array(
        'designer' => array(
            'label_key' => 'designer',
            'link_type' => 'category',
        ),
        'foundry' => array(
            'label_key' => 'foundry',
            'link_type' => 'category',
        ),
        'font_style' => array(
            'label_key' => 'style',
            'link_type' => 'category',
        ),
        'font_mood' => array(
            'label_key' => 'mood',
            'link_type' => 'category',
        ),
        'font_use_case' => array(
            'label_key' => 'useCase',
            'link_type' => 'category',
        ),
    );
}

function kreativ_get_branch_terms_by_search( $branch_key, $search_string, $limit = 4 ) {
    $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

    if ( empty( $parent_ids ) ) {
        return array();
    }

    $search_string = trim( (string) $search_string );
    $matching_ids  = array();
    $terms         = get_terms(
        array(
            'taxonomy'   => 'category',
            'hide_empty' => true,
            'search'     => $search_string,
            'number'     => max( 1, (int) $limit * 3 ),
        )
    );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return array();
    }

    foreach ( $terms as $term ) {
        if ( in_array( (int) $term->term_id, $parent_ids, true ) ) {
            continue;
        }

        $ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

        if ( array_intersect( array_map( 'intval', $ancestors ), $parent_ids ) ) {
            $matching_ids[] = (int) $term->term_id;
        }
    }

    if ( empty( $matching_ids ) ) {
        return array();
    }

    $matching_ids = array_slice( array_values( array_unique( $matching_ids ) ), 0, $limit );
    $results      = array();

    foreach ( $matching_ids as $term_id ) {
        $term = get_term( $term_id, 'category' );

        if ( $term && ! is_wp_error( $term ) ) {
            $results[] = $term;
        }
    }

    return $results;
}

function kreativ_get_font_title_suggestions( $search_string, $limit = 5 ) {
    $query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => max( 1, (int) $limit ),
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            's'                      => $search_string,
        )
    );

    if ( ! $query->have_posts() ) {
        return array();
    }

    return $query->posts;
}

function kreativ_build_search_suggestions_response( $search_string ) {
    $search_string = trim( (string) $search_string );
    $response      = array(
        'fonts'    => array(),
        'designer' => array(),
        'foundry'  => array(),
        'style'    => array(),
        'mood'     => array(),
        'useCase'  => array(),
    );

    if ( '' === $search_string ) {
        return $response;
    }

    foreach ( kreativ_get_font_title_suggestions( $search_string, 5 ) as $post ) {
        $font_credits = kreativ_get_font_credit_data( $post );
        $context_bits = array();
        $title        = wp_specialchars_decode( get_the_title( $post ), ENT_QUOTES );

        if ( ! empty( $font_credits['designer'] ) ) {
            $context_bits[] = 'Designer: ' . $font_credits['designer'];
        }

        if ( ! empty( $font_credits['foundry'] ) ) {
            $context_bits[] = 'Foundry: ' . $font_credits['foundry'];
        }

        $response['fonts'][] = array(
            'label'   => $title,
            'url'     => get_permalink( $post ),
            'thumb'   => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
            'context' => wp_specialchars_decode( implode( ' • ', $context_bits ), ENT_QUOTES ),
        );
    }

    foreach ( kreativ_get_search_suggestion_group_map() as $branch_key => $config ) {
        $terms = kreativ_get_branch_terms_by_search( $branch_key, $search_string, 4 );

        foreach ( $terms as $term ) {
            $response[ $config['label_key'] ][] = array(
                'label' => $term->name,
                'url'   => get_category_link( $term ),
            );
        }
    }

    return $response;
}

function kreativ_get_search_branch_term_matches( $search_string, $limit = 4 ) {
    $matches = array();

    foreach ( kreativ_get_search_suggestion_group_map() as $branch_key => $config ) {
        $matches[ $branch_key ] = kreativ_get_branch_terms_by_search( $branch_key, $search_string, $limit );
    }

    return $matches;
}

function kreativ_get_search_refinement_groups( $search_string, $limit = 5 ) {
    return kreativ_get_search_refinement_groups_with_state( $search_string, $limit );
}

function kreativ_get_search_refinement_query_map() {
    return array(
        'designer' => array(
            'label' => 'Designers',
            'param' => 'designer',
        ),
        'foundry' => array(
            'label' => 'Foundries',
            'param' => 'foundry',
        ),
        'font_style' => array(
            'label' => 'Styles',
            'param' => 'style',
        ),
        'font_mood' => array(
            'label' => 'Moods',
            'param' => 'mood',
        ),
        'font_use_case' => array(
            'label' => 'Use Cases',
            'param' => 'use_case',
        ),
    );
}

function kreativ_get_active_search_refinements() {
    $active = array();

    foreach ( kreativ_get_search_refinement_query_map() as $branch_key => $config ) {
        if ( empty( $_GET[ $config['param'] ] ) ) {
            continue;
        }

        $slug = sanitize_title( wp_unslash( $_GET[ $config['param'] ] ) );

        if ( '' !== $slug ) {
            $active[ $branch_key ] = $slug;
        }
    }

    return $active;
}

function kreativ_get_search_refinement_base_args( $search_string, $active_refinements = array() ) {
    $args = array(
        's' => $search_string,
    );

    foreach ( kreativ_get_search_refinement_query_map() as $branch_key => $config ) {
        if ( empty( $active_refinements[ $branch_key ] ) ) {
            continue;
        }

        $args[ $config['param'] ] = $active_refinements[ $branch_key ];
    }

    return $args;
}

function kreativ_get_structured_search_post_ids( $search_string, $active_refinements = array(), $branch_term_limit = 5 ) {
    $search_string = trim( (string) $search_string );

    if ( '' === $search_string ) {
        return array();
    }

    $scores     = array();
    $sort_hints = array();
    $text_query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 120,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'fields'                 => 'ids',
            's'                      => $search_string,
            'kreativ_enhanced_search'=> true,
        )
    );

    foreach ( $text_query->posts as $index => $post_id ) {
        $scores[ $post_id ]     = ( $scores[ $post_id ] ?? 0 ) + max( 600 - ( $index * 8 ), 40 );
        $sort_hints[ $post_id ] = $index;
    }

    $branch_terms = kreativ_get_search_branch_term_matches( $search_string, $branch_term_limit );
    $branch_map   = kreativ_get_search_branch_match_map();

    foreach ( $branch_terms as $branch_key => $terms ) {
        if ( empty( $terms ) || empty( $branch_map[ $branch_key ] ) ) {
            continue;
        }

        $term_ids = array_map(
            static function ( $term ) {
                return (int) $term->term_id;
            },
            $terms
        );

        $branch_posts = get_posts(
            array(
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => 120,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'fields'                 => 'ids',
                'tax_query'              => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'term_id',
                        'terms'    => $term_ids,
                    ),
                ),
            )
        );

        foreach ( $branch_posts as $index => $post_id ) {
            $scores[ $post_id ] = ( $scores[ $post_id ] ?? 0 ) + max( $branch_map[ $branch_key ]['boost'] * 3 - ( $index * 2 ), 18 );

            if ( ! isset( $sort_hints[ $post_id ] ) ) {
                $sort_hints[ $post_id ] = 1000 + $index;
            }
        }
    }

    if ( ! empty( $active_refinements ) ) {
        foreach ( array_keys( $scores ) as $post_id ) {
            if ( ! kreativ_post_matches_search_refinements( $post_id, $active_refinements ) ) {
                unset( $scores[ $post_id ] );
                unset( $sort_hints[ $post_id ] );
            }
        }
    }

    if ( empty( $scores ) ) {
        return array();
    }

    $post_ids = array_keys( $scores );

    usort(
        $post_ids,
        static function ( $left, $right ) use ( $scores, $sort_hints ) {
            $left_score  = $scores[ $left ] ?? 0;
            $right_score = $scores[ $right ] ?? 0;

            if ( $left_score === $right_score ) {
                return ( $sort_hints[ $left ] ?? PHP_INT_MAX ) <=> ( $sort_hints[ $right ] ?? PHP_INT_MAX );
            }

            return $right_score <=> $left_score;
        }
    );

    return $post_ids;
}

function kreativ_count_matching_posts_for_branch_term( $post_ids, $branch_key, $term_slug ) {
    $count = 0;

    foreach ( $post_ids as $post_id ) {
        $terms = kreativ_get_post_category_branch_terms( $post_id, $branch_key );

        foreach ( $terms as $term ) {
            if ( $term->slug === $term_slug ) {
                ++$count;
                break;
            }
        }
    }

    return $count;
}

function kreativ_get_search_refinement_groups_with_state( $search_string, $limit = 5, $active_refinements = array() ) {
    $raw_matches = kreativ_get_search_branch_term_matches( $search_string, $limit );
    $labels      = kreativ_get_search_refinement_query_map();
    $groups      = array();

    foreach ( $labels as $branch_key => $config ) {
        if ( empty( $raw_matches[ $branch_key ] ) ) {
            continue;
        }

        $group_base_refinements = $active_refinements;
        unset( $group_base_refinements[ $branch_key ] );
        $group_base_ids = kreativ_get_structured_search_post_ids( $search_string, $group_base_refinements, $limit );

        $groups[ $branch_key ] = array(
            'label' => $config['label'],
            'param' => $config['param'],
            'terms' => array_map(
                static function ( $term ) use ( $search_string, $active_refinements, $branch_key, $config, $group_base_ids ) {
                    $base_args = kreativ_get_search_refinement_base_args( $search_string, $active_refinements );
                    $is_active = ! empty( $active_refinements[ $branch_key ] ) && $active_refinements[ $branch_key ] === $term->slug;
                    $count     = kreativ_count_matching_posts_for_branch_term( $group_base_ids, $branch_key, $term->slug );

                    if ( $is_active ) {
                        unset( $base_args[ $config['param'] ] );
                    } else {
                        $base_args[ $config['param'] ] = $term->slug;
                    }

                    return array(
                        'name'      => $term->name,
                        'slug'      => $term->slug,
                        'url'       => add_query_arg( $base_args, home_url( '/' ) ),
                        'is_active' => $is_active,
                        'count'     => $count,
                    );
                },
                $raw_matches[ $branch_key ]
            ),
        );
    }

    return $groups;
}

function kreativ_post_matches_search_refinements( $post_id, $active_refinements = array() ) {
    foreach ( $active_refinements as $branch_key => $term_slug ) {
        $terms = kreativ_get_post_category_branch_terms( $post_id, $branch_key );

        if ( empty( $terms ) ) {
            return false;
        }

        $matched = false;

        foreach ( $terms as $term ) {
            if ( $term->slug === $term_slug ) {
                $matched = true;
                break;
            }
        }

        if ( ! $matched ) {
            return false;
        }
    }

    return true;
}

function kreativ_get_single_taxonomy_groups( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $group_map = array(
        'designer' => array(
            'label' => 'Designers',
            'icon'  => 'fa-solid fa-pen-nib',
        ),
        'foundry' => array(
            'label' => 'Foundries',
            'icon'  => 'fa-solid fa-building',
        ),
        'font_style' => array(
            'label' => 'Style',
            'icon'  => 'fa-solid fa-font',
        ),
        'font_mood' => array(
            'label' => 'Mood',
            'icon'  => 'fa-solid fa-sparkles',
        ),
        'font_use_case' => array(
            'label' => 'Use Cases',
            'icon'  => 'fa-solid fa-layer-group',
        ),
    );

    $groups = array();

    foreach ( $group_map as $branch_key => $config ) {
        $terms = kreativ_get_post_category_branch_terms( $post, $branch_key );

        if ( empty( $terms ) ) {
            continue;
        }

        $groups[ $branch_key ] = array(
            'label' => $config['label'],
            'icon'  => $config['icon'],
            'terms' => array_map(
                static function ( $term ) {
                    return array(
                        'name' => $term->name,
                        'url'  => get_category_link( $term ),
                    );
                },
                $terms
            ),
        );
    }

    return $groups;
}

function kreativ_get_single_residual_tags( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $tags = get_the_tags( $post->ID );

    if ( ! $tags || is_wp_error( $tags ) ) {
        return array();
    }

    $structured_names = array();
    $structured_slugs = array();

    foreach ( array_keys( kreativ_get_search_suggestion_group_map() ) as $branch_key ) {
        $terms = kreativ_get_post_category_branch_terms( $post, $branch_key );

        foreach ( $terms as $term ) {
            $structured_names[] = strtolower( trim( $term->name ) );
            $structured_slugs[] = strtolower( trim( $term->slug ) );
        }
    }

    $structured_names = array_unique( $structured_names );
    $structured_slugs = array_unique( $structured_slugs );
    $residual_tags    = array();

    foreach ( $tags as $tag ) {
        $tag_name = strtolower( trim( $tag->name ) );
        $tag_slug = strtolower( trim( $tag->slug ) );

        if ( in_array( $tag_name, $structured_names, true ) || in_array( $tag_slug, $structured_slugs, true ) ) {
            continue;
        }

        $residual_tags[] = $tag;
    }

    return $residual_tags;
}

function kreativ_get_structured_search_results( $search_string, $paged = 1, $posts_per_page = 24, $active_refinements = array() ) {
    $search_string   = trim( (string) $search_string );
    $paged           = max( 1, (int) $paged );
    $posts_per_page  = max( 1, (int) $posts_per_page );
    $empty_query     = new WP_Query(
        array(
            'post_type'      => 'post',
            'post__in'       => array( 0 ),
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
        )
    );

    if ( '' === $search_string ) {
        return array(
            'query' => $empty_query,
            'total' => 0,
        );
    }
    $post_ids = kreativ_get_structured_search_post_ids( $search_string, $active_refinements, 5 );

    if ( empty( $post_ids ) ) {
        return array(
            'query' => $empty_query,
            'total' => 0,
        );
    }

    $total       = count( $post_ids );
    $offset      = ( $paged - 1 ) * $posts_per_page;
    $page_post_ids = array_slice( $post_ids, $offset, $posts_per_page );

    if ( empty( $page_post_ids ) ) {
        return array(
            'query' => $empty_query,
            'total' => $total,
        );
    }

    $query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $posts_per_page,
            'paged'                  => $paged,
            'ignore_sticky_posts'    => true,
            'post__in'               => $page_post_ids,
            'orderby'                => 'post__in',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
        )
    );

    $query->found_posts   = $total;
    $query->max_num_pages = (int) ceil( $total / $posts_per_page );

    return array(
        'query' => $query,
        'total' => $total,
    );
}

function kreativ_handle_search_suggestions() {
    check_ajax_referer( 'kreativ_search_suggest', 'nonce' );

    $search_string = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

    if ( mb_strlen( $search_string ) < 2 ) {
        wp_send_json_success(
            array(
                'groups' => kreativ_build_search_suggestions_response( '' ),
            )
        );
    }

    wp_send_json_success(
        array(
            'groups' => kreativ_build_search_suggestions_response( $search_string ),
        )
    );
}
add_action( 'wp_ajax_kreativ_search_suggest', 'kreativ_handle_search_suggestions' );
add_action( 'wp_ajax_nopriv_kreativ_search_suggest', 'kreativ_handle_search_suggestions' );

function kreativ_tune_main_search_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return;
    }

    $query->set( 'post_type', 'post' );
    $query->set( 'post_status', 'publish' );
    $query->set( 'ignore_sticky_posts', true );
    $query->set( 'posts_per_page', 24 );
    $query->set( 'kreativ_enhanced_search', true );
}
add_action( 'pre_get_posts', 'kreativ_tune_main_search_query' );

function kreativ_search_orderby( $orderby, $query ) {
    if ( ! $query->get( 'kreativ_enhanced_search' ) ) {
        return $orderby;
    }

    global $wpdb;

    $search_string = trim( (string) $query->get( 's' ) );

    if ( '' === $search_string ) {
        return $orderby;
    }

    $search_terms = kreativ_get_search_terms( $search_string );
    $exact_title  = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title = %s THEN 350 ELSE 0 END", $search_string );
    $prefix_title = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 220 ELSE 0 END", $wpdb->esc_like( $search_string ) . '%' );
    $phrase_title = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 140 ELSE 0 END", '%' . $wpdb->esc_like( $search_string ) . '%' );
    $excerpt_hit  = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_excerpt LIKE %s THEN 45 ELSE 0 END", '%' . $wpdb->esc_like( $search_string ) . '%' );
    $content_hit  = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_content LIKE %s THEN 30 ELSE 0 END", '%' . $wpdb->esc_like( $search_string ) . '%' );

    $term_scores = array();

    foreach ( $search_terms as $search_term ) {
        $term_scores[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 40 ELSE 0 END", '%' . $wpdb->esc_like( $search_term ) . '%' );
        $term_scores[] = $wpdb->prepare(
            "CASE WHEN EXISTS (
                SELECT 1
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tr.object_id = {$wpdb->posts}.ID
                AND tt.taxonomy = 'category'
                AND t.name LIKE %s
            ) THEN 35 ELSE 0 END",
            '%' . $wpdb->esc_like( $search_term ) . '%'
        );
    }

    foreach ( kreativ_get_search_branch_match_map() as $branch_key => $branch_config ) {
        $parent_ids = kreativ_get_font_branch_parent_term_ids( $branch_key );

        if ( empty( $parent_ids ) ) {
            continue;
        }

        $parent_ids_sql = implode( ',', array_map( 'intval', $parent_ids ) );

        foreach ( $search_terms as $search_term ) {
            $term_scores[] = $wpdb->prepare(
                "CASE WHEN EXISTS (
                    SELECT 1
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tr.object_id = {$wpdb->posts}.ID
                    AND tt.taxonomy = 'category'
                    AND t.term_id NOT IN ({$parent_ids_sql})
                    AND EXISTS (
                        SELECT 1
                        FROM {$wpdb->term_taxonomy} branch_tt
                        WHERE branch_tt.taxonomy = 'category'
                        AND branch_tt.term_id = tt.term_id
                        AND branch_tt.parent IN ({$parent_ids_sql})
                    )
                    AND (t.name LIKE %s OR t.slug LIKE %s)
                ) THEN {$branch_config['boost']} ELSE 0 END",
                '%' . $wpdb->esc_like( $search_term ) . '%',
                '%' . $wpdb->esc_like( sanitize_title( $search_term ) ) . '%'
            );
        }
    }

    $fonts_category_boost = "CASE WHEN EXISTS (
        SELECT 1
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE tr.object_id = {$wpdb->posts}.ID
        AND tt.taxonomy = 'category'
        AND t.slug = 'fonts'
    ) THEN 25 ELSE 0 END";

    $score_parts = array_merge(
        array(
            $exact_title,
            $prefix_title,
            $phrase_title,
            $excerpt_hit,
            $content_hit,
            $fonts_category_boost,
        ),
        $term_scores
    );

    return '( ' . implode( ' + ', $score_parts ) . " ) DESC, {$wpdb->posts}.post_date DESC";
}
add_filter( 'posts_orderby', 'kreativ_search_orderby', 10, 2 );

function kreativ_get_search_match_label( $post = null, $search_string = '' ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return '';
    }

    $search_string = trim( (string) $search_string );

    if ( '' === $search_string ) {
        $search_string = get_search_query();
    }

    $search_string = trim( (string) $search_string );

    if ( '' === $search_string ) {
        return '';
    }

    $normalized_query = strtolower( $search_string );
    $title            = strtolower( get_the_title( $post ) );
    $excerpt          = strtolower( (string) get_the_excerpt( $post ) );
    $content          = strtolower( wp_strip_all_tags( $post->post_content ) );

    if ( false !== strpos( $title, $normalized_query ) ) {
        return 'Matched title';
    }

    $search_terms = kreativ_get_search_terms( $search_string );
    $branch_match_map = kreativ_get_search_branch_match_map();

    foreach ( $branch_match_map as $branch_key => $branch_config ) {
        $branch_terms = kreativ_get_post_category_branch_terms( $post, $branch_key );

        if ( empty( $branch_terms ) ) {
            continue;
        }

        foreach ( $branch_terms as $branch_term ) {
            $term_name = strtolower( $branch_term->name );
            $term_slug = strtolower( $branch_term->slug );

            if ( false !== strpos( $term_name, $normalized_query ) || false !== strpos( $term_slug, sanitize_title( $normalized_query ) ) ) {
                return $branch_config['label'];
            }

            foreach ( $search_terms as $search_term ) {
                $search_term = strtolower( $search_term );
                $search_slug = sanitize_title( $search_term );

                if ( false !== strpos( $term_name, $search_term ) || false !== strpos( $term_slug, $search_slug ) ) {
                    return $branch_config['label'];
                }
            }
        }
    }

    $categories = get_the_terms( $post->ID, 'category' );

    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            $cat_name = strtolower( $category->name );
            $cat_slug = strtolower( $category->slug );

            if ( false !== strpos( $cat_name, $normalized_query ) || false !== strpos( $cat_slug, $normalized_query ) ) {
                return 'Matched category';
            }

            foreach ( $search_terms as $search_term ) {
                $search_term = strtolower( $search_term );

                if ( false !== strpos( $cat_name, $search_term ) || false !== strpos( $cat_slug, $search_term ) ) {
                    return 'Matched category';
                }
            }
        }
    }

    if ( false !== strpos( $excerpt, $normalized_query ) || false !== strpos( $content, $normalized_query ) ) {
        return 'Matched description';
    }

    return 'Search result';
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
        $args['tax_query'][] = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => array( 'free' ),
        );
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

function kreativ_get_content_summary( $post = null, $max_words = 24 ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return '';
    }

    if ( has_excerpt( $post ) ) {
        return wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), $max_words, '...' );
    }

    $content = apply_filters( 'the_content', $post->post_content );

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

    $designer_term = kreativ_get_primary_branch_term( $post, 'designer' );
    $foundry_term  = kreativ_get_primary_branch_term( $post, 'foundry' );
    $designer      = $designer_term ? $designer_term->name : '';
    $foundry       = $foundry_term ? $foundry_term->name : '';

    $content = apply_filters( 'the_content', $post->post_content );
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

    $summary = strtolower( kreativ_get_content_summary( $post, 28 ) );

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
        'empty_thumb_url'  => get_template_directory_uri() . '/img/default-thumb.png',
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
