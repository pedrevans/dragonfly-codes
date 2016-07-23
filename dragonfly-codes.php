<?php
/**
 * Implement codes functionality for DragonFly page
 *
 * @author Peter Evans
 * @package DragonFly
 * @since 1.0
 */

/*
Plugin Name: Dragonfly Codes
Description: Make a shortcode available for a codes page
Version: 1.0
Author: Peter Evans
Author URI: http://appmotivate.com
License: all rights reserved
*/

/* Dragonfly stuff */
define(DRAGONFLY_SALT, 'Gareth Bale is a Deity');
define(DRAGONFLY_CODE_MAX, 7);

require_once( 'includes/df-codes-controller.php' );

error_log('DragonFly Codes Plugin');
$df = new DfCodesController();
?>
