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

function kreativ_get_search_cache_version() {
    $version = get_option( 'kreativ_search_cache_version' );

    if ( ! $version ) {
        $version = (string) time();
        update_option( 'kreativ_search_cache_version', $version, false );
    }

    return (string) $version;
}

function kreativ_bump_search_cache_version( ...$args ) {
    update_option( 'kreativ_search_cache_version', (string) microtime( true ), false );
}
add_action( 'save_post_post', 'kreativ_bump_search_cache_version' );
add_action( 'deleted_post', 'kreativ_bump_search_cache_version' );
add_action( 'created_category', 'kreativ_bump_search_cache_version' );
add_action( 'edited_category', 'kreativ_bump_search_cache_version' );
add_action( 'delete_category', 'kreativ_bump_search_cache_version' );

function kreativ_get_search_cache_key( $group, $data = array() ) {
    return 'kf_search_' . md5(
        wp_json_encode(
            array(
                'group'   => $group,
                'version' => kreativ_get_search_cache_version(),
                'schema'  => '2026-07-30.1',
                'data'    => $data,
            )
        )
    );
}

function kreativ_get_cached_search_value( $cache_key ) {
    $cached = get_transient( $cache_key );

    if ( is_array( $cached ) && array_key_exists( 'value', $cached ) ) {
        return $cached['value'];
    }

    return null;
}

function kreativ_set_cached_search_value( $cache_key, $value, $ttl = 900 ) {
    set_transient(
        $cache_key,
        array(
            'value' => $value,
        ),
        max( 60, (int) $ttl )
    );
}

function kreativ_get_search_title_match_score( $title, $search_string ) {
    $normalized_title = remove_accents( wp_strip_all_tags( (string) $title ) );
    $normalized_query = remove_accents( wp_strip_all_tags( trim( (string) $search_string ) ) );
    $normalized_title = function_exists( 'mb_strtolower' ) ? mb_strtolower( $normalized_title ) : strtolower( $normalized_title );
    $normalized_query = function_exists( 'mb_strtolower' ) ? mb_strtolower( $normalized_query ) : strtolower( $normalized_query );

    if ( '' === $normalized_query ) {
        return 0;
    }

    if ( $normalized_title === $normalized_query ) {
        return 1800;
    }

    if ( preg_match( '/^' . preg_quote( $normalized_query, '/' ) . '(?:\b|$)/u', $normalized_title ) ) {
        return 1200;
    }

    if ( false !== strpos( $normalized_title, $normalized_query ) ) {
        return 450;
    }

    return 0;
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
    $limit = max( 1, (int) $limit );
    $query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => max( 20, $limit * 6 ),
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
            's'                      => $search_string,
            'kreativ_enhanced_search' => true,
        )
    );

    if ( ! $query->have_posts() ) {
        return array();
    }

    $candidates = array();

    foreach ( $query->posts as $index => $post ) {
        $title_score = kreativ_get_search_title_match_score( get_the_title( $post ), $search_string );

        if ( 'font' !== kreativ_get_single_content_kind( $post ) || 0 === $title_score ) {
            continue;
        }

        $candidates[] = array(
            'post'  => $post,
            'score' => $title_score,
            'index' => $index,
        );
    }

    usort(
        $candidates,
        static function ( $left, $right ) {
            if ( $left['score'] === $right['score'] ) {
                return $left['index'] <=> $right['index'];
            }

            return $right['score'] <=> $left['score'];
        }
    );

    return array_slice( array_column( $candidates, 'post' ), 0, $limit );
}

function kreativ_build_search_suggestions_response( $search_string ) {
    $search_string = trim( (string) $search_string );
    $cache_key     = kreativ_get_search_cache_key(
        'suggestions',
        array(
            'q' => strtolower( $search_string ),
        )
    );
    $cached        = kreativ_get_cached_search_value( $cache_key );

    if ( null !== $cached ) {
        return $cached;
    }

    $response      = array(
        'fonts'    => array(),
        'designer' => array(),
        'foundry'  => array(),
        'style'    => array(),
        'mood'     => array(),
        'useCase'  => array(),
    );

    if ( '' === $search_string ) {
        kreativ_set_cached_search_value( $cache_key, $response, 5 * MINUTE_IN_SECONDS );
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

    kreativ_set_cached_search_value( $cache_key, $response, 15 * MINUTE_IN_SECONDS );

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
    $active_refinements = array_filter( (array) $active_refinements );
    ksort( $active_refinements );

    if ( '' === $search_string ) {
        return array();
    }

    $cache_key = kreativ_get_search_cache_key(
        'structured_ids',
        array(
            'q'           => strtolower( $search_string ),
            'refinements' => $active_refinements,
            'limit'       => (int) $branch_term_limit,
        )
    );
    $cached = kreativ_get_cached_search_value( $cache_key );

    if ( null !== $cached ) {
        return $cached;
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
            'kreativ_enhanced_search' => true,
        )
    );

    foreach ( $text_query->posts as $index => $post_id ) {
        $scores[ $post_id ]     = ( $scores[ $post_id ] ?? 0 ) + max( 600 - ( $index * 8 ), 40 );
        $sort_hints[ $post_id ] = $index;

        $scores[ $post_id ] += kreativ_get_search_title_match_score( get_the_title( $post_id ), $search_string );
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
        kreativ_set_cached_search_value( $cache_key, array(), 10 * MINUTE_IN_SECONDS );
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

    kreativ_set_cached_search_value( $cache_key, $post_ids, 15 * MINUTE_IN_SECONDS );

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
    $search_string = trim( (string) $search_string );
    $active_refinements = array_filter( (array) $active_refinements );
    ksort( $active_refinements );
    $cache_key = kreativ_get_search_cache_key(
        'refinement_groups',
        array(
            'q'           => strtolower( $search_string ),
            'limit'       => (int) $limit,
            'refinements' => $active_refinements,
        )
    );
    $cached = kreativ_get_cached_search_value( $cache_key );

    if ( null !== $cached ) {
        return $cached;
    }

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

    kreativ_set_cached_search_value( $cache_key, $groups, 15 * MINUTE_IN_SECONDS );

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
        'font_style' => array(
            'label' => 'Style',
            'icon'  => 'fa-solid fa-font',
        ),
        'designer' => array(
            'label' => 'Designers',
            'icon'  => 'fa-solid fa-pen-nib',
        ),
        'foundry' => array(
            'label' => 'Foundries',
            'icon'  => 'fa-solid fa-building',
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

function kreativ_get_single_breadcrumb_items( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $items = array(
        array(
            'label' => 'Fonts',
            'url'   => home_url( '/fonts' ),
        ),
    );

    foreach ( array( 'font_style', 'foundry', 'designer' ) as $branch_key ) {
        $term = kreativ_get_primary_branch_term( $post, $branch_key );

        if ( ! $term ) {
            continue;
        }

        $items[] = array(
            'label' => $term->name,
            'url'   => get_category_link( $term ),
        );

        break;
    }

    return $items;
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

    $categories = get_the_terms( $post->ID, 'category' );

    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            if ( kreativ_is_font_archive_term( $category ) ) {
                return 'font';
            }
        }
    }

    foreach ( array_keys( kreativ_get_font_taxonomy_branch_definitions() ) as $branch_key ) {
        if ( kreativ_get_post_category_branch_terms( $post, $branch_key ) ) {
            return 'font';
        }
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

function kreativ_get_font_collection_links() {
    $collections = array(
        array(
            'slug'     => 'trending-commercial-fonts',
            'title'    => 'Trending Commercial Fonts',
            'icon'     => 'fa-solid fa-chart-line',
            'copy'     => 'Premium picks for branding, editorial, packaging, and client work.',
            'group'    => 'popular',
            'featured' => true,
        ),
        array(
            'slug'     => 'best-free-fonts-commercial-use',
            'title'    => 'Best Free Fonts for Commercial Use',
            'icon'     => 'fa-solid fa-gift',
            'copy'     => 'Free font downloads with commercial-use context and licensing reminders.',
            'group'    => 'licensing',
            'featured' => true,
        ),
        array(
            'slug'     => 'best-vintage-script-fonts',
            'title'    => 'Best Vintage Script Fonts',
            'icon'     => 'fa-solid fa-pen-nib',
            'copy'     => 'Script fonts with a vintage mood for nostalgic branding and display work.',
            'group'    => 'style',
            'featured' => true,
        ),
        array(
            'slug'     => 'best-display-fonts',
            'title'    => 'Best Display Fonts',
            'icon'     => 'fa-solid fa-heading',
            'copy'     => 'High-impact display faces for headlines, posters, covers, and visual systems.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-handwritten-fonts',
            'title'    => 'Best Handwritten Fonts',
            'icon'     => 'fa-solid fa-pen',
            'copy'     => 'Handwritten and hand-drawn fonts for personal, expressive, and casual designs.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-modern-sans-serif-fonts',
            'title'    => 'Best Modern Sans Serif Fonts',
            'icon'     => 'fa-solid fa-circle-half-stroke',
            'copy'     => 'Clean sans serif picks for modern interfaces, brands, and editorial systems.',
            'group'    => 'popular',
            'featured' => true,
        ),
        array(
            'slug'     => 'best-elegant-serif-fonts',
            'title'    => 'Best Elegant Serif Fonts',
            'icon'     => 'fa-solid fa-feather-pointed',
            'copy'     => 'Refined serif choices for luxury, fashion, publishing, and premium branding.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-logo-fonts',
            'title'    => 'Best Logo Fonts',
            'icon'     => 'fa-solid fa-signature',
            'copy'     => 'Typeface shortcuts for marks, identities, wordmarks, and brand systems.',
            'group'    => 'use_case',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-fonts-for-branding',
            'title'    => 'Best Fonts for Branding',
            'icon'     => 'fa-solid fa-bullseye',
            'copy'     => 'Fonts selected around identity work, client projects, and visual positioning.',
            'group'    => 'popular',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-wedding-fonts',
            'title'    => 'Best Wedding Fonts',
            'icon'     => 'fa-solid fa-ring',
            'copy'     => 'Elegant, romantic, and decorative fonts for invitations and event design.',
            'group'    => 'use_case',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-poster-fonts',
            'title'    => 'Best Poster Fonts',
            'icon'     => 'fa-solid fa-rectangle-ad',
            'copy'     => 'Display-ready fonts for campaigns, posters, covers, and bold compositions.',
            'group'    => 'use_case',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-minimal-fonts',
            'title'    => 'Best Minimal Fonts',
            'icon'     => 'fa-solid fa-minus',
            'copy'     => 'Quiet, reduced, and practical fonts for clean visual systems.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-retro-fonts',
            'title'    => 'Best Retro Fonts',
            'icon'     => 'fa-solid fa-clock-rotate-left',
            'copy'     => 'Retro and vintage-inspired fonts for nostalgic brands, posters, and packaging.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-condensed-fonts',
            'title'    => 'Best Condensed Fonts',
            'icon'     => 'fa-solid fa-compress',
            'copy'     => 'Narrow, condensed fonts for compact headlines, labels, posters, and bold layouts.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-tech-futuristic-fonts',
            'title'    => 'Best Tech & Futuristic Fonts',
            'icon'     => 'fa-solid fa-microchip',
            'copy'     => 'Tech-forward and futuristic fonts for digital products, sci-fi, and modern brands.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-luxury-fonts',
            'title'    => 'Best Luxury Fonts',
            'icon'     => 'fa-solid fa-gem',
            'copy'     => 'Premium-feeling fonts for beauty, fashion, hospitality, packaging, and high-end brands.',
            'group'    => 'style',
            'featured' => false,
        ),
        array(
            'slug'     => 'best-packaging-fonts',
            'title'    => 'Best Packaging Fonts',
            'icon'     => 'fa-solid fa-box-open',
            'copy'     => 'Fonts selected for labels, product packaging, retail systems, and shelf-ready brand work.',
            'group'    => 'use_case',
            'featured' => false,
        ),
    );

    foreach ( $collections as $index => $collection ) {
        $collections[ $index ]['url']        = home_url( '/collections/' . $collection['slug'] );
        $collections[ $index ]['legacy_url'] = home_url( '/' . $collection['slug'] );
    }

    return $collections;
}

function kreativ_get_font_collection_link_by_slug( $slug ) {
    $slug = sanitize_title( $slug );

    foreach ( kreativ_get_font_collection_links() as $collection ) {
        if ( $slug === $collection['slug'] ) {
            return $collection;
        }
    }

    return array();
}

function kreativ_redirect_legacy_font_collection_urls() {
    if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $request_path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
    $request_path = untrailingslashit( '/' . ltrim( (string) $request_path, '/' ) );

    foreach ( kreativ_get_font_collection_links() as $collection ) {
        if ( '/' . $collection['slug'] === $request_path ) {
            wp_safe_redirect( $collection['url'], 301 );
            exit;
        }
    }
}
add_action( 'template_redirect', 'kreativ_redirect_legacy_font_collection_urls' );

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

function kreativ_get_single_related_font_collections( $post = null, $limit = 4 ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return array();
    }

    $related = array();
    $add_collection = static function( $slug ) use ( &$related ) {
        if ( isset( $related[ $slug ] ) ) {
            return;
        }

        $collection = kreativ_get_font_collection_link_by_slug( $slug );

        if ( ! empty( $collection ) ) {
            $related[ $slug ] = $collection;
        }
    };

    $is_free = kreativ_post_has_category_slugs( $post, kreativ_get_free_fonts_category_slugs() );

    if ( $is_free ) {
        $add_collection( 'best-free-fonts-commercial-use' );
    } else {
        $add_collection( 'trending-commercial-fonts' );
    }

    if (
        kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'sans-serif', 'sansserif' ) )
        && kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'modern' ) )
    ) {
        $add_collection( 'best-modern-sans-serif-fonts' );
    }

    if (
        kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'script' ) )
        && kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'vintage' ) )
    ) {
        $add_collection( 'best-vintage-script-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'display' ) ) ) {
        $add_collection( 'best-display-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'handwritten', 'handwriting', 'hand-drawn', 'handdrawn' ) ) ) {
        $add_collection( 'best-handwritten-fonts' );
    }

    if (
        kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'serif' ) )
        && kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'elegant' ) )
    ) {
        $add_collection( 'best-elegant-serif-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'retro', 'vintage' ) ) ) {
        $add_collection( 'best-retro-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'minimal' ) ) ) {
        $add_collection( 'best-minimal-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'luxury' ) ) ) {
        $add_collection( 'best-luxury-fonts' );
    }

    if ( kreativ_post_has_branch_term_slugs( $post, 'font_style', array( 'condensed', 'narrow', 'compressed' ) ) ) {
        $add_collection( 'best-condensed-fonts' );
    }

    if (
        kreativ_post_has_branch_term_slugs( $post, 'font_mood', array( 'tech', 'technology', 'futuristic', 'sci-fi', 'scifi' ) )
        || kreativ_post_has_branch_term_slugs( $post, 'font_use_case', array( 'tech', 'technology', 'futuristic', 'sci-fi', 'scifi' ) )
    ) {
        $add_collection( 'best-tech-futuristic-fonts' );
    }

    foreach ( array(
        'logo'      => 'best-logo-fonts',
        'branding'  => 'best-fonts-for-branding',
        'wedding'   => 'best-wedding-fonts',
        'poster'    => 'best-poster-fonts',
        'packaging' => 'best-packaging-fonts',
    ) as $use_case_slug => $collection_slug ) {
        if ( kreativ_post_has_branch_term_slugs( $post, 'font_use_case', array( $use_case_slug ) ) ) {
            $add_collection( $collection_slug );
        }
    }

    return array_slice( array_values( $related ), 0, max( 1, (int) $limit ) );
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
        $primary_label   = 'Download ZIP';
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

function kreativ_get_dynamic_font_collection_query_args( $config = array() ) {
    $defaults = array(
        'posts_per_page' => 24,
        'orderby'        => array( 'date' => 'DESC' ),
        'free_mode'      => '',
        'branch_filters' => array(),
    );

    $config    = wp_parse_args( $config, $defaults );
    $tax_query = array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => array( 'fonts' ),
        ),
    );

    $free_fonts_slugs = kreativ_get_free_fonts_category_slugs();

    if ( 'include' === $config['free_mode'] ) {
        if ( empty( $free_fonts_slugs ) ) {
            return array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => (int) $config['posts_per_page'],
                'post__in'       => array( 0 ),
            );
        }

        $tax_query[] = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $free_fonts_slugs,
        );
    }

    if ( 'exclude' === $config['free_mode'] && ! empty( $free_fonts_slugs ) ) {
        $tax_query[] = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $free_fonts_slugs,
            'operator' => 'NOT IN',
        );
    }

    $requested_branch_filters = 0;
    $matched_branch_filters   = 0;

    foreach ( (array) $config['branch_filters'] as $branch_filter ) {
        if ( empty( $branch_filter['branch'] ) || empty( $branch_filter['slugs'] ) ) {
            continue;
        }

        $requested_branch_filters++;
        $clause = kreativ_get_font_filter_tax_clause( $branch_filter['branch'], $branch_filter['slugs'] );

        if ( ! empty( $clause ) ) {
            $matched_branch_filters++;
            $tax_query[] = $clause;
        }
    }

    if ( $requested_branch_filters > $matched_branch_filters ) {
        return array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $config['posts_per_page'],
            'post__in'       => array( 0 ),
        );
    }

    return array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => (int) $config['posts_per_page'],
        'ignore_sticky_posts' => true,
        'orderby'            => $config['orderby'],
        'tax_query'          => $tax_query,
    );
}

function kreativ_post_matches_branch_filters( $post = null, $branch_filters = array() ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    foreach ( (array) $branch_filters as $branch_filter ) {
        if ( empty( $branch_filter['branch'] ) || empty( $branch_filter['slugs'] ) ) {
            continue;
        }

        $valid_slugs = kreativ_get_valid_branch_term_slugs( $branch_filter['branch'], $branch_filter['slugs'] );

        if ( empty( $valid_slugs ) ) {
            continue;
        }

        $post_slugs = array_map(
            static function ( $term ) {
                return $term->slug;
            },
            kreativ_get_post_category_branch_terms( $post, $branch_filter['branch'] )
        );

        if ( ! array_intersect( $valid_slugs, $post_slugs ) ) {
            return false;
        }
    }

    return true;
}

function kreativ_post_matches_excluded_branch_filters( $post = null, $branch_filters = array() ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    foreach ( (array) $branch_filters as $branch_filter ) {
        if ( empty( $branch_filter['branch'] ) || empty( $branch_filter['slugs'] ) ) {
            continue;
        }

        $valid_slugs = kreativ_get_valid_branch_term_slugs( $branch_filter['branch'], $branch_filter['slugs'] );

        if ( empty( $valid_slugs ) ) {
            continue;
        }

        $post_slugs = array_map(
            static function ( $term ) {
                return $term->slug;
            },
            kreativ_get_post_category_branch_terms( $post, $branch_filter['branch'] )
        );

        if ( array_intersect( $valid_slugs, $post_slugs ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_text_matches_any_pattern( $text, $patterns = array() ) {
    foreach ( (array) $patterns as $pattern ) {
        if ( ! is_string( $pattern ) || '' === $pattern ) {
            continue;
        }

        if ( preg_match( $pattern, (string) $text ) ) {
            return true;
        }
    }

    return false;
}

function kreativ_filter_dynamic_collection_posts( $posts = array(), $config = array() ) {
    $filtered_posts = array();

    foreach ( (array) $posts as $post ) {
        $post = get_post( $post );

        if ( ! $post instanceof WP_Post ) {
            continue;
        }

        if ( ! kreativ_post_matches_branch_filters( $post, $config['branch_filters'] ?? array() ) ) {
            continue;
        }

        if ( kreativ_post_matches_excluded_branch_filters( $post, $config['exclude_branch_filters'] ?? array() ) ) {
            continue;
        }

        if ( kreativ_text_matches_any_pattern( get_the_title( $post ), $config['title_exclude_patterns'] ?? array() ) ) {
            continue;
        }

        $filtered_posts[] = $post;
    }

    return $filtered_posts;
}

function kreativ_render_dynamic_font_collection_page( $config = array() ) {
    $defaults = array(
        'eyebrow'        => 'Font collection',
        'eyebrow_icon'   => 'fa-solid fa-font',
        'title'          => get_the_title(),
        'summary'        => '',
        'side_title'     => 'A focused shortcut into the font library.',
        'side_copy'      => 'New matching font posts appear here as the library grows.',
        'badges'         => array(),
        'badge_text'     => 'Fonts',
        'badge_slug'     => 'fonts',
        'context_note'   => '',
        'intro_title'    => '',
        'intro_copy'     => '',
        'intro_points'   => array(),
        'related_slugs'  => array(),
        'empty_title'    => 'No matching fonts yet.',
        'empty_copy'     => 'Matching fonts will appear here as they are added.',
        'posts_per_page' => 24,
        'orderby'        => array( 'date' => 'DESC' ),
        'free_mode'      => '',
        'free_picks_enabled' => true,
        'free_picks_count'   => 4,
        'free_picks_title'   => 'Free options in this collection',
        'free_picks_copy'    => 'A small set of matching free fonts appears here when the library has suitable options.',
        'branch_filters' => array(),
        'exclude_branch_filters' => array(),
        'title_exclude_patterns' => array(),
    );

    $config = wp_parse_args( $config, $defaults );
    $free_picks_count   = max( 0, (int) $config['free_picks_count'] );
    $show_free_picks    = ! empty( $config['free_picks_enabled'] ) && 'include' !== $config['free_mode'] && $free_picks_count > 0;
    $main_query_config  = $config;
    $free_picks_query   = null;

    if ( $show_free_picks ) {
        $main_query_config['free_mode']      = 'exclude';
        $main_query_config['posts_per_page'] = max( 1, (int) $config['posts_per_page'] - $free_picks_count );

        $free_query_config                   = $config;
        $free_query_config['free_mode']      = 'include';
        $free_query_config['posts_per_page'] = $free_picks_count;
        $free_picks_query                    = new WP_Query( kreativ_get_dynamic_font_collection_query_args( $free_query_config ) );
    }

    $query = new WP_Query( kreativ_get_dynamic_font_collection_query_args( $main_query_config ) );
    $main_posts       = kreativ_filter_dynamic_collection_posts( $query->posts, $config );
    $free_picks_posts = $free_picks_query instanceof WP_Query ? kreativ_filter_dynamic_collection_posts( $free_picks_query->posts, $free_query_config ?? $config ) : array();
    $meta_description = $config['summary'] ? $config['summary'] : $config['intro_copy'];
    $meta_description = wp_trim_words( wp_strip_all_tags( (string) $meta_description ), 30, '...' );
    $collection_items = array();
    $visible_posts     = array_merge( $main_posts, $free_picks_posts );

    foreach ( $visible_posts as $index => $collection_post ) {
        $collection_items[] = array(
            '@type'    => 'ListItem',
            'position' => $index + 1,
            'name'     => get_the_title( $collection_post ),
            'url'      => get_permalink( $collection_post ),
        );
    }

    $GLOBALS['kreativ_meta_description_override'] = $meta_description;
    $GLOBALS['kreativ_collection_page_schema']    = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'CollectionPage',
        'name'        => $config['title'],
        'description' => $meta_description,
        'url'         => get_permalink() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ),
        'isPartOf'    => array(
            '@type' => 'WebSite',
            'name'  => 'Kreativ Font',
            'url'   => home_url( '/' ),
        ),
        'mainEntity'  => array(
            '@type'           => 'ItemList',
            'numberOfItems'   => count( $collection_items ),
            'itemListElement' => $collection_items,
        ),
    );

    $has_main_posts = ! empty( $main_posts );
    $has_free_picks = ! empty( $free_picks_posts );

    get_header();
    ?>

    <div class="kreativ-page-shell kreativ-dynamic-collection-page">
        <section class="kreativ-page-hero">
            <div class="kreativ-page-hero-main">
                <div class="kreativ-page-eyebrow">
                    <i class="<?php echo esc_attr( $config['eyebrow_icon'] ); ?>" aria-hidden="true"></i>
                    <?php echo esc_html( $config['eyebrow'] ); ?>
                </div>

                <h1 class="kreativ-page-title"><?php echo esc_html( $config['title'] ); ?></h1>

                <?php if ( ! empty( $config['summary'] ) ) : ?>
                    <p class="kreativ-page-summary"><?php echo esc_html( $config['summary'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $config['badges'] ) ) : ?>
                    <div class="kreativ-page-badges">
                        <?php foreach ( $config['badges'] as $badge ) : ?>
                            <span class="kreativ-page-badge">
                                <?php if ( ! empty( $badge['icon'] ) ) : ?>
                                    <i class="<?php echo esc_attr( $badge['icon'] ); ?>" aria-hidden="true"></i>
                                <?php endif; ?>
                                <?php echo esc_html( $badge['text'] ?? '' ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="kreativ-page-hero-side">
                <div class="kreativ-page-side-card">
                    <h2><?php echo esc_html( $config['side_title'] ); ?></h2>
                    <p><?php echo esc_html( $config['side_copy'] ); ?></p>
                </div>
            </div>
        </section>

        <section class="kreativ-page-content">
            <?php if ( ! empty( $config['intro_title'] ) || ! empty( $config['intro_copy'] ) || ! empty( $config['intro_points'] ) || ! empty( $config['related_slugs'] ) ) : ?>
                <section class="kreativ-collection-intro">
                    <div class="kreativ-collection-intro-copy">
                        <?php if ( ! empty( $config['intro_title'] ) ) : ?>
                            <h2><?php echo esc_html( $config['intro_title'] ); ?></h2>
                        <?php endif; ?>

                        <?php if ( ! empty( $config['intro_copy'] ) ) : ?>
                            <p><?php echo esc_html( $config['intro_copy'] ); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $config['intro_points'] ) ) : ?>
                        <ul class="kreativ-collection-intro-points">
                            <?php foreach ( $config['intro_points'] as $point ) : ?>
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?php echo esc_html( $point ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ( ! empty( $config['related_slugs'] ) ) : ?>
                        <div class="kreativ-collection-related-links">
                            <span>Related collections</span>
                            <?php foreach ( $config['related_slugs'] as $related_slug ) : ?>
                                <?php $related_collection = kreativ_get_font_collection_link_by_slug( $related_slug ); ?>
                                <?php if ( empty( $related_collection ) ) { continue; } ?>
                                <a href="<?php echo esc_url( $related_collection['url'] ); ?>">
                                    <?php echo esc_html( $related_collection['title'] ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="kreativ-collection-actions">
                        <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>">Browse all fonts</a>
                        <a href="<?php echo esc_url( home_url( '/collections' ) ); ?>">View all collections</a>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ( $has_main_posts ) : ?>
                <div class="row kreativ-results-grid">
                    <?php foreach ( $main_posts as $collection_post ) : ?>
                        <?php
                        setup_postdata( $collection_post );
                        kreativ_render_font_card(
                            array(
                                'post_id'         => $collection_post->ID,
                                'badge_text'      => $config['badge_text'],
                                'badge_slug'      => $config['badge_slug'],
                                'context_note'    => $config['context_note'],
                                'column_classes'  => 'col-md-4 col-lg-3 col-sm-6',
                                'animation_class' => 'kreativ-card-animate',
                            )
                        );
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php elseif ( ! $has_free_picks ) : ?>
                <div class="kreativ-empty-state">
                    <h2><?php echo esc_html( $config['empty_title'] ); ?></h2>
                    <p><?php echo esc_html( $config['empty_copy'] ); ?></p>
                    <p>
                        <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">Browse Fonts</a>
                        <a href="<?php echo esc_url( add_query_arg( 'font_filter', 'latest', home_url( '/fonts' ) ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-secondary">Explore Latest Fonts</a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ( $has_free_picks ) : ?>
                <section class="kreativ-collection-free-picks" aria-labelledby="kreativ-collection-free-picks-title">
                    <div class="kreativ-collection-free-picks-head">
                        <span class="kreativ-collection-free-picks-label">
                            <i class="fa-solid fa-gift" aria-hidden="true"></i>
                            Free picks
                        </span>
                        <h2 id="kreativ-collection-free-picks-title"><?php echo esc_html( $config['free_picks_title'] ); ?></h2>
                        <p><?php echo esc_html( $config['free_picks_copy'] ); ?></p>
                    </div>

                    <div class="row kreativ-results-grid kreativ-free-results-grid">
                        <?php foreach ( $free_picks_posts as $collection_post ) : ?>
                            <?php
                            setup_postdata( $collection_post );
                            kreativ_render_font_card(
                                array(
                                    'post_id'         => $collection_post->ID,
                                    'badge_text'      => 'Free',
                                    'badge_slug'      => 'free-fonts',
                                    'context_note'    => 'Free option',
                                    'column_classes'  => 'col-md-4 col-lg-3 col-sm-6',
                                    'animation_class' => 'kreativ-card-animate',
                                )
                            );
                            ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </section>
    </div>

    <?php
    get_footer();
}
