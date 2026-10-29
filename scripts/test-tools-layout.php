<?php
// The interactive tools get compact layouts; tutorials remain separate content.
class WP_Post {
    public $post_name;
    public function __construct($slug) { $this->post_name = $slug; }
}
function get_post($post = null) { return $post; }
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return esc_html($text); }
function esc_url($text) { return esc_html($text); }
function home_url($path = '/') { return 'https://kreativfont.com' . $path; }
function get_the_title(...$args) { return 'Test tool'; }
function the_content() { echo '<div id="plugin-controls">PLUGIN_CONTENT</div>'; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(__DIR__) . '/inc/tools.php';
$tools = kreativ_get_tool_catalogue();
check(count($tools) === 4, 'Four tools, excluding tutorials');
foreach (['kreativ-font-pairing-tools', 'kreativ-font-name-generator'] as $slug) {
    $post = new WP_Post($slug);
    check(kreativ_get_creative_tool_config($post) !== null, 'Recognize the interactive tool');
    ob_start(); require dirname(__DIR__) . '/partials/creative-tool.php'; $html = ob_get_clean();
    check(substr_count($html, '<h1') === 1, 'One main tool heading');
    check(substr_count($html, 'PLUGIN_CONTENT') === 1, 'Render the plugin once');
    check(!str_contains($html, 'kreativ-page-hero'), 'Do not regain the generic hero');
    check(str_contains($html, 'aria-label="Breadcrumb"') && str_contains($html, 'Tips for this tool'), 'Breadcrumb and relevant guidance');
    check(substr_count($html, '/tools/') === 3, 'Links to the other three tools');
}
foreach (['kreativ-font-identifier', 'fancy-text-generator', 'kreativ-fancy-text-android-png-tutorial', 'unrelated'] as $slug) {
    check(kreativ_get_creative_tool_config(new WP_Post($slug)) === null, 'Leave other page renderers in control');
}
check(kreativ_get_creative_tool_config(null) === null, 'Handle missing posts');
echo "PASS: naming and pairing use compact workspaces; four tools are distinguished from guides.\n";
