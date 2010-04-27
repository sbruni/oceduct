<?php
/**
 * Database Interface
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Database
 * @access private
 */
interface Database
{
	/**
	 * Gives the total records in the given table
	 * 
	 * @param string $tableName
	 * @param string $where [optional]
	 * @return integer
	 * @since version 1.0.0
	 */
	public function countRecords($tableName, $where = '');

	/**
	 * Set the Database
	 * 
	 * @param string $database
	 * @since version 1.0.0
	 */
	public function database($database);

	/**
	 * Gets the next available record
	 * A query MUST be run first
	 * 
	 * @param bool $freeResults [optional]
	 * @return bool
	 * @see Database::query()
	 * @see Database::queryOnce()
	 * @since version 1.0.0
	 */
	public function nextRecord($freeResults = true);

	/**
	 * Returns total number of fields in query result
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function numFields();

	/**
	 * Returns total amount of rows in query result
	 * 
	 * @return integer
	 * @since version 1.0.0
	 */
	public function numRows();

	/**
	 * Runs the query
	 * 
	 * @param string $query
	 * @since version 1.0.0
	 */
	public function query($query);

	/**
	 * Returns the first results of a query
	 * 
	 * @param string $query
	 * @param bool $freeResults [optional]
	 * @see Database::query()
	 * @see Database::nextRecord()
	 * @since version 1.0.0
	 */
	public function queryOnce($query, $freeResults = true);

	/**
	 * Gets a records data
	 * 
	 * @param mixed $field
	 * @return mixed
	 * @since version 1.0.0
	 */
	public function record($field);

	/**
	 * Goes to record X
	 * 
	 * @param integer $position
	 * @return bool
	 * @see Database::nextRecord()
	 * @since version 1.0.0
	 */
	public function seek($position);
}

?>
