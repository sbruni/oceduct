<?php
/**
 * Authentication class
 *
 * Requires classes:
 * class.http_headers.php
 *
 * Retrive the authorization header
 * sources are:
 * - Basic
 * - Browser
 * - Digest
 *
 * This class uses sessions, they must be enabled
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Authentication
 * @access private
 * @see MainException
 */
class Authentication
{
	/**
	 * @var string
	 * @since version 1.0.0
	 */
	const AUTHTYPE_BASIC				= 'basic';

	/**
	 * @var string
	 * @since version 1.0.0
	 */
	const AUTHTYPE_DIGEST				= 'digest';

	/**
	 * @var string
	 * @since version 1.0.0
	 */
	const AUTHTYPE_NTLM					= 'ntlm';

	/**
	 * description of session array, to be set in the array
	 * a warrning not to modify it from outside this class
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const SESSION_DESC					= 'The session array: _authentication is for internal use only, do NOT modify. See class Authentication.';

	/**
	 * Clears all authentication
	 * resets the _authentication session array
	 * 
	 * @since version 1.0.0
	 */
	public function clear()
	{
		unset($_SESSION[$this->_sessionPrefix.'_authentication']);
		$_SESSION[$this->_sessionPrefix.'_authentication']['description'] = self::SESSION_DESC;
	}

	/**
	 * Creates the Authorization header
	 * valid methods:
	 * - Basic
	 * - Digest
	 * - NTLM
	 *
	 * @param string $method [optional]
	 * @param string $id [optional]
	 * @param string $password [optional]
	 * @since version 1.0.0
	 * @todo add NTLM and Digest authentication types
	 */
	public function createAuthorizationHeader($method = '', $id = '',
		$password = '')
	{
		switch ($method)
		{
			case self::AUTHTYPE_BASIC:
				/**
				 * The word "Basic" states the authorization type for the browser
				 * to use. (for compatibility use exaclty Basic (capital B)
				 * Basic authorization is created by base64 encoding the user id
				 * and password with a colon between them.
				 */
				return 'Basic '.base64_encode($id.':'.$password);
				break;

			/**
			 * @todo add NTLM and Digest auth type
			 *
			 * case self::AUTHTYPE_DIGEST:
			 * case self::AUTHTYPE_NTLM:
		 	 */

			default:
				throw new MainException('Invalid authorization type',
					MainException::INVALID_PARAM);
		}
	}

	/**
	 * Gets the active authorization header
	 * 
	 * @return string
	 * @since version 1.0.0
	 */
	public function getAuthorization()
	{
		return $this->_authorization();
	}

	/**
	 * Gets the active userid
	 * 
	 * @return string active userid
	 * @since version 1.0.0
	 */
	public function getUserId()
	{
		return $this->_userid();
	}

	/**
	 * Login with the browsers authorization header
	 *
	 * @param string $defaultUserid
	 * @param bool $override [optional]
	 * @since version 1.0.0
	 */
	public function loginBrowser($defaultUserid = '', $override = false)
	{
		// set the user id to be from the browser (use default if not found)
		$user = $this->_getBrowsersUser($defaultUserid);

		// set the userid
		$user = $this->_userid($user, $override);

		// set the authorization to the browsers
		$this->_authorization($this->_headers->get('Authorization'), $override);
	}

	/**
	 * Does a custom login (recommened ONLY for debugging)
	 *
	 * @param string $id
	 * @param string $authorization
	 * @param $override [optional]
	 * @since version 1.0.0
	 */
	public function loginCustom($id, $authorization, $override = true)
	{
		$this->_userid($id, $override);
		$this->_authorization($authorization, $override);
	}

	/**
	 * Log in via NTLM (using apaches mod_ntlm or mod_auth_sspi)
	 *
	 * NTLM login: Valid methods are:
	 * - redirect
	 * - direct
	 *
	 * NTLM -> Redirect
	 * - A specific directory is set to use NTLM, the user is redirected to
	 * that location, upon detecting the userid the user is redirected back
	 * to where they came from
	 * - Process is seamless to the user
	 * - The user does not need to enter a username or password
	 * (some disadvantages)
	 * - Bots PDAs etc... can't login as easily (@todo allow pda viewing)
	 * (redirect screws them up)
	 * - If user B uses user A's computer they'll be logged in as user A
	 * with all permissions etc...
	 *
	 * NTLM -> direct
	 * The script that is calling this function MUST be within an NTLM directory
	 * This is the second part of the redirect, but can be called on it's own
	 * for direct access
	 *
	 * @param string $method [optional]
	 * @param string $location [optional]
	 * @param string $defaultUserId [optional]
	 * @param bool $override [optional]
	 * @since version 1.0.0
	 * @see Authentication::_login()
	 */
	public function loginNtlm($method = 'direct', $redirectTo = '',
		$defaultUserId = '', $override = false)
	{
		if (empty($method))
		{
			throw new MainException('method',
				MainException::PARAM_EMPTY);
		}

		// exit unless override is true or user is empty
		// get the userid
		$user = $this->_userid();
		if ($override === false && !empty($user))
		{
			return;
		}

		switch (strtolower($method))
		{
			case 'redirect':
				// standard http start
				$http = 'http://';

				// check for SSL if enabled change to https
				if ((isset($_SERVER['HTTPS']) &&
					$_SERVER['HTTPS'] == 'on')
					||
					(isset($_SERVER['SERVER_PORT']) &&
					$_SERVER['SERVER_PORT'] == 443))
				{
					$http = 'https://';
				}

				// this is the return url for the ntlm set
				$_SESSION[$this->_sessionPrefix.'_authentication']['ntlmReturnUrl'] =
					$http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

				// send them on there way
				$this->_headers->set(307);
				$this->_headers->set('Location', $redirectTo);
				$this->_headers->set('Connection', 'close');
				// exit is a must
				exit();
				break;
			case 'direct':
			default:
				$user = $this->_getBrowsersUser($defaultUserId);

				// attempt to set the new user
				$user = $this->_userid($user, $override);

				/**
				 * When a user is logged in via NTLM we get there username only
				 * for the authorization header we create a Basic authorization
				 * header and give a fake password. (md5 of there userid)
				 */
				$this->_authorization($this->createAuthorizationHeader('basic',
					$user, md5($user)));

				// we only return i	f we have a valid session to return to
				// this MUST be created by this class
				if (isset($_SESSION[$this->_sessionPrefix.'_authentication']['ntlmReturnUrl']))
				{
					$url = $_SESSION[$this->_sessionPrefix.'_authentication']['ntlmReturnUrl'];
					// we don't need this session variable
					unset($_SESSION[$this->_sessionPrefix.'_authentication']['ntlmReturnUrl']);

					$this->_headers->set(307);
					$this->_headers->set('Location', $url);
					$this->_headers->set('Connection', 'close');
					// exit is a must
					exit();
				}
		}
	}

	/**
	 * Class constructor
	 *
	 * @since version 1.0.0
	 */
	public function __construct()
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();

		// make sure sessions are enabled
		if (!in_array('session', get_loaded_extensions()))
		{
			throw new MainException('Sessions are not available, sessions MUST '.
				'be enabled to use this class properly');
		}
		// check for HttpHeaders
		if (in_array('HttpHeaders', $declaredClasses) === false)
		{
			throw new MainException('HttpHeaders: class not found',
				MainException::INVALID_PARAM);
		}

		$this->_httpVersion = '1.1';

		// ini HTTP Headers class
		$this->_headers = new HttpHeaders();
		$this->_headers->httpVersion = $this->_httpVersion;

		// nonce check
		$this->_checkNonce();

		// always set/reset the description
		$_SESSION[$this->_sessionPrefix.'_authentication']['description'] = self::SESSION_DESC;
	}

	/**
	 * Get overloading
	 * Is case sensitive
	 * 
	 * @param string $var
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function __get($var)
	{
		$return = '';
		switch ($var)
		{
			case 'authorization':
				$return = $this->getAuthorization();
				break;
			case 'httpVersion':
				$return = $this->_httpVersion;
				break;
			case 'sessionPrefix':
				$return = $this->_sessionPrefix;
				break;
			case 'username':
			case 'userid':
			case 'user':
				$return = $this->getUserId();
				break;
			default:
		}
		return $return;
	}

   	/**
	 * Set overloading
	 * 
	 * @param string $var
	 * @param mixed $value
	 * @since version 1.0.0
	 */
	public function __set($var, $value)
	{
		switch ($var)
		{
			case 'httpVersion':
				// make sure it's a string
				if (!is_string($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}

				// currently only 1.1 and 1.0 are supported more can be added
				switch ($value)
				{
					case '1.1':
					case '1.0':
						// set the local version and the headers version
						$this->_httpVersion = $value;
						$this->_headers->httpVersion = $this->_httpVersion;
						break;
					default:
					throw new MainException('Currently only HTTP 1.1 and 1.0'.
						' are supported', MainException::INVALID_PARAM);
				}
				break;
			case 'sessionPrefix':
				// session prefix
				// if set the session _authentication is changed to yourprefix.'_authentication'
				// this allows the auth class to be used in multiple sites
				// within the same domain and not conflict with each other
				if (!is_string($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}
				$this->_sessionPrefix = $value;
			default:
				throw new MainException('Invalid parameter "'.$var.
					'" with value "'.$value.'"', MainException::INVALID_PARAM);
		}
	}

	// private
	/**
	 * HTTP Headers class
	 * 
	 * @var object
	 * @since version 1.0.0
	 */
	private $_headers;

	/**
	 * the http version
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	private $_httpVersion;

	/**
	 * Session variables prefix
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	private $_sessionPrefix;

	/**
	 * Sets/gets active authorization
	 * 
	 * @param string $authorization [optional]
	 * @param bool $override [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	private function _authorization($authorization = '', $override = false)
	{
		// if override is enabled set active userid to given userid
		// a valid userid must be given
		if ($override === true && !empty($authorization))
		{
			$_SESSION[$this->_sessionPrefix.'_authentication']['authorization'] = $authorization;
			return $authorization;
		}

		// if the session userid exists use it
		if (isset($_SESSION[$this->_sessionPrefix.'_authentication']['authorization']))
		{
			return $_SESSION[$this->_sessionPrefix.'_authentication']['authorization'];
		}
		elseif (!empty($authorization))
		{
			$_SESSION[$this->_sessionPrefix.'_authentication']['authorization'] = $authorization;
			return $authorization;
		}
		else
		{
			return '';
		}
	}

	/**
	 * Runs a check on the current nonce
	 * The nonce is used to store the correct session info
	 *
	 * @since version 1.0.0
	 */
	private function _checkNonce()
	{
		// if an nonce exists we set it in the session array for validation later
		$matches = array();
		$authorization = $this->_headers->get('Authorization');
		if (preg_match('!nonce="(.[^"]+)",!i', $authorization, $matches))
		{
			if (empty($_SESSION[$this->_sessionPrefix.'_authentication']['nonce']))
			{
				// no session created yet make a new one
				$_SESSION[$this->_sessionPrefix.'_authentication']['nonce'] = $matches[1];
			}
			else
			{
				// session already exists check if it's the same
				// if it is the same as what we have don't do anything
				// if it's not the same reinital the auth session
				if ($_SESSION[$this->_sessionPrefix.'_authentication']['nonce'] != $matches[1])
				{
					unset($_SESSION[$this->_sessionPrefix.'_authentication']);
					$_SESSION[$this->_sessionPrefix.'_authentication']['nonce'] = $matches[1];
				}
			}
		}
		// if we have an nonce in the session but the browser isn't sending any
		// thorw an error, the browser always needs to send one
		elseif (!empty($_SESSION[$this->_sessionPrefix.'_authentication']['nonce']))
		{
			throw new MainException('Nonce in session but not sent with request: '.
				$_SESSION[$this->_sessionPrefix.'_authentication']['nonce'],
				MainException::UNKNOWN);
		}
	}

	/**
	 * Gets the userid from the browser if not found sets to default
	 * default should be a low level account with no permissions
	 *
	 * @param string $default [optional]
	 * @param bool $raw [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	private function _getBrowsersUser($default = '', $raw = false)
	{
		// get the active user id
		$user = '';
		if (isset($_SERVER['REMOTE_USER']))
		{
			$user = $_SERVER['REMOTE_USER'];
		}
		elseif (isset($_SERVER['PHP_AUTH_USER']))
		{
			$user = $_SERVER['PHP_AUTH_USER'];
		}
		else
		{
			if (!empty($default))
			{
				// default user if none exists
				$user = $default;
			}
			else
			{
				// exit siently
				exit();
			}
		}

		if ($raw !== true)
		{
			/**
			 * This is specific to NTLM login but shouldn't hurt other modes
			 * if a domain exists remove it
			 * remove the domain i.e WS\JohnDoe
			 * so far I've only seen this on windows systems
			 * with mod_auth_sspi, but it shouldn't hurt
			 * so that means there shouldn't be backslashes (\) in user ids
			 */
			if (strpos($user, '\\') !== false)
			{
				$user = trim(strstr($user, '\\'), '\\');
			}
		}
		return $user;
	}

	/**
	 * Sets/gets active userid
	 *
	 * The userid is NEVER changed within this class, the reason is that this
	 * class should contain an EXACT case for case match of all info.
	 *
	 * Lowercasing or other changes to the userid for storagte etc... should
	 * be taken care of by the calling scripts.
	 *
	 * @param string $userid [optional]
	 * @param bool $override [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	private function _userid($userid = '', $override = false)
	{
		// if override is enabled set active userid to given userid
		// a valid userid must be given
		if ($override === true && !empty($userid))
		{
			$_SESSION[$this->_sessionPrefix.'_authentication']['userid'] = $userid;
			return $userid;
		}

		// if the session userid exists use it
		if (isset($_SESSION[$this->_sessionPrefix.'_authentication']['userid']))
		{
			return $_SESSION[$this->_sessionPrefix.'_authentication']['userid'];
		}
		elseif (!empty($userid))
		{
			$_SESSION[$this->_sessionPrefix.'_authentication']['userid'] = $userid;
			return $userid;
		}
		else
		{
			return '';
		}
	}
}

?>
