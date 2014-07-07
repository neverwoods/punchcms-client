<?php

/***
 *
 * ElementFieldDate DBA Class.
 *
 */

class DBA_ElementFieldDate extends DBA__Object {
	protected $id = NULL;
	protected $value = "0000-00-00 00:00:00";
	protected $fieldid = 0;
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function DBA_ElementFieldDate() {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementFieldDate";
		self::$table = "pcms_element_field_date";

		return parent::duplicate();
	}
}

?>