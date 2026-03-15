<?php
/**
 * The template for displaying search forms in Kreativ Font
 *
 */
?>
<form method="get" id="searchform" action="<?php echo esc_url(home_url('/')); ?>">

    <input id="s" type="text" placeholder="<?php esc_attr_e('Search', 'kreativ'); ?>" name="s">

</form>
