<?php
/**
 * Disable website script
 * This script SHOULD be run BEFORE everything else, thus minimizing any overhead
 *
 * This script is ONLY run if DISABLE_SITE = true
 *
 * include the following constants within your config.php
 *
 * DISABLE_SITE true|false (or missing is the same)
 *
 * DISABLE_START_TIME timestamp
 * ex..
 * define('DISABLE_TIME_START',  mktime(16, 00, 0, 04, 1, 2010));
 *
 * DISABLE_END_TIME timestamp
 * ex..
 * define('DISABLE_TIME_END',  mktime(18, 58, 0, 04, 1, 2010));
 *
 * mktime($hour, $minute, $second, $month, $day, $year)
 *
 * DISABLE_BYPASS_IPS string
 * a comma seperated list of ips that bypass the disabling, used for testing
 *
 * DISABLE_TEMPLATE_TIME_LEFT string
 * an override location, so you can place your custom site down template
 *
 * DISABLE_TEMPLATE_NO_TIME string
 * an override location, so you can place your custom site down template
 * 
 * Example: This would be in your config.php
 * define('DISABLE_SITE', true);
 * define('DISABLE_TIME_START',  mktime(19, 50, 0, 04, 1, 2010));
 * define('DISABLE_TIME_END',  mktime(02, 50, 0, 04, 2, 2010));
 * define('DISABLE_BYPASS_IPS', '172.27.3.249,127.0.0.1');
 * #define('DISABLE_TEMPLATE_TIME_LEFT', '/path/to/file/disabled_display_time_left.tpl');
 * #define('DISABLE_TEMPLATE_NO_TIME', '/path/to/file/disabled_dont_display_time.tpl');
 * 
 * This should be in your index.php
 * // Disable entry website
 * require_once(_OCEDUCT_.'framework/disabled.php');
 * 
 * @author Steven Bruni (steven.bruni@gmail.com)
 * @version 1.0.0 2010/04/14
 * @package oceduct
 */

if (!defined('DISABLE_TEMPLATE_TIME_LEFT'))
{
	define('DISABLE_TEMPLATE_TIME_LEFT', _OCEDUCT_.'framework/templates/disabled_display_time_left.tpl');
}
if (!defined('DISABLE_TEMPLATE_NO_TIME'))
{
	define('DISABLE_TEMPLATE_NO_TIME', _OCEDUCT_.'framework/templates/disabled_dont_display_time.tpl');
}

$time = time();

if (defined('DISABLE_SITE') && DISABLE_SITE === true)
{
	if (defined('DISABLE_BYPASS_IPS'))
	{
		$ips = explode(',', DISABLE_BYPASS_IPS);

		for ($i = 0; $i < count($ips); $i++)
		{
			$ips[$i] = trim($ips[$i]);
		}

		if (in_array($_SERVER['REMOTE_ADDR'], $ips) !== false)
		{
			return;
		}
	}

	if ((!defined('DISABLE_TIME_START') || !defined('DISABLE_TIME_END')) ||
		((defined('DISABLE_TIME_START') && DISABLE_TIME_START == 0) &&
		(defined('DISABLE_TIME_END') && DISABLE_TIME_END == 0)))
	{
		require_once(DISABLE_TEMPLATE_NO_TIME);

		exit();
	}
	elseif ((defined('DISABLE_TIME_START') && $time >= DISABLE_TIME_START) && (defined('DISABLE_TIME_END') && DISABLE_TIME_END >= $time))
	{
		$seconds = DISABLE_TIME_END - $time;
		$minutesLeft = round($seconds / 60, 1);

		require_once(DISABLE_TEMPLATE_TIME_LEFT);

		exit();
	}
}

?>
