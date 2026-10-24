<?php
/** Admin-only paired evaluation. Live catalogue algorithm is frozen at 0.8.2. */
defined('ABSPATH') || exit;
add_action('wp_ajax_gpt_font_id_compare', 'gpt_font_id_compare');

function gpt_font_id_compare_metrics($calls, $started)
{
    $metrics = ['model' => 'gpt-4o', 'pipeline_version' => '0.8.2', 'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
        'call_count' => count($calls), 'calls' => $calls, 'estimated_cost_usd' => 0, 'input_tokens' => 0, 'output_tokens' => 0,
        'provider_status' => 'completed', 'http_status' => 200];
    foreach ($calls as $call) {
        foreach (['estimated_cost_usd', 'input_tokens', 'output_tokens'] as $key) {
            $metrics[$key] = $metrics[$key] !== null && isset($call[$key]) ? $metrics[$key] + $call[$key] : null;
        }
        if (($call['http_status'] ?? 0) !== 200) { $metrics['http_status'] = $call['http_status'] ?? 0; }
        if (($call['provider_status'] ?? '') !== 'completed') { $metrics['provider_status'] = $call['provider_status'] ?? ''; }
    }
    if (!$calls) { $metrics['estimated_cost_usd'] = null; }
    return $metrics;
}

function gpt_font_id_compare()
{
    nocache_headers();
    if (!current_user_can('manage_options')) { wp_send_json_error('Not permitted.', 403); }
    if (!check_ajax_referer('gpt_font_id_benchmark_nonce', 'nonce', false)) { wp_send_json_error('Refresh this settings page before benchmarking.', 403); }
    $pipeline = $_POST['pipeline'] ?? '';
    if (!is_string($pipeline) || !in_array($pipeline, ['ai_legacy', 'catalogue_082'], true)) { wp_send_json_error('Unsupported comparison pipeline.'); }
    $image = gpt_font_id_prepare_image($_FILES['font_image'] ?? null);
    if (is_wp_error($image)) { wp_send_json_error($image->get_error_message()); }
    $key = get_option('gpt_font_id_api_key', '');
    if (!$key) { wp_send_json_error('No API key configured.'); }
    $now = time(); $lease = $now . ':' . wp_generate_password(12, false);
    if (!add_option('gpt_font_id_benchmark_lock', $lease, '', false)) {
        if ((int) get_option('gpt_font_id_benchmark_lock', 0) < $now - 180) { delete_option('gpt_font_id_benchmark_lock'); }
        wp_send_json_error('A benchmark request is still running. Wait a moment before retrying.');
    }
    $response = null;
    try {
        // A persistent option survives deployment cache clears. Separate from visitor and model-lab counters.
        $budget_key = 'gpt_font_id_compare_' . gmdate('Ymd', $now);
        $budget = get_option($budget_key, ['calls' => 0, 'estimated_usd' => 0]);
        $reserved_calls = $pipeline === 'catalogue_082' ? 3 : 1;
        $reserve = $pipeline === 'catalogue_082' ? 0.30 : 0.05;
        if ($budget['calls'] + $reserved_calls > 320 || $budget['estimated_usd'] + $reserve > 5.00) {
            $response = new WP_Error('budget', 'The paired benchmark daily guard ($5 estimated / 320 generation requests) has been reached. Export partial results.');
        } else {
            $budget['calls'] += $reserved_calls; $budget['estimated_usd'] += $reserve;
            update_option($budget_key, $budget, false);
            $GLOBALS['gpt_font_id_eval_calls'] = [];
            $started = microtime(true); $metrics = null;
            if ($pipeline === 'ai_legacy') {
                $result = gpt_font_id_request_matches($image, $key, 'gpt-4o', $metrics, ['frozen_ai' => true, 'timeout' => 55]);
            } else {
                $result = gpt_font_id_catalogue_matches($image, $key, 'gpt-4o');
            }
            $calls = $GLOBALS['gpt_font_id_eval_calls'];
            $metrics = gpt_font_id_compare_metrics($calls, $started);
            $metrics['pipeline_version'] = $pipeline === 'ai_legacy' ? '0.7.0' : '0.8.2';
            $budget['calls'] += count($calls) - $reserved_calls;
            if ($metrics['estimated_cost_usd'] !== null) { $budget['estimated_usd'] += $metrics['estimated_cost_usd'] - $reserve; }
            update_option($budget_key, $budget, false);
            $response = ['ok' => !is_wp_error($result), 'pipeline' => $pipeline, 'metrics' => $metrics,
                'candidates' => is_wp_error($result) ? [] : $result['candidates'],
                'note' => is_wp_error($result) ? $result->get_error_message() : $result['note'],
                'catalogue' => is_wp_error($result) ? null : ($result['catalogue'] ?? null),
                'budget' => $budget, 'active_model' => gpt_font_id_active_model()];
        }
    } finally {
        unset($GLOBALS['gpt_font_id_eval_calls']);
        if (get_option('gpt_font_id_benchmark_lock') === $lease) { delete_option('gpt_font_id_benchmark_lock'); }
    }
    if (is_wp_error($response)) { wp_send_json_error($response->get_error_message()); }
    wp_send_json_success($response);
}
