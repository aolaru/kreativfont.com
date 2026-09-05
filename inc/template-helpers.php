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
