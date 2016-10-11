
	
	<?php if( of_get_option('mailchimp_showhidden') == 1 && !$IsSubscribed) { ?>
	<div class='mailchimp-sub'>
		<h2><?php echo of_get_option('mc_sidebar_title','Plugins direct to your inbox') ?></h2>
		<?php 
			$action = of_get_option('mailchimp_action_hidden');
			echo ph_mailchimp($action);
		?>
	</div>
	<?php } ?>

	<div class='right-sidebar-widget'>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div>
