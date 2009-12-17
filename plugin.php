<?php
/*
Plugin Name: Draft Post Notifier
Plugin URI: http://github.com/kennethreitz/draft-post-notifier-wordpress-plugin
Description: What's the point of post moderation if you don't know about it? Exactly. So, this emails editors and admins.
Author: Kenneth Reitz
Version: 0.9
Author URI: http://www.kennethreitz.com

Based on Draft Notification by Dagon Design ( http://www.dagondesign.com ).

*/


function dddn_process($id) {

	// emails anyone on or above this level
	$email_user_level = 7;

	global $wpdb;
	
	$tp = $wpdb->prefix;

	$result = $wpdb->get_row("
		SELECT post_status, post_title, user_login, user_nicename, display_name 
		FROM {$tp}posts, {$tp}users 
		WHERE {$tp}posts.post_author = {$tp}users.ID 
		AND {$tp}posts.ID = '$id'
	");

	if (($result->post_status == "draft") || ($result->post_status == "pending")) {

		$message = "";
		$message .= "A draft was updated on '" . get_bloginfo('name') . "'\n\n";
		$message .= "Title: " . $result->post_title . "\n\n";

		// *** Choose one of the following options to show the author's name
	
		$message .= "Author: " . $result->display_name . "\n\n";
		// $message .= "Author: " . $result->user_nicename . "\n\n";
		// $message .= "Author: " . $result->user_login . "\n\n";

		$message .= "Link: " . get_permalink($id);
		$subject = "Draft Updated on '" . get_bloginfo('name') . "'";
		$editors = $wpdb->get_results("SELECT user_id FROM {$tp}usermeta WHERE {$tp}usermeta.meta_value >= " . $email_user_level);		
		$recipient = "";	
		
		foreach ($editors as $editor) {			
			$user_info = get_userdata($editor->user_id);
			$recipient .= $user_info->user_email . ','; 
		} 
		
		mail($recipient, $subject, $message);

	}
}


add_action('save_post', 'dddn_process');

?>