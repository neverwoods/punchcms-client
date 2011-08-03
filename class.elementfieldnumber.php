<?php

/**
 * 
 * Handles ElementFieldNumber properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class ElementFieldNumber extends DBA_ElementFieldNumber {

	public static function getByFieldId($intFieldId, $intLanguageId = 0) {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		$objReturn = new ElementFieldNumber();

		if ($intFieldId > 0) {
			$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE fieldId = '%s' AND languageId = '%s'",
						quote_smart($intFieldId), quote_smart($intLanguageId));
			$objElementValues = ElementFieldNumber::select($strSql);

			if (is_object($objElementValues) && $objElementValues->count() > 0) {
				$objReturn = $objElementValues->current();
			}
		}

		return $objReturn;
	}

}

?>