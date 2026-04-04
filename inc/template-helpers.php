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
    $clauses    = array();
    $child_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', (array) $child_slugs ) ) ) );

    if ( ! empty( $child_slugs ) && kreativ_filter_has_branch_terms( $branch_key, $child_slugs ) ) {
        $clauses[] = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $child_slugs,
        );
    }

    $fallback_tag_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', (array) $fallback_tag_slugs ) ) ) );

    if ( ! empty( $fallback_tag_slugs ) && kreativ_filter_has_terms( 'post_tag', $fallback_tag_slugs ) ) {
        $clauses[] = array(
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $fallback_tag_slugs,
        );
    }

    if ( empty( $clauses ) ) {
        return array();
    }

    if ( 1 === count( $clauses ) ) {
        return $clauses[0];
    }

    return array_merge( array( 'relation' => 'OR' ), $clauses );
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
                AND tt.taxonomy IN ('category', 'post_tag')
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

    $terms        = get_the_terms( $post->ID, 'post_tag' );

    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $term_name = strtolower( $term->name );
            $term_slug = strtolower( $term->slug );

            if ( false !== strpos( $term_name, $normalized_query ) || false !== strpos( $term_slug, $normalized_query ) ) {
                return 'Matched tag';
            }

            foreach ( $search_terms as $search_term ) {
                $search_term = strtolower( $search_term );

                if ( false !== strpos( $term_name, $search_term ) || false !== strpos( $term_slug, $search_term ) ) {
                    return 'Matched tag';
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

    $terms = get_the_terms( $post->ID, 'post_tag' );

    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $style_map as $slug => $label ) {
            foreach ( $terms as $term ) {
                $term_slug = strtolower( (string) $term->slug );
                $term_name = strtolower( (string) $term->name );

                if ( $term_slug === $slug || $term_name === $slug ) {
                    return $label;
                }
            }
        }
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
