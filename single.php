<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$page_summary = kreativ_get_content_summary( get_post(), 24 );
$font_credits = kreativ_get_font_credit_data( get_post() );
$categories   = get_the_category();
$primary_cat  = kreativ_get_single_font_eyebrow( get_post() );
$hero_image   = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
$share_url    = rawurlencode( get_permalink() );
$share_title  = rawurlencode( get_the_title() );
$share_email_subject = rawurlencode( get_the_title() . ' | Kreativ Font' );
$share_email_body    = rawurlencode( get_the_title() . "\n\n" . get_permalink() );
$share_thumb  = $hero_image ? rawurlencode( $hero_image ) : '';
$taxonomy_groups = kreativ_get_single_taxonomy_groups( get_post() );
$residual_tags   = kreativ_get_single_residual_tags( get_post() );

if ( $page_summary && preg_match( '/view\\s*&?\\s*purchase|important notice/i', $page_summary ) ) {
    $page_summary = '';
}
?>

<div class="kreativ-page-shell">
    <section class="kreativ-page-hero <?php echo $hero_image ? 'kreativ-single-hero' : 'kreativ-page-hero-compact'; ?>">
        <div class="kreativ-page-hero-main">
            <div class="kreativ-page-eyebrow">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <?php echo esc_html( $primary_cat ); ?>
            </div>

            <h1 class="kreativ-page-title"><?php the_title(); ?></h1>

            <?php if ( ! empty( $page_summary ) ) : ?>
                <p class="kreativ-page-summary"><?php echo esc_html( wp_strip_all_tags( $page_summary ) ); ?></p>
            <?php endif; ?>

            <div class="kreativ-page-badges">
                <?php if ( ! empty( $font_credits['designer'] ) ) : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-pen-nib"></i> <?php echo esc_html( 'Designer: ' . $font_credits['designer'] ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $font_credits['foundry'] ) ) : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-building"></i> <?php echo esc_html( 'Foundry: ' . $font_credits['foundry'] ); ?></span>
                <?php endif; ?>
                <?php if ( empty( $font_credits['designer'] ) && empty( $font_credits['foundry'] ) ) : ?>
                    <span class="kreativ-page-badge"><i class="fa-solid fa-calendar"></i> <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $hero_image ) : ?>
            <div class="kreativ-page-hero-side kreativ-single-hero-media">
                <div class="kreativ-single-hero-image-frame">
                    <img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="kreativ-single-hero-image">
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="kreativ-page-content">
        <nav class="kreativ-post-nav" aria-label="Post navigation">
            <div><?php previous_post_link( '%link', '<i class="fa-solid fa-arrow-left"></i> Newer' ); ?></div>
            <div><?php next_post_link( '%link', 'Older <i class="fa-solid fa-arrow-right"></i>' ); ?></div>
        </nav>

        <p class="kreativ-post-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <span>/</span>
            <?php the_category( ' / ' ); ?>
        </p>

        <article class="kreativ-post-content">
            <?php the_content(); ?>
        </article>

        <div class="kreativ-share-bar">
            <span class="kreativ-share-label">Share</span>
            <div class="kreativ-share-actions">
                <a href="<?php echo esc_url( 'https://www.pinterest.com/pin/create/button/?url=' . $share_url . '&description=' . $share_title . ( $share_thumb ? '&media=' . $share_thumb : '' ) ); ?>" class="kreativ-share-button" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-pinterest-p" aria-hidden="true"></i>
                    <span>Pinterest</span>
                </a>
                <a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_title ); ?>" class="kreativ-share-button" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
                    <span>X</span>
                </a>
                <a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url ); ?>" class="kreativ-share-button" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                    <span>Facebook</span>
                </a>
                <a href="<?php echo esc_url( 'mailto:?subject=' . $share_email_subject . '&body=' . $share_email_body ); ?>" class="kreativ-share-button">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <span>Email</span>
                </a>
                <button type="button" class="kreativ-share-button kreativ-share-copy" data-share-url="<?php echo esc_url( get_permalink() ); ?>">
                    <i class="fa-solid fa-link" aria-hidden="true"></i>
                    <span>Copy Link</span>
                </button>
            </div>
        </div>

        <div class="kreativ-post-footer">
            <?php if ( ! empty( $taxonomy_groups ) ) : ?>
                <div class="kreativ-post-taxonomy-grid">
                    <?php foreach ( $taxonomy_groups as $group ) : ?>
                        <section class="kreativ-post-taxonomy-group">
                            <span class="kreativ-post-taxonomy-label">
                                <i class="<?php echo esc_attr( $group['icon'] ); ?>" aria-hidden="true"></i>
                                <?php echo esc_html( $group['label'] ); ?>
                            </span>
                            <div class="kreativ-post-taxonomy-pills">
                                <?php foreach ( $group['terms'] as $term ) : ?>
                                    <a href="<?php echo esc_url( $term['url'] ); ?>" class="kreativ-post-taxonomy-pill">
                                        <?php echo esc_html( $term['name'] ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( $residual_tags ) : ?>
                <div class="kreativ-post-tags">
                    <span class="kreativ-post-tags-label">Tags</span>
                    <div class="kreativ-post-tag-list">
                        <?php foreach ( $residual_tags as $post_tag ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $post_tag ) ); ?>" class="kreativ-post-tag-chip">
                                <?php echo esc_html( $post_tag->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="kreativ-post-meta">
                <span class="kreativ-post-meta-item">
                    <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
                </span>
                <?php edit_post_link( 'Edit', '<span class="kreativ-post-meta-item kreativ-post-meta-edit">', '</span>' ); ?>
            </div>
        </div>

    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kreativ-share-copy').forEach(function (button) {
        button.addEventListener('click', async function () {
            var url = button.getAttribute('data-share-url');
            if (!url) {
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(url);
                } else {
                    var tempInput = document.createElement('input');
                    tempInput.value = url;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                }

                var originalText = button.querySelector('span').textContent;
                button.querySelector('span').textContent = 'Copied';
                window.setTimeout(function () {
                    button.querySelector('span').textContent = originalText;
                }, 1500);
            } catch (error) {
                window.open(url, '_blank', 'noopener');
            }
        });
    });
});
</script>

<?php endwhile; else : ?>
    <div class="kreativ-page-shell">
        <section class="kreativ-page-content">
            <div class="kreativ-empty-state">
                <h2>Sorry, no articles matched your criteria.</h2>
                <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kreativ-hero-cta kreativ-hero-cta-primary">Back to Home</a></p>
            </div>
        </section>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
