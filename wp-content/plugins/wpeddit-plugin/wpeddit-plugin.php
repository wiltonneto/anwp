<?php
/*
Plugin Name: WPeddit: Plugin Hunt Edition
Plugin URI: http://pluginhunt.com/
Description: Turn your WordPress website into a powerful product rating site.
Version: 1.0
Author: Mike Stott
Author URI: http://www.epicplugins.com/
*/

    #} Hooks
   
    #} Install/uninstall
    register_activation_hook(__FILE__,'epicred__install');
    register_deactivation_hook(__FILE__,'epicred__uninstall');
    
    #} general
    add_action('init', 'epicred__init');
    add_action('admin_menu', 'epicred__admin_menu'); #} Initialise Admin menu
    
    

	#} Initial Vars
	global $epicred_db_version;
	$epicred_db_version             	   = "1.0";
	$epicred_version           		       = "2.5";
	$epicred_activation                    = '';


	#} Urls
    global $epicred_urls;
    $epicred_urls['home']   	   	 = 'http://wpeddit.com/';
    $epicred_urls['docs']     		 = plugins_url('/documentation/index.html',__FILE__);
	$epicred_urls['forum']      	 = 'http://epicplugins.com/help/';
    $epicred_urls['updateCheck']	 = 'http://www.epicplugins.com/api/';
	$epicred_urls['regCheck']		 = 'http://www.epicplugins.com/registration/';
	$epicred_urls['subscribe'] 		 = "http://eepurl.com/tW_t9";
	
	#} Page slugs
    global $epicred_slugs;
    $epicred_slugs['config']           = "epicred-plugin-config";
    $epicred_slugs['settings']         = "epicred-plugin-settings";

	#} Install function
	function epicred__install(){

    #} Default Options

    add_option('epicred_ip','no','','yes');
	add_option('wpedditnewpost','pending','','yes');



	epicred_install();
	add_option('wpedditshared','no','','yes'); 
		
	$current_user = wp_get_current_user();    //email the current user rather than admin info more likely to reach a human email 
	$userEmail = $current_user->user_email;
	$userName =  $current_user->user_firstname;
	$LastName =  $current_user->user_lastname;
	$plugin = 'WPeddit';
			
	if(get_option('wpedditshared') == 'no'){    //only send them an install email once
			wpeddit_sendReg($userEmail,$userName,$plugin);
		    update_option('wpedditshared','yes'); 
	}  
	
	
 
	}
	
	
	global $epicred_db_version;
	$epicred_db_version = "1.0";

   function epicred_install() {
   global $wpdb;
   global $epicred_db_version;

   $table_name = $wpdb->prefix . "epicred";
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  epicred_id mediumint(9) NOT NULL,
	  epicred_option mediumint(9) NOT NULL,
	  epicred_ip text NOT NULL,
	  UNIQUE KEY id (id)
	    );";

	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   dbDelta($sql);

    $table_name = $wpdb->prefix . "epicred_comment";
      
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      epicred_id mediumint(9) NOT NULL,
      epicred_option mediumint(9) NOT NULL,
      epicred_ip text NOT NULL,
      UNIQUE KEY id (id)
        );";

       dbDelta($sql);
	 
	   add_option("epicred_db_version", $epicred_db_version);
	   


    }


#} Initialisation - enqueueing scripts/styles
function epicred__init(){
  
    global $epicred_slugs, $epicred_taxonomy; #} Req
    
    #} Admin & Public
    wp_enqueue_script("jquery");
    wp_enqueue_script( 'jquery-form',array('jquery')); 
    wp_enqueue_script('epicred-ajax',plugins_url('/js/epicred.js',__FILE__),array('jquery'));
    wp_localize_script( 'epicred-ajax', 'EpicAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	
	if(!is_admin()){
    wp_enqueue_style('epicred-css', plugins_url('/css/epicred.css',__FILE__) );
	}
    
    #} Admin only
    if (is_admin()) {
    wp_enqueue_style('myo-polling-admin-css', plugins_url('/css/epicadmin.css',__FILE__) );
    }


}

#} Add le admin menu
function epicred__admin_menu() {

    global $epicred_slugs,$epicred_menu; #} Req
    
    $epicred_menu = add_menu_page( 'wpeddit menu', 'WPeddit', 'manage_options', $epicred_slugs['config'], 'epicred_menu', plugins_url('i/wpedditicon.png',__FILE__));
     add_submenu_page( $epicred_slugs['config'], 'Settings', 'Settings', 'manage_options', $epicred_slugs['settings'] , 'epicred_pages_settings' );
	 
}


#}Settings
function epicred_pages_settings() {
    
    global $wpdb;    #} Req
    
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    
?><div id="sgpBod">
          <div class='myslogo'><?php echo '<img src="' .plugins_url( 'i/logo.png' , __FILE__ ). '" > ';   ?></div>
        <div class='mysearch'>
        	
        <?php epicred_header(); ?>
            
                
         <?php
         	if(isset($_GET['save'])){ 
             if ($_GET['save'] == "1"){
                epicred_html_save_settings();
             }
		    }
            if(!isset($_GET['save'])){
                epicred_html_settings();
            }
    ?></div>
</div>
<?php
}


function epicred_html_settings(){
        
    global $wpdb, $epicred_slugs;  #} Req
    
    $myoConfig = array();
    $myoConfig['trans_id'] 		   =           get_option('epicred_trans_id');    
    $myoConfig['epicred_ip']   	   =           get_option('epicred_ip');    
	$myoConfig['pending']		   =		   get_option('wpedditnewpost')


    
    ?>
    

     <form action="?page=<?php echo $epicred_slugs['settings']; ?>&save=1" method="post">
     <div class="postbox">
     <h3><label>General settings</label></h3>
     
     <table class="form-table" width="100%" border="0" cellspacing="0" cellpadding="6">
         

        <tr valign="top">
                        <td width="25%" align="left"><strong>Lock votes by IP or Member:</strong></td>
                        <?php if($myoConfig['epicred_ip'] == 'yes'){ ?>
                        <td align="left">
                            <input type="radio" name="epicred_ip" value="yes" checked> IP
                            <input type="radio" name="epicred_ip" value="no"> Member
                            <br><i>Restrict the votes to one per IP address or one vote per member?</i>
                        </td>
                        <?php }else{ ?>
                         <td align="left">
                            <input type="radio" name="epicred_ip" value="yes"> IP
                            <input type="radio" name="epicred_ip" value="no" checked> Member
                            <br><i>Restrict the votes to one per IP address or one vote per member?</i>
                        </td>
                        <?php } ?>
         </tr>
         
         <tr valign="top">
                        <td width="25%" align="left"><strong>Save front end posts as:</strong></td>
                        <?php if($myoConfig['pending'] == 'pending'){ ?>
                        <td align="left">
                            <input type="radio" name="pending" value="pending" checked> Pending Review
                            <input type="radio" name="pending" value="published"> Published
                            <br><i>Increase security by making sure front end posts are pending review before you publish them</i>
                        </td>
                        <?php }else{ ?>
                         <td align="left">
                            <input type="radio" name="pending" value="pending"> Pending Review
                            <input type="radio" name="pending" value="published" checked> Published
                            <br><i>Increase security by making sure front end posts are pending review before you publish them</i>
                        </td>
                        <?php } ?>
         </tr>
         

      
    </table>
    <p id="footerSub"><input class = "button-primary" type="submit" value="Save settings" /></p>
    </form>
</div>

<?php }





#} Save options changes
function epicred_html_save_settings(){
    
    global $wpdb;  #} Req
    
    $myoConfig = array();
    $myoConfig['epicred_ip'] 		=       $_POST['epicred_ip'];
    $myoConfig['trans_id'] 			=       $_POST['epicred_trans_id'];
    $myoConfig['pending']			=		$_POST['pending']; 	

    
    #} Save down
    update_option("epicred_ip", $myoConfig['epicred_ip']);
    update_option("epicred_trans_id", $myoConfig['trans_id']);
	update_option("wpedditnewpost", $myoConfig['pending']);


    #} Msg
    epicred_html_msg(0,"Saved options");
    
    #} Run standard
    epicred_html_settings();
    
}






function epicred_checkForMessages(){
    
    global $epicred_urls;

    # First deal with legit purchases
    if (isset($_GET['legit'])){
        
        # Update
        update_option('epicred_myo_firstLoadMsg',1);
        
        #} Set this here
        $flFlag = 1;
        
    } else $flFlag = get_option('epicred_myo_firstLoadMsg');
    
    
    
    if (empty($flFlag)) {
        
        epicred_html_msg(2,'<div class="sgThanks">
            <h3>Thank you for installing WPeddit</h3>
            <p>This license entitles you to use the WPeddit on a single WordPress install.</br>
            </p>
                        
            <p>Its Easy to get started, you can work it out for yourself below or read the <a href="'.$epicred_urls['docs'].'" target="_blank">WPeddit Support Manual</a>.<br />To keep up to date with WPeddit follow us on <a href="http://codecanyon.net/user/mikemayhem3030/follow/" target="_blank">CodeCanyon</a></p>
        
            <div class="sgButtons">
                <a class="buttonG" href="?page=epicred-plugin-config&legit=true">I have a License</a>
                <a class="buttonBad" href="http://codecanyon.net/item/pics-mash-image-rating-tool/3256459">I need a License</a>
            </div>
                    
            <div class="clear"></div>
        </div>');
        
    }
    
}

#} Options page
function epicred_menu() {
    
    global $wpdb, $epicred_urls, $epicred_version,$epicred_slugs;    #} Req
    // add database pointer
    
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    ?>
    <div id="sgpBod">
       <div class='myslogo'><?php echo '<img src="' .plugins_url( 'i/logo.png' , __FILE__ ). '" > ';   ?></div>
        <div class='mysearch'>
            
            <?php epicred_header(); ?>
            
            <?php epicred_checkForMessages(); ?>

               <div class="postbox-container" id="main-container" style="width:75%;">
            <div class="postbox">
           <h3 style="text-align:center"><label>Welcome to WPeddit</label></h3>
                <div class="inside">
                    <p style="text-align:center"><strong>Welcome to WPeddit</strong>: the Ultimate WordPress Post Ranking Plugin If you want to vote on future features or discover more cool plugins, check out the <br/><a href="http://epicplugins.com" target="_blank">epic plugins site</a>!</p>
                    <div id="SocialGalleryOptions">
                        <div><a href="admin.php?page=<?php echo $epicred_slugs['settings']; ?>" class="SocialGalleryOB">Settings</a></div>
                        <div><a href="edit.php?post_type=post" class="SocialGalleryOB">Check out rankings</a></div>
                        <div><a href="<?php echo $epicred_urls['home']; ?>" class="SocialGalleryOB">Demo Site</a></div>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
           
            
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Epic News'); ?></label></h3>
                <div class="inside">
                	<?php wpeddit_retrieveNews(); ?>
                </div>
            </div>  
   </div>

    <div class="postbox-container" id="side-container" style="width:24%;margin-left:1%">
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Share the love'); ?></label></h3>
                <div class="inside">
                	<p>This plugin has been developed with love & effort, it's a work in progress and I really appreciate all of the contribution you guys make to it. Thank you!</p>
                	
                    <!-- <a href="codecanyon.net/item/social-gallery-wordpress-photo-viewer-plugin/2665332?ref=stormgate" target="_blank">Rate it 5 stars on Code Canyon</a><br /> -->
                  
                    <div  style="text-align:center;margin-top:12px"><strong>Share WPeddit:</strong></div>
                    <div class="socialGalleryShareBox">
	                    <a href="http://www.facebook.com/sharer.php?s= 100&amp;p[title]= WPeddit - The Ultimate WordPress Post Rating Plugin and Theem&amp;p[url]=http://reddit.epicplugins.com/&amp;p[summary]=Let your visitors vote up your posts. A Must Have plugin for all WordPress users."target="_blank"><img src="<?php echo plugins_url('/i/fbshare.png',__FILE__); ?>" alt="" title="Share on Facebook" /></a>
	        	     	<a href="http://twitter.com/home?status=I Recommend You WPeddit for WordPress!! http://reddit.epicplugins.com/" target="_blank"><img src="<?php echo plugins_url('/i/tweet.png',__FILE__); ?>" alt="" title="Share this on Twitter" /></a>
					 	<a href="http://www.linkedin.com/shareArticle?mini=true&url=http://reddit.epicplugins.com/&title=WPeddit for WordPress&source=PicsMash" target="_blank"><img src="<?php echo plugins_url('/i/linkedin.png',__FILE__); ?>" alt="" title="Share this on LinkedIn" /></a>
						<a href="https://plus.google.com/share?url=http://reddit.epicplugins.com/" target="_blank"><img src="<?php echo plugins_url('/i/gp.png',__FILE__); ?>" alt="" title="Share this on Google+1" /></a>
         			</div>
                </div>
            </div>
   </div>

   <div class="postbox-container" id="side-container" style="width:24%;margin-left:1%">
            <div class="postbox">
                <h3 style="padding:8px;"><label><?php _e('Other Plugins'); ?></label></h3>
                <div class="inside">
					<table>
						<tr>
							<td><a href = "http://codecanyon.net/item/wordpress-social-polling-plugin/3750798?ref=mikemayhem3030" target = "_blank"><img src = "http://0.s3.envato.com/files/47574285/WP-SocialPolling-Thumb.jpg"/></a></td>
							<td style = "padding:3px"><a href = "http://codecanyon.net/item/wordpress-social-polling-plugin/3750798?ref=mikemayhem3030?ref=mikemayhem3030" target = "blank">Social Polling Plugin</a> is the future of website polling. Once someone votes on a poll it posts out to the voters news feed to help bring in more voters!
								</td>
						</tr>
												<tr>
							<td><a href = "http://codecanyon.net/item/dilemma-wordpress-plugin/3377683?ref=mikemayhem3030" target = "_blank"><img src = "http://2.s3.envato.com/files/47583855/Dilemma-Thumb-1.jpg"/></a></td>
							<td style = "padding:3px"><a href = "http://codecanyon.net/item/dilemma-wordpress-plugin/3377683?ref=mikemayhem3030?ref=mikemayhem3030" target = "blank">Dilemma!</a> the Ultimate Yes/No Plugin for WordPress. Ask your visitors engaging Dilemmas!
								</td>
						</tr>
					</table>
                </div>
            </div>
	 
	 <div style = 'clear:both'></div>
	 
	 </div>            

     </div>


</div>
<?php
}

#} Retrieves updated news.
function wpeddit_retrieveNews(){

				global $tweety_urls;
                include_once(ABSPATH . WPINC . '/feed.php');
                add_filter( 'wp_feed_cache_transient_lifetime' , 'wpeddit_feed_cache' );
				$url = 'http://epicplugins.com/feed/';
                $rss = fetch_feed($url);
                remove_filter( 'wp_feed_cache_transient_lifetime' , 'wpeddit_feed_cache' );
                
                if (!is_wp_error( $rss ) ) {
					
					$maxitems = $rss->get_item_quantity(5); 
                    $rss_items = $rss->get_items(0, $maxitems); 
					
				} ?>
                
                <ul>
                    <?php 
					if ($maxitems == 0) 
						echo '<li>No News (is this good news?)</li>';
                    else 
						foreach ( $rss_items as $item ) : ?>
                    <li>
                        <a href='<?php echo esc_url( $item->get_permalink() ); ?>' target = '_blank'
                        title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                        <?php echo  $item->get_title() ; ?></a><br/>
                        <?php echo  $item->get_description() ; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php
	
}

function wpeddit_feed_cache( $seconds )
			{
			  // change the default feed cache recreation period to 2 hours
			  return 7200;
			}



add_action( 'wp_ajax_nopriv_epicred_vote', 'epicred_vote' );
add_action( 'wp_ajax_epicred_vote', 'epicred_vote' );

function epicred_vote(){
	global $wpdb, $current_user;
	
    get_currentuserinfo();
	
	$wpdb->myo_ip   = $wpdb->prefix . 'epicred';
		
    $option = (int)$_POST['option'];
	$current = (int)$_POST['current'];
	
	//if we are locked via IP set the fid variable to be the IP address, otherwise log the member ID
	if(get_option('epicred_ip') == 'yes'){
		$fid = "'" . $_SERVER['REMOTE_ADDR'] . "'";	
	}else{
		$fid = $current_user->ID;
	}

	
	$postid = (int)$_POST['poll'];	


	
	$query = "SELECT epicred_option FROM $wpdb->myo_ip WHERE epicred_ip = $fid AND epicred_id = $postid";
	
	$al = $wpdb->get_var($query);
    
	
	if($al == NULL){
		$query = "INSERT INTO $wpdb->myo_ip ( epicred_id , epicred_ip, epicred_option) VALUES ( $postid, $fid, $option)";
		$wpdb->query($query);
	}else{
		$query = "UPDATE $wpdb->myo_ip SET epicred_option = $option WHERE epicred_ip = $fid AND epicred_id = $postid";
		$wpdb->query($query);
	}
	
    $vote = get_post_meta($postid,'epicredvote',true);
	
		if($option == 1){
			if($al != 1){
				if($al == -1){
				$vote = $vote+2;	
				}else{
				$vote = $vote+1;
				}
			}
		}
		
		
		if($option == -1){
			
			if($al != -1){
				if($al == 1){
					$vote = $vote-2;
				}else{
				$vote = $vote-1;
				}	
			}	
		}
		update_post_meta($postid,'epicredvote',$vote);

	
		$response['poll'] = $postid;
		$response['vote'] = $vote;
    
    echo json_encode($response);
  
	// IMPORTANT: don't forget to "exit"
	exit;
}


function wpeddit_post_ranking($post_id){
	
	$x = get_post_meta($post_id, 'epicredvote', true );
	if($x == ""){
		$x = 0;
	}
	
	$ts = get_the_time("U",$post_id);
	
	if($x > 0){
		$y = 1;
	}elseif($x<0){
		$y = -1;
	}else{
		$y = 0;
	}
	
	$absx = abs($x);
	if($absx >= 1){
		$z = $absx;
	}else{
		$z = 1;
	}
	
	
	$rating = log10($z) + (($y * $ts)/45000);
	
	update_post_meta($post_id,'epicredrank',$rating);
	
	return $rating;
	
}  




function epicred_header(){

    global $epicred_urls;
    ?>
     
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=438275232886336";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    <?php
    //build the twitter text tweet
        $URL = $epicred_urls['home'];
        $siteURL = get_site_url();
        $PicsM = "http://epicplugins.com/";
        $text = "I love " . $PicsM;
        $hash = "#epicred";
        $QP = "?url=".$URL."&text=".$text."&hashtags=".$hash;
    ?>
    
	
	
    <?php

        $img = "";
        echo "<a href='http://pinterest.com/pin/create/button/?url=$URL&media=$img&description=Description' class='pin-it-button' count-layout='horizontal'><img border'0' src='//assets.pinterest.com/images/PinExt.png' title='Pin It' /></a>";
    ?>

    <div class="fb-like" data-href="http://epicplugins.com/" data-send="true" data-width="360" data-show-faces="false" data-font="arial"></div>

    <?php
	$home = $epicred_urls['home'];
	$docs = $epicred_urls['docs'];
	$forum =$epicred_urls['forum'];
	$subs = $epicred_urls['subscribe'];
        echo "<div id = 'ps-links' style = 'padding-top:1%;padding-bottom:1%'><ul style = 'display:inline'>
        <li style = 'display:inline;padding-right:1%'><a href = '$home'>Demo Site</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$docs'>Documentation</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$forum'>Support Forum</a></li>
        <li style = 'display:inline;padding-right:1%'><a href = '$subs'>Subscribe to the EPIC mailing list</a></li>
        <li style = 'display:inline;padding-right:1%'><a href='mailto:mike@epicplugins.com?Subject=Hi%20Mike You Rock!'>Contact the developer</a></li>
        </ul></div>";

}


#} Outputs HTML message
function epicred_html_msg($flag,$msg,$includeExclaim=false){
    
    if ($includeExclaim){ $msg = '<div id="sgExclaim">!</div>'.$msg.''; }
    
    if ($flag == -1){
        echo '<div class="sgfail wrap">'.$msg.'</div>';
    }
    if ($flag == 0){ ?>
        <div id="message" class="updated fade below-h2"><p><strong>Settings saved!</strong></p></div>
    <?php }
    if ($flag == 1){
        echo '<div class="sgwarn wrap">'.$msg.'</div>';
    }
    if ($flag == 2){
        echo '<div class="sginfo wrap">'.$msg.'</div>';
    }
    if ($flag == 666){ ?>
        <div id="message" class="updated fade below-h2"><p><strong><?php echo $msg; ?>!</strong></p></div>
    <?php }
}




//new code for autoupdating and regCheck
#} Send registration info to my server
function wpeddit_sendReg($e='',$na='',$pl=''){

			global $epicred_urls;	
			if( function_exists('curl_init') ) { 
					$postData = array('ori'=>get_site_url());
					$postData['e'] = $e; //email
					$postData['na'] = $na;  //name
					$postData['pl'] = $pl;  //plugin

					
					$fields = ''; foreach($postData as $key => $value) $fields .= $key . '=' . $value . '&'; rtrim($fields, '&');
					$ch = curl_init($epicred_urls['regCheck']);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_POST, count($postData));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$regDets = curl_exec($ch);
					
					curl_close($ch);
					return $regDets;
			} # else, cry?			
			return  $false;
}


//code for the warnings and auto updating
global $epicred_urls;
$api_url = $epicred_urls['updateCheck'];
$plugin_slug = basename(dirname(__FILE__));


// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'wpeddit_check_for_plugin_update');

function wpeddit_check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug;

	//Comment out these two lines during testing.
	if (empty($checked_data->checked))
		return $checked_data;

	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);

	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;

	return $checked_data;
}



add_filter('plugins_api', 'wpeddit_plugin_api_call', 10, 3);

function  wpeddit_plugin_api_call($def, $action, $args) {
	global $plugin_slug, $api_url;

	if ($args->slug != $plugin_slug)
		return false;

	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;

	$request_string = array(
			'body' => array(
				'action' => $action, 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	$request = wp_remote_post($api_url, $request_string);

	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}

	return $res;
}

function wpeddit_hot($posts){
	global $wp_query,$post,$wpdb, $current_user,$query_string;
	wp_reset_query();
	
    $args = array(
        'meta_key' => 'epicredrank',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'posts_per_page' => $posts
    );
	
	query_posts($args);
	
	if ( have_posts() ) : ?>
 		<ul>	
		<?php while ( have_posts() ) : the_post(); ?> 
		<li><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>
		<?php endwhile; ?>
		</ul>
	<?php else: ?> 

	<?php endif; 
}


function epic_reddit_index($args){
	global $wp_query,$post,$wpdb, $current_user,$query_string;
    get_currentuserinfo();
	$wpdb->myo_ip   = $wpdb->prefix . 'epicred';

    //need to create our own query_posts for the hot and controversial
	if($args == 'hot'){
		
	if(!$wp_query) {
    global $wp_query;
    }
    
	$cat = get_query_var('cat');
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'meta_key' => 'epicredrank',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'paged' => $paged,
        'cat' => $cat
    );

    query_posts( array_merge( $args , $wp_query->query ) );
		
	}else{
	wp_reset_query(); 
	$cat = get_query_var('cat');
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
   
        'paged' => $paged,
        'cat' => $cat,
        
    );

    query_posts( $query_string );

	}
    
	if ( have_posts() ) : ?>
 			
			<?php while ( have_posts() ) : the_post(); ?> 
				
			<?php if(is_page()){
				
			}else{
			
			 $postvote = get_post_meta($post->ID, 'epicredvote' ,true);

			wpeddit_post_ranking($post->ID);

			if($postvote == NULL){
				$postvote = 0;
			}
			
			//again if IP locked set the fid variable to be the IP address.
	if(get_option('epicred_ip') == 'yes'){
		$fid = "'" . $_SERVER['REMOTE_ADDR'] . "'";	
	}else{
		$fid = $current_user->ID;
	}
			
			$query = "SELECT epicred_option FROM $wpdb->myo_ip WHERE epicred_ip = $fid AND epicred_id = $post->ID";
			$al = $wpdb->get_var($query);
			if($al == NULL){
				$al = 0;
			}
			if($al == 1){
				$redclassu = 'upmod';
				$redclassd = 'down';
				$redscore = 'likes';
			}elseif($al == -1){
				$redclassd = 'downmod';
				$redclassu = 'up';
				$redscore = "dislikes";
			}else{
				$redclassu = "up";
				$redclassd = "down";
				$redscore = "unvoted";
			}
			
			 ?>
			
			<div class = 'row' style = 'margin-bottom:20px'>
			
			
			<?php if(!is_user_logged_in() && get_option('epicred_ip') == 'no') { ?>
			<script>var loggedin = 'false';</script>
			<?php }else{  ?>
			<script>var loggedin = 'true';</script>
			<?php } ?>
			
			<?php if(!is_user_logged_in() && get_option('epicred_ip') == 'no') { ?>
			<a href="#myModal" data-toggle="modal">
			
			<?php } ?>
			
			<div class = 'span3'>

			<div class = 'reddit-voting'>
				<ul class="unstyled">
			<?php  if(!is_user_logged_in() && get_option('epicred_ip') == 'no') { ?>
					<div class="arrow2 <?php echo $redclassu;?> arrow-up-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "up" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>
					<div class="score2 <?php echo $redscore;?> score-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?>><?php echo $postvote; ?></div>
					<div class="arrow2 <?php echo $redclassd;?> arrow-down-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "down" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>
					<?php }else{ ?>
					<div class="arrow <?php echo $redclassu;?> arrow-up-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "up" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>
					<div class="score <?php echo $redscore;?> score-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?>><?php echo $postvote; ?></div>
					<div class="arrow <?php echo $redclassd;?> arrow-down-<?php echo $post->ID;?>" data-red-current = <?php echo $al;?> data-red-like = "down" data-red-id = "<?php echo $post->ID;?>" role="button" aria-label="upvote" tabindex="0"></div>	
					<?php }  ?>
				</ul>
			</div>	
			<?php  if(!is_user_logged_in() && get_option('epicred_ip') == 'no') { ?>
			</a>
			<?php } ?>

			<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
			<?php if ( has_post_thumbnail() ) { ?>
				<div class = 'reddit-image pull-left' style = 'width:180px'>
					<img src = "<?php echo $image[0]; ?>" width = "180px" class="img-rounded">
				</div>
			<?php }else{ ?>
				<div class = 'reddit-image pull-left' style = 'width:180px'>
					<img src = "<?php echo get_post_meta( $post->ID, 'wpedditimage', true ); ?>" width = "180px" class="img-rounded">
				</div>
			<?php } ?>
			
			</div>
			
			<div class = 'span5'>
				<div class = 'reddit-post pull-left'>
				<p class = 'title'><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
				<span class = 'tagline'>submitted <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?> by <?php the_author_posts_link(); ?> in <?php the_category(' '); ?></span> 
					
					<?php if(!is_single()){ ?>
					<p style = "text-align:justify">
					<?php the_excerpt(); ?> 
					</p>
					<?php }else{ ?>
					<?php the_content(); ?> 
					<?php } ?>
									<a href="<?php comments_link(); ?>">
				    <?php comments_number( 'no comments', 'one comment', '% comments' ); ?>. 
				</a>
				</div>
			

			</div>
			
			<div style="clear:both"></div>
			
				<div class = 'span8 pull-right'>
					<?php comments_template(); ?>
				</div>
			
			</div>
			
			<?php } ?>
			
			<?php endwhile; ?>

			<?php else: ?> 
				<p><?php _e('Sorry, no posts matched your criteria.'); ?></p> 
			<?php endif; ?>
	
	
            <?php echo get_next_posts_link('More Posts'); ?>
	
			
			<?php wp_reset_query(); ?>
			
<?php			}



add_filter( 'manage_edit-post_columns', 'wpeddit_post_columns' ) ;

function wpeddit_post_columns( $columns ) {

    $new_columns = array(

		'rating' => __('Ranking', 'WPeddit'),

    );
	
	return array_merge($columns, $new_columns);

}

add_action( 'manage_post_posts_custom_column', 'wpeddit_post_columnsw', 10, 2 );

function wpeddit_post_columnsw( $column, $post_id ) {
    global $post;

    switch( $column ) {
        
        
        case 'rating' :

            /* Get the post meta. */
            echo number_format((double)get_post_meta( $post_id, 'epicredvote', true ),0);

            break;

        /* Just break out of the switch statement for everything else. */
default:
            break;
    }
}

add_filter( 'manage_edit-post_sortable_columns', 'wpeddit_sortable_columns' );

function wpeddit_sortable_columns( $columns ) {

    $columns['rating'] = 'rating';

   
    return $columns;
}


/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'wpeddit_post_load' );

function wpeddit_post_load() {
    add_filter( 'request', 'wpeddit_sort_post' );
}

/* Sorts the pics. */
function wpeddit_sort_post( $vars ) {

    /* Check if we're viewing the 'picsmash' post type. */
    if ( isset( $vars['post_type'] ) && 'post' == $vars['post_type'] ) {

        /* Check if 'orderby' is set to 'rating'. */
        if ( isset( $vars['orderby'] ) && 'rating' == $vars['orderby'] ) {

            /* Merge the query vars with our custom variables. */
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => 'epicredvote',
                    'orderby' => 'meta_value_num'
                )
            );
        }
        

    }

    return $vars;
}


function wpeddit_comment_ranking($comment_id){
    $ups        =   get_comment_meta($comment_id,'wpeddit_comment_up',true);
    $downs      =   get_comment_meta($comment_id,'wpeddit_comment_down',true);
    $n = $ups + $downs;
    if($n == 0){
        return 0;
    }else{
    $z = 1.0;
    $phat = $ups / $n;
    $rating = sqrt($phat+$z*$z/(2*$n)-$z*(($phat*(1-$phat)+$z*$z/(4*$n))/$n))/(1+$z*$z/$n);
    }   
    update_comment_meta($comment_id,'wpeddit_comment_rank',$rating);
    return $rating;
}

add_action( 'wp_ajax_nopriv_epicred_vote_comment', 'epicred_vote_comment' );
add_action( 'wp_ajax_epicred_vote_comment', 'epicred_vote_comment' );

function epicred_vote_comment(){
    global $wpdb, $current_user;
    
    get_currentuserinfo();
    
    $wpdb->myo_ip   = $wpdb->prefix . 'epicred_comment';
        
    $option = (int)$_POST['option'];
    $current = (int)$_POST['current'];
    $postid = (int)$_POST['poll'];  
        
    //if we are locked via IP set the fid variable to be the IP address, otherwise log the member ID
    if(get_option('epicred_ip') == 'yes'){
        $ipAddr = isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ? $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
        $fid = "'" . $ipAddr . "'"; 
    }else{
        $fid = $current_user->ID;
    }
    
    $query = "SELECT epicred_option FROM $wpdb->myo_ip WHERE epicred_ip = $fid AND epicred_id = $postid";
    
    $al = $wpdb->get_var($query);
    
    
    if($al == NULL){
        $query = "INSERT INTO $wpdb->myo_ip ( epicred_id , epicred_ip, epicred_option) VALUES ( $postid, $fid, $option)";
        $wpdb->query($query);
    }else{
        $query = "UPDATE $wpdb->myo_ip SET epicred_option = $option WHERE epicred_ip = $fid AND epicred_id = $postid";
        $wpdb->query($query);
    }
    
    $ups        =   get_comment_meta($postid,'wpeddit_comment_up',true);
    $downs      =   get_comment_meta($postid,'wpeddit_comment_down',true);
    $vote       =   get_comment_meta($postid,'wpeddit_comment_votes',true);
    
        if($option == 1){
            if($al != 1){
                if($al == -1){
                $vote = $vote+2;    
                $downs = $downs - 1;
                $ups = $ups + 1;
                }else{
                $vote = $vote+1;
                $ups = $ups+1;
                }
            }
        }
        
        
        if($option == -1){
            
            if($al != -1){
                if($al == 1){
                    $vote = $vote-2;
                    $ups = $ups -1;
                    $downs = $downs + 1;
                }else{
                $vote = $vote-1;
                $downs = $downs + 1;
                }   
            }   
        }
        update_comment_meta($postid,'wpeddit_comment_votes',$vote);
        update_comment_meta($postid,'wpeddit_comment_up',$ups);
        update_comment_meta($postid,'wpeddit_comment_down',$downs);

    
        $response['poll'] = $postid;
        $response['vote'] = $vote;
    
    echo json_encode($response);
  
    // IMPORTANT: don't forget to "exit"
    exit;
}




