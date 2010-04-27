<?php
/**
 * Framework Ntlm authentication
 *
 * Logs a user in via ntlm
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Global variable used to specify which classes/functions/variables
 * we want to include with the script
 * 
 * @var array
 * @since version 1.1.0
 */
$GLOBALS['_includeFiles_'] = array(
	// classess
	'classes' => array(
		'Authentication',
		'HttpHeaders'
	)
);

/**
 * Global Include script
 * includes the needed classes/functions selected by
 * @see $GLOBALS['_includeFiles_']
 * @since version 1.0.0
 */
require_once(_OCEDUCT_.'include.php');

try
{
	/**
	 * @var object authentication object
	 * @since version 1.0.0
	 */
	$authentication = new Authentication();
	// login directly to NTLM

	$authentication->loginNtlm('direct');
	// NOTE: below this will never be read (unless exception is thrown)
	// because there is an exit() within the above method
}
catch (Exception $e)
{
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

?>
