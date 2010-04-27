<?php
/**
 * Oceduct Framework Redirect file
 *
 * Redirects based on a given listing of urls and there replacements.
 *
 * NOTE: This can NOT be done with apache rewrite because of how the framework
 * uses rewrite already. If you specify an additional rewrite, the framework
 * still uses $_SERVER['REQUEST_URI'] and this doesn't change with mod_rewrite
 *
 * NOTE: The FIRST match found is used as the redirect none of the rest are
 * even looked at
 *
 * File format (redirect.conf):
 * any line starting with # are comments and are ignored
 *
 * one match per line
 * [SEARCH][TAB][REPLACE][TAB][CODE]
 *
 * [TAB] a SINGLE tab char (\t)
 *
 * [SEARCH] a fully valid regex, used both in a preg_match and a preg_replace
 * If a part is enclosed with () they can be used in a backtrace $1 $2 $3 etc...
 *
 * [REPLACE] a fully valid preg_replace replacement, fully supports $1 $2
 *
 * [CODE] MUST be 301 or 307
 * 301 is permanent redirect
 * 307 is temporary redirect
 *
 * You CAN replace with a FULL http link i.e
 * http://www.yoursite.com/your/link/
 * or you can just give an internal path
 * path/to/locally/
 *
 * the current HTTP address is added when there is none given
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

if (defined('_DIR_ROOT_') && defined('FILE_REDIRECT') &&
	!empty($_SERVER['HTTP_HOST']) && defined('SITE_ROOT_PATH') &&
	file_exists(_DIR_ROOT_.FILE_REDIRECT))
{
	// file to process
	$filename = _DIR_ROOT_.FILE_REDIRECT;

	$file = file($filename);

	// process each line of the file
	foreach ($file as $key => $line)
	{
		// ignore lines starting with hash (#)
		// valid lines MUST start with 307 or 301
		if (preg_match('!^([^#\t]*)\t+([^\t]*)\t+(307|301)!i',
			$line, $matches))
		{
			$results = preg_match('!^'.SITE_ROOT_PATH.trim($matches[1]).'!i',
				$_SERVER['REQUEST_URI']);

			if ($results === false)
			{
				print '<pre>';
				trigger_error('Invalid regex on line: '.$key.', in file '.$filename."\n".
					"regex was: \n".$line."\n", E_USER_ERROR);
			}

			if ($results !== 0)
			{
				$go = '';

				// check for http(s) at the front of the replace string
				// if there is NOT one we add the current site
				if (!preg_match('!http(s)?://!i', trim($matches[2])))
				{
					// if we are currently using SSL add it on
					$http = 'http://';
					if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
						(!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443))
					{
						$http = 'https://';
					}
					$go = $http.$_SERVER['HTTP_HOST'].SITE_ROOT_PATH;
				}

				$go .= preg_replace('!'.SITE_ROOT_PATH.trim($matches[1])
					.'!i', trim($matches[2]), $_SERVER['REQUEST_URI']);

				// redirect using the header code 301 or 307
				redirect($go, 0, 'php', $matches[3]);

				// NOTE: once redirect runs it calls exit()
				// nothing below here is processed
			}
		}
	}
}

?>
