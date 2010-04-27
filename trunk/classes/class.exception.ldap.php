<?php
/**
 * LDAP Exceptions class
 *
 * LDAP Exceptions error code range
 * 0 - 150
 *
 * Result code definitions are taken from
 * http://docs.sun.com/source/817-6707/resultcodes.html
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Exception
 * @access private
 * @see MainException
 */
class LdapException extends MainException
{
	/**
	 * Indicates that the LDAP operation was successful
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30482
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_SUCCESS									= 0x00;

	/**
	 * General result code indicating that an error has occurred
	 * Server might send this code if, for example, memory cannot
	 * be allocated on the server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30374
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_OPERATIONS_ERROR							= 0x01;

	/**
	 * Indicates that the LDAP client’s request does not comply with the LDAP
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30395
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_PROTOCOL_ERROR							= 0x02;

	/**
	 * Time limit on a search operation has been exceeded
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30486
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_TIMELIMIT_EXCEEDED						= 0x03;

	/**
	 * Maximum number of search results to return has been exceeded
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30457
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_SIZELIMIT_EXCEEDED						= 0x04;

	/**
	 * The result indicates that the specified attribute value
	 * is not present in the specified entry.
	 * Returned after an LDAP compare operation is completed
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30205
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_COMPARE_FALSE							= 0x05;

	/**
	 * Indicates that the specified attribute value is present in the
	 * specified entry
	 * Returned after an LDAP compare operation is completed
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30209
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_COMPARE_TRUE								= 0x06;

	/**
	 * Indicates that the server does not recognize or support the specified
	 * authentication method
	 * Returned as the result of a bind operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp34547
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_STRONG_AUTH_NOT_SUPPORTED				= 0x07;

	/**
	 * Indicates that a stronger method of authentication is required to
	 * perform the operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30477
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_STRONG_AUTH_REQUIRED						= 0x08;

	/**
	 * To LDAPv2 clients refering them to another LDAP server
	 * When sending this code to a client, the server includes a new
	 * line-delimited list of LDAP URLs that identifies another LDAP server
	 * If the client identifies itself as an LDAPv3 client in the request,
	 * an LDAP_REFERRAL result code is sent instead of this result code.
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30390
	 * Not used in LDAPv3
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_PARTIAL_RESULTS							= 0x09;

	// Next 5 new in LDAPv3
	/**
	 * Indicates that the server is referring the client to another LDAP server
	 * When sending this code to a client, the server includes a list of LDAP
	 * URLs that identify another LDAP server
	 * This result code is part of the LDAPv3
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp33141
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_REFERRAL									= 0x0a;

	/**
	 * Indicates that the look-through limit on a search operation has been exceeded
	 * The look-through limit is the maximum number of entries that the server
	 * will check when gathering a list of potential search result candidates
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30160
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_ADMINLIMIT_EXCEEDED						= 0x0b;

	/**
	 * Indicates that the specified control or matching rule is not supported
	 * by the server
	 * Maybe sent if a request includes an unsupported control or if the filter
	 * in the search request specifies an unsupported matching rule
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30516
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_UNAVAILABLE_CRITICAL_EXTENSION			= 0x0c;

	/**
	 * Indicates that confidentiality is required for the operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30213
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_CONFIDENTIALITY_REQUIRED					= 0x0d;

	/**
	 * Is used in multi-stage SASL bind operations
	 * Sent back to the client to indicate that the authentication process has
	 * not yet completed
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30446
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_SASL_BIND_INPROGRESS						= 0x0e;

	/**
	 * Indicates that the specified attribute does not exist in the entry
	 * Maybe sent if a modify request specifies the modification or removal
	 * of a non-existent attribute or if a compare request specifies a
	 * non-existent attribute
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30335
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NO_SUCH_ATTRIBUTE						= 0x10;

	/**
	 * Indicates that the request specifies an undefined attribute type.
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30521
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_UNDEFINED_TYPE							= 0x11;

	/**
	 * Indicates that an extensible match filter in a search request contained
	 * a matching rule that does not apply to the specified attribute type
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30261
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INAPPROPRIATE_MATCHING					= 0x12;

	/**
	 * Indicates that a value in the request does not comply with certain constraints
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30224
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_CONSTRAINT_VIOLATION						= 0x13;

	/**
	 * Indicates that the request attempted to add an attribute type or value
	 * that already exists
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30500
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_TYPE_OR_VALUE_EXISTS						= 0x14;

	/**
	 * Indicates that the request contains invalid syntax
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30287
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INVALID_SYNTAX							= 0x15;

	/**
	 * Indicates that the server cannot find an entry specified in the request
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30340
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NO_SUCH_OBJECT							= 0x20;

	/**
	 * Indicates that the alias is invalid
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30178
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_ALIAS_PROBLEM							= 0x21;

	/**
	 * Indicates than an invalid DN has been specified
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30282
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INVALID_DN_SYNTAX						= 0x22;

	// Next two not used in LDAPv3
	/**
	 * Indicates that the specified entry is a leaf entry
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30296
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_IS_LEAF									= 0x23;

	/**
	 * Indicates that a problem occurred when dereferencing an alias
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30173
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_ALIAS_DEREF_PROBLEM						= 0x24;

	/**
	 * Indicates that the type of credentials are not appropriate for the
	 * method of authentication used
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30256
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INAPPROPRIATE_AUTH						= 0x30;

	/**
	 * Indicates that the credentials provided in the request are invalid
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30277
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INVALID_CREDENTIALS						= 0x31;

	/**
	 * Indicates that the client has insufficient access to perform the operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30272
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INSUFFICIENT_ACCESS						= 0x32;

	/**
	 * Indicates that the server is currently too busy to perform the requested
	 * operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30196
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_BUSY										= 0x33;

	/**
	 * Indicates that the server is unavailable to perform the requested operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30511
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_UNAVAILABLE								= 0x34;

	/**
	 * Indicates that the server is unwilling to perform the requested operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30526
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_UNWILLING_TO_PERFORM						= 0x35;

	/**
	 * Indicates that the server was unable to perform the requested operation
	 * because of an internal loop
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30305
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_LOOP_DETECT								= 0x36;

	/**
	 * Indicates that server did not receive a required server-side sorting control
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30466
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_SORT_CONTROL_MISSING						= 0x3C;

	/**
	 * Indicates that the search results exceeded the range specified by the
	 * requested offsets
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30266
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_INDEX_RANGE_ERROR						= 0x3D;

	/**
	 * Indicates that the request violates the structure of the DIT
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp34327
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NAMING_VIOLATION							= 0x40;

	/**
	 * Indicates that the request specifies a new entry or a change to an
	 * existing entry that does not comply with the server’s schema
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30366
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_OBJECT_CLASS_VIOLATION					= 0x41;

	/**
	 * Indicates that the requested operation is allowed only on entries that
	 * do not have child entries
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30345
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NOT_ALLOWED_ON_NONLEAF					= 0x42;

	/**
	 * Indicates that the requested operation will affect the RDN of the entry
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30350
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NOT_ALLOWED_ON_RDN						= 0x43;

	/**
	 * indicates that the request is attempting to add an entry that already
	 * exists in the directory
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30183
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_ALREADY_EXISTS							= 0x44;

	/**
	 * Indicates that the request is attempting to modify an object class that
	 * should not be modified
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30325
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NO_OBJECT_CLASS_MODS						= 0x45;

	/**
	 * Indicates that the results of the request are too large
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30441
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_RESULTS_TOO_LARGE						= 0x46;

	// Next two for LDAPv3
	/**
	 * Indicates that the requested operation needs to be performed on multiple
	 * servers, where this operation is not permitted
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30168
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_AFFECTS_MULTIPLE_DSAS					= 0x47;

	/**
	 * Indicates than an unknown error has occurred
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30380
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_OTHER									= 0x50;

	// Used by some APIs

	/**
	 * Indicates that the LDAP SDK for C cannot establish a connection with,
	 * or lost the connection to, the LDAP server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30451
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_SERVER_DOWN								= 0x51;

	/**
	 * Indicates that an error occurred in the LDAP client
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30301
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_LOCAL_ERROR								= 0x52;

	/**
	 * Indicates that the LDAP client encountered an error when encoding the
	 * LDAP request to be sent to the server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30247
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_ENCODING_ERROR							= 0x53;

	/**
	 * Indicates that the LDAP client encountered an error when decoding the
	 * LDAP response received from the server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30243
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_DECODING_ERROR							= 0x54;

	/**
	 * Indicates that the LDAP client timed out while waiting for a response
	 * from the server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30495
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_TIMEOUT									= 0x55;

	/**
	 * Indicates that an unknown authentication method was specified
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30191
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_AUTH_UNKNOWN								= 0x56;

	/**
	 * Indicates that an error occurred when specifying the search filter
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30251
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_FILTER_ERROR								= 0x57;

	/**
	 * Indicates that the user cancelled the LDAP operation
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30543
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_USER_CANCELLED							= 0x58;

	/**
	 * Indicates that an invalid parameter was specified
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30385
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_PARAM_ERROR								= 0x59;

	/**
	 * Indicates that no memory is available
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30320
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NO_MEMORY								= 0x5a;

	// Preliminary LDAPv3 codes

	/**
	 * Indicates that the LDAP client cannot establish a connection, or has
	 * lost the connection, with the LDAP server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30218
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_CONNECT_ERROR							= 0x5b;

	/**
	 * Indicates that the LDAP client is attempting to use functionality
	 * that is not supported
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30355
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NOT_SUPPORTED							= 0x5c;

	/**
	 * Indicates that a requested LDAP control was not found
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30234
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_CONTROL_NOT_FOUND						= 0x5d;

	/**
	 * Indicates that no results were returned from the server
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30330
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_NO_RESULTS_RETURNED						= 0x5e;

	/**
	 * Indicates that there are more results in the chain of results
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30310
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_MORE_RESULTS_TO_RETURN					= 0x5f;

	/**
	 * Indicates that the LDAP client detected a loop, for example,
	 * when following referrals.
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30201
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_CLIENT_LOOP								= 0x60;

	/**
	 * Indicates that the referral hop limit was exceeded
	 * http://docs.sun.com/source/817-6707/resultcodes.html#wp30436
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_REFERRAL_LIMIT_EXCEEDED					= 0x61;

	// Custom errors

	/**
	 * Indicates that the DN does not exist in the database
	 * @var integer
	 * @since version 1.0.0
	 */
	const LDAP_DN_NOT_EXIST								= 120;

	/**
	 * Class constructor
	 * @param string msg [optional]
	 * @param integer code [optional]
	 * @since version 1.0.0
	 */
	public function __construct($msg = '', $code = 1000)
	{
		switch ($code)
		{
			case self::LDAP_SUCCESS:
				$msg = 'LDAP operation was successful';
				break;
			case self::LDAP_OPERATIONS_ERROR:
				$msg = $msg.': An error has occurred, possible cause: memory cannot '.
					'be allocated on the server';
				break;
			case self::LDAP_PROTOCOL_ERROR:
				$msg = $msg.': LDAP client’s request does not comply with the LDAP protocol version';
				break;
			case self::LDAP_TIMELIMIT_EXCEEDED:
				$msg = $msg.': Time limit on a search operation has been exceeded';
				break;
			case self::LDAP_SIZELIMIT_EXCEEDED:
				$msg = $msg.': Maximum number of search results to return has been exceeded';
				break;
			case self::LDAP_COMPARE_FALSE:
				$msg = $msg.': The specified attribute value is not present in the specified entry';
				break;
			case self::LDAP_COMPARE_TRUE:
				$msg = $msg.': The specified attribute value is present in the specified entry';
				break;
			case self::LDAP_STRONG_AUTH_NOT_SUPPORTED:
				$msg = $msg.': The server does not recognize or support the specified authentication method';
				break;
			case self::LDAP_STRONG_AUTH_REQUIRED:
				$msg = $msg.': A stronger method of authentication is required to perform the operation';
				break;
			case self::LDAP_PARTIAL_RESULTS:
				$msg = $msg.': To LDAPv2 clients refering them to another LDAP server';
				break;
			case self::LDAP_REFERRAL:
				$msg = $msg.': The server is referring the client to another LDAP server';
				break;
			case self::LDAP_ADMINLIMIT_EXCEEDED:
				$msg = $msg.': The look-through limit on a search operation has been exceeded';
				break;
			case self::LDAP_UNAVAILABLE_CRITICAL_EXTENSION:
				$msg = $msg.': The specified control or matching rule is not supported by the server';
				break;
			case self::LDAP_CONFIDENTIALITY_REQUIRED:
				$msg = $msg.': Confidentiality is required for the operation';
				break;
			case self::LDAP_SASL_BIND_INPROGRESS:
				$msg = $msg.': Multi-stage SASL bind operations';
				break;
			case self::LDAP_NO_SUCH_ATTRIBUTE:
				$msg = $msg.': The specified attribute does not exist in the entry';
				break;
			case self::LDAP_UNDEFINED_TYPE:
				$msg = $msg.': the request specifies an undefined attribute type';
				break;
			case self::LDAP_INAPPROPRIATE_MATCHING:
				$msg = $msg.': Extensible match filter in a search request contained a matching rule that does not apply to the specified attribute type';
				break;
			case self::LDAP_CONSTRAINT_VIOLATION:
				$msg = $msg.': The request does not comply with certain constraints';
				break;
			case self::LDAP_TYPE_OR_VALUE_EXISTS:
				$msg = $msg.': The request attempted to add an attribute type or value that already exists';
				break;
			case self::LDAP_INVALID_SYNTAX:
				$msg = $msg.': The request contains invalid syntax';
				break;
			case self::LDAP_NO_SUCH_OBJECT:
				$msg = $msg.': The server cannot find an entry specified in the request';
				break;
			case self::LDAP_ALIAS_PROBLEM:
				$msg = $msg.': The alias is invalid';
				break;
			case self::LDAP_INVALID_DN_SYNTAX:
				$msg = $msg.': An invalid DN has been specified';
				break;
			case self::LDAP_IS_LEAF:
				$msg = $msg.': The specified entry is a leaf entry';
				break;
			case self::LDAP_ALIAS_DEREF_PROBLEM:
				$msg = $msg.': A problem occurred when dereferencing an alias';
				break;
			case self::LDAP_INAPPROPRIATE_AUTH:
				$msg = $msg.': The type of credentials are not appropriate for the method of authentication used';
				break;
			case self::LDAP_INVALID_CREDENTIALS:
				$msg = $msg.': The credentials provided in the request are invalid';
				break;
			case self::LDAP_INSUFFICIENT_ACCESS:
				$msg = $msg.': The client has insufficient access to perform the operation';
				break;
			case self::LDAP_BUSY:
				$msg = $msg.': The server is currently too busy to perform the requested operation';
				break;
			case self::LDAP_UNAVAILABLE:
				$msg = $msg.': The server is unavailable to perform the requested operation';
				break;
			case self::LDAP_UNWILLING_TO_PERFORM:
				$msg = $msg.': The server is unwilling to perform the requested operation';
				break;
			case self::LDAP_LOOP_DETECT:
				$msg = $msg.': The server was unable to perform the requested operation because of an internal loop';
				break;
			case self::LDAP_SORT_CONTROL_MISSING:
				$msg = $msg.': The server did not receive a required server-side sorting control';
				break;
			case self::LDAP_INDEX_RANGE_ERROR:
				$msg = $msg.': The search results exceeded the range specified by the requested offsets';
				break;
			case self::LDAP_NAMING_VIOLATION:
				$msg = $msg.': The request violates the structure of the DIT';
				break;
			case self::LDAP_OBJECT_CLASS_VIOLATION:
				$msg = $msg.': The request specifies a new entry or a change to an existing entry that does not comply with the server’s schema';
				break;
			case self::LDAP_NOT_ALLOWED_ON_NONLEAF:
				$msg = $msg.': The requested operation is allowed only on entries that do not have child entries';
				break;
			case self::LDAP_NOT_ALLOWED_ON_RDN:
				$msg = $msg.': The requested operation will affect the RDN of the entry';
				break;
			case self::LDAP_ALREADY_EXISTS:
				$msg = $msg.': The request is attempting to add an entry that already exists in the directory';
				break;
			case self::LDAP_NO_OBJECT_CLASS_MODS:
				$msg = $msg.': The request is attempting to modify an object class that should not be modified';
				break;
			case self::LDAP_RESULTS_TOO_LARGE:
				$msg = $msg.': The results of the request are too large';
				break;
			case self::LDAP_AFFECTS_MULTIPLE_DSAS:
				$msg = $msg.': The requested operation needs to be performed on multiple servers, where this operation is not permitted';
				break;
			case self::LDAP_OTHER:
				$msg = $msg.': An unknown error has occurred';
				break;
			case self::LDAP_SERVER_DOWN:
				$msg = $msg.': Cannot establish a connection with, or lost the connection to, the LDAP server';
				break;
			case self::LDAP_LOCAL_ERROR:
				$msg = $msg.': An error occurred in the LDAP client';
				break;
			case self::LDAP_ENCODING_ERROR:
				$msg = $msg.': The LDAP client encountered an error when encoding the LDAP request to be sent to the server';
				break;
			case self::LDAP_DECODING_ERROR:
				$msg = $msg.': The LDAP client encountered an error when decoding the LDAP response received from the server';
				break;
			case self::LDAP_TIMEOUT:
				$msg = $msg.': The LDAP client timed out while waiting for a response from the server';
				break;
			case self::LDAP_AUTH_UNKNOWN:
				$msg = $msg.': An unknown authentication method was specified';
				break;
			case self::LDAP_FILTER_ERROR:
				$msg = $msg.': An error occurred when specifying the search filter';
				break;
			case self::LDAP_USER_CANCELLED:
				$msg = $msg.': The user cancelled the LDAP operation';
				break;
			case self::LDAP_PARAM_ERROR:
				$msg = $msg.': An invalid parameter was specified';
				break;
			case self::LDAP_NO_MEMORY:
				$msg = $msg.': No memory is available';
				break;
			case self::LDAP_CONNECT_ERROR:
				$msg = $msg.': The LDAP client cannot establish a connection, or has lost the connection, with the LDAP server';
				break;
			case self::LDAP_NOT_SUPPORTED:
				$msg = $msg.': The LDAP client is attempting to use functionality that is not supported';
				break;
			case self::LDAP_CONTROL_NOT_FOUND:
				$msg = $msg.': A requested LDAP control was not found';
				break;
			case self::LDAP_NO_RESULTS_RETURNED:
				$msg = $msg.': No results were returned from the server';
				break;
			case self::LDAP_MORE_RESULTS_TO_RETURN:
				$msg = $msg.': There are more results in the chain of results';
				break;
			case self::LDAP_CLIENT_LOOP:
				$msg = $msg.': The LDAP client detected a loop';
				break;
			case self::LDAP_REFERRAL_LIMIT_EXCEEDED:
				$msg = $msg.': The referral hop limit was exceeded';
				break;
			case self::LDAP_DN_NOT_EXIST:
				$msg = 'The DN '.$msg.' does not exist in the current database';
				break;
			default:
				// any unknown exceptions
				// no need to do anything just pass it on
		}

		// pass everything on to the parent class
		parent::__construct($msg, $code);
	}
}

?>
