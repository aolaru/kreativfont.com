<?php
/** Offline replay only. Any accidental model request fails immediately. */
define('ABSPATH', __DIR__ . '/');
function sanitize_title($value) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($value)), '-'); }
function wp_strip_all_tags($value) { return strip_tags($value); }
function remove_accents($value) { return $value; }
function wp_upload_dir() { return ['basedir' => $GLOBALS['local_fixtures']['uploads']]; }
function get_post_thumbnail_id($id) { return 0; }
function wp_get_attachment_image_src(...$args) { return false; }
function wp_parse_url($value, $part = -1) { return parse_url($value, $part); }
function home_url($path = '') { return 'https://kreativfont.com' . $path; }
function gpt_font_id_request_matches(...$args) { throw new RuntimeException('PAID API CALLS ARE FORBIDDEN IN THIS OFFLINE TEST'); }
require dirname(__DIR__) . '/plugin/raster.php';
require dirname(__DIR__) . '/plugin/catalogue.php';
$fixtures = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$GLOBALS['local_fixtures'] = $fixtures;
$old = $argv[3] ?? null;
if ($old) { require $old; }
$saved = json_decode(file_get_contents($fixtures['saved_manifest']), true);
$saved_dir = dirname($fixtures['saved_manifest']);
$cached = json_decode(file_get_contents($argv[4]), true);
$samples = [];
foreach ($saved['samples'] as $sample) {
    $sample['file'] = $saved_dir . '/' . $sample['file'];
    $sample['text'] = $sample['provenance']['text'] ?? $sample['provenance']['computed_style']['text'] ?? '';
    $sample['group'] = 'saved'; $samples[] = $sample;
}
foreach ($fixtures['heldout'] as $sample) { $sample['group'] = 'heldout'; $samples[] = $sample; }
$results = [];
foreach ($samples as $sample) {
    $image = 'data:image/png;base64,' . base64_encode(file_get_contents($sample['file']));
    $started = microtime(true);
    $raster = gpt_font_id_raster_matches($image, $sample['text'], $fixtures['rows']);
    $before = $old ? kfi_old_raster_matches($image, $sample['text'], $fixtures['rows']) : null;
    $guesses = [];
    foreach ($cached['rows'] as $row) {
        if ($row['model'] === 'ai_legacy' && $row['sample']['id'] === $sample['id']) { $guesses = $row['outcome']['candidates']; break; }
    }
    $analysis = ['readable' => $sample['kind'] === 'known_font', 'sample_text' => $sample['text'], 'candidates' => $guesses, 'note' => '', 'style' => 'unknown', 'features' => []];
    $grounded = gpt_font_id_catalogue_ground($analysis, $fixtures['rows'], $raster);
    $name = static function ($result) use ($fixtures) {
        $id = $result['matches'][0]['id'] ?? null;
        foreach ($fixtures['rows'] as $row) { if ($row['id'] === $id) { return $row['name']; } }
        return null;
    };
    $results[] = ['id' => $sample['id'], 'group' => $sample['group'], 'truth' => $sample['font'], 'style' => $sample['style'],
        'ranked_family' => $name($raster), 'ranked_style' => $raster['matches'][0]['style'] ?? null,
        'distance' => $raster['matches'][0]['distance'] ?? null, 'strong_shapes' => $raster['strong'], 'limited' => $raster['limited'] ?? false,
        'old_ranked_family' => $before ? $name($before) : null, 'old_strong_shapes' => $before['strong'] ?? false,
        'cached_ai_names' => array_column($guesses, 'name'), 'final_names' => array_column($grounded['candidates'], 'name'),
        'alternatives' => array_column($grounded['catalogue_alternatives'], 'name'), 'status' => $grounded['catalogue']['status'],
        'elapsed_ms' => round((microtime(true) - $started) * 1000)];
    if (count($results) % 10 === 0) { echo 'Offline cases: ' . count($results) . '/' . count($samples) . "\n"; }
}
file_put_contents($argv[2], json_encode(['paid_api_calls' => 0, 'limits' => $fixtures['limits'], 'results' => $results], JSON_PRETTY_PRINT));
echo 'Saved offline component results: ' . $argv[2] . "\n";
