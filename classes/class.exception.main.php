<?php
/**
 * Main Exception class
 *
 * All other exceptions SHOULD extend this one
 *
 * General Exceptions error code range
 * 1000 - 1099
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @access private
 */
class MainException extends Exception
{
	/**
	 * general errors/catch all
	 * @var integer
	 * @since version 1.0.0
	 */
	const UNKNOWN					= 1000;

	/**
	 * invalid param type, use custom message
	 * @var integer
	 * @since version 1.0.0
	 */
	const INVALID_PARAM				= 1001;

	/**
	 * default message
	 * msg should be param name
	 * param given is empty, data expected
	 * @var integer
	 * @since version 1.0.0
	 */
	const PARAM_EMPTY				= 1002;

	/**
	 * default message
	 * type check for a string
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_STRING				= 1005;
	/**
	 * custom message
	 * type check for a string
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_STRING_CUSTOM		= 1006;

	/**
	 * default message
	 * msg should be type received
	 * type check for a integer
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_INTEGER				= 1007;
	/**
	 * custom message
	 * type check for a integer
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_INTEGER_CUSTOM		= 1008;

	/**
	 * default message
	 * msg should be type received
	 * type check for a boolean
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_BOOLEAN				= 1009;
	/**
	 * custom message
	 * type check for a boolean
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_BOOLEAN_CUSTOM		= 1010;

	/**
	 * default message
	 * msg should be type received
	 * type check for a array
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_ARRAY				= 1011;
	/**
	 * custom message
	 * type check for a array
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_ARRAY_CUSTOM			= 1012;

	/**
	 * default message
	 * msg should be type received
	 * type check for a object
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_OBJECT				= 1013;
	/**
	 * custom message
	 * type check for a object
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_OBJECT_CUSTOM		= 1014;

	/**
	 * default message
	 * msg should be type received
	 * type check for a resource
	 * @var integer
	 * @since version 1.0.0
	 */
	const TYPE_RESOURCE				= 1015;
	/**
	 * custom message
	 * type check for a resource
	 * @var integer type check for a resource
	 * @since version 1.0.0
	 */
	const TYPE_RESOURCE_CUSTOM		= 1016;

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
			case self::PARAM_EMPTY:
				$msg = 'Parameter '.$msg.' is empty.';
				break;
			case self::TYPE_STRING:
				$msg = 'String expected: '.$msg.' received.';
				break;
			case self::TYPE_INTEGER:
				$msg = 'Integer expected: '.$msg.' received.';
				break;
			case self::TYPE_BOOLEAN:
				$msg = 'Boolean expected: '.$msg.' received.';
				break;
			case self::TYPE_ARRAY:
				$msg = 'Array expected: '.$msg.' received.';
				break;
			case self::TYPE_OBJECT:
				$msg = 'Object expected: '.$msg.' received.';
				break;
			case self::TYPE_RESOURCE:
				$msg = 'Resource expected: '.$msg.' received.';
				break;
			// pass all custom error messages on
			case self::UNKNOWN:
			case self::INVALID_PARAM:
			case self::TYPE_STRING_CUSTOM:
			case self::TYPE_INTEGER_CUSTOM:
			case self::TYPE_BOOLEAN_CUSTOM:
			case self::TYPE_ARRAY_CUSTOM:
			case self::TYPE_OBJECT_CUSTOM:
			case self::TYPE_RESOURCE_CUSTOM:
			default:
				// any unknown exceptions
				// no need to do anything just pass it on
		}

		// pass on to parent class
		parent::__construct($msg, $code);
	}

	/**
	 * Custom output
	 * 
	 * @return string
	 * @since version 1.0.0
	 */
	public function __toString()
	{
		$msg = "<br/>\n";
		$msg .= '<b>File:</b> '.parent::getFile()."<br/>\n";
		$msg .= '<b>Line:</b> '.parent::getLine()."<br/>\n";
		$msg .= '<b>Code:</b> '.parent::getCode()."<br/>\n";
		$msg .= "<b>Message:</b> <pre>".parent::getMessage()."</pre><br/>\n";
		$msg .= '<b>Trace:</b> <pre>'.print_r(parent::getTrace(), true)."</pre><br/>\n";
		return $msg;
	}
}

?>
