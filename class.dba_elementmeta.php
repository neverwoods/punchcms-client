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
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "ElementMeta";
		self::$__table = "pcms_element_meta";

		return parent::duplicate();
	}
}

?>