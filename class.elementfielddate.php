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
		self::$__object = "ElementFieldDate";
		self::$__table = "pcms_element_field_date";

		$objReturn = new ElementFieldDate();

		if ($intFieldId > 0) {
			$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE fieldId = '%s' AND languageId = '%s'",
						quote_smart($intFieldId), quote_smart($intLanguageId));
			$objElementValues = ElementFieldDate::select($strSql);

			if (is_object($objElementValues) && $objElementValues->count() > 0) {
				$objReturn = $objElementValues->current();
			}
		}

		return $objReturn;
	}

}

?>