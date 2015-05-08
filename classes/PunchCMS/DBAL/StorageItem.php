<?php

namespace PunchCMS\DBAL;

/**
 * StorageItem DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class StorageItem extends Object
{
    protected $id = null;
    protected $accountid = 0;
    protected $parentid = 0;
    protected $name = "";
    protected $description = "";
    protected $typeid = 0;
    protected $username = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\StorageItem";
        self::$table = "pcms_storage_item";

        return parent::duplicate();
    }
}
