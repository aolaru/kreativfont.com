<?php
// Development router: php -S 127.0.0.1:8767 -t . scripts/tools-preview.php
// Original plugin clients run against sample responses; this never calls live AI.
if ( PHP_SAPI !== 'cli-server' ) { http_response_code( 404 ); exit; }
$preview_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$preview_runtime = getenv( 'KREATIV_TOOLS_NODE_MODULES' ) ?: __DIR__ . '/tools/node_modules';
if ( '/_tool-fixture/jquery.js' === $preview_path ) {
    header( 'Content-Type: text/javascript' );
    readfile( $preview_runtime . '/jquery/dist/jquery.min.js' );
    return;
}
if ( '/_tool-fixture/name' === $preview_path ) {
    header( 'Content-Type: application/json' );
    echo json_encode( array( 'result' => "Velora Serif\nNocturne Text\nArden Display\nSolenne\nPaper Moon\nCinder Sans" ) );
    return;
}
if ( '/_tool-fixture/pairing' === $preview_path ) {
    header( 'Content-Type: application/json' );
    $font = htmlspecialchars( $_POST['font'] ?? 'Your font', ENT_QUOTES, 'UTF-8' );
    $html = '<h3>A companion for ' . $font . '</h3><p>Try a clear text face alongside your display family.</p><h4>Start with the reading experience</h4><p>Keep body text comfortable and give headings room to breathe. Test your actual content before choosing.</p><ul><li>Use a contrasting family for headings.</li><li>Check similar weights at the sizes you need.</li><li>Review language support and licensing.</li></ul>';
    echo json_encode( array( 'success' => true, 'data' => array( 'html' => $html ) ) );
    return;
}
if ( ! in_array( $preview_path, array( '/naming', '/pairing', '/tools' ), true ) ) return false;

class WP_Post {
    public $post_name;
    public function __construct( $slug ) { $this->post_name = $slug; }
}
$preview_kind = trim( $preview_path, '/' );
$post = new WP_Post( 'naming' === $preview_kind ? 'kreativ-font-name-generator' : 'kreativ-font-pairing-tools' );
function get_post( $value = null ) { return $value ?? $GLOBALS['post']; }
function home_url( $path = '/' ) {
    $local_routes = array(
        '/tools' => '/tools',
        '/tools/kreativ-font-name-generator' => '/naming',
        '/tools/kreativ-font-pairing-tools' => '/pairing',
    );
    return $local_routes[ $path ] ?? 'https://kreativfont.com' . $path;
}
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $text ) { return esc_html( $text ); }
function get_the_title( $value = null ) {
    if ( ! empty( $GLOBALS['preview_guide'] ) ) return 'How to Create Fancy Text and PNGs on Android';
    return 'naming' === $GLOBALS['preview_kind'] ? 'Kreativ Font Name Generator' : 'Kreativ Font Pairing Tools';
}
function the_title() { echo esc_html( get_the_title() ); }
function get_permalink( $value = null ) { return home_url( '/blog/kreativ-fancy-text-android-png-tutorial' ); }
function the_content() { readfile( __DIR__ . '/tools/fixtures/' . ( 'naming' === $GLOBALS['preview_kind'] ? 'name' : 'pairing' ) . '.html' ); }
function get_posts( $args ) { return array( 1, 2, 3, 4 ); }
function wp_reset_postdata() { $GLOBALS['preview_guide'] = false; }
class WP_Query {
    private $index = 0;
    public function __construct( $args ) {}
    public function have_posts() { return $this->index < 1; }
    public function the_post() { $this->index++; $GLOBALS['preview_guide'] = true; }
}
require dirname( __DIR__ ) . '/inc/tools.php';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( 'tools' === $preview_kind ? 'Browse Font Tools' : get_the_title() ); ?> — Local preview</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="/assets/dist/main.min.css"><link rel="stylesheet" href="/css/kreativ-header.css">
<?php if ( 'tools' !== $preview_kind ) : ?><link rel="stylesheet" href="/scripts/tools/fixtures/<?php echo 'naming' === $preview_kind ? 'name' : 'pairing'; ?>.css"><?php endif; ?>
<link rel="stylesheet" href="/css/kreativ-tools.css">
<style>
body{margin:0;padding-top:0!important;background:#fff}.tool-preview-header{position:sticky;top:0;z-index:200;height:72px;display:flex;align-items:center;gap:12px;padding:12px 20px;background:#fff;border-bottom:1px solid #dce1ed;color:#172033;font:16px system-ui}.tool-preview-header b{display:grid;place-items:center;width:36px;height:36px;border-radius:9px;background:#0b1120;color:#8675ff;font-size:26px}.tool-preview-header button{margin-left:auto;min-height:44px;padding:8px 12px;border:1px solid #a6aec2;border-radius:9px;background:transparent;color:inherit;font:14px system-ui}.tool-preview-footer{max-width:1120px;margin:0 auto;padding:20px;color:#536078;font:13px/1.5 system-ui}body.dark-mode .tool-preview-header{background:#0b1120;border-color:#33415a;color:#f1f5fb}body.dark-mode .tool-preview-footer{color:#b5bfd1}
</style></head><body>
<header class="tool-preview-header"><b aria-hidden="true">K</b><span>Kreativ Font</span><button type="button" id="preview-theme">Switch to dark mode</button></header>
<main id="content"><?php require dirname( __DIR__ ) . '/partials/' . ( 'tools' === $preview_kind ? 'tools-directory.php' : 'creative-tool.php' ); ?></main>
<footer class="tool-preview-footer">Local preview · Generation uses sample responses. <a href="/tools">Tools</a> · <a href="/pairing">Pairing</a> · <a href="/naming">Naming</a></footer>
<script>document.getElementById('preview-theme').addEventListener('click',function(){var dark=document.body.classList.toggle('dark-mode');this.textContent=dark?'Switch to light mode':'Switch to dark mode';});</script>
<?php if ( 'tools' !== $preview_kind ) : ?>
<script src="/_tool-fixture/jquery.js"></script>
<script>var kfngConfig={restUrl:'/_tool-fixture/name',nonce:'fixture'};var KFM={ajax_url:'/_tool-fixture/pairing',nonce:'fixture',i18n:{loading:'Finding font ideas…',error:'Could not generate suggestions. Try again.'}};</script>
<script src="/scripts/tools/fixtures/<?php echo 'naming' === $preview_kind ? 'name' : 'pairing'; ?>-client.js"></script>
<script src="/js/kreativ-tools.js"></script>
<?php endif; ?></body></html>
