<?php
/**
 * Conversion Functions
 *
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

/**
 * Converts from Binary to Text
 * 
 * @param mixed $bin
 * @return string
 * @since version 1.0.0
 */
function bin2txt($bin)
{
	$text = '';
	for ($i = 0; $i < strlen($bin); $i += 8)
	{
		$text .= chr(base_convert(substr($bin, $i, 8), 2, 10));
	}
	return $text;
}

/**
 * Converts binary number to hex
 * 10010010 = 92 in hex
 *
 * @param mixed $bin
 * @return string
 * @since version 1.1.0
 */
function binhex($bin)
{
	return dechex(bindec($bin));
}

/**
 * Converts filesizes between types: GB MB KB and bytes
 *
 * - 1 kibibyte [KiB] (or kilobyte (KB or kB)) = 1 024 (210) bytes
 * - 1 mebibyte [MiB] (or megabyte (MB)) = 1 048 576 (220) bytes
 * - 1 gibibyte [GiB] (or gigabyte (GB)) = 1 073 741 824 (230) bytes
 * - 1 tebibyte [TiB] (or terabyte (TB)) = 1 099 511 627 776 (240) bytes
 * - 1 pebibyte [PiB] (or petabyte (PB)) = 1 125 899 906 842 624 (250) bytes
 * - 1 exbibyte [EiB] (or exabyte (EB)) = 1 152 921 504 606 846 976 (260) bytes
 * The following two unofficial prefixes were not included in the IEC proposal, and are too large to have had any realistic use.
 * - 1 zebibyte [ZiB] = 1 180 591 620 717 411 303 424 (270) bytes
 * - 1 yobibyte [YiB] = 1 208 925 819 614 629 174 706 176 (280) bytes
 *
 * @param string $filesize
 * @param string $outputType [optional]
 * @param string $inputType [optional]
 * @param integer $precision [optional]
 * @param bool $longNames [optional]
 * @return string
 * @since version 1.0.0
 */
function filesizeConvert($filesize, $outputType = 'mb', $inputType = 'bytes',
	$precision = 2, $longNames = false)
{
	$return = $filesize;

	$inputType = strtolower($inputType);
	$outputType = strtolower($outputType);
	switch ($outputType)
	{
		case 'byte':
		case 'bytes':
			switch ($inputType)
			{
				case 'byte':
				case 'bytes':
					$return = $filesize;
					break;
				case 'kb':
				case 'kilobytes':
					$return = round(($filesize * (1 << 10)), $precision);
					break;
				case 'mb':
				case 'megabytes':
					$return = round(($filesize * (1 << 20)), $precision);
					break;
				case 'gb':
				case 'gigabytes':
					$return = round(($filesize * (1 << 30)), $precision);
			}
			// add the extension
			$return .= ($return <= 1)?' Byte':' Bytes';
			break;
		case 'kb';
			switch ($inputType)
			{
				case 'byte':
				case 'bytes':
					$return = round(($filesize / (1 << 10)), $precision);
					break;
				case 'kb':
				case 'kilobytes':
					$return = $filesize;
					break;
				case 'mb':
				case 'megabytes':
					$return = round(($filesize * (1 << 10)), $precision);
					break;
				case 'gb':
				case 'gigabytes':
					$return = round(($filesize * (1 << 20)), $precision);
			}
			// add the extension
			$return .= $longNames === true?' Kilobytes':' KB';
			break;
		case 'mb':
			switch ($inputType)
			{
				case 'byte':
				case 'bytes':
					$return = round(($filesize / (1 << 20)), $precision);
					break;
				case 'kb':
				case 'kilobytes':
					$return = round(($filesize / (1 << 10)), $precision);
					break;
				case 'mb':
				case 'megabytes':
					$return = $filesize;
					break;
				case 'gb':
				case 'gigabytes':
					$return = round(($filesize * (1 << 10)), $precision);
			}
			// add the extension
			$return .= $longNames === true?' Megabytes':' MB';
			break;
		case 'gb':
			switch ($inputType)
			{
				case 'byte':
				case 'bytes':
					$return = round(($filesize / (1 << 30)), $precision);
					break;
				case 'kb':
				case 'kilobytes':
					$return = round(($filesize / (1 << 20)), $precision);
					break;
				case 'mb':
				case 'megabytes':
					$return = round(($filesize / (1 << 10)), $precision);
					break;
				case 'gb':
				case 'gigabytes':
					$return = $filesize;
			}
			// add the extension
			$return .= $longNames === true?' Gigabytes':' GB';
			break;
		default:
			$return = $filesize;
	}
	return $return;
}

/**
 * Converts from Hex to Text
 * 
 * @param string $hex
 * @return string
 * @since version 1.0.0
 */
function hex2txt($hex)
{
	$text = '';
	for ($i = 0; $i < strlen($hex); $i+=2)
	{
		$text .= chr(hexdec(substr($hex,$i,2)));
	}
	return $text;
}

/**
 * Converts hex to binary
 *
 * @param string $hex
 * @return string
 * @since version 1.1.0
 */
function hexbin($hex)
{
	return decbin(hexdec($hex));
}

/**
 * Converts from Text to Binary
 * 
 * @param string $text
 * @return string
 * @since version 1.0.0
 */
function txt2bin($text)
{
	$bin = '';
	for ($i = 0; $i < strlen($text); $i++)
	{
		$bin .= substr("0000".base_convert(ord(
			substr($text,$i,1)),10,2),-8);
	}
	return $bin;
}

/**
 * Converts from Text to Hex
 * 
 * @param string $text
 * @return string
 * @since version 1.0.0
 */
function txt2hex($text)
{
	$hex = '';
	for ($i = 0; $i < strlen($text); $i++)
	{
		$hex .= dechex(ord(substr($text,$i,1)));
	}
	return $hex;
}

?>
