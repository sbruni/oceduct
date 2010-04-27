<?php
/**
 * Stream Class
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Stream
 * @see IoException
 * @see Io
 * @access private
 */
class Stream extends Io
{
	/**
	 * default line ending for a stream
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	const CRLF							= "\r\n";

	/**
	 * Cleans a connection of any data
	 *
	 * @param mixed $rid [optional]
	 * @since version 1.0.0
	 */
	public function clear($rid = 0)
	{
		// only run on a active connection (do not attempt to connect)
		if (!empty($this->resource[$rid]) && !is_resource($this->resource[$rid]))
		{
			return;
		}

		// flush connection after request clean up data
		// disable blocking
		$this->setBlocking(false, $rid);

		// get all the remaining data
		do
		{
			$data = fread($this->resource[$rid], self::BUFFER_LENGTH);
		}
		// loop while data is received
		while (strlen($data) > 0);

		// re-enable blocking
		$this->setBlocking(true, $rid);
	}

	/**
	 * Connect to stream
	 * Give the RID you wish to use
	 *
	 * @param string $host [optional]
	 * @param integer $port [optional]
	 * @param mixed $rid [optional]
	 * @since version 1.0.0
	 */
	public function connect($host = '', $port = 0, $rid = 0)
	{
		// only connect once unless a new connection is requested
		if (!empty($this->resource[$rid]) && is_resource($this->resource[$rid]))
		{
			return;
		}

		if (!empty($host))
		{
			// if a host is given set it
			$this->__set('host', $host);
		}

		if (!empty($port))
		{
			// if a port is given set it
			$this->__set('port', $port);
		}

		// ini error vars
		$errno = null;
		$errstr = null;

		if (!empty($this->resource[$rid]))
		{
			unset($this->resource[$rid]);
		}

		// open the connection
		if ($this->_persistent === true)
		{
			// persitent connection
			$this->resource[$rid] = pfsockopen($this->_host, $this->_port,
				$errno, $errstr, $this->_timeout);
		}
		else
		{
			// non persitent connection
			$this->resource[$rid] = fsockopen($this->_host, $this->_port,
				$errno, $errstr, $this->_timeout);
		}

		// connection is active
		if (!empty($this->resource[$rid]) && !is_resource($this->resource[$rid]))
		{
			// create the error message
			$msg = ' Error#: '.$errno.' Error message: '.$errstr;
			// could not connect
			throw new IoException($this->_host.':'.$this->_port.$msg,
				IoException::STREAM_CONNECTION_FAILED);
		}

		// set the stream timeout
		$this->setTimeout($rid);

		// enable blocking
		$this->setBlocking(true, $rid);
	}

	/**
	 * Close the active connection
	 *
	 * @param mixed $rid [optional]
	 * @since version 1.0.0
	 */
	public function disconnect($rid = 0)
	{
		// if it's an open connection and is not a persistent connection close it
		if (!empty($this->resource[$rid]) && is_resource($this->resource[$rid]) &&
			$this->persistent === false)
		{
			fclose($this->resource[$rid]);
			unset($this->resource[$rid]);
		}
	}

	/**
	 * Read from x type of stream
	 * NOTE: don't read from length or until streams with this method
	 * use the specific ones below
	 *
	 * @param string $type [optional]
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.0.0
	 * @see Stream::_read()
	 * @see Stream::_readChunked()
	 * @see Stream::_readLength()
	 * @see Stream::_readUntil()
	 */
	public function read($type = '', $rid = 0)
	{
		return $this->_read($type, 0, '', $rid);
	}

	/**
	 * Read from a chunked encoded stream
	 *
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.0.0
	 * @see Stream::_read()
	 */
	public function readChunked($rid = 0)
	{
		return $this->_read('chunked', 0, '', $rid);
	}

	/**
	 * Read from stream $length amount of bytes
	 *
	 * @param integer $length
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.0.0
	 * @see Stream::_read()
	 */
	public function readLength($length, $rid = 0)
	{
		return $this->_read('length', $length, '', $rid);
	}

	/**
	 * Read a single line from the stream
	 *
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.4.0
	 * @see Stream::_read()
	 */
	public function readLine($rid = 0)
	{
		return $this->_read('line', 0, '', $rid);
	}

	/**
	 * Read from the stream until $until is found
	 * or server disconnects
	 *
	 * NOTE: $until MUST be found EXACTLY on a single fgets() call
	 * meaning you can't do $until = CRLF.'stuff'.CRLF
	 * If you want to stop reading at a specific char etc.. then use
	 * Stream::readUntilInStr()
	 *
	 * @param string $until
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.0.0
	 * @see Stream::_read()
	 */
	public function readUntil($until, $rid = 0)
	{
		return $this->_read('until-fullcheck', 0, $until, $rid);
	}

	/**
	 * Read from the stream until the string is found
	 * (by default) 2048 bytes are read in at a time
	 * OR until a newline (\n) is found
	 * this string is then checked for $until, if $until is found within
	 * the string, no futher reading is preformed, also only UP TO $until
	 * will be returned, anything else on the current line WILL be ignored
	 *
	 * If $cutstring is false then when $until IS FOUND the FULL string WILL
	 * be returned, you can use this as a search through a incoming stream.
	 *
	 * @param string $until
	 * @param mixed $rid [optional]
	 * @param bool $cutstring [optional]
	 * @return string
	 * @since version 1.4.0
	 * @see Stream::_read()
	 */
	public function readUntilInStr($until, $rid = 0, $cutstring = true)
	{
		if ($cutstring === true)
		{
			return $this->_read('until-instr-cut', 0, $until, $rid);
		}

		return $this->_read('until-instr', 0, $until, $rid);
	}

	/**
	 * Enables or disables the stream's blocking mode
	 * 
	 * @param bool $mode [optional]
	 * @param mixed $rid [optional]
	 * @since version 1.0.0
	 */
	public function setBlocking($mode = true, $rid = 0)
	{
		if (!is_bool($mode))
		{
			// boolean expected
			throw new MainException(gettype($mode),
				MainException::TYPE_BOOLEAN);
		}

		if (stream_set_blocking($this->resource[$rid], $mode) === false)
		{
			throw new IoException('',
				IoException::STREAM_BLOCKING_FAILED);
		}
	}

	/**
	 * Sets the type of output the read methods produce
	 *
	 * Files are NEVER overwritten this is a security precuation
	 * if allowed to overwrite files, someone could possibliy overwrite your
	 * system files, so you'll have to manually replace files if wanted.
	 *
	 * $type can be one of the following
	 * return
	 * direct
	 * file
	 *
	 * If type is 'file' and file contains a /path/filename, data will be
	 * written to that file directly and not outputted or returned
	 * The file will be created, it will NOT overwrite an existing file
	 *
	 * $file should be a full /path/to/filename
	 *
	 * @param string $type [optional]
	 * @param string $file [optional]
	 * @since version 1.0.0
	 */
	public function setOutput($type = 'return', $file = '')
	{
		// default
		if ($type == 'return')
		{
			$this->_outputType = 'return';
		}
		elseif ($type == 'direct')
		{
			$this->_outputType = 'direct';
		}
		elseif ($type == 'file' && !empty($file))
		{
			if (file_exists($file))
			{
				throw new IoException('', IoException::FILE_EXIST);
			}
			$this->_outputType = 'file';
			$this->_outputFile = $file;
		}
	}

	/**
	 * Set the stream's timeout
	 *
	 * @param mixed $rid [optional]
	 * @since version 1.0.0
	 */
	public function setTimeout($rid = 0)
	{
		// set the stream's timeout
		if (stream_set_timeout($this->resource[$rid], $this->_timeout) === false)
		{
			// could not set stream
			throw new IoException($this->_timeout,
				IoException::STREAM_TIMEOUT);
		}
	}

	/**
	 * Write to stream
	 * 
	 * @param mixed $data
	 * @param mixed $rid [optional]
	 * @return integer
	 * @since version 1.0.0
	 */
	public function write($data, $rid = 0)
	{
		if (!empty($this->resource[$rid]) && !is_resource($this->resource[$rid]))
		{
			// connect
			$this->connect('', 0, $rid);
		}

		// write request to stream
		$write = fputs($this->resource[$rid], $data, strlen($data));

		if ($write === false)
		{
			// could not write to the stream
			throw new IoException('',
				IoException::STREAM_WRITE_DENIED);
		}
	}

	/**
	 * Class constructor
	 *
	 * @param bool $persistent
	 * @param integer $timeout [optional]
	 * @since version 1.0.0
	 */
	public function __construct($persistent = false, $timeout = 5)
	{
		// run parent construct
		parent::__construct();

		// make sure $timeout is an integer
		if (!settype($timeout, 'integer'))
		{
			throw new MainException(gettype($timeout),
				MainException::TYPE_INTEGER);
		}
		// make sure $persistent is boolean
		if (!is_bool($persistent))
		{
			throw new MainException(gettype($persistent),
				MainException::TYPE_BOOL);
		}

		// ini variables
		$this->_timeout = $timeout;
		$this->_persistent = $persistent;
		$this->_outputType = 'return';

		// resource is an array of open connections
		$this->resource = array();
	}

	/**
	 * Class Deconstructor
	 * 
	 * @since version 1.0.0
	 */
	public function __destruct()
	{
		// clean up before closing
		if (is_array($this->resource))
		{
			foreach ($this->resource as $rid => $res)
			{
				$this->disconnect($rid);
			}
		}
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
			case 'host':
				$return = $this->_host;
				break;
			case 'persistent':
				$return = $this->_persistent;
				break;
			case 'port':
				$return = $this->_port;
				break;
			case 'timeout':
				$return = $this->_timeout;
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
			case 'host':
				if (!is_string($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}
				$this->_host = $value;
				break;
			case 'persistent':
				// make sure $value is boolean
				if (!is_bool($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_BOOLEAN);
				}
				$this->_persistent = $value;
				break;
			case 'port':
				if (!settype($value, 'integer'))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_INTEGER);
				}
				$this->_port = $value;
				break;
			case 'timeout':
				// make sure $value is an integer
				if (!settype($value, 'integer'))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_INTEGER);
				}
				$this->_timeout = $value;
				break;
			default:
				throw new MainException('Invalid parameter "'.$var.
					'" with value "'.$value.'"', MainException::INVALID_PARAM);
		}
	}

	// private

	/**
	 * @var string
	 * @since version 1.0.0
	 */
	private $_host;

	/**
	 * Output file
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	private $_outputFile;

	/**
	 * Output type
	 * 
	 * @var string
	 * @since version 1.0.0
	 */
	private $_outputType;

	/**
	 * true = use persistent connection
	 * 
	 * @var bool
	 * @since version 1.0.0
	 */
	private $_persistent;

	/**
	 * port number
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_port;

	/**
	 * resource timeout
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_timeout;

	/**
	 * Read from stream
	 *
	 * type is one of
	 * chunked
	 * line
	 * until
	 * until-fullcheck
	 * until-instr
	 * until-instr-cut
	 * length (default)
	 *
	 * chunked: (3.6.1 Chunked Transfer Coding)
	 * read data in chunks (see RFC 2616)
	 *
	 * until:
	 * read data until you hit $until or end of stream
	 *
	 * length: (default)
	 * read in xlength data (if length is unknown set to zero)
	 *
	 * @param string $type [optional]
	 * @param integer $length [optional]
	 * @param string $until [optional]
	 * @param mixed $rid [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	private function _read($type = 'length', $length = 0, $until = '', $rid = 0)
	{
		if (!is_numeric($length))
		{
			throw new IoException('Length is not a number', IoException::INVALID_PARAM);
		}

		// never overwrite files
		// security precuation
		if ($this->_outputType == 'file' && !empty($this->_outputFile) &&
			file_exists($this->_outputFile))
		{
			throw new IoException('', IoException::FILE_EXIST);
		}

		if (!empty($this->resource[$rid]) && !is_resource($this->resource[$rid]))
		{
			// connect
			$this->connect('', 0, $rid);
		}

		$body = '';
		$file = '';
		switch (strtolower(trim($type)))
		{
			case 'chunked':
				$length = 0;
				// read from chunked stream
				// loop though the stream
				do
				{
					// NOTE: for chunked encoding to work properly make sure
					// there is NOTHING (besides newlines) before the first
					// hexlength, it is suggested you use 'until' with
					// Stream::CRLF as the argument to get all the headers
					// first before using chunked

					// get the line which has the length of this chunk
					// (use fgets here)
					$line = fgets($this->resource[$rid], self::BUFFER_LENGTH);

					// if it's only a newline this normally means it's read
					// the total amount of data requested minus the newline
					// (it's how chunked encoding works)
					if ($line == self::CRLF)
					{
						continue;
					}

					// the length of the block is sent in hex
					// decode it then loop through that much data
					// get the length
					$length = hexdec($line);

					if (!is_int($length))
					{
						throw new IoException('Stream most likly not chunked'.
							' encoded. Recieved: "'.$length.'" was expecting '.
							'an integer. Line was: "'.$line.'"',
							MainException::TYPE_INTEGER_CUSTOM);
					}

					// zero is sent when at the end of the chunks
					// or the end of the stream or error
					if ($line === false || $length < 1 || feof($this->resource[$rid]))
					{
						// break out of the streams loop
						break;
					}

					// loop though the chunk
					do
					{
						// read $length amount of data
						// (use fread here)
						$data = fread($this->resource[$rid], $length);

						// remove the amount received from the total length
						// on the next loop it'll attempt to that that much
						// less in
						$length -= strlen($data);

						// output directly
						if ($this->_outputType == 'direct')
						{
							print $data;
							flush();
						}
						// output to a file
						elseif ($this->_outputType == 'file' && !empty($this->_outputFile))
						{
							if (!is_resource($file))
							{
								$file = fopen($this->_outputFile, 'xb');
							}
							$r = fwrite($file, $data, strlen($data));
							fflush($file);
							if ($r === false)
							{
								throw new IoException($this->_outputFile,
									IoException::FILE_WRITE_ERROR);
							}
						}
						// return from method
						else
						{
							$body .= $data;
						}

						// zero or less or end of connection break
						if ($length <= 0 || feof($this->resource[$rid]))
						{
							// break out of the cunk loop
							break;
						}
					}
					while (true);
					// end of chunk loop
				}
				while (true);
				// end of stream loop
				break;
			case 'line':
				// retrive a single line from the server
				// fgets stops after the first \n is found to a max of BUFFER_LENGTH
				$data = fgets($this->resource[$rid], self::BUFFER_LENGTH);

				// output directly
				if ($this->_outputType == 'direct')
				{
					print $data;
					flush();
				}
				// output to a file
				elseif ($this->_outputType == 'file' && !empty($this->_outputFile))
				{
					if (!is_resource($file))
					{
						$file = fopen($this->_outputFile, 'xb');
					}
					$r = fwrite($file, $data, strlen($data));
					fflush($file);
					if ($r === false)
					{
						throw new IoException($this->_outputFile,
							IoException::FILE_WRITE_ERROR);
					}
				}
				// return from method
				else
				{
					$body .= $data;
				}
				break;
			case 'until-instr-cut':
				$cut = true;
			case 'until-instr':
				$instr = true;
			case 'until':
			case 'until-fullcheck':
				// same as until except it cuts off if the string is found
				// within instead of fully
				if (empty($until))
				{
					throw new MainException('until',
						MainException::PARAM_EMPTY);
				}

				do
				{
					// use fgets, we read line by line
					$data = fgets($this->resource[$rid], self::BUFFER_LENGTH);

					$break = false;

					// check for error or breaking point or end of file
					// instr-cut and instr
					if (!empty($instr) && $instr === true)
					{
						$pos = stripos($data, $until);

						// instr-cut
						if (!empty($cut) && $cut === true)
						{
							if ($data === false || $pos !== false ||
								feof($this->resource[$rid]))
							{
								if ($pos !== false)
								{
									// cut off everything AFTER and including $until
									$data = substr($data, 0, $pos);

									// break AFTER OUTPUT
									$break = true;
								}
								else
								{
									// break BEFORE OUTPUT
									break;
								}
							}
						}
						// instr
						else
						{
							if ($data === false || $pos !== false ||
								feof($this->resource[$rid]))
							{
								if ($pos !== false)
								{
									// break AFTER OUTPUT
									$break = true;
								}
								else
								{
									// break BEFORE OUTPUT
									break;
								}
							}
						}
					}
					else
					{
						// fullcheck
						if ($data === false || $data == $until ||
							feof($this->resource[$rid]))
						{
							// break BEFORE OUTPUT
							break;
						}
					}

					// output directly
					if ($this->_outputType == 'direct')
					{
						print $data;
						flush();
					}
					// output to a file
					elseif ($this->_outputType == 'file' && !empty($this->_outputFile))
					{
						if (!is_resource($file))
						{
							$file = fopen($this->_outputFile, 'xb');
						}
						$r = fwrite($file, $data, strlen($data));
						fflush($file);
						if ($r === false)
						{
							throw new IoException($this->_outputFile,
								IoException::FILE_WRITE_ERROR);
						}
					}
					// return from method
					else
					{
						$body .= $data;
					}

					if ($break === true)
					{
						break;
					}
				}
				while (true);
				// end of loop
				break;
			case 'length':
			default:
				// if a length is given then read that much data in
				// otherwise it'll attempt to read everything in
				if ($length > 0)
				{
					// read as much as possible and keep looping till done
					do
					{
						// read in buffer length only this way php doesn't load
						// everything into memory which causes Allowed memory
						// size exhausted
						if ($length > self::BUFFER_LENGTH)
						{
							$len = self::BUFFER_LENGTH;
						}
						else
						{
							$len = $length;
						}

						// read xlength amount of data
						$data = fread($this->resource[$rid], $len);

						// remove the amount received from the total length
						// on the next loop it'll attempt to that that much
						// less in
						$length -= strlen($data);

						// output directly
						if ($this->_outputType == 'direct')
						{
							print $data;
							flush();
						}
						// output to a file
						elseif ($this->_outputType == 'file' && !empty($this->_outputFile))
						{
							if (!is_resource($file))
							{
								$file = fopen($this->_outputFile, 'xb');
							}
							$r = fwrite($file, $data, strlen($data));
							fflush($file);
							if ($r === false)
							{
								throw new IoException($this->_outputFile,
									IoException::FILE_WRITE_ERROR);
							}
						}
						// return from method
						else
						{
							$body .= $data;
						}

						// zero or less or end of connection break
						if ($length <= 0 || feof($this->resource[$rid]) || empty($data))
						{
							// break out
							break;
						}
					}
					while (true);
					// NOTE: there could still be data left on the stream but we
					// are ONLY retiving the amount given in $length
				}
				else
				{
					do
					{
						// read in the data
						// when blocking mode is enabled fread will wait until
						// data becomes ready, it will wait until the timeout
						// is hit, if the timeout is to high you'll find that
						// it'll take a long time for your script to end
						// NOTE: this is stream timeout not fsocket timeout
						// through in this class they are set to the same
						$data = fread($this->resource[$rid], self::BUFFER_LENGTH);

						// output directly
						if ($this->_outputType == 'direct')
						{
							print $data;
							flush();
						}
						// output to a file
						elseif ($this->_outputType == 'file' && !empty($this->_outputFile))
						{
							if (!is_resource($file))
							{
								$file = fopen($this->_outputFile, 'xb');
							}
							$r = fwrite($file, $data, strlen($data));
							fflush($file);
							if ($r === false)
							{
								throw new IoException($this->_outputFile,
									IoException::FILE_WRITE_ERROR);
							}
						}
						// return from method
						else
						{
							$body .= $data;
						}

						// break out when there is NO data or end of connection
						// this is where the timeout comes into play (read above)
						if (strlen($data) == 0 || feof($this->resource[$rid]))
						{
							// break out
							break;
						}
					}
					while (true);
				}
				break;
		}

		// close any open files
		if ($this->_outputType == 'file' && is_resource($file))
		{
			fclose($file);
		}
		// return the data received
		return $body;
	}
}

?>
