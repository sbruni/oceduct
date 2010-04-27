<?php
/**
 * HTTP Cache Class
 *
 * Easy caching of files/images etc...
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package HttpCache
 * @see HttpHeaders
 * @see MainException
 * @access private
 */
class HttpCache
{
	/**
	 * Send caching headers
	 *
	 * $expires and $lastModified MUST be in the format
	 * gmdate('D, j M Y H:i:s')
	 *
	 * @param integer $maxage [optional] seconds until expiry
	 * @param string $expires [optional] GMT date untill expiry
	 * @param string $lastModified [optional] last modified date (in GMT)
	 * @since version 1.0.0
	 */
	public function set($maxAge = 0, $expires = '', $lastModified = '', $private = false)
	{
		try
		{
			// clear all present caching headers
			$this->_headers->clear(
				array(
					'Cache-Control',
					'Expires',
					'Pragma',
					'Last-Modified'
				)
			);

			// public or private
			$public = 'public';
			if ($private === true)
			{
				$public = 'private';
			}

			// Cache-Control
			// don't add max-age or min-age etc..
			$this->_headers->set('Cache-Control', $public);

			/**
				RFC 2616
				Note: if a response includes a Cache-Control field with the
				max-age directive (see section 14.9.3), that directive
				overrides the Expires field.

				send the expires ALSO incase we are dealing with HTTP 1.0
				the above WILL overwrite for HTTP 1.1
			*/
			// use Cache-Control if maxage is more then 0
			if ($maxAge > 0)
			{
				// Cache-Control
				$this->_headers->set('Cache-Control',
					$public.', max-age='.$maxAge.', s-maxage='.$maxAge);

				// set expires to now + maxage if no expiry date is given
				if (empty($expires))
				{
					$expires = gmdate('D, j M Y H:i:s', time() + $maxAge);
				}
				$this->_headers->set('Expires', $expires);
			}
			else
			{
				// if no expiry date given default to expire in one week
				if (empty($expires))
				{
					$expires = gmdate('D, j M Y H:i:s',
						(time()+HttpHeaders::S_WEEK));
				}
				$this->_headers->set('Expires', $expires);
			}

			// only set if one is given
			if (!empty($lastModified))
			{
				$this->_headers->set('Last-Modified', $lastModified.' GMT');
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Send no caching headers
	 * 
	 * @since version 1.0.0
	 */
	public function setNone()
	{
		try
		{
			// clear all present caching headers
			$this->_headers->clear(
				array(
					'Cache-Control',
					'Expires',
					'Pragma',
					'Last-Modified'
				)
			);

			$cacheControl = '';
			// IE bug (only seen so far in IE 6.0)
			// not tested in other versions
			if (!strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
			{
				/*
					if no-store or no-cache are sent IE errors (sometimes)
					also if the user clicks open instead of save it does not work
					and (sometimes) stores indefinitly instead of clearing
				*/
				$cacheControl .= 'no-store, no-cache, ';
			}
			$cacheControl .= 'must-revalidate';

			// set the Cache-Control header
			$this->_headers->set('Cache-Control',
				'private, post-check=0, pre-check=0, max-age=0, '.$cacheControl);

			// set expiry date (date in past)
			$this->_headers->set('Expires',
				gmdate('D, d M Y H:i:s', (time()-HttpHeaders::S_WEEK)));

			// set the last modified date (default to today's date)
			$this->_headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
		}
		catch (Exception $e)
		{
			throw $e;
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
		// check for HttpHeaders
		if (in_array('HttpHeaders', $declaredClasses) === false)
		{
			throw new MainException('HttpHeaders: class not found',
				MainException::INVALID_PARAM);
		}

		try
		{
			// ini HttpHeaders class
			$this->_headers = new HttpHeaders();
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	// private

	/**
	 * @var object HTTP headers object
	 * 
	 * @since version 1.0.0
	 */
	private $_headers;
}

?>
