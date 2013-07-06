<?php

/**
 *
 * Handles ElementSchedule properties and methods.
 * @author felix
 * @version 0.1.0
 *
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
		$strSql = sprintf($strSql, self::quote($intElementId), self::quote($_CONF['app']['account']->getId()));
		$objSchedules = self::select($strSql);

		return $objSchedules;
	}
}
