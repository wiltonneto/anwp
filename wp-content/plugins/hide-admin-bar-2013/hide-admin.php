<?php
/*
Plugin Name: Remove Admin Bar
Plugin URI: http://samberson.co.uk
Description: Simply removes the admin bar from all users. Written for Our Village.
Version: 1.0
Author: Sam Berson
Author URI: http://samberson.co.uk
License: None
*/

add_filter('show_admin_bar', '__return_false');  

?>