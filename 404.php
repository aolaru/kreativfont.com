<?php get_header(); ?>

<main id="primary" class="site-main container-fluid error-404 not-found text-center py-5">
    <div class="center">
        <h1 class="heading-h1 mb-3">Nothing found</h1>
        <p>Hi! We’re sorry, but the information you’re looking for doesn’t exist or may have been moved.</p>
        <p>You can use the search field below to find it, or <a href="<?php echo esc_url( home_url('/') ); ?>"><strong>return to the home page</strong></a>.</p>

        <div class="my-4">
            <?php get_search_form(); ?>
			<br/>
        </div>

        <p class="text-muted mt-4">Thank you for visiting KREATIV!</p>
    </div>
    <hr class="my-5" />

    <section class="suggested-posts text-center">
        <h2 class="heading-h1 mb-4">Explore More Creatives</h2>
        <div class="row justify-content-center">

            <?php
            // Suggested posts (latest 8 posts)
            $suggested_args = array(
                'post_type'      => 'post',
                'posts_per_page' => 8,
                'ignore_sticky_posts' => true,
            );

            $suggested_query = new WP_Query($suggested_args);

            if ( $suggested_query->have_posts() ) :
                while ( $suggested_query->have_posts() ) : $suggested_query->the_post();
			
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
            else :
                echo '<p>No posts available right now.</p>';
            endif;

            wp_reset_postdata();
            ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>
