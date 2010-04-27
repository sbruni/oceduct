<?php
/**
 * Framework Downloads
 *
 * This file will DOWNLOAD the file it will NOT create the filename for that file
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

if (empty($GLOBALS['id']))
{
	// if there isn't an ID send them to the home page
	siteRedirect();
}

// if there isn't a _DIR_DOWNLOAD_ specified, it's quite dangers to
// allow access to the file system, so don't allow any downloads
if (!defined('_DIR_DOWNLOAD_'))
{
	trigger_error('Downloads, from this location, are disabled.',
		E_USER_ERROR);
}

// get a listing of all the valid directories
$dirs = '';
if (!empty($GLOBALS['e']))
{
	$dirs = implode('/', $GLOBALS['e']).'/';
}

// create the filename from the ID ONLY
$filename = Io::validateFilename($GLOBALS['id']);

try
{
	// download the file
	$download = new FileDownload();
	// always limit downloads to be gotten from the downloads directory
	// this prevents people from access files from outside the system

	// no need to send a contentType, defaults to application/octet-stream
	// this forces a download
	$download->file(_DIR_DOWNLOAD_.$dirs.$filename);
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
	trigger_error($e->__toString(), E_USER_ERROR);
}

// exit the script
exit();

?>
