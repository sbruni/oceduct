<?php
/**
 * Framework Functions file
 *
 * Any functions used by the Framework
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Used for access control
 * A list of vaild aclItems are parsed out of the memberOf array
 * from there they are checked against a custom regex(AUTH_MEMOF_REGEX)
 * If they match they are added to the Session aclItems list
 *
 * $acl CAN be a comma seperated list of levels, if the user has ONE of them
 * then it returns true
 *
 * Admins always return true (admin name is 'fulladmin')
 *
 * Set $partialMatch to true if you want the acl to partially match any item
 * within the acl listing. Remember if you do something short it has a chance
 * of matching WITHIN an item.
 *
 * Returns true or false
 *
 * @param string $acl [optional]
 * @param bool $refresh [optional]
 * @param bool $partialMatch [optional]
 * @return bool
 * @since version 1.0.0
 */
function acl($acl = '', $refresh = false, $partialMatch = false)
{
	// no authentication given always return false
	if (empty($GLOBALS['authentication']))
	{
		return false;
	}

	$memof = array();
	if (empty($_SESSION[SP.'userInfo']['aclItems']) || $refresh === true)
	{
		$memof = array();
		if (!empty($_SESSION[SP.'userInfo']['group']))
		{
			$memof = $_SESSION[SP.'userInfo']['group'];

			// if it's a string covert it to an array
			if (!empty($memof) && is_string($memof))
			{
				$memof = array(
					$memof
				);
			}
		}

		$userId = $GLOBALS['authentication']->getUserId();
		$sectionAdmins = array();

		// if the user is admin of any sections, add those sections to the acl
		try
		{
			// must ini new connection in case we are already looping through the
			// global one
			$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
			if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
			{
				$db = new PostgreSql($GLOBALS['dbInfo']);
			}
			elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
			{
				$db = new MySql($GLOBALS['dbInfo']);
			}

			$db->query('
				SELECT "Value"
				FROM "UserOptions"
				WHERE "UserID" = \''.strtolower($userId).'\'
				AND "Option" = \'sectionAdmin\'
			');
			while ($db->nextRecord())
			{
				// append sectionAdmin so we know it's site specific etc...
				$sectionAdmins[] = 'sectionAdmin_'.$db->record('Value');
			}
		}
		catch (Exception $e)
		{
			// NOTE: you need to call __toString directy when passing it on to a function
			trigger_error($e->__toString(), E_USER_ERROR);
		}

		$_SESSION[SP.'userInfo']['aclItems'] = array_merge($memof, $sectionAdmins);
	}

	// FULL admin has access to everything
	// ONLY allow this if constant AUTH_FULL_ADMIN is defined in the config
	if (defined('AUTH_FULL_ADMIN') && in_array_ext(AUTH_FULL_ADMIN,
		$_SESSION[SP.'userInfo']['aclItems']))
	{
		return true;
	}

	if (!empty($acl))
	{
		// split them up
		$acls = explode(',', $acl);

		// I know in_array supports an array, but I want to trim it
		foreach ($acls as $ac)
		{
			$ac = trim($ac);
			// if the received acl any one of these items
			if (!empty($ac) && $partialMatch === true)
			{
				// partial matching, not to safe
				if (in_array_ext($ac, $_SESSION[SP.'userInfo']['aclItems'], false, false))
				{
					return true;
				}
			}
			else
			{
				if (in_array($ac, $_SESSION[SP.'userInfo']['aclItems']))
				{
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Non-Admin check, checks if the given acl is within the users acl
 * This does a real check unlike acl()
 *
 * acl() returns true if an admin is present, this does NOT
 * so this can be used to see if the user has that acl in there
 * account
 *
 * @param string $acl
 * @return bool
 * @since 1.0.0
 */
function aclCheck($acl)
{
	if (empty($_SESSION[SP.'userInfo']['aclItems']))
	{
		acl();
	}

	if (!empty($acl) && in_array($acl, $_SESSION[SP.'userInfo']['aclItems']))
	{
		return true;
	}
	return false;
}

/**
 * Gets the known Extensions (may have multiple)
 *
 * @param string $mimeType
 * @return array
 * @since version 1.0.0
 */
function getExtsFromMimeType($mimeType)
{
	$return = array();
	$types = file(_OCEDUCT_.'data/mime.types');
	foreach ($types as $data)
	{
		$data = trim($data);
		if (!empty($data) && substr($data, 0, 1) != '#')
		{
			$matches = array();
			if (preg_match('!'.$mimeType.'\t(.*)!i', $data, $matches))
			{
				// return all extensions
				if (!empty($matches[1]))
				{
					$tmp = explode(' ', $matches[1]);
					foreach ($tmp as $t)
					{
						$return[] = trim($t);
					}
					// break out of above foreach
					// we found one no need to keep looking
					break;
				}
			}
		}
	}

	return $return;
}

/**
 * Gets the mimetype for the given extension
 * Returns the FIRST mimetype found, more then one indicates a problem
 *
 * @param string $extension
 * @return string
 * @since version 1.0.0
 */
function getMimeType($extension)
{
	$types = file(_OCEDUCT_.'data/mime.types');
	foreach ($types as $data)
	{
		$data = trim($data);
		if (!empty($data) && substr($data, 0, 1) != '#')
		{
			$matches = array();
			if (preg_match('!(.+)\t.*'.$extension.'.*!i', $data, $matches))
			{
				// return the FIRST found mimeType, if there are more then one
				// it doesn't make sense and something is incorrect
				if (!empty($matches[1]))
				{
					return $matches[1];
				}
			}
		}
	}
	// return empty if nothing found
	return '';
}

/**
 * Get the section
 *
 * Pass the path in and the section ID and Name will be returned
 * If the section is a top level section set $top to true
 *
 * @param string $path
 * @param integer $parentId [optional]
 * @param bool $top [optional]
 * @return array
 * @since 1.0.0
 */
function getSection($path, $parentId = 0, $top = false)
{
	$id = 0;
	$name = '';
	$options = array();

	$path = safeDecode($path);

	try
	{
		// must ini new connection in case we are already looping through the
		// global one
		$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
		if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
		{
			$db = new PostgreSql($GLOBALS['dbInfo']);
		}
		elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
		{
			$db = new MySql($GLOBALS['dbInfo']);
		}

		$query = '
			AND "Top" = false
			AND "ParentID" = \''.$parentId.'\'
		';
		if ($top === true)
		{
			$query = 'AND ("Top" = true OR "ParentID" = \'0\')';
		}

		#todo allow admins to see disabled items

		$db->queryOnce('
			SELECT "Sections"."ID", "Name", "LLName"
			FROM "Sections" LEFT JOIN "SectionNames"
			ON "Sections"."ID" = "SectionNames"."SectionID"
			AND "SectionNames"."LangCode" = \''.$_SESSION[SP.'userSiteOptions']['languageCode'].'\'
			WHERE "Disabled" = false
			AND "Path" = \''.$path.'\'
			'.$query
		);
		if ($db->record('ID') !== false && !is_null($db->record('ID')))
		{
			$id = $db->record('ID');
			$name = $db->record('LLName');
			if (empty($name))
			{
				$name = $db->record('Name');
			}

			// after we get what section we are in we can get the section "options"
			// these are site specific options and should NOT be required by the framework
			$db->query('
				SELECT "Option", "Value"
				FROM "SectionsData"
				WHERE "SectionID" = \''.$id.'\'
			');
			while ($db->nextRecord())
			{
				if ($db->record('Option') !== false)
				{
					// make an array if there are multiple
					if (!empty($options[$db->record('Option')]))
					{
						// array already exists add to it
						if (is_array($options[$db->record('Option')]))
						{
							$options[$db->record('Option')][] = $db->record('Value');
						}
						// no array yet create a new one and add the previous value and
						// the current value to it
						else
						{
							$tmp = $options[$db->record('Option')];
							$options[$db->record('Option')] = array(
								$tmp,
								$db->record('Value')
							);
						}
					}
					else
					{
						$options[$db->record('Option')] = $db->record('Value');
					}
				}
			}

			// return from in here this way we know there were results
			return array(
				'id' => $id,
				'name' => $name,
				'path' => $path,
				'options' => $options
			);
		}
	}
	catch (Exception $e)
	{
		// NOTE: you need to call __toString directy when passing it on to a function
		trigger_error($e->__toString(), E_USER_ERROR);
	}

	return array(
		'id' => 0,
		'name' => '',
		'path' => '',
		'options' => array()
	);
}

/**
 * Get the section options
 *
 * Retrives ONLY the options for the given section
 * Requires the ID
 *
 * @param string $id
 * @return array
 * @since 1.0.0
 */
function getSectionOptions($id)
{
	$options = array();
	try
	{
		// must ini new connection in case we are already looping through the
		// global one
		$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
		if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
		{
			$db = new PostgreSql($GLOBALS['dbInfo']);
		}
		elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
		{
			$db = new MySql($GLOBALS['dbInfo']);
		}

		// get the section options
		// these are site specific options and should NOT be required by the framework
		$db->query('
			SELECT "Option", "Value"
			FROM "SectionsData"
			WHERE "SectionID" = \''.$id.'\'
		');
		while ($db->nextRecord())
		{
			if ($db->record('Option') !== false)
			{
				// make an array if there are multiple
				if (!empty($options[$db->record('Option')]))
				{
					// array already exists add to it
					if (is_array($options[$db->record('Option')]))
					{
						$options[$db->record('Option')][] = $db->record('Value');
					}
					// no array yet create a new one and add the previous value and
					// the current value to it
					else
					{
						$tmp = $options[$db->record('Option')];
						$options[$db->record('Option')] = array(
							$tmp,
							$db->record('Value')
						);
					}
				}
				else
				{
					$options[$db->record('Option')] = $db->record('Value');
				}
			}
		}

		// return all options
		return $options;
	}
	catch (Exception $e)
	{
		// NOTE: you need to call __toString directy when passing it on to a function
		trigger_error($e->__toString(), E_USER_ERROR);
	}

	// no options found
	return array();
}

/**
 * Gets the full path and parents from the sectionid
 *
 * Returned string does NOT start with a backslash or end with one.
 * i.e section/path/here
 *
 * @param integer $id
 * @return string
 * @since version 1.0.0
 */
function getSectionPathFromId($id)
{
	// force $id to be a int
	$id = intval($id);

	$path = '';

	try
	{
		// must ini new connection in case we are already looping through the
		// global one
		$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
		if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
		{
			$db = new PostgreSql($GLOBALS['dbInfo']);
		}
		elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
		{
			$db = new MySql($GLOBALS['dbInfo']);
		}

		$db->queryOnce('
			SELECT "Path", "ParentID"
			FROM "Sections"
			WHERE "Disabled" = false
			AND "ID" = \''.$id.'\'
		');
		$pid = intval($db->record('ParentID'));
		if ($pid !== 0)
		{
			$path .= getSectionPathFromId($pid).'/';
		}

		$path .= $db->record('Path');
	}
	catch (Exception $e)
	{
		// NOTE: you need to call __toString directy when passing it on to a function
		trigger_error($e->__toString(), E_USER_ERROR);
	}

	return $path;
}

/**
 * NOTE: SITE_ROOT_PATH is ALWAYS removed, below descrbies a url WITHOUT
 * the root path within it.
 *
 * - item = text between two slashes (/)
 * - type = download or print or image or update or css
 * - section = a section name corrasponding to a valid section in the database
 * - command = group or language, commands are basicly extra
 *   but issue an automatic command, and are not included within the
 *   $extra array
 * - extra = a varaible that can be used within a template etc..
 *
 * First item MAY be a type or a section or command or an extra
 * Second and higher MAY be a section or command or an extra
 *
 * Items are always lowercased
 *
 * Naming scheme:
 * types can be given as:
 * - download = download | downloads | d
 * - print = print | prints | p
 * - image = image | images | i
 * - update = update | updates | u
 * - css = css
 *
 * sections are given rawurlencoded and SHOULD be lowercase
 *
 * commands can be given as:
 * - group = group | g
 * - language = language | lang | l
 *
 * Possible url formats:
 * (remember to remove the url encoded char before using this url's in pubs etc)
 *
 * Groups can appear like:
 * (url)/group/Women/
 * (url)/groups/people/
 * (url)/g/Shoot Straight/
 *
 * Language code:
 * (url)/pubs/language/es/
 * (url)/pubs/language/es/pub.fsm.407
 *
 * a page number can be either or:
 * (a number i.e 1 2 3)
 * the default is 1
 *
 * ID:
 * The ID is the last item (does NOT have an ending slash (/)) on the url
 * It can be a id or a filename. For example, if you want your site to be
 * better indexed by a search engine, it's good to have an ending on the file, that's
 * what this is used for.
 *
 * @param string $url
 * @param bool $stripSlash [optional]
 * @return array
 * @since 1.0.0
 * @see getSection()
 */
function getSectionsFromUrl($url, $stripSlash = false)
{
	$extra = array();
	$matches = array();
	$section = array(
		'id' => 0,
		'path' => '',
		'options' => array()
	);
	$parents = '';
	$parentsList = array();
	$parentsNames = '';
	$parentsUrl = '';
	$type = '';
	$id = '';
	$sectionGroup = '';
	$sectionCategory = '';

	// NOTE: Do not lowercase incomming url
	// this can cause problems in the site and anything it's accessing

	// decode it for safety
	if ($stripSlash === true)
	{
		// fix for apache bug with %2F
		// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
		// we IGNORE %2F here later we replace it manually
		// we ARE assuming that apache is already running a decode on it once
		// once we upgrade to the fixed apache this can be removed
		$url = safeDecode($url, array(), array(), array('%2F'));
	}
	else
	{
		$url = safeDecode($url);
	}

	// remove the site root path
	$url = preg_replace('!^'.addslashes(rtrim(SITE_ROOT_PATH, '/')).'!i',
		'', $url);

	// split the id off the rest of the url
	// the id is ANYTHING after the last backslash (/)
	// the site root path is already removed
	if (preg_match('!^/?(.+)?/([^/]+)?!', $url, $matches))
	{
		if (isset($matches[1]))
		{
			$url = $matches[1];
		}
		if (isset($matches[2]))
		{
			$id = $matches[2];
		}
	}

	// find the section and such BEFORE messing with the ID
	if (!empty($url))
	{
		$tmp = explode('/', $url);

		// check for a type
		// First item MAY be a type or a section or command or an extra
		if (in_array_ext($tmp[0], $GLOBALS['listTypes']))
		{
			// type is valid, set type
			$type = $tmp[0];
			// remove type from the front of the array
			array_shift($tmp);
		}

		// the first section MUST be a top level section
		$top = true;

	 	// Second and higher MAY be a section or command or an extra
	 	// start (from left to right), the first item should be a top
	 	// level section, all top level sections MUST have a unqiue path
		do
		{
			// perhaps the data given was a type above, or just an extra
			if (empty($tmp))
			{
				break;
			}

			// fix for apache bug with %2F
			// http://issues.apache.org/bugzilla/show_bug.cgi?id=34602
			// this MUST be done after spliting up on / this way we CAN have /
			// in names of sections or groups etc..
			$tmp[0] = str_replace('%2F', '/', $tmp[0]);
	 		$return = getSection($tmp[0], $section['id'], $top);
	 		// top goes false for everything after the first run
	 		$top = false;

	 		// break out when there is nothing left
	 		if (empty($return['id']))
	 		{
	 			break;
	 		}

	 		// we didn't break out so we want this section info
	 		$section = $return;

	 		// get all the parents paths and names
	 		if (!empty($section['path']))
	 		{
		 		$parentsList[] = array(
		 			'path' => $section['path'],
		 			'name' => $section['name']
		 		);
	 		}

	 		// remove the first one again and continue
			array_shift($tmp);

			// this MUST be below array_shift, but can't be with the previous check
			if (empty($tmp))
			{
				break;
			}
	 	} while (true);

	 	// remove the LAST parent on the list since it's the current section
	 	array_pop($parentsList);
	 	if (!empty($parentsList))
	 	{
	 		foreach ($parentsList as $list)
	 		{
	 			$parents .= $list['path'].'_';
	 			$parentsNames .= $list['name'].'_x_';
	 		}
	 	}
	 	// url friendly version of $parents
		$parentsUrl = str_replace('_', '/', $parents);

	 	// add the remaing items to the extra array
	 	if (!empty($tmp))
	 	{
	 		$extra = $tmp;
	 	}

		// the default section group(s)
		if (!empty($section['options']['group']))
		{
			$sectionGroup = $section['options']['group'];
			// force into an array so that it's compatible with page groups
			if (!is_array($section['options']['group']))
			{
				$sectionGroup = array($section['options']['group']);
			}
		}

		// the default section categories
		if (!empty($section['options']['category']))
		{
			$sectionCategory = $section['options']['category'];
			// force into an array so that it's compatible with page categories
			if (!is_array($section['options']['category']))
			{
				$sectionCategory = array($section['options']['category']);
			}
		}
	}

	// if there is NO valid section default to Home
	if (empty($section['path']))
	{
		$section = array(
			'name' => 'Home',
			'path' => 'home',
			'options' => array()
		);
	}

	// safe Url decode the extras, I'm doing it here so it's all in one function
	// so that I'm sure it's done at the top most level
	$tmp = array();
	foreach ($extra as $e)
	{
		$tmp[] = safeDecode($e);
	}
	$extra = $tmp;

	return array(
		'e' => $extra,
		'p' => $parents,
		'pn' => $parentsNames,
		'purl' => $parentsUrl,
		's' => $section['name'],
		'st' => $section['path'],
		'so' => $section['options'],
		't' => $type,
		'id' => $id,
		'sectionGroup' => $sectionGroup,
		'sectionCategory' => $sectionCategory
	);
}

/**
 * Gets all/specific options
 * Populates the session arrays $_SESSION[SP.'userSiteOptions'] and
 * $_SESSION[SP.'siteOptions']
 * This function should only be called once per user session
 * unless the users options change, or if the user needs there session refreshed
 *
 * If $refresh is true then the user options will be refreshed
 *
 * @param bool $refresh
 * @since version 1.0.0
 */
function getOptions($refresh = false)
{
	try
	{
		// set to the default database
		$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
		// must use a new database object

		if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
		{
			$db = new PostgreSql($GLOBALS['dbInfo']);
		}
		elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
		{
			$db = new MySql($GLOBALS['dbInfo']);
		}

		// site options
		if (empty($_SESSION[SP.'siteOptions']) || $refresh === true)
		{
			// reset all session site options
			$_SESSION[SP.'siteOptions'] = array();

			$db->query('
				SELECT "Option", "Value", "Default"
				FROM "SiteOptions"
				WHERE "Option" <> \'\'
			');

			$siteOptions = array();
			while ($db->nextRecord())
			{
				// if there are multiple defaults the last one is final
				if ($db->record('Default') == 't')
				{
					$siteOptions[$db->record('Option')]['default'] =
						$db->record('Value');
				}

				// always write out as an array
				// write it even if value is empty
				$siteOptions[$db->record('Option')][] = $db->record('Value');
			}

			// we don't write directly to the session above so we can clear
			// the specific option first
			foreach ($siteOptions as $key => $val)
			{
				// store options for later
				// ONLY overwrite the options that are in the database NO other
				// options should be overwritten
				// if only one option is given write it out as a single item and not an array
				if (is_array($val) && count($val) == 1)
				{
					$_SESSION[SP.'siteOptions'][$key] = $val[0];
				}
				else
				{
					$_SESSION[SP.'siteOptions'][$key] = $val;
				}
			}
		}

		// User options
		if (empty($_SESSION[SP.'userSiteOptions']) || $refresh === true)
		{
			// no authentication class running do not even try to get user options
			if (!empty($GLOBALS['authentication']))
			{
				$userId = $GLOBALS['authentication']->getUserId();
				if (empty($userId))
				{
					trigger_error('User not found.', E_USER_ERROR);
				}

				// reset all user session options
				$_SESSION[SP.'userSiteOptions'] = array();

				// all userid's in the userdata are stored in LOWER case
				// thus a user can log in with mixed case and still get the same options
				$db->query('
					SELECT "UserID", "Option", "Value"
					FROM "UserOptions"
					WHERE "UserID" = \''.strtolower($userId).'\' AND
					"Option" <> \'\'
				');

				$userOptions = array();
				while ($db->nextRecord())
				{
					// always write out as an array
					// write it even if value is empty
					$userOptions[$db->record('Option')][] = $db->record('Value');
				}

				// we don't write directly to the session above so we can clear
				// the specific option first
				foreach ($userOptions as $key => $val)
				{
					// store options for later
					// ONLY overwrite the options that are in the database NO other
					// options should be overwritten
					// if only one option is given write it out as a single item and not an array
					if (is_array($val) && count($val) == 1)
					{
						$_SESSION[SP.'userSiteOptions'][$key] = $val[0];
					}
					else
					{
						$_SESSION[SP.'userSiteOptions'][$key] = $val;
					}
				}
			} // end !empty auth
		}
	}
	catch (Exception $e)
	{
		// NOTE: you need to call __toString directy when passing it on to a function
		trigger_error($e->__toString(), E_USER_ERROR);
	}
}

/**
 * Manipulate the Extra's array and return any site/framework requested data
 *
 * Any items (in the url) that aren't used by anything (section/url parsing)
 * are added to the "extra" array, this array allows you to access the full
 * incomming data via GET in a safe way.
 *
 * Also if you have your site/framework setup it'll use URL PARSING
 *
 * URL PARSING: Say your url is http://mysite.com/group/people/
 * group is part of the URL parsing and people is the value
 *
 * It's basicly the same as using ?group=people in the query line
 * just a much safer say, also you can retrive multiple values for it.
 *
 * @since version 1.0.0
 * @todo Write more docs explain it more
 */
function parseExtras()
{
	// ini
	$urlParsing = array();
	$return = array();
	$extra = array();

	// no extras return
	if (empty($GLOBALS['e']))
	{
		return;
	}

	// mess with a copy of GLOBAL extra
	$e = $GLOBALS['e'];

	// APPEND is true by default not false
	// so if they don't include this it WILL append
	$conUrlParsing = '';
	if (defined('SITE_URL_PARSING_APPEND') && SITE_URL_PARSING_APPEND === false)
	{
		$conUrlParsing = SITE_URL_PARSING;
	}
	elseif (defined('SITE_URL_PARSING') && SITE_URL_PARSING != '')
	{
		// simply append it's not our fault if it's not created propely
		#@todo fix this maybe?
		$conUrlParsing = trim(FRAMEWORK_URL_PARSING, '/').'/'.
			trim(SITE_URL_PARSING, '/');
	}
	elseif (defined('FRAMEWORK_URL_PARSING'))
	{
		// default to the framework one
		$conUrlParsing = trim(FRAMEWORK_URL_PARSING, '/');
	}

	// $urlParsing is the array we'll loop through later to veryify
	$tmp = explode('/', $conUrlParsing);
	foreach ($tmp as $x)
	{
		$y = explode(':', $x);
		if (empty($y[1]))
		{
			trigger_error('Variable name missing from URL PARSING constant, value given was: '.
				$conUrlParsing, E_USER_ERROR);
		}
		else
		{
			$urlParsing[$y[0]] = explode(',', $y[1]);
			// if given limit the amount of items that can be returned
			// for this type
			if (isset($y[2]) && $t = intval($y[2]))
			{
				$urlParsing[$y[0]]['max'] = $t;
			}
		}
	}

	$ran = false;
	do
	{
		foreach ($urlParsing as $name => $items)
		{
			if (in_array($e[0], $items))
			{
				// we are limiting so check if $return[$name] already exists if so
				// count it to see how many there are
				if (!empty($items['max']) && !empty($return[$name]))
				{
					if (count($return[$name]) >= $items['max'])
					{
						// we don't want to add to this one
						break;
					}
				}
				if (!empty($e[1]))
				{
					$return[$name][] = $e[1];
					array_shift($e);
					array_shift($e);
					$ran = true;
					break;
				}
			}
		}
		if ($ran === false)
		{
			$extra[] = $e[0];
			array_shift($e);
		}
		$ran = false;
	} while (!empty($e));

	// add to globals WITHIN THIS function

	// global extras
	$GLOBALS['e'] = $extra;

	// eFirst is a fast way of accessing the first extra variable
	// NOTE it is NOT removed from the extra array
	if (!empty($GLOBALS['e'][0]))
	{
		$GLOBALS['eFirst'] = $GLOBALS['e'][0];
	}

	// NOTE: all of these dynamic globals ARE arrays
	foreach ($return as $name => $item)
	{
		$GLOBALS['p'.ucfirst($name)] = $item;
	}
}

/**
 * Sets/Saves user options
 *
 * The settings are always overridden, by what is given, it does not
 * append to it.
 *
 * $values MUST always be an array
 *
 * For single items
 * $values = array(
 *    'value'
 * )
 *
 * For multiple values
 * $values = array(
 *    'value1',
 *    'value2',
 *    'value3',
 * )
 *
 * $location MUST be either 'user' or 'site', this will determin in which table
 * the data is stored
 *
 * When saving with $location 'site' the $values array MAY contain a default
 *
 * $values = array(
 *    'default' => 'value1',
 *    'value2',
 *    'value3',
 * )
 *
 * Any value with default as the key will be saved as the default for that option
 *
 * NOTE: You can set more then one value as the default but when you retrive
 * the options only ONE of them will be returned as the default
 *
 * @param string $option
 * @param array $values
 * @param string $location[optional]
 * @since version 1.0.0
 */
function setOptions($option, $values, $location = 'user')
{
	if ($location != 'user' && $location != 'site')
	{
		trigger_error('$location MUST be \'user\' or \'site\'', E_USER_ERROR);
	}

	try
	{
		// set to the default database
		$GLOBALS['dbInfo']['database'] = DATABASE_DEFAULT;
		// must use a new database object
		if (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'postgresql')
		{
			$db = new PostgreSql($GLOBALS['dbInfo']);
		}
		elseif (defined('DATABASE_SYSTEM') && DATABASE_SYSTEM == 'mysql')
		{
			$db = new MySql($GLOBALS['dbInfo']);
		}

		$option = safeDecode($option);

		if (empty($option))
		{
			trigger_error('No option given', E_USER_ERROR);
		}

		if (!is_array($values))
		{
			trigger_error('Expecting an array '.gettype($values).
				' received', E_USER_ERROR);
		}

		// save site
		if ($location == 'site')
		{
			// replace all current options with the ones given
			$db->query('
				DELETE FROM "SiteOptions"
				WHERE "Option" = \''.$option.'\'
			');

			// multiple options insert each one as a seperate record
			foreach ($values as $key => $value)
			{
				// if the key is 'default' then set this value as default
				$default = ($key == 'default')?'true':'false';

				if (!empty($value))
				{
					$value = safeDecode($value);
					$db->query('
						INSERT INTO "SiteOptions"
						("Option", "Value", "Default")
						VALUES(\''.$option.'\', \''.$value.'\', \''.$default.'\')
					');
				}
			}
		}
		elseif ($location == 'user' && !empty($GLOBALS['authentication']))
		{
			// if no user is given use the current user
			$userId = $GLOBALS['authentication']->getUserId();
			if (empty($userId))
			{
				trigger_error('User not found.', E_USER_ERROR);
			}

			// all userid's in the userdata are stored in LOWER case
			// thus a user can log in with mixed case and still set the same options
			$userId = strtolower($userId);

			// replace all current options with the ones given
			$db->query('
				DELETE FROM "UserOptions"
				WHERE "Option" = \''.$option.'\'
				AND "UserID" = \''.$userId.'\'
			');

			// multiple options insert each one as a seperate record
			foreach ($values as $value)
			{
				if (!empty($value))
				{
					$value = safeDecode($value);
					$db->query('
						INSERT INTO "UserOptions"
						("UserID", "Option", "Value")
						VALUES(\''.$userId.'\', \''.$option.'\', \''.$value.'\')
					');
				}
			}
		}
	}
	catch (Exception $e)
	{
		// NOTE: you need to call __toString directy when passing it on to a function
		trigger_error($e->__toString(), E_USER_ERROR);
	}

	// reini the options retriving the newest ones
	getOptions(true);
}

/**
 * Custom redirect (automaticly adds the internal http etc..)
 * $code 301 is used by default use 307 for temporary or error redirects
 * If $stripRoot is true SITE_ROOT_PATH will automaticly be removed from the
 * url. This is so that you can give a full path with the root path and have
 * it redirect correctly.
 *
 * @param string $url
 * @param integer $code [optional]
 * @param string $type [optional]
 * @param bool $stripRoot [optional]
 * @param bool $ssl [optional]
 * @param bool $debug [optional]
 * @since 1.0.0
 */
function siteRedirect($url, $code = 301, $type = 'php', $stripRoot = true,
	$ssl = false, $debug = false)
{
	if (!isset($url))
	{
		trigger_error('Missing $url for siteRedirect()', E_USER_ERROR);
	}

	if ($stripRoot === true)
	{
		$url = preg_replace('!^'.addslashes(ltrim(SITE_ROOT_PATH, '/')).'!i',
		'', $url);
	}
	// if we want SSL enabled
	$http = HTTP;
	if ($ssl === true)
	{
		$http = HTTPS;
	}
	if ($debug === true)
	{
		var_dump($http.$_SERVER['HTTP_HOST'].SITE_ROOT_PATH.ltrim($url, '/'));
	}
	else
	{
		redirect($http.$_SERVER['HTTP_HOST'].SITE_ROOT_PATH.ltrim($url, '/'), 0,
			$type, $code);
	}
}

?>
