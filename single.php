<?php get_header(); ?>

<!-- Post Navigation Arrows -->
<div class="post_nav_next">
    <?php previous_post_link('%link', '<i class="fas fa-chevron-right"></i>'); ?>
</div>

<div class="post_nav_prev">
    <?php next_post_link('%link', '<i class="fas fa-chevron-left"></i>'); ?>
</div>

<main id="post" class="container-fluid my-5">

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <h1 class="heading-h1 mb-4"><?php the_title(); ?></h1>

    <!-- Breadcrumb -->
    <div class="col-lg-7 col-md-9 col-sm-12 center-margin">
        <p class="breadcrumb" style="font-size: 0.7em; text-align: left;">
            <a href="<?php echo esc_url(home_url('/')); ?>" title="Home">Home</a> › 
            <?php the_category(' › '); ?>
        </p>
    </div>

    <!-- Main Post Content -->
    <div class="post_content mb-5">
        <div class="col-lg-7 col-md-9 col-sm-12 center-margin">
            <?php the_content(); ?>
        </div>
    </div>

    <!-- Suggested Posts -->
    <section class="related-posts mt-5">
        <div class="col-lg-10 col-md-11 col-sm-12 center-margin text-center">
            <h2 class="heading-h2 mb-4">You Might Also Like</h2>
            <div class="row justify-content-center">
                <?php
                $related_args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 4,
                    'post__not_in' => array(get_the_ID()),
                    'ignore_sticky_posts' => true,
                    'orderby' => 'rand',
                    'category__in' => wp_get_post_categories(get_the_ID()),
                );
                $related_query = new WP_Query($related_args);

                if ( $related_query->have_posts() ) :
                    while ( $related_query->have_posts() ) : $related_query->the_post();
 					
					$image_medium = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
					
					$image_src = $image_medium[0] ?? get_template_directory_uri() . '/img/default-thumb.png';
				
                ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        
						<div class="kreativ-font-card">
							
                        	<a href="<?php echo esc_url( get_permalink() ); ?>" class="kreativ-card-title" title="<?php the_title_attribute(); ?>">
                            <strong><?php the_title(); ?></strong>
								<img
								  class="card-img-top lazyload"
								  data-src="<?php echo esc_url($image_src); ?>"
								  src="<?php echo esc_url(get_template_directory_uri() . '/img/loading.gif'); ?>"
								  alt="<?php the_title_attribute(); ?>"
								  loading="lazy"
								/>
                        	</a>
                        </div>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else:
                    echo '<p>No related posts found.</p>';
                endif;
                ?>
            </div>
        </div>
    </section>	

    <!-- Meta + Tags -->
    <div class="post_content">
        <div class="col-lg-7 col-md-9 col-sm-12 center-margin">

            <?php if ( get_the_tags() ) : ?>
                <div class="post_tags mb-3">
                    <strong>Tags:</strong> <?php the_tags('', ', ', ''); ?>
                </div>
            <?php endif; ?>

            <div class="post_meta text-muted mb-4">
                Published on <time datetime="<?php echo get_the_date('c'); ?>"><?php the_time('F j, Y'); ?></time>
                in <?php the_category(', '); ?> by <?php the_author(); ?>.
                <?php edit_post_link('Edit', ' | ', ''); ?>
            </div>
        </div>
    </div>

<?php endwhile; else: ?>

    <div class="text-center py-5">
        <h2>Sorry, no articles matched your criteria.</h2>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary mt-3">Back to Home</a>
    </div>

<?php endif; ?>

</main>

<?php get_footer(); ?>
