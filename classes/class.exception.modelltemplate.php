<?php
/**
 * ModellTemplate Exceptions Class
 *
 * Template Exceptions error code range
 * 500 - 549
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @access private
 * @see MainException
 */
class ModellTemplateException extends MainException
{
	/**
	 * default message
	 * msg should be the template name
	 * @var integer
	 * @since version 1.0.0
	 */
	const TEMPLATE_NOT_EXIST			= 500;

	/**
	 * default message
	 * msg should be the name of the template that is still active
	 * @var integer
	 * @since version 1.0.0
	 */
	const TEMPLATE_STILL_ACTIVE			= 501;

	/**
	 * default message
	 * @var integer
	 * @since version 1.0.0
	 */
	const INVALID_STORAGE_TYPE			= 510;

	/**
	 * @var integer
	 * @since version 1.0.0
	 */
	const DB_TABLE_MISSING				= 521;
	/**
	 * @var integer
	 * @since version 1.0.0
	 */
	const INVALID_TEMPLATE_TYPE			= 525;

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
			case self::TEMPLATE_NOT_EXIST:
				$msg = 'Template '.$msg.' does not exist';
				break;
			case self::TEMPLATE_STILL_ACTIVE:
				$msg = 'Template '.$msg.
					' is in use, use a seperate instance of the object.';
				break;
			case self::INVALID_STORAGE_TYPE:
				$msg = 'Invalid storage type given, expectiong either "file"'.
				' or "postgresql". Received '.$msg;
				break;
			case self::DB_TABLE_MISSING:
				$msg = ''.$msg;
				break;
			case self::INVALID_TEMPLATE_TYPE:
				$msg = 'Template given does not match created templates '.$msg;
				break;
			default:
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
