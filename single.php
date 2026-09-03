<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php
$content_kind = kreativ_get_single_content_kind( get_post() );

if ( 'font' !== $content_kind ) {
    kreativ_render_partial(
        'partials/single-content.php',
        array(
            'post'         => get_post(),
            'content_kind' => $content_kind,
        )
    );
    continue;
}

$page_summary = kreativ_get_content_summary( get_post(), 24, false );
$font_credits = kreativ_get_font_credit_data( get_post() );
$primary_cat  = kreativ_get_single_font_eyebrow( get_post() );
$hero_image_data = has_post_thumbnail() ? wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' ) : false;
$hero_image      = $hero_image_data ? $hero_image_data[0] : '';
$share_url    = rawurlencode( get_permalink() );
$share_title  = rawurlencode( get_the_title() );
$share_email_subject = rawurlencode( get_the_title() . ' | Kreativ Font' );
$share_email_body    = rawurlencode( get_the_title() . "\n\n" . get_permalink() );
$share_thumb  = $hero_image ? rawurlencode( $hero_image ) : '';
$taxonomy_groups = kreativ_get_single_taxonomy_groups( get_post() );
$residual_tags   = kreativ_get_single_residual_tags( get_post() );
$breadcrumb_items = kreativ_get_single_breadcrumb_items( get_post() );
$primary_taxonomy_groups = array_intersect_key( $taxonomy_groups, array_flip( array( 'font_style', 'designer', 'foundry' ) ) );
$secondary_taxonomy_groups = array_diff_key( $taxonomy_groups, $primary_taxonomy_groups );
$related_collections = kreativ_get_single_related_font_collections( get_post(), 4 );
$primary_action      = kreativ_get_single_font_primary_action_data( get_post() );
$font_facts          = kreativ_get_single_font_facts( get_post() );
$research_item        = kreativ_get_font_research_board_item( get_post() );
$pairing_tool_url     = home_url( '/tools/kreativ-font-pairing-tools/' );

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
                    <img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" width="<?php echo esc_attr( $hero_image_data[1] ?? 900 ); ?>" height="<?php echo esc_attr( $hero_image_data[2] ?? 600 ); ?>" class="kreativ-single-hero-image">
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="kreativ-page-content">
        <nav class="kreativ-post-nav" aria-label="Post navigation">
            <div><?php previous_post_link( '%link', '<i class="fa-solid fa-arrow-left"></i> Older' ); ?></div>
            <div><?php next_post_link( '%link', 'Newer <i class="fa-solid fa-arrow-right"></i>' ); ?></div>
        </nav>

        <p class="kreativ-post-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
            <?php foreach ( $breadcrumb_items as $breadcrumb_item ) : ?>
                <span>/</span>
                <a href="<?php echo esc_url( $breadcrumb_item['url'] ); ?>"><?php echo esc_html( $breadcrumb_item['label'] ); ?></a>
            <?php endforeach; ?>
        </p>

        <?php if ( ! empty( $primary_action ) ) : ?>
            <aside class="kreativ-single-quick-download kreativ-single-quick-download-<?php echo esc_attr( $primary_action['type'] ); ?>">
                <div>
                    <span class="kreativ-single-quick-label">
                        <i class="<?php echo esc_attr( $primary_action['icon'] ); ?>" aria-hidden="true"></i>
                        <?php echo esc_html( $primary_action['eyebrow'] ); ?>
                    </span>
                    <h2><?php echo esc_html( $primary_action['title'] ); ?></h2>
                    <p><?php echo esc_html( $primary_action['copy'] ); ?></p>
                </div>

                <div class="kreativ-single-quick-actions">
                    <?php if ( ! empty( $primary_action['primary_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $primary_action['primary_url'] ); ?>" class="kreativ-font-cta-button" target="_blank" rel="<?php echo esc_attr( $primary_action['primary_rel'] ); ?>">
                            <?php echo esc_html( $primary_action['primary_label'] ); ?>
                            <i class="<?php echo esc_attr( $primary_action['primary_icon'] ); ?>" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $primary_action['secondary_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $primary_action['secondary_url'] ); ?>" class="kreativ-font-cta-secondary"<?php echo ! empty( $primary_action['secondary_blank'] ) ? ' target="_blank"' : ''; ?><?php echo ! empty( $primary_action['secondary_rel'] ) ? ' rel="' . esc_attr( $primary_action['secondary_rel'] ) . '"' : ''; ?>>
                            <?php echo esc_html( $primary_action['secondary_label'] ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </aside>
        <?php endif; ?>

        <?php if ( ! empty( $font_facts ) || ! empty( $research_item ) ) : ?>
            <section class="kreativ-font-decision-panel" aria-labelledby="kreativ-font-decision-title">
                <div class="kreativ-font-decision-head">
                    <div>
                        <span class="kreativ-single-quick-label"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Font facts</span>
                        <h2 id="kreativ-font-decision-title">Useful context before you choose</h2>
                    </div>

                    <?php if ( ! empty( $research_item ) ) : ?>
                        <button type="button" class="kreativ-research-save" data-kreativ-save-font="<?php echo esc_attr( wp_json_encode( $research_item ) ); ?>" aria-pressed="false">
                            <i class="fa-regular fa-bookmark" aria-hidden="true"></i>
                            <span>Save to board</span>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $font_facts ) ) : ?>
                    <dl class="kreativ-font-facts">
                        <?php foreach ( $font_facts as $fact ) : ?>
                            <div>
                                <dt><?php echo esc_html( $fact['label'] ); ?></dt>
                                <dd><?php echo esc_html( $fact['value'] ); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>

                <div class="kreativ-research-board" data-kreativ-research-board data-pairing-url="<?php echo esc_url( $pairing_tool_url ); ?>">
                    <div class="kreativ-research-board-head">
                        <span><i class="fa-solid fa-book-bookmark" aria-hidden="true"></i> Research board</span>
                        <button type="button" class="kreativ-research-clear" data-kreativ-research-clear hidden>Clear board</button>
                    </div>
                    <p class="kreativ-research-empty" data-kreativ-research-empty>Save fonts here to keep a shortlist while you browse.</p>
                    <ol class="kreativ-research-list" data-kreativ-research-list></ol>
                    <a class="kreativ-research-compare" data-kreativ-research-compare hidden><i class="fa-solid fa-object-group" aria-hidden="true"></i><span>Compare saved fonts</span></a>
                </div>
            </section>
        <?php endif; ?>

        <article class="kreativ-post-content">
            <?php the_content(); ?>
        </article>

        <?php if ( ! empty( $related_collections ) ) : ?>
            <section class="kreativ-single-collections" aria-labelledby="kreativ-single-collections-title">
                <div class="kreativ-single-collections-head">
                    <span class="kreativ-single-collections-label">
                        <i class="fa-solid fa-compass" aria-hidden="true"></i>
                        More ways to browse
                    </span>
                    <h2 id="kreativ-single-collections-title">Related font collections</h2>
                    <p>Continue from this font into focused collections built from style, mood, use case, and licensing context.</p>
                </div>

                <div class="kreativ-single-collections-grid">
                    <?php foreach ( $related_collections as $collection ) : ?>
                        <a href="<?php echo esc_url( $collection['url'] ); ?>" class="kreativ-single-collection-card">
                            <span class="kreativ-single-collection-icon">
                                <i class="<?php echo esc_attr( $collection['icon'] ); ?>" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong><?php echo esc_html( $collection['title'] ); ?></strong>
                                <small><?php echo esc_html( $collection['copy'] ); ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

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
                    <span aria-live="polite">Copy Link</span>
                </button>
            </div>
        </div>

        <div class="kreativ-post-footer">
            <?php if ( ! empty( $primary_taxonomy_groups ) ) : ?>
                <div class="kreativ-post-taxonomy-grid kreativ-post-taxonomy-grid-primary">
                    <?php foreach ( $primary_taxonomy_groups as $group ) : ?>
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

            <?php if ( ! empty( $secondary_taxonomy_groups ) || $residual_tags ) : ?>
                <div class="kreativ-post-secondary-discovery">
                    <?php if ( ! empty( $secondary_taxonomy_groups ) ) : ?>
                        <div class="kreativ-post-taxonomy-grid kreativ-post-taxonomy-grid-secondary">
                            <?php foreach ( $secondary_taxonomy_groups as $group ) : ?>
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
                            <div class="kreativ-post-tags-heading">
                                <span class="kreativ-post-tags-label">Additional tags</span>
                                <p class="kreativ-post-tags-note">Secondary labels kept for legacy browsing and extra context.</p>
                            </div>
                            <div class="kreativ-post-tag-list">
                                <?php foreach ( $residual_tags as $post_tag ) : ?>
                                    <a href="<?php echo esc_url( get_tag_link( $post_tag ) ); ?>" class="kreativ-post-tag-chip">
                                        <?php echo esc_html( $post_tag->name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
