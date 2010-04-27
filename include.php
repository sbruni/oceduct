<?php
/**
 * Includes Classes or Functions
 *
 * This does NOT include the framework, this file is ment to make it easy to
 * include class or functions.
 *
 * $GLOBALS['_includeFiles_']
 * If the above variable is set then ONLY those classes/functions will
 * be included.
 *
 * If the array IS set AND empty then NO classes/functions will be included
 * If the array is NOT set then ALL classes/functions WILL be included
 *
 * Example:
 * <code>
 * $GLOBALS['_includeFiles_'] = array(
 *    // classess
 *    'classes' => array(
 *       'Authentication',
 *       'FileDownload',
 *       'forms',
 *       'HttpCache',
 *       'HttpHeaders',
 *       'HomeReview',
 *       'Image',
 *       'Io',
 *       'Ldap',
 *       'ModellTemplate',
 *       'MySql',
 *       'Poll',
 *       'PostgreSql',
 *       'Scraper',
 *       'SoapMessaging',
 *       'smtp',
 *       'Stream',
 *    ),
 *    // functions
 *    'functions' => array(
 *       'General',
 *       'Text',
 *       'Errors',
 *       'Conversion'
 *    )
 * );
 * </code
 *
 * NOTE: the class/function MUST be listed in this file otherwise they will NOT be included.
 * 
 *
 * This file MUST be in the top of the oceduct directory, the directories
 * classes and functions should be right below it.
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * List of ALL available classes
 * The key of the array MUST be the class name
 * Subarray:
 * fn(string) (required) = filename of the class
 *
 * rc(array) = required classes (use class name), the current class requires
 * that these other classes be available, ALL classes (including execptions etc..
 * SHOULD be included. The order does NOT matter.
 *
 * @var array
 * @since version 1.0.0
 */
$GLOBALS['iClasses'] = array(
	'Authentication' => array(
		'fn' => 'class.auth.php',
		'rc' => array(
			'AuthenticationException'
		)
	),
	'AuthenticationException' => array(
		'fn' => 'class.exception.auth.php',
		'rc' => array(
			'MainException'
		)
	),
	'Database' => array(
		'fn' => 'interface.db.php'
	),
	'DatabaseException' => array(
		'fn' => 'class.exception.db.php',
		'rc' => array(
			'MainException'
		)
	),
	'FileDownload' => array(
		'fn' => 'class.file_download.php',
		'rc' => array(
			'IoException',
			'Stream',
			'HttpHeaders',
			'HttpCache'
		)
	),
	'HttpCache' => array(
		'fn' => 'class.http_cache.php',
		'rc' => array(
			'MainException',
			'HttpHeaders'
		)
	),
	'HttpHeaders' => array(
		'fn' => 'class.http_headers.php',
		'rc' => array(
			'MainException'
		)
	),
	'Image' => array(
		'fn' => 'class.image.php',
		'rc' => array(
			'IoException',
			'FileDownload',
			'Stream',
			'HttpHeaders',
			'HttpCache'
		)
	),
	'Io' => array(
		'fn' => 'class.io.php',
		'rc' => array(
			'IoException'
		)
	),
	'IoException' => array(
		'fn' => 'class.exception.io.php',
		'rc' => array(
			'MainException'
		)
	),
	'Ldap' => array(
		'fn' => 'class.ldap.php',
		'rc' => array(
			'LdapException'
		)
	),
	'LdapException' => array(
		'fn' => 'class.exception.ldap.php',
		'rc' => array(
			'MainException'
		)
	),
	'MainException' => array(
		'fn' => 'class.exception.main.php'
	),
	'ModellTemplate' => array(
		'fn' => 'class.modelltemplate.php',
		'rc' => array(
			'ModellTemplateException',
			'IoException'
		)
	),
	'ModellTemplateException' => array(
		'fn' => 'class.exception.modelltemplate.php',
		'rc' => array(
			'MainException'
		)
	),
	'MySql' => array(
		'fn' => 'class.db_mysql.php',
		'rc' => array(
			'DatabaseException',
			'Database'
		)
	),
	'PostgreSql' => array(
		'fn' => 'class.db_postgresql.php',
		'rc' => array(
			'DatabaseException',
			'Database'
		)
	),
	'SoapMessaging' => array(
		'fn' => 'class.soap_messaging.php',
		'rc' => array(
			'SoapMessagingException',
			'Stream',
			'HttpHeaders'
		)
	),
	'SoapMessagingException' => array(
		'fn' => 'class.exception.soap_messaging.php',
		'rc' => array(
			'MainException'
		)
	),
	'SmtpException' => array(
		'fn' => 'class.exception.smtp.php',
		'rc' => array(
			'MainException'
		)
	),
	'Smtp' => array(
		'fn' => 'class.smtp.php',
		'rc' => array(
			'SmtpException'
		)
	),
	'Stream' => array(
		'fn' => 'class.io.stream.php',
		'rc' => array(
			'IoException',
			'Io'
		)
	)
);

/**
 * List of ALL available Functions
 * The key of the array MUST be the function scope name
 * Subarray:
 * fn(string) (required) = filename of the class
 *
 * rc(array) = required classes (use class name), the current class requires
 * that these other classes be available, ALL classes (including execptions etc..
 * SHOULD be included. The order does NOT matter.
 *
 * @var array
 * @since version 1.0.0
 */
$GLOBALS['iFunctions'] = array(
	'General' => array(
		'fn' => 'function.general.php'
	),
	'Text' => array(
		'fn' => 'function.text.php'
	),
	'Errors' => array(
		'fn' => 'function.errors.php'
	),
	'Conversion' => array(
		'fn' => 'function.convert.php'
	)
);

/**
 * Dynamicly includes the requested file AND it's dependencies
 * This function IS recursive
 * 
 * @param string $type
 * @param string $name
 * @param string $path
 * @since version 1.0.0
 */
function dynamicRequire($type, $name, $path)
{
	if ($type == 'classes')
	{
		$vd = $GLOBALS['iClasses'];
	}
	elseif ($type == 'functions')
	{
		$vd = $GLOBALS['iFunctions'];
	}
	else
	{
		trigger_error('Not a valid type for dynamicRequire: '.$type, E_USER_ERROR);
	}

	if (isset($vd[$name]))
	{
		if (!empty($vd[$name]['rc']) &&
			is_array($vd[$name]['rc']))
		{
			foreach ($vd[$name]['rc'] as $rc)
			{
				dynamicRequire($type, $rc, $path);
			}
		}

		/**
		 * Inlucde the file
		 * MUST use require_once since some files MAY be included multiple times
		 */
		require_once($path.$vd[$name]['fn']);
	}
}

// if classes is NOT set then include ALL classes
if (!isset($GLOBALS['_includeFiles_']['classes']))
{
	foreach ($GLOBALS['iClasses'] as $key => $value)
	{
		$GLOBALS['_includeFiles_']['classes'][] = $key;
	}
}

// if functions is NOT set then include ALL functions
if (!isset($GLOBALS['_includeFiles_']['functions']))
{
	foreach ($GLOBALS['iFunctions'] as $key => $value)
	{
		$GLOBALS['_includeFiles_']['functions'][] = $key;
	}
}

// loop through the valid files and include the ones requested
if (is_array($GLOBALS['_includeFiles_']['classes']))
{
	foreach ($GLOBALS['_includeFiles_']['classes'] as $name)
	{
		dynamicRequire('classes', $name, 'classes/');
	}
}

// loop through the valid files and include the ones requested
if (is_array($GLOBALS['_includeFiles_']['functions']))
{
	foreach ($GLOBALS['_includeFiles_']['functions'] as $name)
	{
		dynamicRequire('functions', $name, 'functions/');
	}
}

?>