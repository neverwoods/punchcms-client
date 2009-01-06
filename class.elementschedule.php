<?php

/* ElementSchedule Class v0.1.0
 * Handles ElementPermission properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 25 Jul 2007
 *   NEW: Created class.
 */

class ElementSchedule extends DBA_ElementSchedule {

	public static function selectByElement($intElementId) {
		global $_CONF;
	
		$objReturn = NULL;
	
		$strSql = "SELECT pcms_element_schedule.* 
					FROM pcms_element_schedule, pcms_element 
					WHERE pcms_element_schedule.elementId = '%s' 
					AND pcms_element.accountId = '%s' 
					AND pcms_element_schedule.elementId = pcms_element.id";
		$strSql = sprintf($strSql, quote_smart($intElementId), quote_smart($_CONF['app']['account']->getId()));
		$objSchedules = self::select($strSql);
		
		return $objSchedules;
	}

}

?>
