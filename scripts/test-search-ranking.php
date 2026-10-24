<?php

// Run the real ranking paths with deterministic candidates instead of a WP DB.
define( 'MINUTE_IN_SECONDS', 60 );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function remove_accents( $text ) { return strtr( $text, array( 'é' => 'e', 'É' => 'E' ) ); }
function get_option( $key ) { return 'test-version'; }
function wp_json_encode( $value ) { return json_encode( $value ); }
function get_transient( $key ) { return false; }
function set_transient( ...$args ) {}
function is_wp_error( $value ) { return false; }
function kreativ_get_font_branch_parent_term_ids( $branch ) { return array( 1 ); }
function get_terms( $args ) { return array( get_term( 20 ) ); }
function get_ancestors( ...$args ) { return array( 1 ); }
function get_term( $id, ...$args ) { return (object) array( 'term_id' => $id, 'name' => 'Inter', 'slug' => 'inter' ); }
function get_posts( $args ) { return array( 1 ); } // Give the newer variant every taxonomy boost.
function get_the_title( $post ) { return is_object( $post ) ? $post->title : $GLOBALS['fixtures'][ $post ]->title; }
function kreativ_get_single_content_kind( $post ) { return $post->kind; }
function kreativ_is_font_post( $id ) { return $GLOBALS['fixtures'][ $id ]->kind === 'font'; }
class WP_Query {
    public $posts;
    public function __construct( $args ) {
        $this->posts = ( $args['fields'] ?? '' ) === 'ids'
            ? array_keys( $GLOBALS['fixtures'] ) : array_values( $GLOBALS['fixtures'] );
    }
    public function have_posts() { return ! empty( $this->posts ); }
}
require dirname( __DIR__ ) . '/inc/search.php';
function check_ranking( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
}

foreach ( array(
    array( 'Inter Font Free Download (Commercial Use)', 'Inter' ),
    array( 'Inter Tight Font Free Download (Commercial Use)', 'Inter Tight' ),
    array( 'Dary – modern serif with contrast', 'Dary' ),
    array( 'Dary &#8211; modern serif with contrast', 'Dary' ),
    array( 'Dary — modern serif with contrast', 'Dary' ),
    array( 'Dary - modern serif with contrast', 'Dary' ),
    array( 'Trade-Gothic – editorial sans', 'Trade-Gothic' ),
    array( 'R&amp;D – modern sans', 'R&D' ),
    array( "  INTER\u{00A0}Font   Free Download (Commercial Use)  ", ' inter ' ),
    array( 'Café Font', 'cafe' ),
) as list( $title, $query ) ) {
    check_ranking( kreativ_get_search_title_match_score( $title, $query ) === 1800, "Exact font name: {$title}" );
}
check_ranking( kreativ_get_search_title_match_score( 'Inter Tight Font Free Download (Commercial Use)', 'Inter' ) === 1200, 'Inter Tight remains a prefix match' );
check_ranking( kreativ_get_search_title_match_score( 'Interlink', 'Inter' ) === 450, 'Interlink remains a substring match' );
check_ranking( kreativ_get_search_title_match_score( 'Winter', 'Inter' ) === 450, 'Preserve partial discovery' );
check_ranking( kreativ_get_search_title_match_score( 'Inter', '' ) === 0, 'Empty query' );
check_ranking( kreativ_get_search_title_match_score( 'Inter', 'Roboto' ) === 0, 'Unrelated query' );

$fixtures = array(
    1 => (object) array( 'title' => 'Inter Tight Font Free Download (Commercial Use)', 'kind' => 'font' ),
    2 => (object) array( 'title' => 'Inter', 'kind' => 'tool' ),
    3 => (object) array( 'title' => 'Interlink', 'kind' => 'font' ),
);
// The exact font is older than many competing prefix matches.
for ( $i = 4; $i < 120; $i++ ) {
    $fixtures[ $i ] = (object) array( 'title' => 'Inter Variant ' . $i, 'kind' => 'font' );
}
$fixtures[120] = (object) array( 'title' => 'Inter Font Free Download (Commercial Use)', 'kind' => 'font' );
$suggestions = kreativ_get_font_title_suggestions( 'Inter' );
check_ranking( $suggestions[0] === $fixtures[120], 'Autocomplete ranks the older exact family first' );
check_ranking( ! in_array( $fixtures[2], $suggestions, true ), 'Autocomplete excludes non-font content' );
$results = kreativ_get_structured_search_post_ids( 'Inter' );
check_ranking( $results[0] === 120, 'Search exact family wins over recency and all taxonomy boosts' );
check_ranking( ! in_array( 2, $results, true ), 'Search excludes non-font content' );
$tight = kreativ_get_font_title_suggestions( 'Inter Tight' );
check_ranking( $tight[0] === $fixtures[1], 'Explicit variant query ranks that variant first' );
check_ranking( kreativ_get_structured_search_post_ids( '' ) === array(), 'Empty search returns no candidates' );
echo "PASS: exact family names, title labels/subtitles, partial matches, variant queries, autocomplete, search, and content eligibility\n";
