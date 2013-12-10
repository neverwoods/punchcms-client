<?php

/**
 *
 * Handles ElementFieldDate properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementFieldDate extends DBA_ElementFieldDate {

	public static function getByFieldId($intFieldId, $intLanguageId = 0) {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		$objReturn = new ElementFieldDate();

		if ($intFieldId > 0) {
			$strSql = sprintf(
				"SELECT * FROM " . self::$table . " WHERE fieldId = %s AND languageId = %s",
				self::quote($intFieldId),
				self::quote($intLanguageId)
			);
			$objElementValues = ElementFieldDate::select($strSql);

			if (is_object($objElementValues) && $objElementValues->count() > 0) {
				$objReturn = $objElementValues->current();
			}
		}

		return $objReturn;
	}
}
