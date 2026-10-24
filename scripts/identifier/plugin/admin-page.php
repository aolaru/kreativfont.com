<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_options_page('Kreativ Font Identifier', 'Font Identifier', 'manage_options', 'gpt-font-id', 'gpt_font_id_settings_page');
});

add_action('admin_init', function () {
    register_setting('gpt_font_id', 'gpt_font_id_model', [
        'type' => 'string', 'default' => 'gpt-4o', 'sanitize_callback' => 'gpt_font_id_sanitize_model',
    ]);
    register_setting('gpt_font_id', 'gpt_font_id_api_key', [
        'sanitize_callback' => function ($value) {
            $value = sanitize_text_field($value);
            return $value !== '' ? $value : get_option('gpt_font_id_api_key', '');
        },
    ]);
    register_setting('gpt_font_id', 'gpt_font_id_allow_review', [
        'default' => 'yes', 'sanitize_callback' => function ($value) { return $value === 'yes' ? 'yes' : 'no'; },
    ]);
    foreach (['gpt_font_id_ip_limit' => 5, 'gpt_font_id_daily_limit' => 200] as $name => $default) {
        register_setting('gpt_font_id', $name, [
            'type' => 'integer', 'default' => $default,
            'sanitize_callback' => function ($value) { return max(1, min(10000, absint($value))); },
        ]);
    }
});

function gpt_font_id_sanitize_model($value)
{
    $current = gpt_font_id_active_model();
    if (!is_string($value) || !isset(gpt_font_id_models()[$value])) {
        add_settings_error('gpt_font_id_model', 'unsupported_model', 'Unsupported model; the live model was not changed.');
        return $current;
    }
    if ($value !== $current && ($_POST['gpt_font_id_model_ack'] ?? '') !== 'yes') {
        add_settings_error('gpt_font_id_model', 'confirm_model', 'Confirm the live-model change before saving. Benchmarking never changes it.');
        return $current;
    }
    return $value;
}

function gpt_font_id_settings_page()
{
    if (!current_user_can('manage_options')) { return; }
    wp_enqueue_script('gpt-font-id-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery'], KREATIV_GPT_FONT_ID_VERSION, true);
    wp_add_inline_script('gpt-font-id-admin', 'var GPTFontIdAdmin = ' . wp_json_encode([
        'ajaxurl' => admin_url('admin-ajax.php'),
        'benchmarkNonce' => wp_create_nonce('gpt_font_id_benchmark_nonce'),
        'accessNonce' => wp_create_nonce('gpt_test_api_nonce'),
        'activeModel' => gpt_font_id_active_model(), 'profiles' => gpt_font_id_models(),
        'version' => KREATIV_GPT_FONT_ID_VERSION,
    ]) . ';', 'before');
    ?>
    <div class="wrap">
        <h1>Kreativ Font Identifier — Settings</h1>
        <p>Version <?php echo esc_html(KREATIV_GPT_FONT_ID_VERSION); ?> · Shortcode: <code>[gpt_font_identifier]</code></p>
        <div class="notice notice-info inline"><p><strong>No automatic public storage.</strong> Images are analysed from a temporary upload, which is removed before the OpenAI request. Nothing is added to the Media Library. OpenAI receives the image with response storage disabled; its separate retention policies still apply.</p><p>Only explicit visitor opt-in saves the image and suggestions in the admin-only review queue. Reviews cannot be published from that queue. Older auto-post and post-status settings are no longer used. Previously published reports have not been changed.</p></div>
        <p><a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=kfi_review')); ?>">Review shared submissions</a></p>
        <form method="post" action="options.php">
            <?php settings_fields('gpt_font_id'); ?>
            <table class="form-table" role="presentation">
                <tr><th><label for="gpt_font_id_model">Live identification model</label></th><td>
                    <select id="gpt_font_id_model" name="gpt_font_id_model">
                    <?php foreach (gpt_font_id_models() as $model => $profile) : ?>
                        <option value="<?php echo esc_attr($model); ?>" <?php selected(gpt_font_id_active_model(), $model); ?>><?php echo esc_html($profile['label']); ?></option>
                    <?php endforeach; ?>
                    </select>
                    <p class="description">Default: GPT-4o. This controls both the website and installed Chrome extensions. Models are not promoted automatically, and visitors cannot override this setting. Check account access and benchmark before switching.</p>
                    <label><input type="checkbox" name="gpt_font_id_model_ack" value="yes"> I intend to change the live model when saving these settings.</label>
                </td></tr>
                <tr><th><label for="gpt_font_id_api_key">OpenAI API key</label></th><td>
                    <input type="password" id="gpt_font_id_api_key" name="gpt_font_id_api_key" value="" autocomplete="new-password" class="regular-text" placeholder="Leave blank to keep the existing key">
                    <p class="description">The saved key is never displayed here. Leave blank to keep it unchanged.</p>
                    <button type="button" class="button" id="gpt-test-api">Check saved API access</button> <span id="gpt-test-result" role="status"></span>
                </td></tr>
                <tr><th>Optional review queue</th><td>
                    <input type="hidden" name="gpt_font_id_allow_review" value="no">
                    <label><input type="checkbox" name="gpt_font_id_allow_review" value="yes" <?php checked(get_option('gpt_font_id_allow_review', 'yes'), 'yes'); ?>> Allow visitors to explicitly share their image and suggestions for admin review.</label>
                    <p class="description">Default identification and older extension clients never opt in automatically. Review images are stored as protected metadata, not public attachments. Retain only what you need; trash reviews when finished.</p>
                </td></tr>
                <tr><th><label for="kfi-ip-limit">Requests per visitor / 15 minutes</label></th><td><input id="kfi-ip-limit" type="number" name="gpt_font_id_ip_limit" min="1" max="10000" value="<?php echo esc_attr(get_option('gpt_font_id_ip_limit', 5)); ?>"><p class="description">Visitors on a shared network may share this limit. Only a keyed hash of the network address is stored temporarily.</p></td></tr>
                <tr><th><label for="kfi-daily-limit">Total requests per day (UTC)</label></th><td><input id="kfi-daily-limit" type="number" name="gpt_font_id_daily_limit" min="1" max="10000" value="<?php echo esc_attr(get_option('gpt_font_id_daily_limit', 200)); ?>"><p class="description">Caps attempted analyses, including provider failures, to help control API usage.</p></td></tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
        <hr>
        <section id="kfi-benchmark" aria-labelledby="kfi-benchmark-title">
            <h2 id="kfi-benchmark-title">Identifier benchmark lab (admin only)</h2>
            <p><strong>Live model: <?php echo esc_html(gpt_font_id_active_model()); ?>. Benchmark runs do not change it.</strong></p>
            <p>AI-only mode measures the first AI pass. Paired mode compares the original AI-only pipeline against the complete current catalogue flow, including local font rendering and specimen comparison.</p>
            <p><label for="kfi-bench-mode">Evaluation mode</label> <select id="kfi-bench-mode"><option value="models">AI-only model comparison</option><option value="paired">Catalogue 0.8.2 versus original AI-only 0.7.0 (GPT-4o)</option></select></p>
            <p>Paired mode freezes both algorithms, alternates their order per image, and includes all catalogue API calls and local processing in latency/cost. Separate daily guard: $5 estimated and 320 generation calls. No live-model or matching changes are made. Scores measure family identification, grouped by the supplied style labels; they do not certify the exact weight or italic face.</p>
            <p>Use only synthetic or non-sensitive samples you may send to OpenAI. No benchmark images, reports or review records are retained on the site. Results stay in this browser until you export them. Keep this tab open while running.</p>
            <p>Daily benchmark guard: $5 estimated token usage and 160 generation requests, separate from visitor limits. Calls with unknown usage retain a conservative $0.10 reservation. Prices are estimates from the standard model rates verified on 24 October 2026, not an invoice; other tiers, cache writes, taxes or account terms can differ.</p>
            <p><label for="kfi-bench-manifest">Labelled sample manifest (JSON)</label><br><input id="kfi-bench-manifest" type="file" accept="application/json,.json"></p>
            <p><label for="kfi-bench-images">Matching sample images</label><br><input id="kfi-bench-images" type="file" accept="image/png,image/jpeg,image/webp" multiple></p>
            <p><label for="kfi-bench-resume">Optional checkpoint JSON to resume</label><br><input id="kfi-bench-resume" type="file" accept="application/json,.json"><br>Resumes only an identical paired dataset/profile. Completed outcomes, including errors, are preserved and never rerun.</p>
            <fieldset><legend>Models to compare</legend>
            <?php foreach (gpt_font_id_models() as $model => $profile) : ?>
                <label style="display:block;margin:8px 0"><input type="checkbox" class="kfi-bench-model" value="<?php echo esc_attr($model); ?>" checked> <?php echo esc_html($profile['label']); ?></label>
            <?php endforeach; ?>
            </fieldset>
            <p><button type="button" class="button button-primary" id="kfi-bench-run">Run benchmark (paid API calls)</button> <button type="button" class="button" id="kfi-bench-stop" disabled>Stop after current request</button> <button type="button" class="button" id="kfi-bench-export" disabled>Export results JSON</button></p>
            <p id="kfi-bench-status" role="status" aria-live="polite">Choose the manifest and its images. Expected font names are scored locally and are never included in the model request.</p>
            <pre id="kfi-bench-summary" style="white-space:pre-wrap;overflow-wrap:anywhere"></pre>
            <div style="overflow:auto"><table class="widefat striped" id="kfi-bench-table"><thead><tr><th>Sample</th><th>Model</th><th>Outcome</th><th>Top 1 / Top 3</th><th>Seconds</th><th>Estimated USD</th><th>Suggestions</th></tr></thead><tbody></tbody></table></div>
        </section>
    </div>
    <?php
}

add_action('wp_ajax_gpt_test_api_key', 'gpt_font_id_check_model_access');
function gpt_font_id_check_model_access() {
    if (!current_user_can('manage_options')) { wp_send_json_error('Not permitted.', 403); }
    check_ajax_referer('gpt_test_api_nonce', 'nonce');
    $key = get_option('gpt_font_id_api_key', '');
    if (!$key) { wp_send_json_error('No key configured.'); }
    $model = $_POST['model'] ?? gpt_font_id_active_model();
    if (!is_string($model) || !isset(gpt_font_id_models()[$model])) { wp_send_json_error('Unsupported model.'); }
    // Read-only model access check; no paid generation or uploaded content.
    $response = wp_remote_get('https://api.openai.com/v1/models/' . rawurlencode($model), [
        'timeout' => 10, 'headers' => ['Authorization' => 'Bearer ' . $key],
    ]);
    $status = is_wp_error($response) ? 0 : wp_remote_retrieve_response_code($response);
    if ($status === 200) { wp_send_json_success(['model' => $model, 'available' => true]); }
    wp_send_json_error(['model' => $model, 'available' => false, 'http_status' => $status, 'message' => 'Could not confirm access to this exact model. No generation was attempted.']);
}
