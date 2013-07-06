<?php

/***
 *
 * Account DBA Class.
 *
 */

class DBA_Account extends DBA__Object {
	protected $id = NULL;
	protected $punchid = 0;
	protected $name = "";
	protected $uri = "";
	protected $timezoneid = 0;
	protected $active = 0;

	//*** Constructor.
	public function DBA_Account() {
		self::$object = "Account";
		self::$table = "punch_account";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "Account";
		self::$table = "punch_account";

		return parent::duplicate();
	}
}

?>