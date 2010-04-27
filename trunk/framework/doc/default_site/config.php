<?php
/**
 * Oceduct default config file
 */

/**
 * To disable the site enable these constants
 * DISABLE_TEMPLATE_TIME_LEFT
 * DISABLE_TEMPLATE_NO_TIME
 * By default these 2 constants are set to
 * oceduct/framework/templates/
 * if you want a custom version of them replace them here
 * each file should be a PHP file thus it CAN contain PHP code
 * NOTE: these files are simply run with require_once()
 */

#define('DISABLE_SITE', true);
#define('DISABLE_TIME_START',  mktime(3, 22, 0, 04, 23, 2010));
#define('DISABLE_TIME_END',  mktime(3, 30, 0, 04, 23, 2010));
// bypass ips seperate by commas
#define('DISABLE_BYPASS_IPS', '172.27.1.1,127.0.0.1');
#define('DISABLE_TEMPLATE_TIME_LEFT', 'disabled_display_time_left.tpl');
#define('DISABLE_TEMPLATE_NO_TIME', 'disabled_dont_display_time.tpl');

/**
 * Default site language code, English
 * Use 2 char language codes
 * Defaults to: en
 */
#define('SITE_LANGUAGE_CODE_DEFAULT', 'en');

/**
 * i.e http://localhost/mydir/ then this would be /mydir/
 * must start and end with a backslash or just a single backslash
 * Defaults to: /
 */
#define('SITE_ROOT_PATH', '/');

/**
 * The depth of the site
 * 
 * If your site path is within another one, you can still get the top level
 * by giving how many levels deep it goes. i.e
 * if your site is http://mysite.com/something/mypage.php then you'd give 1
 * or http://mysite.com/some/thing/mypage.php would be 2
 * 
 * This allows mulitple sites to exisit on the same domain and allows easy referencing
 * from within the templates by using the constant RELATIVE_PATH (which is ../ or ../../ etc...)
 * So you you put something like:
 * print '<a href="'.RELATIVE_PATH.'mypath/myvar/etc/">click me</a>';
 * and you can be certain that it'll be a valid link
 * 
 * Defaults to: 0
 */
#define('RELATIVE_PATH_DEPTH', 0);

/**
 * Append the url parsing values to the frameworks defaults
 * var-name:items,seperated,by,commas:max-amount-of-items-for-this-type/
 * var-name:items,seperated,by,commas:max-amount-of-items-for-this-type
 *
 * (/ is the seperator, between types)
 *
 * Only set if you want to append/overwite urlitems
 * @see oceduct/framework/constants.php FRAMEWORK_URL_PARSING
 */
#define('SITE_URL_PARSING', '');

/**
 * Append the (above) custom url parsing values to the frameworks defaults
 * (If the above is not set, this has no affect)
 * Defaults to: true
 */
#define('SITE_URL_PARSING_APPEND', true);

/**
 * Caching enables/disables
 * Allow all pages on the site to be cached on the clients computer for x
 * amount of time. @see CACHING_MAX_AGE in oceduct/framework/cache.php
 *
 * true/false
 *
 * Defaults to: false
 */
#define('USE_CACHING', true);

/**-------------------------------------
 * Authentication options
 *-------------------------------------*/

/**
 * Type of authentication to use
 * Options are:
 * - none
 * - ntlm
 * - browser
 * - openid
 * @var string
 * @since version 1.2.0
 */
###########################################################define('AUTH_METHOD', 'none');

/**-------------------------------------
 * Path Locations
 *-------------------------------------*/

/**
 * Relitive CSS files directory
 */
#define('_PATH_CSS_', '/css/');

/**
 * Relitive Graphics directory
 */
#define('_PATH_GRAPHICS_', '/graphics/');

/**-------------------------------------
 * Directory Locations
 *-------------------------------------*/

/**
 * Directory of Oceduct
 * NOTE: this CAN BE included within the index.php if desired
 * Give it an ending /
 */
define('_OCEDUCT_', '/path/to/oceduct/');

/**
 * Files download directory
 */
define('_DIR_DOWNLOAD_', '/path/to/my/website/downloads/');

/**
 * File upload directory
 * this directory should have write access allowed by the user that is running apache/php
 */
define('_DIR_UPlOAD_', '/path/to/my/website/uploads/');

/**
 * The root directory of the site that is currently running the framework
 * This is the location of index.php
 */
define('_DIR_ROOT_', '/path/to/my/website/');

/**
 * Template directory, location of the top level template directory
 */
define('_DIR_TEMPLATE_', _DIR_ROOT_.'templates/');

/**
 * Security directory
 * Contains configs (that have passwords etc...) that should not be globally available
 * It's recommened that this directory be outside the document root, thus not
 * accessable via http
 */
define('_DIR_SECURITY_', '/path/to/security/');


/**-------------------------------------
 * Databases
 *
 * Oceduct requires a database for storing it's section/data
 * this can be on a postgresql (8.2+) or mysql (4x+) system. (Defaults to: postgresql)
 *
 * - What database system you using? postgresql/mysql
 * - Enable 2 global connections to the default database? (useful for looping through
 * results and then requering/inserting based on the previous data, disabled by default)
 *
 * NOTE: None of the following constants should be defined in this file, they should be 
 * in a seperate file that is proteced from outside sources.
 *
 * DATABASE_PGSQL_HOST_DEFAULT string
 * DATABASE_PGSQL_USERNAME_DEFAULT string
 * DATABASE_PGSQL_PASSWORD_DEFAULT string
 * DATABASE_PGSQL_PORT_DEFAULT int
 *
 * DATABASE_MYSQL_HOST_DEFAULT string
 * DATABASE_MYSQL_USERNAME_DEFAULT string
 * DATABASE_MYSQL_PASSWORD_DEFAULT string
 * DATABASE_MYSQL_PORT_DEFAULT int
 *
 *-------------------------------------*/

/**
 * Currently only 2 values are valid
 * - postgresql
 * - mysql
 */
#define('DATABASE_SYSTEM', 'postgresql');

/**
 * 2 global database connections
 */
#define('DATABASE_2_GLOBAL_CONNECTIONS', false);

/**
 * Name of the default Database
 */
define('DATABASE_DEFAULT', 'mysite');

/**-------------------------------------
 * Misc options
 *-------------------------------------*/

/**
 * Enables debug mode
 * to be used for a debugable version of the site
 * removing this line will log and email ALL errors instead of displaying them
 * (except parse errors they'll be logged normally by PHP)
 *
 * NOT recommened to enable on a production site
 *
 * Remember if this is disabled ERRORS DO NOT SHOW on the page AT ALL
 *
 * Defaults to false
 */
define('DEBUG_ENABLED', true);

?>
