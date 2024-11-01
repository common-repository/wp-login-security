=== WP Login Security ===
Contributors: joshuascott94
Donate link: http://www.joshuascott.net/projects
Tags: authentication, whitelisting, admin, security, login
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 0.1.2

Whitelist User IP addresses. Sends an email to the admin if IP is unknown with one-time key.  

== Description ==

WP Login Security provides enhanced security for blog administrators by requiring administrators to register or whitelist their IP address.  
If the IP address is not recognized, the plugin will send an email to the blog administrator with a link that contains a one-time key.     

**What does this Plugin do?**

1. Each time a user logs in, the plugin will compare their existing IP address to the last seen IP address.
1. If the IP does not match or no IP addresses have been whitelisted, an email will be sent to the users registered email address.
1. The user must login to their email and click the included link, which contains the one-time password.  Note: passwords expire after 
1. The plugin can be configured to also send an email to the blog administrator as well as the user.  

**Upcoming Features**

1. Ability to update whitelist from within admin interface.
1. Custom set expiry time for one-time key
1. Admin activity audit/access log

== Installation ==

This Plugin works without you having to make any changes. 

1. Search for the plugin using the WordPress Plugin Installer OR download and unzip the directory into your plugins directory
1. Activate the Plugin through the 'Plugins' menu in WordPress - Upon activation, your current IP will be automatically whitelisted.
1. Enjoy the enhanced security!

== Frequently Asked Questions ==

= Can I help you develop this framework/Plugin? =

Yes, I am open to anyone with experience who can provide assistance in making this Plugin better.  Just [send](http://www.joshuascott.net/contact) me a message.

= How to ask a question? =

Click [here](http://www.joshuascott.net/contact) and ask me a question.

== Screenshots ==

There are currently no screenshots.

== Changelog ==

= 0.1.2 = 
* Default is now enabled after activation.  Plugin was not setup to provide protection after activation before.  

= 0.1.1 =
* Minor corrections of all version numbers

= Beta 0.1.0 =
* First Release. Provides base administrator whitelisting functionality.


