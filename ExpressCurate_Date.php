<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

/**
 * Class responsible for date formatting
 *
 * Class allpago_Util_DateFormatting
 */
class ExpressCurate_Date {

	/**
	 * Current date
	 *
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function today() {
		return date('Y-m-d');
	}

	/**
	 * Date for yesterday
	 *
	 * @return string - date in YYYY-MM-DD format
	 */
    public static function yesterday() {
		list($year, $month, $day) = explode('-', date('Y-m-d'));
        return date('Y-m-d', mktime(0, 0, 0, $month, $day - 1, $year));
    }

	/**
	 * change given date to given format
	 *
	 * @param string $format - format
	 * @param string $value - date
	 * @return string - date in given format
	 */
	public static function changeDateFormat($format, $value) {
        return date($format, strtotime($value));
    }

	/**
	 * Date and Time for yesterday
	 *
	 * @return string - date and time in YYYY-MM-DD 00:00:00 format
	 */
    public static function yesterdayWithTime() {
		$time = time();
        return date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', $time), date('d', $time) - 1, date('Y', $time)));
    }

	/**
	 * @param $date
	 * @return string  - week day correspondingly in following format
	 * 0 (for Sunday) through 6 (for Saturday)
	 */
	public static function getWeeDay($date) {
		return date( "w", strtotime($date));
	}


	/**
	 * Get previous Date in YYYY-MM-DD format
	 *
	 * @param $date
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function previousDate($date) {
		return date('Y-m-d', strtotime('-1 day', strtotime($date)));
	}

	/**
	 * Get tomorrow Date in YYYY-MM-DD format
	 *
	 * @param $date
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function tomorrow($date) {
		return date('Y-m-d', strtotime('+1 day', strtotime($date)));
	}

	/**
	 * Get  Date plus given count of days in YYYY-MM-DD format
	 *
	 * @param $date
	 * @param $count
	 * @return string
	 */
	public static function addDay($date,$count) {
		return date('Y-m-d', strtotime('+'.$count.' day', strtotime($date)));
	}

	/**
	 * Get Date plus given count of hours
	 *
	 * @param $date - date in YYYY-MM-DD HH:II::SS format
	 * @param $count
	 * @return string - date in YYYY-MM-DD HH:II::SS format
	 */
	public static function addHours($date, $count) {
		return date('Y-m-d H:i:s', strtotime('+'.$count.' hour', strtotime($date)));
	}

	/**
	 *  timezone wrapper for addDay() function
	 *
	 * @param $date
	 * @param $count
	 * @return string
	 */
	public static function addDayUtc($date, $count) {
		$originalTimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');

		$date = self::addDay($date, $count);

		date_default_timezone_set($originalTimezone);

		return $date;
	}

	/**
	 * Get  Date plus given count of months in YYYY-MM-DD format
	 *
	 * @param $date
	 * @param $count
	 * @return string
	 */
	public static function addMonth($date,$count) {
		return date('Y-m-d', strtotime('+'.$count.' month', strtotime($date)));
	}

	/**
	 * Get  Date minus given count of days in YYYY-MM-DD format
	 *
	 * @param $date
	 * @param $count
	 * @return string
	 */
	public static function minusDay($date,$count) {
		return date('Y-m-d', strtotime('-'.$count.' day', strtotime($date)));
	}

	/**
	 * Date without time for given timestamp
	 *
	 * @param int $timestamp - timestamp for which date will be formatted
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function dateOnly($timestamp) {
		return date('Y-m-d', $timestamp);
	}

	/**
	 * Only hours for given timestamp
	 *
	 * @param string $dateString - timestamp for which date will be formatted
	 * @return string - hours in int format
	 */
	public static function hoursOnly($dateString) {
		$timestamp = strtotime($dateString);
		return (int)date('H', $timestamp);
	}

	/**
	 * UTC timezone wrapper for dateWithTime() function
	 *
	 * @param int $timestamp
	 * @return string
	 */
	public static function dateOnlyUtc($timestamp) {
		$originalTimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');

		$date = self::dateOnly($timestamp);

		date_default_timezone_set($originalTimezone);

		return $date;
	}

	/**
	 * Current date and time
	 *
	 * @return string - date in YYYY-MM-DD hh:mm:ss format
	 */
	public static function now() {
		return date('Y-m-d H:i:s');
	}

	/**
	 * UTC timezone wrapper for now() function
	 *
	 * @return string
	 */
	public static function nowUtc() {
		$originalTimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');

		$date = self::now();

		date_default_timezone_set($originalTimezone);

		return $date;
	}

	/**
	 * Date with time for given timestamp
	 *
	 * @param int $timestamp - timestamp for which date with time will be formatted
	 * @return string - date in YYYY-MM-DD hh:mm:ss format
	 */
    public static function dateWithTime($timestamp) {
		return date('Y-m-d H:i:s', $timestamp);
    }

	/**
	 * UTC timezone wrapper for dateWithTime() function
	 *
	 * @param int $timestamp
	 * @return string
	 */
	public static function dateWithTimeUtc($timestamp) {
		$originalTimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');

		$date = self::dateWithTime($timestamp);

		date_default_timezone_set($originalTimezone);

		return $date;
	}

	/**
	 * Current month's first day's date
	 *
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function firstDayOfCurrentMonth() {
		return date('Y-m-01');
	}

	/**
	 * Month's first day's date for given timestamp
	 *
	 * @param int $timestamp - timestamp for which the month's first day's date will be returned
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function firstDayOfMonth($timestamp) {
		return date('Y-m-01', $timestamp);
	}

	/**
	 * Get month of given timestamp
	 *
	 * @param int $timestamp - timestamp for which the month's first day's date will be returned
	 * @return string - date in MM format
	 */
	public static function getYearMonthFromDate($timestamp) {
		return date('Y-m', $timestamp);
	}

	/**
	 * Month's given day's date for given timestamp
	 *
	 * @param int $timestamp - timestamp for which the month's given day's date will be returned
	 * @param int $day - given day's number (from 1 to 31)
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function givenDayOfMonth($timestamp, $day) {
		return date('Y-m-'.sprintf('%02d', $day), $timestamp);
	}

	/**
	 * Month's last day of current month
	 *
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function lastDayOfCurrentMonth() {
		return date('Y-m-t');
	}

	/**
	 * Month's last day's date for given timestamp
	 *
	 * @param int $timestamp - timestamp for which the month's last day's date will be returned
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function lastDayOfMonth($timestamp) {
		return date('Y-m-t', $timestamp);
	}

	/**
	 * A short textual representation of a month and year
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @return string - A short textual representation of a month (three letters) and year (ex. Jan 2013)
	 */
	public static function FormatAsShortMonthAndYear($date) {
		return date('M Y', strtotime($date));
	}

	/**
	 * A short textual representation of a month and year
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @return string - A short textual representation of a month (three letters) and year (ex. Jan 2013)
	 */
	public static function formatAsYearMonth($date) {
		return date('Y_m', strtotime($date));
	}

	/**
	 * A short textual representation of a month and year
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @return string - A short textual representation of a month (full name) and year (ex. January-2013)
	 */
	public static function formatAsMonthYear($date) {
		return date('F-Y', strtotime($date));
	}

	/**
	 * Years's first day's date for given date
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @return string - date in YYYY-MM-DD format
	 */
	public static function firstDayOfYear($date) {
		return date('Y-01-01', strtotime($date));
	}

	/**
	 * A textual representation of date according to given mask
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @param string $mask - format
	 *
	 * @return string - A textual representation of date according to mask
	 */
	public static function formatByMask($date, $mask) {
		return date($mask, strtotime($date));
	}


	/**
	 * Last (before given month for timestamp) and Previous months first and last days dates
	 *
	 * @param string $timestamp - timestamp for which dates will be calculated
	 * @return array - dates in YYYY-MM-DD format
	 */
	public static function lastAndPreviousMonthsStartAndEndDates($timestamp) {
		$lastMonthTime = strtotime('-1 day', strtotime(date('Y-m-01', $timestamp)));

		$prevMonthTime = strtotime('-1 day', strtotime(date('Y-m-01', $lastMonthTime)));

		return array(
			'last' => array('first_day' => date('Y-m-01', $lastMonthTime), 'last_day' => date('Y-m-t', $lastMonthTime)),
			'prev' => array('first_day' => date('Y-m-01', $prevMonthTime), 'last_day' => date('Y-m-t', $prevMonthTime))
		);
	}

	/**
	 * Given dates difference in seconds
	 *
	 * @param $startDate - start date
	 * @param $endDate - end date
	 * @return int - Date difference in seconds
	 */
	public static function dateDifference($startDate , $endDate) {
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);
		$secondsDiff =  $endDate - $startDate;
		return $secondsDiff;
	}

	/**
	 * Given dates difference
	 *
	 * @param $startDate - start date
	 * @param $endDate - end date
	 * @return int - Date difference in Days
	 */
	public static function dateDifferenceInDays($startDate , $endDate) {
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);
		$seconds_diff =  $endDate - $startDate;
		return $seconds_diff/60*60*24;
	}

	/**
	 * Date in days
	 *
	 * @param $timestamp
	 * @return float
	 */
	public static function dateInDays($timestamp) {
		$startDate = strtotime($timestamp);
		return $startDate/60/60/24;
	}

	/**
	 * Get journey from given date
	 *
	 * @param $date
	 * @return bool|string
	 */
	public static function getDayFromDate($date) {
		return date('j', strtotime($date));
	}

	/**
	 * Days count in the month for given date
	 *
	 * @param string $date - date in YYYY-MM-DD format
	 * @return int - Days count
	 */
	public static function getDaysCountInMonthForDate($date) {
		return intval(date('t', strtotime($date)));
	}

	/**
	 * Get first and last days for given month and year
	 *
	 * @param $monthNum - month's number (from 1 to 12)
	 * @param $year - year
	 * @return array - list of first and last days
	 */
	public static function firstAndLastDaysOfMonth($monthNum, $year) {
		$monthNum = sprintf('%02d', $monthNum);

		$first = $year.'-'.$monthNum.'-01';
		$last = date('Y-m-t', strtotime($first));

		return array($first, $last);
	}
}
?>