<?php
/*
Template Name: Glossary
*/
?>
<?php get_header(); ?>

<div id="page">

	<h1><?php the_title(); ?></h1>

	<div class="page_column">
		<h2>Articles in alphabetic order</h2>
		<ul class="page_column_list">
			<?php wp_get_archives('type=alpha'); ?> 
		</ul>
	</div>

	<div class="page_column">
		<h2>Articles by category</h2>
		<ul class="page_column_list">
			<?php wp_list_categories('show_count=1&title_li=');?>
		</ul>
		
		<h2>Articles by month</h2>
		<ul class="page_column_list">
			<?php wp_get_archives('type=monthly'); ?>
		</ul>
	</div>

	<div class="page_column">
		<h2>Articles by tags</h2>
			<?php wp_tag_cloud('smallest=8&largest=22&number=10000'); ?>
	</div>

</div>

<?php get_footer();