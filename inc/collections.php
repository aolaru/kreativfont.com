<?php

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

function kreativ_serve_virtual_font_collection_pages( $template ) {
    if ( is_admin() || ! is_404() || empty( $_SERVER['REQUEST_URI'] ) ) {
        return $template;
    }

    $request_path = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
    $request_path = trim( (string) $request_path, '/' );

    if ( ! preg_match( '#^collections/([^/]+)$#', $request_path, $matches ) ) {
        return $template;
    }

    $collection = kreativ_get_font_collection_link_by_slug( $matches[1] );
    $template_path = get_template_directory() . '/page-' . sanitize_file_name( $matches[1] ) . '.php';

    if ( empty( $collection ) || ! file_exists( $template_path ) ) {
        return $template;
    }

    global $wp_query;

    $wp_query->is_404 = false;
    $GLOBALS['kreativ_virtual_collection_url'] = $collection['url'];
    $GLOBALS['kreativ_virtual_collection_title'] = $collection['title'];
    status_header( 200 );

    return $template_path;
}
add_filter( 'template_include', 'kreativ_serve_virtual_font_collection_pages', 99 );


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

    $font_exclusion_clause = kreativ_get_font_exclusion_tax_clause();

    if ( ! empty( $font_exclusion_clause ) ) {
        $tax_query[] = $font_exclusion_clause;
    }

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

        if ( ! kreativ_is_font_post( $post ) ) {
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
        'guide_sections' => array(),
        'faq_items'      => array(),
        'faq_title'      => 'Frequently asked questions',
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

    $GLOBALS['kreativ_collection_page_extra_schemas'] = array();

    if ( ! empty( $config['faq_items'] ) && is_array( $config['faq_items'] ) ) {
        $faq_entities = array();

        foreach ( $config['faq_items'] as $faq_item ) {
            $question = trim( (string) ( $faq_item['question'] ?? '' ) );
            $answer   = trim( (string) ( $faq_item['answer'] ?? '' ) );

            if ( '' === $question || '' === $answer ) {
                continue;
            }

            $faq_entities[] = array(
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags( $answer ),
                ),
            );
        }

        if ( ! empty( $faq_entities ) ) {
            $GLOBALS['kreativ_collection_page_extra_schemas'][] = array(
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $faq_entities,
            );
        }
    }

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

            <?php if ( ! empty( $config['guide_sections'] ) && is_array( $config['guide_sections'] ) ) : ?>
                <div class="kreativ-collection-guide" aria-label="Collection guide">
                    <?php foreach ( $config['guide_sections'] as $guide_section ) : ?>
                        <?php
                        $guide_title      = trim( (string) ( $guide_section['title'] ?? '' ) );
                        $guide_paragraphs = ! empty( $guide_section['paragraphs'] ) && is_array( $guide_section['paragraphs'] ) ? $guide_section['paragraphs'] : array();
                        $guide_points     = ! empty( $guide_section['points'] ) && is_array( $guide_section['points'] ) ? $guide_section['points'] : array();

                        if ( '' === $guide_title && empty( $guide_paragraphs ) && empty( $guide_points ) ) {
                            continue;
                        }
                        ?>
                        <section class="kreativ-collection-guide-section">
                            <?php if ( '' !== $guide_title ) : ?>
                                <h2><?php echo esc_html( $guide_title ); ?></h2>
                            <?php endif; ?>

                            <?php foreach ( $guide_paragraphs as $guide_paragraph ) : ?>
                                <?php if ( '' !== trim( (string) $guide_paragraph ) ) : ?>
                                    <p><?php echo esc_html( $guide_paragraph ); ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if ( ! empty( $guide_points ) ) : ?>
                                <ul>
                                    <?php foreach ( $guide_points as $guide_point ) : ?>
                                        <?php if ( '' !== trim( (string) $guide_point ) ) : ?>
                                            <li><?php echo esc_html( $guide_point ); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
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

            <?php if ( ! empty( $config['faq_items'] ) && is_array( $config['faq_items'] ) ) : ?>
                <section class="kreativ-collection-faq" aria-labelledby="kreativ-collection-faq-title">
                    <h2 id="kreativ-collection-faq-title"><?php echo esc_html( $config['faq_title'] ); ?></h2>

                    <div class="kreativ-collection-faq-list">
                        <?php foreach ( $config['faq_items'] as $faq_item ) : ?>
                            <?php
                            $question = trim( (string) ( $faq_item['question'] ?? '' ) );
                            $answer   = trim( (string) ( $faq_item['answer'] ?? '' ) );

                            if ( '' === $question || '' === $answer ) {
                                continue;
                            }
                            ?>
                            <details>
                                <summary><?php echo esc_html( $question ); ?></summary>
                                <p><?php echo esc_html( $answer ); ?></p>
                            </details>
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
