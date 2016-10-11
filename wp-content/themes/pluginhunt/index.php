<?php
$mobile_force = 0;
if(wp_is_mobile() || $mobile_force == 1){
	require_once dirname( __FILE__ ) . '/index-mobile.php';
}else{
	if(of_get_option('ph_layout_style') == 'index-2'){
		require_once dirname( __FILE__ ) . '/index-2.php';
	}else{
		require_once dirname( __FILE__ ) . '/index-1.php';
	}	
}
?>