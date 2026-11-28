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

        $is_credit = in_array( $branch_key, array( 'designer', 'foundry' ), true );
        $status = $is_credit ? get_post_meta( $post_id, '_kreativ_' . $branch_key . '_credit_status', true ) : '';
        $fix = 'Assign at least one ' . strtolower( $label ) . ' branch term.';
        if ( $is_credit ) {
            $fix = 'unavailable' === $status ? 'No reliable credit identified; review again when new evidence is available.' : 'Not yet verified. Check the original font or publisher before assigning a credit.';
            if ( ! empty( $terms ) && 'verified' !== $status ) {
                $fix = 'Assigned, but not yet verified against a source.';
            }
        }
        $checks[] = array(
            'label'  => $label,
            'passed' => ! empty( $terms ) && ( ! $is_credit || 'verified' === $status ),
            'deferred' => $is_credit && empty( $terms ) && 'unavailable' === $status,
            'fix'    => $fix,
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

    $total = count( array_filter( $checks, static function ( $check ) { return empty( $check['deferred'] ); } ) );
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

        .kreativ-font-quality-item.is-deferred .kreativ-font-quality-status { background: #646970; }

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
            <div class="kreativ-font-quality-item<?php echo ! empty( $check['passed'] ) ? ' is-passed' : ( ! empty( $check['deferred'] ) ? ' is-deferred' : '' ); ?>">
                <span class="kreativ-font-quality-status" aria-hidden="true">
                    <?php echo ! empty( $check['passed'] ) ? esc_html( 'OK' ) : ( ! empty( $check['deferred'] ) ? esc_html( '-' ) : esc_html( '!' ) ); ?>
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

function kreativ_add_credit_review_meta_box() {
    add_meta_box( 'kreativ-credit-review', 'Designer and Foundry Credits', 'kreativ_render_credit_review_meta_box', 'post', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'kreativ_add_credit_review_meta_box' );

function kreativ_render_credit_review_meta_box( $post ) {
    wp_nonce_field( 'kreativ_save_credit_review', 'kreativ_credit_nonce' );
    foreach ( array( 'designer' => 'Designer', 'foundry' => 'Foundry' ) as $branch => $label ) {
        $status = get_post_meta( $post->ID, '_kreativ_' . $branch . '_credit_status', true );
        $source = get_post_meta( $post->ID, '_kreativ_' . $branch . '_credit_source', true );
        $parents = kreativ_get_font_branch_parent_term_ids( $branch );
        $assigned = wp_list_pluck( kreativ_get_post_category_branch_terms( $post, $branch ), 'term_id' );
        ?>
        <fieldset class="kreativ-credit-admin-branch" data-credit-admin-branch="<?php echo esc_attr( $branch ); ?>" data-credit-admin-parents="<?php echo esc_attr( implode( ',', $parents ) ); ?>" data-credit-admin-assigned="<?php echo esc_attr( implode( ',', $assigned ) ); ?>">
            <legend><strong><?php echo esc_html( $label ); ?></strong></legend>
            <div data-credit-admin-finder hidden>
                <label>Search existing <?php echo esc_html( strtolower( $label ) ); ?> terms <input type="search" class="widefat" data-credit-admin-search autocomplete="off"></label>
                <div class="kreativ-credit-admin-results" data-credit-admin-results aria-live="polite"></div>
            </div>
            <p><label>Review status <select name="kreativ_credit_status[<?php echo esc_attr( $branch ); ?>]">
                <option value="unreviewed"<?php selected( $status, 'unreviewed' ); ?>>Not yet verified</option>
                <option value="verified"<?php selected( $status, 'verified' ); ?>>Verified against source</option>
                <option value="unavailable"<?php selected( $status, 'unavailable' ); ?>>No reliable credit identified</option>
            </select></label></p>
            <p><label>Verification source URL <input type="url" class="widefat" name="kreativ_credit_source[<?php echo esc_attr( $branch ); ?>]" value="<?php echo esc_attr( $source ); ?>"></label></p>
        </fieldset>
        <?php
    }
    echo '<p>Google Fonts and marketplaces may be sources rather than foundries. Leave a credit unassigned when evidence is insufficient. After changing assignments, save first and then record verification.</p>';
}

function kreativ_save_credit_review( $post_id ) {
    if ( ! isset( $_POST['kreativ_credit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kreativ_credit_nonce'] ) ), 'kreativ_save_credit_review' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    $statuses = isset( $_POST['kreativ_credit_status'] ) && is_array( $_POST['kreativ_credit_status'] ) ? wp_unslash( $_POST['kreativ_credit_status'] ) : array();
    $sources = isset( $_POST['kreativ_credit_source'] ) && is_array( $_POST['kreativ_credit_source'] ) ? wp_unslash( $_POST['kreativ_credit_source'] ) : array();
    foreach ( array( 'designer', 'foundry' ) as $branch ) {
        $status = isset( $statuses[ $branch ] ) && is_scalar( $statuses[ $branch ] ) ? sanitize_key( $statuses[ $branch ] ) : 'unreviewed';
        if ( ! in_array( $status, array( 'unreviewed', 'verified', 'unavailable' ), true ) ) {
            $status = 'unreviewed';
        }
        $source = isset( $sources[ $branch ] ) && is_scalar( $sources[ $branch ] ) ? esc_url_raw( $sources[ $branch ], array( 'http', 'https' ) ) : '';
        $terms = kreativ_get_post_category_branch_terms( $post_id, $branch );
        if ( ! empty( $GLOBALS['kreativ_credit_terms_changed'][ $post_id ][ $branch ] ) || ( 'verified' === $status && ( empty( $terms ) || ! $source || ! wp_http_validate_url( $source ) ) ) || ( 'unavailable' === $status && ! empty( $terms ) ) ) {
            $status = 'unreviewed';
        }
        update_post_meta( $post_id, '_kreativ_' . $branch . '_credit_status', $status );
        update_post_meta( $post_id, '_kreativ_' . $branch . '_credit_source', $source );
    }
}
add_action( 'save_post_post', 'kreativ_save_credit_review' );

function kreativ_invalidate_changed_credit_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    if ( 'category' !== $taxonomy || 'post' !== get_post_type( $object_id ) || wp_is_post_revision( $object_id ) ) {
        return;
    }
    $before = array( 'designer' => array(), 'foundry' => array() );
    $after = $before;
    $new_tt_ids = $append ? array_unique( array_merge( (array) $old_tt_ids, (array) $tt_ids ) ) : (array) $tt_ids;
    foreach ( array( 'before' => (array) $old_tt_ids, 'after' => $new_tt_ids ) as $side => $term_taxonomy_ids ) {
        foreach ( $term_taxonomy_ids as $tt_id ) {
            $term = get_term_by( 'term_taxonomy_id', (int) $tt_id, 'category' );
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
            $branch = kreativ_get_branch_key_for_category_term( $term );
            if ( isset( $before[ $branch ] ) && ! in_array( (int) $term->term_id, kreativ_get_font_branch_parent_term_ids( $branch ), true ) ) {
                ${$side}[ $branch ][] = (int) $term->term_id;
            }
        }
    }
    foreach ( array_keys( $before ) as $branch ) {
        sort( $before[ $branch ] );
        sort( $after[ $branch ] );
        if ( $before[ $branch ] !== $after[ $branch ] ) {
            $GLOBALS['kreativ_credit_terms_changed'][ $object_id ][ $branch ] = true;
            update_post_meta( $object_id, '_kreativ_' . $branch . '_credit_status', 'unreviewed' );
        }
    }
}
add_action( 'set_object_terms', 'kreativ_invalidate_changed_credit_terms', 10, 6 );

function kreativ_credit_term_website_field( $term ) {
    if ( ! in_array( kreativ_get_branch_key_for_category_term( $term ), array( 'designer', 'foundry' ), true ) ) {
        return;
    }
    $website = get_term_meta( $term->term_id, '_kreativ_credit_website', true );
    echo '<tr class="form-field"><th scope="row"><label for="kreativ-credit-website">Official website</label></th><td>' . wp_nonce_field( 'kreativ_save_credit_website', 'kreativ_credit_website_nonce', true, false ) . '<input type="url" id="kreativ-credit-website" name="kreativ_credit_website" value="' . esc_attr( $website ) . '"><p class="description">Use the designer or foundry website, not a marketplace listing.</p></td></tr>';
}
add_action( 'category_edit_form_fields', 'kreativ_credit_term_website_field' );

function kreativ_save_credit_term_website( $term_id ) {
    if ( ! isset( $_POST['kreativ_credit_website_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kreativ_credit_website_nonce'] ) ), 'kreativ_save_credit_website' ) || ! current_user_can( 'manage_categories' ) || ! in_array( kreativ_get_branch_key_for_category_term( $term_id ), array( 'designer', 'foundry' ), true ) ) {
        return;
    }
    $website = isset( $_POST['kreativ_credit_website'] ) && is_scalar( $_POST['kreativ_credit_website'] ) ? esc_url_raw( wp_unslash( $_POST['kreativ_credit_website'] ), array( 'http', 'https' ) ) : '';
    update_term_meta( $term_id, '_kreativ_credit_website', $website );
}
add_action( 'edited_category', 'kreativ_save_credit_term_website' );

function kreativ_enqueue_credit_admin( $hook ) {
    $post_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'post' === get_post_type();
    $categories = 'edit-tags.php' === $hook && isset( $_GET['taxonomy'] ) && 'category' === sanitize_key( wp_unslash( $_GET['taxonomy'] ) );
    if ( ! $post_editor && ! $categories ) {
        return;
    }
    wp_enqueue_script( 'kreativ-credit-admin', get_template_directory_uri() . '/js/kreativ-credit-admin.js', array(), kreativ_asset_version( '/js/kreativ-credit-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'kreativ_enqueue_credit_admin' );
