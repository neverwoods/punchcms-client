<?php

/***
 *
 * ElementMeta DBA Class.
 *
 */

class DBA_ElementMeta extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $name = "";
	protected $value = "";
	protected $languageid = 0;
	protected $cascade = 0;

	//*** Constructor.
	public function DBA_ElementMeta() {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementMeta";
		self::$table = "pcms_element_meta";

		return parent::duplicate();
	}
}

?>