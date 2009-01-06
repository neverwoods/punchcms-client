<?php

/***
 *
 * ElementFieldNumber DBA Class.
 *
 */

class DBA_ElementFieldNumber extends DBA__Object {
	protected $id = NULL;
	protected $value = 0;
	protected $fieldid = 0;
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function DBA_ElementFieldNumber() {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "ElementFieldNumber";
		self::$__table = "pcms_element_field_number";

		return parent::duplicate();
	}
}

?>