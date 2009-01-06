<?php

/* Date Class v0.2.2
 * Holds methods for misc. date calls.
 *
 * CHANGELOG
 * version 0.2.2, 02 Apr 2008
 *   FIX: Fixed parseDate.
 * version 0.2.1, 14 Feb 2008
 *   CHG: Changed fromMysql. Removed language check.
 * version 0.2.0, 15 Nov 2007
 *   CHG: Extended toMysql method.
 * version 0.1.0, 12 Apr 2006
 *   NEW: Created class.
 */

class Date {

	public static function fromMysql($strFormat, $strDateTime) {
		$strReturn = $strDateTime;

		if ($strDateTime != "0000-00-00 00:00:00" && !empty($strDateTime)) {
			$strTStamp = strtotime($strDateTime);

			if ($strTStamp !== -1 || $strTStamp !== FALSE) {
				$strReturn = strftime($strFormat, $strTStamp);
			}
		} else {
			$strReturn = "";
		}

		return $strReturn;
	}

	public static function toMysql($strDateTime = "") {
		$strReturn = $strDateTime;
		$strFormat = "%Y-%m-%d %H:%M:%S";

		if (empty($strDateTime)) {
			$strTStamp = strtotime("now");
		} else if (is_numeric($strDateTime)) {
			$strTStamp = $strDateTime;
		} else {
			$strTStamp = strtotime($strDateTime);
		}

		if ($strTStamp !== -1 || $strTStamp !== FALSE) {
			$strReturn = strftime($strFormat, $strTStamp);
		}

		return $strReturn;
	}

	public static function parseDate($strDate, $strFormat) {
		/* This method parses a date/time value using a defined format. 
		 * It returns a timestamp that can be used with strftime.
		*/
		
		$arrDate = strptime($strDate, $strFormat);
		$hour 	= ($arrDate['tm_hour'] > 23 || $arrDate['tm_hour'] < 0) ? 0 : $arrDate['tm_hour'];
		$minute = ($arrDate['tm_min'] > 59 || $arrDate['tm_min'] < 0) ? 0 : $arrDate['tm_min'];
		$second = ($arrDate['tm_sec'] > 61 || $arrDate['tm_sec'] < 0) ? 0 : $arrDate['tm_sec'];
		
		$timestamp = mktime($hour, $minute, $second, $arrDate['tm_mon'] + 1, $arrDate['tm_mday'], $arrDate['tm_year'] + 1900);
		
		return $timestamp;
	}

	public static function convertDate($strDate, $strInFormat, $strOutFormat) {
		/* This method takes a date/time value and converts it from one format to the other. 
		 * It returns the converted value.
		*/
		
		return strftime($strOutFormat, self::parseDate($strDate, $strInFormat));
	}

}

?>