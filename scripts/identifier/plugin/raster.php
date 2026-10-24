<?php
/** Compare a clean single-line crop with existing local catalogue font files, entirely in memory. */
defined('ABSPATH') || exit;

function gpt_font_id_raster_signature($image, $grid_width = 256, $grid_height = 48)
{
    $width = imagesx($image); $height = imagesy($image);
    $scale = min(1, 1200 / $width, 240 / $height);
    if ($scale < 1) { $image = imagescale($image, max(1, (int) ($width * $scale)), max(1, (int) ($height * $scale))); }
    $width = imagesx($image); $height = imagesy($image);
    $corners = [imagecolorat($image, 0, 0), imagecolorat($image, $width - 1, 0), imagecolorat($image, 0, $height - 1), imagecolorat($image, $width - 1, $height - 1)];
    $rgb = static fn($color) => [($color >> 16) & 255, ($color >> 8) & 255, $color & 255];
    $background = $rgb($corners[0]);
    foreach ($corners as $corner) {
        $color = $rgb($corner);
        if (max(abs($color[0] - $background[0]), abs($color[1] - $background[1]), abs($color[2] - $background[2])) > 25) { return null; }
    }
    $binary = imagecreatetruecolor($width, $height);
    imagefill($binary, 0, 0, 0xffffff);
    $left = $width; $right = -1; $top = $height; $bottom = -1; $ink = 0;
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $color = $rgb(imagecolorat($image, $x, $y));
            if (abs($color[0] - $background[0]) + abs($color[1] - $background[1]) + abs($color[2] - $background[2]) > 180) {
                imagesetpixel($binary, $x, $y, 0);
                $left = min($left, $x); $right = max($right, $x); $top = min($top, $y); $bottom = max($bottom, $y); $ink++;
            }
        }
    }
    if ($ink < 20 || $ink > $width * $height * 0.6 || $right <= $left || $bottom <= $top) { return null; }
    $crop_width = $right - $left + 1; $crop_height = $bottom - $top + 1;
    $aspect = $crop_width / $crop_height;
    if ($aspect < 3 || $aspect > 45 || $crop_height < 12) { return null; }
    $normalized = imagecreatetruecolor($grid_width, $grid_height);
    imagecopyresampled($normalized, $binary, 0, 0, $left, $top, $grid_width, $grid_height, $crop_width, $crop_height);
    $values = [];
    for ($y = 0; $y < $grid_height; $y++) {
        for ($x = 0; $x < $grid_width; $x++) { $values[] = (imagecolorat($normalized, $x, $y) & 255) / 255; }
    }
    return ['values' => $values, 'aspect' => $aspect];
}

function gpt_font_id_raster_render($font, $text, $size = 48)
{
    $box = @imagettfbbox($size, 0, $font, $text);
    if (!$box) { return null; }
    $left = min($box[0], $box[2], $box[4], $box[6]); $right = max($box[0], $box[2], $box[4], $box[6]);
    $top = min($box[1], $box[3], $box[5], $box[7]); $bottom = max($box[1], $box[3], $box[5], $box[7]);
    if ($right - $left < 1 || $right - $left > 6000 || $bottom - $top > 240) { return null; }
    $image = imagecreatetruecolor($right - $left + 16, $bottom - $top + 16);
    imagefill($image, 0, 0, 0xffffff);
    if (@imagettftext($image, $size, 0, 8 - $left, 8 - $top, 0, $font, $text) === false) { return null; }
    return $image;
}

/** Existing real faces only. Never synthesize slant/weight or trust a metadata filesystem path. */
function gpt_font_id_raster_faces($root, $name)
{
    $slug = sanitize_title($name);
    if (!preg_match('/^[a-z0-9-]+$/D', $slug)) { return []; }
    $faces = [];
    foreach (['regular' => ['regular', '400'], 'bold' => ['700', 'bold'], 'italic' => ['italic', '400italic'], 'bold_italic' => ['700italic', 'bolditalic']] as $style => $variants) {
        foreach ($variants as $variant) {
            $font = realpath($root . '/' . $slug . '/' . $slug . '-' . $variant . '.ttf');
            if (!$font || !str_starts_with($font, $root . '/' . $slug . '/') || !is_readable($font) || filesize($font) > 5000000) { continue; }
            $faces[] = ['path' => $font, 'variant' => $variant, 'style' => $style];
            break;
        }
    }
    return $faces;
}

function gpt_font_id_raster_distance($a, $b)
{
    $distance = 0;
    foreach ($a['values'] as $index => $value) { $distance += abs($value - $b['values'][$index]); }
    return $distance / count($a['values']) + min(0.3, abs(log($a['aspect'] / $b['aspect'])) * 0.15);
}

function gpt_font_id_raster_matches($image, $text, $rows)
{
    $result = ['available' => false, 'tested' => 0, 'strong' => false, 'matches' => []];
    if (!function_exists('imagettftext') || !function_exists('imagecreatefromstring') || !is_string($text)
        || strlen($text) < 6 || strlen($text) > 120 || !preg_match('/^[\x20-\x7e]+$/D', $text)
        || preg_match_all('/[A-Za-z]/', $text) < 10) { return $result; }
    $bytes = base64_decode(explode(',', $image, 2)[1] ?? '', true);
    $size = $bytes !== false ? @getimagesizefromstring($bytes) : false;
    // Keep decoding memory bounded independently of the general upload limit.
    if (!$size || $size[0] * $size[1] > 4000000 || $size[0] / $size[1] < 2) { return $result; }
    $source = @imagecreatefromstring($bytes);
    if (!$source) { return $result; }
    if (!imageistruecolor($source)) { imagepalettetotruecolor($source); }
    // Composite transparency before checking the plain background.
    $flat = imagecreatetruecolor($size[0], $size[1]); imagefill($flat, 0, 0, 0xffffff);
    imagecopy($flat, $source, 0, 0, 0, 0, $size[0], $size[1]); unset($source, $bytes);
    $signature = gpt_font_id_raster_signature($flat);
    $coarse_signature = gpt_font_id_raster_signature($flat, 64, 16); unset($flat);
    if (!$signature) { return $result; }
    $uploads = wp_upload_dir();
    $root = realpath($uploads['basedir'] . '/kreativ-fonts');
    if (!$root) { return $result; }
    $result['available'] = true;
    $started = microtime(true); $coarse = []; $tested_families = [];
    foreach ($rows as $row) {
        foreach (gpt_font_id_raster_faces($root, $row['name']) as $face) {
            if (microtime(true) - $started > 6) { $result['limited'] = true; break 2; }
            // Small renders shortlist faces; acceptance always uses a larger independent comparison below.
            $render = gpt_font_id_raster_render($face['path'], $text, 24);
            $reference = $render ? gpt_font_id_raster_signature($render, 64, 16) : null; unset($render);
            if (!$reference) { continue; }
            $result['tested']++;
            $tested_families[$row['id']] = true;
            $coarse[] = $face + ['id' => $row['id'], 'coarse_distance' => gpt_font_id_raster_distance($coarse_signature, $reference)];
        }
    }
    $result['tested_families'] = count($tested_families);
    usort($coarse, static fn($a, $b) => $a['coarse_distance'] <=> $b['coarse_distance']);
    // Refine every face of the twelve nearest distinct families, so sibling faces cannot hide rivals.
    $shortlist = [];
    foreach ($coarse as $face) { $shortlist[$face['id']] = true; if (count($shortlist) >= 12) { break; } }
    $best_faces = [];
    foreach ($coarse as $face) {
        if (!isset($shortlist[$face['id']])) { continue; }
        if (microtime(true) - $started > 7) { $result['limited'] = true; break; }
        $render = gpt_font_id_raster_render($face['path'], $text);
        $reference = $render ? gpt_font_id_raster_signature($render) : null; unset($render);
        if (!$reference) { continue; }
        $distance = gpt_font_id_raster_distance($signature, $reference);
        $id = $face['id'];
        if (!isset($best_faces[$id]) || $distance < $best_faces[$id]['distance']) {
            $best_faces[$id] = ['id' => $id, 'distance' => $distance, 'variant' => $face['variant'], 'style' => $face['style'], 'aspect_error' => abs(log($signature['aspect'] / $reference['aspect']))];
        }
    }
    $result['matches'] = array_values($best_faces);
    usort($result['matches'], static fn($a, $b) => $a['distance'] <=> $b['distance']);
    $first = $result['matches'][0]['distance'] ?? 1;
    $second = $result['matches'][1]['distance'] ?? $first;
    // Strict shape distance and separation; these are retrieval gates, never confidence percentages.
    $result['strong'] = empty($result['limited']) && count($result['matches']) >= 3 && $first <= 0.06 && $second - $first >= 0.025
        && ($result['matches'][0]['aspect_error'] ?? 1) <= 0.06;
    $result['matches'] = array_slice($result['matches'], 0, 6);
    return $result;
}
