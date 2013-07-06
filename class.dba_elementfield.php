<?php

/***
 *
 * ElementField DBA Class.
 *
 */

class DBA_ElementField extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $templatefieldid = 0;
	protected $fieldtypeid = 0;
	protected $originalname = "";
	protected $username = "";

	//*** Constructor.
	public function DBA_ElementField() {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementField";
		self::$table = "pcms_element_field";

		return parent::duplicate();
	}
}

?>