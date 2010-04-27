<?php
/**
 * Error Functions
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Custom error handler, replace PHP's built in error handler
 * 
 * $errNo the error code i.e E_USER_ERROR E_USER_WARNING etc...
 * 
 * Return false = PHP's built in error handler will run, true successful
 * 
 * @param integer $errNo
 * @param string $errStr
 * @param string $errFile [optional]
 * @param integer $errLine [optional]
 * @param array $errContext [optional]
 * @return bool
 * @see set_error_handler()
 * @since version 1.0.0
 */
function errorHandler($errNo, $errStr, $errFile = '', $errLine = 0,
	$errContext = array())
{
	// extra error message
	// these are normally default values that are set etc.. that you want present
	// in the error but not always have to manually give.
	if (!empty($GLOBALS['errorMessage']))
	{
		// add to the end of error string
		$errStr .= "<br/>\n<br/>\n<br/>\n<b>Debug code ignore this</b>:<br/>\nGlobal values:\n".$GLOBALS['errorMessage'];
	}

	/*
		check for a constant called DEBUG_ENABLED if it is true
		it means the current site is under devlopment
		and we should display ALL errors
	*/
	// check if DEBUG_ENABLED is defined and is true
	$debug = defined('DEBUG_ENABLED') && DEBUG_ENABLED === true?true:false;

	// get the user ip and host/proxy, ONLY on the production server
	// while debugging don't need this
	$ip = '';
	$host = '';
	$proxy = '';
	if ($debug === false)
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			if (preg_match("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?/",
				$_SERVER['HTTP_X_FORWARDED_FOR'])
			)
			{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				$host = gethostbyaddr($_SERVER['HTTP_X_FORWARDED_FOR']);
				$proxy = $_SERVER['REMOTE_ADDR'];
			}
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
			$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}
	}

	// get the errors and messages etc..
	$fatal = false;
	$logerror = true;
	$errorMessage = '';
	$errorMessageLog = '';
	$errorLevels = '';
	$errorDisplay = true;

	// taken from php.net user comment
	$bit = ini_get('error_reporting');
	while ($bit > 0)
	{
		for($i = 0, $n = 0; $i <= $bit; $i = 1 * pow(2, $n), $n++)
		{
			$end = $i;
		}
		$errorLevels[] = $end;
		$bit = $bit - $end;
	}
	// end taken from php.net user comment

	switch ($errNo)
	{
		// fatal
		case E_USER_ERROR:
		case E_ERROR:
			// type
			$eType = 'Fatal error';
			// kill fatal errors
			$fatal = true;
			break;
		// warning
		case E_USER_WARNING:
		case E_WARNING:
			$eType = 'Warning';
			if (!in_array($errNo, $errorLevels))
			{
				$errorDisplay = false;
			}
			// no need to log warning, ONLY while debuging
			if ($debug === true)
			{
				$logerror = false;
			}
			break;
		// notices
		case E_USER_NOTICE:
		case E_NOTICE:
			$eType = 'Notice';
			if (!in_array($errNo, $errorLevels))
			{
				$errorDisplay = false;
			}
			// no need to log notices, ONLY while debuging
			if ($debug === true)
			{
				$logerror = false;
			}
			break;
		default:
			$eType = 'Unknown';
			// kill all other errors
			$fatal = true;
	}
	// create the displyable error
	$errorMessage = '<b>'.$eType.'</b>: '.$errStr.' in <b>'.$errFile.'</b> on'.
		' line <b>'.$errLine."</b><br/>\n";

	// create the logable error
	$errorMessageLog = '['.date('Y-m-d H:i:s').'] ['.$ip.
		'/'.$host.'/'.$proxy.'] '.$eType.': '.$errStr.' in '.$errFile.
		' on line '.$errLine."\n";

	// if debug mode is off get some email info
	if ($debug === true)
	{
		// if @ is sent don't display error
		if (error_reporting() !== 0)
		{
			if ($errorDisplay === true)
			{
				// display the errors
				print '<br/></td></tr></body></html>'."\n";
				print $errorMessage;
			}
		}
	}
	else
	{
		/*
			debug mode is disabled, we are on a production site, log all errors
			and send them via email
			expected constants:
			ERROR_EMAIL string
			ERROR_FROM_EMAIL string
			ERROR_EMAIL_HOST string [optional]
			ERROR_EMAIL_PORT integer [optional]
			ERROR_EMAIL_VERIFY_USER string [optional]
		*/

		// error email address
		$errorEmail = defined('ERROR_EMAIL')?ERROR_EMAIL:'';
		// error from email address
		$errorFromEmail = defined('ERROR_FROM_EMAIL')?ERROR_FROM_EMAIL:'';

		// todo fix these
		$subject = '';
		$message = $errContext;

		if (!@mail($errorEmail, $subject, $message, 'From: <'.
			$errorFromEmail.">\n"))
		{
			// error couldn't send email, @todo add handling here
		}

		// Nothing SHOULD be printed out to the user.
	}

	if ($logerror === true)
	{
		/*
			log the error to the PHP error log
			NOTE: most errors SHOULD be logged, and the only times they shouldn't
			are when developing
			get the php error log file
		*/
		$errorLogFile = ini_get('error_log');
		error_log($errorMessageLog, 3, $errorLogFile);
	}

	// kill the script if a fatal error
	if ($fatal === true)
	{
		exit(1);
	}

	/*
		returning false will make php's built in error handler run
		returning true won't
	*/
	return true;
}

/**
 * Exceptions handling function
 *
 * @param string $exception
 * @since version 1.1.0
 */
function exceptionHandler($exception)
{
	print '<b>Uncaught exception (missing try block):</b><br>'.$exception."\n";
}

/**
 * Sends a 404 error
 * 
 * @param string $message [optional]
 * @since version 1.0.0
 */
function http404($message = '')
{
	httpError('HTTP/1.1 404 Not Found', $message);
}

/**
 * Sends a 500 error
 * 
 * @param string $message [optional]
 * @since version 1.0.0
 */
function http500($message = '')
{
	httpError('HTTP/1.1 500 Internal Server Error', $message);
}

/**
 * Display HTTP errors
 * 
 * @param string $error
 * @param string $message [optional]
 * @param string $httpVersion [optional]
 * @since version 1.0
 */
function httpError($error, $message = '', $httpVersion = '1.1')
{
	if (stristr($error, 'http/'.$httpVersion))
	{
		// remove any output buffering
		if (ob_get_level() > 0)
		{
			while (@ob_end_clean());
		}

		// send error
		header($error);
		// close the connection
		header('Connection: close');

		if (empty($message))
		{
			// display the error
			print str_ireplace('http/'.$httpVersion.' ', '', $error."\n");
		}
		else
		{
			print $message;
		}
		/*
			exit isn't called because the user may wish to do
			something after sending the error
			it is NOT recommened to send any other data
			(except error explanation)
		*/
	}
}

?>
