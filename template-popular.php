<?php
/*
Template Name: Popular
*/
?>
<?php get_header(); ?>

<div id="page">

	<h1><?php the_title(); ?></h1>

	<div class="page_column">

		<h2>Most popular articles</h2>

		<?php if (function_exists('get_most_viewed')): ?>

			<ol class="page_column_list">

			<?php get_most_viewed_category(1,'post', 500); ?>

			</ol>

		<?php endif; ?>

	</div>

	<div class="page_column">

		<h2>Most popular fonts</h2>

		<?php if (function_exists('get_most_viewed_category')): ?>

			<ol class="page_column_list">

			<?php get_most_viewed_category(3, 'post', 500); ?>

			</ol>

		<?php endif; ?>

	</div>

	<div class="page_column">

		<h2>Most popular freebies</h2>

		<?php if (function_exists('get_most_viewed_category')): ?>

			<ol class="page_column_list">

			<?php get_most_viewed_category(1519, 'post', 500); ?>

			</ol>

		<?php endif; ?>

	</div>
	
	<div class="page_column">

		<h2>Most popular tags</h2>

			<?php wp_tag_cloud('number=1000'); ?>

	</div>	

</div>

<?php get_footer();