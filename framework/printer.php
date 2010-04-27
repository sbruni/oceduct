<?php
/**
 * Framework Printer page
 *
 * Displays a printer friendly version of a page
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// printer section
$GLOBALS['s'] = 'Printer';
$GLOBALS['st'] = 'printer';

$return = '';
if (empty($GLOBALS['output']))
{
	$GLOBALS['output'] = '';
}

try
{
	// The class is cloned so that we can recuse as far as we want,
	// into as many templates as we want
	$printTemplate = clone $GLOBALS['php'];

	// get the page's php template
	// run the PHP templates BEFORE any output
	$phpReturned = $printTemplate->getEvaluatedData(
		TP.$GLOBALS['eFirst']
	);

	// mandatory header template
	// it can be used to include any amount of stuff
	// to pass in a value use the above sections template OR the $GLOBALS variable
	$header = $printTemplate->getEvaluatedData(
		TP.'header',
		$phpReturned
	);

	// mandatory footer template
	// it can be used to include any amount of stuff
	// to pass in a value use the above sections template OR the $GLOBALS variable
	$footer = $printTemplate->getEvaluatedData(
		TP.'footer',
		$phpReturned
	);

	if ($GLOBALS['output'] == 'string')
	{
		$return .= $header['header'];

		// get the page's html template
		$return .= $GLOBALS['html']->getEvaluatedData(
			TP.$GLOBALS['eFirst'],
			$phpReturned
		);

		$return .= $footer['footer'];

		return $return;
	}
	else
	{
		print $header['header'];
		flush();

		// get the page's html template
		print $GLOBALS['html']->getEvaluatedData(
			TP.$GLOBALS['eFirst'],
			$phpReturned
		);
		flush();

		print $footer['footer'];
		flush();
	}
}
catch (ModellTemplateException $e)
{
	// template doesn't exist, redirect to home page
	// no need to error out to the user
	// this basicly means the url is invalid
	if ($e->getCode() == 500)
	{
		siteRedirect('', 307);
	}
}
catch (Exception $e)
{
	// this caches ALL excetions thrown from within a evaluated template
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

// exit so nothing else can run
exit();

?>
