<?php

namespace PunchCMS\DBAL;

/**
 * ElementPermission DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class ElementPermission extends Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $userid = 0;
	protected $groupid = 0;

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\ElementPermission";
		self::$table = "pcms_element_permission";

		return parent::duplicate();
	}
}
