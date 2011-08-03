<?php

/***
 *
 * StorageData DBA Class.
 *
 */

class DBA_StorageData extends DBA__Object {
	protected $id = NULL;
	protected $itemid = 0;
	protected $originalname = "";
	protected $localname = "";

	//*** Constructor.
	public function DBA_StorageData() {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "StorageData";
		self::$__table = "pcms_storage_data";

		return parent::duplicate();
	}
}

?>