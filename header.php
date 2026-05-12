<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <?php
    $theme_uri = get_template_directory_uri();
    $site_url = home_url( '/' );
    $current_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $current_url = home_url( $current_request_uri );
    $document_title = wp_get_document_title();
    $logo_url = $theme_uri . '/img/logo-512.png';

    if ( is_singular() && has_post_thumbnail() ) {
        $logo_url = get_the_post_thumbnail_url( null, 'large' );
    }

    if ( ! empty( $GLOBALS['kreativ_meta_description_override'] ) ) {
        $meta_description = wp_strip_all_tags( $GLOBALS['kreativ_meta_description_override'] );
    } elseif ( is_singular() && has_excerpt() ) {
        $meta_description = wp_strip_all_tags( get_the_excerpt() );
    } elseif ( is_singular() ) {
        $meta_description = wp_trim_words(
            wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ),
            25,
            '...'
        );
    } else {
        $meta_description = 'Discover fonts, compare styles, and use practical font tools for identification, pairing, and naming.';
    }
    ?>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Dynamic meta description -->
    <meta name="description" content="<?php echo esc_attr( $meta_description ); ?>">

    <?php if ( is_search() || is_page_template( 'template-filter-market.php' ) ) : ?>
        <meta name="robots" content="noindex,follow">
    <?php endif; ?>

    <!-- SEO keywords -->
    <meta name="keywords" content="kreativ font, fonts, typography, font tools, font identifier, font pairing, font name generator, curated fonts">

    <!-- Canonical URL for SEO -->
    <?php if ( is_singular() ) : ?>
        <link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">
    <?php endif; ?>

    <!-- Open Graph / Twitter Meta -->
    <meta property="og:site_name" content="Kreativ Font">
    <meta property="og:title" content="<?php echo esc_attr( $document_title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $current_url ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $logo_url ); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( $document_title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $meta_description ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $logo_url ); ?>">

    <!-- PWA: Web App metadata -->
    <meta name="application-name" content="Kreativ Font">
    <meta name="theme-color" content="#ffffff">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo esc_url( $theme_uri . '/img/favicon.svg' ); ?>" type="image/svg+xml">
    <link rel="icon" sizes="32x32" href="<?php echo esc_url( $theme_uri . '/img/favicon-32x32.png' ); ?>" type="image/png">
    <link rel="icon" sizes="16x16" href="<?php echo esc_url( $theme_uri . '/img/favicon-16x16.png' ); ?>" type="image/png">

    <!-- Manifest -->
    <link rel="manifest" href="<?php echo esc_url( $theme_uri . '/manifest.json' ); ?>">

    <!-- PWA: Apple iOS Support -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="<?php echo esc_url( $theme_uri . '/img/apple-touch-icon.png' ); ?>">

    <?php wp_head(); ?>

	    <!-- Google Analytics -->
	    <script async src="https://www.googletagmanager.com/gtag/js?id=G-4E74M6PB1Y"></script>
	    <script>
	        window.dataLayer = window.dataLayer || [];
	        function gtag(){dataLayer.push(arguments);}
	        gtag('js', new Date());
	        gtag('config', 'G-4E74M6PB1Y');
	    </script>

	    <!-- Google AdSense -->
	    <meta name="google-adsense-account" content="ca-pub-4706844277814411">
	    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4706844277814411" crossorigin="anonymous"></script>

	    <!-- Affiliate / CJ Script (async for performance) -->
	    <script async src="https://www.anrdoezrs.net/am/100743026/include/allCj/generate/onLoad/impressions/page/am.js"></script>

    <!-- Schema.org Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Kreativ Font",
      "url": "<?php echo esc_url( $site_url ); ?>",
      "logo": "<?php echo esc_url( $theme_uri . '/img/logo-512.png' ); ?>",
      "sameAs": [
          "https://www.instagram.com/kreativandrei",
          "https://x.com/kreativfont",
          "https://www.facebook.com/kreativfont"
      ]
    }
    </script>

    <?php if ( ! empty( $GLOBALS['kreativ_collection_page_schema'] ) && is_array( $GLOBALS['kreativ_collection_page_schema'] ) ) : ?>
        <script type="application/ld+json">
            <?php echo wp_json_encode( $GLOBALS['kreativ_collection_page_schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ); ?>
        </script>
    <?php endif; ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="kreativ-header">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
	            <div class="navbar-collapse offcanvas-collapse">

                <div class="kreativ-hdr-left">
                    <h1 class="kreativ-logo">
						<a href="<?php echo esc_url( $site_url ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<img src="<?php echo esc_url( $theme_uri . '/img/logo-96.png' ); ?>"
								 alt="Kreativ Font logo"
								 class="kreativ-logo-icon">
							<span class="kreativ-logo-lockup">
								<span class="kreativ-logo-text">Kreativ Font</span>
								<span class="kreativ-logo-tagline">Curated Fonts and Tools</span>
							</span>
						</a>
					</h1>

	                </div>

                <div class="kreativ-search">
                    <form method="get" id="searchform" class="kreativ-search-form" action="<?php echo esc_url( $site_url ); ?>" role="search" aria-label="Search Kreativ Font">
                        <span class="kreativ-search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchi" type="search" name="s" value="<?php echo get_search_query(); ?>"
                               maxlength="128" placeholder="Search fonts, styles, foundries, or tools"
                               aria-label="Search Kreativ Font"
                               autocomplete="off"
                               class="form-control form-control-sm kreativ-search-input">
                        <button type="submit" class="kreativ-search-submit">Search</button>
                    </form>
                    <div class="kreativ-search-suggestions" hidden aria-live="polite"></div>
                </div>

                <div class="kreativ-hdr-right">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'navbar-nav btn',
                        'depth' => 1,
                    ));
                    ?>

                    <button type="button" class="kreativ-theme-toggle d-none d-lg-inline-flex" aria-label="Toggle dark mode" aria-pressed="false" title="Toggle dark mode">
                        <i class="fa-solid fa-moon" aria-hidden="true"></i>
                    </button>
                </div>

            </div>


			<h2 class="kreativ-logo offcanvas-show">
				<a href="<?php echo esc_url( $site_url ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( $theme_uri . '/img/logo-96.png' ); ?>"
						 alt="Kreativ Font logo"
						 class="kreativ-logo-icon">
					<span class="kreativ-logo-lockup">
						<span class="kreativ-logo-text">Kreativ Font</span>
						<span class="kreativ-logo-tagline">Curated Fonts and Tools</span>
					</span>
				</a>
			</h2>

            <div class="offcanvas-show kreativ-header-mobile-actions">
                <button type="button" class="kreativ-theme-toggle" aria-label="Toggle dark mode" aria-pressed="false" title="Toggle dark mode">
                    <i class="fa-solid fa-moon" aria-hidden="true"></i>
                </button>

                <button class="navbar-toggler" type="button" data-toggle="offcanvas" aria-label="Toggle navigation">
                    Menu
                </button>
            </div>
        </nav>
    </div>
</header>

<section class="kreativ-content">
