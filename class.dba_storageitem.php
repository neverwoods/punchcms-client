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
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array()) {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::selectByPK($varValue, $arrFields);
	}

	public static function select($strSql = "") {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete() {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::delete();
	}

	public function duplicate() {
		self::$__object = "StorageItem";
		self::$__table = "pcms_storage_item";

		return parent::duplicate();
	}
}

?>