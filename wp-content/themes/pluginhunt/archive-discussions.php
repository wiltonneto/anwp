<?php
$mobile_force = 0;
if(wp_is_mobile() || $mobile_force == 1){
	require_once dirname( __FILE__ ) . '/archive-discussions-mobile.php';
}else{
	require_once dirname( __FILE__ ) . '/archive-discussions-desktop.php';
}
?>