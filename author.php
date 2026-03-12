<?php get_header(); ?>

<div id="blog">

	<div id="post">
	
		<div class="post_category">

		<!-- This sets the $curauth variable -->

    <?php
    $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
    ?>

    <h2>About <?php the_author(); ?></h2>
    <dl>
        <dt>Website</dt>
        <dd><a href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->user_url; ?></a></dd>
        <dt>Profile</dt>
        <dd><?php echo $curauth->user_description; ?></dd>
    </dl>

    <h2>Articles written by <?php the_author(); ?></h2>

    <ul>
	
	<!-- The Loop -->

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	
        <li>
            <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>">
            <?php the_title(); ?></a>,
            <?php the_time('d M Y'); ?> in <?php the_category('&');?>
        </li>

    <?php endwhile; else: ?>

        <p><?php echo 'No posts by this author.'; ?></p>

    <?php endif; ?>
    <br/>
    </ul>

	<!-- End Loop -->

		<div id="post_nav">
		
			<?php if (function_exists( 'wp_pagenavi' )) : wp_pagenavi();
				  
				  else : ?>
					
					<div class="post_nav_previous"><?php next_posts_link(' &larr; Older articles ') ?></div>
		
					<div class="post_nav_next"><?php previous_posts_link(' Newer articles &rarr; ') ?></div>
					
			<?php endif; ?>
			
		</div>		
	
		</div>
	
	</div>

</div>

<?php get_footer(); ?>