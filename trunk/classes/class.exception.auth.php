<?php
/**
 * Authentication Exceptions class
 *
 * Authentication Exceptions error code range
 * 5100 - 5149
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @see MainException
 * @access private
 * @todo add error handling to this
 */
class AuthenticationException extends MainException
{
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
			default:
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
