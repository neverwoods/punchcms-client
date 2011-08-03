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
		parent::$__object = "StorageData";
		parent::$__table = "pcms_storage_data";
		
		$strSql = sprintf("SELECT * FROM " . parent::$__table . " WHERE itemId = '%s'", $intId);
		
		return parent::select($strSql);
	}

}

?>
