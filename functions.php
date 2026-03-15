<?php
function kreativ_font_setup() {

	//Enable Custom Menus
    add_theme_support('menus');

	if (function_exists('register_nav_menu')) {
		register_nav_menu('primary', 'Primary Menu');
	}

	//Enable Post Thumbnails
	add_theme_support('post-thumbnails');

	//Enable Theme Titles
	add_theme_support('title-tag');

}

add_action( 'after_setup_theme', 'kreativ_font_setup' );

function kreativ_asset_version( $relative_path ) {
	$absolute_path = get_template_directory() . $relative_path;

	if ( file_exists( $absolute_path ) ) {
		return (string) filemtime( $absolute_path );
	}

	return null;
}

function kreativ_jquery_loading() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js', array(), null, true);
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'kreativ_jquery_loading');

function kreativ_enqueue_lazyload() {
    wp_enqueue_script(
        'lazysizes',
        'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js',
        array(),
        '5.3.2',
        true
    );
}
add_action('wp_enqueue_scripts', 'kreativ_enqueue_lazyload');


function kreativ_script_enqueue()
{
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );
    wp_enqueue_style(
        'kreativ-styles',
        get_template_directory_uri() . '/assets/dist/main.min.css',
        array( 'font-awesome' ),
        kreativ_asset_version( '/assets/dist/main.min.css' )
    );
    wp_enqueue_style(
        'kreativ-header',
        get_template_directory_uri() . '/css/kreativ-header.css',
        array( 'kreativ-styles' ),
        kreativ_asset_version( '/css/kreativ-header.css' )
    );
    wp_register_script(
        'init',
        get_template_directory_uri() . '/assets/assets/components/init.js',
        array('jquery'),
        kreativ_asset_version( '/assets/assets/components/init.js' ),
        true
    );
    wp_enqueue_script('init');
	if ( is_singular('post') || is_front_page() || is_tag() || is_category() ) {
		wp_dequeue_style( 'edd-styles' );
		wp_dequeue_script( 'edd-ajax' );
	}
	wp_deregister_script('wp-embed');
}
add_action('wp_enqueue_scripts', 'kreativ_script_enqueue');


function remove_comments_rss($for_comments)
{
    return;
}

add_filter('post_comments_feed_link', 'remove_comments_rss');

remove_action('wp_head', 'wp_generator');

remove_action('wp_head', 'wlwmanifest_link');

remove_action('wp_head', 'rsd_link');

remove_action('wp_head', 'print_emoji_detection_script', 7);

remove_action('wp_print_styles', 'print_emoji_styles');

function my_login_head()
{
    echo "
	<style>
	body.login #login h1 a {
		background: url('//kreativfont.com/wp-content/uploads/2019/08/Kreativ-Font-logo-128.png') no-repeat scroll center top transparent;
		height: 128px;
		width: 128px;
	}
	</style>
	";
}

add_action("login_head", "my_login_head");

//change login form URL
function my_login_logo_url()
{
    return home_url();
}

add_filter('login_headerurl', 'my_login_logo_url');

//change login form info
function my_login_logo_url_title()
{
    return get_bloginfo('name');
}

add_theme_support( 'title-tag' );


add_filter('login_headertitle', 'my_login_logo_url_title');

// marketplace slug menu
if ( ! defined( 'EDD_SLUG' ) ) {
	define( 'EDD_SLUG', 'market' );
}

if ( ! isset( $content_width ) ) $content_width = 900;

add_action('admin_init', function() {
    if (isset($_GET['convert_showcases'])) {
        $args = [
            'post_type' => 'kreativ_showcase',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ];
        $query = new WP_Query($args);
        $count = 0;
        foreach ($query->posts as $post) {
            wp_update_post([
                'ID' => $post->ID,
                'post_type' => 'post'
            ]);
            $count++;
        }
        echo "<div class='notice notice-success'><p>Converted {$count} showcase posts to regular posts.</p></div>";
    }
});

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

add_action('wp_enqueue_scripts', function () {

    if (is_category() || is_tag()) {
        wp_enqueue_style(
            'kreativ-archive',
            get_template_directory_uri() . '/css/kreativ-archive.css',
            [],
            kreativ_asset_version( '/css/kreativ-archive.css' )
        );
    }

});
