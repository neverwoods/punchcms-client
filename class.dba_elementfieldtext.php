<?php

/***
 *
 * ElementFieldText DBA Class.
 *
 */

class DBA_ElementFieldText extends DBA__Object {
	protected $id = NULL;
	protected $value = "";
	protected $fieldid = 0;
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function DBA_ElementFieldText() {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "ElementFieldText";
		self::$__table = "pcms_element_field_text";

		return parent::duplicate();
	}
}

?>