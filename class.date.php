<?php

/* Date Class v0.2.5
 * Holds methods for misc. date calls.
 *
 * CHANGELOG
 * version 0.2.5, 29 Sep 2009
 *   ADD: Added a replcament function for strptime.
 *   FIX: Fixed the call to strptime on Windows.
 * version 0.2.4, 16 Jun 2008
 *   FIX: Fixed the timeSince method.
 * version 0.2.3, 11 May 2008
 *   ADD: Added the timeSince method.
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
		
		$arrDate = (function_exists("strptime")) ? strptime($strDate, $strFormat) : self::__strptime($strDate, $strFormat);
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
	
	public function timeSince($strMysqlDateTime, $arrLocalTime = array('days', 'day', 'hours', 'hour', 'minutes', 'minute', 'seconds', 'second')) {
		$intNow = strtotime("now");
		$intPast = strtotime($strMysqlDateTime);
		$intSince = ($intNow <= $intPast) ? $intPast - $intNow : $intNow - $intPast;
		
		$intTempDays = $intSince / 60 / 60 / 24;
		if ($intTempDays > 0) {
			$intDays = round($intTempDays);
			$intTempHours = ($intTempDays - $intDays) * 24;
		} else {
			$intDays = 0;
			$intTempHours = $intSince / 60 / 60;
		}
		if ($intTempHours > 0) {
			$intHours = round($intTempHours);
			$intTempMinutes = ($intTempHours - $intHours) * 60;
		} else {
			$intHours = 0;
			$intTempMinutes = $intSince / 60;
		}
		if ($intTempMinutes > 0) {
			$intMinutes = round($intTempMinutes);
			$intSeconds = round(($intTempMinutes - $intMinutes) * 60);
		} else {
			$intMinutes = 0;
			$intSeconds = $intSince;
		}
		
		if ($intDays > 1) {
			//*** Days.
			$strReturn = "{$intDays} {$arrLocalTime[0]}";
		} else {
			if ($intDays > 0) {
				//*** Day + hours.
				$strReturn = "{$intDays} {$arrLocalTime[1]}";
				//$strReturn .= ($intHours > 0) ? " {$intHours} {$arrLocalTime[2]}" : " 1 {$arrLocalTime[3]}";
			} else {
				if ($intHours > 0) {
					//*** Hours + minutes.
					$strReturn = ($intHours > 1) ? "{$intHours} {$arrLocalTime[2]}" : " 1 {$arrLocalTime[3]}";
					//$strReturn .= ($intMinutes > 1) ? " {$intMinutes} {$arrLocalTime[4]}" : " 1 {$arrLocalTime[5]}";
				} else {
					if ($intMinutes > 0) {
						$strReturn = ($intMinutes > 1) ? "{$intMinutes} {$arrLocalTime[4]} " : "{$intMinutes} {$arrLocalTime[5]} ";
					} else {
						//*** Minutes + seconds.
						//$strReturn = ($intMinutes > 1) ? "{$intMinutes} {$arrLocalTime[4]} " : ($intMinutes > 0) ? "{$intMinutes} {$arrLocalTime[5]} " : "";
						$strReturn = ($intSeconds > 1) ? "{$intSeconds} {$arrLocalTime[6]}" : " 1 {$arrLocalTime[7]}";
					}
				}
			}
		}
				
		return $strReturn;
	}
	
    private static function __strptime($sDate, $sFormat) {
        $aResult = array
        (
            'tm_sec'   => 0,
            'tm_min'   => 0,
            'tm_hour'  => 0,
            'tm_mday'  => 1,
            'tm_mon'   => 0,
            'tm_year'  => 0,
            'tm_wday'  => 0,
            'tm_yday'  => 0,
            'unparsed' => $sDate,
        );
        
        while($sFormat != "")
        {
            // ===== Search a %x element, Check the static string before the %x =====
            $nIdxFound = strpos($sFormat, '%');
            if($nIdxFound === false)
            {
                // There is no more format. Check the last static string.
                $aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
                break;
            }
            
            $sFormatBefore = substr($sFormat, 0, $nIdxFound);
            $sDateBefore   = substr($sDate,   0, $nIdxFound);
            
            if($sFormatBefore != $sDateBefore) break;
            
            // ===== Read the value of the %x found =====
            $sFormat = substr($sFormat, $nIdxFound);
            $sDate   = substr($sDate,   $nIdxFound);
            
            $aResult['unparsed'] = $sDate;
            
            $sFormatCurrent = substr($sFormat, 0, 2);
            $sFormatAfter   = substr($sFormat, 2);
            
            $nValue = -1;
            $sDateAfter = "";
                        
            switch($sFormatCurrent)
            {
                case '%S': // Seconds after the minute (0-59)
                    
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 59)) return false;
                    
                    $aResult['tm_sec']  = $nValue;
                    break;
                
                // ----------
                case '%M': // Minutes after the hour (0-59)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 59)) return false;
                
                    $aResult['tm_min']  = $nValue;
                    break;
                
                // ----------
                case '%H': // Hour since midnight (0-23)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 23)) return false;
                
                    $aResult['tm_hour']  = $nValue;
                    break;
                
                // ----------
                case '%d': // Day of the month (1-31)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 1) || ($nValue > 31)) return false;
                
                    $aResult['tm_mday']  = $nValue;
                    break;
                
                // ----------
                case '%m': // Months since January (0-11)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 1) || ($nValue > 12)) return false;
                
                    $aResult['tm_mon']  = ($nValue - 1);
                    break;
                
                // ----------
                case '%Y': // Years since 1900
                    sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);
                    
                    if($nValue < 1900) return false;
                
                    $aResult['tm_year']  = ($nValue - 1900);
                    break;
                
                // ----------
                case '%B': // Monthname
                	$arrDate = explode(" ", $sDate);
                	$nValue = array_shift($arrDate);
                	$sDateAfter = " " . implode(" ", $arrDate);
                	                     
                	for ($intCount = 1; $intCount <= 12; $intCount++) {
                		$sName = date("F", mktime(0, 0, 0, $intCount, 10));
                		if ($sName == $nValue) {
                			$nValue = $intCount - 1;
                			break;
                		}
                	}
                	
                	if (is_string($nValue)) return false;
                                    
                    $aResult['tm_mon'] = $nValue;
                    break;
                
                // ----------
                default:
                    break 2; // Break Switch and while
                
            } // END of case format
            
            // ===== Next please =====
            $sFormat = $sFormatAfter;
            $sDate   = $sDateAfter;
            
            $aResult['unparsed'] = $sDate;
            
        } // END of while($sFormat != "")
                
        // ===== Create the other value of the result array =====
        $nParsedDateTimestamp = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
                                $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
        
        // Before PHP 5.1 return -1 when error
        if(($nParsedDateTimestamp === false)
        ||($nParsedDateTimestamp === -1)) return false;
        
        $aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
        $aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)

        return $aResult;
    }

}

?>