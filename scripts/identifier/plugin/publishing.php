<?php
/** Public reports are created only for the versioned, explicit publication consent. */
defined('ABSPATH') || exit;

/** Re-encode pixels so original filenames, metadata and appended data are not published. */
function gpt_font_id_public_sample($image)
{
    if (!function_exists('imagecreatefromstring')) {
        return new WP_Error('publication_image', 'The image could not be prepared for publication.');
    }
    $bytes = base64_decode(explode(',', $image, 2)[1] ?? '', true);
    $source = $bytes !== false ? @imagecreatefromstring($bytes) : false;
    if (!$source) { return new WP_Error('publication_image', 'The image could not be prepared for publication.'); }
    $clean = null;
    try {
        $scale = min(1, 1600 / max(imagesx($source), imagesy($source)));
        $width = max(1, (int) round(imagesx($source) * $scale));
        $height = max(1, (int) round(imagesy($source) * $scale));
        $clean = imagecreatetruecolor($width, $height);
        imagealphablending($clean, false);
        imagesavealpha($clean, true);
        imagefill($clean, 0, 0, imagecolorallocatealpha($clean, 0, 0, 0, 127));
        if (!imagecopyresampled($clean, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source))) {
            return new WP_Error('publication_image', 'The image could not be prepared for publication.');
        }
        ob_start();
        try {
            $saved = imagepng($clean, null, 6);
            $encoded = ob_get_contents();
        } finally { ob_end_clean(); }
        return $saved && $encoded ? $encoded : new WP_Error('publication_image', 'The image could not be prepared for publication.');
    } catch (Throwable $error) {
        return new WP_Error('publication_image', 'The image could not be prepared for publication.');
    } finally {
        // GdImage objects release their pixel buffers when their references are dropped.
        $source = null;
        $clean = null;
    }
}

function gpt_font_id_publish_identification($result, $image)
{
    $sample = gpt_font_id_public_sample($image);
    if (is_wp_error($sample)) { return $sample; }
    $category = get_term_by('slug', 'identified-fonts', 'category');
    if ($category) {
        $category_id = (int) $category->term_id;
    } else {
        $created = wp_insert_term('Identified Fonts', 'category', ['slug' => 'identified-fonts']);
        if (is_wp_error($created)) { return new WP_Error('publication', 'The post category is unavailable.'); }
        $category_id = (int) ($created['term_id'] ?? 0);
    }
    if (!$category_id) { return new WP_Error('publication', 'The post category is unavailable.'); }
    $name = gpt_font_id_clean_text($result['candidates'][0]['name'] ?? 'No clear match', 100);
    $post_id = wp_insert_post([
        'post_type' => 'post', 'post_status' => 'draft', 'post_author' => 0,
        'post_title' => 'Font identification: ' . $name,
        'post_content' => '', 'post_category' => [$category_id],
        'comment_status' => 'closed', 'ping_status' => 'closed',
    ], true);
    if (is_wp_error($post_id) || !$post_id) { return new WP_Error('publication', 'The post could not be created.'); }
    $attachment_id = 0;
    $upload = null;
    $published = false;
    try {
        $upload = wp_upload_bits('font-identification-' . $post_id . '.png', null, $sample);
        if (!empty($upload['error']) || empty($upload['file'])) { return new WP_Error('publication', 'The image could not be saved.'); }
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => 'image/png', 'post_title' => 'Font identification sample ' . $post_id,
            'post_content' => '', 'post_status' => 'inherit',
        ], $upload['file'], $post_id, true);
        if (is_wp_error($attachment_id) || !$attachment_id) {
            $attachment_id = 0;
            return new WP_Error('publication', 'The image could not be attached.');
        }
        if (!function_exists('wp_generate_attachment_metadata')) { require_once ABSPATH . 'wp-admin/includes/image.php'; }
        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        if (!is_array($metadata) || !$metadata) { return new WP_Error('publication', 'The image could not be prepared.'); }
        wp_update_attachment_metadata($attachment_id, $metadata);
        add_post_meta($attachment_id, '_wp_attachment_image_alt', 'Submitted font sample', true);
        set_post_thumbnail($post_id, $attachment_id);
        if (!add_post_meta($post_id, '_kfi_publication_consent', 'public-image-and-results-v1:' . KREATIV_GPT_FONT_ID_VERSION, true)) {
            return new WP_Error('publication', 'The publication consent could not be saved.');
        }
        $content = '<figure class="wp-block-image size-large">' . wp_get_attachment_image($attachment_id, 'large', false, ['alt' => 'Submitted font sample']) . '</figure>';
        $content .= gpt_font_id_result_html($result);
        $content .= '<p><a href="' . esc_url(home_url('/tools/kreativ-font-identifier')) . '">Identify another font with Kreativ Font Identifier</a></p>';
        $updated = wp_update_post(['ID' => $post_id, 'post_content' => $content, 'post_status' => 'publish'], true);
        if (is_wp_error($updated) || !$updated) { return new WP_Error('publication', 'The post could not be published.'); }
        $published = true;
        return $post_id;
    } catch (Throwable $error) {
        return new WP_Error('publication', 'The post could not be published.');
    } finally {
        if (!$published) {
            if ($attachment_id) { wp_delete_attachment($attachment_id, true); }
            elseif (!empty($upload['file'])) { wp_delete_file($upload['file']); }
            wp_trash_post($post_id);
        }
    }
}
