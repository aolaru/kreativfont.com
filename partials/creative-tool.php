<?php
$tool_config = kreativ_get_creative_tool_config( $post ?? get_post() );
if ( ! $tool_config ) {
    return;
}
$research_pair = 'pairing' === $tool_config['kind'] && function_exists( 'kreativ_get_requested_font_research_pair' )
    ? kreativ_get_requested_font_research_pair() : array();
?>
<div class="kreativ-creative-tool" data-tool-kind="<?php echo esc_attr( $tool_config['kind'] ); ?>">
    <header class="kreativ-tool-heading">
        <nav class="kreativ-tool-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span>
            <a href="<?php echo esc_url( home_url( '/tools' ) ); ?>">Font tools</a>
        </nav>
        <div class="kreativ-tool-eyebrow"><i class="fa-solid <?php echo esc_attr( $tool_config['icon'] ); ?>" aria-hidden="true"></i> <?php echo esc_html( $tool_config['task'] ); ?></div>
        <h1><?php echo esc_html( get_the_title( $post ) ); ?></h1>
        <p><?php echo esc_html( $tool_config['summary'] ); ?></p>
    </header>
    <div class="kreativ-tool-workspace">
        <article class="kreativ-tool-app" aria-label="<?php echo esc_attr( $tool_config['title'] ); ?>">
            <?php if ( $research_pair ) : ?>
                <section class="kreativ-tool-selection" aria-label="Fonts selected on your research board">
                    <h2>Selected fonts</h2>
                    <?php foreach ( $research_pair as $pair_font ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $pair_font ) ); ?>"><?php echo esc_html( get_the_title( $pair_font ) ); ?></a>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
            <?php the_content(); ?>
        </article>
        <aside class="kreativ-tool-guide" aria-label="Tips for this tool">
            <span class="kreativ-tool-eyebrow">A good starting point</span>
            <h2><?php echo esc_html( $tool_config['input'] ); ?></h2>
            <p><?php echo esc_html( $tool_config['hint'] ); ?></p>
            <p class="kreativ-tool-note"><?php echo esc_html( $tool_config['note'] ); ?></p>
            <a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>">Explore the font library <span aria-hidden="true">→</span></a>
        </aside>
    </div>
    <nav class="kreativ-tool-more" aria-label="More font tools">
        <span>Try another tool</span>
        <?php foreach ( kreativ_get_tool_catalogue() as $tool_slug => $other_tool ) : ?>
            <?php if ( $other_tool['title'] !== $tool_config['title'] ) : ?>
                <a href="<?php echo esc_url( home_url( '/tools/' . $tool_slug ) ); ?>"><?php echo esc_html( $other_tool['title'] ); ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>
