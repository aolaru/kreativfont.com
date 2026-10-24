<?php
/** Local catalogue retrieval and specimen comparison. Never stores visitor images/results. */
defined('ABSPATH') || exit;

function gpt_font_id_catalogue_styles()
{
    return ['unknown' => [], 'sans_serif' => ['sans-serif'], 'serif' => ['serif'],
        'slab_serif' => ['slab-serif'], 'script' => ['script'], 'handwritten' => ['handwritten', 'handwriting'],
        'blackletter' => ['blackletter'], 'monospace' => ['monospace', 'monospaced'], 'display' => ['display']];
}

function gpt_font_id_catalogue_features()
{
    return ['geometric', 'humanist', 'grotesque', 'condensed', 'rounded', 'brush', 'stencil', 'retro', 'calligraphic'];
}

function gpt_font_id_catalogue_name($title)
{
    $title = html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $title = preg_split('/\s+[–—]\s+|\s+-\s+/u', $title)[0];
    $title = explode(',', $title, 2)[0];
    return trim(preg_replace('/(?:\s+Font)?\s+Free Download(?:\s*\(Commercial Use\))?$|\s+Font$/iu', '', $title));
}

function gpt_font_id_catalogue_normalize($name)
{
    $name = remove_accents(gpt_font_id_catalogue_name($name));
    $name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
    return preg_replace('/[^\p{L}\p{N}]+/u', '', $name);
}

/** Only the site's own public upload images (including its WordPress image CDN). */
function gpt_font_id_catalogue_image_url($url)
{
    $site = wp_parse_url(home_url('/'));
    $parts = wp_parse_url($url);
    if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) { return ''; }
    $path = $parts['path'] ?? '';
    $host = strtolower($parts['host'] ?? '');
    $own = $host === strtolower($site['host']) && str_starts_with($path, '/wp-content/uploads/');
    $cdn = in_array($host, ['i0.wp.com', 'i1.wp.com', 'i2.wp.com'], true)
        && str_starts_with($path, '/' . $site['host'] . '/wp-content/uploads/');
    return ($own || $cdn) && preg_match('/\.(?:png|jpe?g|webp)$/i', $path) ? $url : '';
}

function gpt_font_id_catalogue_tax_query()
{
    $include = [];
    foreach (['fonts', 'free-fonts'] as $slug) {
        if (get_term_by('slug', $slug, 'category')) { $include[] = $slug; }
    }
    // Fail closed on a different theme/site with no font catalogue categories.
    if (!$include) { return []; }
    return ['relation' => 'AND',
        ['taxonomy' => 'category', 'field' => 'slug', 'terms' => $include, 'include_children' => true],
        ['taxonomy' => 'category', 'field' => 'slug', 'terms' => ['tools', 'blog', 'identified-fonts', 'templates-themes', 'graphics', 'photos', 'videos', 'sounds'], 'operator' => 'NOT IN', 'include_children' => true]];
}

function gpt_font_id_catalogue_preview($id)
{
    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'medium_large');
    return gpt_font_id_catalogue_image_url($thumb[0] ?? '');
}

function gpt_font_id_catalogue_posts($args, $tax)
{
    $query = new WP_Query(array_merge(['post_type' => 'post', 'post_status' => 'publish', 'has_password' => false,
        'posts_per_page' => 30, 'no_found_rows' => true, 'ignore_sticky_posts' => true,
        'orderby' => 'title', 'order' => 'ASC', 'tax_query' => $tax], $args));
    return $query->posts;
}

function gpt_font_id_catalogue_retrieve($analysis)
{
    $tax = gpt_font_id_catalogue_tax_query();
    if (!$tax) { return []; }
    $ranked = [];
    foreach ($analysis['candidates'] as $guess) {
        $needle = gpt_font_id_catalogue_normalize($guess['name']);
        if (!$needle) { continue; }
        $words = preg_split('/\s+/u', gpt_font_id_catalogue_name($guess['name']));
        usort($words, static fn($a, $b) => strlen($b) <=> strlen($a));
        // A distinctive word also retrieves close family names and minor typos in another word.
        $queries = array_unique([$guess['name'], $words[0] ?? $guess['name']]);
        foreach ($queries as $search) {
            foreach (gpt_font_id_catalogue_posts(['s' => $search, 'search_columns' => ['post_title'], 'orderby' => 'relevance'], $tax) as $post) {
                $name = gpt_font_id_catalogue_normalize($post->post_title);
                if (!$name) { continue; }
                $similarity = 1 - levenshtein($needle, $name) / max(strlen($needle), strlen($name));
                if ($similarity < 0.72) { continue; }
                $score = $needle === $name ? 1000 : (int) ($similarity * 100);
                if (!isset($ranked[$post->ID]) || $score > $ranked[$post->ID]['score']) { $ranked[$post->ID] = ['post' => $post, 'score' => $score]; }
            }
        }
    }
    $styles = gpt_font_id_catalogue_styles()[$analysis['style']] ?? [];
    $styles = array_values(array_filter($styles, static fn($slug) => (bool) get_term_by('slug', $slug, 'category')));
    if ($styles) {
        $style_tax = $tax;
        $style_tax[] = ['taxonomy' => 'category', 'field' => 'slug', 'terms' => $styles, 'include_children' => true];
        $features = array_values(array_filter(array_slice($analysis['features'], 0, 3), static fn($slug) => (bool) get_term_by('slug', $slug, 'category')));
        $feature_tax = $style_tax;
        if ($features) { $feature_tax[] = ['taxonomy' => 'category', 'field' => 'slug', 'terms' => $features, 'include_children' => true]; }
        if ($features) {
            foreach (gpt_font_id_catalogue_posts([], $feature_tax) as $post) {
                if (!isset($ranked[$post->ID])) { $ranked[$post->ID] = ['post' => $post, 'score' => 2]; }
            }
        }
        // Keep a broad style pool: uncertain AI feature labels must not exclude the actual family.
        foreach (gpt_font_id_catalogue_posts(['posts_per_page' => 600], $style_tax) as $post) {
            if (!isset($ranked[$post->ID])) { $ranked[$post->ID] = ['post' => $post, 'score' => 1]; }
        }
    }
    uasort($ranked, static fn($a, $b) => ($b['score'] <=> $a['score']) ?: ($a['post']->ID <=> $b['post']->ID));
    $rows = []; $seen = [];
    foreach ($ranked as $item) {
        $post = $item['post'];
        // Recheck publication state; never expose drafts, password-protected posts or reviews.
        if ($post->post_type !== 'post' || $post->post_status !== 'publish' || $post->post_password !== '') { continue; }
        $name = gpt_font_id_catalogue_name($post->post_title);
        $normalized = gpt_font_id_catalogue_normalize($name);
        if (!$normalized || isset($seen[$normalized])) { continue; }
        $url = get_permalink($post);
        if (wp_parse_url($url, PHP_URL_SCHEME) !== 'https' || wp_parse_url($url, PHP_URL_HOST) !== wp_parse_url(home_url('/'), PHP_URL_HOST)) { continue; }
        $seen[$normalized] = true;
        // Resolve attachment metadata only for selected references, not hundreds of rows.
        $rows[] = ['id' => (int) $post->ID, 'name' => $name, 'url' => $url, 'preview_url' => ''];
        if (count($rows) === 600) { break; }
    }
    return $rows;
}

function gpt_font_id_catalogue_label($candidate)
{
    if (($candidate['catalogue_status'] ?? '') !== 'listed') { return 'AI suggestion; no matching catalogue entry found in this search.'; }
    if (($candidate['evidence'] ?? '') === 'local_render_alternative') { return 'Similar catalogue alternative; not an identification. A real font file was compared.'; }
    if (($candidate['evidence'] ?? '') === 'local_render_match') { return 'Listed in Kreativ; locally rendered letter shapes compared. Exact identification is unverified.'; }
    return ($candidate['evidence'] ?? '') === 'specimen_compared'
        ? 'Listed in Kreativ; catalogue specimen compared by AI. Exact identification is unverified.'
        : 'Listed in Kreativ by name. The name lookup does not verify its appearance or identity.';
}

/** Exact family/declared face lookup only; never treat a fuzzy retrieval as corroboration. */
function gpt_font_id_catalogue_find_row($name, $rows)
{
    $needle = gpt_font_id_catalogue_normalize($name);
    foreach ($rows as $row) {
        if ($needle === gpt_font_id_catalogue_normalize($row['name'])) { return $row; }
    }
    foreach ($rows as $row) {
        $family = gpt_font_id_catalogue_normalize($row['name']);
        foreach (['regular', 'bold', 'italic', 'bolditalic', 'oblique', 'boldoblique'] as $face) {
            if ($needle === $family . $face) { return $row; }
        }
    }
    return null;
}

/** Compose evidence without allowing a nearest-neighbour lookup to establish identity. */
function gpt_font_id_catalogue_ground($analysis, $rows, $raster)
{
    $analysis['catalogue_alternatives'] = [];
    $analysis['catalogue'] = ['status' => 'lookup_only', 'retrieved' => count($rows),
        'compared' => $raster['tested'] ?? 0, 'families_compared' => $raster['tested_families'] ?? 0,
        'limited' => !empty($raster['limited'])];
    $analysis['catalogue_note'] = 'Catalogue names checked. AI suggestions remain unverified; a nearby catalogue font can be an alternative rather than the original font.';
    foreach ($analysis['candidates'] as &$candidate) {
        $row = gpt_font_id_catalogue_find_row($candidate['name'], $rows);
        if ($row) {
            $candidate = array_merge($candidate, $row, ['catalogue_status' => 'listed', 'evidence' => 'name_lookup']);
            $candidate['preview_url'] = gpt_font_id_catalogue_preview($row['id']);
        } else {
            $candidate['catalogue_status'] = 'not_found'; $candidate['evidence'] = 'ai_only';
        }
    }
    unset($candidate);
    $best = $raster['matches'][0] ?? null;
    if (!$best) { return $analysis; }
    $row = null;
    foreach ($rows as $entry) { if ($entry['id'] === $best['id']) { $row = $entry; break; } }
    if (!$row) { return $analysis; }
    $corroborated_index = null;
    foreach ($analysis['candidates'] as $index => $candidate) {
        if (($candidate['id'] ?? null) === $row['id']) { $corroborated_index = $index; break; }
    }
    $style = $best['style'] ?? 'regular';
    $style_label = ['regular' => 'regular', 'bold' => 'bold', 'italic' => 'italic', 'bold_italic' => 'bold italic'][$style] ?? 'available';
    if (!empty($raster['strong']) && empty($raster['limited']) && $corroborated_index !== null) {
        $candidate = $analysis['candidates'][$corroborated_index];
        // A similar alternative from the AI remains an alternative even if its rendered shape is close.
        if ($candidate['kind'] === 'possible_match') {
            $candidate['evidence'] = 'local_render_match'; $candidate['matched_style'] = $style;
            $candidate['reason'] = 'The AI suggested this family and a separate comparison with its actual ' . $style_label . ' font file found closely matching letter shapes. The exact family and face remain unverified.';
            array_splice($analysis['candidates'], $corroborated_index, 1);
            array_unshift($analysis['candidates'], $candidate);
            $analysis['catalogue']['status'] = 'corroborated_shape_match';
            $analysis['catalogue_note'] = 'Compared ' . ($raster['tested'] ?? 0) . ' local font faces. The leading suggestion is supported by both AI and a rendered-text comparison; this is not proof of identity.';
            return $analysis;
        }
    }
    // Keep all original suggestions. A conflicting local result belongs in a separate alternatives section.
    if ($corroborated_index === null && ($best['distance'] ?? 1) <= 0.20) {
        $row['catalogue_status'] = 'listed'; $row['evidence'] = 'local_render_alternative';
        $row['kind'] = 'similar_alternative'; $row['matched_style'] = $style;
        $row['preview_url'] = gpt_font_id_catalogue_preview($row['id']);
        $row['reason'] = 'This catalogue family offers similar letter shapes in its ' . $style_label . ' face. It is a comparison option, not an identification of the font in your image.';
        $analysis['catalogue_alternatives'][] = $row;
        $analysis['catalogue']['status'] = 'local_alternative';
    }
    if (!empty($raster['limited'])) {
        $analysis['catalogue_note'] .= ' The local comparison reached its time limit; no match was promoted.';
    }
    return $analysis;
}

function gpt_font_id_catalogue_matches($image, $api_key, $model = null)
{
    $analysis = gpt_font_id_request_matches($image, $api_key, $model ?? gpt_font_id_active_model());
    if (is_wp_error($analysis)) { return $analysis; }
    $analysis['catalogue'] = ['status' => 'skipped', 'retrieved' => 0, 'compared' => 0];
    $analysis['catalogue_note'] = ''; $analysis['catalogue_alternatives'] = [];
    if (!$analysis['readable']) { return $analysis; }
    $rows = gpt_font_id_catalogue_retrieve($analysis);
    $raster = gpt_font_id_raster_matches($image, $analysis['sample_text'], $rows);
    // No additional AI reranking calls. Catalogue gaps must not erase a plausible original suggestion.
    return gpt_font_id_catalogue_ground($analysis, $rows, $raster);
}
