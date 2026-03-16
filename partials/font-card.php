<?php
$title_tag = isset( $title_tag ) && in_array( $title_tag, array( 'h2', 'h3', 'h4' ), true ) ? $title_tag : 'h3';
?>
<div class="<?php echo esc_attr( trim( $column_classes . ' ' . $animation_class ) ); ?>">
    <div class="kreativ-font-card">
        <a href="<?php echo esc_url( $permalink ); ?>">
            <div class="kreativ-card-media">
                <?php if ( ! empty( $badge_text ) ) : ?>
                    <span class="kf-badge kf-badge-<?php echo esc_attr( $badge_slug ); ?>">
                        <?php echo esc_html( $badge_text ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( ! empty( $show_new_badge ) ) : ?>
                    <span class="kf-badge-new"><?php echo esc_html( $new_label ); ?></span>
                <?php endif; ?>

                <img class="lazyload"
                    loading="lazy"
                    decoding="async"
                    alt="<?php echo esc_attr( $title_attr ); ?>"
                    data-src="<?php echo esc_url( $thumb_url ); ?>"
                    src="<?php echo esc_url( $loading_thumb_url ); ?>" />
            </div>

            <<?php echo esc_html( $title_tag ); ?>><?php echo esc_html( $title ); ?></<?php echo esc_html( $title_tag ); ?>>
        </a>
    </div>
</div>
