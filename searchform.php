<?php
/**
 * The template for displaying search forms in Kreativ Font
 */
?>
<div class="kreativ-search kreativ-inline-search">
    <form method="get" class="kreativ-search-form kreativ-search-form-inline" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search" aria-label="Search Kreativ Font">
        <span class="kreativ-search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input
            id="kreativ-inline-search"
            type="search"
            name="s"
            value="<?php echo esc_attr( get_search_query() ); ?>"
            maxlength="128"
            placeholder="<?php esc_attr_e( 'Search fonts, styles, foundries, or tools', 'kreativ' ); ?>"
            aria-label="Search Kreativ Font"
            autocomplete="off"
            class="form-control form-control-sm kreativ-search-input">
        <button type="submit" class="kreativ-search-submit"><?php esc_html_e( 'Search', 'kreativ' ); ?></button>
    </form>
    <div class="kreativ-search-suggestions" hidden aria-live="polite"></div>
</div>
