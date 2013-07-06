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
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementFieldText";
		self::$table = "pcms_element_field_text";

		return parent::duplicate();
	}
}

?>