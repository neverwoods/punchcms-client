<?php

namespace PunchCMS\DBAL;

/**
 * StorageData DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class StorageData extends Object
{
    protected $id = null;
    protected $itemid = 0;
    protected $originalname = "";
    protected $localname = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\StorageData";
        self::$table = "pcms_storage_data";

        return parent::duplicate();
    }
}
