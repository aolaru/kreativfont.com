<?php
/*
Template Name: Links
*/
?>

<?php get_header(); ?>

<div id="content">

	<h1><?php the_title(); ?></h1>
	<ul>
		<?php wp_list_bookmarks(); ?>
	</ul>
	
</div>

<?php get_footer();
