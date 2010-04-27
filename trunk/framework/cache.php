<?php
/**
 * Framework cache file
 *
 * Adds caching to any pages
 *
 * Optional variables that can be given:
 * $maxAge seconds until expiry
 * $expires same as above (mainly used for HTTP 1.0) MUST be in the format
 * gmdate('D, j M Y H:i:s')
 * $lastModified the date the page was last modified, MUST be in the format
 * gmdate('D, j M Y H:i:s')
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// user MAY send the max age
// otherwise the default max age is used (default for the framework is 24 hours)
if (empty($maxAge) && defined('CACHING_MAX_AGE'))
{
	#$maxAge = CACHING_MAX_AGE;
	// temp use 1 hour
	$maxAge = 3600;
}

// don't set unless one is given by calling page
if (empty($expires))
{
	// don't set expires (second argument) as it's automaticly set to
	// time() + $maxAge
	$expires = '';
}

// if none is given don't set one
if (empty($lastModified))
{
	$lastModified = '';
}

$cache = new HttpCache();
$cache->set($maxAge, $expires, $lastModified, true);

?>
