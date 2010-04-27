<?php
/**
 * Framework Update script
 * Performs any update requested from a form or url (i.e GET or POST)
 *
 * (method = GET or POST)
 *
 * ONLY one input method per request allowed
 * this is for security reasons, this blocks the user from sending GET requests
 * from a POST form
 * POST has preference over GET
 *
 * Requirements:
 * -- method can be either GET or POST
 * -- href/action MUST be "{@RELATIVE_PATH}update/{templatename}/"
 * -- a template name MUST be given to the action, this name does NOT have to
 *    be unique, the referral and the templatename are used to retirve the
 *    template. the template name MUST be unique within the same referral url
 *    multiple templates are allowed per referral
 * -- MUST contain a (hidden input box/or another variable) called "referral"
 * -- "referral" MUST contain $_SERVER['REQUEST_URI']
 *
 * <code>
 * <form action="{@RELATIVE_PATH}update/{templatename}/" method="POST">
 *     <input type="hidden" name="referral" value="{$_SERVER['REQUEST_URI']}" />
 *     <input type="submit" name="update" value="(anything)" />
 * </form>
 * </code>
 *
 * {@RELATIVE_PATH}update/{templatename}/?referral=SITE_ROOT_PATH/offices/homerev/
 *
 * all variables MUST be url encoded (use safeEncode())
 * when printing data to a form input box etc... i.e referral make sure to
 * safeEncode() it first
 *
 * Any files that are uploaded are automaticly passed on to the update template
 * it is recomened that the file variable be called "userFile"
 * when uploading files you MUST have enctype="multipart/form-data" in the form
 * tag
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

// default cloning for most templates
$phpTemplate = clone $GLOBALS['php'];

// strip u and s from $_GET
// these SHOULD always be therem they are part of the mod_rewrite's regex output
if (isset($_GET['s']))
{
	unset($_GET['s']);
}
if (isset($_GET['u']))
{
	unset($_GET['u']);
}

// find which method is to be used
if (!empty($_POST))
{
	$type = '_POST';
}
elseif (!empty($_GET))
{
	$type = '_GET';
}
else
{
	// a method MUST be sent for this page to run
	siteRedirect('');
}

// we MUST have a referral
// NOTE: variable variables are used below watch out for them
// all variable variables SHOULD be surounded with { } after the first $
// this is proper codeing practice
if (empty(${$type}['referral']))
{
	trigger_error('referral is empty: $'.$type.' == '.print_r(${$type}, true),
		E_USER_ERROR);
}

// clean each item of the array
$data = array();
foreach (${$type} as $key => $val)
{
	// don't decode $val, the calling template can do that if needed
	$data[$key] = $val;
}

// extract the section and parent of the calling script from the referral url
$sectionData = getSectionsFromUrl($data['referral']);

if (!empty($data['global']) && $data['global'] == 'true' && !empty($GLOBALS['eFirst']))
{
	$template = $GLOBALS['eFirst'];
}
else
{
	$template = $sectionData['p'].$sectionData['st'].'_'
		.$GLOBALS['eFirst'];
}

// pretend we are in this location
// this sets the trail etc... correctly
$GLOBALS['p'] = $sectionData['p'];
$GLOBALS['purl'] = $sectionData['purl'];
$GLOBALS['s'] = $sectionData['s'];
$GLOBALS['st'] = $sectionData['st'];
$GLOBALS['e'] = $sectionData['e'];

try
{
	/**
	 * run the template
	 * use the referral and the templatename ($_GLOBALS['eFirst'])
	 * to create the template name
	 * 	 * scripts should NOT call $_POST directly
	 * - sectionData is a return from getSectionsFromUrl($data['referral'])
	 * - template is sent in for use in retriving a confirm page
	 * and allowing a redirect
	 * - $_FILES is passed in and if a file was uploaded the template MUST
	 * take care of it
	 */
	$phpReturned = $phpTemplate->getEvaluatedData(
		TU.$template, array(
			'data' => $data,
			'sectionData' => $sectionData,
			'template' =>  $template,
			'files' => $_FILES
		)
	);

	// if a confirm page is needed it should be specified in the above template
	// and returned as a item confirm (i.e $phpReturned['confirm'])
	$confirm = $phpReturned['confirm'];

	// if a redirection is needed it should be added to the above template
	// the redirection can be php or html, if a confirm page is given
	// it is recommened to use a html redirect (i.e meta tag)
	// use the siteRedirect() function for easy redirection
}
catch (Exception $e)
{
	// this caches ALL excetions thrown from within a evaluated template
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

if (empty($confirm))
{
	// if no confermation well they should have redirected in the previous script
	// show what the $_POST and $_GET were
	$errors = '$_GET == '.print_r($_GET, true)."\n";
	$errors .= '$_POST == '.print_r($_POST, true)."\n";
	trigger_error('confirm is empty: '.$errors
		, E_USER_ERROR);
}

// mandatory header template
// it can be used to include any amount of stuff
// to pass in a value use the above sections template OR the $GLOBALS variable
$header = $phpTemplate->getEvaluatedData(
	TD.'header',
	$phpReturned
);

// mandatory footer template
// it can be used to include any amount of stuff
// to pass in a value use the above sections template OR the $GLOBALS variable
$footer = $phpTemplate->getEvaluatedData(
	TD.'footer',
	$phpReturned
);

print $header['header'];
print $confirm;
print $footer['footer'];

// exit so nothing else can run
exit();

?>
