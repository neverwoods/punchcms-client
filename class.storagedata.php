<?php

/**
 *
 * Handles StorageData properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class StorageData extends DBA_StorageData {

	public static function selectByItemId($intId) {
		global $_CONF;
		parent::$object = "StorageData";
		parent::$table = "pcms_storage_data";

		$strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE itemId = '%s'", $intId);

		return parent::select($strSql);
	}

}

?>
