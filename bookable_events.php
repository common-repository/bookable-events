<?php
/*
	Plugin Name: Bookable Events
	Plugin URI: http://provaqui.net84.net/wp/?p=176
	Description: This plugin allows you to create a bookable event using the custom fields. A post creator can make a post 'bookable' selecting the custom field 'bookable_event' (automatically created at the plugin activation) and setting a value for it, e.g. the 'yes' value.
	The registered users can add/remove their participation to an event (represented by a post) by links at the bottom of the post.
	This plugin provides also a widget displaying the list of the last 'bookable events' added.
	Version: 1.0.5
	Author: Francesca 'sefran' Secondo 
	Author URI: http://provaqui.net84.net/wp
*/

/*
	Copyright 2009  Francesca Secondo  (email : sefran2 (at) gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
	Global vars for this plugin:
	- $bookable_key: name of the custom field used to make a post 'bookable'. This custom field have to be unique.
	- $participant_key: name of the custom field used to memorize the event participants' username. This custom field can be not unique.
	- $max_num_participants_key: name of the custom field used to memorize the max number of participants. This custom field have to be unique.
	They are declared 'global' so the functions called by register_activation_hook() and register_deactivation_hook() have access to global variables.

	- $bookable: true if the post is 'bookable'.
	- $click_register: true if the user clicks on the registration link.
	- $click_unregister: true if the user clicks on the unregistration link.
	- $postid_clicked: post ID the user registers/unregisters for/from
	- $click_viewlist: true if the user clicks on the 'view list' link.
*/

global $bookable_key;
global $participant_key;
global $max_num_participants_key;
$bookable_key = 'bookable_event';
$participant_key = 'participant';
$max_num_participants_key = 'max_num_participants'; // default value 0 is for an unlimited number of participants

$bookable;
$click_register = $_GET['click_register'];
$click_unregister = $_GET['click_unregister'];
//$postid_clicked = $_GET['post'];
$postid_clicked = $_GET['p'];
$click_viewlist = $_GET['click_viewlist'];

/*
	Internationalization
*/
load_plugin_textdomain( 'bookable-events', 'wp-content/plugins/bookable-events/lang' );

/* 
	Function to be run when the plugin is activated. 
	It adds the custom field 'bookable_event', in this way the post creator can select it (setting any value for it), making the post 'bookable'.
*/
function bookable_events_activate() {
	global $bookable_key;
	global $max_num_participants_key;
	$bookable_value = '';
	$max_num_participants_value = 0;
	$unique = true; // There can be only one custom field named 'bookable_event'. The same for 'max_num_participants'.
	
	$allposts = get_posts('numberposts=0&post_type=post&post_status=');
	
	foreach( $allposts as $postinfo ) {
		add_post_meta( $postinfo->ID, $bookable_key, $bookable_value, $unique );
		add_post_meta( $postinfo->ID, $max_num_participants_key, $max_num_participants_value, $unique );
    }
}

/*
function set_custom_fields_for_new_posts() {
	global $bookable_key;
	global $max_num_participants_key;
	$bookable_value = '';
	$max_num_participants_value = 0;
	$unique = true; // There can be only one custom field named 'bookable_event'. The same for 'max_num_participants'.
	
	add_post_meta( get_the_ID(), $bookable_key, $bookable_value, $unique );
	add_post_meta( get_the_ID(), $max_num_participants_key, $max_num_participants_value, $unique );
	//add_post_meta( $bookable_key, $bookable_value, $unique );
	//add_post_meta( $max_num_participants_key, $max_num_participants_value, $unique );
}
*/

/* 
	Function to be run when the plugin is deactivated. 
	It removes all the custom fields added by the plugin ('bookable_event','participant'). It also makes unavailable the "Last bookable events" widget.
*/
function bookable_events_deactivate() {
	global $bookable_key;
	global $participant_key;
	global $max_num_participants_key;

	$allposts = get_posts('numberposts=0&post_type=post&post_status=');
	
	foreach( $allposts as $postinfo ) {
		delete_post_meta( $postinfo->ID , $bookable_key );
		delete_post_meta( $postinfo->ID , $participant_key );
		delete_post_meta( $postinfo->ID , $max_num_participants_key );
    }

	// Makes unavailable the "Last bookable events" widget
	unregister_sidebar_widget( __("Last bookable events", 'bookable-events') );
	// Removes the 'bookable_events_widget_control' callback
	unregister_widget_control( __("Last bookable events", 'bookable-events') );
}

/*
	Returns the xhtml registration link.
*/
function add_link_registration() {
	//return __("If you want to register for this event, click on the following link after login.", 'bookable-events')."<br/><a href='$PHP_SELF?click_register=true&post=".get_the_ID()."'>".__("Register!", 'bookable-events')."</a>";
	return __("If you want to register for this event, click on the following link after login.", 'bookable-events')."<br/><a href='$PHP_SELF?click_register=true&p=".get_the_ID()."'>".__("Register!", 'bookable-events')."</a>";
}

/*
	Returns the xhtml unregistration link.
*/
function add_link_unregistration() {
	//return __("If you want to unregister from this event, click on the following link after login.", 'bookable-events')."<br/><a href='$PHP_SELF?click_unregister=true&post=".get_the_ID()."'>".__("Unregister!", 'bookable-events')."</a>";
	return __("If you want to unregister from this event, click on the following link after login.", 'bookable-events')."<br/><a href='$PHP_SELF?click_unregister=true&p=".get_the_ID()."'>".__("Unregister!", 'bookable-events')."</a>";
}

/*
	Returns the xhtml link to view the participants' list.
*/
function add_link_viewlist() {
	return "<a href='$PHP_SELF?click_viewlist=true&p=".get_the_ID()."'>".__("View the participants' list", 'bookable-events')."</a>";
}

/*
	Checks if a post is bookable.
*/
function is_bookable() {
	global $bookable_key;
	global $bookable;
	$meta = get_post_custom();
	$meta_book_event = $meta[$bookable_key][0];

	if ( $meta_book_event != '' ) return $bookable = true;
	else return $bookable = false;
}

/*
	Adds the registration/unregistration links at the bottom of the 'bookable' post. Moreover, it adds the 'bookable event' label at the post beginning.
*/
function add_links_to_post( $content = '' ) {
	if ( is_bookable() && !post_password_required()) $content = "<div class='redtext'>".__("bookable event", 'bookable-events')."</div>$content<br/><hr/>".add_link_registration()."<br/>".add_link_unregistration()."<br/><br/>".add_link_viewlist()."<br/>";
	return $content;
}

/*
	Function to be run when the user clicks on the registration link. 
	It checks the user is logged and, if so, adds it to the event participants.
*/
function register_to_event( $content = '' ) {
	global $participant_key;
	global $max_num_participants_key;
	global $bookable;
	global $postid_clicked;

	if ( $bookable && is_user_logged_in() ) {
		$fs_current_user = wp_get_current_user();
		$mykey_values = get_post_custom_values($participant_key);
		$meta_value = $fs_current_user->user_login;
		$unique = false;
			
		$user_in_array = in_array( $meta_value, (array)$mykey_values );

		$custom_fields = get_post_custom();
		$max_num_participants_setted = $custom_fields[$max_num_participants_key][0];
		
		if (isset($mykey_values)) $num_partecipanti = count($mykey_values);
		else $num_partecipanti = 0;
		
		if ( !$user_in_array && ( get_the_ID() == $postid_clicked ) ) {
			if ( $max_num_participants_setted && ( $num_partecipanti >= $max_num_participants_setted) ) {
				$content = "$content<br/><div class='mgs_t1'>$fs_current_user->user_login, ".__("you can't be registered: sold out event!", 'bookable-events')."</div><br/>";
			} else {
				$content = "$content<br/><div class='mgs_t1'>$fs_current_user->user_login, ".__("thank you for the registration!", 'bookable-events')."</div><br/>";
				add_post_meta($postid_clicked, $participant_key, $meta_value, $unique);	

				// Before adding the first participant, the array is empty, so we have to recall the updated array
				$mykey_values = get_post_custom_values($participant_key); 
				
				$num_partecipanti = count($mykey_values);
			
				if ($num_partecipanti) {
					$content="$content<br/>".__("Registrered users for this event:", 'bookable-events');
					for ($i=0; $i<$num_partecipanti; $i++) {
						if ( $i == $num_partecipanti-1 ) $content = "$content ".$mykey_values[$i]." (".__("in total", 'bookable-events')." ".$num_partecipanti.").";
						else $content = "$content ".$mykey_values[$i].",";
					}
				} else {
					$content="$content<br/>".__("No registered users for this event.", 'bookable-events');
				}
			
				$content = "$content<br/><br/>";
			} 
		} elseif ( $user_in_array && ( get_the_ID() == $postid_clicked ) ) {
			$content = "$content<br/><div class='mgs_t1'>$fs_current_user->user_login, ".__("you are already registered!", 'bookable-events')."</div><br/>";
		}

	} elseif ( ( $bookable && !is_user_logged_in() ) && ( get_the_ID() == $postid_clicked ) ) {
		$content = "$content<br/><div class='mgs_t1'>".__("You must be logged in to register for this event!", 'bookable-events')."</div><br/>";
	}
	return $content;
}

/*
	Function to be run when the user clicks on the unregistration link.
	It checks the user is logged and, if so, removes it from the event participants.
*/
function unregister_from_event( $content = '' ) {
	global $participant_key;
	global $bookable;
	global $postid_clicked;

	if ( $bookable && is_user_logged_in() ) {
		$fs_current_user = wp_get_current_user();
		$mykey_values = get_post_custom_values($participant_key);
		$meta_value = $fs_current_user->user_login;
			
		$user_in_array = in_array( $meta_value, (array)$mykey_values );
		if ( !$user_in_array && ( get_the_ID() == $postid_clicked ) ) {
			$content = "$content<br/><div class='mgs_t1'>$fs_current_user->user_login, ".__("you have never been registered for this event!", 'bookable-events')."</div><br/>";
		} elseif ( $user_in_array && ( get_the_ID() == $postid_clicked ) ) {
			delete_post_meta($postid_clicked, $participant_key, $meta_value);
			$content = "$content<br/><div class='mgs_t1'>$fs_current_user->user_login, ".__("you have been unregistered!", 'bookable-events')."</div><br/>";

			// We have to recall the updated array for displaying the new participants' number.
			$mykey_values = get_post_custom_values($participant_key); 
				
			$num_partecipanti = count($mykey_values);
			
			if ($num_partecipanti) {
				$content="$content<br/>".__("Registrered users for this event:", 'bookable-events');
				for ($i=0; $i<$num_partecipanti; $i++) {
					if ( $i == $num_partecipanti-1 ) $content = "$content ".$mykey_values[$i]." (".__("in total", 'bookable-events')." ".$num_partecipanti.").";
					else $content = "$content ".$mykey_values[$i].",";
				}
			} else {
				$content="$content<br/>".__("No registered users for this event.", 'bookable-events');
			}
			
			$content = "$content<br/><br/>";
		}
	
	} elseif ( ( $bookable && !is_user_logged_in() ) && ( get_the_ID() == $postid_clicked ) ) {
		$content = "$content<br/><div class='mgs_t1'>".__("You must be logged in to unregister from this event!", 'bookable-events')."</div><br/>";
	}
	return $content;
}

/*
	Function to be run when the user clicks on the 'view list' link.
	It checks the user is logged and, if so, views the participants' list.
*/
function view_participants_list( $content = '' ) {
	global $participant_key;
	global $bookable;
	global $postid_clicked;

	if ( ( $bookable && is_user_logged_in() ) && ( get_the_ID() == $postid_clicked ) ) {
		$mykey_values = get_post_custom_values($participant_key); 
				
		$num_partecipanti = count($mykey_values);
			
		if ($num_partecipanti) {
				$content="$content<br/>".__("Registrered users for this event:", 'bookable-events');
				for ($i=0; $i<$num_partecipanti; $i++) {
					if ( $i == $num_partecipanti-1 ) $content = "$content ".$mykey_values[$i]." (".__("in total", 'bookable-events')." ".$num_partecipanti.").";
					else $content = "$content ".$mykey_values[$i].",";
				}
		} else {
				$content="$content<br/>".__("No registered users for this event.", 'bookable-events');
		}
			
		$content = "$content<br/><br/>";
	
	} elseif ( ( $bookable && !is_user_logged_in() ) && ( get_the_ID() == $postid_clicked ) ) {
		$content = "$content<br/><div class='mgs_t1'>".__("You must be logged in to view the participants' list for this event!", 'bookable-events')."</div><br/>";
	}

	return $content;
}


function my_css() {
	if ( !defined('WP_PLUGIN_URL') ) $my_css_file = get_bloginfo( 'url' )."/wp-content/plugins/bookable-events/bookable_events.css";
	else $my_css_file = WP_PLUGIN_URL.'/bookable-events/bookable_events.css';
	echo "<link type='text/css' rel='stylesheet' href='$my_css_file' />";
}

/*
	Registers the "Last bookable events" widget, in this way it will be included in the widgets palette. 
	Moreover, it adds the output of the 'bookable_events_widget_control' function to the admin interface as an inline popup.
*/
function bookable_events_widget_init() {
	$widget_options = array( 'classname' => 'widget_recent_bookable_events' );
	register_sidebar_widget(__("Last bookable events", 'bookable-events'), 'bookable_events_widget', $widget_options);   
	register_widget_control(__("Last bookable events", 'bookable-events'),'bookable_events_widget_control', 250,350);    
}

/*
	Outputs the content of the "Last bookable events" widget, that is the titles of 'bookable post'. 
*/
function bookable_events_widget($args) {
	global $bookable_key;
	global $participant_key;

	extract($args);
	$options = get_option('widget_recent_bookable_events');

	$title = empty($options['title']) ? __("Last bookable events", 'bookable-events') : apply_filters('widget_title', $options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 10 )
		$number = 10;

    echo $before_widget;
    echo $before_title . $title . $after_title;

	$allposts = get_posts('numberposts=0&post_type=post&post_status=');

	echo "<ul>";
	$count = 0;
	foreach( $allposts as $postinfo ) {
			$meta = get_post_custom($postinfo->ID);
			$meta_book_event = $meta[$bookable_key][0];
		
			if ( $meta_book_event != '' ) {
				if ( !post_password_required( $postinfo->ID ) ) {
					$participants_values = get_post_custom_values($participant_key, $postinfo->ID);
					$num_partecipanti = count($participants_values);
					if ($num_partecipanti) {
						$participants = $participants_values[0];
						for ($i=1; $i<$num_partecipanti; $i++) $participants = $participants.", ".$participants_values[$i];
						$participants = $participants.".";
					} else $participants = __("No registered users", 'bookable-events');
				} else $participants = __("Protected post", 'bookable-events');

				echo "<li><a href=".get_permalink($postinfo->ID)." title=\"$participants\">".$postinfo->post_title."</a></li>";
				$count++;
			}
			if ( $count > $number-1 ) break;
	}
	echo "</ul>";
	
	echo $after_widget; 
}

/*
	Display and process recent bookable events widget options form.
*/
function bookable_events_widget_control() {

	$options = $newoptions = get_option('widget_recent_bookable_events');

	if ( isset($_POST["recent-bookable-events-submit"]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["recent-bookable-events-title"]));
		$newoptions['number'] = (int) $_POST["recent-bookable-events-number"];
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_recent_bookable_events', $options);
	}

	$title = attribute_escape($options['title']);

	if ( !$number = (int) $options['number'] ) $number = 5;
	

	echo "<p><label for='recent-bookable-events-title'>".__("Title", 'bookable-events')."<input class='widefat' id='recent-bookable-events-title' name='recent-bookable-events-title' type='text' value=\"$title\" /></label></p>
			<p>
				<label for='recent-bookable-events-number'>".__("Number of posts to show", 'bookable-events')."<input style='width: 25px; text-align: center;' id='recent-bookable-events-number' name='recent-bookable-events-number' type='text' value=\"$number\" /></label>
				<br />
				<small>".__("(at most 10)", 'bookable-events')."</small>
			</p>
			<input type='hidden' id='recent-bookable-events-submit' name='recent-bookable-events-submit' value='1' />";
}

/* 
	Registration of the plugin function 'bookable_events_activate' to be run when the plugin is activated. 
*/
register_activation_hook( __FILE__, 'bookable_events_activate' );

/* 
	Registration of the plugin function 'bookable_events_deactivate' to be run when the plugin is deactivated. 
*/
register_deactivation_hook( __FILE__, 'bookable_events_deactivate' );

//add_action ('post-new', 'set_custom_fields_for_new_posts');
//add_action ('edit_form_advanced', 'set_custom_fields_for_new_posts');

/*
	Loads my css 
*/
add_action( 'wp_head', 'my_css' );
//add_action( 'wp_print_styles', 'my_css');

/* 
	Addition of the filter function 'add_links_to_post' to the 'the_content' (so the post content is modified as it is displayed in the browser screen).
	The registration/unregistration links are added to a 'bookable' post. Moreover, the 'bookable event' label is added at the post beginning.
*/
add_filter( 'the_content', 'add_links_to_post' );

/* 
	Addition of the filter function 'register_to_event' to the 'the_content' if the user clicks on the registration link.
*/
if ($click_register) {
	add_filter( 'the_content', 'register_to_event' );
}

/* 
	Addition of the filter function 'unregister_from_event' to the 'the_content' if the user clicks on the unregistration link.
*/
if ($click_unregister) {
	add_filter( 'the_content', 'unregister_from_event' );
}

/* 
	Addition of the filter function 'view_participants_list' to the 'the_content' if the user clicks on the 'view list' link.
*/
if ($click_viewlist) {
	add_filter( 'the_content', 'view_participants_list' );
}

/*
	Hooks the 'bookable_events_widget_init' function to widgets_init, so this function will be run when WordPress loads the list of widgets.
*/
add_action('widgets_init', 'bookable_events_widget_init');

?>