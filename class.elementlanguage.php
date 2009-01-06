<?php

/* ElementLanguage Class v0.1.0
 * Handles element language properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class ElementLanguage extends DBA_ElementLanguage {

	public static function deleteByElement($intElementId) {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		$strSql = sprintf("DELETE FROM " . self::$__table . " WHERE elementId = '%s'", quote_smart($intElementId));
		self::select($strSql);
	}

	public static function selectByElement($intElementId) {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		$strSql = sprintf("SELECT * FROM " . self::$__table . " WHERE elementId = '%s'", quote_smart($intElementId));
		return self::select($strSql);
	}

}

?>