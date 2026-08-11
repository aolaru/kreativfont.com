<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <?php
    $theme_uri = get_template_directory_uri();
    $site_url = home_url( '/' );
    $seo_meta = function_exists( 'kreativ_get_document_meta' ) ? kreativ_get_document_meta() : array(
        'title'       => wp_get_document_title(),
        'description' => 'Discover fonts, compare styles, and use practical font tools for identification, pairing, and naming.',
        'canonical'   => is_singular() ? get_permalink() : '',
        'url'         => home_url( '/' ),
        'image'       => $theme_uri . '/img/logo-512.png',
    );
    ?>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Dynamic meta description -->
    <meta name="description" content="<?php echo esc_attr( $seo_meta['description'] ); ?>">

    <!-- SEO keywords -->
    <meta name="keywords" content="kreativ font, fonts, typography, font tools, font identifier, font pairing, font name generator, curated fonts">

    <!-- Canonical URL for SEO -->
    <?php if ( ! empty( $seo_meta['canonical'] ) ) : ?>
        <link rel="canonical" href="<?php echo esc_url( $seo_meta['canonical'] ); ?>">
    <?php endif; ?>

    <!-- Open Graph / Twitter Meta -->
    <meta property="og:site_name" content="Kreativ Font">
    <meta property="og:title" content="<?php echo esc_attr( $seo_meta['title'] ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $seo_meta['description'] ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $seo_meta['url'] ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $seo_meta['image'] ); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( $seo_meta['title'] ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $seo_meta['description'] ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $seo_meta['image'] ); ?>">

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

    <?php foreach ( function_exists( 'kreativ_get_document_schemas' ) ? kreativ_get_document_schemas() : array() as $schema ) : ?>
        <script type="application/ld+json">
            <?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ); ?>
        </script>
    <?php endforeach; ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sr-only sr-only-focusable" href="#content">Skip to content</a>

<header class="kreativ-header">
    <div class="container">
        <nav class="navbar navbar-expand-lg" aria-label="Primary navigation">
            <div class="navbar-collapse offcanvas-collapse" id="primary-navigation-panel" aria-hidden="false">

                <div class="kreativ-hdr-left">
                    <div class="kreativ-logo">
						<a href="<?php echo esc_url( $site_url ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<img src="<?php echo esc_url( $theme_uri . '/img/logo-96.png' ); ?>"
								 alt="Kreativ Font logo"
                                 width="96"
                                 height="96"
								 class="kreativ-logo-icon">
							<span class="kreativ-logo-lockup">
								<span class="kreativ-logo-text">Kreativ Font</span>
								<span class="kreativ-logo-tagline">Curated Fonts and Tools</span>
							</span>
						</a>
					</div>

	                </div>

                <div class="kreativ-search">
                    <form method="get" id="searchform" class="kreativ-search-form" action="<?php echo esc_url( $site_url ); ?>" role="search" aria-label="Search Kreativ Font">
                        <span class="kreativ-search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchi" type="search" name="s" value="<?php echo get_search_query(); ?>"
                               maxlength="128" placeholder="Search fonts and styles"
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


			<div class="kreativ-logo offcanvas-show">
				<a href="<?php echo esc_url( $site_url ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( $theme_uri . '/img/logo-96.png' ); ?>"
						 alt="Kreativ Font logo"
                         width="96"
                         height="96"
						 class="kreativ-logo-icon">
					<span class="kreativ-logo-lockup">
						<span class="kreativ-logo-text">Kreativ Font</span>
						<span class="kreativ-logo-tagline">Curated Fonts and Tools</span>
					</span>
				</a>
			</div>

            <div class="offcanvas-show kreativ-header-mobile-actions">
                <button type="button" class="kreativ-theme-toggle" aria-label="Toggle dark mode" aria-pressed="false" title="Toggle dark mode">
                    <i class="fa-solid fa-moon" aria-hidden="true"></i>
                </button>

                <button class="navbar-toggler" type="button" data-toggle="offcanvas" aria-label="Open navigation" aria-controls="primary-navigation-panel" aria-expanded="false">
                    Menu
                </button>
            </div>
        </nav>
    </div>
</header>

<main id="content" class="kreativ-content" tabindex="-1">
