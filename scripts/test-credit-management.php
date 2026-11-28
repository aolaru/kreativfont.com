<?php
// Isolated checks for category-backed credits; no WordPress database required.
class WP_Post { public $ID; public function __construct( $id ) { $this->ID = $id; } }
function add_action( ...$args ) {}
function get_post( $post ) { return $post instanceof WP_Post ? $post : new WP_Post( (int) $post ); }
function is_wp_error( $value ) { return false; }
function get_term_by( $field, $slug, $taxonomy ) {
    if ( 'term_taxonomy_id' === $field ) return (object) array( 'term_id' => (int) $slug );
    $ids = array( 'designer' => 1, 'foundry' => 2, 'font-style' => 3, 'font-mood' => 4, 'font-use-case' => 5 );
    return isset( $ids[ $slug ] ) ? (object) array( 'term_id' => $ids[ $slug ] ) : false;
}
function get_term( $term, $taxonomy = '' ) { return is_object( $term ) ? $term : (object) array( 'term_id' => (int) $term ); }
function get_post_type( $id ) { return 'post'; }
function get_terms( $args ) {
    if ( isset( $args['fields'] ) ) return array();
    $count = 1 === $args['parent'] ? 120 : 3;
    if ( ! empty( $args['number'] ) ) $count = min( $count, $args['number'] );
    $terms = array();
    for ( $i = 1; $i <= $count; $i++ ) $terms[] = (object) array( 'term_id' => $args['parent'] * 1000 + $i, 'slug' => 'term-' . $i, 'name' => 'Term ' . $i );
    return $terms;
}
function get_the_terms( $id, $taxonomy ) { return $GLOBALS['test_terms'] ?? array(); }
function get_ancestors( $id, $taxonomy, $type ) { return array( intdiv( (int) $id, 1000 ) ); }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['test_meta'][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['test_meta'][ $key ] = $value; }
function get_post_field( $field, $id ) { return ''; }
function has_post_thumbnail( $id ) { return true; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return (string) $value; }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) ); }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function current_user_can( ...$args ) { return $GLOBALS['test_can_edit'] ?? true; }
function wp_is_post_revision( $id ) { return false; }
function wp_is_post_autosave( $id ) { return false; }
function esc_url_raw( $url, $protocols ) { return preg_match( '~^https?://~', $url ) ? $url : ''; }
function wp_http_validate_url( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : false; }
function check( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); }
require dirname( __DIR__ ) . '/inc/font-taxonomy.php';
require dirname( __DIR__ ) . '/inc/admin.php';

$facets = kreativ_get_font_archive_facets( 5 );
check( count( $facets['designer']['terms'] ) === 120, 'All designers must be available, not only the first page.' );
check( count( $facets['foundry']['terms'] ) === 3, 'Foundries remain available.' );

$GLOBALS['test_terms'] = array( (object) array( 'term_id' => 1001 ), (object) array( 'term_id' => 2001 ) );
$GLOBALS['test_meta'] = array();
$_POST = array( 'kreativ_credit_nonce' => 'valid', 'kreativ_credit_status' => array( 'designer' => 'verified', 'foundry' => 'verified' ), 'kreativ_credit_source' => array( 'designer' => 'https://example.com/source', 'foundry' => 'javascript:alert(1)' ) );
kreativ_save_credit_review( 42 );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'verified', 'An assigned credit with a valid source can be verified.' );
check( $GLOBALS['test_meta']['_kreativ_foundry_credit_status'] === 'unreviewed', 'Invalid source cannot verify a foundry.' );
$GLOBALS['test_can_edit'] = false;
$_POST['kreativ_credit_status']['designer'] = 'unavailable';
kreativ_save_credit_review( 42 );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'verified', 'Unauthorized saves do not modify credits.' );
$GLOBALS['test_can_edit'] = true;
$GLOBALS['test_terms'] = array();
$_POST['kreativ_credit_status']['designer'] = 'verified';
$_POST['kreativ_credit_status']['foundry'] = 'unavailable';
kreativ_save_credit_review( 42 );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'unreviewed', 'A missing designer cannot be marked verified.' );
check( $GLOBALS['test_meta']['_kreativ_foundry_credit_status'] === 'unavailable', 'No reliable foundry can be recorded explicitly.' );
$GLOBALS['test_terms'] = array( (object) array( 'term_id' => 1001 ) );
$GLOBALS['kreativ_credit_terms_changed'][42]['designer'] = true;
kreativ_save_credit_review( 42 );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'unreviewed', 'Changed assignments require a fresh review even without JavaScript.' );
unset( $GLOBALS['kreativ_credit_terms_changed'] );
$GLOBALS['test_meta']['_kreativ_designer_credit_status'] = 'verified';
kreativ_invalidate_changed_credit_terms( 42, array(), array( 1001, 2001 ), 'category', false, array( 1, 1001, 2001 ) );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'verified', 'Removing a branch root must not invalidate its unchanged credit.' );
kreativ_invalidate_changed_credit_terms( 42, array(), array( 1002, 2001 ), 'category', false, array( 1001, 2001 ) );
check( $GLOBALS['test_meta']['_kreativ_designer_credit_status'] === 'unreviewed', 'Replacing a designer invalidates its verification.' );
check( empty( $GLOBALS['kreativ_credit_terms_changed'][42]['foundry'] ), 'An unchanged foundry remains verified.' );
echo "PASS: complete credit facets and protected credit-review saves.\n";
