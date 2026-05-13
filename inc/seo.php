<?php

function kreativ_get_current_url() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

    return home_url( $request_uri );
}

function kreativ_get_default_meta_description() {
    return 'Discover fonts, compare styles, and use practical font tools for identification, pairing, and naming.';
}

function kreativ_get_meta_description() {
    if ( ! empty( $GLOBALS['kreativ_meta_description_override'] ) ) {
        return wp_strip_all_tags( $GLOBALS['kreativ_meta_description_override'] );
    }

    if ( is_singular() && has_excerpt() ) {
        return wp_strip_all_tags( get_the_excerpt() );
    }

    if ( is_singular() ) {
        return wp_trim_words(
            wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ),
            25,
            '...'
        );
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

function kreativ_is_legacy_template() {
    return is_page_template( array(
        'template-filter-market.php',
        'template-popular.php',
        'template-sitemap.php',
    ) );
}

function kreativ_get_robots_content() {
    if ( is_search() || kreativ_is_legacy_template() ) {
        return 'noindex,follow';
    }

    return '';
}

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
        'robots'      => kreativ_get_robots_content(),
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
