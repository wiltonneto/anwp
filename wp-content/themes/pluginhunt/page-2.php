<?php
get_header(); ?>


<div class='col-md-8 col-md-offset-2 page-2'>
			<?php
				// Start the Loop.
				while ( have_posts() ) : the_post();
					// Include the page content template.
					get_template_part( 'content', 'page' );
				endwhile;
			?>
<div class='disclaimer'><?php echo of_get_option('ph_hunt_disclaimer'); ?></div>
</div>


<?php
get_footer();
