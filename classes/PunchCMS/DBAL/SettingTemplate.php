<?php

namespace PunchCMS\DBAL;

/**
 * SettingTemplate DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class SettingTemplate extends Object
{
    protected $id = null;
    protected $name = "";
    protected $value = "";
    protected $section = "";
    protected $type = "";

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\SettingTemplate";
        self::$table = "pcms_setting_tpl";

        return parent::duplicate();
    }
}
