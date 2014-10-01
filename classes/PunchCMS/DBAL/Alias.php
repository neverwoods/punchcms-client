<?php

namespace PunchCMS\DBAL;

/**
 * Alias DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 */
class Alias extends Object
{
    protected $id = null;
    protected $accountid = 0;
    protected $languageid = 0;
    protected $cascade = 0;
    protected $alias = "";
    protected $url = "";
    protected $active = 1;

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";
    }

    //*** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\Alias";
        self::$table = "pcms_alias";

        return parent::duplicate();
    }
}
