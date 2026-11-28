<?php

class WP_Post { public $ID; public function __construct( $id ) { $this->ID = $id; } }
$GLOBALS['test_meta'] = array();
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function get_post( $post = null ) { return $post instanceof WP_Post ? $post : ( $post ? new WP_Post( $post ) : null ); }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['test_meta'][ $id ][ $key ] ?? ''; }
function metadata_exists( $type, $id, $key ) { return array_key_exists( $key, $GLOBALS['test_meta'][ $id ] ?? array() ); }
function update_post_meta( $id, $key, $value ) { $GLOBALS['test_meta'][ $id ][ $key ] = $value; }
function get_term_by( $field, $value, $taxonomy ) { return in_array( $value, array( 'fonts', 'free-fonts' ), true ) ? (object) array( 'slug' => $value ) : false; }
function is_wp_error( $value ) { return false; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function current_user_can( ...$args ) { return true; }
function wp_is_post_revision( $id ) { return false; }
function wp_is_post_autosave( $id ) { return false; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim( $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ); }
function esc_url_raw( $value, $protocols = null ) { return filter_var( $value, FILTER_VALIDATE_URL ) ? $value : ''; }
function wp_http_validate_url( $value ) { return filter_var( $value, FILTER_VALIDATE_URL ); }

require __DIR__ . '/../inc/font-taxonomy.php';
require __DIR__ . '/../inc/admin.php';
require __DIR__ . '/../inc/collections.php';

function expect_same( $expected, $actual, $label ) {
    if ( $expected !== $actual ) {
        fwrite( STDERR, $label . ': expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . "\n" );
        exit( 1 );
    }
}

expect_same( 'unknown', kreativ_get_font_usage_rights( 1 ), 'unreviewed default' );
$GLOBALS['test_meta'][1]['kreativ_font_availability'] = 'personal_use';
expect_same( 'personal_use', kreativ_get_font_usage_rights( 1 ), 'legacy Cocobella override' );
$_POST = array( 'kreativ_font_license_nonce' => 'valid', 'kreativ_font_rights' => 'personal_use', 'kreativ_font_license_source' => '' );
kreativ_save_font_license( 1 );
expect_same( 'personal_use', kreativ_get_font_usage_rights( 1 ), 'preserve legacy setting on save' );
$_POST['kreativ_font_rights'] = 'commercial_use';
kreativ_save_font_license( 1 );
expect_same( 'unknown', kreativ_get_font_usage_rights( 1 ), 'source required for verified rights' );
$_POST['kreativ_font_license_source'] = 'https://example.com/license';
$_POST['kreativ_font_license_checked'] = '2026-02-30';
kreativ_save_font_license( 1 );
expect_same( 'commercial_use', kreativ_get_font_usage_rights( 1 ), 'verified commercial rights' );
expect_same( '', get_post_meta( 1, '_kreativ_font_license_checked', true ), 'invalid review date' );
$_POST['kreativ_font_license_nonce'] = 'invalid';
$_POST['kreativ_font_rights'] = 'personal_use';
kreativ_save_font_license( 1 );
expect_same( 'commercial_use', kreativ_get_font_usage_rights( 1 ), 'invalid nonce does not save' );
$query = kreativ_get_dynamic_font_collection_query_args( array( 'free_mode' => 'include', 'usage_rights' => 'commercial_use' ) );
expect_same( 'commercial_use', $query['meta_query'][0]['value'], 'commercial collection query' );
$query = kreativ_get_font_archive_rights_meta_query( array( 'usage_rights' => 'personal_use' ) );
expect_same( 'OR', $query[0]['relation'], 'personal-use filter includes legacy values' );
expect_same( array(), kreativ_get_font_archive_rights_meta_query( array( 'usage_rights' => 'invalid' ) ), 'invalid filter ignored' );
echo "Font licensing checks passed.\n";
