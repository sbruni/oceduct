<?php
/**
 * Framework Include file
 *
 * Includes all required files/classes etc...
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// force the correct level of error reporting
// disable warnings for all of the oceduct framework
error_reporting(E_ALL & ~E_WARNING | E_STRICT);

/**
 * Global Include all script
 * includes the needed classes/functions/variables selected by
 * {@code $GLOBALS['_includeFiles_']}
 * @access private
 * @see $GLOBALS['_includeFiles_']
 * @since version 1.0.0
 */
require_once(_OCEDUCT_.'include.php');

/**
 * Redirect
 * 
 * @access private
 * @since 1.0.0
 */
require_once(_OCEDUCT_.'framework/redirect.php');

/**
 * Custom Framework specific constants
 * 
 * @since version 1.0.0
 */
require_once(_OCEDUCT_.'framework/constants.php');

/**
 * Custom Framework specific functions
 * 
 * @since version 1.0.0
 */
require_once(_OCEDUCT_.'framework/functions.php');

/**
 * All options/variables/etc... are initialized in here
 * 
 * @since version 1.0.0
 */
require_once(_OCEDUCT_.'framework/initialize.php');

?>
