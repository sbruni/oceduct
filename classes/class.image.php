<?php
/**
 * Image download/display Class
 *
 * Displays an image inline (within the browser)
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Image
 * @access private
 */
class Image extends FileDownload
{
	/**
	 * Display an image (in browser)
	 *
	 * Reads the image from a File
	 *
	 * $file is the FULL path and filename to the image
	 *
	 * @param string $file
	 * @param string $displayFilename [optional]
	 * @param string $expires [optional]
	 * @param string $lastModified [optional]
	 * @param string $contentType [optional]
	 * @param bool $noCache [optional]
	 * @param bool $noExit [optional]
	 * @since version 1.0.0
	 */
	public function displayFile($file, $displayFilename = '', $expires = '',
		$lastModified = '', $contentType = '', $noCache = false, $noExit = false)
	{
		// set unlimited time to download
		set_time_limit(0);
		// remove sessions
		session_write_close();

		// remove any output buffering
		if (ob_get_level() > 0)
		{
			while (ob_end_clean());
		}

		// check if the file exists
		if (!file_exists($file))
		{
			throw new IoException($file,
				IoException::FILE_NOT_EXIST);
		}

		// get just the filename
		$filename = basename($file);

		if (!is_readable($file))
		{
			throw new IoException($file,
				IoException::FILE_READ_DENIED);
		}

		// length of the file
		$contentLength = filesize($file);

		// use default filename if none given
		if (!empty($displayFilename))
		{
			$filename = $displayFilename;
		}

		$this->sendImageHeaders($filename, $contentLength, $contentType,
			$expires, $lastModified, $noCache);

		readfile($file);

		// always exit to prevent any more data from being sent
		if ($noExit !== true)
		{
			exit();
		}
	}

	/**
	 * Display an image (in browser)
	 *
	 * Receives the image from a Stream
	 *
	 * $transferEncoding stream encodings chunked|until|length
	 * 
	 * @param string $displayFilename [optional]
	 * @param string $expires [optional]
	 * @param string $lastModified [optional]
	 * @param string $contentType [optional]
	 * @param integer $contentLength [optional]
	 * @param bool $noCache [optional]
	 * @param bool $noExit [optional]
	 * @param string $transferEncoding [optional]
	 * @since version 1.0.0
	 */
	public function displayStream($displayFilename = '', $expires = '',
		$lastModified = '', $contentType = '', $contentLength = 0, $noCache = false,
		$noExit = false, $transferEncoding = '')
	{
		// set unlimited time to download
		set_time_limit(0);
		// remove sessions
		session_write_close();

		// remove any output buffering
		if (ob_get_level() > 0)
		{
			while (ob_end_clean());
		}

		$this->sendImageHeaders($displayFilename, $contentLength, $contentType,
			$expires, $lastModified, $noCache);

		// read the image from the stream and output directly to the user
		try
		{
			$this->_stream->setOutput('direct');

			if (!empty($contentLength))
			{
				// defaults to length
				$this->_stream->readLength($contentLength);
			}
			elseif (!empty($transferEncoding))
			{
				$this->_stream->read($transferEncoding);
			}
			else
			{
				throw new IoException('',
					IoException::STREAM_READ_DENIED);
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		// exit the script at the end of the displaying
		// allows you to NOT exit at the end, not recommened
		if ($noExit !== true)
		{
			exit();
		}
	}

	/**
	 * Send the headers that display the image
	 *
	 * @param string $filename [optional]
	 * @param string $filesize [optional]
	 * @param string $contentType [optional]
	 * @param string $expires [optional]
	 * @param string $lastModified [optional]
	 * @param bool $noCache [optional]
	 * @since version 1.0.0
	 */
	public function sendImageHeaders($filename = '', $contentLength = 0,
		$contentType = '', $expires = '', $lastModified = '', $noCache = false)
	{
		try
		{
			// clear some headers that aren't needed with an image
			$this->_headers->clear(
				array(
					'X-Powered-By',
					'Set-Cookie'
				)
			);

			if ($noCache === true)
			{
				// no caching
				$this->_cache->setNone();
			}
			else
			{
				// cache for x amount of time
				$this->_cache->set(0, $expires, $lastModified);
			}

			// contenttype
			if (!empty($contentType))
			{
				$this->_headers->set('Content-Type', $contentType);
			}

			// send image specific headers
			// forcing the image to display in the browser
			$this->_headers->set('Content-Encoding', 'binary');

			// set the length if set
			if (!empty($contentLength))
			{
				$this->_headers->set('Content-Length', $contentLength);
			}

			// if a name is given, use it
			$cd = 'inline;';
			if (!empty($filename))
			{
				 $cd .= ' filename="'.$filename.'"';
			}
			$this->_headers->set('Content-Disposition', $cd);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
 	 * Class constructor
 	 *
	 * @param object $stream [optional]
	 * @param object $headers [optional]
	 * @since version 1.0.0
	 */
	public function __construct(&$stream = null, &$headers = null)
	{
		// run the parent constructor to ini it
		parent::__construct($stream, $headers);
	}
}

?>
