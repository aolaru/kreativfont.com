<?php
// Local integration harness only. This directory is never deployed to WordPress.
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
define('KFI_VERSION', 'other-plugin-version'); // Another active Kreativ plugin owns this generic name.
$fixture = $_GET['fixture'] ?? 'normal';
$options = ['gpt_font_id_api_key' => 'fixture-not-a-key', 'gpt_font_id_auto_post' => 'yes', 'gpt_font_id_post_status' => 'publish'];
$transients = []; $actions = []; $filters = []; $shortcodes = []; $posts = []; $metas = []; $api_calls = [];
if ($fixture === 'review_off') { $options['gpt_font_id_allow_review'] = 'no'; }
if ($fixture === 'live_sol') { $options['gpt_font_id_model'] = 'gpt-6.1-sol'; }
if ($fixture === 'bad_model') { $options['gpt_font_id_model'] = 'untrusted-model'; }
class WP_Error {
    public function __construct(public $code, public $message) {}
    public function get_error_message() { return $this->message; }
}
class TestReply extends Exception { public function __construct(public $payload) { parent::__construct('reply'); } }
function add_action($name, $callback) { $GLOBALS['actions'][$name][] = $callback; }
function add_filter($name, $callback) { $GLOBALS['filters'][$name][] = $callback; }
function add_shortcode($name, $callback) { $GLOBALS['shortcodes'][$name] = $callback; }
function register_post_type($name, $args) { $GLOBALS['review_type'] = $args; }
function add_meta_box(...$args) {}
function is_wp_error($value) { return $value instanceof WP_Error; }
function get_option($name, $default = false) { return $GLOBALS['options'][$name] ?? $default; }
function add_option($name, $value, ...$args) { if (isset($GLOBALS['options'][$name])) return false; $GLOBALS['options'][$name] = $value; return true; }
function delete_option($name) { unset($GLOBALS['options'][$name]); }
function update_option($name, $value, ...$args) { $GLOBALS['options'][$name] = $value; return true; }
function get_transient($name) { return $GLOBALS['fixture'] === 'rate' && str_starts_with($name, 'kfi_ip_') ? 5 : ($GLOBALS['transients'][$name] ?? false); }
function set_transient($name, $value, $ttl) { $GLOBALS['transients'][$name] = $value; }
function wp_hash($value) { return hash_hmac('sha256', $value, 'test-only-salt'); }
function wp_generate_password(...$args) { return bin2hex(random_bytes(6)); }
function wp_json_encode($value) { return json_encode($value); }
function sanitize_text_field($text) { return trim(strip_tags($text)); }
function wp_strip_all_tags($text) { return strip_tags($text); }
function remove_accents($text) { return $text; }
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }
function wp_upload_dir() { return ['basedir' => '/nonexistent-test-catalogue']; }
function sanitize_title($value) { return strtolower(str_replace(' ', '-', $value)); }
function get_term_by($field, $value, $taxonomy) { return $GLOBALS['fixture'] === 'no_catalogue' ? false : (object) ['slug' => $value]; }
function get_permalink($post) { return 'https://kreativfont.com/fonts/' . strtolower(str_replace(' ', '-', gpt_font_id_catalogue_name($post->post_title))); }
function get_post_thumbnail_id($id) { return $id; }
function wp_get_attachment_image_src($id, $size) {
    if ($GLOBALS['fixture'] === 'no_previews') { return false; }
    return [$GLOBALS['fixture'] === 'unsafe_previews' ? 'https://attacker.test/private.png' : 'https://kreativfont.com/wp-content/uploads/fixture-' . $id . '.png', 768, 432];
}
class WP_Query {
    public $posts = [];
    public function __construct($args) {
        if ($args['post_type'] !== 'post' || $args['post_status'] !== 'publish' || $args['has_password'] !== false || empty($args['tax_query'][0]['terms']) || ($args['tax_query'][1]['operator'] ?? '') !== 'NOT IN') { throw new Exception('Unrestricted catalogue query'); }
        if ($GLOBALS['fixture'] === 'empty_catalogue') { return; }
        foreach ([301 => 'Georgia Font', 302 => 'Bitter Font Free Download (Commercial Use)', 303 => 'Baskerville'] as $id => $title) {
            if (isset($args['s']) && !str_contains(strtolower($title), strtolower($args['s']))) { continue; }
            $this->posts[] = (object) ['ID' => $id, 'post_title' => $title, 'post_type' => 'post', 'post_status' => 'publish', 'post_password' => ''];
        }
        if ($GLOBALS['fixture'] === 'invalid_rows') {
            $this->posts = [(object) ['ID' => 400, 'post_title' => 'Georgia Font', 'post_type' => 'kfi_review', 'post_status' => 'publish', 'post_password' => ''],
                (object) ['ID' => 401, 'post_title' => 'Georgia Font', 'post_type' => 'post', 'post_status' => 'draft', 'post_password' => ''],
                (object) ['ID' => 402, 'post_title' => 'Georgia Font', 'post_type' => 'post', 'post_status' => 'publish', 'post_password' => 'secret']];
        }
    }
}
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_attr($text) { return esc_html($text); }
function esc_url($text) { return esc_html($text); }
function home_url($path = '') { return 'https://kreativfont.com' . $path; }
function admin_url($path = '') { return 'https://kreativfont.com/wp-admin/' . $path; }
function add_query_arg($key, $value, $url) { return $url . '?' . http_build_query([$key => $value]); }
function plugin_dir_url($file) { return '/plugin/'; }
function wp_enqueue_style(...$args) {}
function wp_enqueue_script(...$args) {}
function wp_localize_script($handle, $name, $config) { echo '<script>var ' . $name . '=' . json_encode($config) . ';</script>'; }
function wp_add_inline_script($handle, $code, $position) { echo '<script>' . $code . '</script>'; }
function wp_create_nonce($action) { return 'fixture-nonce'; }
function check_ajax_referer($action, $field = false, $stop = true) { return ($_POST[$field ?: '_ajax_nonce'] ?? '') === 'fixture-nonce'; }
function nocache_headers() { header('Cache-Control: no-store'); }
function current_user_can($cap) { return str_starts_with($GLOBALS['fixture'], 'admin'); }
function register_setting(...$args) {}
function add_settings_error(...$args) {}
function selected($a, $b) { if ($a === $b) echo 'selected'; }
function checked($a, $b) { if ($a === $b) echo 'checked'; }
function settings_fields($group) {}
function submit_button($text) { echo '<button type="submit">' . esc_html($text) . '</button>'; }
function wp_remote_get($url, $args) { return ['code'=>$GLOBALS['fixture'] === 'admin_unavailable' && !str_ends_with($url,'gpt-4o') ? 404 : 200, 'body'=>'']; }
function wp_remote_post($url, $args) {
    $request = json_decode($args['body'], true);
    $GLOBALS['api_calls'][] = [
        'store' => $request['store'], 'model' => $request['model'],
        'format' => $request['text']['format']['type'], 'max_output_tokens' => $request['max_output_tokens'],
        'temporary_file_deleted' => !file_exists($_FILES['font_image']['tmp_name'] ?? '/nonexistent-test-upload'),
        'reasoning' => $request['reasoning'] ?? null,
        'comparison' => isset($request['text']['format']['schema']['properties']['candidates']['items']['properties']['name']['enum']),
        'selection' => ($request['text']['format']['schema']['properties']['candidates']['maxItems'] ?? 3) === 6,
        'image_count' => count(array_filter($request['input'][0]['content'], static fn($item) => $item['type'] === 'input_image')),
        'style_schema' => isset($request['text']['format']['schema']['properties']['style']),
    ];
    if ($GLOBALS['fixture'] === 'failure') return ['code' => 500, 'body' => 'secret-provider-details-DO-NOT-EXPOSE'];
    if ($GLOBALS['fixture'] === 'network') return new WP_Error('network', 'secret-network-debug');
    if ($GLOBALS['fixture'] === 'refusal') return ['code' => 200, 'body' => json_encode(['status'=>'completed','output'=>[['content'=>[['type'=>'refusal','refusal'=>'private details']]]]] )];
    if ($GLOBALS['fixture'] === 'incomplete') return ['code' => 200, 'body' => json_encode(['status'=>'incomplete','output'=>[]])];
    $comparison = end($GLOBALS['api_calls'])['comparison'];
    $selection = end($GLOBALS['api_calls'])['selection'];
    if ($comparison && !$selection && $GLOBALS['fixture'] === 'comparison_failure') { return ['code' => 500, 'body' => 'secret-comparison-failure']; }
    if ($selection && $GLOBALS['fixture'] === 'selection_failure') { return ['code' => 500, 'body' => 'secret-selection-failure']; }
    $result = ['readable'=>true,'sample_text'=>'Kreativ Serif','note'=>'Check the lowercase terminals against the original.','style'=>'serif','features'=>[],'candidates'=>[
        ['name'=>'Georgia','reason'=>'Bracketed serifs and a generous x-height resemble the sample.','kind'=>'possible_match'],
        ['name'=>'Bitter','reason'=>'A similar serif direction; proportions differ.','kind'=>'similar_alternative'],
    ]];
    if ($GLOBALS['fixture'] === 'unlisted') { $result['candidates'][0]['name'] = 'Unknown Serif'; }
    if ($GLOBALS['fixture'] === 'style_only') { $result['candidates'] = []; }
    if ($comparison) {
        $result['candidates'][0] = ['name' => 'catalogue_301', 'reason' => 'Bracketed serifs resemble the specimen; the exact family is unverified.', 'kind' => 'possible_match'];
        $result['candidates'][1] = ['name' => 'catalogue_302', 'reason' => 'The slab serifs offer a similar alternative; proportions differ.', 'kind' => 'similar_alternative'];
        if ($GLOBALS['fixture'] === 'unlisted') { $result['candidates'][0]['name'] = 'ai_0'; }
        if ($GLOBALS['fixture'] === 'invalid_key') { $result['candidates'][0]['name'] = 'catalogue_999999'; $result['candidates'][1]['name'] = 'https://attacker.test'; }
        if ($GLOBALS['fixture'] === 'comparison_abstain') { $result['candidates'] = []; }
    }
    if ($GLOBALS['fixture'] === 'no_text') { $result['readable'] = false; }
    if ($GLOBALS['fixture'] === 'hostile') {
        $result['candidates'][0]['name'] = '<script>alert(1)</script>Georgia';
        $result['candidates'][0]['reason'] = '<img src=x onerror=alert(1)>Good serifs';
        $result['candidates'][0]['url'] = 'javascript:alert(1)';
        $result['candidates'][] = $result['candidates'][1];
    }
    $text = $GLOBALS['fixture'] === 'malformed' ? 'not JSON' : json_encode($result);
    return ['code'=>200,'body'=>json_encode(['status'=>'completed','usage'=>['input_tokens'=>1000,'input_tokens_details'=>['cached_tokens'=>100],'output_tokens'=>300,'output_tokens_details'=>['reasoning_tokens'=>100]],'output'=>[['type'=>'reasoning'],['content'=>[['type'=>'output_text','text'=>$text]]]]])];
}
function wp_remote_retrieve_body($response) { return $response['body']; }
function wp_remote_retrieve_response_code($response) { return $response['code']; }
function wp_insert_post($post, ...$args) {
    foreach ($GLOBALS['filters']['wp_insert_post_data'] ?? [] as $filter) $post = $filter($post);
    $GLOBALS['posts'][] = $post; return count($GLOBALS['posts']);
}
function add_post_meta($id, $key, $value, $unique) {
    if ($GLOBALS['fixture'] === 'review_fail' && $key === '_kfi_review_image') return false;
    $GLOBALS['metas'][] = ['key'=>$key,'private_image'=>str_starts_with($value, 'data:image/')]; return 1;
}
function wp_trash_post($id) { $GLOBALS['posts'][$id-1]['post_status'] = 'trash'; }
function wp_send_json_error($message, $code = null) { throw new TestReply(['success'=>false,'data'=>$message]); }
function wp_send_json_success($data) { throw new TestReply(['success'=>true,'data'=>$data]); }
function wp_handle_upload(...$args) { throw new Exception('PUBLIC UPLOAD STORAGE MUST NEVER BE USED'); }
function wp_insert_attachment(...$args) { throw new Exception('MEDIA LIBRARY MUST NEVER BE USED'); }

require dirname(__DIR__) . '/plugin/gpt-font-id.php';
foreach ($actions['init'] as $callback) $callback();

if (($_SERVER['REQUEST_URI'] ?? '') === '/unit') {
    $checks = [];
    for ($i = 0; $i < 6; $i++) { $checks[] = !is_wp_error(gpt_font_id_check_rate_limit()); }
    $filter = $filters['wp_insert_post_data'][0];
    $model_before = gpt_font_id_active_model();
    $sanitized_without_ack = gpt_font_id_sanitize_model('gpt-6-luna');
    $_POST['gpt_font_id_model_ack'] = 'yes';
    $sanitized_with_ack = gpt_font_id_sanitize_model('gpt-6-luna');
    $sanitized_invalid = gpt_font_id_sanitize_model('untrusted');
    $profiles = [];
    foreach (array_keys(gpt_font_id_models()) as $model) {
        $metrics = null;
        gpt_font_id_request_matches('data:image/png;base64,fixture', 'test-key', $model, $metrics);
        $profiles[$model] = $metrics;
    }
    header('Content-Type: application/json');
    echo json_encode([
        'rate_sequence' => $checks,
        'counter_keys' => array_keys($transients),
        'publishing' => $filter(['post_type'=>'kfi_review','post_status'=>'publish'])['post_status'],
        'trashing' => $filter(['post_type'=>'kfi_review','post_status'=>'trash'])['post_status'],
        'ordinary_post' => $filter(['post_type'=>'post','post_status'=>'publish'])['post_status'],
        'model_before'=>$model_before, 'model_after'=>gpt_font_id_active_model(),
        'without_ack'=>$sanitized_without_ack, 'with_ack'=>$sanitized_with_ack, 'invalid'=>$sanitized_invalid,
        'profiles'=>$profiles, 'payloads'=>$api_calls,
    ]); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($fixture === 'admin_compare_budget') { $options['gpt_font_id_compare_' . gmdate('Ymd')] = ['calls'=>320,'estimated_usd'=>5]; }
    if ($fixture === 'admin_budget') { $transients['gpt_font_id_bench_' . gmdate('Ymd')] = ['calls'=>160,'estimated_usd'=>5]; }
    try {
        if (($_POST['action'] ?? '') === 'gpt_font_id_compare') { gpt_font_id_compare(); }
        elseif (($_POST['action'] ?? '') === 'gpt_font_id_benchmark') { gpt_font_id_benchmark(); }
        elseif (($_POST['action'] ?? '') === 'gpt_test_api_key') { gpt_font_id_check_model_access(); }
        else { gpt_font_id_identify(); }
    }
    catch (TestReply $reply) {
        header('Content-Type: application/json');
        $reply->payload['_test'] = ['calls'=>$api_calls,'posts'=>$posts,'metas'=>$metas,'type'=>$GLOBALS['review_type'],
            'active_model'=>gpt_font_id_active_model(),'benchmark_locked'=>isset($options['gpt_font_id_benchmark_lock'])];
        echo json_encode($reply->payload); exit;
    }
}
if (($_SERVER['REQUEST_URI'] ?? '') === '/plugin/assets/script.js') {
    header('Content-Type: application/javascript'); readfile(dirname(__DIR__) . '/plugin/assets/script.js'); exit;
}
if (($_SERVER['REQUEST_URI'] ?? '') === '/plugin/assets/style.css') {
    header('Content-Type: text/css'); readfile(dirname(__DIR__) . '/plugin/assets/style.css'); exit;
}
if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
    echo '<!doctype html><html><meta charset="utf-8"><body>';
    gpt_font_id_settings_page();
    echo '</body></html>'; exit;
}
echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Identifier integration test</title><link rel="stylesheet" href="/plugin/assets/style.css"><body style="font-family:system-ui;max-width:850px;margin:30px auto;padding:0 16px"><main>';
echo $shortcodes['gpt_font_identifier']();
echo '</main></body></html>';
