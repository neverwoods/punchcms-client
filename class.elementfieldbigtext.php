<?php

/* ElementFieldBigText Class v0.1.0
 * Handles ElementFieldBigText properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class ElementFieldBigText extends DBA_ElementFieldBigText {

	public static function getByFieldId($intFieldId, $intLanguageId = 0) {
		self::$__object = "ElementFieldBigText";
		self::$__table = "pcms_element_field_bigtext";

		$objReturn = new ElementFieldBigText();

		if ($intFieldId > 0) {
			$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE fieldId = '%s' AND languageId = '%s'",
						quote_smart($intFieldId), quote_smart($intLanguageId));
			$objElementValues = ElementFieldBigText::select($strSql);

			if (is_object($objElementValues) && $objElementValues->count() > 0) {
				$objReturn = $objElementValues->current();
			}
		}

		return $objReturn;
	}

}

?>