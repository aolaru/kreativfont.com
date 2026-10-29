<?php
$tools = kreativ_get_tool_catalogue();
$tool_ids = get_posts( array(
    'post_type' => array( 'post', 'page' ),
    'post_status' => 'publish',
    'post_name__in' => array_keys( $tools ),
    'posts_per_page' => count( $tools ),
    'fields' => 'ids',
) );
$guides = new WP_Query( array(
    'post_type' => 'post', 'post_status' => 'publish', 'category_name' => 'tools',
    'post__not_in' => $tool_ids, 'posts_per_page' => 3, 'no_found_rows' => true,
) );
?>
<div class="kreativ-tools-directory">
    <header class="kreativ-tool-heading">
        <div class="kreativ-tool-eyebrow">Font tools</div>
        <h1>Browse Font Tools</h1>
        <p>Identify a typeface, find a pairing, name a family, or make expressive text. Choose the tool for the job.</p>
        <span class="kreativ-tools-count"><?php echo esc_html( count( $tools ) ); ?> free tools</span>
    </header>
    <section class="kreativ-tools-catalogue" aria-label="Choose a font tool">
        <?php foreach ( $tools as $tool_slug => $tool ) : ?>
            <a class="kreativ-tool-card" href="<?php echo esc_url( home_url( '/tools/' . $tool_slug ) ); ?>">
                <i class="fa-solid <?php echo esc_attr( $tool['icon'] ); ?>" aria-hidden="true"></i>
                <span class="kreativ-tool-eyebrow"><?php echo esc_html( $tool['task'] ); ?></span>
                <h2><?php echo esc_html( $tool['title'] ); ?></h2>
                <p><?php echo esc_html( $tool['summary'] ); ?></p>
                <span class="kreativ-tool-card-action"><?php echo esc_html( $tool['action'] ); ?> <span aria-hidden="true">→</span></span>
            </a>
        <?php endforeach; ?>
    </section>
    <?php if ( $guides->have_posts() ) : ?>
        <section class="kreativ-tool-guides" aria-labelledby="tool-guides-title">
            <h2 id="tool-guides-title">Guides and tutorials</h2>
            <p>Practical walkthroughs to help you get more from the tools.</p>
            <?php while ( $guides->have_posts() ) : $guides->the_post(); ?>
                <a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?> <span aria-hidden="true">→</span></a>
            <?php endwhile; wp_reset_postdata(); ?>
        </section>
    <?php endif; ?>
</div>
