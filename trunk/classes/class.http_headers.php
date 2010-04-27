<?php
/**
 * HTTP Headers Class
 *
 * Read RFC 2616 for more info
 * http://www.ietf.org/rfc/rfc2616
 *
 * Cookies RFC 2109
 * http://www.faqs.org/rfcs/rfc2109
 *
 * Keep-Alive RFC 2068
 * http://www.faqs.org/rfcs/rfc2068
 *
 * - 1xx: Informational - Request received, continuing process
 * - 2xx: Success - The action was successfully received, understood, and
 * accepted
 * - 3xx: Redirection - Further action must be taken in order to complete
 * the request
 * - 4xx: Client Error - The request contains bad syntax or cannot be
 * fulfilled
 * - 5xx: Server Error - The server failed to fulfill an apparently valid
 * request
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package HttpHeaders
 * @access private
 */
class HttpHeaders
{
	/**
	 * CR LF line ending
	 * @var string
	 * @since version 1.0.0
	 */
	const CRLF							= "\r\n";

	/**
	 * seconds in one hour
	 * @var integer
	 * @since version 1.0.0
	 */
	const S_HOUR						= 3600;

	/**
	 * seconds in one day
	 * @var integer
	 * @since version 1.0.0
	 */
	const S_DAY							= 86400;

	/**
	 * seconds in one week
	 * @var integer
	 * @since version 1.0.0
	 */
	const S_WEEK						= 604800;

	/**
	 * seconds in (appox) one month (4.3 weeks)
	 * @var integer
	 * @since version 1.0.0
	 */
	const S_MONTH						= 2600640;

	/**
	 * seconds in (appox) one year (12 months)
	 * @var integer
	 * @since version 1.0.0
	 */
	const S_YEAR						= 31207680;

	/**
	 * HTTP version (default 1.1)
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	public $httpVersion;

	/**
	 * Clears list/one pre-sent headers
	 * A new value can be set or this can be used to clear a specific header
	 *
	 * @param mixed $headers
	 * @since version 1.0.0
	 */
	public function clear($headers)
	{
		// default headers to clear
		if (empty($headers))
		{
			throw new MainException('headers',
				MainException::PARAM_EMPTY);
		}

		// if there has been text already sent don't send headers
		if (headers_sent())
		{
			throw new MainException(
				'Headers already sent, can\'t clear headers',
				MainException::INVALID_PARAM);
		}

		// if a string is given only clear one header
		// otherwise if it's an array clear them one at a time
		if (is_string($headers))
		{
			header($headers.':');
		}
		elseif (is_array($headers))
		{
			// remove a list of headers
			foreach ($headers as $header)
			{
				header($header.':');
			}
		}
		else
		{
			throw new MainException(gettype($headers),
				MainException::TYPE_ARRAY);
		}
	}

	/**
	 * Sends a file not found set of commands
	 *
	 * @param bool $noExit [optional]
	 * @since version 1.1.0
	 */
	public function fileNotFound($noExit = false)
	{
		$this->set(404);
		$this->set('Content-Type', 'text/html; charset=iso-8859-1');
		$this->set('Content-Length', '18');
		$this->clear(array('Cache-Control', 'Expires', 'X-Powered-By', 'Pragma'));
		if ($noExit !== true)
		{
			exit('404 File not Found');
		}
	}

	/**
	 * Gets info/value of a header
	 *
	 * NOTE: {@link HttpHeaders::parse()} MUST be run first before this method
	 *
	 * By default it'll retrun the value of the header
	 * if $returnAsString is false then an array is returned containing
	 * infomation split up as much as possible
	 *
	 * If CRLF's are returned within a string then MULITPLE values were found
	 * and returned. Do not forget to check for this.
	 *
	 * @param string $header
	 * @param bool $returnAsString [optional]
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function get($header, $returnAsString = true)
	{
		if (empty($this->_headers))
		{
			$this->setActiveHeadersApacheOrEnv();
		}

		if (isset($this->_headers[$header]))
		{
			if ($returnAsString === true)
			{
				if (is_array($this->_headers[$header]))
				{
					if (isset($this->_headers[$header]['string']))
					{
						return trim($this->_headers[$header]['string']);
					}
					else
					{
						// first check if there are sub indexes if so return those
						// if not then error

						$multiple = '';
						foreach ($this->_headers[$header] as $array)
						{
							if (isset($array['string']))
							{
								$multiple .= trim($array['string']).self::CRLF;
							}
						}

						if (!empty($multiple))
						{
							return $multiple;
						}

						throw new MainException('string index missing from '.
							'header array', MainException::INVALID_PARAM);
					}
				}
				else
				{
					throw new MainException(gettype($this->_headers[$header]),
						MainException::TYPE_ARRAY);
				}
			}
			else
			{
				return $this->_headers[$header];
			}
		}
		return '';
	}

	/**
	 * Get all the headers return an array or a string (valid http/1.1)
	 *
	 * @param bool $returnAsString [optional]
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function getAll($returnAsString = false)
	{
		if (empty($this->_headers))
		{
			$this->setActiveHeadersApacheOrEnv();
		}

		$returnString = '';
		$returnArray = array();

		foreach ($this->_headers as $key => $array)
		{
			if (!empty($array[0]))
			{
				foreach ($array as $i => $x)
				{
					if (!empty($x['orgString']))
					{
						$returnString .= trim($x['orgString']).self::CRLF;
						$returnArray[$key][$i] = $x['string'];
					}
				}
			}
			else
			{
				if (!empty($array['orgString']))
				{
					$returnString .= trim($array['orgString']).self::CRLF;
					$returnArray[$key] = $array['string'];
				}
			}
		}

		$returnString .= self::CRLF;

		if ($returnAsString === true)
		{
			return $returnString;
		}

		return $returnArray;
	}

	/**
	 * Parse the data and retrive valid headers
	 *
	 * @param string $data
	 * @return array
	 * @since version 1.0.0
	 */
	public function parse($data, $lineEnding = self::CRLF)
	{
		// empty the headers list
		// don't add to what is there override it
		$this->_headers = array();

		$allHeaders = preg_split('!'.$lineEnding.'!', $data);

		/**
		 * Dynamic regex
		 * idea taken from http://www.killersoft.com/contrib/
		 * some code used from there
		 */
		$esc			= '\\\\';
		$nonAscii		= '\x80-\xff';
		$ctrl			= '\000-\037\127';
		$crList			= '\n\015';
		$newline		= '\r\n';
		$seperators		= '\(\)<>@,;:\\"/\[\]?={}\040\t';
		// no seperators or ctrl char
		$noSepCtrl = '([^'.$seperators.$ctrl.']+)';
		// quoted string
		$quotedStr = '"([^'.$esc.$nonAscii.$crList.'"]+)"';
		// comment
		$comment = '\(([^()]+)\)';
		// End Dynamic regex

		$headersRetrived = array();
		foreach ($allHeaders as $header)
		{
			$header = trim($header);

			$matches = array();
			$name = '';
			if (preg_match('!(?:([-a-z]+):(.+))|(?:([-a-z]+)(.+))!i',
				$header, $matches))
			{
				if (!empty($matches[1]) && !empty($matches[2]))
				{
					$name = $matches[1];
				}
				elseif (!empty($matches[3]) && !empty($matches[4]))
				{
					$name = $matches[3];
				}
			}

			if (empty($header))
			{
				continue;
			}

			$matches = array();
			switch (strtolower($name))
			{
				case 'content-type':
					$name = 'Content-Type';
					if (preg_match('!'.$newline.$name.': ?(('.$noSepCtrl.'+)/('.
						$noSepCtrl.'+);? ?(('.$noSepCtrl.'+)=('.$noSepCtrl.'+|'.
						$quotedStr.'))?(.*)?)'.$newline.'!i',
						self::CRLF.$header.self::CRLF, $matches))
					{
						// support multiple values per header
						// only a single header so far
						if (!empty($this->_headers[$name]) && empty($this->_headers[$name][0]))
						{
							$tmp = $this->_headers[$name];
							$this->_headers[$name] = array();
							$this->_headers[$name][0] = $tmp;
							$this->_headers[$name][1] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'type' => $matches[2],
								'subtype' => $matches[3],
								'attribute' => $matches[5],
								'value' => $matches[6],
								'extra' => $matches[7],
							);
						}
						// already has multiple values
						elseif (!empty($this->_headers[$name]) && !empty($this->_headers[$name][0]))
						{
							$this->_headers[$name][] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'type' => $matches[2],
								'subtype' => $matches[3],
								'attribute' => $matches[5],
								'value' => $matches[6],
								'extra' => $matches[7],
							);
						}
						// single value
						else
						{
							$this->_headers[$name] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'type' => $matches[2],
								'subtype' => $matches[3],
								'attribute' => $matches[5],
								'value' => $matches[6],
								'extra' => $matches[7],
							);
						}
					}
					break;
				case 'http':
					$name = 'HTTP';
					if (preg_match('!'.$newline.$name.'/([0-9]+)\.([0-9]+) ([0-9]+) (.+)'.$newline.'!i',
						self::CRLF.$header.self::CRLF, $matches))
					{
						// SHOULD ONLY EVERY BE ONE HTTP NO MULTI VALUE support
						$this->_headers[$name] = array(
							'orgString' => $matches[0],
							'string' => trim($matches[0]),
							'version' => array(
								'major' => $matches[1],
								'minor' => $matches[2],
							),
							'code' => $matches[3],
							'phrase' => $matches[4],
						);
					}
					break;
				case 'server':
					$name = 'Server';
					if (preg_match('!'.$newline.$name.': ?(('.$noSepCtrl.'+)(/('.
						$noSepCtrl.'+))?( ?'.$comment.')(.*))'.$newline.'!i',
						self::CRLF.$header.self::CRLF, $matches))
					{
						// support multiple values per header
						// only a single header so far
						if (!empty($this->_headers[$name]) && empty($this->_headers[$name][0]))
						{
							$tmp = $this->_headers[$name];
							$this->_headers[$name] = array();
							$this->_headers[$name][0] = $tmp;
							$this->_headers[$name][1] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'product' => array(
									'name' => $matches[2],
									'version' => $matches[4],
								),
								'comment' => $matches[6],
								'extra' => $matches[7],
							);
						}
						// already has multiple values
						elseif (!empty($this->_headers[$name]) && !empty($this->_headers[$name][0]))
						{
							$this->_headers[$name][] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'product' => array(
									'name' => $matches[2],
									'version' => $matches[4],
								),
								'comment' => $matches[6],
								'extra' => $matches[7],
							);
						}
						// single value
						else
						{
							$this->_headers[$name] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'product' => array(
									'name' => $matches[2],
									'version' => $matches[4],
								),
								'comment' => $matches[6],
								'extra' => $matches[7],
							);
						}
					}
					break;
				case 'transfer-encoding':
					$name = 'Transfer-Encoding';
					if (preg_match('!'.$newline.$name.': ?(('.$noSepCtrl.'+);? ?(('.
						$noSepCtrl.'+)=('.$noSepCtrl.'+|'.$quotedStr.')?)?(.*)?)'.
						$newline.'!i',
						self::CRLF.$header.self::CRLF, $matches))
					{
						// support multiple values per header
						// only a single header so far
						if (!empty($this->_headers[$name]) && empty($this->_headers[$name][0]))
						{
							$tmp = $this->_headers[$name];
							$this->_headers[$name] = array();
							$this->_headers[$name][0] = $tmp;
							$this->_headers[$name][1] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'encoding' => $matches[2],
								'attribute' => $matches[4],
								'value' => $matches[5],
								'extra' => $matches[6],
							);
						}
						// already has multiple values
						elseif (!empty($this->_headers[$name]) && !empty($this->_headers[$name][0]))
						{
							$this->_headers[$name][] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'encoding' => $matches[2],
								'attribute' => $matches[4],
								'value' => $matches[5],
								'extra' => $matches[6],
							);
						}
						// single value
						else
						{
							$this->_headers[$name] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
								'encoding' => $matches[2],
								'attribute' => $matches[4],
								'value' => $matches[5],
								'extra' => $matches[6],
							);
						}
					}
					break;
				// default headers
				default:
					if (preg_match('!'.$newline.$name.': ?(.[^\r\n]+)'.$newline.'!i',
						self::CRLF.$header.self::CRLF, $matches))
					{
						// support multiple values per header
						// only a single header so far
						if (!empty($this->_headers[$name]) && empty($this->_headers[$name][0]))
						{
							$tmp = $this->_headers[$name];
							$this->_headers[$name] = array();
							$this->_headers[$name][0] = $tmp;
							$this->_headers[$name][1] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
							);
						}
						// already has multiple values
						elseif (!empty($this->_headers[$name]) && !empty($this->_headers[$name][0]))
						{
							$this->_headers[$name][] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
							);
						}
						// single value
						else
						{
							$this->_headers[$name] = array(
								'orgString' => $matches[0],
								'string' => $matches[1],
							);
						}
					}
			}

			$headersRetrived[] = $name;
		}

		return $headersRetrived;
	}

	/**
	 * Set a header (some special cases read more below)
	 *
	 * Verify that the data given for that header is in the correct format
	 *
	 * When setting a HTTP header. $header should be the HTTP code i.e 401
	 * and $value can be anything you want after the code to display, i.e HTTP/1.1 401 ($value goes here)
	 * if no $value is given then the default will be used
	 *
	 * @param mixed $header
	 * @param mixed $value [optional]
	 * @param bool $replace [optional]
	 * @since version 1.0.0
	 */
	public function set($header, $value = '', $replace = true)
	{
		// if there has been text already sent don't send headers
		if (headers_sent())
		{
			throw new MainException(
				'Headers already sent, can\'t clear headers',
				MainException::INVALID_PARAM);
		}

		// if it's any of the HTTP codes
		$http = 'HTTP/'.$this->httpVersion;

		switch (strtolower($header))
		{
			// custom expires
			case 'expires':
				// default one week for expires
				if (empty($value))
				{
					// if no date given default to one week
					$value = gmdate('D, d M Y H:i:s',
						(time()+self::S_WEEK));
				}
				$send = 'Expires: '.$value.' GMT';
				break;
			// custom last-modified
			case 'last-modified':
				// default to today for last modified
				if (empty($value))
				{
					// if no date given default to today's date
					$value = gmdate('D, d M Y H:i:s');
				}
				$send = 'Last-Modified: '.$value.' GMT';
				break;

			// HTTP commands to send
			// send the http version and the code
			case 100:
				$send = $http.' 100 '.(empty($value)?'Continue':$value);
				break;
			case 101:
				$send = $http.' 101 '.(empty($value)?'Switching Protocols':$value);
				break;
			case 200:
				$send = $http.' 200 '.(empty($value)?'OK':$value);
				break;
			case 201:
				$send = $http.' 201 '.(empty($value)?'Created':$value);
				break;
			case 202:
				$send = $http.' 202 '.(empty($value)?'Accepted':$value);
				break;
			case 203:
				$send = $http.' 203 '.(empty($value)?'Non-Authoritative Information':$value);
				break;
			case 204:
				$send = $http.' 204 '.(empty($value)?'No Content':$value);
				break;
			case 205:
				$send = $http.' 205 '.(empty($value)?'Reset Content':$value);
				break;
			case 206:
				$send = $http.' 206 '.(empty($value)?'Partial Content':$value);
				break;
			case 300:
				$send = $http.' 300 '.(empty($value)?'Multiple Choices':$value);
				break;
			case 301:
				$send = $http.' 301 '.(empty($value)?'Moved Permanently':$value);
				break;
			case 302:
				$send = $http.' 302 '.(empty($value)?'Found':$value);
				break;
			case 303:
				$send = $http.' 303 '.(empty($value)?'See Other':$value);
				break;
			case 304:
				$send = $http.' 304 '.(empty($value)?'Not Modified':$value);
				break;
			case 305:
				$send = $http.' 305 '.(empty($value)?'Use Proxy':$value);
				break;
//					case 306:
//						$send = $http.' 306 '.(empty($value)?'(Unused)':$value);
//						break;
			case 307:
				$send = $http.' 307 '.(empty($value)?'Temporary Redirect':$value);
				break;
			case 400:
				$send = $http.' 400 '.(empty($value)?'Bad Request':$value);
				break;
			case 401:
				$send = $http.' 401 '.(empty($value)?'Unauthorized':$value);
				break;
			case 402:
				$send = $http.' 402 '.(empty($value)?'Payment Required':$value);
				break;
			case 403:
				$send = $http.' 403 '.(empty($value)?'Forbidden':$value);
				break;
			case 404:
				$send = $http.' 404 '.(empty($value)?'Not Found':$value);
				break;
			case 405:
				$send = $http.' 405 '.(empty($value)?'Method Not Allowed':$value);
				break;
			case 406:
				$send = $http.' 406 '.(empty($value)?'Not Acceptable':$value);
				break;
			case 407:
				$send = $http.' 407 '.(empty($value)?'Proxy Authentication Required':$value);
				break;
			case 408:
				$send = $http.' 408 '.(empty($value)?'Request Timeout':$value);
				break;
			case 409:
				$send = $http.' 409 '.(empty($value)?'Conflict':$value);
				break;
			case 410:
				$send = $http.' 410 '.(empty($value)?'Gone':$value);
				break;
			case 411:
				$send = $http.' 411 '.(empty($value)?'Length Required':$value);
				break;
			case 412:
				$send = $http.' 412 '.(empty($value)?'Precondition Failed':$value);
				break;
			case 413:
				$send = $http.' 413 '.(empty($value)?'Request Entity Too Large':$value);
				break;
			case 414:
				$send = $http.' 414 '.(empty($value)?'Request-URI Too Long':$value);
				break;
			case 415:
				$send = $http.' 415 '.(empty($value)?'Unsupported Media Type':$value);
				break;
			case 416:
				$send = $http.' 416 '.(empty($value)?'Requested Range Not Satisfiable':$value);
				break;
			case 417:
				$send = $http.' 417 '.(empty($value)?'Expectation Failed':$value);
				break;
			case 500:
				$send = $http.' 500 '.(empty($value)?'Internal Server Error':$value);
				break;
			case 501:
				$send = $http.' 501 '.(empty($value)?'Not Implemented':$value);
				break;
			case 502:
				$send = $http.' 502 '.(empty($value)?'Bad Gateway':$value);
				break;
			case 503:
				$send = $http.' 503 '.(empty($value)?'Service Unavailable':$value);
				break;
			case 504:
				$send = $http.' 504 '.(empty($value)?'Gateway Timeout':$value);
				break;
			case 505:
				$send = $http.' 505 '.(empty($value)?'HTTP Version Not Supported':$value);
				break;

			// get and post
			// @todo finish this
			case 'GET':
			case 'POST':
				$send = $header.' '.$value;
				// break out of the header switch
				break;

			// any custom headers just set as is
			default:
				$send = $header.': '.$value;
				// break out of the header switch
				break;
		}

		// send the header
		header($send, $replace);
	}

	/**
	 * Sets the active header list to that of apache (if exsits) or the $_ENV variable
	 *
	 * @since version 1.0.0
	 */
	public function setActiveHeadersApacheOrEnv()
	{
		// use the headers the browser received

		// if php isn't running as a apache mod use the $_ENV info isntead
		if (function_exists('apache_request_headers'))
		{
			$response = apache_request_headers();
		}
		else
		{
			$response = $_ENV;
		}

		// turn the array into a string
		$data = '';
		foreach ($response as $key => $val)
		{
			$data .= $key.': '.$val.self::CRLF;
		}

		$this->parse($data);
	}

	/**
	 * Class constructor
	 * 
	 * @since version 1.0.0
	 */
	public function __construct()
	{
		$this->httpVersion = '1.1';
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
			default:
				// by default get the header
				$return = $this->get($var, true);
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
			default:
				// by default set the header
				$this->set($var, $value);
		}
	}

	// private

	/**
	 * extracted from {@link HttpHeaders::parse}
	 * @var array
	 * @since version 1.0.0
	 */
	private $_headers;
}

?>
