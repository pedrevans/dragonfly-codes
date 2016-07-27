DragonFly codes

The idea is that you get the user to find some codes (maybe you hid them on the web site somewhere)
and with this plugin, he gets a form where he can type the codes in and send them to you.
The codes get checked, and if they match the ones you set for the page, the admin gets a mail
about the winner, and the user gets an appropriate message.

You can use this on multiple different pages with different code sets.

Use as follows:

Unzip the plugin into wp-content/plugins.

Create a WordPress page or post.

Enter the dragonfly_codes shortcode on the page.

The short code has options "max_codes" (number of input fields to draw) and "mail_to", neither of which must be set.

    [dragonfly_codes]

will make 7 fields available for entering codes and
will send a mail to the WordPress blog admin.

    [dragonfly_codes max_codes="5" mail_to="info@email-to-foo.net"] 

and will make 5 fields available for entering codes and will send a mail to the given address.

You can put any other text you like on the page.

You're not finished just yet!

Now create a custom field for the page or post called "dragonfly_codes".
You can activate custom fields under the Screen Options on the top right of your editor window.

Give the custome field "dragonfly_codes" a value consisting of all the codes separated by spaces.

E.g "123 456 789 AB246" defines 4 codes. The codes are case insensitive. Codes cannot contain a space.

You can have different sets of codes on different pages.

There are loads of CSS classes for you to use to style it. Do it in wp-content/plugins/dragonfly-codes/static/css/layout.css

Navigate to the page to use it.

It doesn't mmatter in which order the user types in the codes.

The following constants are defines in dragonfly-codes.php. You should at least give DRAGONFL_SALT a difficult
to guess value.

define(DRAGONFLY_SALT, 'xxxxx'); // you can use any unguessable string here
define(DRAGONFLY_CODE_MAX, 7); // this is the max number of fields you make available for typing in the codes.
