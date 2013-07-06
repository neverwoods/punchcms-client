<?php

/***
 *
 * ElementFieldBigText DBA Class.
 *
 */

class DBA_ElementFieldBigText extends DBA__Object {
	protected $id = NULL;
	protected $value = "";
	protected $fieldid = 0;
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function DBA_ElementFieldBigText() {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementFieldBigText";
		self::$table = "pcms_element_field_bigtext";

		return parent::duplicate();
	}
}

?>