<?php
/**
 * PostgreSQL class for PHP 5.0.0+
 *
 * Verified to work with PHP 5.0.0 and PostgreSQL 7.3.2
 *
 * Example:
 * <code>
 * $dbInfo = array(
 *    'database' => 'myDatabase',
 *    'host' => 'localhost',
 *    'user' => 'postgresql',
 *    'password' => 'password',
 *    'port' => 5432
 * );
 * try
 * {
 *    $db = new PostgreSql($dbInfo);
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
 * @package PostgreSql
 * @access private
 * @see Database
 * @see DatabaseException
 * @todo change the way data is returned, allowing for multiple connections
 * while still retiving all the data
 * update method names to standard format
 */
class PostgreSql implements Database
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
		if (empty($tableName))
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
			SELECT COUNT(*) AS "num"
			FROM "'.$tableName.'"'.
			$where
		);
		return $this->record('num');
	}

	/**
	 * Set the Database
	 * 
	 * @param string $database
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

		// if there is a open connection close it and start a new one
		$this->_disconnect();
		// set new database
		$this->_database = $database;
		// connect with new database
		$this->_checkConnection();
	}

	/**
	 * Returns the last inserted ID
	 * This will ONLY retive the LAST ID
	 * call it before running other queries
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

		// get the last id
		$this->queryOnce('SELECT lastval() as lastid');
		return $this->record('lastid');
	}

	/**
	 * Returns the last OID
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function lastOid()
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}
		// get oid
		return pg_last_oid($this->_results);
	}

	/**
	 * Lists all the columns (fields) from the last query
	 *
	 * @return array
	 * @since version 1.0.0
	 */
	public function listColumns()
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}
		$num = $this->numFields();
		$columns = array();
		for ($i = 0; $i < $num; $i++)
		{
			$columns[] = pg_field_name($this->_results, $i);
		}

		return $columns;
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
		$removeDatabases = array('pgsql', 'template1', 'template0');
		if (!is_array($userRemoveDatabases))
		{
			throw new MainException(gettype($userRemoveDatabases),
				MainException::TYPE_ARRAY);
		}
		$tmp = array_merge($removeDatabases, $userRemoveDatabases);
		$tmpDatabases = array();

		$dbInfo = array(
			'database' => 'template1',
			'host' => $this->_host,
			'user' => $this->_user,
			'password' => $this->_password,
			'port' => $this->_port
		);

		// Create a new object, otherwise (with the db being changed)
		// it'll reset the connection and further access will error
		$db = new PostgreSql($dbInfo);
		$db->query('
			SELECT *
			FROM "pg_database"'
		);
		while ($db->nextRecord())
		{
			if (!in_array($db->record('datname'), $tmp))
			{
			    $tmpDatabases[] = $db->record('datname');
			}
		}
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
	 * @see PostgreSql::listDatabases()
	 * @since version 1.0.0.0
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

		$dbInfo = array(
			'database' => $database,
			'host' => $this->_host,
			'user' => $this->_user,
			'password' => $this->_password,
			'port' => $this->_port
		);

		// Create a new object, otherwise (with the db being changed)
		// it'll reset the connection and further access will error
		$db = new PostgreSql($dbInfo);
		$db->query('
			SELECT "tablename"
			FROM "pg_tables"
			WHERE "tablename" !~* \'pg_*\'
			ORDER BY "tablename"
		');
		while ($db->nextRecord())
		{
			if (!in_array($db->record('tablename'), $tmp))
			{
			    $tmpTables[] = $db->record('tablename');
			}
		}
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
	 * @see PostgreSql::query()
	 * @see PostgreSql::queryOnce()
	 * @since version 1.0.0
	 */
	public function nextRecord($freeResults = true)
	{
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

		// because of the check later set this to false
		// this way when there are no more records it'll break out
		$this->_record = false;

		if ($this->_row < pg_num_rows($this->_results))
		{
			$this->_record = pg_fetch_assoc($this->_results, $this->_row);

			// increment counter
			$this->_row += 1;
		}

		if ($this->_record === false)
		{
			if ($freeResults === true)
			{
				if (pg_free_result($this->_results) === false)
				{
					throw new DatabaseException('Unable to free results');
				}
				$this->_results = null;
				// false is returned here so it doesn't continue looping
			}
			return false;
		}

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
		// get number of fields
		return pg_num_fields($this->_results);
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
		// get number of rows
		return pg_num_rows($this->_results);
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
		$this->_results = pg_query($this->_connection, $this->_query);
		if ($this->_results === false)
		{
			throw new DatabaseException($this->_query.' '.
				pg_last_error($this->_connection),
				DatabaseException::QUERY_INVALID);
		}
	}

	/**
	 * Returns the first results of a query
	 * 
	 * @param string $query
	 * @param bool $freeResults [optional]
	 * @see PostgreSql::query()
	 * @see PostgreSql::nextRecord()
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
			if (pg_free_result($this->_results) === false)
			{
				throw new DatabaseException('Unable to free results');
			}
			$this->_results = null;
		}
	}

	/**
	 * Gets the next value from the sequence and inserts the row with
	 * that value instead of allowing it to default. Returns the Inserted ID
	 * NOTE: You should use this for any inserts that you want the last
	 * inserted ID from, don't use if you don't need the info.
	 * 
	 * @param string $query
	 * @param string $idField [optional]
	 * @param string $sequenceName [optional]
	 * @param string $table [optional]
	 * @param string $replacementText [optional]
	 * @return integer
	 * @see PostgreSql::query()
	 * @see PostgreSql::queryOnce()
	 * @since version 1.0.0
	 */
	public function queryIdInsert($query, $idField = 'ID',
		$sequenceName = '', $table = '', $replacementText = '[SequenceID]'
	)
	{
		if (empty($sequenceName))
		{
			// if no table given then it gets it from the query
			// this is only run if $sequenceName is empty so if you give
			// it a value no need to give a table
			if (empty($table))
			{
				$aryMatches = array();
				// if this doesn't match anything it will error at the query
				// unless you have a secquence that starts with _
				if (preg_match('/INTO\s*"?(\S[^\/\\"\']*)/is',
					$query, $aryMatches)
				)
				{
				 	// set the table
					$table = $aryMatches[1];
				}
			}
			// no sequence name given attempt to use the default one
			$sequenceName = $table.'_'.$idField.'_seq';
		}

		// get the nextvalue
		$this->queryOnce('SELECT nextval(\'"'.$sequenceName.'"\')');
		if ($this->record('nextval') == '')
		{
			throw new DatabaseException('Could not retrive next value',
				DatabaseException::RECORDS_UNKNOWN);
		}
		$nextSequence = $this->record('nextval');

		// add the next ID into the query
		$query = str_replace($replacementText, $nextSequence, $query);
		$this->query($query);
		return $nextSequence;
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
		if (is_null($this->_record[$field]))
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
	 * @see PostgreSql::nextRecord()
	 * @since version 1.0.0
	 */
	public function seek($position)
	{
		// verify that a query was run
		if (is_resource($this->_results) === false)
		{
			throw new DatabaseException('',
				DatabaseException::QUERY_NOT_RUN);
		}

		if ($position < 1 || $position > pg_num_rows($this->_results))
		{
			throw new DatabaseException('Record not found',
				DatabaseException::RECORD_UNKNOWN);
		}
		// set position
		$this->_row = $position;
		// move to that record
		return $this->nextRecord();
	}

	/**
	 * Set client encoding
	 *
	 * UNICODE
	 *
	 * @param string $encoding
	 * @return integer
	 * @since version 1.0.0
	 */
	public function setEncoding($encoding)
	{
		return pg_set_client_encoding($this->_connection, $encoding);
	}

	/**
	 * Class constructor
	 *
	 * Stores the settings connects to the database
	 * Accepts an array as it's param
	 * Example:
	 * <code>
	 * $dbInfo = array(
	 *    'database' => 'myDatabase',
	 *    'host' => 'localhost',
	 *    'user' => 'postgresql',
	 *    'password' => 'password',
	 *    'port' => 5432
	 *    'persistent' => false
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

		// default to 5432 (postgresql default port)
		$this->_port = 5432;
		// set port if not empty
		if (!empty($dbInfo['port']))
		{
			$this->_port = $dbInfo['port'];
		}

		// leave empty if not set
		$this->_user = '';
		if (!empty($dbInfo['user']))
		{
			$this->_user = $dbInfo['user'];
		}

		// leave empty if not set
		$this->_password = '';
		if (!empty($dbInfo['password']))
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
	 * database name
	 * @var string
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
	 * @since version 1.0.0
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
		// make sure there is always a valid database
		if (empty($this->_database))
		{
			throw new DatabaseException('',
				DatabaseException::DATABASE_EMPTY);
		}

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
			$this->_connection = pg_pconnect('host=\''.$this->_host.'\' port=\''.
				$this->_port.'\' dbname=\''.$this->_database.'\' user=\''.$this->_user.
				'\' password=\''.$this->_password.'\'');
		}
		else
		{
			$this->_connection = pg_connect('host=\''.$this->_host.'\' port=\''.
				$this->_port.'\' dbname=\''.$this->_database.'\' user=\''.$this->_user.
				'\' password=\''.$this->_password.'\'');
		}

		// verify that the connection is active
		if (is_resource($this->_connection) === false ||
			pg_connection_status($this->_connection) == PGSQL_CONNECTION_BAD
		)
		{
			throw new DatabaseException('',
				DatabaseException::CONNECTION_FAILED);
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
			if (pg_close($this->_connection) === false)
			{
				throw new DatabaseException('',
					DatabaseException::CONNECTION_INVALID);
			}
		}
		// clear the connection string
		unset($this->_connection);
		$this->_connection = null;
	}
}

?>
