<?php
/**
 * Framework Command Execution file
 *
 * Executes all commands i.e switched between types runs the templates etc...
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// start types
// most type code should be in a seperate file
// all types are covered in this way except print which is different

// view an image
if (in_array($GLOBALS['t'], $GLOBALS['listTypes']['image']))
{
	require_once('image.php');
}

// downloads
// we have to do all download stuff BEFORE any output
if (in_array($GLOBALS['t'], $GLOBALS['listTypes']['download']))
{
	require_once('download.php');
}

// printer
if (in_array($GLOBALS['t'], $GLOBALS['listTypes']['print']))
{
	require_once('printer.php');
}

// css
if (in_array($GLOBALS['t'], $GLOBALS['listTypes']['css']))
{
	require_once('css.php');
}

// runs updates from pages i.e from a submit button
if (in_array($GLOBALS['t'], $GLOBALS['listTypes']['update']))
{
	require_once('update.php');
}

try
{
	// The class is cloned so that we can recuse as far as we want,
	// into as many templates as we want
	$mainTemplate = clone $GLOBALS['php'];

	// get the page's php template
	// run the PHP templates BEFORE any output
	$phpReturned = $mainTemplate->getEvaluatedData(
		TS.$GLOBALS['p'].$GLOBALS['st']
	);

	// mandatory header template
	// it can be used to include any amount of stuff
	// to pass in a value use the above sections template OR the $GLOBALS variable
	$header = $mainTemplate->getEvaluatedData(
		TD.'header',
		$phpReturned
	);

	// mandatory footer template
	// it can be used to include any amount of stuff
	// to pass in a value use the above sections template OR the $GLOBALS variable
	$footer = $mainTemplate->getEvaluatedData(
		TD.'footer',
		$phpReturned
	);

	// cache headers are sent here
	// they have to be sent AFTER the sections PHP template but before any output.
	/*
		If the template returns $phpReturned['cacheDisable'] = true then we DON'T
		use the cache.
		The template also MAY return any of these 3 variables
		$phpReturned['cacheMaxAge']
		$phpReturned['cacheExpires']
		$phpReturned['cacheLastModified']
		@see cache.php for specifics on what each variable MUST be
	*/
	if (defined('USE_CACHING') && USE_CACHING === true &&
		(empty($phpReturned['cacheDisable']) || $phpReturned['cacheDisable'] !== true))
	{
		// check if the template returned caching info
		if (!empty($phpReturned['cacheMaxAge']))
		{
			$maxAge = $phpReturned['cacheMaxAge'];
		}
		if (!empty($phpReturned['cacheExpires']))
		{
			$expires = $phpReturned['cacheExpires'];
		}
		if (!empty($phpReturned['cacheLastModified']))
		{
			$lastModified = $phpReturned['cacheLastModified'];
		}
		require_once('cache.php');
	}

	print $header['header'];
	flush();

	// if the HTML template for the section is missing AND $phpReturned['page']
	// (isn't empty) we run a fake template on it, this allows us not to have
	// to have a template with {$page} in it everywhere
	if ($GLOBALS['html']->exists(TS.$GLOBALS['p'].$GLOBALS['st']) === false &&
		!empty($phpReturned['page']))
	{
		// print a fake template
		print $GLOBALS['html']->doEvaluate(
			'{$page}',
			$phpReturned
		);
		flush();
	}
	else
	{
		// get the page's html template
		print $GLOBALS['html']->getEvaluatedData(
			TS.$GLOBALS['p'].$GLOBALS['st'],
			$phpReturned
		);
		flush();
	}

	print $footer['footer'];
	flush();
}
catch (Exception $e)
{
	// this caches ALL excetions thrown from within a evaluated template
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

?>
