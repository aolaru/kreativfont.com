<?php

function kreativ_font_cta_shortcode( $atts = array(), $content = null, $shortcode_tag = 'kreativ_font_cta' ) {
    $atts = shortcode_atts(
        array(
            'type'            => 'kreativ_font_download' === $shortcode_tag ? 'free' : 'commercial',
            'url'             => '',
            'label'           => '',
            'title'           => '',
            'note'            => '',
            'secondary_url'   => '',
            'secondary_label' => '',
        ),
        $atts,
        'kreativ_font_cta'
    );

    $type = sanitize_key( $atts['type'] );
    $type = in_array( $type, array( 'commercial', 'free' ), true ) ? $type : 'commercial';
    $url  = esc_url_raw( $atts['url'] );

    if ( '' === $url ) {
        return '';
    }

    $is_free = 'free' === $type;
    $title   = sanitize_text_field( $atts['title'] );
    $label   = sanitize_text_field( $atts['label'] );
    $note    = sanitize_text_field( $atts['note'] );

    if ( null !== $content && '' === $note ) {
        $note = wp_strip_all_tags( do_shortcode( $content ) );
    }

    if ( '' === $title ) {
        $title = $is_free ? 'Free font download' : 'Official font source';
    }

    if ( '' === $label ) {
        $label = $is_free ? 'Download Free Font' : 'View / Purchase Font';
    }

    if ( '' === $note ) {
        $note = $is_free
            ? 'Check the included license before using this font in commercial work.'
            : 'Use the official marketplace or foundry source for legal personal or commercial licensing.';
    }

    $secondary_url   = esc_url_raw( $atts['secondary_url'] );
    $secondary_label = sanitize_text_field( $atts['secondary_label'] );
    $primary_rel     = $is_free ? 'nofollow noopener' : 'nofollow sponsored noopener';

    if ( '' === $secondary_url && ! $is_free ) {
        $secondary_url   = add_query_arg( 'font_filter', 'free', home_url( '/fonts' ) );
        $secondary_label = 'Browse free fonts';
    }

    if ( '' === $secondary_label ) {
        $secondary_label = 'Browse similar fonts';
    }

    ob_start();
    ?>
    <aside class="kreativ-font-cta kreativ-font-cta-<?php echo esc_attr( $type ); ?>">
        <div class="kreativ-font-cta-main">
            <span class="kreativ-font-cta-eyebrow">
                <i class="fa-solid <?php echo $is_free ? 'fa-download' : 'fa-cart-shopping'; ?>" aria-hidden="true"></i>
                <?php echo esc_html( $is_free ? 'Free download' : 'License this font' ); ?>
            </span>
            <h2><?php echo esc_html( $title ); ?></h2>
            <p><?php echo esc_html( $note ); ?></p>
        </div>

        <div class="kreativ-font-cta-actions">
            <a href="<?php echo esc_url( $url ); ?>" class="kreativ-font-cta-button" target="_blank" rel="<?php echo esc_attr( $primary_rel ); ?>">
                <?php echo esc_html( $label ); ?>
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>

            <?php if ( $secondary_url ) : ?>
                <a href="<?php echo esc_url( $secondary_url ); ?>" class="kreativ-font-cta-secondary">
                    <?php echo esc_html( $secondary_label ); ?>
                </a>
            <?php endif; ?>
        </div>
    </aside>
    <?php

    return trim( ob_get_clean() );
}
add_shortcode( 'kreativ_font_cta', 'kreativ_font_cta_shortcode' );
add_shortcode( 'kreativ_font_download', 'kreativ_font_cta_shortcode' );

function kreativ_adsense_shortcode( $atts = array() ) {
    $atts = shortcode_atts(
        array(
            'slot'   => '',
            'format' => 'auto',
            'layout' => '',
            'class'  => '',
        ),
        $atts,
        'kreativ_adsense'
    );

    $slot = preg_replace( '/[^0-9]/', '', (string) $atts['slot'] );

    if ( '' === $slot ) {
        return '';
    }

    $format = sanitize_key( $atts['format'] );
    $format = $format ? $format : 'auto';
    $layout = sanitize_key( $atts['layout'] );
    $class  = sanitize_html_class( $atts['class'] );

    ob_start();
    ?>
    <aside class="kreativ-ad-slot <?php echo esc_attr( $class ); ?>" aria-label="Advertisement">
        <span class="kreativ-ad-label">Advertisement</span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-4706844277814411"
             data-ad-slot="<?php echo esc_attr( $slot ); ?>"
             data-ad-format="<?php echo esc_attr( $format ); ?>"
             <?php if ( $layout ) : ?>
                data-ad-layout="<?php echo esc_attr( $layout ); ?>"
             <?php endif; ?>
             data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </aside>
    <?php

    return trim( ob_get_clean() );
}
add_shortcode( 'kreativ_adsense', 'kreativ_adsense_shortcode' );
