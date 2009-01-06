<?php

/***
 *
 * Setting DBA Class.
 *
 */

class DBA_Setting extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $settingid = 0;
	protected $value = "";
	protected $username = "";

	//*** Constructor.
	public function DBA_Setting() {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "Setting";
		self::$__table = "pcms_setting";

		return parent::duplicate();
	}
}

?>