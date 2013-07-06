<?php

/***
 *
 * Alias DBA Class.
 *
 */

class DBA_Alias extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $languageid = 0;
	protected $cascade = 0;
	protected $alias = "";
	protected $url = "";
	protected $active = 1;

	//*** Constructor.
	public function DBA_Alias() {
		self::$object = "Alias";
		self::$table = "pcms_alias";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "Alias";
		self::$table = "pcms_alias";

		return parent::duplicate();
	}
}

?>