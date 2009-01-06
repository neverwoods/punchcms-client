<?php

/* StorageData Class v0.1.0
 * Handles StorageData properties and methods.
 *
 * CHANGELOG
 * version 0.1.0, 04 Apr 2006
 *   NEW: Created class.
 */

class StorageData extends DBA_StorageData {

	public static function selectByItemId($intId) {
		global $_CONF;
		parent::$__object = "StorageData";
		parent::$__table = "pcms_storage_data";
		
		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE itemId = '%s'", $intId);
		
		return parent::select($strSql);
	}

}

?>
