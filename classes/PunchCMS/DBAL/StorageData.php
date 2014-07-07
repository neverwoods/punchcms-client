<?php

namespace PunchCMS\DBAL;

/**
 * StorageData DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
abstract class StorageData extends Object {
	protected $id = NULL;
	protected $itemid = 0;
	protected $originalname = "";
	protected $localname = "";

	//*** Constructor.
	public function __construct() {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = TRUE) {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$object = "\\PunchCMS\\DBAL\\StorageData";
		self::$table = "pcms_storage_data";

		return parent::duplicate();
	}
}
