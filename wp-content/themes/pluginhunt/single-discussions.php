<?php
get_header(); 

	global $wp_query,$post,$wpdb, $current_user,$query_string;
    wp_get_current_user();
	$wpdb->myo_ip   = $wpdb->prefix . 'epicred';
    
			
			$postvote = get_post_meta($post->ID, 'epicredvote' ,true);
			wpeddit_post_ranking($post->ID);

			if($postvote == NULL){
				$postvote = 0;
			}
			
			$fid = $current_user->ID;
	
			$query = "SELECT epicred_option FROM $wpdb->myo_ip WHERE epicred_ip = $fid AND epicred_id = $post->ID";
			$al = $wpdb->get_var($query);
			if($al == NULL){
				$al = 0;
				$redclassu = 'up';
				$redscore = "unvoted";
				$c = "";
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
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
			$out =  get_post_meta($post->ID, 'outbound', true);
			$n = parse_url($out);

			phsetPostViews($post->ID);

			?>

<div class="ph-user-message">
	<i class="fa fa-bell faa-ring animated"></i> 
	<span class='ph-user-message-text'></span>
	<span class='ph-user-close'>x</span>
</div>

<div id='phsf'>
	<div class='row'>
			 <div class='post-header' style="background-image: url('<?php echo $image[0]; ?>')">
			 	<div class='post-header-shadow'>
				 	<div class='container-title'>
				 		<span class='post-title-single'><a class='title-link title-link-html-<?php echo $post->ID;?>' href="<?php echo esc_url($out); ?>" title="<?php the_title_attribute(); ?>" target="_blank" rel="nofollow"><?php the_title(); ?></a></span>
				 		<div class='post-description-short'><?php echo wp_trim_words( $post->post_content , 35, '...' ); ?></div>
				 		
				 		<div class='reddit-wrapper'>
						 	<div class = 'reddit-voting <?php echo $c; ?>' style='margin-left: 65px;'>
								<ul class="unstyled">
									<div class="arrow fa fa-caret-up  fa-2x <?php echo $c;?> arrow-up-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "up" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>
									<div class="score score-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?>><?php echo $postvote; ?></div>
								</ul>
							</div>	
				 		</div>
				 		<div class="post-detail--header--buttons">
				 			<?php
							$categories = get_the_terms($post->ID,'discussion_category');
							foreach($categories as $category) {
								$cat_id = $category->term_id;
								$cat_name = $category->name;
								if ($cat_id != 1) {
									echo '<a class="post-detail--header--button" href="' . esc_url( get_category_link( $cat_id ) ) . '">'.$cat_name.'</a>';
								}
							}
				 			?>				 			
						</div>
				 	</div>
			 	</div>



			 </div>

		<div class = "row post-detail">
			 <div class='full-width-ph'>
			 	<section class="col-md-8 ph-lhs">

			 	<?php if(!is_user_logged_in()){ ?>
			 		<div class='sign-up-cta'>
			 			<h3 class='section--heading'><?php echo of_get_option('ph_logged_out_tit'); ?></h3>
			 			<h4><?php echo of_get_option('ph_logged_out_sub'); ?></h4>
			 		<hr>
			 		  <div class='ph_socials'>
			 		  	<div class='ph-join'>
                           <div class='ph-soc-block'>
                           	<ul class='ps-main'>
                            	<li class='tw ph-sm'><a href="<?php echo wp_login_url(); ?>?loginTwitter=1&redirect=<?php echo $surl;?>" onclick="window.location = '<?php echo wp_login_url(); ?>?loginTwitter=1&redirect='+window.location.href; return false;">
                            	<i class="fa fa-twitter"></i><?php _e('Log in to vote','pluginhunt'); ?></a></li>
                            	<li class='fb ph-sm'><a href="<?php echo wp_login_url(); ?>?loginFacebook=1&redirect=<?php echo $surl;?>" onclick="window.location = '<?php echo wp_login_url(); ?>?loginFacebook=1&redirect='+window.location.href; return false;">
                            	<i class="fa fa-facebook"></i><?php _e('Log in to vote','pluginhunt'); ?></a></li>
                        	</ul>
                           </div>
                       </div>
                      </div>
                      <div style="clear:both"></div>
					</div>  <!-- end sign up CTA -->
					<?php } ?>					
					<div class='section section-media'>

						<div class='ph-media-items'>
						<h3 class='h3'><?php _e('media','pluginhunt'); ?></h3>
						<div class="carousel--controls">
					    <?php
					    $media = get_post_meta($post->ID,'phmedia',true);

					     if(current_user_can( 'upload_files' ) && $media !=''){ ?> 
							<a class="carousel--controls--button v-add" title="Add content" data-pid="<?php echo $post->ID; ?>">
								<span>
									<svg width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg">
										<path d="M5 5V1.002C5 .456 5.448 0 6 0c.556 0 1 .45 1 1.002V5h3.998C11.544 5 12 5.448 12 6c0 .556-.45 1-1.002 1H7v3.998C7 11.544 6.552 12 6 12c-.556 0-1-.45-1-1.002V7H1.002C.456 7 0 6.552 0 6c0-.556.45-1 1.002-1H5z" fill="#948B88" fill-rule="evenodd"></path>
									</svg>
								</span>
							</a>
					    <?php } ?>

							<a class="carousel--controls--button v-prev m-disabled hide">
								<span>
									<svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
										<path d="M.256 5.498c0-.255.098-.51.292-.703L4.795.548C5.18.163 5.817.16 6.207.55c.393.393.392 1.023.002 1.412L2.67 5.5l3.54 3.538c.384.384.388 1.02-.003 1.412-.393.393-1.023.39-1.412.002L.548 6.205c-.192-.192-.29-.447-.29-.702z" fill="#948B88" fill-rule="evenodd"></path>
									</svg>
								</span>
							</a>
							<a class="carousel--controls--button v-next hide">
								<span>
									<svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
										<path d="M6.744 5.502c0 .255-.098.51-.292.703l-4.247 4.247c-.385.385-1.022.388-1.412-.002C.4 10.057.4 9.427.79 9.038L4.33 5.5.79 1.962C.407 1.578.403.942.794.55 1.186.157 1.816.16 2.204.548l4.248 4.247c.192.192.29.447.29.702z" fill="#948B88" fill-rule="evenodd"></path>
									</svg>
								</span>
							</a>
						</div>

						<?php
						
						$media_array = json_decode($media);
						if($media_array !=''){
						foreach($media_array as $m){
							if($m->source == 'yt'){
								echo "<a href='$m->url' class='phlb' data-yturl='$m->url'><img src='http://img.youtube.com/vi/$m->id/0.jpg' height='210px'/></a>";
							}else{
								echo "<a href='$m->url' class='phlb'><img src='$m->url' height='210px'/></a>";
							}
						}
						}?>
					</div>
					<?php if(current_user_can( 'upload_files' )){ ?>
					<?php if($media==''){ ?>
						<p class="post-detail placeholder media-placeholder"><span><?php _e('No media yet. Be the first one to','pluginhunt'); ?>&nbsp;</span><a class='postmedia' data-pid='<?php echo $post->ID;?>' href="#"><?php _e('add media on this product','pluginhunt'); ?></a><span>.</span></p>
						<?php } ?>
					
					<?php } ?>
					</div>
					<div class='section section-details'>
						<h3 class='h3'><?php _e('details','pluginhunt');?></h3>
						<p class='details-content'>
						<?php echo $post->post_content; ?>
						</p>
					</div>

					
					<div class='section section-discussion'>
						<h3 class='h3'><?php _e('discussion','pluginhunt');?></h3>
						<?php comments_template(); ?> 
					</div>

					<?php if(!is_user_logged_in()){ ?>
					<div class='section can-comment'>
						<p class="post-detail placeholder"><span><?php _e('Commenting is limited to those invited by others in the community','pluginhunt');?></span><br/><a class='ph-login-link' href="#"><?php _e(' Login to continue','pluginhunt'); ?></a><span> <?php _e('or','pluginhunt'); ?> <a href="<?php echo esc_url(of_get_option('ph_faq')); ?>"><?php _e('learn more','pluginhunt'); ?></a>.</span></p>
					</div>
					<?php }

					if(current_user_can( 'subscriber' )){  ?>
					<div class='section can-comment'>
						<p class="post-detail placeholder"><span><?php _e('Commenting is limited to those invited by others in the community','pluginhunt');?></span><br/>
							<?php 
							$cid = get_current_user_id();
							$access = get_user_meta($cid, 'ph_access_request',true);
							if($access == ''){ ?>
							<span class='ph-request-msg'>
							<a class='ph-request-access' data-uid ='<?php echo $cid; ?>' href="#"><?php _e('request access','pluginhunt'); ?></a>
						</span>
						<?php }else{ ?>
						<span><?php _e('You have been added to the waiting list.','pluginhunt');?><span class='emo'>&#x1f483;</span></span>
						<?php } ?>
							<br/><span><?php _e('Questions? check out our','pluginhunt'); ?><a href='<?php echo esc_url(get_theme_mod('phfaq')); ?>'> <?php _e('FAQ','pluginhunt');?></a>.</span></p>
					</div>						
				<?php } ?>

					<div class='lhs-bottompad'></div>
                  
				</section>
				<?php 
				if(wp_is_mobile()){
					$c ='-mobile';
				}; ?>

				<div class='col-md-4 aside'>
					<div class='section sharer'>
						<h3 class='h3'><?php _e('share','pluginhunt'); ?></h3>
						<ul class='post-share'>
							<a class="share" href="<?php echo get_permalink($post->ID);?>" title="<?php the_title(); ?>" data-action="facebook"><li class='fb ph-s'><i class="fa fa-facebook"></i></li></a>
							<a class="share" href="<?php echo get_permalink($post->ID);?>" title="<?php the_title(); ?>" data-action="twitter"><li class='tw ph-s'><i class="fa fa-twitter"></i></li></a>
							<a class="share" href="<?php echo get_permalink($post->ID);?>" title="<?php the_title(); ?>" data-action="google"><li class='gp ph-s'><i class="fa fa-google-plus"></i></li></a>
							<li class='em ph-s'><i class="fa fa-envelope"></i></li>
						</ul>
					</div>

					<div style="clear:both"></div>

<!--  MAKER UX IN V3.7 - see epicplugins.com for more info 
					<div class='section'>
						<h3 class='h3'>0 makers</h3>
						<p class="post-detail placeholder v-white">
							<span>No maker yet.</span><br>
							<span>Be the first to&nbsp;</span>
							<a data-popover="click" data-popover-href="/posts/28453/maker_suggestions/new" href="#">suggest a maker</a>
						</p>
					</div>
-->
					<div class='section section-upv'>

					<?php
					$wpdb->epic   = $wpdb->prefix . 'epicred';
					$query = $wpdb->prepare("SELECT epicred_ip FROM $wpdb->epic WHERE epicred_id = %d", $post->ID);
					$upvotes = $wpdb->get_results($query);
					$u = count($upvotes);
					if($u == 0){
					  $uc = 'hide';
					}
					?>
					  
					  <div class="title upvotes upvotes-modal <?php echo $uc; ?>">
					  	<h3 class='h3'><?php echo $uc; ?><?php _e("Upvotes",'pluginhunt'); ?></h3>
					  </div>

					  <div data-user-carousel="true" class="user-votes">
					  <?php
					  $ui = 0;
					  foreach($upvotes as $upvote){

					    $ava = ph_avatar($upvote->epicred_ip);
					    $href = get_author_posts_url( $upvote->epicred_ip );
					    $upv = get_userdata( $upvote->epicred_ip );

					  ?>
					  <div class="who-by-v example votes-inner">
					    <a class="drop-target drop-theme-arrows-bounce"><img class='img-rounded flash-ava' src='<?php echo $ava; ?>'/></a>
					     <div class="ph-content pop-ava-v">
					        <img class='modal-img' id='modal-img-<?php echo $ui;?>' src='<?php echo $ava; ?>'/>
					        <div class='user-info'>
					          <span class='user-name'><?php echo $upv->display_name; ?></span>
					          <div class='user-desc'>
					              <?php echo $upv->description; ?>
					          </div>
					          <div class='view-profile'>
					            <div class='btn btn-success primary ph_vp'><a class='vp' href='<?php echo $href;?>'><?php _e("View Profile","pluginhunt"); ?></a></div>
					          </div>
					        </div>
					    </div>
					  </div>

					  <?php
					  $ui++;
					   }
					   ?>
					  </div>

					  <div style="clear:both"></div>

					</div>
				</div>
			 </div>
		</div>

			<div class='row'>
				<?php if(current_user_can( 'edit_posts' )){
					if(wp_is_mobile()){ $c = 'col-md-12'; }else{$c='col-md-8';}
				 ?>
                <div class="<?php echo $c;?> post-detail--footer-2">
                    <main class="">
                    	
                        <div class="post-detail--footer--comments-form-toggle">
                        	<form action="<?php echo home_url(); ?>/wp-comments-post.php" method="post" id="phcommentform">
                            <input class="post-detail--footer--comments-form-toggle--link comment-post" name="comment" id="comment" placeholder="<?php _e('Leave a reply','pluginhunt');?>" />
                        	<div class='comment-actions'><span class='comment-cancel'><?php _e('Cancel','pluginhunt'); ?></span> <span class='comment-submit'><?php _e('Submit','pluginhunt'); ?></span></div>
                        	<input type="hidden" name="comment_post_ID" value="<?php echo $post->ID;?>" id="comment_post_ID">
                        	<input type="hidden" name="comment_parent" id="comment_parent" value="0">
                        	</form>
                        </div>
                      
                    </main>
                </div>
                  <?php } ?>

                    <!-- aside footer for single page -->
                    <div class="post-detail--footer v-no-access col-md-4">
                        <div class="aside-foot">
                            <section class="post-detail--footer--meta">
                                <a class="user-image post-detail--footer--meta--user-image" href="#">
                                    <span class="user-image">
                                        <div class="user-image--badge v-hunter">H</div>
                                        <?php 
                                        global $post;  $aid=$post->post_author;
                                        $pluginava = ph_avatar($post->post_author);
                                        $pname = get_the_author_meta('user_nicename',$post->post_author);
                                        $auth = 'yes';
                                        $profileUrl = get_author_posts_url($post->post_author); 
                                        ?>
                                      <div class="who-by profile-drop">
                                        <a class="drop-target drop-theme-arrows-bounce-dark"><img class='img-rounded flash-ava' src='<?php echo $pluginava; ?>'/></a>
                                         <div class="ph-content pop-ava">
                                            <img id='modal-img' class='poster-ava' src='<?php echo $pluginava; ?>'/>
                                            <div class='user-info'>
                                              <span class='user-name'><?php echo $pname; ?></span>
                                              <div class='view-profile'>
                                                <div class='btn btn-success primary ph_vp'><a class='vp submitter-vp' href='<?php echo $profileUrl; ?>'><?php _e("View Profile","pluginhunt"); ?></a></div>   
                                              </div>
                                            </div>
                                        </div>
                                      </div>
                                    </span>
                                </a>
                                <a class="post-detail--footer--meta--time" href="<?php echo get_post_permalink($post->ID); ?>">
                                    <span><?php _e('Posted','pluginhunt');?></span>
                                    <span> </span>
                                    <time><?php printf( _x( '%s ago', '%s = human-readable time difference', 'pluginhunt' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
                                </a>

						      <span class='ph_em ph_ft'>
						          <div class="who-by-e flag-drop votes-inner">
						            <span class="drop-target drop-theme-arrows-bounce">        
						            <i class="fa fa-flag"></i>
						            </span>
						            <div class="ph-content pop-ava-v">
						                <div class='user-info-e ph-flag'>
						                  <div class='user-desc-em'>
						                      <span class='ph_title_em'><?php _e("Flag","pluginhunt");?> </span><span class='ph_red' id ="ph_red_title_flag"><?php echo $phid->post_title; ?></span><span class='ph_title_em'></span>
						                  </div>
						        
						                  <textarea name="flag" id="body-flag-ph" class="textarea tooltip-field tooltip-textarea textarea-flag" placeholder="<?php _e('Why should this be removed ?','pluginhunt'); ?>"></textarea><br>
						                  <span class='ph-flag-done'><strong><?php _e("Thank you ", "pluginhunt"); ?></strong><?php _e(" we have received your feedback", "pluginhunt"); ?></span>
						                  <div class='view-profile flag-post-ph'>
						                    <button class='btn btn-cancel primary ph_vp ph_cancel'><?php _e("Cancel","pluginhunt"); ?></button>
						                    <button class='btn btn-success primary ph_vp ph_vp_flag' data-perma ="<?php echo $perma; ?>" data-id ="<?php echo $phid->ID; ?>"><?php _e("Send","pluginhunt"); ?></button>
						                  </div>

						           
						              </div>
						            </div>
						          </div>
						      </span>


                            </section>
                        </aside>
                    </div>
                </div>

    </div>
</div> <!-- ph-sing-flash -->
			
<?php get_footer(); ?>