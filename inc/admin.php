<?php

function kreativ_handle_showcase_conversion() {
    if ( ! isset( $_GET['convert_showcases'] ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You are not allowed to convert showcase posts.', 'kreativfont' ) );
    }

    check_admin_referer( 'kreativ_convert_showcases' );

    $query = new WP_Query(
        array(
            'post_type'      => 'kreativ_showcase',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        )
    );

    $count = 0;

    foreach ( $query->posts as $post ) {
        wp_update_post(
            array(
                'ID'        => $post->ID,
                'post_type' => 'post',
            )
        );
        $count++;
    }

    echo "<div class='notice notice-success'><p>Converted {$count} showcase posts to regular posts.</p></div>";
}
add_action( 'admin_init', 'kreativ_handle_showcase_conversion' );

function kreativ_get_font_post_quality_checks( $post_id ) {
    $content = (string) get_post_field( 'post_content', $post_id );
    $checks  = array(
        array(
            'label'  => 'Font library category',
            'passed' => function_exists( 'kreativ_is_font_post' ) && kreativ_is_font_post( $post_id ),
            'fix'    => 'Assign Fonts or Free Fonts so this post can appear in the font library.',
        ),
        array(
            'label'  => 'Featured image',
            'passed' => has_post_thumbnail( $post_id ),
            'fix'    => 'Add a strong font preview image.',
        ),
    );

    $branch_checks = array(
        'font_style'    => 'Style',
        'designer'      => 'Designer',
        'foundry'       => 'Foundry',
        'font_mood'     => 'Mood',
        'font_use_case' => 'Use Case',
    );

    foreach ( $branch_checks as $branch_key => $label ) {
        $terms = function_exists( 'kreativ_get_post_category_branch_terms' )
            ? kreativ_get_post_category_branch_terms( $post_id, $branch_key )
            : array();

        $checks[] = array(
            'label'  => $label,
            'passed' => ! empty( $terms ),
            'fix'    => 'Assign at least one ' . strtolower( $label ) . ' branch term.',
        );
    }

    $has_cta = (bool) preg_match( '/\\[kreativ_font_(?:cta|download)\\b/i', $content );

    $checks[] = array(
        'label'  => 'CTA or download shortcode',
        'passed' => $has_cta,
        'fix'    => 'Add [kreativ_font_cta] or [kreativ_font_download].',
    );

    return $checks;
}

function kreativ_add_font_quality_meta_box() {
    add_meta_box(
        'kreativ-font-quality',
        'Kreativ Font Quality',
        'kreativ_render_font_quality_meta_box',
        'post',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'kreativ_add_font_quality_meta_box' );

function kreativ_render_font_quality_meta_box( $post ) {
    $checks = kreativ_get_font_post_quality_checks( $post->ID );
    $passed = 0;

    foreach ( $checks as $check ) {
        if ( ! empty( $check['passed'] ) ) {
            ++$passed;
        }
    }

    $total = count( $checks );
    ?>
    <style>
        .kreativ-font-quality-summary {
            margin: 0 0 10px;
            font-weight: 700;
        }

        .kreativ-font-quality-list {
            display: grid;
            gap: 8px;
            margin: 0;
        }

        .kreativ-font-quality-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 7px;
            align-items: start;
            margin: 0;
            padding: 8px;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            background: #fff;
        }

        .kreativ-font-quality-item.is-passed {
            border-color: #b8e6c8;
            background: #f3fbf6;
        }

        .kreativ-font-quality-status {
            width: 24px;
            height: 18px;
            border-radius: 50%;
            background: #d63638;
            color: #fff;
            font-size: 12px;
            line-height: 18px;
            text-align: center;
        }

        .kreativ-font-quality-item.is-passed .kreativ-font-quality-status {
            background: #008a20;
        }

        .kreativ-font-quality-fix {
            display: block;
            margin-top: 2px;
            color: #646970;
            font-size: 12px;
            line-height: 1.35;
        }
    </style>

    <p class="kreativ-font-quality-summary">
        <?php echo esc_html( sprintf( '%d of %d checks passed.', $passed, $total ) ); ?>
    </p>

    <div class="kreativ-font-quality-list">
        <?php foreach ( $checks as $check ) : ?>
            <div class="kreativ-font-quality-item<?php echo ! empty( $check['passed'] ) ? ' is-passed' : ''; ?>">
                <span class="kreativ-font-quality-status" aria-hidden="true">
                    <?php echo ! empty( $check['passed'] ) ? esc_html( 'OK' ) : esc_html( '!' ); ?>
                </span>
                <span>
                    <strong><?php echo esc_html( $check['label'] ); ?></strong>
                    <?php if ( empty( $check['passed'] ) ) : ?>
                        <span class="kreativ-font-quality-fix"><?php echo esc_html( $check['fix'] ); ?></span>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
