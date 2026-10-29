<?php
// Keep the Fancy Text header in the deployed theme, alongside the plugin's CSS.
class WP_Post {
    public $ID;
    public $post_content;
    public function __construct($id, $content) {
        $this->ID = $id;
        $this->post_content = $content;
    }
}
function get_post($post = null) { return $post; }
function kreativ_is_identifier_page($post) { return false; }
function get_the_date(...$args) { return 'October 29, 2026'; }
function get_the_title(...$args) { return 'Test tool'; }
function home_url($path = '/') { return 'https://kreativfont.com' . $path; }
function has_shortcode($content, $tag) { return str_contains($content, '[' . $tag . ']'); }
function kreativ_get_content_summary(...$args) { return 'Generic page summary'; }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html($text) { return esc_attr($text); }
function esc_url($text) { return esc_attr($text); }
function wp_strip_all_tags($text) { return strip_tags($text); }
function has_post_thumbnail(...$args) { return $GLOBALS['fancy_test_artwork']; }
function get_the_post_thumbnail(...$args) { return '<img src="artwork.jpg" alt="Tool artwork">'; }
function the_content() { echo '<div id="test-generator">Tool content</div>'; }
function edit_post_link(...$args) {}
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function render($id, $content, $kind = 'tool', $artwork = true) {
    $post = new WP_Post($id, $content);
    $content_kind = $kind;
    $GLOBALS['fancy_test_artwork'] = $artwork;
    ob_start(); require dirname(__DIR__) . '/partials/single-content.php'; return ob_get_clean();
}

foreach ([true, false] as $artwork) {
    $html = render(224093, '[kreativ_font_translator]', 'tool', $artwork);
    check(str_contains($html, 'kft-tool-page'), 'Fancy Text needs the shell that enables its responsive and dark-mode CSS');
    check(!str_contains($html, 'kreativ-page-hero'), 'Fancy Text must not regain the generic hero');
    check(substr_count($html, '<h1') === 1 && str_contains($html, 'id="kft-page-title"'), 'Exactly one compact heading');
    check(substr_count($html, 'aria-label="Breadcrumb"') === 1, 'Exactly one breadcrumb');
    check(str_contains($html, '20 Unicode styles') && str_contains($html, '18 typography presets'), 'Current style counts');
    check(str_contains($html, '#generator-faq') && str_contains($html, 'Enter text'), 'Quick guide retained');
    check(str_contains($html, 'Tool artwork') === $artwork, 'Artwork is optional');
    check(substr_count($html, 'id="test-generator"') === 1, 'Render the generator content once');
}
foreach (['tool', 'article', 'information'] as $kind) {
    $html = render(999, '[kreativ_font_translator]', $kind);
    check(!str_contains($html, 'kft-tool-page') && str_contains($html, 'kreativ-page-hero'), 'Keep unrelated page layouts');
    check(str_contains($html, 'Generic page summary') && substr_count($html, '<h1') === 1, 'Keep unrelated headings and summaries');
}
check(!str_contains(render(224093, 'No generator shortcode'), 'kft-tool-page'), 'Do not use the tool header if the generator is removed');
echo "PASS: Fancy Text retains its compact header and dark-mode shell; unrelated page layouts are unchanged.\n";
