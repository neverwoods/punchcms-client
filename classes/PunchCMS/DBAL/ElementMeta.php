<?php

namespace PunchCMS\DBAL;

/**
 * ElementMeta DBA Class.
 * @author Felix Langfeldt <felix@neverwoods.com>
 * @internal
 *
 */
class ElementMeta extends Object
{
    protected $id = null;
    protected $elementid = 0;
    protected $name = "";
    protected $value = "";
    protected $languageid = 0;
    protected $cascade = 0;

    //*** Constructor.
    public function __construct()
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";
    }

    // *** Static inherited functions.
    public static function selectByPK($varValue, $arrFields = array(), $accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::selectByPK($varValue, $arrFields, $accountId);
    }

    public static function select($strSql = "")
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::select($strSql);
    }

    public static function doDelete($varValue)
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::doDelete($varValue);
    }

    public function save($blnSaveModifiedDate = true)
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::save($blnSaveModifiedDate);
    }

    public function delete($accountId = null)
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::delete($accountId);
    }

    public function duplicate()
    {
        self::$object = "\\PunchCMS\\ElementMeta";
        self::$table = "pcms_element_meta";

        return parent::duplicate();
    }
}
