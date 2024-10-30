=== Bookable Events ===
Contributors: sefran
Tags: bookable events, bookable posts, bookable, register, events, event, participants, registered users, posts, widget, plugin, custom fields, eventi prenotabili, post prenotabili, prenotare, reservar, partecipanti, participantes, usuarios registrados, utenti registrati
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.0.5

Plugin that makes a post 'bookable', using the custom fields.

== Description ==

This plugin allows you to create a bookable event using the custom fields. A post creator can make a post 'bookable' selecting the custom field 'bookable_event' (automatically created at the plugin activation) and setting a value for it, e.g. the 'yes' value.
The registered users can add/remove their participation to an event (represented by a post) by links at the bottom of the post.
This plugin provides also a widget displaying the list of the last 'bookable events' added.

== Installation ==

1. Upload the 'bookable_events' folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Optionally add 'Last bookable events' widget to your sidebar

== Internationalization ==

Support for: English (default), Italian, Spanish.
If you want the plugin in another language, the .pot file is in the `lang` directory.

== Usage ==

* Set 'bookable_event' custom field to an unempty string (e.g. "yes") if you want a bookable post
* Set 'max_num_participants' custom field to 0 if you want an unlimited number of participants

== Changelog ==

= 1.0.5 =
* 'max_num_participants' custom field added so it's possible set the max number of participants for an event.
* Added a link to view the participants' list (for logged in users).

= 1.0.4 =
* Added: registered users (to an event) list available putting the mouse cursor over the corresponding event link in the 'Last bookable events' widget (if the post is protected, it works only for authorized users).   
* Fixed registration/unregistration message for not logged users (now it's visible only on the proper post).
* Fixed title displaying in the widget control.

= 1.0.3 = 
* A widget displaying the list of the last 'bookable events' added.
* The 'bookable event' label no more near the title, but at the top of the post.

= 1.0.2 = 
* Fixed number version.

= 1.0.1 =
* Fixed the plugin name dir and textdomain name for i18n.

= 1.0 =
* Initial release version.