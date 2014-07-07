<?php

/***
 *
 * StorageItem DBA Class.
 *
 */

class DBA_StorageItem extends DBA__Object {
	protected $id = NULL;
	protected $accountid = 0;
	protected $parentid = 0;
	protected $name = "";
	protected $description = "";
	protected $typeid = 0;
	protected $username = "";

	//*** Constructor.
	public function DBA_StorageItem() {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "StorageItem";
		self::$table = "pcms_storage_item";

		return parent::duplicate();
	}
}

?>