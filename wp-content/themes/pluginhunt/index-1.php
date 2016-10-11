<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 * Lets enhance this and move the epic_reddit_index($agrs) function into this file.
 */

get_header(); 


?>
<div class='ph-layout-1'>

<div class="container-main-head container  side-collapse-container">
	<a id="ph-log-social-new" href="#animatedModal" style="display:none">.</a>

<?php
if( of_get_option('ph_slider_shortcode') != '') { 
echo ph_slider();
}else{
	echo "<div class='noslider'></div>";
}

$IsSubscribed = UserSubscribed();

if( of_get_option('mailchimp_showhidden') == 1 && !$IsSubscribed) { 
$action = of_get_option('mailchimp_action_hidden');
echo ph_mailchimp($action);
}else{
	echo "<div class='nomc'></div>";
}

if( of_get_option('ph_sticky_on') == 1) {
#} new code for the sticky post flydown. Running from the websites featured posts, drawn randomly
 $querystr = "
    SELECT 	* 
    FROM 	$wpdb->postmeta
    WHERE 	meta_key = 'featured-product' 
    AND     meta_value = '1' ORDER BY RAND() LIMIT 1
 ";

 $pageposts = $wpdb->get_results($querystr, OBJECT);
 foreach($pageposts as $ppost){
 	$ID = $ppost->post_id;
    $title = get_the_title( $ID ); 
    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $ID ), 'single-post-thumbnail' );
    $t = $thumb[0];
    $desc = get_post($ID)->post_content;
    $out = get_the_permalink($ID);
  $output = "
<div class='ph-sticky' id='phf'>
    <span class='icon-x'><i class='fa fa-times icon-xy'></i></span>
    <div class='row hunt-row-fp'>
    <a class='title' href='$out'>$title</a>
    <div class='img-featured'><img class='phsi' src='$t'/></div>
    <span class='description'>$desc</span>
  </div>
</div>"; 	
 }

 echo $output;
 wp_reset_query();
}



 ?>


<div class='result'></div>

<?php
do_action('ph-popular-this');
?>

<div class='clear'></div>
<div class='container postlist'>
	<div class='maincontent'>

<?php
	global $wp_query,$post,$wpdb, $current_user,$query_string;
   	wp_get_current_user();
	$wpdb->myo_ip   = $wpdb->prefix . 'epicred';


	#} check that our paged variable is being passed
	 if(isset($_GET["page"])){
		 $paged = ($_GET["page"]) ? $_GET["page"] : 0;
	 }


	#} first query for 20 posts and get the post_date of the last post
	if($paged == 0){
		$query = "SELECT post_date, YEAR(post_date) AS `year`, MONTH(post_date) AS `month`,
	        DAYOFMONTH(post_date) AS `dayofmonth` FROM $wpdb->posts WHERE post_status='publish' AND post_type = 'post' ORDER BY post_date DESC LIMIT 10";
		$first = $wpdb->get_results($query);
		$c =  count($first) - 1;
		$f =  $first[$c]->post_date;
		$tf = substr($f,0,10);
		$date = date('U');
		$d =  $first[$c]->dayofmonth;
		$m =  $first[$c]->month;
		$y =  $first[$c]->year;
		$key = '';
		#} extra check - does the day, month and year match for the first and last post from the 10. If so, then get all posts from that day, month and year
		if($d == $first[0]->dayofmonth && $m == $first[0]->month && $y == $first[0]->year){
		$querystr = "
		    SELECT $wpdb->posts.*, YEAR(post_date) AS `year`,
	        MONTH(post_date) AS `month`,
	        DAYOFMONTH(post_date) AS `dayofmonth`
		    FROM $wpdb->posts, $wpdb->postmeta
		    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
		    AND $wpdb->postmeta.meta_key = 'epicredvote' 
		    AND $wpdb->posts.post_status = 'publish' 
		    AND $wpdb->posts.post_type = 'post'
		    AND DAYOFMONTH(post_date) ='$d'
		    AND MONTH(post_date) = '$m'
		    AND YEAR(post_date) = '$y'
		    GROUP BY ID
		    ORDER BY LEFT($wpdb->posts.post_date, 10) DESC, $wpdb->postmeta.meta_value+0 DESC
		 ";
		}else{
		#} build our first query of posts minimum 20 + the full day in which the 20th post is taken
		$querystr = "
		    SELECT $wpdb->posts.*, YEAR(post_date) AS `year`,
	        MONTH(post_date) AS `month`,
	        DAYOFMONTH(post_date) AS `dayofmonth`
		    FROM $wpdb->posts, $wpdb->postmeta
		    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
		    AND $wpdb->postmeta.meta_key = 'epicredvote' 
		    AND $wpdb->posts.post_status = 'publish' 
		    AND $wpdb->posts.post_type = 'post'
		    AND post_date >= '$tf'
		    GROUP BY ID
		    ORDER BY LEFT($wpdb->posts.post_date, 10) DESC, $wpdb->postmeta.meta_value+0 DESC
		 ";
		}
		 $pageposts = $wpdb->get_results($querystr, OBJECT);


	}else if($paged == 1){
		$d = $_GET['day'];
		$m = $_GET['month'];
		$y = $_GET['year'];
		$key = $_GET['key'];

        $query = "SELECT YEAR(post_date) AS `year`,
                  MONTH(post_date) AS `month`,
                  DAYOFMONTH(post_date) AS `dayofmonth`,
                  count(ID) as posts
                  FROM $wpdb->posts
                  WHERE post_type = 'post'
                  AND post_status = 'publish'
                  GROUP BY YEAR(post_date),
                  MONTH(post_date),
                  DAYOFMONTH(post_date)
                  ORDER BY post_date DESC";
 
        $arcresults = $wpdb->get_results($query);    //this gets the posts grouped by year, month, dayofmonth



		$key = findPrevious($y, $m, $d, $arcresults);

		echo "<div id='epic-key' class='hideme'>" . $key . "</div>";

		$d = $arcresults[$key]->dayofmonth;
		$m = $arcresults[$key]->month;
		$y = $arcresults[$key]->year;


		$querystr = "
		    SELECT $wpdb->posts.*, YEAR(post_date) AS `year`,
	        MONTH(post_date) AS `month`,
	        DAYOFMONTH(post_date) AS `dayofmonth`
		    FROM $wpdb->posts, $wpdb->postmeta
		    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
		    AND $wpdb->postmeta.meta_key = 'epicredvote' 
		    AND $wpdb->posts.post_status = 'publish' 
		    AND $wpdb->posts.post_type = 'post'
		    AND DAYOFMONTH(post_date) ='$d'
		    AND MONTH(post_date) = '$m'
		    AND YEAR(post_date) = '$y'
		    GROUP BY (ID)
		    ORDER BY LEFT($wpdb->posts.post_date, 10) DESC, $wpdb->postmeta.meta_value+0 DESC
		 ";


		 $pageposts = $wpdb->get_results($querystr, OBJECT);


	}else{
        $query = "SELECT YEAR(post_date) AS `year`,
                  MONTH(post_date) AS `month`,
                  DAYOFMONTH(post_date) AS `dayofmonth`,
                  count(ID) as posts
                  FROM $wpdb->posts ".$join."
                  WHERE post_type = 'post'
                  AND post_status = 'publish'
                  GROUP BY YEAR(post_date),
                  MONTH(post_date),
                  DAYOFMONTH(post_date)
                  ORDER BY post_date DESC";
 
        $arcresults = $wpdb->get_results($query);  //this gets the posts grouped by year, month, dayofmonth
		$key = $_GET['key'];

		$d = $arcresults[$key]->dayofmonth;
		$m = $arcresults[$key]->month;
		$y = $arcresults[$key]->year;

		$querystr = "
		    SELECT $wpdb->posts.*, YEAR(post_date) AS `year`,
	        MONTH(post_date) AS `month`,
	        DAYOFMONTH(post_date) AS `dayofmonth`
		    FROM $wpdb->posts, $wpdb->postmeta
		    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
		    AND $wpdb->postmeta.meta_key = 'epicredvote' 
		    AND $wpdb->posts.post_status = 'publish' 
		    AND $wpdb->posts.post_type = 'post'
		    AND DAYOFMONTH(post_date) ='$d'
		    AND MONTH(post_date) = '$m'
		    AND YEAR(post_date) = '$y'
		    GROUP BY (ID)
		    ORDER BY LEFT($wpdb->posts.post_date, 10) DESC, $wpdb->postmeta.meta_value+0 DESC
		 ";
		 $pageposts = $wpdb->get_results($querystr, OBJECT);
	}

    $hide = 1;
    $day_check = '';
    $num_posts = 0;
	$fday = true;

if ($pageposts): ?>
 <?php global $post; ?>
 <?php foreach ($pageposts as $post): ?>
 <?php setup_postdata($post); 

			$num_posts++;
			
			$localDateTime = current_time('mysql');
			
			$DateTimeSplit = explode(' ',$localDateTime);
			$dateParts = explode('-',$DateTimeSplit[0]);
			$localDateMidnight = date("Y-m-d",mktime(0,0,0,$dateParts[1],$dateParts[2],$dateParts[0]));
			$localDateEpoch = strtotime($localDateMidnight);
			$yesterday = strtotime("-1 day", $localDateMidnight);
			$yesterdayEpoch = $localDateEpoch + $yesterday;
			
			$day = get_the_date('j');
			$date = get_the_date('l');
			
			$stamp = get_the_date('U');
			
			if ($stamp >= $localDateEpoch)
	        		$date = __("Today",'pluginhunt');
	    		else if ($stamp >= $yesterdayEpoch)
	        		 $date = __('Yesterday','pluginhunt');


			  if ($day != $day_check) {
				if($fday){
			  		echo "<div class='phday ".$p."'>";
			  	}else{
			  		echo "<div style='clear:both'></div></div><div class='phday'>";
			  	}
			    echo "<div class='timing'><span class='day'>". $date ."</span><span class='date'>" . get_the_date() . "</span></div>";
			    $day_check = $day;
			    $fday = false;
			  }

			 ?> 
				
			<?php if(is_page()){
				
			}else{
			
			$postvote = get_post_meta($post->ID, 'epicredvote' ,true);
			wpeddit_post_ranking($post->ID);

			if($postvote == NULL){
				$postvote = 0;
			}
			
			$fid = $current_user->ID;
	
			$query = "SELECT epicred_option FROM $wpdb->myo_ip WHERE epicred_ip = $fid AND epicred_id = $post->ID";
			$al = $wpdb->get_var($query);
			$c = "none";
			
			if($al == NULL){
				$al = 0;
				$redclassu = 'up';
				$redscore = "unvoted";
				$c = "none";
			}else if($al==1){
				$redclassu = 'upmod';
				$redclassd = 'down';
				$redscore = 'likes';
				$voted = 'yesvote';
				$c = 'blue';
			}else{
				$redclassu = 'upmod';
				$redclassd = 'down';
				$redscore = 'likes';
			}

			if($num_posts > 10 && $paged > 1){
				$blob = 'hidepost hidepost-' . $d . '-'. $m. '-' . $y;
			}else{
				$blob = '';
			}			
			 ?>
			
			<div class = 'row hunt-row <?php echo $blob;?>' style = 'margin-bottom:20px'>
				<div class = "reddit-voting <?php echo $c; ?> reddit-voting-<?php echo $post->ID; ?>">
					<ul class="unstyled">

						<div class="arrow fa fa-caret-up  fa-2x arrow-up-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "<?php echo $redclassd; ?>" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>
						<div class="score score-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?>><?php echo $postvote; ?></div>
		
					</ul>
				</div>	
				<?php
								$plugina = get_post_meta($post->ID,'pluginauthor', true);
								if($plugina ==''){
									$pname = get_the_author_meta('user_nicename');
									$auth = 'yes';
								}else{
									$pname = $plugina;
									$auth = 'no';
								}
                    			$profileUrl = '#'; if (isset($post->ID)) $profileUrl = get_author_posts_url($post->post_author); 
								$url = home_url();

								if(wp_is_mobile()){
									$mob = '-mob';
								}

							$out =  get_post_meta($post->ID, 'outbound', true);
							$n = parse_url($out);
							$phfi = get_post_meta($post->ID,'phfeaturedimage',true);
			?>
			
			<?php if ( has_post_thumbnail() ) {   ?>
						<div class = 'reddit-post pull-left' id='reddit-post-<?php echo $post->ID;?>' data-ph-url="<?php  echo post_permalink( $post->ID ); ?>" data-slug='<?php echo $post->post_name; ?>' data-id='<?php echo $post->ID; ?>' data-url = '<?php echo $url; ?>' data-auth = '<?php echo $auth; ?>' data-rajax = '<?php echo $post->ID; ?>' data-rups = '<?php echo $postvote;?>' data-pname='<?php echo $pname;?>' data-profurl="<?php echo $profileUrl; ?>" data-red-current = <?php echo $al;?> data-red-like = "<?php echo $redclassd; ?>">
							<div class='ph-list-thumbnail'>
							<?php the_post_thumbnail('small'); ?>
							</div>
			<?php }elseif($phfi != ''){ ?>
						<div class = 'reddit-post pull-left' id='reddit-post-<?php echo $post->ID;?>' data-ph-url="<?php  echo post_permalink( $post->ID ); ?>" data-slug='<?php echo $post->post_name; ?>' data-id='<?php echo $post->ID; ?>' data-url = '<?php echo $url; ?>' data-auth = '<?php echo $auth; ?>' data-rajax = '<?php echo $post->ID; ?>' data-rups = '<?php echo $postvote;?>' data-pname='<?php echo $pname;?>' data-profurl="<?php echo $profileUrl; ?>" data-red-current = <?php echo $al;?> data-red-like = "<?php echo $redclassd; ?>">			
							<div class='ph-list-thumbnail'>
								<img src="<?php echo $phfi;?>" class="attachment-small size-small wp-post-image">
							</div>
			<?php }else{ ?>
						<div class = 'reddit-post pull-left' id='reddit-post-<?php echo $post->ID;?>' data-ph-url="<?php  echo post_permalink( $post->ID ); ?>" data-slug='<?php echo $post->post_name; ?>' data-id='<?php echo $post->ID; ?>' data-url = '<?php echo $url; ?>' data-auth = '<?php echo $auth; ?>' data-rajax = '<?php echo $post->ID; ?>' data-rups = '<?php echo $postvote;?>' data-pname='<?php echo $pname;?>' data-profurl="<?php echo $profileUrl; ?>" data-red-current = <?php echo $al;?> data-red-like = "<?php echo $redclassd; ?>">
			<?php } ?>
							<div class='post-meta-hunt'>
							<!-- external URL button -->
							<div class='ph-external ph-action'>
								<span class="post-item--action v-icon"><a href="<?php echo esc_url($out); ?>" target="_blank" data-reactid=".3.0.0:$1.0.2.0.$31121.0.0.3.0.0"><span data-reactid=".3.0.0:$1.0.2.0.$31121.0.0.3.0.0.0"><svg width="16" height="14" viewBox="0 0 16 14" xmlns="http://www.w3.org/2000/svg"><g fill="#BBB" fill-rule="evenodd"><path d="M0 3h16v2H0z"></path><rect width="16" height="2" rx="2"></rect><rect y="12" width="16" height="2" rx="2"></rect><rect width="2" height="14" rx="2"></rect><rect x="14" width="2" height="14" rx="2"></rect><path d="M9.355 6.355C9.16 6.16 9.215 6 9.49 6h3.26c.138 0 .25.115.25.25v3.26c0 .27-.152.338-.355.135l-3.29-3.29z"></path></g></svg></span></a></span>
							</div>

								<?php if(!ph_in_user_collection($post->ID)) { ?>
							<div class='ph-collect ph-action' data-pid ='<?php echo $post->ID;?>'>
							<span class="collect-button--icon" data-pid ='<?php echo $post->ID;?>'>
								<svg width="15" height="14" viewBox="0 0 15 14" xmlns="http://www.w3.org/2000/svg">
									<path d="M13 10V8.99c0-.54-.448-.99-1-.99-.556 0-1 .444-1 .99V10H9.99c-.54 0-.99.448-.99 1 0 .556.444 1 .99 1H11v1.01c0 .54.448.99 1 .99.556 0 1-.444 1-.99V12h1.01c.54 0 .99-.448.99-1 0-.556-.444-1-.99-1H13zM0 1c0-.552.447-1 .998-1h11.004c.55 0 .998.444.998 1 0 .552-.447 1-.998 1H.998C.448 2 0 1.556 0 1zm0 5c0-.552.447-1 .998-1h11.004c.55 0 .998.444.998 1 0 .552-.447 1-.998 1H.998C.448 7 0 6.556 0 6zm0 5c0-.552.453-1 .997-1h6.006c.55 0 .997.444.997 1 0 .552-.453 1-.997 1H.997C.447 12 0 11.556 0 11z" fill="#C8C0B1" fill-rule="evenodd"></path>
								</svg>
								<?php }else{ ?>
							<div class='ph-collect ph-collect-in in ph-action' data-pid ='<?php echo $post->ID;?>'>
							<span class="collect-button--icon in" data-pid ='<?php echo $post->ID;?>'>
								<svg width="17" height="14" viewBox="0 0 17 14" xmlns="http://www.w3.org/2000/svg">
							      <path d="M11.036 10.864L9.62 9.45c-.392-.394-1.022-.39-1.413 0-.393.393-.39 1.023 0 1.414l2.122 2.12c.193.198.45.295.703.295.256 0 .51-.1.706-.295l4.246-4.246c.385-.385.39-1.02-.002-1.413-.393-.393-1.022-.39-1.412-.002l-3.537 3.538zM0 1c0-.552.447-1 1-1h11c.553 0 1 .444 1 1 0 .552-.447 1-1 1H1c-.553 0-1-.444-1-1zm0 5c0-.552.447-1 1-1h11c.553 0 1 .444 1 1 0 .552-.447 1-1 1H1c-.553 0-1-.444-1-1zm0 5c0-.552.447-1 1-1h4.5c.552 0 1 .444 1 1 0 .552-.447 1-1 1H1c-.552 0-1-.444-1-1z" fill="#DC5425" fill-rule="evenodd"></path>
							  	</svg>
							  	<?php } ?>
							</span>
							</div>


							<div class='author-ava'>
								<?php
								 if($plugina == ''){  ?>
									<span class='author-tool' data-toggle="tooltip" data-placement="left" title="<?php echo get_the_author_meta('user_nicename'); ?>">             <?php 
                            		$args = array( 'class' => 'img-rounded');
                            		echo get_avatar($post->post_author, 40); ?></span><?php }else{ 
									$pluginava = get_post_meta($post->ID,'pluginavatar',true);
									?>
									<span class='author-tool' data-toggle="tooltip" data-placement="left" title="<?php echo $plugina; ?>"><img class='img-rounded' src="<?php echo $pluginava;?>" height = "40px" width="40px"/></span>
								<?php } ?>
							</div>


							<div class='comment-icon'>
									<span><svg width="19" height="17" viewBox="0 0 19 17" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="b"><stop stop-color="#CCC" offset="0%"></stop><stop stop-color="#BABABA" offset="100%"></stop></linearGradient><filter x="-50%" y="-50%" width="200%" height="200%" filterUnits="objectBoundingBox" id="a"><feOffset dy="1" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation=".5" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.070000001 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"></feColorMatrix><feMerge><feMergeNode in="shadowMatrixOuter1"></feMergeNode><feMergeNode in="SourceGraphic"></feMergeNode></feMerge></filter></defs><path d="M1.753 18.06C.66 16.996 0 15.623 0 14.125 0 10.742 3.358 8 7.5 8c4.142 0 7.5 2.742 7.5 6.125s-3.358 6.125-7.5 6.125c-1.03 0-2.01-.17-2.904-.476-1.46.845-4.318 1.962-4.318 1.962s.955-2.298 1.475-3.676z" transform="translate(2 -7)" filter="url(#a)" stroke="url(#b)" fill="#FFF" fill-rule="evenodd"></path></svg></span>
									 <span class='hunt-comm-count'><?php $comments = get_comments('post_id=' . $post->ID); echo count($comments); ?></span>
							</div>


						</div>
						<?php
						//trim the title to 37 characters...
						$title = get_the_title($post->ID);
						if(strlen($title)>37){
						// $title = substr( $title, 0, 37) . "...";
						} ?>
						<?php // if ( wp_is_mobile() ) { echo "<a href='". post_permalink( $post->ID ) ."'>";  }  ?>
						<span class='title'><?php echo $title; ?></span>	
						<?php // if ( wp_is_mobile() ) {  echo '</a>'; }  ?>		
							 <span class='description'>
							 	<?php
							 	if( of_get_option('ph_grid_on') == 1) { 
									echo wp_trim_words( get_the_content(), 10); 
								}else{
									echo wp_trim_words( get_the_content(), 30); 
								}
							 	?>
							 </span>
						</div>

				<div style="clear:both"></div>
			
			</div>
			
			<?php 


		} 


			?>
			
 <?php endforeach; ?>
 <?php else : ?>


 <div id="epic_page_end_2"><?php _e('No more','pluginhunt'); ?> <?php echo of_get_option('ph_hunt_plural');?>...</div>


 <?php endif; ?>

 			<?php if($num_posts > 10 && $paged >1){ 
 			$more = $num_posts - 10;
 			echo "<div class='unhide show-hidden-posts'><span class='showmore' data-d=$d data-m=$m data-y=$y><i class='hp fa fa-chevron-down'></i> ";
 			$text = sprintf( _n( 'Show 1 more ' . of_get_option('ph_hunt_single'), 'Show %s more ' . of_get_option('ph_hunt_plural'), $more, 'pluginhunt' ), $more );
			echo $text;
 			echo "</span></div>";

 			 } ?>



			<div class='container'>
			<div id='navigation'>
			    <div class='next-posts-link'>
					<a href="<?php echo esc_url( home_url( '/' ) )?>?day=<?php echo $d;?>&month=<?php echo $m;?>&year=<?php echo $y;?>">next</a>
				</div>
			</div>
			</div>

			<div id="results"></div>
			<div id = "error"></div>


			<div class='hide'>
				<?php wp_link_pages(); ?>
			</div>

			

		</div>

	</div>
			
			<?php wp_reset_query(); ?>


</div>



<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

<?php get_footer(); ?>
