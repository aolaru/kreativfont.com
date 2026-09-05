<?php

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

    foreach ( array_keys( $scores ) as $post_id ) {
        if ( ! kreativ_is_font_post( $post_id ) ) {
            unset( $scores[ $post_id ] );
            unset( $sort_hints[ $post_id ] );
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
