<?php
/**
 * General Functions
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Extends array_flip()
 * Flips an array with keys = values
 *
 * $uniqueKeys
 * This will prevent the values from overwritting each other within the keys
 * instead the item will become an array and will have multiple values
 *
 * $loseValues
 * Say you want to flip an array, but you don't care about the values going
 * becoming the keys, set this to true and the keys become the values, but
 * the values DO NOT become the keys
 *
 * NOTE: $uniqueKeys and $loseValues DO NOT work together, $uniqueKeys if true
 * will take presedence over $loseValues
 *
 * @param array $array
 * @param bool $loseValues
 * @return array
 * @see array_flip()
 * @since version 1.0.0
 * @todo add more features
 */
function array_flip_ext($array, $uniqueKeys = true, $loseValues = false)
{
	if (!is_array($array))
	{
		return false;
	}

	$newarray = array();
	if ($uniqueKeys === true)
	{
		foreach ($array as $key => $val)
		{
			if (!empty($newarray[$val]) && is_array($newarray[$val]))
			{
				$newarray[$val][] = $key;
			}
			elseif (!empty($newarray[$val]) && is_string($newarray[$val]))
			{
				$newarray[$val] = array($newarray[$val], $key);
			}
			else
			{
				// default just add the key value
				$newarray[$val] = $key;
			}
		}
		$array = $newarray;
	}
	elseif ($loseValues === true)
	{
		foreach ($array as $key => $val)
		{
			$newarray[] = $key;
		}
		$array = $newarray;
	}
	else
	{
		$array = array_flip($array);
	}

	return $array;
}

/**
 * Extends array_search()
 * - Search multidimentional arrays
 * - Case Sensitive or Insensitive
 * - Search for an Exact match or if needle is anywhere in it
 * - Search within array keys
 * - Return ALL results or just the first one
 * Returns a comma seperated list of keys
 * No strict check
 * 
 * @param mixed $needle
 * @param array $haystack
 * @param bool $searchKey [optional]
 * @param bool $returnList [optional]
 * @param bool $exactMatch [optional]
 * @param bool $caseSensitive [optional]
 * @param string $seperator [optional]
 * @return mixed
 * @see array_search()
 * @see in_array()
 * @see in_array_ext()
 * @since version 1.0.0
 */
function array_search_ext($needle, $haystack, $searchKey = false,
	$returnList = false, $exactMatch = true, $caseSensitive = false,
	$seperator = ',')
{
	$list = array();

	// if the needle is an array recuse though all elements
	if (is_array($needle))
	{
		foreach ($needle as $n)
		{
			// recuse through this function again
			$return = array_search_ext($n, $haystack, $searchKey, $returnList,
				$exactMatch, $caseSensitive);
			if ($returnList === true)
			{
				// only set if return is an aray otherwise dismise it
				if (!empty($return))
				{
					$list[$n] = $return;
				}
			}
			else
			{
				if ($return !== false)
				{
					return $return;
				}
			}
		}
	}
	else
	{
		// no need to search here if needle is an array
		// because we'll loop though each needle as if it wasn't an array
		// haystack MUST be an array
		if (is_array($haystack))
		{
			// haystack IS an array loop though all entries
			foreach ($haystack as $key => $index)
			{
				if (is_array($index))
				{
					// index is an array recuse it by calling ourself
					$return = array_search_ext($needle, $index, $searchKey,
						$returnList, $exactMatch, $caseSensitive);

					// check for a valid return
					if ($return !== false)
					{
						if ($returnList === true)
						{
							// check for an array being returned
							if (is_array($return))
							{
								// we need to recreate the return list
								// and add the current key to the front
								foreach ($return as $val)
								{
									$list[] = $key.$seperator.$val;
								}
							}
							else
							{
								// not an array just addto the list
								$list[] = $key.$seperator.$return;
							}
						}
						else
						{
							return $key.$seperator.$return;
						}
					}
					// check the array key to see if it's in it
					// for this one search key is at the top
					// because index is an array so we only could search key
					if ($searchKey === true)
					{
						$continue = false;
						if ($exactMatch === true)
						{
							// normal string comparison
							$comparison = ($caseSensitive === true)?'strcmp':'strcasecmp';
							// comparison is done twice because of how it's checked
							if ($comparison($key, $needle) === 0)
							{
								$continue = true;
							}
						}
						else
						{
							// check if needle is 'within' the string
							$comparison = ($caseSensitive === true)?'strpos':'stripos';
							// comparison is done twice because of how it's checked
							if ($comparison($key, $needle) !== false)
							{
								$continue = true;
							}
						}
						if ($continue === true)
						{
							if ($returnList === true)
							{
								// add to list
								$list[] = $key;
							}
							else
							{
								// no list just return
								return $key;
							}
						}
					}
				}
				else
				{
					// not an array check normally
					$continue = false;
					// check if we want exact matching or not
					if ($exactMatch === true)
					{
						// normal string comparison
						$comparison = ($caseSensitive === true)?'strcmp':'strcasecmp';
						// comparison is done twice because of how it's checked
						if ($comparison($index, $needle) === 0 ||
							($searchKey === true && $comparison($key, $needle) === 0)
						)
						{
							$continue = true;
						}
					}
					else
					{
						// check if needle is 'within' the string
						$comparison = ($caseSensitive === true)?'strpos':'stripos';
						// comparison is done twice because of how it's checked
						if ($comparison($index, $needle) !== false ||
							($searchKey === true && $comparison($key, $needle) !== false)
						)
						{
							$continue = true;
						}
					}

					if ($continue === true)
					{
						if ($returnList === true)
						{
							// add to list
							$list[] = $key;
						}
						else
						{
							// no list just return
							return $key;
						}
					}
				}
			} // end foreach haystack
		}
		else
		{
			// haystack not an array
			return false;
		}
	}

	// return the list
	if ($returnList === true && !empty($list))
	{
		return $list;
	}
	// no results
	return false;
}

/**
 * Extends array_walk_recursive()
 * Apply a user defined function recursively to every member of an array
 * - Allows the key of an array to be used
 * 
 * @param array $array
 * @param string userFunction
 * @param mixed $userData [optional]
 * @see array_walk_recursive()
 * @since version 1.0.0
 */
function array_walk_recursive_ext(&$input, $userFunction, $userData = null)
{
	foreach ($input as $key => $value)
	{
		if (is_array($value))
		{
			/*
				call the user function and pass all the arguments
				this is what array_walk_recursive() is missing
				$value will be an array but we can still use $key
				and perhaps you want to do something with each array
			*/
			call_user_func_array($userFunction,
				array(
					$value, $key, $userData
				)
			);
			// recuse though the next level
			array_walk_recursive_ext($value, $userFunction, $userData);
		}
		else
		{
			// call the user function and pass all the arguments
			call_user_func_array($userFunction,
				array(
					$value, $key, $userData
				)
			);
		}
	}
}

/**
 * Verifies that the client has cookies enabled
 *
 * Requires Sessions to be active
 *
 * By default it'll send it to itself, you can specify a page/path to
 * redirect it to instead
 *
 * @param string comand [optional]
 * @param string pageLocation [optional]
 * @param string sessionUserName [optional]
 * @since version 1.0.0
 */
function cookieCheck($command = 'send', $pageLocation = '',
	$sessionUserName = 'username')
{
	if (!isset($_SESSION))
	{
		trigger_error('Sessions not started', E_USER_WARNING);
	}

	switch ($command)
	{
		case 'check':
			if (isset($_SESSION['cookieCheck']) && $_SESSION['cookieCheck'] == true)
			{
				// set the global user if it exists
				if (!empty($_SERVER['REMOTE_USER']))
				{
					$_SESSION[$sessionUserName] = $_SERVER['REMOTE_USER'];
				}

				if (!empty($_GET['u']))
				{
					$url = rawurldecode($_GET['u']);
					// parase the url
				}
				else
				{
					$url = 'http://'.$_SERVER['HTTP_HOST'].'/';
					if ((isset($_SERVER['HTTPS']) &&
						$_SERVER['HTTPS'] == 'on')
						||
						(isset($_SERVER['SERVER_PORT']) &&
						$_SERVER['SERVER_PORT'] == 443))
					{
						$url = 'https://'.$_SERVER['HTTP_HOST'].'/';
					}
				}
				header('Location: '.$url);
				exit();
			}
			break;
		case 'send':
		default:
			if (!isset($_SESSION['cookieCheck']) || $_SESSION['cookieCheck'] == false)
			{
				// set the cookie to check
				$_SESSION['cookieCheck'] = true;

				// if no pageLocation is given default to PHP_SELF
				if (empty($pageLocation))
				{
					$pageLocation = $_SERVER['PHP_SELF'];
				}

				$url = 'http://'.$_SERVER['HTTP_HOST'];
				if ((isset($_SERVER['HTTPS']) &&
					$_SERVER['HTTPS'] == 'on')
					||
					(isset($_SERVER['SERVER_PORT']) &&
					$_SERVER['SERVER_PORT'] == 443))
				{
					$url = 'https://'.$_SERVER['HTTP_HOST'];
				}
				$url .= $pageLocation.'?u='.
					rawurlencode($url.$_SERVER['REQUEST_URI']);
				header('Location: '.$url);
				exit();
			}
	}
}

/**
 * Gets the content type based on the given extension
 * defaults to application/octet-stream
 * 
 * @param string $type [optional]
 * @return string
 * @since version 1.0.0
 */
function getContentType($extension)
{
	// default content type if nothing is found
	$contentType = 'application/octet-stream';

	if (isset($GLOBALS['gContentTypes']))
	{
		// search the list
		$list = array_search_ext($extension, $GLOBALS['gContentTypes'],
			true, true, true);

		// if found split it up to a valid Content-Type
		if ($list !== false)
		{
			$tmp = explode(',', $list[0]);
			$tmp0 = (isset($tmp[0]))?$tmp[0]:'';
			$tmp1 = (isset($tmp[1]))?$tmp[1]:'';
			$contentType = $tmp0.'/'.$tmp1;
		}
	}

	// return the results
	return $contentType;
}

/**
 * Gets the English ordinal suffix for any number
 * 
 * @param integer $number
 * @return string
 * @since version 1.0.0
 */
function getEnglishOrdinalSuffix($number)
{
	$number = intval($number);
	if ($number % 100 > 10 && $number % 100 < 14)
	{
		$suffix = 'th';
	}
	else
	{
		switch($number % 10)
		{
			case 0:
				$suffix = "th";
				break;
			case 1:
				$suffix = "st";
				break;
			case 2:
				$suffix = "nd";
				break;
			case 3:
				$suffix = "rd";
				break;
			default:
				$suffix = "th";
				break;
		}
	}
	return $suffix;
}

/**
 * Gets the extension of a filename
 * 
 * @param string $filename
 * @return string
 * @since version 1.0.0
 */
function getFileExtension($filename)
{
	$start = strrpos($filename, '.');
	if ($start !== false)
	{
		return substr($filename, $start+1);
	}
	return '';
}

/**
 * Gets the users IP, Host and Proxy
 * Proxy will be retrived if detected
 * An array is returned index's of
 * "IP" "Host" "Proxy"
 * 
 * @return array "IP" "Host" "Proxy"
 * @since version 1.0.0
 */
function getIP()
{
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?/',
			$_SERVER['HTTP_X_FORWARDED_FOR']))
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
		$proxy = null;
	}

	return array(
		'IP' => $ip,
		'Host' => $host,
		'Proxy' => $proxy
	);
}

/**
 * Returns the relative path to the home dir
 * (i.e ../../../)
 * If you know the depth give it
 * (i.e http://localhost/onedir/twodir/thisisyoursitedir)
 * 
 * @param integer $depth [optional]
 * @return string
 * @since version 1.0.0
 */
function getRelativePath($depth = 0)
{
	$path = explode('/', $_SERVER['SCRIPT_NAME']);
	$count = count($path)-1;
	$max = ($count - 1) - $depth;
	if ($max <= 0)
	{
		return './';
	}
	return str_repeat('../', $max);
}

/**
 * Implodes by keys instead of values
 * NOTE: This function is NOT backwards compatible
 * 
 * @param string $glue
 * @param array $pieces
 * @param bool $returnValue [optional]
 * @return mixed
 * @since version 1.0.0
 */
function implode_key($glue, $pieces, $returnValue = false)
{
	if (!is_array($pieces) || !is_string($glue))
	{
		return false;
	}
	$return = '';
	foreach ($pieces as $key => $val)
	{
		if (is_array($val))
		{
			$return .= $key.$glue;
			$return .= implode_key($glue, $pieces[$key], $returnValue).$glue;
		}
		else
		{
			$return .= $key.$glue;
			if ($returnValue === true)
			{
				$return .= $val.$glue;
			}
		}
	}
	return substr($return, 0, strlen($return) - strlen($glue));
}

/**
 * Implodes on a multidimentional array
 * 
 * @param string $glue
 * @param array $pieces
 * @return string
 * @since version 1.0.0
 */
function implode_multi($glue, $pieces)
{
	if (!is_array($pieces) || !is_string($glue))
	{
		return false;
	}
	$return = '';
	foreach ($pieces as $val)
	{
		if (is_array($val))
		{
			$return = implode_multi($glue, $val).$glue;
		}
		else
		{
			$return .= $val;
		}
	}
	return substr($return, 0, strlen($return) - strlen($glue));
}

/**
 * Extends in_array()
 * - Search multidimentional arrays
 * - Case Sensitive or Insensitive
 * - Search for an Exact match or if needle is anywhere in it
 * - Search within array keys
 * No strict check
 * 
 * Returns false on error
 * 
 * @param mixed $needle
 * @param array $haystack
 * @param bool $searchKey [optional]
 * @param bool $exactMatch [optional]
 * @param bool $caseSensitive [optional]
 * @return bool
 * @see array_search()
 * @see array_search_ext()
 * @see in_array()
 * @since version 1.0.0
 */
function in_array_ext($needle, $haystack, $searchKey = false,
	$exactMatch = true, $caseSensitive = false)
{
	if (array_search_ext($needle, $haystack, $searchKey, false, $exactMatch,
		$caseSensitive) === false)
	{
		return false;
	}
	return true;
}

/**
 * Each element of an array is checked if it's within the string
 * 
 * Returns false on error
 * 
 * @param array $needle
 * @param string $haystack
 * @param bool $searchKey [optional]
 * @param bool $returnNeedleKeys [optional]
 * @return bool
 * @since version 1.0.0
 */
function in_string($needle, $haystack, $searchKey = false,
	$returnNeedleKeys = false)
{
	if (is_array($haystack))
	{
		return false;
	}
	$returnList = array();
	if (is_array($needle))
	{
		foreach ($needle as $needleKey => $value)
		{
			if (stristr($haystack, $value) !== false)
			{
				if ($returnNeedleKeys === true)
				{
					$returnList[] = $needleKey;
				}
				else
				{
					return true;
				}
			}
			if ($searchKey === true)
			{
				if (stristr($haystack, $needleKey) !== false)
				{
					if ($returnNeedleKeys === true)
					{
						$returnList[] = $needleKey;
					}
					else
					{
						return true;
					}
				}
			}
		}
	}
	else
	{
		return false;
	}
	if ($returnNeedleKeys === true)
	{
		return $returnList;
	}
	return false;
}

/**
 * rawurlencode's a string by spliting on a backslash FIRST then encoding
 * each peice then joining them with a backslash
 *
 * @param string $string
 * @return string
 * @since version 1.0.0
 */
function rawurlencodeSpecial($string)
{
	$np = '';
	$ss = strpos($string, '/');
	$es = strrpos($string, '/');
	$tmp = explode('/', trim($string, '/'));

	// loop through each item and encode it if it's not empty
	foreach ($tmp as $item)
	{
		if (!empty($item))
		{
			$np .= rawurlencode($item) . '/';
		}
	}

	// had a starting slash add it back
	if ($ss === 0)
	{
		$np = '/'.$np;
	}

	// did NOT have an ending slash
	if (($es + 1) != strlen($string))
	{
		// remove it
		$np = rtrim($np, '/');
	}

	return $np;
}

/**
 * Redirect to the given url
 * Using PHP's headers or HTML's meta tag
 * $url MUST be a FULL http link i.e http://mysite.com/mypage.whatever
 * 
 * types are: php, html (default php)
 * 
 * return: string if type is html and void if type is php
 * 
 * @param string $url
 * @param integer $delay
 * @param string $type
 * @return mixed
 * @since version 1.0.0
 */
function redirect($url, $delay = 0, $type = 'php', $code = 301)
{
	switch (strtolower($type))
	{
		case 'html':
			// return the full meta tag
			return '<meta http-equiv="refresh" content="'.$delay.'; URL='.$url.'" />';
			break;
		case 'php':
		default:
			if ($delay > 0)
			{
				// if a delay is given, pause the script for that amount of time
				// can't see how this is uesful but someone might want it
				sleep($delay);
			}
			switch ($code)
			{
				case 302:
					$codeValue = '302 Found';
					break;
				case 303:
					$codeValue = '303 See Other';
					break;
				case 307:
					$codeValue = '307 Temporary Redirect';
					break;
				case 301:
				default:
					$codeValue = '301 Moved Permanently';
			}
			header('HTTP/1.1 '.$codeValue);
			header('Location: '.$url);
			header('Content-Type: text/html; charset=iso-8859-1');
			header('Connection: close');
			exit();
	}
	return '';
}

/**
 * Verifies that the submission came from the current site
 * 
 * @param string $checkUrl
 * @param integer sslPort [optional]
 * @return bool
 * @since version 1.0.0
 */
function refererCheck($checkUrl, $sslPort = 443)
{
	$check = '';
	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
		(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == $sslPort)
	)
	{
		$check .= 'https://';
	}
	else
	{
		$check .= 'http://';
	}

	// verify needed variables exist
	if (!isset($_SERVER['SERVER_NAME']) || !isset($_SERVER['HTTP_REFERER']))
	{
		return false;
	}

	$check .= $_SERVER['SERVER_NAME'].$checkUrl;

	// preform check
	if ($check == $_SERVER['HTTP_REFERER'])
	{
		return true;
	}
	return false;
}

/**
 * Replaces all lines endings
 * \r\n \n\r \n \r
 * default replace with a space
 * 
 * @param string $data
 * @param string $replacement [optional]
 * @return string
 * @since version 1.0.0
 */
function replaceNewLines($data, $replacement = ' ')
{
	return str_replace(array("\r\n", "\n\r", "\n", "\r"), $replacement, $data);
}

/**
 * Decode text that was safe encoded
 * The key for any unsafe and unsafe replacement passed in must be
 * the replacement that you want if that char is found and wasn't
 * encoded with safeEncode()
 *
 * @param string $text
 * @param array $uUnsafe [optional]
 * @param array $uUnsafeReplace [optional]
 * @param array $uBypass [optional]
 * @return string
 * @since version 1.0.0
 */
function safeDecode($text, $uUnsafe = array(), $uUnsafeReplace = array(),
	$uBypass = array())
{
	// any char that can be used to do a sql inject attack are replaced
	$unsafeChar = array(
		'&#061;' => '=', '&#040;' => '(', '&#041;' => ')',
		'&lt;' => '<', '&gt;' => '>', '&#039;' => "'",
		'&quot;' => '"',
		// fix for apache bug with url rewrite
		// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
		'/' => '%2F', '&' => '%26'
	);
	$unsafe = array_merge($unsafeChar, $uUnsafe);
	$unsafeCharReplace = array(
		'&#061;' => '+=+', '&#040;' => '-(-', '&#041;' => '$)$',
		'&lt;' => '!<!', '&gt;' => '@>@', '&#039;' => "*'*",
		'&quot;' => '?"?',
		// fix for apache bug with url rewrite
		// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
		'/' => '%2F', '&' => '%26'
	);
	$unsafeReplace = array_merge($unsafeCharReplace, $uUnsafeReplace);

	if (count($unsafe) != count($unsafeReplace))
	{
		return 'invalid number of elements';
	}

	// if the user wants to bypass and allow a certain char let them
	$results = array_search_ext($uBypass, $unsafe, false, true);

	if ($results !== false)
	{
		// if there are matches
		foreach($results as $array)
		{
			foreach ($array as $key => $unsafekey)
			{
				unset($unsafe[$unsafekey]);
				unset($unsafeReplace[$unsafekey]);
			}
		}
	}

	// rawurldecode and stripslashes
	$text = stripslashes(rawurldecode($text));
	// replace any unsafe char that aren't encoded
	// with there replacement entity
	$tmpReplace = array_flip($unsafeReplace);
	$newtext = str_replace($unsafe, $tmpReplace, $text);
	// replace the vars
	foreach ($unsafeReplace as $key => $value)
	{
		$c1 = substr($value, 0, 1);
		$c2 = substr($value, -1);
		$tmpSearch[$key] = $c1.$key.$c2;
	}
	$newtext = str_replace($tmpSearch, $unsafe, $newtext);
	return $newtext;
}

/**
 * Encodes text and makes it safe from injection attacks
 * The key for any unsafe and unsafe replacement passed in must be
 * the replacement that you want if that char is found and wasn't
 * encoded with safeEncode()
 *
 * @param string $text
 * @param array $uUnsafe [optional]
 * @param array $uUnsafeReplace [optional]
 * @param array $uBypass [optional]
 * @return string
 * @since version 1.0.0
 */
function safeEncode($text, $uUnsafe = array(), $uUnsafeReplace = array(),
	$uBypass = array())
{
	if (!is_string($text))
	{
		trigger_error('String expected '.gettype($text).' received<br>'.
			var_dump($text), E_USER_ERROR);
	}

	// any char that can be used to do a sql inject attack are replaced
	$unsafeChar = array(
		'&#061;' => '=', '&#040;' => '(', '&#041;' => ')',
		'&lt;' => '<', '&gt;' => '>', '&#039;' => "'",
		'&quot;' => '"',
		// fix for apache bug with url rewrite
		// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
		'/' => '/', '&' => '&'
	);
	$unsafe = array_merge($unsafeChar, $uUnsafe);
	$unsafeCharReplace = array(
		'&#061;' => '+=+', '&#040;' => '-(-', '&#041;' => '$)$',
		'&lt;' => '!<!', '&gt;' => '@>@', '&#039;' => "*'*",
		'&quot;' => '?"?',
		// fix for apache bug with url rewrite
		// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
		'/' => '%2F', '&' => '%26'
	);
	$unsafeReplace = array_merge($unsafeCharReplace, $uUnsafeReplace);

	if (count($unsafe) != count($unsafeReplace))
	{
		return 'invalid number of elements';
	}

	// if the user wants to bypass and allow a certain char let them
	$results = array_search_ext($uBypass, $unsafe, false, true);
	if ($results !== false)
	{
		// TODO: fix this
		// if there are matches
		foreach($results as $unsafekey)
		{
			unset($unsafe[$unsafekey]);
			unset($unsafeReplace[$unsafekey]);
		}
	}

	// replace the vars
	$newtext = str_replace($unsafe, $unsafeReplace, $text);
	// rawurlencode the new string
	$newtext = rawurlencode($newtext);
	return $newtext;
}

/**
 * Trims all elements of an array
 * 
 * @param array $array
 * @param stirng charlist [optional]
 * @return array
 * @see trim()
 * @since version 1.0.0
 */
function trim_array($array, $charlist = '')
{
	$newArray = array();
	foreach ($array as $key => $value)
	{
		if ($charlist == '')
		{
			// use the defaults to trim with and not an empty charlist
			$newArray[$key] = trim($value);
		}
		else
		{
			$newArray[$key] = trim($value, $charlist);
		}
	}
	return $newArray;
}

?>
