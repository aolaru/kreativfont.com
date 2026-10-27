<?php
// Exercise the real template routing: the identifier must never regain a generic hero.
class WP_Post {
    public $post_name;
    public $post_content = 'SAVED_EDITORIAL_CONTENT';
    public function __construct($slug) { $this->post_name = $slug; }
}
function add_filter(...$args) {}
function add_action(...$args) {}
function get_post($post = null) { return $post ?? $GLOBALS['layout_post']; }
function get_page_template_slug(...$args) { return ''; }
function home_url($path = '') { return 'https://kreativfont.com' . $path; }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return esc_html($text); }
function esc_url($text) { return esc_html($text); }
function locate_template($path) { return dirname(__DIR__) . '/' . $path; }
function get_header() {}
function get_footer() {}
function have_posts() { return $GLOBALS['layout_post_checks']++ < 2; }
function the_post() {}
function the_title() { echo 'Test tool'; }
function get_the_title(...$args) { return 'Test tool'; }
function get_the_date(...$args) { return 'October 27, 2026'; }
function has_excerpt(...$args) { return false; }
function wp_strip_all_tags($text) { return strip_tags($text); }
function wp_trim_words($text, ...$args) { return $text; }
function wp_unslash($text) { return $text; }
function wp_parse_url($url, $part) { return parse_url($url, $part); }
function kreativ_is_font_post(...$args) { return false; }
function edit_post_link(...$args) {}
function the_content(...$args) { echo 'SAVED_EDITORIAL_CONTENT'; }
function do_shortcode($shortcode) {
    if ($shortcode !== '[gpt_font_identifier]') { throw new RuntimeException('Unexpected tool shortcode'); }
    return '<form id="gpt-font-id-form">Tool controls</form>';
}
require dirname(__DIR__) . '/inc/template-helpers.php';

foreach (['single.php', 'page.php', 'template-tools-page.php'] as $template) {
    $GLOBALS['layout_post'] = new WP_Post('kreativ-font-identifier');
    $GLOBALS['layout_post_checks'] = 0;
    $_SERVER['REQUEST_URI'] = '/tools/kreativ-font-identifier';
    ob_start(); require dirname(__DIR__) . '/' . $template; $html = ob_get_clean();
    if (str_contains($html, 'kreativ-page-hero') || str_contains($html, 'SAVED_EDITORIAL_CONTENT') || substr_count($html, 'id="gpt-font-id-form"') !== 1 || substr_count($html, '<h1') !== 1) {
        throw new RuntimeException('Identifier layout regressed through ' . $template);
    }
}
$GLOBALS['layout_post'] = new WP_Post('other-tool');
$GLOBALS['layout_post_checks'] = 0;
$_SERVER['REQUEST_URI'] = '/tools/other-tool';
ob_start(); require dirname(__DIR__) . '/page.php'; $html = ob_get_clean();
if (!str_contains($html, 'kreativ-page-hero') || !str_contains($html, 'SAVED_EDITORIAL_CONTENT')) {
    throw new RuntimeException('An unrelated tool layout was changed');
}
echo "PASS: identifier has one compact title and tool across post, page and tools templates; other tool heroes remain unchanged.\n";
