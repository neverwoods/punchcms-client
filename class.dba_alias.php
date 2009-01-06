<?php

/***
 *
 * Alias DBA Class.
 *
 */

class DBA_Alias extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $alias = "";
	protected $url = "";
	protected $active = 1;

	//*** Constructor.
	public function DBA_Alias() {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "Alias";
		self::$__table = "pcms_alias";

		return parent::duplicate();
	}
}

?>