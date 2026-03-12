<?php get_header(); ?>

<div class="post_nav_next">
    <?php next_post_link( '%link', '<i class="icon-angle-right"></i>' ); ?>
</div>
<div class="post_nav_prev">
    <?php previous_post_link( '%link', '<i class="icon-angle-left"></i>' ); ?>
</div>

<div id="post">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<h1 class="heading-h1"><?php the_title(); ?></h1>

	<div class="post_content">
	<div class="col-lg-7 col-md-9 col-sm-12 center-margin">
		<?php the_content('Read the rest of this article &larr;'); ?>
	</div>
		
	<?php
		$basename = basename(get_permalink());
		$search_creativemarket = str_replace('-', '/', $basename);
		$search_myfonts = str_replace('-', ' ', $basename);?>
	<span>You might find a better price for this font on </span>
	<a rel="nofollow" href="//www.kreativfont.com/creativemarket/<?php echo $search_creativemarket; ?>" target="_blank">CreativeMarket</a>,
	<a rel="nofollow" href="//www.kreativfont.com/myfonts/<?php echo $search_myfonts; ?>" target="_blank">MyFonts</a>,
	<a rel="nofollow" href="//fontbundles.net/search/<?php echo $search_myfonts; ?>/rel=tgD63I">FontBundles</a>,
	<a rel="nofollow" href="//www.fontspring.com/search?q=<?php echo $search_myfonts; ?>&refby=kreativ">FontSpring</a> or
	<a rel="nofollow" href="//www.creativefabrica.com/ref/73113/?s=<?php echo $search_myfonts; ?>&post_type=product&campaign=Search">CreativeFabrica</a>

    </div>

    <div class="post_content">
        <div class="col-lg-7 col-md-9 col-sm-12 center-margin">
            <?php if(function_exists('the_ratings')) { echo "<h2>";the_title_attribute();echo " font ratings</h2>"; the_ratings(); echo "<br/>";} ?>
            <div class="post_tags">
                <i class="icon-tags"></i> <?php the_terms( $post->ID, 'download_tag', 'Tags: ', ', ', '' ); ?>
            </div>
            <div class="post_meta">
                <i class="icon-info-circle"></i> A font family published by on <?php the_time('l, F jS, Y') ?> that has <?php if(function_exists('the_views')) { the_views(); } ?>.
			    <?php edit_post_link('Edit','',''); ?>
				<?php 
					$u_time = get_the_time('U'); 
					$u_modified_time = get_the_modified_time('U'); 
					if ($u_modified_time >= $u_time + 86400) {
					echo "<p>Last modified on "; 
					the_modified_time('F jS, Y'); 
					echo " at "; 
					the_modified_time(); 
					echo "</p> "; } ?>
                <?php edit_post_link('Edit','',''); ?>				
            </div>
        </div>
    </div>

    <?php endwhile; else: ?>
    Sorry, no articles matched your criteria.

    <?php endif; ?>

</div>

<?php get_footer();
