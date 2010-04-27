<?php
/**
 * Soap Messaging Exceptions class
 *
 * 1800 - 1850
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @access private
 * @see MainException
 */
class SoapMessagingException extends MainException
{
	/**
	 * Invalid request/command
	 * @var integer
	 * @since version 1.0.0
	 */
	const SOAP_INVALID							= 1800;

	/**
	 * SOAP response was unparseable
	 * @var integer
	 * @since version 1.0.0
	 */
	const SOAP_UNPARSEABLE						= 1801;

	/**
	 * SOAP type uninmplemented
	 * @var integer
	 * @since version 1.0.0
	 */
	const SOAP_UNINMPLEMENTED					= 1802;

	/**
	 * SOAP type unsupported
	 * @var integer
	 * @since version 1.0.0
	 */
	const SOAP_UNSUPPORTED						= 1803;

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
			case self::SOAP_INVALID:
				$msg = 'Invalid: '.$msg;
				break;
			case self::SOAP_UNPARSEABLE:
				$msg = 'SOAP response was unparseable.';
				break;
			case self::SOAP_UNINMPLEMENTED:
				$msg = 'Uninmplemented SOAP type.';
				break;
			case self::SOAP_UNSUPPORTED:
				$msg = 'Unsupported SOAP type.';
				break;
			default:
				// any unknown exceptions
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
