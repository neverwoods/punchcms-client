<?php

/***
 *
 * ElementPermission DBA Class.
 *
 */

class DBA_ElementPermission extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $userid = 0;
	protected $groupid = 0;

	//*** Constructor.
	public function DBA_ElementPermission() {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "ElementPermission";
		self::$__table = "pcms_element_permission";

		return parent::duplicate();
	}
}

?>