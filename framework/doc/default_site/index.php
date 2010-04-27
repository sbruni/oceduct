<?php
/**
 * Default Oceduct Framework index file
 */

/**
 * Default config file, if a setting is not set within it then a default is used
 */
require_once('./config.php');

/**
 * Disable entry website set within the config or index
 */
require_once(_OCEDUCT_.'framework/disabled.php');

/**
 * Sets up all includes
 * NOTE: command execution is at the bottom of THIS file NOT this one
 * NOTE: if you want to specify specific classes and not include all of them
 * then read the instructions in oceduct/include.php NOT the one below which is
 * the framework specific version
 */
require_once(_OCEDUCT_.'framework/include.php');

/**
 * Sitewide Custom functions
 * Custom functions provided by your web site, this is not required but suggested
 */
#require_once('functions.php');

// ini acl, this MUST be run when the user first logs in, otherwise
// the user won't have access to items intill the second run
acl();

//-------------------------------------
// Display the templates
//-------------------------------------

/**
 * Command Execution
 * Runs all commands and executes the templates
 */
require_once(_OCEDUCT_.'framework/cex.php');

?>
