<?php
/**
 * Io Class
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Io
 * @see IoException
 * @access private
 */
class Io
{
	/**
	 * As of PHP 5.0.0 fread($stream, 8192) will only grab 1 packet worth of
	 * data from the buffer in a TCP/IP or UDP stream.
	 * 8192 is the suggested size to make sure you get all of one packet, any
	 * size larger than that will still have the same result.
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const BUFFER_LENGTH					= 8192;

	/**
	 * Validates the filename, makes sure there aren't any disallowed chars in it
	 *
	 * Requires preg functions
	 *
	 * @param string $filename
	 * @return bool
	 * @since version 1.0.0
	 */
	static public function validFilename($filename)
	{
		if (preg_match('!^[^*|\\\\:/"<>?]*$!', $filename) !== 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Validates the filename, AND REMOVES all invalid chars
	 *
	 * Requires preg functions
	 *
	 * @param string $filename
	 * @return string
	 * @since version 1.0.0
	 */
	static public function validateFilename($filename)
	{
		return preg_replace('![*|\\\\:"/<>?]!', '', $filename);
	}

	/**
	 * Class constructor
	 *
	 * @since version 1.0
	 */
	public function __construct()
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();
		// check for IoException
		if (in_array('IoException', $declaredClasses) === false)
		{
			throw new MainException('IoException: class not found',
				MainException::INVALID_PARAM);
		}
	}

	// protected

	/**
	 * @var resource the active stream
	 * 
	 * @since version 1.0
	 */
	protected $resource;
}

?>
