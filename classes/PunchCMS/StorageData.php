<?php

namespace PunchCMS;

/**
 *
 * Handles StorageData properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class StorageData extends \PunchCMS\DBAL\StorageData
{
    public static function selectByItemId($intId)
    {
        global $_CONF;
        parent::$object = "\\PunchCMS\\StorageData";
        parent::$table = "pcms_storage_data";

        $strSql = sprintf("SELECT * FROM " . parent::$table . " WHERE itemId = '%s'", $intId);

        return parent::select($strSql);
    }
}
