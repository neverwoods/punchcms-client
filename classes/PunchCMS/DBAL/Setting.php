<?php

namespace PunchCMS\DBAL;

/**
 * Setting DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class Setting extends Object
{
	protected $id = NULL;
	protected $accountid = 0;
	protected $settingid = 0;
	protected $value = "";
	protected $username = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\Setting";
		self::$table = "pcms_setting";

		return parent::duplicate();
	}
}
