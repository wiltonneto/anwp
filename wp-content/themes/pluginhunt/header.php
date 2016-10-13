<!DOCTYPE html>
<html <?php language_attributes(); ?> xmlns:fb="http://ogp.me/ns/fb#" id="desktop">
  <head>
    <?php  $desc = esc_html(wp_trim_words( $post->post_content , 40, '...' )); ?>
    <meta charset="utf-8">
    <title><?php wp_title(''); ?></title> 
    <meta name="description" content="<?php echo $desc; ?>">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">

    <meta name="twitter:widgets:csp" content="on">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
<script>window.twttr = (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0],
    t = window.twttr || {};
  if (d.getElementById(id)) return;
  js = d.createElement(s);
  js.id = id;
  js.src = "https://platform.twitter.com/widgets.js";
  fjs.parentNode.insertBefore(js, fjs);
 
  t._e = [];
  t.ready = function(f) {
    t._e.push(f);
  };
 
  return t;
}(document, "script", "twitter-wjs"));</script>

<?php  		
global $post;
#}code used for throttling of submitted content...
$current_user = wp_get_current_user();
if ( 0 == $current_user->ID ) {
    // Not logged in.
} else {
    $ehacklast = get_user_meta($current_user->ID, 'ehacklast', true);
    $ehacksince = time() - $ehacklast;
    echo '<script>var ehacklast = ' . $ehacksince . '</script>';
} ?>
	

    <meta property="og:title" content="<?php wp_title(); ?>"/>
    <meta property="og:site_name" content="<?php echo get_bloginfo( 'name' ); ?>"/>
    <meta property="og:description" content="<?php echo $desc; ?>"/> 

<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ); ?>
 <meta property="og:image" content="<?php echo $image[0]; ?>"/> 

   
<?php
if ( ! isset( $content_width ) ) $content_width = 900;

wp_head();   
?>
<style>
.ph-nav-center a .active, .ph-nav-center a:hover, {
    color: <?php echo of_get_option('ph_main_color'); ?>;
}
.newsletter-box input[type=submit],.ph-layout-2-body .mailchimp-sub .newsletter-box .subscribeButton {
background:  <?php echo of_get_option('ph_main_color'); ?>;
}

.hunt-row .none .score{
  color: <?php echo of_get_option('ph_novote'); ?> !important;
}

.ph-layout-2-body .mailchimp-sub .newsletter-box .subscribeButton{
  border: 1px solid <?php echo of_get_option('ph_main_color'); ?>
}

.post-submission--form-media-section .media-item, .ph-layout-2-body{
background-color: <?php echo of_get_option('ph_secondary_color'); ?>;
border: 1px solid <?php echo of_get_option('ph_secondary_color'); ?>;
}
.modal-post-submission--header.ph-newsubmit, .ph-newsubmit .modal-post-submission--header {
background:  <?php echo of_get_option('ph_newpost_header'); ?>;
}
#post-submission input[type=submit] {
background:  <?php echo of_get_option('ph_newpost_header'); ?>;
}
.none .fa{
  color: <?php echo of_get_option('ph_novote'); ?>;
}

.post-header .get-it {
    background: <?php echo of_get_option('ph_main_color'); ?>;
}
.media-placeholder a:hover, .single-post .upmod {
    color:  <?php echo of_get_option('ph_main_color'); ?>;
    text-decoration: underline;
}
.site--header{
  background: <?php echo of_get_option('ph_menu_color'); ?>;
}
.none .score{
  color: <?php echo of_get_option('ph_novote'); ?>;
}
.m-active{color:<?php echo of_get_option('ph_newpost_header'); ?> !important;}
.m-active-g{color:<?php echo of_get_option('ph_discuss_header'); ?> !important;}


.nogrid .ph-layout-2 .blue {
    background: <?php echo of_get_option('ph_main_color'); ?>;
    color: white;
    border: 1px solid <?php echo of_get_option('ph_main_color'); ?>;
}
.nogrid .ph-layout-2 .blue .fa {
    color: <?php echo of_get_option('ph_vote'); ?>;;
}
.nogrid .ph-layout-2 .blue .score {
    color: <?php echo of_get_option('ph_vote'); ?>;
}

</style>
<?php 
if(!wp_is_mobile()){
echo '<style>' . of_get_option('ph_custom_css') .'</style>';
wp_enqueue_style('custom_css', get_template_directory_uri() . '/css/custom_desktop.css' );
}else{
echo '<style>' . of_get_option('ph_mobile_custom_css') .'</style>';  
wp_enqueue_style('custom_css', get_template_directory_uri() . '/css/custom_mobile.css' );
}
?>
  <script>
    <?php 
      $ph_ga = of_get_option('ph_ga');
      echo $ph_ga;
    ?>
    </script>
</head>
<div id="fb-root"></div>
 <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '427029257462972',
          xfbml      : true,
          version    : 'v2.0'
        });
      };

      (function(d, s, id){
         var js, fjs = d.getElementsByTagName(s)[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement(s); js.id = id;
         js.src = "//connect.facebook.net/en_US/sdk.js";
         fjs.parentNode.insertBefore(js, fjs);
       }(document, 'script', 'facebook-jssdk'));
</script>

<body <?php 
  if (!isset($class)) $class = ''; 
  body_class( $class ); ?>>
<div id='wrapper'>
<header class="site--header headroom headroom--top" data-auto-hide="true">

  <nav class="navbar nav-fixed-top" role="navigation">
    <div class="container-header">


      <div id='leftm'>
        <div class='ph-logo'>
          <div class='site-logo'>
            <span class='pull-left'><a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>' rel='home'><img src='<?php echo esc_url('https://s3-sa-east-1.amazonaws.com/achenovidade/assets/an-logo.png'); ?>' alt='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>'></a>
            </span>
          </div>
        </div>
        <form class="navbar-form navbar-left header--search-form" role="search" action="<?php echo esc_url( home_url('/') );?>">
          <div class="form-group">
            <label class="header--search-form--label" for="search" data-reactid=".0.0.0.0.0"><span><svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg"><title>Oval 95</title><path d="M9.383 10.347c-.987.78-2.233 1.244-3.588 1.244C2.595 11.59 0 8.997 0 5.796 0 2.595 2.595 0 5.795 0c3.2 0 5.796 2.595 5.796 5.795 0 1.355-.464 2.6-1.243 3.588L15 14.036l-.964.964-4.653-4.653zm-3.588-.12c2.448 0 4.432-1.984 4.432-4.432 0-2.447-1.984-4.43-4.432-4.43-2.447 0-4.43 1.983-4.43 4.43 0 2.448 1.983 4.432 4.43 4.432z" fill="#BBB" fill-rule="evenodd"></path></svg></span></label>
            <input type="text" class="form-control header--search-form--field" placeholder = "<?php _e('Descubra sua próxima novidade...','pluginhunt'); ?>" value="<?php the_search_query(); ?>" name="s" id="s">
          </div>
          <button type="submit" class="btn btn-default hide"><?php _e('Submit','pluginhunt');?></button>
        </form>
        <?php if( of_get_option('ph_grid_toggle') == 1){ $h = 'hide'; }else{ $h = ''; } ?>
        <?php if( of_get_option('ph_grid_on') == 1) {  ?>
          <div class='ph-grid-view'><i class="ph-view-container fa fa-list-ul <?php echo $h;?>"></i></div>
        <?php }else{ ?>
          <div class='ph-grid-view'><i class="ph-view-container fa fa-th <?php echo $h; ?>"></i></div>
         <?php } ?>
      </div>

      <div id='rightm'>

            <li class='dropdown pull-left phmenu'>
               <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><svg width="20" height="4" viewBox="0 0 20 4" xmlns="http://www.w3.org/2000/svg"><path d="M2 4c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm8 0c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm8 0c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z" fill="#BBB" fill-rule="evenodd"></path></svg></a>
              <ul class="dropdown-menu ph-dots" role="menu"> 
              <?php wp_nav_menu( array( 'container' => '','container_class' => '','theme_location' => 'primary','menu_class' => 'nav','walker' => new Bootstrap_Walker()) ); ?>
              </ul>
            </li>


        <span class="pull-right topnav-options">
          <span class='usermeta'>
                            <nav>
            <span class="inline">
                    <div class='new-post-button'><svg width="22" height="22" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg"><path d="M10 12H0v-2h10V0h2v10h10v2H12v10h-2V12z" fill="#BBB" fill-rule="evenodd"></path></svg></div>

                    <?php do_action('epic-notify'); ?>


                    <span class='hopt'>
                      <?php
                  if ( is_user_logged_in() ){
                  $size='35';
                  $current_user = wp_get_current_user();
                  $email = $current_user->user_email;
                  $author_id = $current_user->ID;
                  ?>
                    <span class="dropdown inline ph-drop-arr">
                      <a class="dropdown-toggle small" id="account-dropdown" role="button" data-toggle="dropdown" href="#">
                        <span class="strongest">            
                            <?php 
                            $args = array( 'class' => 'img-rounded');
                            echo get_avatar($author_id, 32); ?>
                        </span>
                      </a>
                      <ul class="dropdown-menu" id="user-dropdown-menu" role="menu" aria-labelledby="account-dropdown">
                        <li><a href="<?php echo get_author_posts_url( $author_id ); ?>"><i class="icon-fixed-width icon-user"></i><?php _e('My Profile','pluginhunt'); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/' ) ) . of_get_option('ph_edit_profile','/your-profile'); ?>"><i class="icon-fixed-width icon-user"></i><?php _e('Edit Profile','pluginhunt'); ?></a></li>
                        <li><a href="<?php echo wp_logout_url(); ?>" title="Logout"><?php _e('Logout','pluginhunt'); ?></a></li>
                      </ul>
                  </span>
                  <?php
                  }else{
                    $logged = 'loggedout';
                  ?>
                      <?php $surl = get_home_url(); ?>
                      <span class='login-link-header'>
                          <a href="#" class='ph-log-new'><?php _e('Login','pluginhunt'); ?></a>
                      </span>
                      <?php   } ?>
                     </span>
                   </span>  
              <?php
              global $woocommerce;
               // $cart_url = $woocommerce->cart->get_cart_url();
                ?>     
                   <div class='ph-woo-cart hide'><a href='<?php echo $cart_url;?>'><i class='fa fa-shopping-cart'></i></a></div>   

                </nav>
              </span>
          </span>
        </span>
      </div> <!-- end rightm -->


      
      <div class='mobile-menu'>
<a href='<?php echo esc_url( home_url( '/' ) ); ?>' title='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>' rel='home'><img src='<?php echo esc_url( of_get_option('main_logo') ); ?>' alt='<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>'></a>
        <span class="dropdown inline ph-drop-arr ph-main-logo-drop">
          <a class="dropdown-toggle small" id="account-dropdown" role="button" data-toggle="dropdown" href="#">
            <span class="strongest">            
              <i class='fa fa-chevron-down'></i>
            </span>
          </a>
          <ul class="dropdown-menu" id="main-dropdown-menu" role="menu" aria-labelledby="account-dropdown">
            <?php wp_nav_menu( array( 'theme_location' => 'main',   'container_class' => 'menu-c') ); ?>
          </ul>
      </span>
    </div>



      <div id = 'midm'>
		
        <div class='ph-nav-center'>
          <?php 
          wp_nav_menu( array( 'theme_location' => 'main',   'container_class' => 'menu-c') ); 

         ?> 

        </div>


	</div>
  </div>    <!-- end of header container -->
</nav>
</header>

<div style='clear:both'></div>
<!-- new post modal -->

  <?php // } ?>
</div>

<div class='modal-overlay-lightbox'>

  <a class="modal--close-lb <?php echo $mc; ?>" href="#" title="Close">
    <span>
      <svg width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 4.586l4.24-4.24c.395-.395 1.026-.392 1.416-.002.393.393.39 1.024 0 1.415L7.413 6l4.24 4.24c.395.395.392 1.026.002 1.416-.393.393-1.024.39-1.415 0L6 7.413l-4.24 4.24c-.395.395-1.026.392-1.416.002-.393-.393-.39-1.024 0-1.415L4.587 6 .347 1.76C-.05 1.364-.048.733.342.343c.393-.393 1.024-.39 1.415 0L6 4.587z" fill-rule="evenodd"></path>
      </svg>
    </span>
  </a>



  <div class='ph-lb-post-image'></div>
  <div class="ph-lb-nav"><div class="v-left-lb"><span><svg width="26" height="46" viewBox="0 0 26 46" xmlns="http://www.w3.org/2000/svg"><path d="M24 2L3 23l21 21" stroke-width="4" fill="none"></path></svg></span></div><div class="lb-mid"><span id='lb-num'>1</span><span> <?php _e('of','pluginhunt');?> </span><span id='lb-tot'></span></div><div class="v-right-lb"><span><svg width="26" height="46" viewBox="0 0 26 46" xmlns="http://www.w3.org/2000/svg"><path d="M2 2l21 21L2 44" stroke-width="4" fill="none"></path></svg></span></div></div>
</div>

<!-- new post modal -->
<div class="modal-overlay-new">
  <?php if(wp_is_mobile()){ $mc = 'v-mobile'; }else{ $mc = 'v-desktop'; } ?>
  <a class="modal--close-new <?php echo $mc; ?>" href="#" title="Close">
    <span>
      <svg width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 4.586l4.24-4.24c.395-.395 1.026-.392 1.416-.002.393.393.39 1.024 0 1.415L7.413 6l4.24 4.24c.395.395.392 1.026.002 1.416-.393.393-1.024.39-1.415 0L6 7.413l-4.24 4.24c-.395.395-1.026.392-1.416.002-.393-.393-.39-1.024 0-1.415L4.587 6 .347 1.76C-.05 1.364-.048.733.342.343c.393-.393 1.024-.39 1.415 0L6 4.587z" fill-rule="evenodd"></path>
      </svg>
    </span>
  </a>

  <div class="new-post-modal">  
     <div class='modal-loading'>
        <i class="fa fa-spinner fa-spin"></i>
    </div>
    <div class="new-modal-container">

      <!-- new post form -->

  <?php if(current_user_can( 'edit_posts' )){ 

    if(of_get_option('ph_full_content') == 0) {
      $full = 'post-submission-full';
    }else{
      $full = '';
    }

    ?>
  <form class="post-submission <?php echo $full; ?>" id="post-submission">
    <header class="modal-post-submission--header ph-newsubmit">
      <div class='new-post-info'>
        <h1><span><?php echo of_get_option('ph_newpost_title','Found something?'); ?></h1>
        <h2><?php echo of_get_option('ph_newpost_subtitle','Hunt it'); ?></h2>
      </div>
      <div class='new-discuss-info hide'>
        <h1><span><?php echo of_get_option('ph_newdis_title','Want to talk?'); ?></h1>
        <h2><?php echo of_get_option('ph_newdis_subtitle','Discuss it'); ?></h2>
      </div>
      <?php if(of_get_option('ph_enable_marketplace') == 1){ ?>
      <div class='new-woo-info hide'>
        <h1><span><?php echo of_get_option('ph_newwoo_title','Got something to sell?'); ?></h1>
        <h2><?php echo of_get_option('ph_newwoo_subtitle','Sell it'); ?></h2>
      </div>
      <?php } ?>
    </header>


    <input name='ph-post-or-dis' id='ph-post-or-dis' type='hidden' value=1/>

    <div class="post-type-choice">
      <?php   do_action('ph_post_type_choice');  ?>
    </div>

      <div class="post-submission--body">
        <?php do_action('ph_form_extras'); ?>

        <div class="alert alert-warning alert-dismissible hide" role="alert" id='form-alert'>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong><?php _e('Warning','pluginhunt'); ?></strong> <?php _e('You have not filled in the form correctly','pluginhunt'); ?>
</div>

        <?php 

        if(of_get_option('ph_type_drop') == 1){
          ph_type(); 
        }

        ?>
        

        <?php ph_category(); ?>

        <?php ph_product_title(); ?>
        <?php ph_discuss_title(); ?>

      <?php if(of_get_option('ph_enable_marketplace')){ ?>
        <?php ph_woo_form(); ?>
        <?php } ?>

       <?php ph_product_link(); ?>

        <?php 
        if( of_get_option('ph_full_content') == 1) { 
          ph_product_excerpt();
        }else{
          ph_product_content();
        }
        ph_dis_content();


        ?>

      <div class="post-submission--form-media-section post-submission--form-row">
        <label class="form--label" for="media_url">
          <span class="form--label-icon">
            <svg width="16" height="14" viewBox="0 0 16 14" xmlns="http://www.w3.org/2000/svg"><path d="M14 1.167c0 .397.373.833 1 .833H1c.627 0 1-.436 1-.833v11.666C2 12.436 1.627 12 1 12h14c-.627 0-1 .436-1 .833V1.167zm2 0v11.666c0 .645-.448 1.167-1 1.167H1c-.552 0-1-.522-1-1.167V1.167C0 .522.448 0 1 0h14c.552 0 1 .522 1 1.167zM4 10l2.246-3.935 5.915 3.84L4 10zm6-5c0 .552.448 1 1 1s1-.448 1-1-.448-1-1-1-1 .448-1 1z" fill="#BBB" fill-rule="evenodd"></path>
            </svg>
          </span>
          <span><?php _e('Media','pluginhunt');?></span>
      </label>
      <div class="post-submission--form-field-group">
        <div class="form--field">
          <input class="form--input" name="media_url" placeholder="<?php _e("Paste a direct link to an image or a YouTube video","pluginhunt");?>" type="text" id="media_url">
          <div>
            <a class="trigger-upload" href="#" id="_unique_name_button" data-pid='1'>+ <?php _e('Upload an image','pluginhunt');?></a><input accept="image/gif, image/jpeg, image/png" class="uploader" type="file">
            <span class='invalid'><span>
          </div>
        </div>
      <div class="media-items"></div>
      </div>
    </div>

    <?php  

    if(of_get_option('ph_type_avail') == 1){

      ph_availability(); 

    }

    ?>



  </div>


<div class="post-submission--footer"><input class="button ph-newsubmit-button" type="submit" value="<?php echo of_get_option('ph_newpost_cta'); ?>" data-reactid=".4.3.0"></div>

</form>
<?php }else{  ?>
<header class="modal--header">
  <h1 class="modal--header--title"><?php _e('Posting on ','pluginhunt');?><?php echo get_bloginfo( 'name' ); ?></h1>
  <p class="modal--header--description"><?php _e('An invite is needed to submit content','pluginhunt');?></p>
</header>

<div class="p-new-post">
  <p class="s-p">
      <?php echo of_get_option('ph_lockout'); ?>
  </p>


  <p class="s-p"><a href="<?php echo of_get_option('ph_faq');?>"><?php _e('Learn more about invites','pluginhunt');?></a></p>
</div>

<?php } ?>

      <!-- end new post form -->


    </div>
  </div>
</div>



<!-- post modal -->
<div class="modal-overlay">

  <a class="modal--close <?php echo $mc; ?>" href="javascript:;" title="Close">
    <span>
      <svg width="12" height="12" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 4.586l4.24-4.24c.395-.395 1.026-.392 1.416-.002.393.393.39 1.024 0 1.415L7.413 6l4.24 4.24c.395.395.392 1.026.002 1.416-.393.393-1.024.39-1.415 0L6 7.413l-4.24 4.24c-.395.395-1.026.392-1.416.002-.393-.393-.39-1.024 0-1.415L4.587 6 .347 1.76C-.05 1.364-.048.733.342.343c.393-.393 1.024-.39 1.415 0L6 4.587z" fill-rule="evenodd"></path>
      </svg>
    </span>
  </a>
  <div class="post-detail--navigation hide">
    <button class="post-detail--navigation--button" data-action="open-modal" data-href="/tech/ultimate-startup-decision-maker" >
      <span>
        <svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
          <path d="M.256 5.498c0-.255.098-.51.292-.703L4.795.548C5.18.163 5.817.16 6.207.55c.393.393.392 1.023.002 1.412L2.67 5.5l3.54 3.538c.384.384.388 1.02-.003 1.412-.393.393-1.023.39-1.412.002L.548 6.205c-.192-.192-.29-.447-.29-.702z" fill="#948B88" fill-rule="evenodd"></path>
        </svg>
      </span>
    </button>
    <button class="post-detail--navigation--button"  data-action="open-modal" data-modal-replace="true" title="Ultimate Startup Decision Maker">
      <span data-reactid=".6.1.1.0">
        <svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
          <path d="M6.744 5.502c0 .255-.098.51-.292.703l-4.247 4.247c-.385.385-1.022.388-1.412-.002C.4 10.057.4 9.427.79 9.038L4.33 5.5.79 1.962C.407 1.578.403.942.794.55 1.186.157 1.816.16 2.204.548l4.248 4.247c.192.192.29.447.29.702z" fill="#948B88" fill-rule="evenodd"></path>
        </svg>
      </span>
    </button>
  </div>

  <div class="show-post-modal">  
     <div class='modal-loading'>
        <i class="fa fa-spinner fa-spin"></i>
    </div>
    <div class="modal-container"></div>
  </div>
</div>

</div>

<a id="ph-log-social-new" href="#animatedModal" style="display:none">.</a>

