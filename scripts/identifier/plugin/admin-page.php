<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_options_page('Kreativ Font Identifier', 'Font Identifier', 'manage_options', 'gpt-font-id', 'gpt_font_id_settings_page');
});

add_action('admin_init', function () {
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

function gpt_font_id_settings_page()
{
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap">
        <h1>Kreativ Font Identifier — Settings</h1>
        <p>Version <?php echo esc_html(KREATIV_GPT_FONT_ID_VERSION); ?> · Shortcode: <code>[gpt_font_identifier]</code></p>
        <div class="notice notice-info inline"><p><strong>No automatic public storage.</strong> Images are analysed from a temporary upload, which is removed before the OpenAI request. Nothing is added to the Media Library. OpenAI receives the image with response storage disabled; its separate retention policies still apply.</p><p>Only explicit visitor opt-in saves the image and suggestions in the admin-only review queue. Reviews cannot be published from that queue. Older auto-post and post-status settings are no longer used. Previously published reports have not been changed.</p></div>
        <p><a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=kfi_review')); ?>">Review shared submissions</a></p>
        <form method="post" action="options.php">
            <?php settings_fields('gpt_font_id'); ?>
            <table class="form-table" role="presentation">
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
    </div>
    <script>
    jQuery(function ($) {
        $('#gpt-test-api').on('click', function () {
            const button = $(this).prop('disabled', true);
            $('#gpt-test-result').text('Checking…');
            $.ajax({url: ajaxurl, method: 'POST', timeout: 15000, data: {
                action: 'gpt_test_api_key', nonce: <?php echo wp_json_encode(wp_create_nonce('gpt_test_api_nonce')); ?>
            }}).done(function (response) {
                $('#gpt-test-result').text(response.success ? 'Saved key can access the configured model.' : 'Could not confirm model access. Check the saved key and account.');
            }).fail(function () { $('#gpt-test-result').text('The check could not complete. Try again.'); })
              .always(function () { button.prop('disabled', false); });
        });
    });
    </script>
    <?php
}

add_action('wp_ajax_gpt_test_api_key', function () {
    if (!current_user_can('manage_options')) { wp_send_json_error('Not permitted.', 403); }
    check_ajax_referer('gpt_test_api_nonce', 'nonce');
    $key = get_option('gpt_font_id_api_key', '');
    if (!$key) { wp_send_json_error('No key configured.'); }
    // Read-only model access check; no paid generation or uploaded content.
    $response = wp_remote_get('https://api.openai.com/v1/models/gpt-4o', [
        'timeout' => 10, 'headers' => ['Authorization' => 'Bearer ' . $key],
    ]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) { wp_send_json_success('OK'); }
    wp_send_json_error('Could not confirm model access.');
});
