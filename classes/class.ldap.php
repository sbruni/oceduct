<?php
/**
 * LDAP class
 *
 * Avaliable options: (member overloading)
 * dereference
 * timelimit
 * sizelimit
 *
 * If you need to rebind as a new user, it's recommened to create a new instance
 * of the class instead of changing users.
 *
 * $this->validateDn is NOT called in each method because it'd give a very
 * large overhead to each method. each method should compain in the dn
 * is invalid
 *
 * NOTE: @ is used throughout this class to supress warning output.
 * Otherwise you receive the warrning and the exception which is annoying.
 * All errors should throw an exception.
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package Ldap
 * @access private
 * @see MainException
 * @see LdapException
 */
class Ldap
{
	/**
	 * Adds an entry on the given RDN
	 *
	 * $data MUST be an array, the key is the attribute name
	 * and the value is the value to add with.
	 * If adding a multiple attributes the key is the attribute name
	 * and the value is an array with each value being another item
	 *
	 * Example: array(
	 *    'id' => 'test',
	 *    'title' => 'mytitle',
	 *    'multi' => array(
	 *        'val1', 'val2', 'etc..'
	 *    ),
	 *   'anotherone' => 'value'
	 * )
	 *
	 * @param string $rdn
	 * @param array $data
	 * @since version 1.0.0
	 * @see ldap_add()
	 */
	public function addEntry($rdn, $data)
	{
		// $data must be an array
		if (!is_array($data))
		{
			throw new MainException(gettype($data),
				MainException::TYPE_ARRAY);
		}

		// make sure we have an active connection
		$this->_connect();

		if (!@ldap_add($this->_resource, $rdn, $data))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * Adds an attribute(s) to a given entry
	 *
	 * $data MUST be an array, the key is the attribute name
	 * and the value is the value to add with.
	 * If adding a multiple attributes the key is the attribute name
	 * and the value is an array with each value being another item
	 *
	 * Example: array(
	 *    'id' => 'test',
	 *    'title' => 'mytitle',
	 *    'multi' => array(
	 *        'val1', 'val2', 'etc..'
	 *    ),
	 *   'anotherone' => 'value'
	 * )
	 *
	 * @param string $rdn
	 * @param array $data
	 * @since version 1.0.0
	 * @see ldap_mod_add()
	 */
	public function addAttributes($rdn, $data)
	{
		// $data must be an array
		if (!is_array($data))
		{
			throw new MainException(gettype($data),
				MainException::TYPE_ARRAY);
		}

		// make sure we have an active connection
		$this->_connect();

		if (!@ldap_mod_add($this->_resource, $rdn, $data))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * Deletes entries
	 * By default it will ONLY delete a single entry
	 * If $deleteChildren is set to true, then all child entries will be deleted
	 *
	 * @param string $rdn
	 * @param bool $deleteChildren
	 * @since version 1.0.0
	 */
	public function deleteEntry($rdn, $deleteChildren = false)
	{
		// make sure we have an active connection
		$this->_connect();

		// delete all children dns
		if ($deleteChildren === true)
		{
			$dns = $this->getChildDns($rdn);

			// if we get an empty array, it means nothing was found
			// NOT that there were no children
			// if there are no children we SHOULD still get ONE dn (the one we gave)
			if (empty($dns))
			{
				// so try deleting the given DN and see if it errors or not
				if (!@ldap_delete($this->_resource, $rdn))
				{
					$this->_throwLastError();
				}
			}

			// loop through EACH DN and delete it
			// the array is revesed so we start with the lowest child
			$dns = array_reverse($dns);
			foreach ($dns as $dn)
			{
				if (!@ldap_delete($this->_resource, $dn))
				{
					$this->_throwLastError();
				}
			}
		}
		else
		{
			// delete the single DN
			if (!@ldap_delete($this->_resource, $rdn))
			{
				$this->_throwLastError();
			}
		}
	}

	/**
	 * Deletes one or more attributes from the given dn
	 *
	 * Single valued attributes
	 * If it's a single valued attribute use
	 * <code>
	 * $attribs = array(
	 *    'attribute' => array()
	 * )
	 * </code>
	 *
	 * Multivalued attribues
	 * To delete an attribute with a specific value the value must be given
	 * <code>
	 * $attribs = array(
	 *    'attribute' => 'exact value to delete'
	 * )
	 * </code>
	 * To delete all the attributes no matter the value use
	 * <code>
	 * $attribs = array(
	 *    'attribute' => array()
	 * )
	 * </code>
	 *
	 * If the attribute is required by the objectClass you can not delete it
	 * if you want to change the value use $this->modifyAttributes()
	 * If you want to delete all attributes, delete the entry instead
	 * $this->deleteEntry()
	 *
	 * @param string $rdn
	 * @param array $attribs
	 * @since version 1.0.0
	 */
	public function deleteAttributes($rdn, $attribs)
	{
		// $attribs must be an array
		if (!is_array($attribs))
		{
			throw new MainException(gettype($attribs),
				MainException::TYPE_ARRAY);
		}

		// make sure we have an active connection
		$this->_connect();

		if (!@ldap_mod_del($this->_resource, $rdn, $attribs))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * UTF-8 enabled ldap_explode_dn()
	 *
	 * $withAttrib is used to request if the RDNs are returned with only
	 * values or their attributes as well.
	 * To get RDNs with the attributes (i.e. in attribute=value format)
	 *
	 * @param string $dn
	 * @param bool $withAttrib[optional]
	 * @return array
	 * @author gabriel@hrz.uni-marburg.de
	 * @since version 1.0.0
	 */
	public function explodeDn($dn, $withAttrib = false)
	{
		$array = array();
		$results = ldap_explode_dn($dn, $withAttrib);

		//translate hex code into ascii again
		foreach($results as $key => $value)
		{
			$array[$key] = preg_replace('/\\\([0-9A-Fa-f]{2})/e',
				"''.chr(hexdec('\\1')).''", $value);
		}
		return $array;
	}

	/**
	 * Retrives all child DNs
	 * All Dns are returned in a single array
	 * <code>
	 * array(
	 *    '0' => 'id=test,dc=test',
	 *    '1' => 'id=e1,id=test,dc=test',
	 *    '2' => 'id=e1child,id=e1,id=test,dc=test',
	 *    '3' => 'id=e2,id=test,dc=test',
	 *    '4' => 'id=e2child,id=e2,id=test,dc=test',
	 *    '5' => 'id=another,id=e2child,id=e2,id=test,dc=test'
	 * )
	 * </code>
	 *
	 * @param string $dn
	 * @return array
	 * @since version 1.0.0
	 */
	public function getChildDns($dn)
	{
		/**
		 * Use the search method to get the requested data
		 * The filter (objectClass=*) ensures we get all sub entries
		 * The scope MUST be sub so that we get all children
		 */
		return $this->_search('(objectClass=*)', 'sub', $dn, array(),
			false, true);
	}

	/**
	 * Retrive the values (from specified attributes) from a specified dn
	 * @param string $dn
	 * @param array $attributes
	 * @return array
	 * @since version 1.0.0
	 */
	public function getValues($dn, $attributes = array())
	{
		if (!$this->validateDn($dn))
		{
			throw new LdapException($dn, LdapException::LDAP_DN_NOT_EXIST);
		}

		return $this->search('(objectClass=*)', 'base', $dn, $attributes);
	}

	/**
	 * Modify an entry on the given RDN
	 *
	 * $data MUST be an array, the key is the attribute name
	 * and the value is the value to change to
	 *
	 * If changing a multiple attribute the key is the attribute name
	 * and the value is an array with each value being another item
	 * this WILL remove all old values and add the new ones in
	 * Example: array(
	 *    'id' => 'test',
	 *    'title' => 'mytitle',
	 *    'multi' => array(
	 *        'val1', 'val2', 'etc..'
	 *    ),
	 *    'anotherone' => 'value'
	 * )
	 *
	 * To delete an attribute use an empty array as the value
	 * Example: array(
	 *    'id' => 'test',
	 *    'todelete' => array(),
	 *    'anotherone' => 'value'
	 * )
	 *
	 * @param string $rdn
	 * @param array $data
	 * @since version 1.0.0
	 */
	public function modifyEntry($rdn, $data)
	{
		// $data must be an array
		if (!is_array($data))
		{
			throw new MainException(gettype($data),
				MainException::TYPE_ARRAY);
		}

		// make sure we have an active connection
		$this->_connect();

		if (!@ldap_modify($this->_resource, $rdn, $data))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * Modify specific attributes (works almost exactly like $this->modifyEntry)
	 *
	 * $data MUST be an array, the key is the attribute name
	 * and the value is the value to change to
	 *
	 * If changing a multiple attribute the key is the attribute name
	 * and the value is an array with each value being another item
	 * this WILL remove all old values and add the new ones in
	 * Example: array(
	 *    'id' => 'test',
	 *    'title' => 'mytitle',
	 *    'multi' => array(
	 *        'val1', 'val2', 'etc..'
	 *    ),
	 *    'anotherone' => 'value'
	 * )
	 *
	 * To delete an attribute use an empty array as the value
	 * Example: array(
	 *    'id' => 'test',
	 *    'todelete' => array(),
	 *    'anotherone' => 'value'
	 * )
	 *
	 * @param string $rdn
	 * @param array $data
	 * @since version 1.0.0
	 */
	public function modifyAttributes($rdn, $attribs)
	{
		// $data must be an array
		if (!is_array($attribs))
		{
			throw new MainException(gettype($attribs),
				MainException::TYPE_ARRAY);
		}

		// make sure we have an active connection
		$this->_connect();

		if (!@ldap_mod_replace($this->_resource, $rdn, $attribs))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * Cleans up LDAP to conform to a standard
	 * Multiple valued attributes are in arrays
	 * The "count" property of arrays are removed
	 * Single valued attributes are represented by strings
	 * Empty values are replaced by empty arrays for deletes
	 *
	 * @param mixed $data
	 * @param bool $root[optional]
	 * @param bool $leaveNumbered[optional]
	 * @author sean
	 * @since version 1.0.0
	 */
	public function normalize($data, $root = true, $leaveNumbered = false)
	{
		if(is_array($data))
		{
			if(!$root && (count($data) === 1 ||
				(isset($data['count']) && $data['count'] == 1)) &&
				array_key_exists(0, $data)
			)
			{
				$data = $data[0];
			}
			else
			{
				foreach($data as $key => $value)
				{
					// Remove all numbered attributes as they get returned
					// from ldap_get_attributes
					if(($root && is_numeric($key)) && $leaveNumbered === false)
					{
						unset($data[$key]);
						continue;
					}
					$data[$key] = $this->normalize($value, false);
				}

				// Take out the dumb count attribute
				if(isset($data['count']))
				{
					unset($data['count']);
				}
			}
		}

		// To delete an object need to have an empty array
		if(is_string($data) && strlen($data) === 0)
		{
			$data = array();
		}

		return $data;
	}

	/**
	 * Preforms a search on the ldap server
	 *
	 * Avaliable scopes are: base, read or one, onelevel, list
	 * or sub, subtree, search
	 *
	 * If basedn is empty the global basedn will be used
	 *
	 * $attribs is an array of attributes you want returned
	 * if $attribs is empty all "visable" attributes will be returned
	 * If operational attributes are required, they must be specified, as well
	 * as all ther other attributes you want
	 * createTimestamp
	 * modifyTimestamp
	 * creatorsName
	 * modifiersName
	 * subschemaSubentry
	 *
	 * If $dnsOnly is set to true, then an array of dn's will be returned
	 *
	 * No sorting implemented yet
	 *
	 * @param string $filter
	 * @param string $scope[optional]
	 * @param string $baseDn[optional]
	 * @param array $attribs[optional]
	 * @param bool $attrsonly[optional]
	 * @param bool $dnsOnly[optional]
	 * @return array
	 * @since version 1.0.0
	 */
	public function search($filter, $scope = 'sub', $baseDn = '',
		$attribs = array(), $attrsonly = false, $dnsOnly = false)
	{
		return $this->_search($filter, $scope, $baseDn, $attribs,
			$attrsonly, $dnsOnly);
	}

	/**
	 * Verifies that the given DN exists in the tree
	 * @param string $dn
	 * @return bool
	 * @since version 1.0.0
	 */
	public function validateDn($dn)
	{
		// search for objectclass with this dn as a base
		// this will tell if it exists
		$results = $this->search('(objectClass=*)', 'base', $dn,
			array('objectClass'));

		if (!empty($results))
		{
			return true;
		}
		return false;
	}

	/**
	 * Class constructor
	 * If $bindDn is empty an annoymous bind is used
	 *
	 * @param string $host ldap server host ip or hostname
	 * @param string $baseDn[optional] base dn to connect with
	 * @param string $bindDn[optional] user dn to bind with
	 * @param string $bindPassword[optional] user password matching above dn
	 * @param integer $port[optional]
	 * @param integer $protocolVersion[optional] LDAP protocol to use defaults to 3
	 * @since version 1.0.0
	 */
	public function __construct($host, $baseDn = '', $bindDn = '',
		$bindPassword = '', $port = 389, $protocolVersion = 3)
	{
		// verify that the needed classes are avaiable
		$declaredClasses = get_declared_classes();

		// check for DatabaseException
		if (in_array('LdapException', $declaredClasses) === false)
		{
			throw new MainException('LdapException: class not found',
				MainException::INVALID_PARAM);
		}

		$this->_host = $host;
		$this->_port = $port;
		$this->_bindDn = $bindDn;
		$this->_bindPassword = $bindPassword;
		$this->_baseDn = $baseDn;
		$this->_protocolVersion = $protocolVersion;

		$this->_sizelimit = 0;
		$this->_timelimit = 0;
		$this->_dereference = LDAP_DEREF_NEVER;

		// a connection is made when required not on initization
	}

	/**
	 * Get overloading
	 * Is case sensitive
	 * @param string $var the class option
	 * @return mixed the value of the option you requested
	 * @since version 1.0.0
	 */
	public function __get($var)
	{
		$return = '';
		switch ($var)
		{
			case 'dereference':
				$return = $this->_dereference;
				break;
			case 'sizelimit':
				$return = $this->_sizelimit;
				break;
			case 'timelimit':
				$return = $this->_timelimit;
				break;
			default:
		}
		return $return;
	}

   	/**
	 * Set overloading
	 * @param string $var the class option
	 * @param mixed $value the value to set
	 * @since version 1.0.0
	 */
	public function __set($var, $value)
	{
		switch ($var)
		{
			case 'dereference':
			var_dump($value);
				if ($value !== LDAP_DEREF_NEVER && $value !== LDAP_DEREF_SEARCHING &&
					$value !== LDAP_DEREF_FINDING && $value !== LDAP_DEREF_ALWAYS)
				{
					throw new MainException('dereference must be one of the'.
						'following constants:<br>LDAP_DEREF_NEVER, LDAP_DEREF_SEARCHING'.
						', LDAP_DEREF_FINDING, LDAP_DEREF_ALWAYS',
						MainException::INVALID_PARAM);
				}
				$this->_dereference = $value;
				break;
			case 'sizelimit':
				if (!is_numeric($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_INTEGER );
				}
				$this->_sizelimit = $value;
				break;
			case 'timelimit':
				if (!is_numeric($value))
				{
					throw new MainException(gettype($value),
						MainException::TYPE_INTEGER );
				}
				$this->_timelimit = $value;
				break;
			default:
				throw new MainException('Invalid parameter "'.$var.
					'" with value "'.$value.'"', MainException::INVALID_PARAM);
		}
	}

	// private

	/**
	 * the active connection
	 * @var resource
	 * @since version 1.0.0
	 */
	private $_resource;

	/**
	 * hostname or ip
	 * @var string
	 * @since version 1.0.0
	 */
	private $_host;

	/**
	 * port number defaults to 389
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_port;

	/**
	 * Bind DN
	 * @var string
	 * @since version 1.0.0
	 */
	private $_bindDn;

	/**
	 * Bind Password
	 * @var string
	 * @since version 1.0.0
	 */
	private $_bindPassword;

	/**
	 * Base DN
	 * @var string
	 * @since version 1.0.0
	 */
	private $_baseDn;

	/**
	 * LDAP protocol version to use, defaults to 3
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_protocolVersion;

	/**
	 * Limit the amount of entries retrived default: 0
	 * Can NOT override server-side preset timelimit but it can be set lower
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_sizelimit;

	/**
	 * Number of seconds spent on searching 0 == no limit default: 0
	 * Can NOT override server-side preset timelimit but it can be set lower
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_timelimit;

	/**
	 * DeReference default: LDAP_DEREF_NEVER
	 * Valid values are: LDAP_DEREF_NEVER, LDAP_DEREF_SEARCHING,
	 * LDAP_DEREF_FINDING, LDAP_DEREF_ALWAYS
	 * 
	 * @var integer
	 * @since version 1.0.0
	 */
	private $_dereference;

	/**
	 * Binds to the given user
	 * If no binddn is given does an annoymous bind
	 * 
	 * @param string $bindDn[optional]
	 * @param string $bindPassword[optional]
	 * @since version 1.0.0
	 */
	private function _bind($bindDn = '', $bindPassword = '')
	{
		if (!ldap_bind($this->_resource, $bindDn, $bindPassword))
		{
			$this->_throwLastError();
		}
	}

	/**
	 * Connects and binds to the ldap server
	 * 
	 * @since version 1.0.0
	 */
	private function _connect()
	{
		// if there is an active resource running use that instead of creating
		// a new connection
		if (is_resource($this->_resource))
		{
			return;
		}

		// ini connection
		$this->_resource = ldap_connect($this->_host, $this->_port);

		// change ldap options set the protocol version
		$this->_setOption(LDAP_OPT_PROTOCOL_VERSION, $this->_protocolVersion);

		// attempt an annoymous bind to verify that the server is running
		$this->_bind();

		// bind with userdn and password
		$this->_bind($this->_bindDn, $this->_bindPassword);
	}

	/**
	 * Preforms a search on the ldap server
	 *
	 * Avaliable scopes are: base, read or one, onelevel, list
	 * or sub, subtree, search
	 *
	 * If basedn is empty the global basedn will be used
	 *
	 * $attribs is an array of attributes you want returned
	 * if $attribs is empty all "visable" attributes will be returned
	 * If operational attributes are required, they must be specified, as well
	 * as all ther other attributes you want
	 * createTimestamp
	 * modifyTimestamp
	 * creatorsName
	 * modifiersName
	 * subschemaSubentry
	 *
	 * If $dnsOnly is set to true, then an array of dn's will be returned
	 *
	 * No sorting implemented yet
	 *
	 * @param string $filter
	 * @param string $scope[optional]
	 * @param string $baseDn[optional]
	 * @param array $attribs[optional]
	 * @param bool $attrsonly[optional]
	 * @param bool $dnsOnly[optional]
	 * @return array
	 * @since version 1.0.0
	 */
	private function _search($filter, $scope = 'sub', $baseDn = '',
		$attribs = array(), $attrsonly = false, $dnsOnly = false)
	{
		// make sure we have an active connection
		$this->_connect();

		// if no basedn is given (default) we use the global basedn
		if (empty($baseDn))
		{
			$baseDn = $this->_baseDn;
		}

		// we MUST have a basedn
		if (empty($baseDn))
		{
			throw new LdapException('BaseDn',
				MainException::PARAM_EMPTY);
		}

		// $attribs must be an array
		if (!is_array($attribs))
		{
			throw new MainException(gettype($attribs),
				MainException::TYPE_ARRAY);
		}

		// preforms a search based on the scope
		switch ($scope)
		{
			case 'base':
			case 'read':
				$searchResults = ldap_read($this->_resource, $baseDn, $filter,
					$attribs, $attrsonly, $this->_sizelimit,
					$this->_timelimit, $this->_dereference
				);
				break;
			case 'one':
			case 'onelevel':
			case 'list':
				$searchResults = ldap_list($this->_resource, $baseDn, $filter,
					$attribs, $attrsonly, $this->_sizelimit,
					$this->_timelimit, $this->_dereference
				);
				break;
			case 'sub':
			case 'subtree':
			case 'search':
			default:
				$searchResults = ldap_search($this->_resource, $baseDn, $filter,
					$attribs, $attrsonly, $this->_sizelimit,
					$this->_timelimit, $this->_dereference
				);
		}

		if (!is_resource($searchResults))
		{
			throw new MainException('Error in search result',
				MainException::UNKNOWN);
		}

		$data = array();
		$entry = ldap_first_entry($this->_resource, $searchResults);

		do
		{
			if (!is_resource($entry))
			{
				break;
			}

			// we want a listing of all the dn's, so only retrive the dn
			// and add it to the array
			if ($dnsOnly === true)
			{
				$data[] = ldap_get_dn($this->_resource, $entry);
				$entry = ldap_next_entry($this->_resource, $entry);
			}
			else
			{
				// we want all attributes and what they store
				$info = ldap_get_attributes($this->_resource, $entry);
				$data[ldap_get_dn($this->_resource, $entry)] = $this->normalize($info);
				$entry = ldap_next_entry($this->_resource, $entry);
			}
		}
		while ($entry !== false);

		return $data;
	}

	/**
	 * Sets a new ldap option
	 * @param integer the option to change
	 * @param mixed the value to set it to
	 * @since version 1.0.0
	 */
	private function _setOption($option, $value)
	{
		if (!is_resource($this->_resource) ||
			!ldap_set_option($this->_resource, $option, $value))
		{
			throw new LdapException('Failed to set option ('.$option.
				') to ('.$value.')', LdapException::INVALID_PARAM);
		}
	}

	/**
	 * Throws an expection with the last LDAP error
	 * @since version 1.0.0
	 */
	public function _throwLastError()
	{
		// all non zero responses trigger an exception
		$error = ldap_errno($this->_resource);
		if ($error !== 0)
		{
			throw new LdapException(ldap_err2str($error), $error);
		}
	}
}

?>
