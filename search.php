<?php get_header(); ?>

    <div class="container-fluid">

        <div class="text-center">

            <h2 class="heading-h2">No results for “<?php echo esc_html(get_search_query()); ?>”</h2>

        </div>

        <div class="row">

			<?php if (have_posts()) : while (have_posts()) : the_post();

			$post_id = $post->ID;
			
			$image_medium = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
					
			$image_src = $image_medium[0] ?? get_template_directory_uri() . '/img/default-thumb.png';
			
			?>
            <div class="col-md-4 col-lg-3 col-sm-6">

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

		<?php endwhile; ?>

		<?php else : ?>

        </div>

		<p class="text-center"> We couldn’t find any matching fonts. Try another keyword or explore our <a href="https://kreativfont.com/fonts">Popular Fonts</a> collection.</p>

		<?php endif; ?>

    </div>

<?php get_footer();