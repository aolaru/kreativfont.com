<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Dynamic meta description -->
    <meta name="description" content="<?php
        if ( is_singular() && has_excerpt() ) {
            echo esc_attr( wp_strip_all_tags( get_the_excerpt() ) );
        } elseif ( is_singular() ) {
            echo esc_attr( wp_trim_words( wp_strip_all_tags( get_the_content() ), 25, '…' ) );
        } else {
            echo esc_attr( 'Discover, identify, and submit fonts, templates, and creatives with KREATIV — your home for modern typography and design.' );
        }
    ?>">

    <!-- SEO keywords (optional but harmless) -->
    <meta name="keywords" content="kreativ, fonts, templates, graphics, photos, sounds, typography, ai font identifier, creative marketplace, design assets">

    <!-- Canonical URL for SEO -->
    <?php if ( is_singular() ) : ?>
        <link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">
    <?php endif; ?>

    <!-- Open Graph / Twitter Meta -->
    <?php 
    // Use featured image if available, fallback to logo
    $og_image = get_template_directory_uri() . '/img/logo-512.png';
    if ( is_singular() && has_post_thumbnail() ) {
        $og_image = get_the_post_thumbnail_url( null, 'large' );
    }
    ?>
    <meta property="og:site_name" content="KREATIV">
    <meta property="og:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( get_bloginfo('description') ); ?>">
    <meta property="og:url" content="<?php echo esc_url( home_url( $_SERVER['REQUEST_URI'] ) ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( get_bloginfo('description') ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">

    <!-- PWA: Web App metadata -->
    <meta name="application-name" content="KREATIV">
    <meta name="theme-color" content="#ffffff">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/img/favicon-96x96.png" type="image/png">

    <!-- Manifest -->
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">

    <!-- PWA: Apple iOS Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="https://kreativfont.com/wp-content/uploads/pwa/icon-192.png">

    <!-- Safari pinned tab -->
    <link rel="mask-icon" href="https://kreativfont.com/wp-content/uploads/pwa/icon-512-maskable.png" color="#000000">

    <!-- Font Preload -->
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/webfonts/2EE639_0_0.woff2" as="font" type="font/woff2" crossorigin>

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
      "url": "<?php echo esc_url( home_url( '/' ) ); ?>",
      "logo": "<?php echo get_template_directory_uri(); ?>/img/logo-512.png",
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
						<a href="<?php echo esc_url( home_url('/') ); ?>" title="<?php bloginfo('description'); ?>">
							<img src="https://kreativfont.com/wp-content/uploads/pwa/icon-96.png"
								 alt="KREATIV Logo"
								 class="kreativ-logo-icon">
							<span class="kreativ-logo-text">KREATIV</span>
						</a>
					</h1>

                </div>				

                <div class="kreativ-search">
                    <form method="get" id="searchform" action="<?php echo esc_url( home_url('/') ); ?>">
                        <input id="searchi" type="search" name="s" value="<?php echo get_search_query(); ?>"
                               maxlength="128" placeholder="Type your search and press enter"
                               class="form-control form-control-sm">
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
				<a href="<?php echo esc_url( home_url('/') ); ?>" title="<?php bloginfo('description'); ?>">
					<img src="https://kreativfont.com/wp-content/uploads/pwa/icon-96.png"
						 alt="KREATIV Logo"
						 class="kreativ-logo-icon">
					<span class="kreativ-logo-text">KREATIV</span>
				</a>
			</h2>

            <button class="navbar-toggler" type="button" data-toggle="offcanvas">
                Menu
            </button>
        </nav>
    </div>
</header>

<section class="kreativ-content">
