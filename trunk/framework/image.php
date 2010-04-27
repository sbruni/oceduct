<?php
/**
 * Framework Image display
 *
 * Displays an image from either a directory
 *
 * Default Image directory is _DIR_DOWNLOAD_.'images/'
 *
 * To display a file from with in a directory add that directory
 * to the http string like normal
 *
 * i.e
 * http://mysite.com/image/dir1/dir2/dir3/file.jpg (will display a normal file)
 *
 * Giving just a directory is invalid
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

$contentType = '';
$defaultContentType = 'image/jpeg';

if (empty($GLOBALS['id']))
{
	// if there isn't an ID send them to the home page
	siteRedirect();
}

try
{
	// expire in one week
	$expires = gmdate('D, d M Y H:i:s', (time() + HttpHeaders::S_WEEK));

	$dirs = '';
	if (!empty($GLOBALS['e']))
	{
		$dirs = implode('/', $GLOBALS['e']).'/';
	}

	// any directories in the filename are removed
	$file = _DIR_DOWNLOAD_.'images/'.$dirs.IO::validateFilename($GLOBALS['id'], true);

	$contentType = getMimeType(getFileExtension(basename($file)));

	if (empty($contentType))
	{
		$contentType = $defaultContentType;
	}

	$img = new Image();
	$img->displayFile($file, basename($file), $expires, '', $contentType);
}
catch (IoException $e)
{
	if ($e->getCode() == IoException::FILE_NOT_EXIST)
	{
		// send 404 file not found
		$header = new HttpHeaders();
		$header->fileNotFound();
	}
}
catch (Exception $e)
{
	// this caches ALL excetions thrown from within a evaluated template
	// NOTE: you need to call __toString directy when passing it on to a function
	trigger_error($e->__toString(), E_USER_ERROR);
}

// NOTE: the image class automaticly kills the script, but this is here just
// in case it doesn't
exit();

?>
