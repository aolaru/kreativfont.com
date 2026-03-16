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

    if ( is_singular() && has_excerpt() ) {
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

    <!-- SEO keywords (optional but harmless) -->
    <meta name="keywords" content="kreativ, fonts, templates, graphics, photos, sounds, typography, ai font identifier, creative marketplace, design assets">

    <!-- Canonical URL for SEO -->
    <?php if ( is_singular() ) : ?>
        <link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">
    <?php endif; ?>

    <!-- Open Graph / Twitter Meta -->
    <meta property="og:site_name" content="KREATIV">
    <meta property="og:title" content="<?php echo esc_attr( $document_title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $current_url ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $logo_url ); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( $document_title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $meta_description ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $logo_url ); ?>">

    <!-- PWA: Web App metadata -->
    <meta name="application-name" content="KREATIV">
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

    <!-- Cloudflare Web Analytics -->
    <script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"token": "ab7a9c1b54714400a0112acefa6e4479"}'></script>

    <!-- Affiliate / CJ Script (async for performance) -->
    <script async src="https://www.anrdoezrs.net/am/100743026/include/allCj/generate/onLoad/impressions/page/am.js"></script>

    <!-- Schema.org Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "KREATIV",
      "url": "<?php echo esc_url( $site_url ); ?>",
      "logo": "<?php echo esc_url( $theme_uri . '/img/logo-512.png' ); ?>",
      "sameAs": [
          "https://www.instagram.com/kreativandrei",
          "https://x.com/kreativfont",
          "https://www.facebook.com/kreativfont"
      ]
    }
    </script>
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
								 alt="KREATIV Logo"
								 class="kreativ-logo-icon">
							<span class="kreativ-logo-lockup">
								<span class="kreativ-logo-text">KREATIV</span>
								<span class="kreativ-logo-tagline">Fonts and Font Tools</span>
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
                               class="form-control form-control-sm kreativ-search-input">
                        <button type="submit" class="kreativ-search-submit">Search</button>
                    </form>
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
                </div>

            </div>


			<h2 class="kreativ-logo offcanvas-show">
				<a href="<?php echo esc_url( $site_url ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( $theme_uri . '/img/logo-96.png' ); ?>"
						 alt="KREATIV Logo"
						 class="kreativ-logo-icon">
					<span class="kreativ-logo-lockup">
						<span class="kreativ-logo-text">KREATIV</span>
						<span class="kreativ-logo-tagline">Fonts and Font Tools</span>
					</span>
				</a>
			</h2>

            <button class="navbar-toggler" type="button" data-toggle="offcanvas" aria-label="Toggle navigation">
                Menu
            </button>
        </nav>
    </div>
</header>

<section class="kreativ-content">
