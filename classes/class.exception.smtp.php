<?php
/**
 * SMTP Exception class
 *
 * General Exceptions error code range
 * SMTP 400 - 599
 * 4000 - 4999
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @access private
 */
class SmtpException extends MainException
{
	/**
	 * 421 <domain> Service not available, closing transmission channel
	 * (This may be a reply to any command if the service knows it must shutdown)
	 * @var integer
	 * @since version 1.0.0
	 */
	const SERVICE_NOT_AVAILABLE				= 421;

	/**
	 * 450 Requested mail action not taken: mailbox unavailable
	 * (e.g., mailbox busy)
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_UNAVAILABLE_BUSY			= 450;

	/**
	 * 451 Requested action aborted: local error in processing
	 * @var integer
	 * @since version 1.0.0
	 */
	const LOCAL_ERROR						= 451;

	/**
	 * 452 Requested action not taken: insufficient system storage
	 * @var integer
	 * @since version 1.0.0
	 */
	const INSUFFICIENT_SYSTEM_STORAGE		= 452;

	/**
	 * 500 Syntax error, command unrecognized
	 * (This may include errors such as command line too long)
	 * @var integer
	 * @since version 1.0.0
	 */
	const SE_COMMAND_UNRECOGNIZED			= 500;

	/**
	 * 501 Syntax error in parameters or arguments
	 * @var integer
	 * @since version 1.0.0
	 */
	const SE_PARAMETERS_ARGUMENTS			= 501;

	/**
	 * 502 Command not implemented
	 * @var integer
	 * @since version 1.0.0
	 */
	const COMMAND_NOT_IMPLEMENTED			= 502;

	/**
	 * 503 Bad sequence of commands
	 * @var integer
	 * @since version 1.0.0
	 */
	const BAD_SEQUENCE						= 503;

	/**
	 * 504 Command parameter not implemented
	 * @var integer
	 * @since version 1.0.0
	 */
	const COMMAND_PARMS_NOT_IMPLEMENTED		= 504;

	/**
	 * 550 Requested action not taken: mailbox unavailable
	 * (e.g., mailbox not found, no access, or command rejected for policy reasons)
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_UNAVAILABLE				= 550;

	/**
	 * 551 User not local; please try <forward-path>
	 * @var integer
	 * @since version 1.0.0
	 */
	const USER_NOT_LOCAL_NO_FORWARD			= 551;

	/**
	 * 552 Requested mail action aborted: exceeded storage allocation
	 * @var integer
	 * @since version 1.0.0
	 */
	const EXCEEDED_STORAGE					= 552;

	/**
	 * 553 Requested action not taken: mailbox name not allowed
	 * (e.g., mailbox syntax incorrect)
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAILBOX_NAME_NOT_ALLOWED			= 553;

	/**
	 * 554 Transaction failed  (Or, in the case of a connection-opening
	 * response, "No SMTP service here")
	 * @var integer
	 * @since version 1.0.0
	 */
	const TRANSACTION_FAILED				= 554;

	/**
	 * Invalid address
	 * i.e address contains invalid chars or is formatted wrong
	 * @var integer
	 * @since version 1.0.0
	 */
	const INVALID_ADDRESS				= 4001;

	/**
	 * Subject is empty
	 * @var integer
	 * @since version 1.0.0
	 */
	const SUBJECT_EMPTY					= 4002;

	/**
	 * Connection failed
	 * @var integer
	 * @since version 1.0.0
	 */
	const CONNECTION_FAILED				= 4005;

	/**
	 * Connection error
	 * @var integer
	 * @since version 1.0.0
	 */
	const CONNECTION_ERROR				= 4006;


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
			case self::SERVICE_NOT_AVAILABLE:
				$msg = '421 <domain> Service not available, closing transmission channel'.$msg;
				break;
			case self::MAILBOX_UNAVAILABLE_BUSY:
				$msg = '450 Requested mail action not taken: mailbox unavailable'.$msg;
				break;
			case self::LOCAL_ERROR:
				$msg = '451 Requested action aborted: local error in processing'.$msg;
				break;
			case self::INSUFFICIENT_SYSTEM_STORAGE:
				$msg = '452 Requested action not taken: insufficient system storage'.$msg;
				break;
			case self::SE_COMMAND_UNRECOGNIZED:
				$msg = '500 Syntax error, command unrecognized'.$msg;
				break;
			case self::SE_PARAMETERS_ARGUMENTS:
				$msg = '501 Syntax error in parameters or arguments'.$msg;
				break;
			case self::COMMAND_NOT_IMPLEMENTED:
				$msg = '502 Command not implemented'.$msg;
				break;
			case self::BAD_SEQUENCE:
				$msg = '503 Bad sequence of commands'.$msg;
				break;
			case self::COMMAND_PARMS_NOT_IMPLEMENTED:
				$msg = '504 Command parameter not implemented'.$msg;
				break;
			case self::MAILBOX_UNAVAILABLE:
				$msg = '550 Requested action not taken: mailbox unavailable'.$msg;
				break;
			case self::USER_NOT_LOCAL_NO_FORWARD:
				$msg = '551 User not local; please try <forward-path>'.$msg;
				break;
			case self::EXCEEDED_STORAGE:
				$msg = '552 Requested mail action aborted: exceeded storage allocation'.$msg;
				break;
			case self::MAILBOX_NAME_NOT_ALLOWED:
				$msg = '553 Requested action not taken: mailbox name not allowed'.$msg;
				break;
			case self::TRANSACTION_FAILED:
				$msg = '554 Transaction failed  (Or, in the case of a connection-opening'.$msg;
				break;
			case self::INVALID_ADDRESS:
				$msg = 'Invalid (e-mail) address: '.$msg;
				break;
			case self::SUBJECT_EMPTY:
				$msg = 'Subject required: Subject was empty';
				break;
			case self::CONNECTION_FAILED:
				// nothing
			case self::CONNECTION_ERROR:
				// nothing
			default:
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>