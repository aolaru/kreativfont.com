<?php

if ( PHP_SAPI !== 'cli' ) {
    http_response_code( 404 );
    exit;
}

if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'dom' ) ) {
    fwrite( STDERR, "The cURL and DOM PHP extensions are required.\n" );
    exit( 2 );
}

$base_url    = rtrim( $argv[1] ?? 'https://kreativfont.com', '/' );
$cache_token = preg_replace( '/[^a-zA-Z0-9_-]/', '', $argv[2] ?? (string) time() );
$failures    = array();

function kreativ_audit_pass( $message ) {
    echo "[PASS] {$message}\n";
}

function kreativ_audit_fail( $message ) {
    global $failures;

    $failures[] = $message;
    echo "[FAIL] {$message}\n";
}

function kreativ_audit_url( $base_url, $path, $cache_token ) {
    $url       = $base_url . $path;
    $separator = false === strpos( $url, '?' ) ? '?' : '&';

    return $url . $separator . 'audit=' . rawurlencode( $cache_token );
}

function kreativ_audit_fetch( $url ) {
    $last_error = '';

    for ( $attempt = 1; $attempt <= 3; ++$attempt ) {
        $handle = curl_init( $url );
        curl_setopt_array(
            $handle,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 40,
                CURLOPT_USERAGENT      => 'KreativFontProductionAudit/1.0',
                CURLOPT_HTTPHEADER     => array( 'Cache-Control: no-cache' ),
            )
        );

        $body         = curl_exec( $handle );
        $status       = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
        $effective_url = (string) curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
        $last_error    = curl_error( $handle );

        if ( false !== $body && 0 < $status && $status < 500 ) {
            return array(
                'body'          => $body,
                'status'        => $status,
                'effective_url' => $effective_url,
                'error'         => '',
            );
        }

        sleep( $attempt * 2 );
    }

    return array(
        'body'          => '',
        'status'        => 0,
        'effective_url' => $url,
        'error'         => $last_error ?: 'Request failed after three attempts.',
    );
}

function kreativ_audit_fetch_headers( $url ) {
    $handle = curl_init( $url );
    curl_setopt_array(
        $handle,
        array(
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'KreativFontProductionAudit/1.0',
            CURLOPT_HTTPHEADER     => array( 'Cache-Control: no-cache' ),
        )
    );

    curl_exec( $handle );
    $status   = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
    $location = (string) curl_getinfo( $handle, CURLINFO_REDIRECT_URL );
    $error    = curl_error( $handle );
    return array(
        'status'   => $status,
        'location' => $location,
        'error'    => $error,
    );
}

function kreativ_audit_document( $html ) {
    $previous_state = libxml_use_internal_errors( true );
    $document       = new DOMDocument();
    $loaded         = $document->loadHTML( $html, LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();
    libxml_use_internal_errors( $previous_state );

    return $loaded ? array( $document, new DOMXPath( $document ) ) : array( null, null );
}

function kreativ_audit_class_query( $class_name, $suffix = '' ) {
    return '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class_name . ' ")]' . $suffix;
}

function kreativ_audit_text( $node ) {
    return trim( preg_replace( '/\s+/', ' ', $node ? $node->textContent : '' ) );
}

function kreativ_audit_page( $base_url, $cache_token, $spec ) {
    $label    = $spec['label'];
    $response = kreativ_audit_fetch( kreativ_audit_url( $base_url, $spec['path'], $cache_token ) );

    if ( $response['status'] !== $spec['status'] ) {
        kreativ_audit_fail( "{$label}: expected HTTP {$spec['status']}, received {$response['status']} ({$response['error']})" );
        return null;
    }

    kreativ_audit_pass( "{$label}: HTTP {$spec['status']}" );
    list( $document, $xpath ) = kreativ_audit_document( $response['body'] );

    if ( ! $document || ! $xpath ) {
        kreativ_audit_fail( "{$label}: response is not valid HTML" );
        return null;
    }

    $h1_nodes = $xpath->query( '//h1' );
    $h1_text  = 1 === $h1_nodes->length ? kreativ_audit_text( $h1_nodes->item( 0 ) ) : '';

    if ( 1 !== $h1_nodes->length ) {
        kreativ_audit_fail( "{$label}: expected one H1, found {$h1_nodes->length}" );
    } elseif ( ! empty( $spec['h1'] ) && $h1_text !== $spec['h1'] ) {
        kreativ_audit_fail( "{$label}: unexpected H1 '{$h1_text}'" );
    } else {
        kreativ_audit_pass( "{$label}: H1 and document structure" );
    }

    $main_nodes = $xpath->query( '//main' );

    if ( 1 !== $main_nodes->length ) {
        kreativ_audit_fail( "{$label}: expected one main landmark, found {$main_nodes->length}" );
    }

    $title       = kreativ_audit_text( $xpath->query( '//title' )->item( 0 ) );
    $description = trim( (string) $xpath->evaluate( 'string(//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "description"]/@content)' ) );
    $description_nodes = $xpath->query( '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "description"]' );

    if ( '' === $title || '' === $description ) {
        kreativ_audit_fail( "{$label}: missing title or meta description" );
    } else {
        kreativ_audit_pass( "{$label}: title and meta description" );
    }

    if ( ! empty( $spec['single_meta_description'] ) && 1 !== $description_nodes->length ) {
        kreativ_audit_fail( "{$label}: expected one meta description, found {$description_nodes->length}" );
    }

    $ids       = array();
    $duplicate = array();

    foreach ( $xpath->query( '//*[@id and not(ancestor::noscript)]' ) as $node ) {
        $id = $node->getAttribute( 'id' );

        if ( isset( $ids[ $id ] ) ) {
            $duplicate[] = $id;
        }

        $ids[ $id ] = true;
    }

    if ( $duplicate ) {
        kreativ_audit_fail( "{$label}: duplicate IDs: " . implode( ', ', array_unique( $duplicate ) ) );
    }

    $missing_alt = 0;

    foreach ( $xpath->query( '//img' ) as $image ) {
        if ( ! $image->hasAttribute( 'alt' ) ) {
            ++$missing_alt;
        }
    }

    if ( $missing_alt ) {
        kreativ_audit_fail( "{$label}: {$missing_alt} images are missing alt attributes" );
    }

    $canonical_nodes = $xpath->query( '//link[translate(@rel, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "canonical"]' );

    if ( ! empty( $spec['canonical'] ) && 1 !== $canonical_nodes->length ) {
        kreativ_audit_fail( "{$label}: expected one canonical link, found {$canonical_nodes->length}" );
    }

    return array(
        'document' => $document,
        'xpath'    => $xpath,
        'response' => $response,
    );
}

$page_specs = array(
    array( 'label' => 'Home', 'path' => '/', 'status' => 200, 'h1' => 'Curated fonts. Practical tools. Faster decisions.', 'canonical' => true ),
    array( 'label' => 'Fonts', 'path' => '/fonts', 'status' => 200, 'h1' => 'Browse Fonts', 'canonical' => true ),
    array( 'label' => 'Collections', 'path' => '/collections', 'status' => 200, 'h1' => 'Curated Font Collections', 'canonical' => true ),
    array( 'label' => 'Tools', 'path' => '/tools', 'status' => 200, 'h1' => 'Browse Font Tools', 'canonical' => true ),
    array( 'label' => 'Blog', 'path' => '/blog', 'status' => 200, 'h1' => 'Browse Blog', 'canonical' => true ),
    array( 'label' => 'Search', 'path' => '/?s=Inter', 'status' => 200, 'h1' => 'Results for "Inter"', 'canonical' => false ),
    array( 'label' => 'Font post', 'path' => '/fonts/hanley-pro-expressive-script-variety', 'status' => 200, 'h1' => 'Hanley Pro – expressive script variety', 'canonical' => true, 'single_meta_description' => true ),
    array( 'label' => 'Collection route', 'path' => '/collections/best-retro-fonts', 'status' => 200, 'h1' => 'Best Retro Fonts', 'canonical' => true, 'single_meta_description' => true ),
    array( 'label' => 'Tag archive', 'path' => '/tag/handmade-typeface', 'status' => 200, 'canonical' => true ),
    array( 'label' => 'Tool post', 'path' => '/tools/fancy-text-generator', 'status' => 200, 'h1' => 'Kreativ Fancy Text Generator', 'canonical' => true ),
    array( 'label' => 'Contact', 'path' => '/blog/contact', 'status' => 200, 'h1' => 'Contact', 'canonical' => true ),
    array( 'label' => 'Legal', 'path' => '/blog/terms-of-use-privacy-policy', 'status' => 200, 'h1' => 'Terms Of Use & Privacy Policy', 'canonical' => true ),
    array( 'label' => '404', 'path' => '/production-audit-not-found', 'status' => 404, 'h1' => 'Page not found', 'canonical' => false ),
);
$pages      = array();

foreach ( $page_specs as $spec ) {
    $pages[ $spec['label'] ] = kreativ_audit_page( $base_url, $cache_token, $spec );
}

if ( ! empty( $pages['Blog'] ) ) {
    $blog_filters = $pages['Blog']['xpath']->query( kreativ_audit_class_query( 'kreativ-font-filter-bar' ) );

    if ( 0 === $blog_filters->length ) {
        kreativ_audit_pass( 'Blog: font filters are absent' );
    } else {
        kreativ_audit_fail( 'Blog: font filters are still present' );
    }
}

if ( ! empty( $pages['Search'] ) ) {
    $search_xpath = $pages['Search']['xpath'];
    $card_titles  = $search_xpath->query( kreativ_audit_class_query( 'kreativ-font-card', '//*[self::h2 or self::h3]' ) );
    $first_title  = $card_titles->length ? kreativ_audit_text( $card_titles->item( 0 ) ) : '';
    $robots       = $search_xpath->query( '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "robots"]' );
    $robots_text  = $robots->length ? strtolower( $robots->item( 0 )->getAttribute( 'content' ) ) : '';

    if ( false !== strpos( $first_title, 'Inter Font Free Download' ) ) {
        kreativ_audit_pass( 'Search: exact font result ranks first' );
    } else {
        kreativ_audit_fail( "Search: unexpected first result '{$first_title}'" );
    }

    if ( 1 === $robots->length && false !== strpos( $robots_text, 'noindex' ) ) {
        kreativ_audit_pass( 'Search: one noindex robots directive' );
    } else {
        kreativ_audit_fail( "Search: expected one noindex robots directive, found {$robots->length}" );
    }
}

if ( ! empty( $pages['Home'] ) && preg_match( '/var\s+kreativSearchSuggest\s*=\s*(\{.*?\});/s', $pages['Home']['response']['body'], $suggest_config_match ) ) {
    $suggest_config = json_decode( $suggest_config_match[1], true );
    $suggest_url    = $suggest_config['ajaxUrl'] ?? '';
    $suggest_nonce  = $suggest_config['nonce'] ?? '';

    if ( $suggest_url && $suggest_nonce ) {
        $suggest_response = kreativ_audit_fetch(
            $suggest_url . ( false === strpos( $suggest_url, '?' ) ? '?' : '&' ) . http_build_query(
                array(
                    'action' => 'kreativ_search_suggest',
                    'q'      => 'Inter',
                    'nonce'  => $suggest_nonce,
                )
            )
        );
        $suggest_data     = json_decode( $suggest_response['body'], true );
        $font_suggestions = $suggest_data['data']['groups']['fonts'] ?? array();
        $first_suggestion = $font_suggestions[0]['label'] ?? '';
        $font_urls_valid  = ! empty( $font_suggestions );

        foreach ( $font_suggestions as $suggestion ) {
            if ( 0 !== strpos( $suggestion['url'] ?? '', $base_url . '/fonts/' ) ) {
                $font_urls_valid = false;
                break;
            }
        }

        if ( 200 === $suggest_response['status'] && false !== strpos( $first_suggestion, 'Inter Font Free Download' ) && $font_urls_valid ) {
            kreativ_audit_pass( 'Autocomplete: exact result first and Fonts group contains only fonts' );
        } else {
            kreativ_audit_fail( 'Autocomplete: relevance or content-type checks failed' );
        }
    } else {
        kreativ_audit_fail( 'Autocomplete: localized endpoint configuration is incomplete' );
    }
} else {
    kreativ_audit_fail( 'Autocomplete: localized endpoint configuration was not found' );
}

foreach ( array( 'Tool post' => 'Font tool', 'Contact' => 'Information', 'Legal' => 'Information' ) as $label => $expected_eyebrow ) {
    if ( empty( $pages[ $label ] ) ) {
        continue;
    }

    $xpath       = $pages[ $label ]['xpath'];
    $eyebrows    = $xpath->query( kreativ_audit_class_query( 'kreativ-page-eyebrow' ) );
    $eyebrow     = $eyebrows->length ? kreativ_audit_text( $eyebrows->item( 0 ) ) : '';
    $post_nav    = $xpath->query( kreativ_audit_class_query( 'kreativ-post-nav' ) );
    $font_crumbs = $xpath->query( kreativ_audit_class_query( 'kreativ-post-breadcrumb', '//a[normalize-space(.) = "Fonts"]' ) );

    if ( $expected_eyebrow === $eyebrow && 0 === $post_nav->length && 0 === $font_crumbs->length ) {
        kreativ_audit_pass( "{$label}: non-font template routing" );
    } else {
        kreativ_audit_fail( "{$label}: font-specific template elements remain" );
    }
}

if ( ! empty( $pages['404'] ) ) {
    $robots      = $pages['404']['xpath']->query( '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "robots"]' );
    $robots_text = $robots->length ? strtolower( $robots->item( 0 )->getAttribute( 'content' ) ) : '';

    if ( 1 === $robots->length && false !== strpos( $robots_text, 'noindex' ) ) {
        kreativ_audit_pass( '404: one noindex robots directive' );
    } else {
        kreativ_audit_fail( "404: expected one noindex robots directive, found {$robots->length}" );
    }
}

if ( ! empty( $pages['Tag archive'] ) ) {
    $robots      = $pages['Tag archive']['xpath']->query( '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "robots"]' );
    $robots_text = $robots->length ? strtolower( $robots->item( 0 )->getAttribute( 'content' ) ) : '';

    if ( 1 === $robots->length && false !== strpos( $robots_text, 'noindex' ) ) {
        kreativ_audit_pass( 'Tag archive: one noindex robots directive' );
    } else {
        kreativ_audit_fail( "Tag archive: expected one noindex robots directive, found {$robots->length}" );
    }
}

$robots_response = kreativ_audit_fetch( kreativ_audit_url( $base_url, '/robots.txt', $cache_token ) );
$expected_sitemap = 'Sitemap: ' . $base_url . '/sitemap.xml';

if ( 200 === $robots_response['status'] && false !== strpos( $robots_response['body'], $expected_sitemap ) && false === strpos( $robots_response['body'], 'kreativsound.com' ) ) {
    kreativ_audit_pass( 'robots.txt: correct Kreativ Font sitemap' );
} else {
    kreativ_audit_fail( 'robots.txt: missing the correct Kreativ Font sitemap' );
}

$sitemap_response = kreativ_audit_fetch( kreativ_audit_url( $base_url, '/sitemap.xml', $cache_token ) );

if ( 200 === $sitemap_response['status'] && preg_match( '/<(?:sitemapindex|urlset)\b/i', $sitemap_response['body'] ) ) {
    kreativ_audit_pass( 'Sitemap: reachable XML index' );
} else {
    kreativ_audit_fail( 'Sitemap: unavailable or invalid' );
}

if ( false === strpos( $sitemap_response['body'], 'post_tag-sitemap' ) ) {
    kreativ_audit_pass( 'Sitemap: tag archives are excluded' );
} else {
    kreativ_audit_fail( 'Sitemap: tag archives are still included' );
}

$share_redirect = kreativ_audit_fetch_headers( $base_url . '/fonts/nevo?share=twitter' );

if ( 301 === $share_redirect['status'] && $base_url . '/fonts/nevo' === $share_redirect['location'] ) {
    kreativ_audit_pass( 'Legacy share URL: redirects to the canonical font page' );
} else {
    kreativ_audit_fail( "Legacy share URL: expected a 301 to the canonical font page, received {$share_redirect['status']} ({$share_redirect['location']})" );
}

$asset_response = kreativ_audit_fetch( kreativ_audit_url( $base_url, '/wp-content/themes/kreativfont.com/assets/assets/components/init.js', $cache_token ) );

if ( 200 === $asset_response['status'] && false !== strpos( $asset_response['body'], 'initialiseNavigation' ) && false !== strpos( $asset_response['body'], "'Escape'" ) && false === strpos( $asset_response['body'], 'jQuery' ) ) {
    kreativ_audit_pass( 'Theme asset: vanilla navigation and accessibility checks deployed' );
} else {
    kreativ_audit_fail( 'Theme asset: expected deployment markers are missing' );
}

echo "\n";

if ( $failures ) {
    echo count( $failures ) . " production audit check(s) failed.\n";
    exit( 1 );
}

echo "Production audit passed.\n";
