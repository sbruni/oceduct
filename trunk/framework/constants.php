<?php
/**
 * Framework Constants
 *
 * Default values across the Framework
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// MUST be at the top of this script as other constants use it

if (!defined('SITE_ROOT_PATH'))
{
	/**
	 * i.e http://localhost/mydir/ then this would be /mydir/
	 * must start and end with a backslash or just a single backslash
	 * @var string
	 * @since version 1.0.0
	 */
	define('SITE_ROOT_PATH', '/');
}

// Don't have to be at the top

/**-------------------------------------
 * Databases
 *
 * Oceduct requires a database for storing it's section/data
 * this can be on a postgresql (8.2+) or mysql (4x+) system. (Defaults to: postgresql)
 *
 * - What database system you using? postgresql/mysql
 * - Enable 2 global connections to the default database? (useful for looping through
 * results and then requering/inserting based on the previous data, disabled by default)
 *-------------------------------------*/

 /**
 * Currently only 2 values are valid
 * - postgresql
 * - mysql
 * 
 * @var string
 * @since version 1.0.0
 */
if (!defined('DATABASE_SYSTEM'))
{
	define('DATABASE_SYSTEM', 'postgresql');
}

/**
 * 2 global database connections
 */
if (!defined('DATABASE_2_GLOBAL_CONNECTIONS'))
{
	define('DATABASE_2_GLOBAL_CONNECTIONS', false);
}

/**
 * Caching time limit, defaults to 24 hours for most pages
 * The calling page can disable caching altogether or set a different time limit
 *
 * @var int
 * @since version 1.0.0
 */
define('CACHING_MAX_AGE', 86400);

/**
 * Default content type UTF-8
 * 
 * @var string
 * @since version 1.0.0
 */
define('CONTENT_TYPE_UTF_8', 'text/html; charset=utf-8');

if (!defined('_DIR_MISC_') && defined('SITE_ROOT_PATH'))
{
	/**
	 * Misc directory
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	define('_DIR_MISC_', SITE_ROOT_PATH.'misc/');
}

if (!defined('_DIR_NTLM_') && defined('SITE_ROOT_PATH'))
{
	/**
	 * NTLM directory
	 * @var string
	 * @since version 1.0.0
	 */
	define('_DIR_NTLM_', SITE_ROOT_PATH.'ntlm/');
}

/**
 * This is to parse the EXTRAS array
 * The EXTRAS is created at the end of the url, for example:
 *
 * http://www.mysite.com/main/lang/en/g/stuff/
 *
 * "main" is the section, sections are automaticly removed if they are
 * VALID sections
 * lang and g would be what you would put in below format is
 *
 * var-name is the GLOBAL variable that this WILL be put into
 * max-amount-of-items-for-this-type ONLY this many items will be retrived
 * first come first serve, any others will be ignored
 *
 * var-name:items,seperated,by,commas:max-amount-of-items-for-this-type/
 * var-name:items,seperated,by,commas:max-amount-of-items-for-this-type
 *
 * (/ is the seperator, between types)
 *
 * The min and maximum number of entries after the below trigger is one
 * (subject to change)
 *
 * The default for the FRAMEWORK is:
 *
 * group:group,groups,g/category:category,categories,c/language:language,lang,l:1
 *
 * @var string
 * @since version 1.0.0
 * @todo make more flexable
 */
define('FRAMEWORK_URL_PARSING',
	'group:group,groups,g/category:category,categories,c/language:language,lang,l:1/server:s:1');

/**
 * Default language
 * 
 * @var string
 * @since version 1.0.0
 */
define('FRAMEWORK_DEFAULT_LANGUAGE_CODE', 'en');

if (!defined('FILE_NTLM'))
{
	/**
	 * NTLM filename
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	define('FILE_NTLM', 'ntlm.php');
}

if (!defined('FILE_REDIRECT'))
{
	/**
	 * Redirect filename
	 *
	 * If this file exists then it'll be processed
	 *
	 * @see redirect.php
	 * @var string
	 * @since version 1.0.0
	 */
	define('FILE_REDIRECT', 'redirect.conf');
}

/**
 * http string
 * 
 * @var string
 * @since version 1.0.0
 */
define('HTTP', 'http://');

/**
 * https string
 * 
 * @var string
 * @since version 1.0.0
 */
define('HTTPS', 'https://');

/**
 * Character return and a newline
 * 
 * @var string
 * @since version 1.0.0
 */
define('CRLF', "\r\n");

/**
 * New line char
 * 
 * @var string
 * @since version 1.0.0
 */
define('LF', "\n");

/**
 * Time Constants
 * Allows access to the exact time all through the script
 * Say a script takes 1 minute to run, but you want to log it all as having
 * been run at x time, this is where these cone in handy, also you can use them
 * to see how long a script took to run.
 */

/**
 * Current Time
 * 
 * @var string
 * @since version 1.0.0
 */
define('NOW_TIME', time());

/**
 * Current Date
 * 
 * @var string
 * @since version 1.0.0
 */
define('NOW_DATE', date('Y-m-d', NOW_TIME));

/**
 * Current Date and Time
 * 
 * @var string
 * @since version 1.0.0
 */
define('NOW_DATE_TIME', date('Y-m-d H:i:s', NOW_TIME));


// RELATIVE_PATH_DEPTH is mandatory
// defaults to zero (0)
if (!defined('RELATIVE_PATH_DEPTH'))
{
	/**
	 * The depth of the site
	 *
	 * If your site path is within another one, you can still get the top level
	 * by giving how mny levels deep it goes. i.e
	 * if your site is http://mysite.com/something/index.php then you'd give 1
	 * or http://mysite.com/some/thing/index.php would be 2
	 *
	 * @var integer
	 * @since version 1.0.0
	 */
	define('RELATIVE_PATH_DEPTH', 0);
}

/**
 * The relative path to the top level of the site
 * 
 * @var string
 * @see getRelativePath()
 * @since version 1.0.0
 */
define('RELATIVE_PATH', getRelativePath(RELATIVE_PATH_DEPTH));

if (!defined('_PATH_CSS_'))
{
	/**
	 * CSS files directory
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	define('_PATH_CSS_', '/css/');
}

if (!defined('_PATH_GRAPHICS_'))
{
	/**
	 * Graphics directory
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	define('_PATH_GRAPHICS_', '/graphics/');
}

/**
 * Session Prefix
 * This is prefixed to all session variables, this makes the session name
 * unique per site. This is to prevent sessions crossing paths when you
 * run more then one site from the same url.
 * 
 * @var string
 * @since version 1.0.0
 */
define('SP', $_SERVER['HTTP_HOST'].'_');

/**
 * Template Default
 * The value that is appened to the front of a template filename when
 * referencing default templates
 * 
 * @var string
 * @since version 1.0.0
 */
define('TD', 'd_');

/**
 * Template Printer
 * The value that is appened to the front of a template filename when
 * referencing printer templates
 * 
 * @var string
 * @since version 1.0.0
 */
define('TP', 'p_');

/**
 * Template Section
 * The value that is appened to the front of a template filename when
 * referencing section templates
 * 
 * @var string
 * @since version 1.0.0
 */
define('TS', 's_');

/**
 * Template Update
 * The value that is appened to the front of a template filename when
 * referencing update templates
 * 
 * @var string
 * @since version 1.0.0
 */
define('TU', 'u_');

?>
