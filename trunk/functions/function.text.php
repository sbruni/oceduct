<?php
/**
 * Text Functions
 *
 * Functions that deal specifically with text
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Cuts a string down to x lengh
 *
 * Empty return string indicates an error
 * 
 * $force = true forces it to cut off the words even if its only up to 5 over.
 *
 * @param string $text
 * @param integer $length
 * @param bool $cutWords[optional]
 * @param string $append[optional]
 * @param bool $force[optional]
 * @return string
 * @since version 1.0.0
 * @requires function.general.php replaceNewLines()
*/
function textCut($text, $length, $cutWords = false, $append = '...', $force = false)
{
	// no valid length given
	if ($length <= 0)
	{
		return '';
	}

	// return if there isn't enough data to cut it
	if (strlen($text) <= $length)
	{
		return $text;
	}
	// failed the first check
	// if there is 5 more then the lengh, we'll let it pass
	// that is if $force is false, when true, it'll forces it to be correct
	elseif ($force === false && (strlen($text) - $length) <= 5)
	{
		return $text;
	}

	if ($cutWords === false)
	{
		$whiteSpaces = array(' ', "\n", "\r", "\t", "\f");
		$tmpString = substr($text, $length);

		$ws = true;
		foreach ($whiteSpaces as $val)
		{
			$pos = strpos($tmpString, $val);
			if ($pos === 0)
			{
				$ws = false;
			}
		}

		if ($ws === true)
		{
			$tmpString = substr($text, 0, $length);
			$last = 0;
			foreach ($whiteSpaces as $val)
			{
				$pos = strrpos($tmpString, $val);
				if ($pos !== false)
				{
					if ($pos >= $last)
					{
						$last = $pos;
					}
				}
			}
			if ($last > 0)
			{
				$length = $last;
			}
		}
	}

	// return cut string plus appened text
	return trim(replaceNewLines(substr($text, 0, $length).$append));
}

?>