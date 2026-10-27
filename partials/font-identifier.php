<?php
// Kept outside the shared page/tool heroes so future hero changes cannot restore one here.
?>
<div class="kreativ-page-shell kreativ-identifier-page">
    <nav class="kfi-page-nav" aria-label="Font tools">
        <a href="<?php echo esc_url( home_url( '/tools' ) ); ?>">&larr; All font tools</a>
        <a href="<?php echo esc_url( home_url( '/identified-fonts' ) ); ?>">Recent identifications</a>
    </nav>
    <h1 class="kfi-page-title">Font Identifier</h1>
    <p class="kfi-page-intro">Find possible fonts from an image. Upload a clear sample, then compare the matches.</p>
    <?php echo do_shortcode( '[gpt_font_identifier]' ); ?>
    <details class="kfi-tips">
        <summary>Tips for a better match</summary>
        <ul>
            <li>Crop to one clear line of text and remove unrelated graphics.</li>
            <li>Use a sharp image with upright letters and good contrast.</li>
            <li>Compare distinctive letters and spacing. Suggestions are not verified identifications.</li>
        </ul>
    </details>
    <?php edit_post_link( 'Edit this tool', '<p class="kfi-edit-link">', '</p>' ); ?>
</div>
