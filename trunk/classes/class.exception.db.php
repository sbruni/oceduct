<?php
/**
 * Database Exceptions class
 *
 * Database Exceptions error code range
 * 200 - 219
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @see MainException
 * @access private
 */
class DatabaseException extends MainException
{
	/**
	 * @var integer could not connect to server
	 * @since version 1.0.0
	 */
	const CONNECTION_FAILED				= 200;
	/**
	 * @var integer connection has been Lost
	 * @since version 1.0.0
	 */
	const CONNECTION_LOST				= 201;
	/**
	 * @var integer connection is Invalid i.e. could not close the conection
	 * @since version 1.0.0
	 */
	const CONNECTION_INVALID			= 202;

	/**
	 * @var integer no database specified
	 * @since version 1.0.0
	 */
	const DATABASE_EMPTY				= 207;

	/**
	 * @var integer an unknown error while retriveing the records
	 * @since version 1.0.0
	 */
	const RECORDS_UNKNOWN				= 209;

	/**
	 * @var integer no query given
	 * @since version 1.0.0
	 */
	const QUERY_EMPTY					= 211;
	/**
	 * @var integer query was not run, need to run one before calling X
	 * @since version 1.0.0
	 */
	const QUERY_NOT_RUN					= 212;
	/**
	 * @var integer query is invalid, either systax wise or other
	 * @since version 1.0.0
	 */
	const QUERY_INVALID					= 213;
	/**
	 * @var integer an unknown query error
	 * @since version 1.0.0
	 */
	const QUERY_UNKNOWN				= 214;

	/**
	 * Class constructor
	 * @param string msg [optional]
	 * @param integer code [optional]
	 * @since version 1.0.0
	 */
	public function __construct($msg = '', $code = 1000)
	{
		switch ($code)
		{
			case self::CONNECTION_FAILED:
				$msg = 'Connection falied '.$msg;
				break;
			case self::CONNECTION_LOST:
				$msg = 'Connection lost '.$msg;
				break;
			case self::CONNECTION_INVALID:
				$msg = 'Connection is invalid '.$msg;
				break;
			case self::DATABASE_EMPTY:
				$msg = 'No database specified '.$msg;
				break;
			case self::RECORDS_UNKNOWN:
				$msg = 'Unknown records error: '.$msg;
				break;
			case self::QUERY_EMPTY:
				$msg = 'Query is empty '.$msg;
				break;
			case self::QUERY_NOT_RUN:
				$msg = 'Query was not run '.$msg;
				break;
			case self::QUERY_INVALID:
				$msg = 'Query is invalid: '.$msg;
				break;
			case self::QUERY_UNKNOWN:
				$msg = 'Unknown query error: '.$msg;
				break;
			default:
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
