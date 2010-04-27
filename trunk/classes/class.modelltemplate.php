<?php
/**
 * ModellTemplate Class
 *
 * Supports Filesystem or Database (currently only PostgreSQL)
 *
 * The database tables <b>MUST</b> be in the following format
 * the table name can be custom or can be in the format: *type*_template i.e
 * php_template or html_template etc...
 * <code>
 * CREATE TABLE "php_template"
 * (
 *    "ID"					SERIAL PRIMARY KEY,
 *    "Name"				CHARACTER VARYING NOT NULL,
 *    "Template"			TEXT NOT NULL,
 *    "LanguageCode"		CHARACTER VARYING NOT NULL,
 *    "CreateDate"			TIMESTAMP NOT NULL DEFAULT 'NOW',
 *    "LastModifiedDate"	TIMESTAMP NOT NULL DEFAULT 'NOW',
 *    "Disabled"			BOOLEAN NOT NULL DEFAULT false
 * );
 * </code>
 *
 * Overloading is used to set the options listed below:
 * activeLanguageCode
 * databaseTable
 * dataCache
 * defaultLanguageCode
 * disabled
 * evaluatedCache
 * fileExtension
 * lineEnding
 * templateDirectory
 *
 * NOTE: About isset() and empty() usage
 * isset() is used over empty() throughout the class when dealing with data
 * as the data could be simply "0" or "" and we STILL want to return thoses
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package ModellTemplate
 * @access private
 * @see ModellTemplateException
 * @todo perhaps add regex replacement support in
 * @todo finish database access part
 */
class ModellTemplate
{
	/**
	 * Default buffer size for reading in files
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const FILE_BUFFER							= 2048;

	/**
	 * The max size of data that will be saved to the cache.
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	const MAX_CACHE_SIZE						= 100000;

	/**
	 * PHP variable replacement regex string
	 *
	 * when a template is retrived all PHP variables within it (excluding
	 * PHP and PHPHTML template types and any others marked as only eval)
	 * are temporary replaced with this regex, allowing more string
	 * replacements without affecting PHP variables
	 *
	 * @see ModellTemplate::_doVariablesEncode()
	 * @var string
	 * @since version 1.0.0
	 */
	const PHP_VAR_REPLACEMENT					= '*#*';

	/**
	 * Other variable replacement regex string
	 *
	 * replaces non PHP variables (i.e any <b>without</b> { and } around them)
	 * they are replaced back to normal <b>after</b> being evaled
	 *
	 * @see ModellTemplate::_doVariablesDecode()
	 * @see ModellTemplate::_doVariablesEncode()
	 * @var string
	 * @since version 1.0.0
	 */
	const OTHER_VAR_REPLACEMENT					= '!#!';

	/**
	 * Clears the cache's
	 * 
	 * @since version 1.0.0
	 */
	public function doClearCache()
	{
		$this->_evaluatedCache = array();
		$this->_dataCache = array();
		$this->_bylineCache = array();
	}

	/**
	 * Evaluate the given data
	 *
	 * If $evaluateAsString is true then $data is placed within a evaled string
	 * Example:
	 * <code>
	 * eval('return "'.$data.'"');
	 * </code>
	 * If $evaluateAsString is false then $data is evaled directly through
	 * {@link eval()}
	 *
	 * @param string $data
	 * @param array $args [optional]
	 * @param array $returnVars [optional]
	 * @param array $replacements [optional]
	 * @param bool $evaluateAsString [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	public function doEvaluate($data, $args = array(), $returnVars = array(),
		$replacements = array(), $evaluateAsString = true)
	{
		// perform cleanup
		$this->_doCleanUp();

		// set the active args
		if (is_array($args))
		{
			$this->_args = $args;
		}

		// set the active replacements
		if (is_array($replacements))
		{
			$this->_replacements = $replacements;
		}

		// set the active return vars
		if (is_array($returnVars))
		{
			$this->_returnVars = $returnVars;
		}

		// unset values to incress memory limit on exit
		unset($args);
		unset($replacements);
		unset($returnVars);

		// create the UID
		// name args replacements activelangcode return
		$this->_uid = md5($data.$this->implode_multi('', $this->_args).
			$this->implode_multi('', $this->_replacements).
			$this->_activeLanguageCode.$this->implode_multi('', $this->_returnVars));

		// set the data to use
		$this->_data = $data;

		// since this is a custom method we override the default
		$onlyEval = $this->_onlyEval;
		// reverse setting
		$this->_onlyEval = ($evaluateAsString === true)?false:true;

		// evaluate the data
		$this->_doEvaluate();

		if (!empty($this->_returnVars) && $this->_onlyEval === true)
		{
			// add the eval output to the array
			$this->_dataArray['evalOutput'] = $this->_data;

			// set back the only eval setting to the class setting
			$this->_onlyEval = $onlyEval;

			// perform cleanup
			$this->_doCleanUp();

			// return array
			return $this->_dataArray;
		}
		else
		{
			// set back the only eval setting to the class setting
			$this->_onlyEval = $onlyEval;

			// perform cleanup
			$this->_doCleanUp();

			return $this->_data;
		}
	}

	/**
	 * Validate the template code
	 *
	 * Makes sure that the given code is valid and will run without errors
	 * NOTE: The code IS evaluated, if the code is something that requires user
	 * input or does an update, then it is recommened to <b>not</b> use this.
	 *
	 * @param string $name
	 * @return bool
	 * @todo write this method
	 * @since version 1.0.0
	 */
	public function doValidate($name)
	{
		// todo
	}

	/**
	 * Check if a template exists
	 *
	 * This <b>will</b> check for a template with either the active language
	 * code or the default one.
	 *
	 * @param string $name
	 * @return bool
	 * @since version 1.0.0
	 */
	public function exists($name)
	{
		// check/set the name
		$this->_doCheckName($name);

		// check if the template exists (true MUST be given as the arg)
		// returns true or false
		$exists = $this->_doOpen(true);

		// perform cleanup
		$this->_doCleanUp();

		// template exists
		return $exists;
	}

	/**
	 * Get byline
	 *
	 * Each line within the template is read into memory and provided as a snippet
	 * of text. Each of the templates can be translated to any number of languages
	 * and the snippets can be referenced from a single id.
	 *
	 * ID is anything before the FIRST TAB char on the line
	 * i.e
	 * id123[tab]the text
	 *
	 * The ID is NOT translated, anything after the first TAB till the FIRST newline (LF)
	 * is able to be translated.
	 *
	 * Any line starting with a # is a comment
	 *
	 * ID can be any char except # \t \n
	 * There is NO length limit on either
	 *
	 * @param string $name
	 * @param string $id
	 * @return string
	 * @since version 1.0.0
	 */
	public function getByline($name, $id)
	{
		// perform cleanup
		$this->_doCleanUp();

		$this->_doCheckName($name);

		// create the UID
		// name activelangcode
		$this->_uid = md5($this->_name.$this->_activeLanguageCode);

		// check for a cache
		if ($this->_disableBylineCache === false &&
			isset($this->_bylineCache[$this->_uid][$id]))
		{
			return $this->_bylineCache[$this->_uid][$id];
		}
		else
		{
			// no cached copy
			// attempt to open the template
			$this->_doOpen();

			// disable datacache
			$disableDataCache = $this->_disableDataCache;
			$this->_disableDataCache = true;

			// get the raw data
			$this->_getRawData();

			// close the template
			$this->_doClose();

			$lines = array();
			$matches = array();
			$tmp = preg_split('!\n!', $this->_data);
			foreach ($tmp as $line)
			{
				$line = trim($line);

				// ignore lines starting with # as they are comments or tabs as
				// they shouldn't be there
				// if there is NO value that line IS ignored
				if (preg_match('!^([^#\t]+?)\t(.*)!i', $line, $matches))
				{
					$lines[$matches[1]] = $matches[2];
				}
			}

			// the item does NOT exist in the current language
			// check if it is in the default language
			if (!isset($lines[$id]))
			{
				$activeCode = $this->_activeLanguageCode;
				$this->_activeLanguageCode = $this->_defaultLanguageCode;

				// reset the filename
				$this->_name = '';
				$this->_doCheckName($name);

				$this->_doOpen();

				// get the raw data
				$this->_getRawData();

				// close the template
				$this->_doClose();

				$dLines = array();
				$matches = array();
				$tmp = preg_split('!\n!', $this->_data);
				foreach ($tmp as $line)
				{
					$line = trim($line);

					// ignore lines starting with # as they are comments or tabs as
					// they shouldn't be there
					// if there is NO value that line IS ignored
					if (preg_match('!^([^#\t]+?)\t(.*)!i', $line, $matches))
					{
						$dLines[$matches[1]] = $matches[2];
					}
				}

				// set language back
				$this->_activeLanguageCode = $activeCode;

				if (!isset($dLines[$id]))
				{
					throw new ModellTemplateException('Invalid byline ID: '.$id,
						ModellTemplateException::INVALID_PARAM);
				}

				// we merge the missing lines into the the current language
				foreach ($dLines as $key => $line)
				{
					if (!isset($lines[$key]))
					{
						$lines[$key] = $line;
					}
				}
			}

			// set datacache back to what it was
			$this->_disableDataCache = $disableDataCache;

			// add to bypass cache
			if ($this->_disableBylineCache === false &&
				!empty($lines))
			{
				$this->_bylineCache[$this->_uid] = $lines;
			}

			// perform cleanup
			$this->_doCleanUp();

			if (isset($lines[$id]))
			{
				return $lines[$id];
			}
		}
	}

	/**
	 * Get byline Evaluated
	 *
	 * Functions exactly the same as {@link getByline()}
	 * but evals the returned item
	 *
	 * The template will be fully parsed and have $args run on it
	 * (as valid variables)
	 * $replacements <b>will</b> be made before evaluation
	 *
	 * If $evaluateAsString is true then the data is placed within a evaled string
	 * Example:
	 * <code>
	 * eval('return "'.$data.'"');
	 * </code>
	 * If $evaluateAsString is false then $data is evaled directly through {@link eval()}
	 *
	 * @param string $name
	 * @param string $id
	 * @param array $args [optional]
	 * @param array $returnVars [optional]
	 * @param array $replacements [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	public function getBylineEvaluated($name, $id, $args = array(),
		$returnVars = array(), $replacements = array())
	{
		// check for a cache
		if ($this->_disableEvaluatedCache === false &&
			isset($this->_evaluatedCache[$this->_uid]))
		{
			return $this->_evaluatedCache[$this->_uid];
		}
		else
		{
			// no cached copy
			// get the item
			$this->_data = $this->getByline($name, $id);

			// set the active args
			if (is_array($args))
			{
				$this->_args = $args;
			}

			// set the active replacements
			if (is_array($replacements))
			{
				$this->_replacements = $replacements;
			}

			// set the active return vars
			if (is_array($returnVars))
			{
				$this->_returnVars = $returnVars;
			}

			// unset values to incress memory limit on exit
			unset($args);
			unset($replacements);
			unset($returnVars);

			// create the UID
			// name id activelangcode
			$this->_uid = md5($this->_name.$id.$this->_activeLanguageCode);

			// evaluate the data
			$this->_doEvaluate();

			// perform cleanup
			$this->_doCleanUp();

			return $this->_data;
		}
	}

	/**
	 * Retrives a template and returns it without evaluation
	 *
	 * This should be used to retrive raw template data
	 * for an evaluated template see {@link ModellTemplate::getEvaluated()}
	 *
	 * @param string $name
	 * @param array $replacements [optional]
	 * @return string
	 * @since version 1.0.0
	 */
	public function getData($name, $replacements = array())
	{
		// perform cleanup
		$this->_doCleanUp();

		$this->_doCheckName($name);

		// set the active replacements
		if (is_array($replacements))
		{
			$this->_replacements = $replacements;
		}

		// unset values to incress memory limit on exit
		unset($replacements);

		// create the UID
		// name replacements activelangcode
		$this->_uid = md5($this->_name.implode($this->_replacements).
			$this->_activeLanguageCode);

		// check for a data cache
		if ($this->_disableDataCache === false &&
			isset($this->_dataCache[$this->_uid]))
		{
			// cached copy found use it
			$this->_data = $this->_dataCache[$this->_uid];
		}
		else
		{
			// no cached copy
			// attempt to open the template
			$this->_doOpen();

			// get the raw data
			$this->_getRawData();

			// close the template
			$this->_doClose();
		}

		// return the raw data
		return $this->_data;
	}

	/**
	 * Retrives and Evaluates a template
	 *
	 * The template will be fully parsed and have $args run on it
	 * (as valid variables)
	 * $replacements <b>will</b> be made before evaluation
	 *
	 * If $evaluateAsString is true then the data is placed within a evaled string
	 * Example:
	 * <code>
	 * eval('return "'.$data.'"');
	 * </code>
	 * If $evaluateAsString is false then $data is evaled directly through {@link eval()}
	 *
	 * {$name} filename of the template to use
	 * {$args} if an array: arguments to be evaluate within the template
	 * {$args} if a string: turns to array[0]
	 * {$returnVars} an array containing the names of the variables
	 * you want returned from this evaluation ONLY works when $onlyEval is true
	 * {$replacements} an array containing any replacements
	 * key = search value = replacement case insensitive
	 *
	 * @param string $name
	 * @param array $args [optional]
	 * @param array $returnVars [optional]
	 * @param array $replacements [optional]
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function getEvaluatedData($name, $args = array(),
		$returnVars = array(), $replacements = array())
	{
		// perform cleanup
		$this->_doCleanUp();

		$this->_doCheckName($name);

		// set the active args
		if (is_array($args))
		{
			$this->_args = $args;
		}

		// set the active replacements
		if (is_array($replacements))
		{
			$this->_replacements = $replacements;
		}

		// set the active return vars
		if (is_array($returnVars))
		{
			$this->_returnVars = $returnVars;
		}

		// unset values to incress memory limit on exit
		unset($args);
		unset($replacements);
		unset($returnVars);

		// create the UID
		// name args replacements activelangcode return
		$this->_uid = md5($this->_name.$this->implode_multi('', $this->_args).
			$this->implode_multi('', $this->_replacements).
			$this->_activeLanguageCode.$this->implode_multi('', $this->_returnVars));

		// check for a evaluated cache
		if ($this->_disableEvaluatedCache === false &&
			isset($this->_evaluatedCache[$this->_uid]))
		{
			// cached copy found use it
			$this->_data = $this->_evaluatedCache[$this->_uid];
		}
		else
		{
			// no cached copy
			// attempt to open the template
			$this->_doOpen();

			// get the raw data
			$this->_getRawData();

			// evaluate the data
			$this->_doEvaluate();

			// close the template
			$this->_doClose();
		}

		if (!empty($this->_returnVars) && $this->_onlyEval === true)
		{
			// add the eval output to the array
			$this->_dataArray['evalOutput'] = $this->_data;

			// perform cleanup
			$this->_doCleanUp();

			// array is returned
			return $this->_dataArray;
		}
		else
		{
			// perform cleanup
			$this->_doCleanUp();

			// return the raw data
			return $this->_data;
		}
	}

	/**
	 * Implodes on a multidimentional array
	 *
	 * @param string $glue
	 * @param array $pieces
	 * @return string
	 * @since version 1.0.0
	 */
	static public function implode_multi($glue, $pieces)
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
	 * Writes the given data to a template
	 *
	 * By default this will validate the template before saving
	 * If an array is given for $languageCode <b>all</b> language codes will
	 * be overwritten with this one (see $overwrite)
	 *
	 * Nothing is returned on success and an exception is thrown on error
	 *
	 * @param string $name
	 * @param string $data
	 * @param bool $noValidate [optional]
	 * @todo finish this method
	 * @since version 1.0.0
	 */
	public function saveData($name, $data, $noValidate = false)
	{
		// todo
	}

	/**
	 * Class constructor
	 *
	 * Valid storage types:
	 * file
	 * postgresql
	 *
	 * File:
	 * $info <b>SHOULD</b> be the full path to the directory where the templates
	 * are stored.
	 * NOTE: you should have <b>ONE</b> type per directory i.e
	 * /usr/local/www/templates/php/ or C:/sites/templates/php/
	 * (ending slash is optional, it will be added automaticly)
	 * PHP <b>SHOULD</b> be given <b>full</b> access to this directory
	 * both reading writing and executing.
	 *
	 * If you are wanting to change from the default .tpl extension then
	 * $info <b>MUST</b> be an array with the following params
	 * <code>
	 * array(
	 *    'dir' => '/usr/local/www/templates/php',
	 *    // new extension name leaving blank will set to .tpl
	 *    'ext' => 'new'
	 * )
	 * </code>
	 *
	 * Database: (i.e postgresql)
	 * $info <b>SHOULD</b> be an array with any of the following data
	 * <code>
	 * array(
	 *    'database' => 'myDatabase',
	 *    'table' => 'mytable',
	 *    'host' => 'localhost',
	 *    'user' => 'username',
	 *    'password' => 'password',
	 *    'port' => 0
	 * )
	 * </code>
	 *
	 * (database isn't mandatory but we <b>NEED</b> a database to know where
	 * the data is stored)
	 * (table is the name of the table that the templates are stored in
	 * if you omit it the default table name will be used.
	 * "type_template" without the quotes i.e "php_template" "html_template"
	 *
	 * Optionally $info can be a database object, the class will then use the
	 * object as the default connection. In this case the table will be retirved
	 * by it's default name "type_template"
	 *
	 * The database tables <b>MUST</b> be in the following format
	 * the table name can be custom or can be in the format: *type*_template i.e
	 * php_template or html_template etc...
	 * <code>
	 * CREATE TABLE "php_template"
	 * (
	 *    "ID"				SERIAL PRIMARY KEY,
	 *    "Name"			CHARACTER VARYING NOT NULL,
	 *    "Template"		TEXT NOT NULL,
	 *    "LanguageCode"	CHARACTER VARYING NOT NULL,
	 *    "Disabled"		BOOLEAN NOT NULL DEFAULT false
	 * );
	 * </code>
	 *
	 * if $onlyEval is true then the template WILL be evaled directly
	 * otherwise it will be placed within a string
	 * PHP and PHPHTML always are set to true (only eval)
	 *
	 * if $languageCode is set then this will because the default language for
	 * this instance, both the default and the active language codes are
	 * able to be changed whenever.
	 *
	 * @param string $type
	 * @param array $storage
	 * @param mixed $info
	 * @param string $languageCode [optional]
	 * @param bool $onlyEval [optional]
	 * @param string $lineEnding [optional]
	 * @since version 1.0.0
	 */
	public function __construct($type, $storage, $info,
		$languageCode = 'en', $onlyEval = false, $lineEnding = '')
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();
		// check for IoException
		if (in_array('IoException', $declaredClasses) === false)
		{
			throw new MainException('IoException: class not found',
				MainException::INVALID_PARAM);
		}

		// check for ModellTemplateException
		if (in_array('ModellTemplateException', $declaredClasses) === false)
		{
			throw new MainException('ModellTemplateException: class not found',
				MainException::INVALID_PARAM);
		}

		// ini variables
		$this->_dataCache = array();
		$this->_evaluatedCache = array();
		$this->_fp = null;
		$this->_db = null;
		$this->_onlyEval = false;
		// caching is enabled by default
		$this->_disableEvaluatedCache = false;
		$this->_disableDataCache = false;
		$this->_disableBylineCache = false;
		// disabled items aren't retrived by default
		$this->_disabled = false;

		if ($onlyEval === true)
		{
			// if only eval is requested
			$this->_onlyEval = true;
		}

		// set the active type for this instance
		// if it doesn't match a default type then use whatever they've given
		// default types MUST be lowercase but custom types can be mixed
		switch (strtolower($type))
		{
			case 'php':
			case 'phphtml':
				// PHP and PHPHTML are ONLY evaled
				$this->_onlyEval = true;
			case 'byline':
			case 'css':
			case 'html':
			case 'xhtml':
			case 'js':
			case 'javascript':
			case 'txt':
			case 'text':
			case 'xml':
			case 'xsl':
				$this->_type = strtolower($type);
				break;
			default:
				if (empty($type))
				{
					throw new MainException('type',
						MainException::PARAM_EMPTY);
				}
				$this->_type = $type;
		}

		// if onlyeval is set then disable output cache
		if ($this->_onlyEval === true)
		{
			$this->_disableEvaluatedCache = true;
		}

		// make sure a language code is given
		if (empty($languageCode))
		{
			throw new MainException('languageCode',
				MainException::PARAM_EMPTY);
		}
		// set the active and default language code to the input one
		$this->_activeLanguageCode = $languageCode;
		$this->_defaultLanguageCode = $languageCode;

		// default to using just a newline
		$this->_lineEnding = "\n";
		if (!empty($lineEnding))
		{
			// use a custom lineending
			$this->_lineEnding = $lineEnding;
		}

		/*
			set the storage type
		*/
		// storage type file
		if ($storage == 'file')
		{
			$this->_storage = 'file';
			$this->_fileDirectory = '';
			// default to .tpl
			$this->_fileExt = 'tpl';

			// if an array is given then check for dir and ext index's
			if (is_array($info))
			{
				// check for a dir index
				if (!isset($info['dir']))
				{
					throw new MainException('Template directory missing',
						MainException::INVALID_PARAM);
				}

				if (is_dir($info['dir']))
				{
					$this->_fileDirectory = $info['dir'];
				}

				// if there is an extension given
				if (isset($info['ext']))
				{
					// remove ALL dot's (.) from the extension
					$this->_fileExt = str_replace('.', '', $info['ext']);
				}
			}
			elseif (is_string($info))
			{
				// if only a string is given assume that it's a directory
				// a valid check is performed later
				$this->_fileDirectory = $info;
			}
			else
			{
				throw new MainException('Invalid parameter given for the '.
					'template directory. Expecting either a string or an '.
					'array.', MainException::INVALID_PARAM);
			}

			// add a ending slash (/) if missing
			if (substr($this->_fileDirectory, -1) != '/')
			{
				$this->_fileDirectory .= '/';
			}
		}
		// storage type postgresql
		elseif ($storage == 'postgresql')
		{
			print 'Not yet supported remember';
			exit();
			$this->_storage = 'postgresql';

			// a PostgreSql object may also be passed in
			// if it is then we can use it here
			// make sure it's registered as PostgreSql
			if (is_object($info) && get_class($info) == 'PostgreSql')
			{
				// pass it by reference
				$this->_db =& $info;
			}
			else
			{
				try
				{
					// ini the db object, if $info isn't an array PostgreSql
					// will throw an exception
					$this->_db = new PostgreSql($info);
				}
				catch (Exception $e)
				{
					// pass on the thrown exception
					throw $e;
				}
			}

			// otherwise use the format type_template
			// NOTE: type CAN be mixed case and/or weird chars but it's not
			// recommened
			$this->_databaseTable = $this->_type.'_template';

			// if the index table exists then use it as the database table
			if (isset($info['table']))
			{
				$this->_databaseTable = $info['table'];
			}
		}
		else
		{
			// if storage type is something else throw an error
			throw new ModellTemplateException($this->_storage,
				ModellTemplateException::INVALID_STORAGE_TYPE);
		}
	}

	/**
	 * Class Destructor
	 *
	 * Clean up all temp variables
	 *
	 * @since version 1.0.0
	 */
	public function __destruct()
	{
		// perform cleanup
		$this->_data = '';
		$this->_dataArray = array();
		$this->_doCleanUp();
		$this->doClearCache();
	}

	/**
	 * Get settings
	 *
	 * All setting and retriving class settings are done via paramaters
	 * or via overloading, depending on when they need to be set.
	 *
	 * @param mixed $name
	 * @see ModellTemplate::_activeLanguageCode
	 * @see ModellTemplate::_databaseTable
	 * @see ModellTemplate::_disableDataCache
	 * @see ModellTemplate::_defaultLanguageCode
	 * @see ModellTemplate::_disabled
	 * @see ModellTemplate::_disableEvaluatedCache
	 * @see ModellTemplate::_fileExt
	 * @see ModellTemplate::_lineEnding
	 * @see ModellTemplate::_fileDirectory
	 * @since version 1.0.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'activeLanguageCode':
				// get the active language code
				return $this->_activeLanguageCode;
				break;
			case 'bylineCache':
				// reverse values
				// all internally are: true = disabled, false = enabled
				return ($this->_disableBylineCache===true)?false:true;
				break;
			case 'databaseTable':
				// if we are using postgresql type
				if ($this->_storage == 'postgresql')
				{
					// get the active table name
					return $this->_databaseTable;
				}
				break;
			case 'dataCache':
				// reverse values
				// all internally are: true = disabled, false = enabled
				return ($this->_disableDataCache===true)?false:true;
				break;
			case 'defaultLanguageCode':
				// get the default language code
				return $this->_defaultLanguageCode;
				break;
			case 'disabled':
				// reverse values
				// all internally are: true = disabled, false = enabled
				return ($this->_disabled===true)?false:true;
				break;
			case 'evaluatedCache':
				// reverse values
				// all internally are: true = disabled, false = enabled
				return ($this->_disableEvaluatedCache===true)?false:true;
				break;
			case 'fileExtension':
				// if we are using file type
				if ($this->_storage == 'file')
				{
					// get the active template file extension
					return $this->_fileExt;
				}
				break;
			case 'lastModifiedDate':
			case 'modifiedDate':
				// gives the date the template was last modified
				return $this->_modifiedDate;
				break;
			case 'lineEnding':
				// get the current line ending
				return $this->_lineEnding;
				break;
			case 'templateDirectory':
				// if we are using file type
				if ($this->_storage == 'file')
				{
					// get the active template directory
					return $this->_fileDirectory;
				}
				break;
			default:
		}
		return '';
	}

	/**
	 * Reset variables after cloning
	 *
	 * @since version 1.0.0
	 */
	public function __clone()
	{
		$this->_doCleanUp();
		$this->doClearCache();
	}

	/**
	 * Get settings
	 *
	 * All setting and retriving class settings are done via paramaters
	 * or via overloading, depending on when they need to be set.
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @see ModellTemplate::_activeLanguageCode
	 * @see ModellTemplate::_databaseTable
	 * @see ModellTemplate::_disableDataCache
	 * @see ModellTemplate::_defaultLanguageCode
	 * @see ModellTemplate::_disabled
	 * @see ModellTemplate::_disableEvaluatedCache
	 * @see ModellTemplate::_fileExt
	 * @see ModellTemplate::_lineEnding
	 * @see ModellTemplate::_fileDirectory
	 * @since version 1.0.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'activeLanguageCode':
				// set the active language code
				// if not a string value throw an exception
				if (is_string($value) === false)
				{
					throw new MainException(gettype($value),
						MainException::TYPE_STRING);
				}

				/**
				 * According to the language code schema's 2 char codes
				 * should be lower cased and 3 char codes should be upper
				 * cased. If there is a custom defined code use it as is.
				 */
				switch (strlen($value))
				{
					case 2:
						$this->_activeLanguageCode = strtolower($value);
						break;
					case 3:
						$this->_activeLanguageCode = strtoupper($value);
						break;
					default:
						$this->_activeLanguageCode = $value;
				}
				break;
			case 'bylineCache':
				// set the data cache
				// if not a boolean value throw an exception
				if (is_bool($value) === false)
				{
					throw new MainException('received '.gettype($value),
						MainException::TYPE_BOOLEAN);
				}

				// reverse setting
				$this->_disableBylineCache = ($value===true)?false:true;
				break;
			case 'databaseTable':
				// set the database table name
				// default table name
				$this->_databaseTable = $this->_type.'_template';
				// if a value is given set it otherwise use the default name
				if (!empty($value))
				{
					$this->_databaseTable = $value;
				}
				break;
			case 'dataCache':
				// set the data cache
				// if not a boolean value throw an exception
				if (is_bool($value) === false)
				{
					throw new MainException('received '.gettype($value),
						MainException::TYPE_BOOLEAN);
				}

				// reverse setting
				$this->_disableDataCache = ($value===true)?false:true;
				break;
			case 'defaultLanguageCode':
				// set the default language code
				// if not a string value throw an exception
				if (is_string($value) === false)
				{
					throw new MainException('received '.gettype($value),
						MainException::TYPE_STRING);
				}

				/**
				 * According to the language code schema's 2 char codes
				 * should be lower cased and 3 char codes should be upper
				 * cased. If there is a custom defined code use it as is.
				 */
				switch (strlen($value))
				{
					case 2:
						$this->_defaultLanguageCode = strtolower($value);
						break;
					case 3:
						$this->_defaultLanguageCode = strtoupper($value);
						break;
					default:
						$this->_defaultLanguageCode = $value;
				}
				break;
			case 'disabled':
				// set use disabled items
				// if not a boolean value throw an exception
				if (is_bool($value) === false)
				{
					throw new MainException('received '.gettype($value),
						MainException::TYPE_BOOLEAN);
				}

				// reverse setting
				$this->_disabled = ($value===true)?false:true;
				break;
			case 'evaluatedCache':
				// set the evaluated cache
				// if not a boolean value throw an exception
				if (is_bool($value) === false)
				{
					throw new MainException('received '.gettype($value),
						MainException::TYPE_BOOLEAN);
				}

				// reverse setting
				$this->_disableEvaluatedCache = ($value===true)?false:true;
				break;
			case 'fileExtension':
				// set the file extension
				$this->_fileExt = 'tpl';
				if (!empty($value))
				{
					// remove any dot's (.)
					$this->_fileExt = str_replace('.', '', $value);
				}
				break;
			case 'lineEnding':
				// set the line ending
				$this->_lineEnding = "\n";
				if (!empty($value))
				{
					$this->_lineEnding = $value;
				}
				break;
			case 'templateDirectory':
				// set the template directory
				$this->_fileDirectory = '';
				if (!empty($value))
				{
					$this->_fileDirectory = $value;
					// add a ending slash (/) if missing
					if (substr($this->_fileDirectory, -1) != '/')
					{
						$this->_fileDirectory .= '/';
					}
				}
				break;
			default:
		}
	}

	// private

	/**
	 * the active template type i.e php html xsl
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_type;

	/**
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_data;

	/**
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_dataArray;

	/**
	 * the name of the currently opened template
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_name;

	/**
	 * the active args array
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_args;

	/**
	 * the active replacement array
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_replacements;

	/**
	 * These variables will be returned if they exist
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_returnVars;

	/**
	 * Depending on the method creating it, different items are used to create it
	 * unique ID for caching
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_uid;

	/**
	 * wither it'll retrive disabled items
	 * @var bool
	 * @since version 1.0.0
	 */
	protected $_disabled;

	/**
	 * if true then the template WILL be evaled directly
	 * it will NOT be placed within a string
	 * PHP and PHPHTML always have this set to true
	 * @var bool
	 * @since version 1.0.0
	 */
	protected $_onlyEval;

	/**
	 * the active language code
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_activeLanguageCode;

	/**
	 * default language code to use if no active code set
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_defaultLanguageCode;

	/**
	 * The type of storage to use: file or postgresql
	 * Currently <b>ONLY</b> supports:
	 * file
	 * postgresql
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_storage;

	/**
	 * A file handler used accross multiple methods
	 * @var resource
	 * @since version 1.0.0
	 */
	protected $_fp;

	/**
	 * this <b>WILL</b> contain the active language code
	 * to use the default language code we do a regex replacement
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_fileName;

	/**
	 * path to the template directory
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_fileDirectory;

	/**
	 * template files extensions defaults to .tpl
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_fileExt;

	/**
	 * the database object can either be created internally or it may be
	 * passed in via the class constructor.
	 * @var resource
	 * @since version 1.0.0
	 */
	protected $_db;

	/**
	 * the name of the table for use with a database type
	 * defaults to "type_template" (without quotes)
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_databaseTable;

	/**
	 * The line ending to use when writing data
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_lineEnding;

	/**
	 * Date the template was last modified
	 * @var string
	 * @since version 1.0.0
	 */
	protected $_modifiedDate;

	/**
		Caching
		Caching is done by storing the output from both template and/or output
		within a two class variables (arrays). This saves database/filesystem
		overhead.

		Upon adding/editing/deleting a template, the template and output cache
		for that template IS cleared.

		The template cache ($this->_dataCache) contains the raw template.
		The template cache is ENABLED by default.
		The template language code IS also stored within the cache i.e
		Example:
		<code>
		array(
			'mytemplate' => array(
				'ENG' => 'my text',
				'otherlangcode' => 'other text'
			),
			'another template' => array(
				'ENG' => 'yeh'
			)
		)
		</code>

		The output cache ($this->_evaluatedCache) contains a processed version
		of the template, with ALL arguments processed on the template.
		The UID for the output cache is determined by an MD5 hash of the
		arguments array and the template name and languge code. In MOST cases
		this is unique enough, but if you're processing a lot of different
		templates, you may want to clear the output cache every once in a while.
		The output cache is ENABLED by default.

		Example:
		<code>
		array(
			'uid' => 'other text',
			'uid' => 'yeh'
		)
		</code>

		NOTE: PHP templates ARE NOT cached, it won't work properly if they ar.
		(you can mark custom templates as PHP templates)

		To clear the active cache call {@link ModellTemplate::clearCache()}
		When evaluating or retiving a template you may optionally choose to
		ignore the cache and retrive/proccess the template again
	*/

	/**
	 * whither output caching is disabled true = disabled
	 * @var bool
	 * @since version 1.0.0
	 */
	protected $_disableEvaluatedCache;

	/**
	 * whither template caching is disabled true = disabled
	 * @var bool
	 * @since version 1.0.0
	 */
	protected $_disableDataCache;

	/**
	 * Byline caching enabled or disabled
	 * @var bool
	 * @since version 1.0.0
	 */
	protected $_disableBylineCache;

	/**
	 * the template cache
	 * the index is the template name
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_dataCache;

	/**
	 * the output cache
	 * the index is an MD5 of the args and template name and language code
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_evaluatedCache;

	/**
	 * Type byline's cache
	 * @var array
	 * @since version 1.0.0
	 */
	protected $_bylineCache;

	/**
	 * Checks the template name and sets it as the active name
	 *
	 * The template name MUST only contain: alpha-numeric or underscore or dash
	 *
	 * @param string $name
	 * @since version 1.0.0
	 */
	protected function _doCheckName($name)
	{
		// if the name IS already set throw an error
		if (!empty($this->_name))
		{
			/*
				only one template is allowed to be opened at a time
				if this does get hit, it means that you have to have another
				instance of this class, the reason is that the calling class
				has within it a reference to the same object as it's self
				if you were to overwrite the orignal template you'd run the
				risk of overwriting any prefomed data.
				the easiest way is to create multiple instances from the start
				and use one as a Sub and another as the main one, if you require
				even more sublevels then ini new objects within those templates

				you'll mainly experence this when using PHP/PHPHTML templates
			*/
			throw new ModellTemplateException($this->_name,
				ModellTemplateException::TEMPLATE_STILL_ACTIVE);
		}

		// bug fix, remove any white spaces
		$name = trim($name);

		if (empty($name))
		{
			throw new MainException('No Template name given.',
				MainException::INVALID_PARAM);
		}

		// make sure the name doesn't have invalid chars
		// ONLY alpha-numeric or underscore or dash are allowed
		if (!preg_match('/^[-_a-z0-9]+$/i', $name))
		{
			throw new MainException('Template name '.$name.' contains invalid '.
				'characters. Valid characters are: alpha-numberic or underscore'.
				' or dash.', MainException::INVALID_PARAM);
		}

		// set the active template
		$this->_name = $name;

		// set the file name
		// NOTE the active language code is set here
		// if we want to use the default code we have to do a regex replacement
		// @see ModellTemplate::exists()
		$this->_fileName = $name.'.'.$this->_activeLanguageCode.'.'.
			$this->_fileExt;

		// always lower case the filename
		$this->_fileName = strtolower($this->_fileName);
	}

	/**
	 * Cleans up the temporary variables etc...
	 * This should always be called at the end of all methods that use these
	 * variables
	 * The Cache is <b>NOT</b> reset
	 *
	 * @since version 1.0.0
	 */
	protected function _doCleanUp()
	{
		// reset all tempoary variables
		$this->_name = '';
		$this->_args = array();
		$this->_replacements = array();
		$this->_returnVars = array();
		$this->_uid = '';
	}

	/**
	 * Close an active template
	 *
	 * @since 1.0.0
	 */
	protected function _doClose()
	{
		switch ($this->_storage)
		{
			case 'file':
				// close any open file connections
				if (is_resource($this->_fp))
				{
					fclose($this->_fp);
				}
				break;
			case 'postgresql':
				// nothing needed here right now
				break;
			default:
				throw new ModellTemplateException($this->_storage,
					ModellTemplateException::INVALID_STORAGE_TYPE);
		}
	}

	/**
	 * Evaluates a template data
	 *
	 * @since version 1.0.0
	 */
	protected function _doEvaluate()
	{
		// check for a evaluated cache
		if ($this->_disableEvaluatedCache === false &&
			isset($this->_evaluatedCache[$this->_uid]))
		{
			// cached copy found use it
			$this->_data = $this->_evaluatedCache[$this->_uid];
			return;
		}

		// make all args part of this functions scope so they can be evaled
		// if there is a collision, don't overwrite the existing variable
		extract($this->_args, EXTR_SKIP);

		// if true then ONLY eval
		// otherwise decode and put withing a string
		if ($this->_onlyEval === true)
		{
			// no variable encoding or decoding
			$this->_data = eval($this->_data);

			// if $this->_returnVars is not empty
			if (!empty($this->_returnVars))
			{
				foreach ($this->_returnVars as $var)
				{
					// make sure $var is a string (very important)
					// check if ${$var} is set (i.e ${'myvariable'})
					if (is_string($var) && isset(${$var}))
					{
						// if the variable exists add it to the data array
						// this is then used when returning from the method
						$this->_dataArray[$var] = ${$var};
					}
				}
			}
		}
		else
		{
			// encode data
			$this->_doVariablesEncode();

			// eval the data pass thru a string
			$this->_data = eval(
				// stripslashes is needed to remove any uneeded slashes
				// from the output
				'return stripslashes("'.$this->_data.'");'
			);

			// decode the data
			$this->_doVariablesDecode();
		}

		if ($this->_disableEvaluatedCache === false && isset($this->_data) &&
			strlen($this->_data) <= self::MAX_CACHE_SIZE)
		{
			// make sure there is a UID
			if (empty($this->_uid))
			{
				throw new MainException('uid',
					MainException::PARAM_EMPTY);
			}
			$this->_evaluatedCache[$this->_uid] = $this->_data;
		}
	}

	/**
	 * Decodes template data
	 * The data is used is from $this->_data
	 *
	 * @see ModellTemplate::OTHER_VAR_REPLACEMENT
	 * @since version 1.0.0
	 */
	protected function _doVariablesDecode()
	{
		// process and remake fake variables
		$this->_data = preg_replace('/'.self::OTHER_VAR_REPLACEMENT.
			'([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'.
			self::OTHER_VAR_REPLACEMENT.'/i', '\$$1', $this->_data);
	}

	/**
	 * Encodes template data
	 *
	 * The data is used is from $this->_data
	 * Makes raw data safe for evaluating
	 *
	 * @see ModellTemplate::OTHER_VAR_REPLACEMENT
	 * @see ModellTemplate::PHP_VAR_REPLACEMENT
	 * @since version 1.0.0
	 */
	protected function _doVariablesEncode()
	{
		// add slashes
		$this->_data = addslashes($this->_data);

		/*
			addslashes adds back-slashes (\) before any arrays in our templates
			i.e {$myarray['var']} becomes {$myarray[\'var\']} this kills the script.
			This regex removes all the back-slashes on an array
			Also $ are replaced temporarly with self::PHP_VAR_REPLACEMENT
			(both in front and at the end of the variable)
			This prevents the following regex from removing our PHP variables
		*/
		$this->_data = preg_replace_callback(
			'!\{\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[.[^}]+\])?\}!i',
			create_function(
				// single quotes are essential here for the temp function
				'$matches',
				'if (count($matches) > 2)
				{
					return \'{'.self::PHP_VAR_REPLACEMENT.'\'.$matches[1].
						stripslashes($matches[2]).\''.
						self::PHP_VAR_REPLACEMENT.'}\';
				}
				else
				{
					return \'{'.self::PHP_VAR_REPLACEMENT.'\'.$matches[1].\''.
						self::PHP_VAR_REPLACEMENT.'}\';
				}'
			), $this->_data
		);

		/*
			replace all fake variables
			for things like XSL that use variables in the same format as PHP
			we replace them temporary
		*/
		$this->_data = preg_replace_callback(
			'![a-zA-Z0-9_\x7f-\xff]?\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[a-zA-Z0-9_\x7f-\xff]?!i',
			create_function(
				// single quotes are essential here for the temp function
				'$matches',
				'return \''.self::OTHER_VAR_REPLACEMENT.'\'.$matches[1].\''.
					self::OTHER_VAR_REPLACEMENT.'\';'
			), $this->_data
		);

		/*
			replace the valid PHP variables back so they'll get parsed by eval
		*/
		$this->_data = preg_replace_callback(
			/*
				note the addcslashes are because * is a special char in regex
				so we add a backslash to it before inserting it into the regex
			*/
			'!\{'.addcslashes(self::PHP_VAR_REPLACEMENT, '*').
				'([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[.[^}]+\])?'.
				addcslashes(self::PHP_VAR_REPLACEMENT, '*').'\}!i',
			create_function(
				// single quotes are essential here for the temp function
				'$matches',
				'if (count($matches) > 2)
				{
					return "{\$".$matches[1].stripslashes($matches[2])."}";
				}
				else
				{
					return "{\$".$matches[1]."}";
				}'
			), $this->_data
		);

		/*
			we check if there are any constants, and if so replace it
			Constants are defined with a @ as a prefix i.e @DATABASE
			NOTE: Constants MUST be in braces {} i.e {@DATABASE}
		*/
		$this->_data = preg_replace_callback(
			'/\{@([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s',
			create_function(
				// single quotes are essential here for the temp function
				'$matches',
				'$constants = get_defined_constants();
				if (isset($constants[$matches[1]]))
				{
					return addslashes($constants[$matches[1]]);
				}
				else
				{
					return $matches[0];
				}'
			), $this->_data
		);
	}

	/**
	 * Retrives the raw data from the active storage
	 *
	 * @since 1.0.0
	 */
	protected function _getRawData()
	{
		// check for a data cache
		if ($this->_disableDataCache === false &&
			isset($this->_dataCache[$this->_uid]))
		{
			// cached copy found use it
			$this->_data = $this->_dataCache[$this->_uid];
			return;
		}

		// always start empty
		$this->_data = '';
		switch ($this->_storage)
		{
			case 'file':
				/*
					Reason for using fopen() over file_get_content()
					the reason I'm using fopen instead of file_get_content() is
					that when ModellTemplate::open() is called it may be being
					referenced by a write operation and not just a read operation.
					Sure perhaps file_get_content() is faster but at the time of
					writing this fopen() suits my needs better
				*/
				// read the file in
				if (is_resource($this->_fp))
				{
					while (!feof($this->_fp))
					{
						// fill $this->_data with the files content
						$this->_data .= fread($this->_fp, self::FILE_BUFFER);
					}
				}
				break;
			case 'postgresql':
				$this->_data = $this->_db->record('Template');
				break;
			default:
				throw new ModellTemplateException($this->_storage,
					ModellTemplateException::INVALID_STORAGE_TYPE);
		}

		/*
			strip off any starting and ending php tags
			i.e <?php and ?>
			(<? are NOT striped)
			both start and end tags ARE optional within the templates
			NOTE: this will strip the data from the START and END of the
			template ONLY, not from within it.
			This is designed manly for storing the template via files
			so that you can edit them directly and have nice highlighting
		*/
		// strip start tag
		// if need be allow this to be deselected
		$this->_data = preg_replace('/^((\s*)?<\?php(\n)?)?/i', '',
			$this->_data);

		// strip end tag
		$this->_data = preg_replace('/((\n)?\?>(\s*)?)?$/i', '', $this->_data);

		// get the keys and values from $this->_replacements and replace them
		$this->_data = str_ireplace(array_keys($this->_replacements),
			array_values($this->_replacements), $this->_data);

		// if it's not empty cache it
		if ($this->_disableDataCache === false && isset($this->_data) &&
			strlen($this->_data) <= self::MAX_CACHE_SIZE)
		{
			// make sure there is a UID
			if (empty($this->_uid))
			{
				throw new MainException('uid',
					MainException::PARAM_EMPTY);
			}
			$this->_dataCache[$this->_uid] = $this->_data;
		}
	}

	/**
	 * Opens a template
	 *
	 * If we only want to check if the template exists set $checkExists to true
	 *
	 * {$checkExists} check if the file exists ONLY
	 *
	 * Returns true or false indicating if the file exists or not
	 *
	 * @param bool $checkExists [optional]
	 * @return bool
	 * @since 1.0.0
	 */
	protected function _doOpen($checkExists = false)
	{
		switch ($this->_storage)
		{
			case 'file':
				// the full location of the file
				$file = $this->_fileDirectory.$this->_fileName;

				// check if file exists (active language code)
				if (!file_exists($file))
				{
					// change to the default language code and test again
					// the default code is lowercased for the filename
					$file = preg_replace('/\.'.$this->_activeLanguageCode.
						'\./i', '.'.strtolower($this->_defaultLanguageCode).
						'.', $file);

					// check if file exists (default language code)
					if (!file_exists($file))
					{
						if ($checkExists === true)
						{
							// if we are just checking the existance return false
							return false;
						}
						else
						{
							// nither files exist error out
							throw new ModellTemplateException($file,
								ModellTemplateException::TEMPLATE_NOT_EXIST);
						}
					}
				}

				// if we got this far then one of them exist
				if ($checkExists === true)
				{
					return true;
				}

				// check if the file is readable
				if (is_readable($file) === false)
				{
					throw new IoException($file,
						IoException::FILE_READ_DENIED);
				}

				// check if the file is writeable
				/*
				disable until I can fix this
				if (is_writable($file) === false)
				{
					throw new IoException($file,
						IoException::FILE_WRITE_DENIED);
				}*/

				/**
				 * last modified time
				 */
				$this->_modifiedDate = filemtime($file);

				/*
					Reason for using fopen() over file_get_content()
					the reason I'm using fopen instead of file_get_content() is
					that when ModellTemplate::open() is called it may be being
					referenced by a write operation and not just a read operation.
					Sure perhaps file_get_content() is faster but at the time of
					writing this fopen() suits my needs better
				*/
				// open the file for reading only
				$this->_fp = fopen($file, 'r');
				break;
			case 'postgresql':
				/*
					check if the record exists
					this query will check for the active language code
					if that isn't found it'll then check for the default
					language code
					using queryOnce() to retrive a single record
				*/
				$this->_db->queryOnce('
					SELECT "ID", "Template", "ModifiedDate"
					FROM "'.$this->_databaseTable.'"
					WHERE "Name" = \''.$this->_name.'\'
					AND "LanguageCode" = \''.$this->_activeLanguageCode.'\''.
					($this->_disabled === false?' AND "Disabled" = false':'').'

					UNION ALL

					SELECT "ID", "Template", "ModifiedDate"
					FROM "'.$this->_databaseTable.'"
					WHERE NOT EXISTS
					(
						SELECT "ID", "Template", "ModifiedDate"
						FROM "'.$this->_databaseTable.'"
						WHERE "Name" = \''.$this->_name.'\'
						AND "LanguageCode" = \''.$this->_activeLanguageCode.'\''.
						($this->_disabled === false?' AND "Disabled" = false':'').'
					)
					AND "Name" = \''.$this->_name.'\'
					AND "LanguageCode" = \''.$this->_defaultLanguageCode.'\''.
					($this->_disabled === false?' AND "Disabled" = false':'')
				);

				// see if the template exists
				if ($this->_db->record('ID') === false)
				{
					if ($checkExists === true)
					{
						// if we are just checking the existance return false
						return false;
					}
					else
					{
						// nither entries exist error out
						throw new ModellTemplateException($this->_name,
							ModellTemplateException::TEMPLATE_NOT_EXIST);
					}
				}

				// if we got this far then one of them exist
				if ($checkExists === true)
				{
					return true;
				}

				/**
				 * @todo finish the database access
				 * return last modified date
				 */
				break;
			default:
				throw new ModellTemplateException($this->_storage,
					ModellTemplateException::INVALID_STORAGE_TYPE);
		}

		// true is always returned if we've gotten this far
		// this is for proper coding standards
		return true;
	}
}

?>
