<?php

/***
 *
 * SettingTemplate DBA Class.
 *
 */

class DBA_SettingTemplate extends DBA__Object {
	protected $id = NULL;
	protected $name = "";
	protected $value = "";
	protected $section = "";
	protected $type = "";

	//*** Constructor.
	public function DBA_SettingTemplate() {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "SettingTemplate";
		self::$table = "pcms_setting_tpl";

		return parent::duplicate();
	}
}

?>