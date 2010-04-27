<?php
/**
 * IO Exception class
 *
 * IO exceptions error code range
 * 100 - 149
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @see MainException
 * @access private
 */
class IoException extends MainException
{
	// file 100 - 114
	/**
	 * file does not exist
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_NOT_EXIST				= 100;
	/**
	 * can not read from file
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_READ_DENIED				= 101;
	/**
	 * can not write to file
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_WRITE_DENIED				= 102;
	/**
	 * can not execute file
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_EXECUTE_DENIED			= 103;
	/**
	 * File already exists
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_EXIST					= 104;
	/**
	 * Error received while writing to file. Give filename
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_WRITE_ERROR				= 105;

	// dir 115 - 129
	/**
	 * directory does not exist
	 * @var integer
	 * @since version 1.0.0.0
	 */
	const DIR_NOT_EXIST					= 115;
	/**
	 * directory can not be read
	 * @var integer
	 * @since version 1.0.0
	 */
	const DIR_READ_DENIED				= 116;
	/**
	 * can not write to directory
	 * @var integer
	 * @since version 1.0.0
	 */
	const DIR_WRITE_DENIED				= 116;

	// stream 130 - 149
	/**
	 * connection to the stream failed
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_CONNECTION_FAILED		= 130;
	/**
	 * active connection lost
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_CONNECTION_LOST		= 131;
	/**
	 * stream has timed out
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_TIMED_OUT				= 132;
	/**
	 * could not set stream timeout
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_TIMEOUT				= 133;
	/**
	 * blocking mode could not be enabled/disabled
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_BLOCKING_FAILED		= 134;
	/**
	 * could not read from stream
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_READ_DENIED			= 135;
	/**
	 * could not write to stream
	 * @var integer
	 * @since version 1.0.0
	 */
	const STREAM_WRITE_DENIED			= 136;

	/**
	 * Class constructor
	 * 
	 * @param string msg [optional]
	 * @param integer code [optional]
	 * @since version 1.0.0
	 */
	public function __construct($msg = '', $code = 1000)
	{
		switch ($code)
		{
			case self::FILE_NOT_EXIST:
				$msg = 'File: '.$msg.' does not exist';
				break;
			case self::FILE_READ_DENIED:
				$msg = 'Could not read file: '.$msg;
				break;
			case self::FILE_WRITE_DENIED:
				$msg = 'Could not write to file: '.$msg;
				break;
			case self::FILE_EXECUTE_DENIED:
				$msg = 'Could not execute file: '.$msg;
				break;
			case self::FILE_EXIST:
				$msg = 'File: '.$msg.' already exists';
				break;
			case self::FILE_WRITE_ERROR:
				$msg = 'Error while writing to file: '.$msg;
				break;
			case self::DIR_NOT_EXIST:
				$msg = 'Directory '.$msg.' does not exist';
				break;
			case self::DIR_READ_DENIED:
				$msg = 'Could not read directory: '.$msg;
				break;
			case self::DIR_WRITE_DENIED:
				$msg = 'Could not write to direcotry: '.$msg;
				break;
			case self::STREAM_CONNECTION_FAILED:
				$msg = 'Could not connect to: '.$msg;
				break;
			case self::STREAM_CONNECTION_LOST:
				$msg = 'Connection to '.$msg.' has been lost';
				break;
			case self::STREAM_TIMED_OUT:
				$msg = 'Connection to '.$msg.' has timed out';
				break;
			case self::STREAM_TIMEOUT:
				$msg = 'Could not set stream timeout to: '.$msg;
				break;
			case self::STREAM_BLOCKING_FAILED:
				$msg = 'Could not enable/disable blocking mode';
				break;
			case self::STREAM_READ_DENIED:
				$msg = 'Could not read from stream';
				break;
			case self::STREAM_WRITE_DENIED:
				$msg = 'Could not write to stream';
				break;
			default:
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
