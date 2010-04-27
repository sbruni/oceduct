<?php
/**
 * Framework Dynamic Style sheets
 *
 * Each stylesheet is a seperate template
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 * @todo add in the correct if-modified headers, will speed things up a ton
 * and will be a valid css file then
 */

// ini cache and http headers
$headers = new HttpHeaders();
$cache = new HttpCache();

// send correct css header
// the correct headers MUST be sent or a browser will not reconize that it as
// a CSS file
$headers->set('Content-Type', 'text/css');

try
{
	// there must be something here as it's the name of the css file to use
	if (empty($GLOBALS['eFirst']))
	{
		// go to home page if no css given
		redirect(RELATIVE_PATH);
	}

	// cache the css file, get the last modified time from the template class
	// no expiry date given so it'll default to 1 week
	$cache->set(0, '', gmdate('D, d M Y H:i:s',
		$GLOBALS['css']->lastModifiedDate));

	// get the template and print it out
	// all checks to make sure theme is a valid template name
	// are done within the class
	print $GLOBALS['css']->getEvaluatedData($GLOBALS['eFirst']);
}
catch (Exception $e)
{
	$cache->setNone();
	// this caches ALL excetions thrown from within a evaluated template
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

// nothing to show after displaying the CSS
exit();

?>
