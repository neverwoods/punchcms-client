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
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "SettingTemplate";
		self::$__table = "pcms_setting_tpl";

		return parent::duplicate();
	}
}

?>