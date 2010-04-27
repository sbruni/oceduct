<?php
/**
 * File Download Class
 *
 * Download files dynamicly from a script
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package FileDownload
 * @access private
 * @todo add speed limiting
 */
class FileDownload
{
	/**
	 * Starts a download for the client
	 * Pass a full filename (including the path)
	 *
	 * Note you can NOT force a user to accept the download, this will simply
	 * bring up the download dialog for the user
	 *
	 * For some files you should use application/octet-stream (default) as the
	 * content type (i.e images) this forces them to be downloaded
	 *
	 * The script always exits after sending the file, set $noExit to true to make
	 * it not exit when complete.
	 *
	 * Content Type default application/octet-stream
	 * 
	 * @param string $file
	 * @param string $contentType [optional]
	 * @param bool $noExit [optional]
	 * @since version 1.0.0
	 */
	public function file($fileloc, $contentType = '', $noExit = false)
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

		// @todo add in speed control
		//$sleep = 0;

		// check if the file exists
		if (!file_exists($fileloc))
		{
			throw new IoException($fileloc,
				IoException::FILE_NOT_EXIST);
		}

		// get just the filename
		$filename = basename($fileloc);

		if (!is_readable($fileloc))
		{
			throw new IoException($fileloc,
				IoException::FILE_READ_DENIED);
		}

		// send the download headers
		// send the headers AFTER we open the file, this way if the file errors
		// no download headers are sent and we can see the error
		$this->sendFileHeaders($filename, filesize($fileloc), $contentType);

		readfile($fileloc);

		// always exit to prevent any more data from being sent
		if ($noExit !== true)
		{
			exit();
		}
	}

	/**
	 * Send the headers that create the download
	 *
	 * @param string $filename
	 * @param string $filesize
	 * @param string $contentType [optional]
	 * @since version 1.0.0
	 */
	public function sendFileHeaders($filename, $filesize, $contentType = '')
	{
		if (empty($contentType))
		{
			// default to application/octet-stream
			$contentType = 'application/octet-stream';
		}
		// clear some headers that aren't needed with an image
		$this->_headers->clear(
			array(
				'X-Powered-By',
				'Set-Cookie'
			)
		);

		// don't cache it
		$this->_cache->setNone();

		// you should ALWAYS give a filesize
		// otherwise the browser doesn't give a progress bar
		if (!empty($filesize))
		{
			$this->_headers->set('Content-Length', $filesize);
		}

		// if you want to FORCE a download (i.e images) send application/octet-stream
		// otherwise you should send the proper content type
		$this->_headers->set('Content-Type', $contentType);

		$attachment = 'attachment;';
		// IE 5.5 bug
		// can't have attachment in header (or it has to be atachment (note
		// spelling)) this bug is ALSO present in IE 4.01 with no workaround
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.5'))
		{
			$attachment = 'atachment';
		}

		// this will forces IE browsers to display the proper encoded chars
		// otherbrowses do not like this and want straight up utf-8 encoded filenames
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
		{
			$filename = rawurlencode($filename);
		}

		// send content disposition
		$this->_headers->set('Content-Disposition', $attachment.
			' filename="'.$filename.'"');
	}

	/**
	 * Starts a download for the client
	 * Pass a stream
	 *
	 * Note you can NOT force a user to accept the download, this will simply
	 * bring up the download dialog for the user
	 *
	 * If $streamEncoding is empty a default reading of the file will happen
	 *
	 * $filesize CAN be 0 but what will happen is that there won't be
	 * a progress bar, and the user won't know how long till the file is done
	 *
	 * The script always exits after sending the file, set $noExit to true to make
	 * it not exit when complete.
	 *
	 * @param string $filename
	 * @param integer $filesize
	 * @param string $contentType [optional]
	 * @param string $streamEncoding [optional]
	 * @param bool $noExit [optional]
	 * @since version 1.0.0
	 */
	public function stream($filename, $filesize, $contentType = '',
		$streamEncoding = '', $noExit = false)
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

		if (!is_numeric($filesize) && empty($streamEncoding))
		{
			throw new IoException('Filesize is not a number: '.$filesize.
				' Type: '.gettype($filesize), MainException::INVALID_PARAM);
		}

		// send the download headers
		$this->sendFileHeaders($filename, $filesize, $contentType);

		// output is printed DIRECTLY, no data is returned
		$this->_stream->setOutput('direct');

		if (empty($streamEncoding) && !empty($filesize))
		{
			// no encoding given, filesize IS given read by length
			$this->_stream->readLength($filesize);
		}
		elseif (!empty($streamEncoding))
		{
			// an encoding IS given use that
			$this->_stream->read($streamEncoding);
		}
		else
		{
			// just use the default which may timeout
			$this->_stream->read('');
		}

		// always exit to prevent any more data from being sent
		if ($noExit !== true)
		{
			exit();
		}
	}

	/**
	 * Starts a download for the client
	 * Pass a string
	 *
	 * This method uses a string instead of a file, but for the most part
	 * everything else applies
	 *
	 * The script always exits after sending the file, set $noExit to true to make
	 * it not exit when complete.
	 *
	 * @param string $data
	 * @param string $filename
	 * @param string $contentType [optional]
	 * @param bool $noExit [optional]
	 * @since version 1.0.0
	 */
	public function string($data, $filename, $contentType = '', $noExit = false)
	{
		// send the download headers
		$this->sendFileHeaders($filename, strlen($data), $contentType);

		// write out the string AFTER the headers are sent
		print $data."\n";

		// always exit to prevent any more data from being sent
		if ($noExit !== true)
		{
			exit();
		}
	}

	/**
	 * Class constructor
	 * @param object $stream [optional]
	 * @param object $headers [optional]
	 * @since version 1.0.0
	 */
	public function __construct(&$stream = null, &$headers = null)
	{
		try
		{
			// make sure it's an object and of the correct class
			if (is_object($stream) && get_class($stream) == 'Stream')
			{
				$this->_stream = $stream;
			}
			else
			{
				// create a new instance of the object
				$this->_stream = new Stream(false);
			}

			// make sure it's an object and of the correct class
			if (is_object($headers) && get_class($headers) == 'HttpHeaders')
			{
				$this->_headers = $headers;
			}
			else
			{
				// create a new instance of the object
				$this->_headers = new HttpHeaders();
			}

			$this->_cache = new HttpCache();
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	// protected

	/**
	 * HTTP cache object
	 * @var object
	 * @since version 1.0.0
	 */
	protected $_cache;

	/**
	 * headers class
	 * @var object
	 * @since version 1.0.0
	 */
	protected $_headers;

	/**
	 * stream class
	 * @var object
	 * @since version 1.0.0
	 */
	protected $_stream;
}

?>
