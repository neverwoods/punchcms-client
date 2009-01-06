<?php

/***
 *
 * ElementLanguage DBA Class.
 *
 */

class DBA_ElementLanguage extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $languageid = 0;
	protected $cascade = 0;
	protected $active = 1;

	//*** Constructor.
	public function DBA_ElementLanguage() {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "ElementLanguage";
		self::$__table = "pcms_element_language";

		return parent::duplicate();
	}
}

?>