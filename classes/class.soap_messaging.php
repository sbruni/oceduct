<?php
/**
 * Soap (Messaging) Class
 * Message SOAP
 *
 * Envelope is initialized on construct
 *
 * To request something simply use the send() method
 *
 * $soap->debug = true; when true this will return all reponses to the user and
 * it will NOT throw an exception on a non 200, this makes it easiser
 * to debug a connection instead of having to do it manually within the class
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package SoapMessaging
 */
class SoapMessaging
{
	/**
	 * http requires \r\n not \n
	 * default line ending to use on the stream
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const CRLF					= "\r\n";

	/**
	 * Newline
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const LF					= "\n";

	/**
	 * SOAP ENVELOPE namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_SOAP_ENV			= 'http://schemas.xmlsoap.org/soap/envelope/';

	/**
	 * SOAP ENCODING namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_SOAP_ENC			= 'http://schemas.xmlsoap.org/soap/encoding/';

	/**
	 * XML SCHEMA XSI namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_XML_SCHEMA_XSI			= 'http://www.w3.org/2001/XMLSchema-instance';

	/**
	 * XML SCHEMA XSD namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_XML_SCHEMA_XSD			= 'http://www.w3.org/2001/XMLSchema';

	/**
	 * XML namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_XML				= 'http://www.w3.org/XML/1998/namespace';

	/**
	 * XHTML namespace
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const NS_XHTML				= 'http://www.w3.org/1999/xhtml';

	/**
	 * Returns the first child element of $el
	 *
	 * @param object $el
	 * @return string
	 * @since version 1.0.0
	 */
	static public function getFirstElement($el)
	{
		$children = $el->childNodes;
		for ($i = 0; $i < $children->length; $i++)
		{
			$ch = $children->item($i);
			if ($ch->nodeType == XML_ELEMENT_NODE)
			{
				return $ch;
			}
		}
		return false;
	}

	/**
	 * Returns first child element of $el that has the name |name|
	 * Returns false on faliure
	 *
	 * @param object $el
	 * @param string $name
	 * @return mixed
	 * @since version 1.0.0
	 */
	static public function getChildElement($el, $name)
	{
		$children = $el->childNodes;
		for ($i = 0; $i < $children->length; $i++)
		{
			$ch = $children->item($i);
			if ($ch->nodeType == XML_ELEMENT_NODE && $ch->nodeName == $name)
			{
				return $ch;
			}
		}
		return false;
	}

	/**
	 * Gets the local name (without prefix) of $node
	 *
	 * @param object $node
	 * @return string
	 * @since version 1.0.0
	 */
	static public function getLocalName($node)
	{
		$name = $node->nodeName;
		return substr($name, strrpos($name, ':')+1);
	}

	/**
	 * Send a SOAP request
	 *
	 * A valid responses MAY return an empty string
	 * Use the $debug to verify that your server is responding correctly
	 *
	 * $postPath the path that the request is to be posted too
	 *
	 * $data the data (string) that will be sent within the envelope
	 *
	 * $headers are any additional headers to be sent with the request
	 * $headers is an array, the key is the header name, and the value
	 * is the header value to be sent.
	 *
	 * @param string $postPath
	 * @param string $data
	 * @param array $headers[optional]
	 * @return string
	 * @since version 1.0.0
	 */
	public function send($postPath, $data, $headers = array())
	{
		if (!is_string($data))
		{
			throw new MainException(gettype($data),
				MainException::TYPE_STRING);
		}

		if (!is_array($headers))
		{
			throw new MainException(gettype($headers),
				MainException::TYPE_ARRAY);
		}

		// add everything to the $envelope
		$envelope = $this->_startEnvelope.$data.$this->_endEnvelope;

		$additionalHeaders = '';
		foreach ($headers as $key => $value)
		{
			if (!empty($value))
			{
				$additionalHeaders .= trim($key).': '.trim($value).self::CRLF;
			}
		}

		try
		{
			$request = 'POST '.$postPath.' HTTP/1.0'.self::CRLF.
				'Host: '.$this->_stream->host.self::CRLF.
				'Content-type: text/xml;charset=utf-8'.self::CRLF.
				'Content-length: '.strlen($envelope).self::CRLF.
				'Connection: close'.self::CRLF.
				$additionalHeaders.self::CRLF.
				$envelope;

			// connect new each time
			$this->_stream->connect();

			// clear the stream before sending a new request
			$this->_stream->clear();

			$this->_stream->write($request);

			// get the headers
			$rawHeaders = $this->_stream->readUntil(self::CRLF);
			$this->_headers->parse($rawHeaders);

			// read the response
			$response = $this->_stream->read();

			// clear the stream after reading the responses
			$this->_stream->clear();
			$this->_stream->disconnect();

			// get the http header
			$http = $this->_headers->get('HTTP', false);

			$this->_responseHeaders = $rawHeaders;

			/**
			 * Anything other then 200 we return ALL DATA from the stream
			 * and let the calling script handle the errors.
			 */
			if (!empty($http['code']) && $http['code'] != 200 && $this->_debug !== true)
			{
				return $response;
			}
		}
		catch (Exception $e)
		{
			trigger_error($e->__toString(), E_USER_ERROR);
		}

		// debug mode, this is for debugging a connection
		// always return string of everything
		if ($this->_debug === true)
		{
			return 'Request sent was: '.self::CRLF.$request.self::CRLF.
				'-------------------------'.self::CRLF.
				'Headers returned: '.self::CRLF.$rawHeaders.self::CRLF.
				'-------------------------'.self::CRLF.
				'Message returned: '.self::CRLF.$response.self::CRLF;
		}

		// returns raw data
		return $this->_stripEnvelope($response);
	}

	/**
	 * Class constructor
	 *
	 * @param string $host
	 * @param integer $port
	 * @param array $envelopeAttributes [optional]
	 * @since version 1.0.0
	 */
	public function __construct($host, $port, $envelopeAttributes = array())
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();
		// check for Stream
		if (in_array('Stream', $declaredClasses) === false)
		{
			throw new MainException('Stream: class not found',
				MainException::INVALID_PARAM);
		}

		if (!is_string($host))
		{
			throw new MainException(gettype($host),
				MainException::TYPE_STRING);
		}

		if (!settype($port, 'integer'))
		{
			throw new MainException(gettype($port),
				MainException::TYPE_INTEGER);
		}

		try
		{
			// ini HTTP Headers class
			$this->_headers = new HttpHeaders();

			// Initialize Stream class
			$this->_stream = new Stream();
			// set host and port, to reset a new instance must be created
			$this->_stream->host = $host;
			$this->_stream->port = $port;
			$this->_stream->timeout = 600;
		}
		catch (Exception $e)
		{
			throw $e;
		}

		// ini envelope
		$this->_iniEnvelope($envelopeAttributes);

		// ini others
		$this->_xmlVersion = '1.0';
		$this->_xmlEncoding = 'UTF-8';
		$this->_debug = false;
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
			case 'debug':
				$return = $this->_debug;
				break;
			case 'responseHeaders':
				$return = $this->_responseHeaders;
				break;
			case 'xmlEncoding':
				$return = $this->_xmlEncoding;
				break;
			case 'xmlVersion':
				$return = $this->_xmlVersion;
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
			case 'debug':
				if ($value === true)
				{
					$this->_debug = true;
				}
				break;
			case 'xmlEncoding':
				if (!is_string($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}
				$this->_xmlEncoding = $value;
				break;
			case 'xmlVersion':
				if (!is_string($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}
				$this->_xmlVersion = $value;
				break;
			default:
				throw new MainException('Not a vaild option to set: '.
					$var.' = '.$value, MainException::INVALID_PARAM);
		}
	}

	// private

	/**
	 * Debug mode, prints out send and recieve commands
	 * @var bool
	 * @since version 1.0.0
	 */
	private $_debug;

	/**
	 * End of envelope
	 * @var string
	 * @since version 1.0.0
	 */
	private $_endEnvelope;

	/**
	 * HTTP Headers class
	 * @var object
	 * @since version 1.0.0
	 */
	private $_headers;

	/**
	 * HTTP Resonse headers regardless of errors
	 * @var string
	 * @since 1.0.0
	 */
	private $_responseHeaders;

	/**
	 * Start of envelope
	 * @var string
	 * @since version 1.0.0
	 */
	private $_startEnvelope;

	/**
	 * IO object class
	 * @var object
	 * @since version 1.0.0
	 */
	private $_stream;

	/**
	 * XML Encoding
	 * defaults to UTF-8
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_xmlEncoding;

	/**
	 * XML version
	 * defaults to 1.0
	 *
	 * @var string
	 * @since version 1.0.0
	 */
	private $_xmlVersion;

	/**
	 * Initialize the envelope
	 *
	 * Creates the Envelope strings and sets them within class variables
	 * Adds any additional attributes to the envelope
	 *
	 * @param array $envelopeAttributes [optional]
	 * @since version 1.0.0
	 */
	private function _iniEnvelope($envelopeAttributes = array())
	{
		if (!is_array($envelopeAttributes))
		{
			throw new MainException(gettype($envelopeAttributes),
				MainException::TYPE_ARRAY);
		}

		$envAttrib = '';
		// additional attributes
		foreach ($envelopeAttributes as $val)
		{
			$envAttrib .= "\t".$val.self::LF;
		}

		// default xml identification tag
		$idTag = '<?xml version="1.0" encoding="utf-8" ?>'.self::LF;

		// default envelope start and end tags
		// $attributes any extra attributes for the envelope
		// for proper output tabbed formatting, leave as is
		$sEnvelope = '<SOAP-ENV:Envelope
	xmlns:SOAP-ENV="'.self::NS_SOAP_ENV.'"
	xmlns:SOAP-ENC="'.self::NS_SOAP_ENC.'"
	xmlns:xsi="'.self::NS_XML_SCHEMA_XSI.'"
	xmlns:xsd="'.self::NS_XML_SCHEMA_XSD.'"
	'.trim($envAttrib, "\t\n").'
	SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'.self::LF;
		$eEvelope = '</SOAP-ENV:Envelope>'.self::LF;

		// default body start and end tags
		$sBody = '<SOAP-ENV:Body>'.self::LF;
		$eBody = self::LF.'</SOAP-ENV:Body>'.self::LF;

		$this->_startEnvelope = $idTag.$sEnvelope.$sBody;
		$this->_endEnvelope = $eBody.$eEvelope;
	}

	/**
	 * Strips the Envelope and Body elements off and returns raw data
	 *
	 * @param string $data
	 * @return string
	 * @since version 1.0.0
	 */
	private function _stripEnvelope($data)
	{
		if (!is_string($data))
		{
			throw new MainException(gettype($data),
				MainException::TYPE_STRING);
		}

		$dom = new DOMDocument($this->_xmlVersion, $this->_xmlEncoding);
		$domReturn = new DOMDocument($this->_xmlVersion, $this->_xmlEncoding);

		if ($dom->loadXML($data) === false)
		{
			throw new SoapMessagingException('',
				SoapMessagingException::SOAP_UNPARSEABLE);
		}

		// The first element MUST be SOAP-ENV:Envelope then the only (element)
		// child of it MUST be SOAP-ENV:Body
		if ($dom->documentElement->hasChildNodes())
		{
			if ($dom->documentElement->nodeName == 'SOAP-ENV:Envelope')
			{
				$body = $this->getFirstElement($this->getChildElement(
					$dom->documentElement, 'SOAP-ENV:Body'));
				$b = $domReturn->importNode($body, true);
				$domReturn->appendChild($b);
			}
		}

		$return = $domReturn->saveXML();
		unset($dom);
		unset($domReturn);

		// return raw data
		return $return;
	}
}

?>
