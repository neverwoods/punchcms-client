<?php

namespace PunchCMS\DBAL;

/**
 * Template DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */

class Template extends Object
{
    protected $id = null;
    protected $accountid = 0;
    protected $name = "";
    protected $apiname = "";
    protected $description = "";
    protected $parentid = 0;
    protected $ispage = 0;
    protected $iscontainer = 0;
    protected $forcecreation = 0;
    protected $active = 0;
    protected $username = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\Template";
        self::$table = "pcms_template";

        return parent::duplicate();
    }
}
