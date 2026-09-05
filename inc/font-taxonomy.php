<?php

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
