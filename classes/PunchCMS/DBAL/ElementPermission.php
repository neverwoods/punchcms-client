<?php

namespace PunchCMS\DBAL;

/**
 * ElementPermission DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementPermission extends Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $userid = 0;
	protected $groupid = 0;

	//*** Constructor.
	public function DBA_ElementPermission() {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::duplicate();
	}
}

?>