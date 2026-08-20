<?php

function kreativ_get_current_url() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

    return home_url( $request_uri );
}

function kreativ_get_default_meta_description() {
    return 'Discover fonts, compare styles, and use practical font tools for identification, pairing, and naming.';
}

function kreativ_clean_meta_description_text( $text, $max_words = 30 ) {
    $text = html_entity_decode( (string) $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
    $text = strip_shortcodes( $text );
    $text = preg_replace( '/\[[\/]?[a-zA-Z][a-zA-Z0-9_-]*(?:\s+[^\]]*)?\]/', ' ', $text );
    $text = wp_strip_all_tags( $text );
    $text = preg_replace( '/\s+/', ' ', trim( $text ) );
    $text = trim( $text, " \t\n\r\0\x0B-|" );

    if ( '' === $text || strlen( $text ) < 35 ) {
        return '';
    }

    if ( preg_match( '/^(version|changelog|major upgrade|note:\s*accepted file types)/i', $text ) || preg_match( '/\b(version|changelog)\s+\d+(?:\.\d+)*/i', $text ) ) {
        return '';
    }

    return wp_trim_words( $text, (int) $max_words, '...' );
}

function kreativ_get_singular_meta_description_fallback( $post = null ) {
    $post = get_post( $post );

    if ( ! $post instanceof WP_Post ) {
        return '';
    }

    if ( is_front_page() ) {
        return 'Browse curated fonts, focused font collections, and practical tools for identifying, pairing, and naming type.';
    }

    $fallbacks = array(
        'collections'                         => 'Browse curated font collections by style, mood, project type, and commercial-use needs on Kreativ Font.',
        'updates'                             => 'Follow Kreativ Font product updates across font browsing, search, tools, mobile UX, and single-page improvements.',
        'contact'                             => 'Contact Kreativ Font with questions about typography, font resources, tools, licensing notes, or published content.',
        'about'                               => 'Learn about Kreativ Font, an independent typography resource for curated fonts, practical font tools, and design inspiration.',
        'terms-of-use-privacy-policy'         => 'Read the Kreativ Font terms of use, privacy policy, cookie notes, affiliate disclosure, and data-control information.',
        'kreativ-font-identifier'             => 'Upload a font image and compare it with trusted font identification services and Kreativ Font resources.',
        'kreativ-font-pairing-tools'          => 'Generate font pairing ideas from a base font and design context, then continue into related font collections.',
        'kreativ-font-name-generator'         => 'Describe a type style and generate brandable font-family name ideas for creative projects.',
        'fancy-text-generator'                => 'Convert plain text into decorative Unicode text styles and aesthetic presets directly in the browser.',
    );

    if ( isset( $fallbacks[ $post->post_name ] ) ) {
        return $fallbacks[ $post->post_name ];
    }

    if ( has_category( 'tools', $post ) ) {
        return sprintf(
            '%s is a practical Kreativ Font tool for faster typography decisions and font discovery.',
            get_the_title( $post )
        );
    }

    return '';
}

function kreativ_get_content_meta_description( $content ) {
    $content = strip_shortcodes( (string) $content );
    $content = preg_replace( '/\[[\/]?[a-zA-Z][a-zA-Z0-9_-]*(?:\s+[^\]]*)?\]/', ' ', $content );
    $blocks  = preg_split( '/<\/p>|<br\s*\/?>|\n{2,}/i', $content );

    foreach ( (array) $blocks as $block ) {
        $description = kreativ_clean_meta_description_text( $block );

        if ( '' !== $description ) {
            return $description;
        }
    }

    return kreativ_clean_meta_description_text( $content );
}

function kreativ_get_meta_description() {
    if ( ! empty( $GLOBALS['kreativ_meta_description_override'] ) ) {
        $override_description = kreativ_clean_meta_description_text( $GLOBALS['kreativ_meta_description_override'] );

        if ( '' !== $override_description ) {
            return $override_description;
        }
    }

    if ( is_front_page() ) {
        $front_page_description = kreativ_get_singular_meta_description_fallback( get_queried_object_id() );

        return '' !== $front_page_description ? $front_page_description : kreativ_get_default_meta_description();
    }

    $singular_post = is_singular() ? get_post( get_queried_object_id() ) : null;

    if ( $singular_post instanceof WP_Post ) {
        $fallback_description = kreativ_get_singular_meta_description_fallback( $singular_post );

        if ( '' !== $fallback_description ) {
            return $fallback_description;
        }
    }

    if ( is_singular() && has_excerpt() ) {
        $excerpt_description = kreativ_clean_meta_description_text( get_the_excerpt() );

        if ( '' !== $excerpt_description ) {
            return $excerpt_description;
        }
    }

    if ( is_singular() ) {
        $post = $singular_post;

        if ( $post instanceof WP_Post ) {
            $content_description = kreativ_get_content_meta_description( get_post_field( 'post_content', $post ) );

            if ( '' !== $content_description ) {
                return $content_description;
            }
        }

        return kreativ_get_default_meta_description();
    }

    if ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();

        if ( $term instanceof WP_Term ) {
            $term_description = wp_strip_all_tags( term_description( $term->term_id ) );

            if ( '' !== trim( $term_description ) ) {
                return wp_trim_words( $term_description, 28, '...' );
            }

            if ( is_tag() ) {
                return sprintf( 'Browse secondary font tag results for %s on Kreativ Font.', $term->name );
            }

            return sprintf( 'Browse fonts and font resources filed under %s on Kreativ Font.', $term->name );
        }
    }

    if ( is_search() ) {
        return sprintf( 'Search Kreativ Font for %s across font titles, designers, foundries, styles, moods, and use cases.', get_search_query() );
    }

    if ( is_404() ) {
        return 'Search the Kreativ Font library or jump back into focused font collections and browsing paths.';
    }

    return kreativ_get_default_meta_description();
}

function kreativ_get_canonical_url() {
    if ( ! empty( $GLOBALS['kreativ_virtual_collection_url'] ) ) {
        return $GLOBALS['kreativ_virtual_collection_url'];
    }

    if ( is_singular() ) {
        return get_permalink();
    }

    if ( is_category() || is_tag() || is_tax() ) {
        $term_link = get_term_link( get_queried_object() );

        return is_wp_error( $term_link ) ? '' : $term_link;
    }

    if ( is_front_page() ) {
        return home_url( '/' );
    }

    return '';
}

function kreativ_get_virtual_collection_document_title( $title ) {
    if ( empty( $GLOBALS['kreativ_virtual_collection_title'] ) ) {
        return $title;
    }

    return sprintf( '%s | %s', $GLOBALS['kreativ_virtual_collection_title'], get_bloginfo( 'name' ) );
}
add_filter( 'pre_get_document_title', 'kreativ_get_virtual_collection_document_title' );

function kreativ_is_legacy_template() {
    return is_page_template( array(
        'template-filter-market.php',
        'template-popular.php',
        'template-sitemap.php',
    ) );
}

function kreativ_request_has_query_parameters() {
    return ! empty( $_GET );
}

function kreativ_is_amp_endpoint_request() {
    if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
        return true;
    }

    $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

    return is_singular() && is_string( $request_path ) && (bool) preg_match( '#/amp/?$#', $request_path );
}

function kreativ_redirect_amp_endpoints_to_canonical() {
    if ( is_admin() || wp_doing_ajax() || ! kreativ_is_amp_endpoint_request() ) {
        return;
    }

    $canonical_url = get_permalink( get_queried_object_id() );

    if ( $canonical_url ) {
        wp_safe_redirect( $canonical_url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'kreativ_redirect_amp_endpoints_to_canonical', 1 );

function kreativ_redirect_legacy_share_links_to_canonical() {
    if ( is_admin() || wp_doing_ajax() || empty( $_GET['share'] ) || ! is_singular() ) {
        return;
    }

    $canonical_url = get_permalink( get_queried_object_id() );

    if ( $canonical_url ) {
        wp_safe_redirect( $canonical_url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'kreativ_redirect_legacy_share_links_to_canonical', 0 );

function kreativ_filter_wp_robots( $robots ) {
    $noindex_archive = is_tag() || ( is_archive() && is_paged() );

    if ( is_search() || is_404() || kreativ_is_legacy_template() || $noindex_archive || kreativ_request_has_query_parameters() ) {
        unset( $robots['index'], $robots['nofollow'] );
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    return $robots;
}
add_filter( 'wp_robots', 'kreativ_filter_wp_robots' );

function kreativ_add_noindex_header_to_feeds() {
    if ( is_feed() && ! headers_sent() ) {
        header( 'X-Robots-Tag: noindex, follow', true );
    }
}
add_action( 'send_headers', 'kreativ_add_noindex_header_to_feeds' );

function kreativ_exclude_low_value_taxonomies_from_sitemap( $taxonomies ) {
    $taxonomies   = is_array( $taxonomies ) ? $taxonomies : array();
    $taxonomies[] = 'post_tag';

    return array_values( array_unique( $taxonomies ) );
}
add_filter( 'sm_sitemap_exclude_taxonomy', 'kreativ_exclude_low_value_taxonomies_from_sitemap' );

function kreativ_get_open_graph_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        return get_the_post_thumbnail_url( null, 'large' );
    }

    return get_template_directory_uri() . '/img/logo-512.png';
}

function kreativ_get_document_meta() {
    return array(
        'title'       => wp_get_document_title(),
        'description' => kreativ_get_meta_description(),
        'canonical'   => kreativ_get_canonical_url(),
        'url'         => kreativ_get_current_url(),
        'image'       => kreativ_get_open_graph_image(),
    );
}

function kreativ_get_organization_schema() {
    $theme_uri = get_template_directory_uri();

    return array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'Kreativ Font',
        'url'      => home_url( '/' ),
        'logo'     => $theme_uri . '/img/logo-512.png',
        'sameAs'   => array(
            'https://www.instagram.com/kreativandrei',
            'https://x.com/kreativfont',
            'https://www.facebook.com/kreativfont',
        ),
    );
}

function kreativ_get_document_schemas() {
    $schemas = array( kreativ_get_organization_schema() );

    if ( ! empty( $GLOBALS['kreativ_collection_page_schema'] ) && is_array( $GLOBALS['kreativ_collection_page_schema'] ) ) {
        $schemas[] = $GLOBALS['kreativ_collection_page_schema'];
    }

    return $schemas;
}
