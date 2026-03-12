<?php get_header(); ?>

<div id="page">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
	<h1><?php the_title(); ?></h1>
		
		<?php edit_post_link('Edit','',''); ?>
		<?php the_content('Read the rest of this page &larr;'); ?>
				
	<?php endwhile; endif; ?>

</div>

<?php get_footer();