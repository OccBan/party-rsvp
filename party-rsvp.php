<?php
/*
Plugin Name: Party RSVP
Version: 1.0
Author: Ari Schenck
License: GPL2
*/

define('PARTY_RSVP_VERSION', '1.0');

define('PARTY_RSVP_FILE_PATH', dirname(__FILE__));

define('PARTY_RSVP_DIR_NAME', basename(PARTY_RSVP_FILE_PATH));

$siteurl = get_option('siteurl'); 

define('PLUGIN_PATH', $siteurl . '/wp-content/plugins/party-rsvp');

include_once (PARTY_RSVP_FILE_PATH . "/includes/partyrsvp_functions.php");

register_activation_hook( __FILE__, 'party_rsvp_install' );

register_deactivation_hook( __FILE__, 'party_rsvp_uninstall' );

add_action('wp_print_styles', 'add_styles');

function add_styles() {

	wp_register_style("partyRSVPStyles", PLUGIN_PATH . "/partyRSVP.css");
	
	wp_enqueue_style("partyRSVPStyles");

}

function rsvp_init_header(){

	wp_enqueue_script("jquery");

	wp_enqueue_script("thickbox");

	wp_enqueue_style("thickbox");
	
	wp_register_script("partyRSVP", PLUGIN_PATH . "/js/party_rsvp.js");
	wp_enqueue_script("partyRSVP");
}

	
function add_to_header(){
	?>
    <script type='text/javascript'>
		
		var $ = jQuery;
		
		var plugin_path = "<?= PLUGIN_PATH ?>";
		
		var ajaxurl = "<?= admin_url('admin-ajax.php'); ?>";
		
		var rsvpCookie; 
        		
		$(document).ready(function(){
			
			rsvpCookie = new Cookie("visitordata");
			
		});
		
	</script>
    <?
}

if( is_admin() ){
	include_once (PARTY_RSVP_FILE_PATH . "/admin.php");
    
	add_action('wp_ajax_nopriv_submit_rsvp', 'submit_rsvp');
	
	add_action('wp_ajax_submit_rsvp', 'submit_rsvp');
	
}else{
	add_action('wp_head', 'add_to_header');	
	
	add_action('init', 'rsvp_init_header');
}

function submit_rsvp(){
	
	global $wpdb;
	
	foreach($_REQUEST as $field => $value){
		${$field} = $wpdb->escape(urldecode($value));
	}
    
	$row = $wpdb->get_row("SELECT email FROM " . $wpdb->prefix . "PARTY_RSVP_invitees WHERE email='$email' AND event_id='$event_id'", ARRAY_N);

	if(count($row) > 0) {
		echo "error=duplicate|";
	}
	else{
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "PARTY_RSVP_invitees
					  VALUES(NULL, '$event_id', '$fname', '$lname', '$email', '$response', '$guests', NOW())");
		
		echo "success|";
	}
	
}


?>