<?php
/**
 * Framework Initiaization file
 *
 * All initiaization goes here
 *
 * IDs:
 * (url)/item.id.3508
 * (url)/section/item.id.3508
 * (url)/photos/pix.2004.10.1
 * (url)/download/language/es/pdf/item.id.408
 * (if a id fails, nothing is done to it, i.e no lowercaseing)
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 * @see getSectionsFromUrl()
 */

// ini any global options

// set the custom error handler
set_error_handler('errorHandler');
$GLOBALS['errorMessage'] = '';

/**
 * @var array list of valid types
 * @since version 1.0.0
 */
$GLOBALS['listTypes'] = array(
	'download' => array(
		'download', 'downloads', 'd'
	),
	'print' => array(
		'print', 'prints', 'p'
	),
	'image' => array(
		'image', 'images', 'i'
	),
	'update' => array(
		'update', 'updates', 'u'
	),
	'css' => array(
		'css'
	)
);

/**
 * @var array list of valid file types
 * @since version 1.0.0
 */
$GLOBALS['fileTypes'] = array(
	'download' => array(
	),
	'image' => array(
		'thm', 'mid', 'lrg', 'org'
	),
);

/**
 * @var string the active type
 * @see GLOBALS['listTypes']
 * @since version 1.0.0
 */
$GLOBALS['t'] = '';

/**
 * @var string an underscore seperated list of parents of this section
 * @since version 1.0.0
 */
$GLOBALS['p'] = '';

/**
 * url friendly version of $GLOBALS['p']
 * @var string
 * @since version 1.0.0
 */
$GLOBALS['purl'] = '';

/**
 * @var string the full name of the section (any char allowed)
 * @since version 1.0.0
 */
$GLOBALS['s'] = '';

/**
 * i.e only a-z 0-9 - and _
 * @var the name of the section but formatted for use when calling a template
 * @since version 1.0.0
 */
$GLOBALS['st'] = '';

/**
 * The ID
 * @var string
 * @since version 1.0.0
 */
$GLOBALS['id'] = '';

/**
 * The language URL
 * Whenever a url string is used within the framework
 * you CAN appened this string to it, whenever a langauge is changed via url
 * this will contain the new string
 * @var string
 * @since version 1.0.0
 */
$GLOBALS['languageUrl'] = '';

/**
 * The base URL, this is used within most templates.
 * The baseUrl SHOULD NOT contain the languageUrl, languageUrl should be appended
 * within the templates.
 *
 * @var string
 * @since version 1.0.0
 */
$GLOBALS['baseUrl'] = '';

/**
 * Sections options
 * 
 * @var string
 * @since version 1.0.0
 */
$GLOBALS['so'] = '';

/**
 * can contain a mixed amount of info
 * holds any data from the url that isn't considered a section
 * 
 * @var array
 * @see getExtra()
 * @since version 1.0.0
 */
$GLOBALS['e'] = array();

/**
 * eFirst is a fast way of accessing the first extra variable
 * NOTE it is NOT removed from the extra array
 * @var string
 * @since 1.0.0
 */
$GLOBALS['eFirst'] = '';

try
{
	//-------------------------------------
	// DB objects
	//-------------------------------------

	if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
	{
		// no database info provided exit framework
		if (!defined('DATABASE_DEFAULT') ||
			!defined('DATABASE_PGSQL_HOST_DEFAULT') ||
			!defined('DATABASE_PGSQL_USERNAME_DEFAULT') ||
			!defined('DATABASE_PGSQL_PORT_DEFAULT') ||
			!defined('DATABASE_PGSQL_PASSWORD_DEFAULT'))
		{
			exit('PostgreSQL database info not given exiting.');
		}

		/**
		 * Default database info
		 * Required by any classes connection to the database
		 * and by the database class
		 *
		 * If a new object is created that needs a different database
		 * change the database index and use this array
		 *
		 * @var array
		 * @since version 1.0.0
		 */
		$GLOBALS['dbInfo'] = array(
			'database' => DATABASE_DEFAULT,
			'host' => DATABASE_PGSQL_HOST_DEFAULT,
			'user' => DATABASE_PGSQL_USERNAME_DEFAULT,
			'password' => DATABASE_PGSQL_PASSWORD_DEFAULT,
			'port' => DATABASE_PGSQL_PORT_DEFAULT
		);

		/**
		 * Initialize Global DB object
		 * @var object main database object
		 * @since version 1.0.0
		 */
		$GLOBALS['db'] = new PostgreSql($GLOBALS['dbInfo']);

		if (defined('DATABASE_2_GLOBAL_CONNECTIONS') && DATABASE_2_GLOBAL_CONNECTIONS === true)
		{
			/**
			 * Same as above except used as a secandary object mainly within
			 * a record loop (of the above db object)
			 * @var object main database object
			 * @since version 1.0.0
			 */
			$GLOBALS['db2'] = new PostgreSql($GLOBALS['dbInfo']);
		}
	}
	elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
	{
		// no database info provided exit framework
		if (!defined('DATABASE_DEFAULT') ||
			!defined('DATABASE_MYSQL_HOST_DEFAULT') ||
			!defined('DATABASE_MYSQL_USERNAME_DEFAULT') ||
			!defined('DATABASE_MYSQL_PORT_DEFAULT') ||
			!defined('DATABASE_MYSQL_PASSWORD_DEFAULT'))
		{
			exit('MySQL database info not given exiting.');
		}

		/**
		 * Default database info
		 * Required by any classes connection to the database
		 * and by the database class
		 *
		 * If a new object is created that needs a different database
		 * change the database index and use this array
		 *
		 * @var array
		 * @since version 1.0.0
		 */
		$GLOBALS['dbInfo'] = array(
			'database' => DATABASE_DEFAULT,
			'host' => DATABASE_MYSQL_HOST_DEFAULT,
			'user' => DATABASE_MYSQL_USERNAME_DEFAULT,
			'password' => DATABASE_MYSQL_PASSWORD_DEFAULT,
			'port' => DATABASE_MYSQL_PORT_DEFAULT
		);

		/**
		 * Initialize Global DB object
		 * @var object main database object
		 * @since version 1.0.0
		 */
		$GLOBALS['db'] = new MySql($GLOBALS['dbInfo']);

		if (defined('DATABASE_2_GLOBAL_CONNECTIONS') && DATABASE_2_GLOBAL_CONNECTIONS === true)
		{
			/**
			 * Same as above except used as a secandary object mainly within
			 * a record loop (of the above db object)
			 * @var object main database object
			 * @since version 1.0.0
			 */
			$GLOBALS['db2'] = new MySql($GLOBALS['dbInfo']);
		}
	}

	//-------------------------------------
	// Template objects
	//-------------------------------------
	/**
	 * html template object
	 * @var object
	 * @since version 1.0.0
	 */
	$GLOBALS['html'] = new ModellTemplate('html', 'file',
		_DIR_TEMPLATE_.'html', FRAMEWORK_DEFAULT_LANGUAGE_CODE);

	/**
	 * javascript template object
	 * @var object
	 * @since version 1.0.0
	 */
	$GLOBALS['js'] = new ModellTemplate('javascript', 'file',
		_DIR_TEMPLATE_.'javascript', FRAMEWORK_DEFAULT_LANGUAGE_CODE);

	/**
	 * PHP template access
	 *
	 * Reads from PHP templates
	 *
	 * @var object
	 * @since version 1.0.0
	 */
	$GLOBALS['php'] = new ModellTemplate('php', 'file',
		_DIR_TEMPLATE_.'php', FRAMEWORK_DEFAULT_LANGUAGE_CODE);

	/**
	 * Translation template access
	 *
	 * Reads from Translation templates
	 *
	 * @var object
	 * @since version 1.0.0
	 */
	$GLOBALS['translation'] = new ModellTemplate('byline', 'file',
		_DIR_TEMPLATE_.'translation', FRAMEWORK_DEFAULT_LANGUAGE_CODE);

	//-------------------------------------
	// Authentication
	//-------------------------------------

	if (defined('AUTH_METHOD') && AUTH_METHOD != 'none')
	{
		/**
		 * Authentication
		 * @var object
		 * @since version 1.0.0
		 */
		$GLOBALS['authentication'] = new Authentication();

		switch (AUTH_METHOD)
		{
		    case 'ntlm':
				// NTLM login, primarily used offline
				$GLOBALS['authentication']->loginNtlm('redirect',
					HTTP.$_SERVER['HTTP_HOST']._DIR_NTLM_.FILE_NTLM);
				break;
			case 'openid':
				// todo add this in
				break;
			case 'browser':
			default:
				// browser based login i.e digest or basic
				$GLOBALS['authentication']->loginBrowser();
		}
	}
}
catch (Exception $e)
{
	trigger_error($e->__toString(), E_USER_ERROR);
}

//-------------------------------------
// User options
//-------------------------------------

// retrive site and user options, saves to session
// use session vars to retrive options
getOptions();

// check/set user email
// if no email is given then ignore this
if (!empty($_SESSION[SP.'userInfo']['email']))
{
	// reset the email if it's missing OR if it doesn't match what we have on record for them
	if (empty($_SESSION[SP.'userSiteOptions']['email']) ||
		$_SESSION[SP.'userSiteOptions']['email'] != $_SESSION[SP.'userInfo']['email'])
	{
		setOptions('email', array($_SESSION[SP.'userInfo']['email']));
	}
}

// use the default language code if user doesn't have a pre selected one
// run before
if (empty($_SESSION[SP.'userSiteOptions']['languageCode']))
{
	if (!empty($_SESSION[SP.'userInfo']['info']['language']))
	{
		// use the users default language from there userinfo (if set)
		$_SESSION[SP.'userSiteOptions']['languageCode'] =
			$_SESSION[SP.'siteOptions']['site_language_codes']['default'];
	}
	elseif (!empty($_SESSION[SP.'userInfo']['info']['language']))
	{
		// use the default site language
		$_SESSION[SP.'userSiteOptions']['languageCode'] =
			$_SESSION[SP.'siteOptions']['site_language_codes']['default'];
	}
	else
	{
		// use the framework default
		$_SESSION[SP.'userSiteOptions']['languageCode'] =
			FRAMEWORK_DEFAULT_LANGUAGE_CODE;
	}
}

// default pLanguageCode to use per page
// whatever is in the url WILL override the users default
if (!empty($_SESSION[SP.'userSiteOptions']['languageCode']))
{
	$GLOBALS['pLanguageCode'] = $_SESSION[SP.'userSiteOptions']['languageCode'];
}
elseif (defined('SITE_LANGUAGE_CODE_DEFAULT'))
{
	$GLOBALS['pLanguageCode'] = SITE_LANGUAGE_CODE_DEFAULT;
}
else
{
	$GLOBALS['pLanguageCode'] = FRAMEWORK_DEFAULT_LANGUAGE_CODE;
}

//-------------------------------------
// Url retirval and resetting etc...
//-------------------------------------

// get the sections etc... from the url
$data = getSectionsFromUrl($_SERVER['REQUEST_URI'], true);

// extras
$GLOBALS['e'] = $data['e'];
// parents
$GLOBALS['p'] = $data['p'];
// parents names
$GLOBALS['pn'] = $data['pn'];
// parents url
$GLOBALS['purl'] = $data['purl'];
// section name
$GLOBALS['s'] = $data['s'];
// section template
$GLOBALS['st'] = $data['st'];
// section options
// options that are NOT required by the framework
$GLOBALS['so'] = $data['so'];
// section group
$GLOBALS['sectionGroup'] = $data['sectionGroup'];
// section category
$GLOBALS['sectionCategory'] = $data['sectionCategory'];
// type
$GLOBALS['t'] = $data['t'];
// id
$GLOBALS['id'] = $data['id'];


$GLOBALS['errorMessage'] .= var_export($data, true);

//-------------------------------------
// Extra urls items parsing and assigning them
//-------------------------------------

$mixed = parseExtras();

// Framework specific

// if a language is in the url use that one ALWAYS
// since we know it's always an array get the first item
// language IS LIMITED via the framework to ONE language item per page
if (!empty($GLOBALS['pLanguage'][0]))
{
	$GLOBALS['pLanguageCode'] = $GLOBALS['pLanguage'][0];
	// set the language url that all links SHOULD use
	// NOTE this ONLY should take effect if the language is different
	// then the current user langauge
	if ($GLOBALS['pLanguageCode'] != $_SESSION[SP.'userSiteOptions']['languageCode'])
	{
		// starts with a /, but does NOT end with one
		$GLOBALS['languageUrl'] = '/language/'.$GLOBALS['pLanguageCode'];
	}
}

// set baseUrl
// default url for all links
$GLOBALS['baseUrl'] = RELATIVE_PATH.$GLOBALS['purl'].$GLOBALS['st'];

?>
