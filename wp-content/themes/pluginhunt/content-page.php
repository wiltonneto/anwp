<?php
/**
 * The template used for displaying page content
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if(!wp_is_mobile()){ ?>
	<div class="entry-content entry-content-2">
	<?php }else{ ?>
	<div class="entry-content">
		<?php
	}
			the_content();
		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
