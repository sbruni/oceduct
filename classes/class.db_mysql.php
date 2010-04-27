<?php
/**
 * MySQL class for PHP 5.0.0+
 *
 * Verified to work with PHP 5.0.0 and MySQL 3.23.55
 *
 * Example:
 * <code>
 * $dbInfo = array(
 *    'database' => 'myDatabase',
 *    'host' => 'localhost',
 *    'user' => 'mysql',
 *    'password' => 'password',
 *    'port' => 3306
 * );
 * try
 * {
 *    $db = new MySql($dbInfo);
 *    $db->query('
 *       SELECT "mYfield"
 *       FROM "mytable"
 *    ');
 *    while ($db->nextRecord())
 *    {
 *       print $db->record('mYfield').'<br>';
 *       // or
 *       print $db->mYfield.'<br>';
 *    }
 *
 *    $db->queryOnce('
 *       SELECT "something"
 *       FROM "yourtable"
 *       WHERE "something" = 3
 *    ');
 *    print $db->record('something');
 *    // or
 *    print $db->something;
 * }
 * catch (DatabaseException $e)
 * {
 * }
 * catch (Exception $e)
 * {
 *    print '<pre>';
 *    print_r($e);
 *    print '</pre>';
 * }
 * </code>
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package MySql
 * @access private
 * @see Database
 * @see DatabaseException
 * @todo change the way data is returned, allowing for multiple connections
 * while still retiving all the data
 * update method names to standard format
 */
class MySql implements Database
{
	/**
	 * Gives the total records in the given table
	 * 
	 * @param string $tableName
	 * @param string $where [optional]
	 * @return integer
	 * @since version 1.0.0
	 */
	public function countRecords($tableName, $where = '')
	{
		// check for empty table
		if (empty($table))
		{
			throw new DatabaseException('No table given');
		}

		// check for non empty where statement
		if (!empty($where))
		{
			// check for WHERE at the beginning of the string
			// if found remove it
			$pos = stristr($where, 'where ');
			if ($pos === 0)
			{
				// remove it
				$where = substr($where, 6);
			}
		    $where = 'WHERE '.$where;
		}

		// get the count
		$this->queryOnce('
			SELECT COUNT(*) AS `num`
			FROM `'.$tableName.'`'.
			$where
		);
		return $this->record('num');
	}

	/**
	 * Set the Database
	 * 
	 * @param string $database active database
	 * @since version 1.0.0
	 */
	public function database($database)
	{
		// no empty databases
		if (empty($database))
		{
			throw new DatabaseException('',
				DatabaseException::DATABASE_EMPTY);
		}

		// return if the database is the same
		if ($this->_database == $database)
		{
			return;
		}

		// check connection
		$this->_checkConnection();

		// only try to connect if there is a database given (with mysql
		// you don't need a database given
		if (!empty($this->_database))
		{
			// change active database
			if (mysql_select_db($this->_database, $this->_connection) === false)
			{
				throw new DatabaseException('Error ('.mysql_errno(
					$this->_connection).') '.mysql_error($this->_connection));
			}
		}
	}

	/**
	 * Returns the last inserted Unique ID
	 * This will ONLY retive the LAST ID call it before running other queries
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function lastInsertedId()
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		// check connection
		$this->_checkConnection();

		// get the last id
		return mysql_insert_id($this->_connection);
	}

	/**
	 * Gets a list of all the Databases, except the specified ones.
	 * 
	 * @param array $userRemoveDatabases [optional]
	 * @return array
	 * @since version 1.0.0
	 */
	public function listDatabases($userRemoveDatabases = array())
	{
		$removeDatabases = array('mysql');
		if (!is_array($userRemoveDatabases))
		{
			throw new MainException(gettype($userRemoveDatabases),
				MainException::TYPE_ARRAY);
		}
		$tmp = array_merge($removeDatabases, $userRemoveDatabases);
		$tmpDatabases = array();

		$dbInfo = array(
			'database' => '',
			'host' => $this->_host,
			'user' => $this->_user,
			'password' => $this->_password,
			'port' => $this->_port
		);

		// Create a new object, otherwise (with the db being changed)
		// it'll reset the connection and further access will error
		$db = new Mysql($dbInfo);
		$db->query('SHOW DATABASES');
		while ($db->nextRecord())
		{
			if (!in_array($db->record('Database'), $tmp))
			{
			    $tmpDatabases[] = $db->record('Database');
			}
		}

		// if a valid array return it
		if (is_array($tmpDatabases))
		{
			return $tmpDatabases;
		}
		return array();
	}

	/**
	 * Gets a list of tables from the specified database
	 * 
	 * @param string $database
	 * @param array $userRemoveTables [optional]
	 * @return array
	 * @see MySql::listDatabases()
	 * @since version 1.0.0
	 */
	public function listTables($database, $userRemoveTables = array())
	{
		if (!is_array($userRemoveTables))
		{
			throw new MainException(gettype($userRemoveDatabases),
				MainException::TYPE_ARRAY);
		}

		$removeTables = array();
		$tmp = array_merge($removeTables, $userRemoveTables);
		$tmpTables = array();

		// Create a new object, otherwise (with the db being changed)
		// it'll reset the connection and further access will error
		$db = new Mysql($database, $this->_host, $this->_user,
			$this->_password, $this->_port);

		// get all the tables
		$db->query('
			SHOW TABLES
			FROM `'.$database.'`
		');
		while ($db->nextRecord())
		{
			if (!in_array($db->record('Tables_in_'.$database), $tmp))
			{
			    $tmpTables[] = $db->record('Tables_in_'.$database);
			}
		}

		// return if valid array
		if (is_array($tmpTables))
		{
			return $tmpTables;
		}
		return array();
	}

	/**
	 * Gets the next available record
	 * A query MUST be run first
	 * 
	 * @param bool $freeResults [optional]
	 * @return bool
	 * @see MySql::query()
	 * @see MySql::queryOnce()
	 * @since version 1.0.0
	 */
	public function nextRecord($freeResults = true)
	{
		// check connection
		$this->_checkConnection();

		// make sure queryresult is a valid
		// NOTE: null means there aren't any more records NOT an error
		if (is_null($this->_results))
		{
			return false;
		}

		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		// empty the records
		$this->_record = array();
		// get the next row
		$this->_record = mysql_fetch_assoc($this->_results);

		// false means there aren't any more records
		if ($this->_record === false)
		{
			if ($freeResults === true)
			{
				// try to free the results
				if (mysql_free_result($this->_results) === false)
				{
					throw new DatabaseException('Unable to free results');
				}
				$this->_results = null;
			}
			return false;
		}

		// increment counter
		// place here so we get the correct count
		$this->_row += 1;

		// if record isn't an array then there is a problem
		if (!is_array($this->_record))
		{
			throw new DatabaseException(pg_last_error($this->_connection).
				' Problem retriving records',
				DatabaseException::RECORDS_UNKNOWN);
		}

		// true will make it loop again
		return true;
	}

	/**
	 * Returns total number of fields in query result
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function numFields()
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		// check connection
		$this->_checkConnection();

		// get number of fields
		return mysql_num_fields($this->_results);
	}

	/**
	 * Returns total amount of rows in query result
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function numRows()
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		// check connection
		$this->_checkConnection();

		// get number of rows
		return mysql_num_rows($this->_results);
	}

	/**
	 * Runs the query
	 * 
	 * @param string $query
	 * @since version 1.0.0
	 */
	public function query($query)
	{
		if (empty($query))
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_EMPTY);
		}

		// verify connection
		$this->_checkConnection();

		// set global variables
		$this->_query = $query;
		$this->_row = 0;

		// try query
		$this->_results = null;
		$this->_results = mysql_query($query, $this->_connection);
		if ($this->_results === false)
		{
			throw new DatabaseException($this->_query.' ('.mysql_errno($this->_connection).
				') '.mysql_error($this->_connection),
				DatabaseException::QUERY_INVALID);
		}
	}

	/**
	 * Returns the first results of a query
	 * 
	 * @param string $query the SQL query
	 * @param bool $freeResults [optional]
	 * @see MySql::query()
	 * @see MySql::nextRecord()
	 * @since version 1.0.0
	 */
	public function queryOnce($query, $freeResults = true)
	{
		// run query
		$this->query($query);

		// get next record
		if ($this->nextRecord($freeResults) === false)
		{
			return;
		}

		if ($freeResults === true)
		{
			// try to free the results
			if (mysql_free_result($this->_results) === false)
			{
				throw new DatabaseException('Unable to free results');
			}
			$this->_results = null;
		}
	}

	/**
	 * Gets a records data
	 * 
	 * @param mixed $field
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function record($field)
	{
		if (isset($this->_record[$field]) && is_null($this->_record[$field]))
		{
			return null;
		}
		if (!isset($this->_record[$field]) || $this->_record[$field] == '')
		{
			return false;
		}
		return $this->_record[$field];
	}

	/**
	 * Goes to record X
	 * 
	 * @param integer $position
	 * @return bool
	 * @see MySql::nextRecord()
	 * @since version 1.0.0
	 */
	public function seek($pos)
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		// check connection
		$this->_checkConnection();

		// lookup the row and move to the record
		if (mysql_data_seek($this->_results, $pos) === false)
		{
			throw new DatabaseException('Record not found',
				DatabaseException::RECORD_UNKNOWN);
		}

		// set postion
		$this->_row = $pos;
	}

	/**
	 * Class constructor
	 * Stores the settings connects to the database
	 * Accepts an array as it's param
	 * Example:
	 * <code>
	 * $dbInfo = array(
	 *    'database' => 'myDatabase',
	 *    'host' => 'localhost',
	 *    'user' => 'mysql',
	 *    'password' => 'password',
	 *    'port' => 3306
	 * );
	 * </code>
	 * NOTE: All array elements are optional
	 * an empty array is allowed as is no param
	 *
	 * @param array $dbInfo [optional]
	 * @since version 1.0.0
	 */
	public function __construct($dbInfo = array())
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();
		// check for DatabaseException
		if (in_array('DatabaseException', $declaredClasses) === false)
		{
			throw new MainException('DatabaseException: class not found',
				MainException::INVALID_PARAM);
		}

		if (!is_array($dbInfo))
		{
			throw new MainException(gettype($dbInfo),
				MainException::TYPE_ARRAY);
		}

		// retive all the settings
		$this->_database = '';
		if (!empty($dbInfo['database']))
		{
			// user can change it later with the $this->database() method
			$this->_database = $dbInfo['database'];
			// the check for an empty database is does within the
			// _checkConnection() method
		}

		// default to localhost
		$this->_host = 'localhost';
		// if host is set (even if empty) set host to that
		if (isset($dbInfo['host']))
		{
			$this->_host = $dbInfo['host'];
		}

		// default to 3306 (mysql default port)
		$this->_port = 3306;
		// set port if not empty
		if (!empty($dbInfo['port']))
		{
			$this->_port = $dbInfo['port'];
		}

		// leave empty if not set
		$this->_user = '';
		if (!empty($dbInfo['port']))
		{
			$this->_user = $dbInfo['user'];
		}

		// leave empty if not set
		$this->_password = '';
		if (!empty($dbInfo['port']))
		{
			$this->_password = $dbInfo['password'];
		}

		// persistent connection
		// default to true
		$this->_persistent = true;
		if (isset($dbInfo['persistent']) && is_bool($dbInfo['persistent']))
		{
			$this->_persistent = $dbInfo['persistent'];
		}
	}

	/**
	 * Class Deconstructor
	 * 
	 * @since version 1.0.0
	 */
	public function __destruct()
	{
		$this->_disconnect();
	}

	/**
	 * Get overloading
	 * 
	 * @param mixed $field
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function __get($field)
	{
		return $this->record($field);
	}

	/**
	 * @var string database name
	 * @since version 1.0.0
	 */
	private $_database;
	/**
	 * @var string database server hostname/ip
	 * @since version 1.0.0
	 */
	private $_host;
	/**
	 * @var integer database sever port
	 * @since version 1.0.0
	 */
	private $_port;
	/**
	 * @var string database user
	 * @since version 1.0.0
	 */
	private $_user;
	/**
	 * @var string database password
	 * @since version 1.0.0
	 */
	private $_password;
	/**
	 * Use persistent connection
	 * Default is true
	 * @var bool
	 * @since version 1.0.0.0
	 */
	private $_persistent;
	/**
	 * @var array tempoary records
	 * @since version 1.0.0
	 */
	private $_record;
	/**
	 * @var resource the returned resource handler
	 * @since version 1.0.0
	 */
	private $_results;
	/**
	 * @var string the given SQL query
	 * @since version 1.0.0
	 */
	private $_query;
	/**
	 * @var integer the current row
	 * @since version 1.0.0
	 */
	private $_row;

	/**
	 * @var resource the active connection resource
	 * @since version 1.0.0
	 */
	private $_connection;

	/**
	 * Checks the current connection if there is one
	 * nothing happens if there is no active connection it'll
	 * attempt to connect
	 * 
	 * @since version 1.0.0
	 */
	private function _checkConnection()
	{
		if (is_resource($this->_connection) === false)
		{
			// no active connection reconnect
			$this->_connect();
		}
	}

	/**
	 * Connects to the (database) server
	 * 
	 * @since version 1.0.0
	 */
	private function _connect()
	{
		// persistent connection can lessen server load
		if ($this->_persistent === true)
		{
			$this->_connection = mysql_pconnect($this->_host.':'.$this->_port,
				$this->_user, $this->_password);
		}
		else
		{
			$this->_connection = mysql_connect($this->_host.':'.$this->_port,
				$this->_user, $this->_password);
		}

		// verify that the connection is active
		if ($this->_connection == false ||
			is_resource($this->_connection) === false
		)
		{
			throw new DatabaseException('',
				DatabaseException::CONNECTION_FAILED
			);
		}

		// only try to connect if there is a database given (with mysql
		// you don't need a database given
		if (!empty($this->_database))
		{
			// try to connect to the database
			if (mysql_select_db($this->_database, $this->_connection) === false)
			{
				throw new DatabaseException('Error ('.
					mysql_errno($this->_connection).') '.
					mysql_error($this->_connection));
			}
		}
	}

	/**
	 * Closes all active connections
	 * 
	 * @since version 1.0.0
	 */
	private function _disconnect()
	{
		// you can't close a persistent connection, so don't bother disconnecting
		if ($this->_persistent === true)
		{
			return;
		}

		if (is_resource($this->_connection) === true)
		{
			if (mysql_close($this->_connection) === false)
			{
				throw new DbDatabaseException('',
					DatabaseException::CONNECTION_INVALID);
			}
		}
		// clear the connection string
		unset($this->_connection);
		$this->_connection = null;
	}
}

?>
