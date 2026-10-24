<?php
// Real FreeType/GD checks against font files already shipped in this repository.
define('ABSPATH', __DIR__ . '/');
require dirname(__DIR__) . '/plugin/raster.php';
function sanitize_title($name) { return strtolower(str_replace(' ', '-', $name)); }
function wp_upload_dir() { return ['basedir' => $GLOBALS['raster_test_root']]; }
function raster_check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
if (!function_exists('imagettftext')) { echo "SKIP: GD/FreeType is unavailable; production safely uses specimen-assisted fallback.\n"; exit; }
$root = sys_get_temp_dir() . '/kfi-raster-test-' . bin2hex(random_bytes(6));
$GLOBALS['raster_test_root'] = $root;
$fonts = [dirname(__DIR__, 3) . '/webfonts/2E46D5_0_0.ttf', dirname(__DIR__, 3) . '/webfonts/2EE639_0_0.ttf'];
foreach (['/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', '/System/Library/Fonts/Supplemental/Arial.ttf'] as $system_font) {
    if (is_file($system_font)) { $fonts[] = $system_font; break; }
}
$created = [];
try {
    foreach ($fonts as $index => $source) {
        $slug = 'test-' . $index;
        $folder = $root . '/kreativ-fonts/' . $slug;
        mkdir($folder, 0700, true);
        $target = $folder . '/' . $slug . '-regular.ttf';
        copy($source, $target); $created[] = $target;
    }
    $rows = [['id' => 1, 'name' => 'Test 0'], ['id' => 2, 'name' => 'Test 1']];
    $text = 'Distinctive letter shapes';
    $render = gpt_font_id_raster_render($created[0], $text);
    ob_start(); imagepng($render); $png = ob_get_clean();
    $image = 'data:image/png;base64,' . base64_encode($png);
    $result = gpt_font_id_raster_matches($image, $text, $rows);
    raster_check($result['available'] && $result['tested'] === 2, 'Both local font files must be compared');
    raster_check($result['matches'][0]['id'] === 1 && $result['matches'][0]['distance'] < 0.00001, 'An exact independently rendered family must rank first');
    raster_check(!$result['strong'], 'A two-family pool must never trigger the strong-match gate');
    if (count($fonts) === 3) {
        $three = gpt_font_id_raster_matches($image, $text, array_merge($rows, [['id' => 3, 'name' => 'Test 2']]));
        raster_check($three['strong'] && $three['matches'][0]['id'] === 1, 'A complete pool with a clearly separated exact shape match must pass the conservative gate');
    }
    $wrong_text = gpt_font_id_raster_matches($image, 'Another completely different sentence', $rows);
    raster_check(!$wrong_text['strong'] && $wrong_text['matches'][0]['distance'] > 0.10, 'A wrong transcription must not be called a strong shape match');
    raster_check(!gpt_font_id_raster_matches($image, 'Текст шрифта', $rows)['available'], 'Unsupported-script matching must fall back');
    raster_check(!gpt_font_id_raster_matches($image, 'line one' . "\n" . 'line two', $rows)['available'], 'Multiline transcriptions must fall back');
    $blank = imagecreatetruecolor(300, 100); imagefill($blank, 0, 0, 0xffffff);
    raster_check(gpt_font_id_raster_signature($blank) === null, 'A blank crop must not acquire a signature');
    imagesetpixel($blank, 0, 0, 0); raster_check(gpt_font_id_raster_signature($blank) === null, 'A mixed-background crop must be rejected');
    // A catalogue title can never traverse outside its own existing font directory.
    $traversal = gpt_font_id_raster_matches($image, $text, [['id' => 99, 'name' => '../Test 0']]);
    raster_check($traversal['tested'] === 0, 'Unsafe font slugs must be ignored');
    echo "PASS: real in-memory font rendering, exact family ranking, transcription mismatch, blank/mixed backgrounds, script limits and font-path confinement.\n";
} finally {
    foreach ($created as $file) { unlink($file); rmdir(dirname($file)); }
    if (is_dir($root . '/kreativ-fonts')) { rmdir($root . '/kreativ-fonts'); }
    if (is_dir($root)) { rmdir($root); }
}
