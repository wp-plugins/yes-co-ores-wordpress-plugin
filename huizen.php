<?php
/*
Template Name: Portfolio
*/
?>
<?php get_header(); ?>
	<div id="content">
	<?php 
		$loop = new WP_Query(array('post_type' => 'portfolio', 'posts_per_page' => 10)); 
	?>
	<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
	<?php	
		$custom = get_post_custom($post->ID);
		$screenshot_url = $custom["screenshot_url"][0];
		$website_url = $custom["website_url"][0];
	?>
        <div id="portfolio-item">
		<h1><?php the_title(); ?></h1>
		<a href="<?=$website_url?>"><?php the_post_thumbnail(); ?> </a>
		<?php the_content(); ?>
	</div>
        <?php endwhile; ?>  
        </div><!-- #content -->  
<?php get_footer(); ?>