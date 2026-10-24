<?php
// Real FreeType/GD checks against font files already shipped in this repository.
define('ABSPATH', __DIR__ . '/');
require dirname(__DIR__) . '/plugin/raster.php';
require dirname(__DIR__) . '/plugin/catalogue.php';
function sanitize_title($name) { return strtolower(str_replace(' ', '-', $name)); }
function wp_strip_all_tags($value) { return strip_tags($value); }
function remove_accents($value) { return $value; }
function get_post_thumbnail_id($id) { return 0; }
function wp_get_attachment_image_src(...$args) { return false; }
function wp_parse_url($value, $part = -1) { return parse_url($value, $part); }
function home_url($path = '') { return 'https://kreativfont.com' . $path; }
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
        foreach (['700','italic','700italic'] as $variant) {
            $target=$root.'/kreativ-fonts/test-0/test-0-'.$variant.'.ttf';copy($created[0],$target);$created[]=$target;
        }
        $faces=gpt_font_id_raster_faces(realpath($root.'/kreativ-fonts'),'Test 0');
        raster_check(array_column($faces,'style')===['regular','bold','italic','bold_italic'],'All real-file face slots must be available');
        $siblings=gpt_font_id_raster_matches($image,$text,array_merge($rows,[['id'=>3,'name'=>'Test 2']]));
        raster_check($siblings['strong']&&$siblings['tested_families']===3&&$siblings['tested']===6,'Sibling faces must not be treated as separate rival families');
        $bold_folder=$root.'/kreativ-fonts/only-bold';mkdir($bold_folder,0700);
        $bold_path=$bold_folder.'/only-bold-700.ttf';copy($fonts[1],$bold_path);$created[]=$bold_path;
        $bold_faces=gpt_font_id_raster_faces(realpath($root.'/kreativ-fonts'),'Only Bold');
        raster_check(count($bold_faces)===1&&$bold_faces[0]['style']==='bold','A missing regular face must not hide an available bold file or fabricate a regular replacement');
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
    $catalogue = [['id'=>1,'name'=>'Test 0','url'=>'https://kreativfont.com/fonts/test-0','preview_url'=>''],['id'=>2,'name'=>'Test 1','url'=>'https://kreativfont.com/fonts/test-1','preview_url'=>'']];
    $analysis = ['readable'=>true,'sample_text'=>$text,'note'=>'','candidates'=>[['name'=>'Unlisted Original','kind'=>'possible_match','reason'=>'Initial AI suggestion','url'=>'https://kreativfont.com/?s=original']]];
    $nearest = ['strong'=>true,'tested'=>8,'tested_families'=>4,'matches'=>[['id'=>2,'distance'=>0.01,'style'=>'italic']]];
    $grounded = gpt_font_id_catalogue_ground($analysis, $catalogue, $nearest);
    raster_check($grounded['candidates'][0]['name']==='Unlisted Original', 'A close neighbour must never overwrite an unlisted AI suggestion');
    raster_check($grounded['catalogue_alternatives'][0]['kind']==='similar_alternative' && $grounded['catalogue_alternatives'][0]['evidence']==='local_render_alternative', 'Uncorroborated local matches must be explicitly marked alternatives');
    $analysis['candidates'][] = ['name'=>'Test 1 Bold','kind'=>'possible_match','reason'=>'AI family suggestion','url'=>'https://kreativfont.com/?s=test'];
    $nearest['matches'][0]['style'] = 'bold';
    $grounded = gpt_font_id_catalogue_ground($analysis, $catalogue, $nearest);
    raster_check($grounded['candidates'][0]['name']==='Test 1' && $grounded['candidates'][0]['matched_style']==='bold', 'Only a corroborated family can be promoted; preserve the compared face');
    $nearest['limited'] = true;
    raster_check(gpt_font_id_catalogue_ground($analysis,$catalogue,$nearest)['candidates'][0]['name']==='Unlisted Original', 'A partial scan cannot promote a match');
    unset($nearest['limited']);$analysis['candidates'][1]['kind']='similar_alternative';
    raster_check(gpt_font_id_catalogue_ground($analysis,$catalogue,$nearest)['candidates'][0]['name']==='Unlisted Original', 'A suggested alternative must never be upgraded to an identification');
    raster_check(gpt_font_id_catalogue_find_row('Arial Narrow',[['id'=>3,'name'=>'Arial']])===null, 'A distinct family is not a face alias');
    raster_check(gpt_font_id_catalogue_find_row('Alpha Bold',[['id'=>4,'name'=>'Alpha Bold'],['id'=>5,'name'=>'Alpha']])['id']===4, 'An actual family name takes precedence over stripping a style suffix');
    echo "PASS: real in-memory font rendering, exact family ranking, transcription mismatch, blank/mixed backgrounds, script limits and font-path confinement.\n";
} finally {
    foreach ($created as $file) { unlink($file); }
    foreach (array_unique(array_map('dirname',$created)) as $folder) { rmdir($folder); }
    if (is_dir($root . '/kreativ-fonts')) { rmdir($root . '/kreativ-fonts'); }
    if (is_dir($root)) { rmdir($root); }
}
